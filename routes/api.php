<?php

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::get('/user', function (Request $request) {
    /**
     * @var Client $client;
     */
    $client = Client::whereId(395)->with('courses.lessons');

    return response()->json([
        'client' => $client->get(),
    ]);
});
