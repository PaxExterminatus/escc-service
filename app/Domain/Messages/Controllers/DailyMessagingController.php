<?php

namespace App\Domain\Messages\Controllers;

use App\Domain\Messages\DataService\DailyMessagesDataService;
use App\Domain\Messages\Enums\MessageTypeEnum;
use App\Domain\Messages\Requests\DailyMessagesRequest;
use App\Domain\Messages\Resources\DailyMessageCollection;
use App\Domain\Messages\Services\DailyBatchDispatcher;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Daily Messaging
 * @tags Messaging, SMS, Emailing
 */
class DailyMessagingController extends Controller
{
    /**
     * Daily: get list
     * @example /api/messages/daily/sms
     * @example /api/messages/daily/email
     */
    public function index(DailyMessagesRequest $request): Responsable
    {
        $messages = $this->repository($request)->get();
        return DailyMessageCollection::make($messages);
    }

    /**
     * Daily: send — та же логика, что и у планировщика (см. App\Console\Commands\SendDailyMessages
     * и DailyBatchDispatcher), только по нажатию кнопки, а не по расписанию.
     * @example /api/messages/daily/sms/send
     * @example /api/messages/daily/email/send
     */
    public function send(DailyMessagesRequest $request, DailyBatchDispatcher $dispatcher): JsonResponse
    {
        // $request->type — имя кейса enum'а ('sms'/'email', см. DailyMessagesRequest), не его
        // числовое значение, поэтому constant(), а не from()/tryFrom().
        $type = constant(MessageTypeEnum::class.'::'.$request->type);

        return response()->json(['response' => $dispatcher->dispatch($type)]);
    }

    /**
     * Daily: get list as txt file
     * @example /api/messages/daily/sms/txt
     * @example /api/messages/daily/email/txt
     */
    public function txt(DailyMessagesRequest $request): Response|Application|ResponseFactory
    {
        $messages = $this->repository($request)->get();

        $content = "Phone number\t1\r\n";

        foreach ($messages as $message)
        {
            $content = $content . "$message->address\t$message->body\r\n";
        }

        $filename = $this->senderName();

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-store, no-cache',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }

    protected function repository(DailyMessagesRequest $request): DailyMessagesDataService
    {
       return DailyMessagesDataService::make()->setType($request->type)->setRange($request->from, $request->to);
    }

    protected function senderName(): string
    {
        return env('MTS_API_ALFA_NAME');
    }
}
