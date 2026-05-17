<?php

namespace App\Domain\Messages\Services\DataManagement;

use App\Domain\Messages\Models\DailyMessage;
use App\Domain\Messages\Models\ElectronicMessage;
use Illuminate\Support\Collection;

class DailyMessagingUpdateStatusService
{
    /**
     * @param DailyMessage[]|Collection $messages
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
