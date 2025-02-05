<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{AuthController, UserController};

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
    'auth:sanctum',
]], function () {
    Route::get('user-me', [AuthController::class, 'me']);
    Route::post('update-profil', [AuthController::class, 'updateProfil']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::delete('delete-account', [AuthController::class, 'deleteAccount']);
    Route::get('logout', [AuthController::class, 'logout']);

    Route::group(['prefix' => 'manage-users'], function (){
        Route::post('newUser', [UserController::class, 'newUser']);
        Route::get('detail/{userKey}', [UserController::class, 'showDetail']);
        Route::post('update/{userKey}', [UserController::class, 'updateUser']);
        Route::delete('delete/{userKey}', [UserController::class, 'destroy']);
    });

    Route::group(['prefix' => 'search'], function (){
        Route::post('name', [UserController::class, 'searchName']);
        Route::post('nim', [UserController::class, 'searchNim']);
        Route::post('ymd', [UserController::class, 'searchYMD']);
    });
});
