<?php

namespace App\Http\Controllers\API;

use App\Models\Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $client = Client::where('id', $id)->with('courses.lessons');

        return response()->json([
            'client' => $client->get(),
        ]);
    }
}
