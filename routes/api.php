<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\OrderController;



Route::post('/orders', [OrderController::class, 'create']);
Route::get('/orders/{localOrderId}', [OrderController::class, 'getRedeemCode']);


//TODO: to test
//Route::get('/orders-index', [OrderController::class, 'index']);
//Route::get('/orders', [OrderController::class, 'create']); //TODO: ToBeRemoved Later after finished




//TODO: to comment out default route
/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/

