<?php

namespace App\Domain\Messages\Services\DataManagement;

use App\Domain\Messages\Models\ElectronicMessage;
use App\Domain\Messages\Models\DailyMessageView;
use Illuminate\Support\Collection;

class DailyMessagingUpdateStatusService
{
    /**
     * @param DailyMessageView[]|Collection $messages
     * @return void
     */
    function massSendingSuccess(Collection $messages): void
    {
        $ids = $messages->pluck('id');
        $eMessages = ElectronicMessage::whereIn('emsg', $ids)->get();

        foreach ($eMessages as $eMessage)
        {
            $eMessage->emsg_status = ElectronicMessage::EMSG_STATUS_TRY;
            $eMessage->save();
        }
    }
}
