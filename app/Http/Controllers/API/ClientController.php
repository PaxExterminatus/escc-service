<?php

namespace App\Http\Controllers\API;

use App\Models\Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $client = Client::where('id', $id)->with('courses.lessons', 'courses.categories');

        return response()->json([
            'client' => $client->first(),
        ]);
    }
}
