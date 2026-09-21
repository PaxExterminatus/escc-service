<?php

namespace App\Console\Commands;

use App\Domain\Messages\Enums\MessageTypeEnum;
use App\Domain\Messages\Services\DailyBatchDispatcher;
use Illuminate\Console\Command;

/**
 * Отправляет накопившуюся дневную очередь (EMSG.EMSG_STATUS = wait) по обоим каналам — то же
 * самое, что раньше делала только ручная кнопка "Отправить" на /messages/daily (см.
 * DailyMessagingController::send и общий DailyBatchDispatcher, который дальше и делает всю
 * работу что для кнопки, что для этой команды).
 *
 * Зарегистрирована в App\Console\Kernel::schedule() — вызывается планировщиком ежедневно.
 * Ручной прогон по одному каналу: php artisan messages:send-daily --type=sms
 */
class SendDailyMessages extends Command
{
    protected $signature = 'messages:send-daily {--type= : sms|email — прогнать только один канал, по умолчанию оба}';

    protected $description = 'Отправляет накопившуюся дневную очередь SMS и Email (EMSG.EMSG_STATUS = wait)';

    public function handle(DailyBatchDispatcher $dispatcher): int
    {
        $only = $this->option('type');
        $types = $only ? [constant(MessageTypeEnum::class.'::'.$only)] : MessageTypeEnum::cases();

        foreach ($types as $type) {
            $result = $dispatcher->dispatch($type);

            $this->info("{$type->label()}: status={$result['status']}, отправлено={$result['sent']}, {$result['reason']}");
        }

        return self::SUCCESS;
    }
}
