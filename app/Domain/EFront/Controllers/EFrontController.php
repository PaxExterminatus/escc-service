<?php

namespace App\Domain\EFront\Controllers;

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
        $procedure = 'APISAS_EFRONT.EFONLINE';

        DB::executeProcedure($procedure, [
            'cl_code' => $request->cl_code,
            'rq_type' => $request->rq_type,
            'rq_code' => $request->rq_code,
            'node_id' => $request->node_id,
            'lesson_item' => $request->lesson_item,
        ]);

        $data = DB::table('API_EFRONT_DATA')
            ->where('client_id', $request->cl_code)
            ->first('data_lob');

        return response(content: $data->data_lob, headers: [
            'Content-Type' => 'application/xml',
        ]);
    }
}
