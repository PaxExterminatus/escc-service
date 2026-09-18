<?php

namespace Database\Seeders;

use App\Domain\Templates\Enums\TemplateOperationEnum;
use App\Domain\Templates\Enums\TemplateTypeEnum;
use App\Domain\Templates\Enums\TemplateWrapperRoleEnum;
use App\Domain\Templates\Models\Template;
use App\Domain\Templates\Services\EmailWrapperLayout;
use Illuminate\Database\Seeder;

/**
 * Стартовый набор шаблонов: обёртки письма, содержимое письма со счётом и свободные шаблоны
 * сообщений. Дальше всё редактируется оператором в /messages/templates.
 *
 * Не идемпотентный updateOrCreate, а firstOrCreate по CODE: повторный запуск на живой базе
 * НЕ затирает правки оператора, только добавляет то, чего ещё нет.
 *
 * php artisan db:seed --class="Database\Seeders\TemplateSeeder"
 */
class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedWrappers();
        $this->seedInvoiceEmail();
        $this->seedMessageTemplates();

        if ($this->command) {
            $this->command->info('Шаблоны готовы: всего записей — ' . Template::count());
        }
    }

    /**
     * Обёртки письма (шапка + футер вокруг {BODY}). Красная/зелёная помечены ролью — по ней
     * EmailComposer::wrapperForBalance подбирает цвет автоматически по факту долга у клиента,
     * без ручного выбора обёртки на каждом шаблоне (см. WRAPPER_AUTO у content-шаблонов ниже).
     */
    protected function seedWrappers(): void
    {
        $presets = [
            'wrapper_neutral_blue' => ['Обёртка — нейтральная (синяя)', EmailWrapperLayout::NEUTRAL, true, null],
            'wrapper_debt_red' => ['Обёртка — задолженность (красная)', EmailWrapperLayout::DEBT, false, TemplateWrapperRoleEnum::debt],
            'wrapper_ok_green' => ['Обёртка — без задолженности (зелёная)', EmailWrapperLayout::POSITIVE, false, TemplateWrapperRoleEnum::positive],
        ];

        foreach ($presets as $code => [$name, $palette, $isDefault, $role]) {
            Template::firstOrCreate(['code' => $code], [
                'type_id' => TemplateTypeEnum::wrapper->value,
                'name' => $name,
                'body' => EmailWrapperLayout::build($palette),
                'is_default' => $isDefault,
                'wrapper_role' => $role?->value,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Содержимое письма со счётом — тело, которое подставляется в {BODY} обёртки. WRAPPER_AUTO:
     * счёт всегда про баланс клиента, поэтому цвет письма подбирается по факту долга, а не
     * фиксируется заранее одной обёрткой.
     */
    protected function seedInvoiceEmail(): void
    {
        Template::firstOrCreate(
            [
                'operation_id' => TemplateOperationEnum::invoice->value,
                'type_id' => TemplateTypeEnum::html->value,
            ],
            [
                'code' => 'op_' . TemplateOperationEnum::invoice->value . '_' . TemplateTypeEnum::html->value,
                'name' => 'Счёт по email',
                'body' => '<p>Здравствуйте, {client_name}! Счёт №{invoice_number} на сумму {total_amount} руб. приложен к письму.</p>',
                'wrapper_auto' => true,
                'is_active' => true,
            ]
        );
    }

    /**
     * Свободные шаблоны — оператор выбирает их вручную при отправке сообщения клиенту.
     * "Задолженность по счёту" — тоже WRAPPER_AUTO: письмо о долге логично подсвечивать
     * красным/зелёным по фактическому балансу в момент отправки, а не заранее одним цветом.
     */
    protected function seedMessageTemplates(): void
    {
        $templates = [
            [
                'code' => 'account_debt',
                'name' => 'Задолженность по счёту',
                'body' => 'Долг {amount} руб. Для оплаты используйте код студента {client_code}.',
                'wrapper_auto' => true,
            ],
            [
                'code' => 'info_client',
                'name' => 'Информационное сообщение',
                'body' => 'Код студента {client_code}.',
                'wrapper_auto' => false,
            ],
        ];

        foreach ($templates as $template) {
            Template::firstOrCreate(
                ['code' => $template['code']],
                $template + ['type_id' => TemplateTypeEnum::text->value, 'is_active' => true]
            );
        }
    }
}
