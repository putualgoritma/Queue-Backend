<?php

use Illuminate\Http\Request;
use Modules\Queue\Http\Controllers\Api\CountersController;
use Modules\Queue\Http\Controllers\Api\QueueController;
use Modules\Queue\Http\Controllers\Api\ServiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/queue', function (Request $request) {
    return $request->user();
});
Route::get('queue', [QueueController::class, 'index']);
Route::post('queue/store', [QueueController::class, 'store']);
Route::get('queue/next', [QueueController::class, 'next']);
Route::get('queue/back', [QueueController::class, 'back']);
Route::get('queue/restart', [QueueController::class, 'restart']);
Route::post('queue/process', [QueueController::class, 'process']);
Route::post('queue/done', [QueueController::class, 'done']);
Route::get('queue/call', [QueueController::class, 'call']);

Route::get('queue/counters', [CountersController::class, 'index']);
Route::get('queue/counters/{id}', [CountersController::class, 'loket']);
Route::get('queue/services', [ServiceController::class, 'index']);
