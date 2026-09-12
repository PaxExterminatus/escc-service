<?php

namespace App\Rules;

use App\Domain\Messages\ValueObjects\EmailAddress;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailAddressRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!EmailAddress::isValid($value)) {
            $fail('Некорректный адрес email.');
        }
    }
}
