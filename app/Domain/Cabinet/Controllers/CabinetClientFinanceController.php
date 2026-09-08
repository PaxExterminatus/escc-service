<?php

namespace App\Domain\Cabinet\Controllers;

use App\Domain\Cabinet\Models\ClientFinanceHistory;
use App\Domain\Cabinet\Resources\ClientFinanceHistoryResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @tags Cabinet
 */
class CabinetClientFinanceController extends Controller
{
    /**
     * Client finance history
     *
     * Charge and payment history for a client: what was charged and what was paid, and when.
     */
    public function history(int $id): AnonymousResourceCollection
    {
        $history = ClientFinanceHistory::where('client_id', $id)
            ->orderByDesc('operation_date')
            ->get();

        return ClientFinanceHistoryResource::collection($history);
    }
}
