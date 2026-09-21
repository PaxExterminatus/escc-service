<?php

namespace App\Domain\Templates\Services;

use App\Domain\App\Container\Models\Container;
use App\Domain\App\Course\Services\CourseBalanceCalculator;
use App\Domain\App\Profile\Models\Profile;
use App\Domain\Messages\Models\ClientCommunication;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Вычисляет значения тегов из TagRegistry для конкретного клиента или конкретного контейнера
 * (счёта). Один и тот же resolveForClient используется и для сообщений (клиент — всё, что
 * есть), и как основа для документов (resolveForContainer = client-теги + свои invoice-теги).
 */
class TagResolver
{
    public function __construct(protected CourseBalanceCalculator $balance)
    {
    }

    /**
     * @return array<string, string>
     */
    public function resolveForClient(int $clientId): array
    {
        $params = [];

        $profile = Profile::where('client_id', $clientId)->first();

        if ($profile) {
            $params['client_code'] = $profile->client_code;
            $params['client_name'] = trim("{$profile->client_last_name} {$profile->client_name} {$profile->client_middle_name}");
            $params['client_birthday'] = $profile->client_birthday?->format('d.m.Y') ?? '—';
        }

        $communication = ClientCommunication::where('client_id', $clientId)->first();

        if ($communication) {
            $params['client_phone'] = $communication->client_mphone ?? '—';
            $params['client_email'] = $communication->client_email ?? '—';
        }

        // Раньше брали готовое значение из API_SERVICE_ACCOUNT.ACCOUNT_CLIENT_TOTAL_DEB — эта
        // легаси-функция требует контекст USERID, которого нет вне самого приложения-легаси
        // (см. project_local_xe_debt_function_broken), поэтому считаем баланс инлайн-SQL по
        // MONEY_DIST — та же формула, что и для курса/контейнера (см. CourseBalanceCalculator).
        $params['amount'] = number_format($this->balance->forClient($clientId)['balance'], 2, '.', '');

        return $params;
    }

    /**
     * @return array<string, string>
     */
    public function resolveForContainer(Container $container): array
    {
        $params = $this->resolveForClient($container->client_id);

        $params['invoice_number'] = (string) $container->container_id;
        $params['invoice_date'] = $container->send_date?->format('d.m.Y') ?? '—';
        $params['item_name'] = "Оплата за посылку №{$container->container_id}";
        $params['item_amount'] = $this->formatInvoiceAmount((float) $container->container_cost);
        $params['total_amount'] = $this->formatInvoiceAmount((float) $container->container_cost);
        $params['container_code'] = $container->container_code ?? '—';

        $subscription = DB::connection('oracle')->selectOne(
            'SELECT cat.node_name, cs.next_date FROM client_sub cs JOIN catalogue cat ON cat.node_id = cs.product_id WHERE cs.sub_id = :subId',
            ['subId' => $container->sub_id]
        );

        if ($subscription) {
            $params['course_name'] = $subscription->node_name;
            $params['next_send_date'] = $subscription->next_date
                ? Carbon::parse($subscription->next_date)->format('d.m.Y')
                : '—';
        }

        try {
            $lastPayment = DB::connection('oracle')->selectOne(
                'SELECT box_pkg_utils.BoxLastPaymentDate(:id) AS d FROM DUAL',
                ['id' => $container->container_id]
            )->d;

            $params['payment_last_date'] = $lastPayment
                ? Carbon::parse($lastPayment)->format('d.m.Y')
                : '—';
        } catch (Throwable $e) {
            // платежей по посылке ещё не было либо функция недоступна — оставляем тег неразрешённым
        }

        return $params;
    }

    protected function formatInvoiceAmount(float $amount): string
    {
        return number_format($amount, 2, ',', ' ');
    }
}
