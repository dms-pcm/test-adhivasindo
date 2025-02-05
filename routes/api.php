<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('user/login', [AuthController::class, 'login']);

Route::group(['middleware' => [
    'auth:sanctum'
]], function () {
    Route::get('user-me', [AuthController::class, 'me']);
    Route::get('logout', [AuthController::class, 'logout']);
});
