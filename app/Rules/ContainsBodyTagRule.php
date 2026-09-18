<?php

namespace App\Rules;

use App\Domain\Templates\Services\EmailComposer;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Обёртка письма обязана содержать спецтег {BODY} — именно на его место встаёт тело письма.
 * Без него EmailComposer вынужден приклеивать тело после футера: письмо уходит внешне целым,
 * и дефект обнаруживается только по жалобе получателя.
 */
class ContainsBodyTagRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !str_contains($value, EmailComposer::BODY_TAG)) {
            $fail('Обёртка письма должна содержать спецтег ' . EmailComposer::BODY_TAG . ' — на его место подставляется тело письма.');
        }
    }
}
