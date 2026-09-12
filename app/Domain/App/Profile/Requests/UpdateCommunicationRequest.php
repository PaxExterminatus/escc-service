<?php

namespace App\Domain\App\Profile\Requests;

use App\Base\ApplicationProgrammingInterfaceRequest;
use App\Rules\BelarusMobilePhoneRule;
use App\Rules\EmailAddressRule;

/**
 * @property string|null phone
 * @property bool sms_allowed
 * @property string|null email
 * @property bool email_allowed
 */
class UpdateCommunicationRequest extends ApplicationProgrammingInterfaceRequest
{
    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'max:100', new BelarusMobilePhoneRule],
            'sms_allowed' => 'boolean',
            'email' => ['nullable', 'string', 'max:255', new EmailAddressRule],
            'email_allowed' => 'boolean',
        ];
    }
}
