<?php

namespace App\Domain\Cabinet\Controllers;

use App\Domain\Cabinet\Models\Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * @tags Cabinet
 */
class CabinetClientController extends Controller
{
    /**
     * Client data
     *
     * Information about client courses and lessons
     */
    public function show(int $id): JsonResponse
    {
        $client = Client::where('id', $id)->with('courses.lessons', 'courses.categories');

        return response()->json([
            'client' => $client->first(),
        ]);
    }
}
