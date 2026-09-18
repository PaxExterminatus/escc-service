/**
 * Зеркала backend-енумов. Значения приходят в ответах API как *_id, а подписи к ним сервер
 * отдаёт сам (type_label, operation_label, category_label) — здесь только идентификаторы,
 * по которым фронт ветвится, чтобы в компонентах не оставалось голых чисел.
 *
 * При изменении соответствующего PHP-енума правится и это — источник истины там.
 */

/** App\Domain\Templates\Enums\TemplateTypeEnum */
const TemplateType = Object.freeze({
    text: 1,
    html: 2,
    docx: 3,
    wrapper: 4,
});

/** App\Domain\Templates\Enums\TemplateOperationEnum */
const TemplateOperation = Object.freeze({
    invoice: 1,
});

/** App\Domain\Templates\Enums\TagScopeEnum */
const TagScope = Object.freeze({
    client: 1,
    invoice: 2,
});

/** App\Domain\Templates\Enums\TemplateWrapperRoleEnum — роль обёртки в авто-выборе по балансу */
const TemplateWrapperRole = Object.freeze({
    debt: 1,
    positive: 2,
});

/** Спецтег обёртки письма — на его место подставляется тело (см. EmailComposer::wrap) */
const BODY_TAG = '{BODY}';

export {
    TemplateType,
    TemplateOperation,
    TagScope,
    TemplateWrapperRole,
    BODY_TAG,
}
