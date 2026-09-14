<?php

namespace App\Domain\App\Profile\Resources;

use App\Domain\Messages\Models\ClientCommunication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\App\Profile\Models\Profile
 */
class ProfileResource extends JsonResource
{
    public static $wrap = 'profile';

    public function toArray(Request $request): array
    {
        $communication = ClientCommunication::where('client_id', $this->client_id)->first();

        return [
            'id' => (int)$this->client_id,
            'name' => $this->client_name,
            'name_middle' => $this->client_middle_name,
            'name_last' => $this->client_last_name,
            'birthday' => $this->client_birthday?->format('d.m.Y'),
            /**
             * @var \App\Enums\SexEnum
             */
            'sex' => $this->client_sex,
            'phone' => $communication?->client_mphone,
            'sms_allowed' => (bool) $communication?->client_smsuse,
            'email' => $communication?->client_email,
            'email_allowed' => (bool) $communication?->subscriber_email_status,
        ];
    }
}
