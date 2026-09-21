<?php

namespace App\Domain\Messages\DataService;

use App\Base\Repositories\JsonReader;
use App\Domain\Messages\Enums\MessageDispatchStatusEnum;
use App\Domain\Messages\Enums\MessageTypeEnum;
use App\Domain\Messages\Models\DailyMessage;
use App\Domain\Messages\Models\ElectronicMessage;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class DailyMessagesDataService
{
    static function make(): static
    {
        return new static;
    }

    protected string $type;
    protected ?string $from = null;
    protected ?string $to = null;

    public function setType(string $type): static
    {
        $this->type = Str::lower($type);
        return $this;
    }

    /**
     * Границы по EMSG_DATE, обе включительно; null с обеих сторон — без ограничения по дате
     * (все ещё неотправленные сообщения, независимо от того, на какой день они поставлены).
     */
    public function setRange(?string $from, ?string $to): static
    {
        $this->from = $from;
        $this->to = $to;
        return $this;
    }

    /**
     * Раньше SMS читались через легаси-представление API_MESSAGES_SMS_DAILY (см. DailyMessage) —
     * оно жёстко фильтрует по TRUNC(EMSG_DATE) <= TRUNC(SYSDATE) и не даёт выбрать период,
     * поэтому теперь оба канала читаются напрямую из EMSG и приводятся к одной форме
     * {id, type, address, body}, которую ожидают DailyMessageResource/DailyMessageCollection —
     * им всё равно, Eloquent-модель это или обычный объект, лишь бы нужные свойства были.
     */
    public function get(): Collection
    {
        if (USE_DEVELOPMENT_REPOSITORY_FOR_ALL || USE_DEVELOPMENT_REPOSITORY_FOR_DAILY_MESSAGES)
        {
            $messages = collect();

            foreach (JsonReader::make()->read('api.messages.daily.sms')['messages'] as $message)
            {
                $model = (new DailyMessage)->fill($message);
                $messages->push($model);
            }

            return $messages;
        }

        $type = constant(MessageTypeEnum::class.'::'.$this->type);

        return ElectronicMessage::where('emsg_type', $type->value)
            ->where('emsg_status', MessageDispatchStatusEnum::wait->value)
            ->when($this->from, fn ($query) => $query->whereDate('emsg_date', '>=', $this->from))
            ->when($this->to, fn ($query) => $query->whereDate('emsg_date', '<=', $this->to))
            ->orderBy('emsg')
            ->get()
            ->map(fn (ElectronicMessage $message) => (object) [
                'id' => $message->emsg,
                'type' => $type->label(),
                'address' => $message->emsg_address,
                'body' => $message->emsg_body,
            ]);
    }
}
