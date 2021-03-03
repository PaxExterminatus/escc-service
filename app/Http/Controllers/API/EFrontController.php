<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\EFrontDataRequest;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class EFrontController extends Controller
{
    public function data(EFrontDataRequest $request): Response|Application|ResponseFactory
    {
        $params = $request->params();

        $result = DB::selectOne("SELECT APISAS_EFRONT.EFONLINE({$params->clientCode}, {$params->requestType}, {$params->requestCode}, {$params->nodeId}, {$params->lessonItem}) AS value FROM DUAL");

        return response(content: $result->value, headers: [
            'Content-Type' => 'application/xml',
        ]);
    }
}
