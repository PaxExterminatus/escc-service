<?php

namespace App\Domain\Templates\Services;

use App\Domain\App\Container\Models\Container;
use App\Domain\Templates\Enums\TemplateOperationEnum;
use App\Domain\Templates\Enums\TemplateTypeEnum;
use App\Domain\Templates\Enums\TemplateWrapperRoleEnum;
use App\Domain\Templates\Models\Template;

/**
 * Собирает html-письмо: произвольное html-тело оборачивается в шаблон обёртки письма
 * (см. Template::wrappers()) — шапка и футер сразу, одним шаблоном, со спецтегом {BODY} на
 * месте вставки тела. Какую именно обёртку взять — решает вызывающий код:
 *  - WRAPPER_ID конкретного content-шаблона — обёртка назначена явно;
 *  - WRAPPER_AUTO — обёртка выбирается по фактическому балансу клиента (см. wrapperForBalance);
 *  - ни то ни другое — берётся помеченная IS_DEFAULT, а если и такой нет — встроенная
 *    (см. defaultWrapperHtml), как и для docx-шаблона счёта.
 *
 * Правила сборки живут только здесь — предпросмотр в интерфейсе ходит на сервер
 * (TemplateController::preview), а не повторяет их на JS.
 */
class EmailComposer
{
    /** Спецтег в обёртке, на место которого встаёт тело письма */
    public const BODY_TAG = '{BODY}';

    /** Чем заменяется {BODY}, когда показываем саму обёртку — реального письма тут нет */
    protected const BODY_STUB = '<div style="border: 1px dashed #999; padding: 12px; color: #888; font-style: italic;">Здесь будет вставлено тело письма</div>';

    public function __construct(protected TagResolver $tags)
    {
    }

    /**
     * @return array{subject: string, html: string}
     */
    public function composeForContainer(Container $container): array
    {
        $content = Template::forOperation(TemplateOperationEnum::invoice, TemplateTypeEnum::html);

        abort_if(!$content, 422, 'Для операции "Счёт по email" не назначен html-шаблон письма.');

        $params = $this->tags->resolveForContainer($container);

        return ['subject' => $content->name, 'html' => $this->wrap($content->render($params), $params, $content->wrapper_id, $content->wrapper_auto)];
    }

    /**
     * Письмо из обычного текста, который набрал оператор: переносы строк становятся <br>,
     * разметка экранируется, результат оборачивается. Единственный путь для plain-текста —
     * иначе предпросмотр и реальная отправка расходятся (раньше правило было скопировано
     * в TemplateController::emailPreview и MessageSendController::send).
     *
     * @param array<string, string> $params
     */
    public function composeFromText(string $text, array $params = [], ?int $wrapperId = null, bool $autoByBalance = false): string
    {
        return $this->wrap(nl2br(e($text)), $params, $wrapperId, $autoByBalance);
    }

    /**
     * Оборачивает уже готовое html-тело в обёртку письма. Для текста, введённого человеком,
     * используйте composeFromText() — здесь экранирование не делается намеренно.
     *
     * @param array<string, string> $params для подстановки тегов внутри самой обёртки — из
     *                                      них же, если $autoByBalance, берётся баланс ({amount})
     * @param int|null $wrapperId конкретная обёртка (WRAPPER_ID content-шаблона); игнорируется при $autoByBalance
     * @param bool $autoByBalance выбрать обёртку по фактическому балансу, а не по WRAPPER_ID
     */
    public function wrap(string $html, array $params = [], ?int $wrapperId = null, bool $autoByBalance = false): string
    {
        // Цепочка: авто-по-балансу (если включено) → выбранная обёртка → обёртка по умолчанию
        // → встроенная. Выбранную могли удалить или деактивировать — тогда письмо всё равно
        // должно уйти в фирменной вёрстке, а не провалиться сразу во встроенную заглушку.
        $wrapper = ($autoByBalance ? $this->wrapperForBalance($params) : null)
            ?? ($wrapperId ? Template::activeWrapper($wrapperId) : null)
            ?? Template::defaultWrapper();

        $wrapperHtml = $wrapper ? $wrapper->render($params) : $this->defaultWrapperHtml($params);

        return $this->spliceBody($wrapperHtml, $html);
    }

    /**
     * Обёртка по фактическому балансу клиента: тег {amount} — сумма задолженности
     * (см. TagResolver::resolveForClient), положительная — долг есть. Балансу нет откуда
     * взяться (тег не разрешился — см. project_local_xe_debt_function_broken) — авто-выбор
     * молча отступает к обычной цепочке фолбэков.
     *
     * @param array<string, string> $params
     */
    protected function wrapperForBalance(array $params): ?Template
    {
        if (!isset($params['amount']) || !is_numeric($params['amount'])) {
            return null;
        }

        $role = (float) $params['amount'] > 0 ? TemplateWrapperRoleEnum::debt : TemplateWrapperRoleEnum::positive;

        return Template::wrapperForRole($role);
    }

    /**
     * Предпросмотр любого шаблона в том виде, в каком его увидит получатель: обёртку —
     * с заглушкой вместо тела, содержимое (текст/html) — внутри его обёртки.
     *
     * Теги не разрешаются: конкретного клиента у шаблона нет, оператор смотрит вёрстку. Из-за
     * этого авто-выбор по балансу здесь всегда отступает к обёртке по умолчанию — какой цвет
     * реально уйдёт, видно только при настоящей отправке.
     */
    public function preview(Template $template): string
    {
        return $template->type_id === TemplateTypeEnum::wrapper
            ? $this->spliceBody($template->body ?? '', self::BODY_STUB)
            : $this->wrap($template->contentHtml(), [], $template->wrapper_id, $template->wrapper_auto);
    }

    /**
     * Вставляет тело в обёртку: на место {BODY}, а если его в обёртке нет — просто следом
     * за ней (чтобы недооформленная обёртка не съедала письмо целиком).
     */
    protected function spliceBody(string $wrapperHtml, string $body): string
    {
        return str_contains($wrapperHtml, self::BODY_TAG)
            ? str_replace(self::BODY_TAG, $body, $wrapperHtml)
            : $wrapperHtml . $body;
    }

    /**
     * Аварийный вариант: в системе нет ни одной обёртки (не сработал TemplateSeeder, обёртки
     * удалили вручную). Письмо всё равно уходит в приличной вёрстке, а не голым текстом.
     *
     * @param array<string, string> $params
     */
    protected function defaultWrapperHtml(array $params): string
    {
        return TagInterpolator::apply(EmailWrapperLayout::build(EmailWrapperLayout::NEUTRAL), $params, true);
    }
}
