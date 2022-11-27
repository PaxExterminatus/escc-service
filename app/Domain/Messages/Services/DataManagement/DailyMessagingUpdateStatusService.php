<?php

namespace App\Domain\Messages\Services\DataManagement;

use App\Domain\Messages\Models\ElectronicMessage;
use App\Domain\Messages\Models\DailyMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DailyMessagingUpdateStatusService
{
    /**
     * @param DailyMessage[]|Collection $messages
     * @return void
     */
    function massSendingSuccess(Collection $messages): void
    {
        foreach ($messages as $message)
        {
            $status = ElectronicMessage::EMSG_STATUS_TRY;
            DB::update("UPDATE EMSG SET EMSG_STATUS = $status WHERE EMSG = $message->id");
        }
    }
}
