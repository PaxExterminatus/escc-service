<?php

namespace App\Domain\Cabinet\Controllers;

use App\Domain\Cabinet\Models\Client;
use App\Domain\Cabinet\Resources\ClientResource;
use App\Http\Controllers\Controller;

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
    public function show(int $id): ClientResource
    {
        $client = Client::where('id', $id)->with('courses.lessons', 'courses.categories')->first();

        return ClientResource::make($client);
    }
}
