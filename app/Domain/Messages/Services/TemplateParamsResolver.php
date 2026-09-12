<?php

namespace App\Domain\Messages\Services;

use App\Domain\App\Profile\Models\Profile;
use Illuminate\Support\Facades\DB;
use Throwable;

class TemplateParamsResolver
{
    public function resolve(int $clientId): array
    {
        $params = [];

        $clientCode = Profile::where('client_id', $clientId)->value('client_code');

        if ($clientCode !== null) {
            $params['client_code'] = $clientCode;
        }

        try {
            $amount = DB::connection('oracle')->selectOne(
                'SELECT API_SERVICE_ACCOUNT.ACCOUNT_CLIENT_TOTAL_DEB(:id) AS DEB FROM DUAL',
                ['id' => $clientId]
            )->deb;

            $params['amount'] = number_format((float) $amount, 2, '.', '');
        } catch (Throwable $e) {
            // debt figure unavailable — leave {amount} unresolved rather than fail the whole preview
        }

        return $params;
    }
}
