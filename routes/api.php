<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthContoller;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\FeedbackController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => 'auth:sanctum' ,'prefix' => 'products'], function () {
    Route::apiResource('/', ProductController::class);
    Route::apiResource('/feedback', FeedbackController::class);
});

Route::post('/user/register', [AuthContoller::class, 'register']);
Route::post('/user/login', [AuthContoller::class, 'login']);
