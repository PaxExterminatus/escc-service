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

        $procedure = 'APISAS_EFRONT.EFONLINE';

        DB::executeProcedure($procedure, [
            'cl_code' => $params->clientCode,
            'rq_type' => $params->requestType,
            'rq_code' => $params->requestCode,
            'node_id' => $params->nodeId,
            'lesson_item' => $params->lessonItem,
        ]);

        $data = DB::table('API_EFRONT_DATA')->first('data_lob');

        return response(content: $data->data_lob, headers: [
            'Content-Type' => 'application/xml',
        ]);
    }
}
