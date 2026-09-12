<?php

namespace App\Domain\Messages\Services\Senders\Mail;

use App\Domain\Messages\Services\Senders\MessagingProviderInterface;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailProvider implements MessagingProviderInterface
{
    static function make(): static
    {
        return new static;
    }

    /**
     * @return array{status: int, reason: string}
     */
    function sendOne(string $to, string $subject, string $body): array
    {
        try {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });

            return ['status' => 200, 'reason' => 'OK'];
        } catch (Throwable $e) {
            $reason = iconv('UTF-8', 'UTF-8//IGNORE', $e->getMessage()) ?: 'Mail error';

            return ['status' => 500, 'reason' => $reason];
        }
    }
}
