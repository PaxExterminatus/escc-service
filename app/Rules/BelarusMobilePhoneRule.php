<?php

namespace App\Rules;

use App\Domain\Messages\ValueObjects\BelarusMobilePhone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BelarusMobilePhoneRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!BelarusMobilePhone::isValid($value)) {
            $fail('Номер телефона должен быть в формате 375XXXXXXXXX.');
        }
    }
}
