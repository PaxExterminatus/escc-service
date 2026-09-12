<?php

namespace Database\Seeders;

use App\Domain\Messages\Models\MessageTemplate;
use Illuminate\Database\Seeder;

/**
 * Стартовый набор шаблонов сообщений (SMS/Email); редактируется через CRUD в /messages/templates.
 *
 * php artisan db:seed --class="Database\Seeders\MessageTemplateSeeder"
 */
class MessageTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'code' => 'account_debt',
                'name' => 'Задолженность по счёту',
                'body' => 'Долг {amount} руб. Для оплаты используйте код студента {client_code}.',
            ],
            [
                'code' => 'info_client',
                'name' => 'Информационное сообщение',
                'body' => 'Код студента {client_code}.',
            ],
        ];

        foreach ($templates as $template) {
            MessageTemplate::updateOrCreate(['code' => $template['code']], $template + ['is_active' => true]);
        }

        if ($this->command) {
            $this->command->info('Шаблоны сообщений готовы: ' . implode(', ', array_column($templates, 'code')));
        }
    }
}
