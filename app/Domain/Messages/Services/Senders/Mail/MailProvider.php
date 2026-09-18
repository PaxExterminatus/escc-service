<?php

namespace App\Domain\Messages\Services\Senders\Mail;

use App\Domain\Messages\Services\Senders\MessagingProviderInterface;
use Illuminate\Support\Facades\Mail;
use Throwable;

class MailProvider implements MessagingProviderInterface
{
    public static function make(): static
    {
        return new static;
    }

    /**
     * Письмо с html-телом (его собирает EmailComposer) и опциональным вложением — основной
     * способ отправки: тело письма в системе всегда проходит через обёртку и является html.
     *
     * @return array{status: int, reason: string}
     */
    public function sendHtml(string $to, string $subject, string $html, ?string $attachmentPath = null, ?string $attachmentName = null): array
    {
        return $this->dispatch(function () use ($to, $subject, $html, $attachmentPath, $attachmentName) {
            Mail::html($html, function ($message) use ($to, $subject, $attachmentPath, $attachmentName) {
                $message->to($to)->subject($subject);

                if ($attachmentPath) {
                    $message->attach($attachmentPath, ['as' => $attachmentName ?? basename($attachmentPath)]);
                }
            });
        });
    }

    /**
     * Письмо голым текстом, без вёрстки — для служебных рассылок, где html не нужен.
     *
     * @return array{status: int, reason: string}
     */
    public function sendOne(string $to, string $subject, string $body): array
    {
        return $this->dispatch(function () use ($to, $subject, $body) {
            Mail::raw($body, function ($message) use ($to, $subject) {
                $message->to($to)->subject($subject);
            });
        });
    }

    /**
     * Общий обработчик: почтовый сбой не должен ронять запрос — вызывающий код решает по
     * status, что показать оператору.
     *
     * @return array{status: int, reason: string}
     */
    protected function dispatch(callable $send): array
    {
        try {
            $send();

            return ['status' => 200, 'reason' => 'OK'];
        } catch (Throwable $e) {
            $reason = iconv('UTF-8', 'UTF-8//IGNORE', $e->getMessage()) ?: 'Mail error';

            return ['status' => 500, 'reason' => $reason];
        }
    }
}
