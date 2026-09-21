<?php

namespace App\Domain\App\Course\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Прогноз будущих отправок курса — виртуальные строки для страницы курса ("график отправки"),
 * контейнеров под них ещё не существует. Портирует формулу легаси-процедуры PARCELS_SCHEDULE
 * (REVELATION 20260904 1518.sql, ~строка 145335, сама она ничего никуда не пишет — строит тот
 * же отчёт "на лету"): дата очередной отправки = первое число месяца, к которому прибавили
 * конкретный день (SbsRecalculationDay), и так перепрыгнули на N месяцев вперёд
 * (SbsFrequencyOfSending); следующая отправка считается от предыдущей той же формулой.
 *
 * Частота/день/кол-во уроков в отправке берутся из OBJECT_CONTRACT (P4/P5/P6) — ключ
 * (TYPE_ID = REV_CONST.objTypeBasket = 16, OBJECT_ID = CLIENT_SUB.ITEM_ID). Строки может не
 * быть (легаси-таблица параметров конкретного заказа, не всегда заполнена) — тогда график
 * посчитать нечем, метод возвращает пустой список, а не гадает частоту.
 *
 * Точка отсчёта — дата последней РЕАЛЬНОЙ отправки (последний CONTAINER.SEND_DATE курса), а не
 * CLIENT_SUB.NEXT_DATE: тот выставляется один раз при оформлении подписки и не переезжает
 * автоматически после каждой реальной отправки, значит на середине курса может быть уже
 * устаревшим сам по себе.
 *
 * Пауза — единственная активная (CLIENT_SUB.BREAK_DATE1..BREAK_DATE2) — если очередная
 * запланированная дата попадает раньше конца паузы, отправка просто откладывается помесячно до
 * первой даты после её окончания.
 *
 * Стоимость будущих отправок — не факт, а оценка: берём CONTAINER_COST последней реальной
 * отправки этого курса и переносим её на все запланированные (тариф мог измениться к моменту
 * реальной отправки, поэтому фронт должен показывать это как "≈", а не как точную цифру).
 * Если реальных отправок ещё не было вовсе — оценивать не от чего, cost = null.
 */
class CourseScheduleCalculator
{
    protected const OBJECT_TYPE_BASKET = 16; // REV_CONST.objTypeBasket

    /**
     * @return array<array{date: Carbon, units: int|null, estimated_cost: float|null}>
     */
    public function project(int $subId, int $maxCount = 6): array
    {
        $sub = DB::connection('oracle')->selectOne(
            'SELECT item_id, start_date, finish_date, break_date2 FROM client_sub WHERE sub_id = :subId',
            ['subId' => $subId]
        );

        if (!$sub) {
            return [];
        }

        $params = DB::connection('oracle')->selectOne(
            'SELECT p4, p5, p6 FROM object_contract WHERE type_id = :typeId AND object_id = :itemId',
            ['typeId' => self::OBJECT_TYPE_BASKET, 'itemId' => $sub->item_id]
        );

        // Нет параметров частоты/дня отправки для этого заказа — график не считаем.
        if (!$params || !$params->p5 || !$params->p6) {
            return [];
        }

        $monthStep = max(1, (int) $params->p5); // "1 Month" -> ведущая цифра
        $recalcDay = max(1, (int) $params->p6);
        $units = $params->p4 !== null ? (int) $params->p4 : null;

        $finishDate = $sub->finish_date ? Carbon::parse($sub->finish_date) : null;
        $breakEnd = $sub->break_date2 ? Carbon::parse($sub->break_date2) : null;

        // ROWNUM, не FETCH FIRST/LIMIT — локальная XE не понимает ANSI OFFSET/FETCH (см.
        // CourseController::search()).
        $lastContainer = DB::connection('oracle')->selectOne(
            "SELECT * FROM (
                SELECT send_date, container_cost FROM container WHERE sub_id = :subId ORDER BY send_date DESC
            ) WHERE ROWNUM <= 1",
            ['subId' => $subId]
        );

        $estimatedCost = $lastContainer ? (float) $lastContainer->container_cost : null;
        $anchor = $lastContainer ? Carbon::parse($lastContainer->send_date) : Carbon::parse($sub->start_date);
        $current = $this->nextRecalcDate($anchor, $recalcDay, $monthStep);

        // Уже просроченное (не ушло, но должно было) и то, что попадает в активную паузу —
        // не показываем как "план на будущее", сразу докручиваем до ближайшей future-даты.
        $today = Carbon::today();

        while ($current->lt($today) || ($breakEnd && $current->lt($breakEnd))) {
            $current = $this->nextRecalcDate($current, $recalcDay, $monthStep);
        }

        $schedule = [];

        for ($i = 0; $i < $maxCount; $i++) {
            if ($finishDate && $current->gt($finishDate)) {
                break;
            }

            $schedule[] = ['date' => $current->copy(), 'units' => $units, 'estimated_cost' => $estimatedCost];
            $current = $this->nextRecalcDate($current, $recalcDay, $monthStep);
        }

        return $schedule;
    }

    /** ADD_MONTHS(TRUNC(date, 'MONTH') + recalcDay - 1, monthStep) — см. PARCELS_SCHEDULE */
    protected function nextRecalcDate(Carbon $from, int $recalcDay, int $monthStep): Carbon
    {
        return $from->copy()->startOfMonth()->addDays($recalcDay - 1)->addMonthsNoOverflow($monthStep);
    }
}
