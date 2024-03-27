<?php

namespace App\Domain\Messages\Requests;

use App\Domain\Messages\Enums\MessageTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property string type
 */
class DailyMessagesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => [Rule::enum(MessageTypeEnum::class)],
        ];
    }
}
