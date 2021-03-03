<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EFrontController extends Controller
{
    public function data(): JsonResponse
    {
        return response()->json([
            1
        ]);
    }
}
