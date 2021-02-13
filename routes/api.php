<?php

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::get('/user', function (Request $request) {
    //$res = DB::table('API_CLIENT')->paginate();
    $res = Client::find(395);

    return response()->json([
        'client' => $res,
    ]);
});
