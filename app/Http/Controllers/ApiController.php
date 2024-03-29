<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
   // use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function success(iterable $data = [], string $message = null, int $code = 200, string $redirect = null): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'redirect' => $redirect,
        ] + (array)$data, $code);
    }

    protected function error(iterable $data = [], string $message = null, int $code = 400, string $redirect = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'redirect' => $redirect,
        ] + $data, $code);
    }
}
