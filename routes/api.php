<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth:sanctum'])->group(function () {

    //Auth
    Route::controller(AuthController::class)->group(function () {
        Route::get('user', 'getUser');
        Route::post('logout', 'logout');
    });

    //Posts
    Route::controller(PostController::class)->group(function () {
        Route::get('/posts', 'index');
        Route::get('/posts/{id}', 'show');
        Route::post('/posts', 'store');
        Route::patch('/posts/{id}', 'update');
        Route::delete('/posts/{id}', 'destroy');
        Route::get('/search/{search}', 'searchPost');
    });
});

Route::controller(AuthController::class)->group(function () {

    Route::post('login', 'login');
    Route::post('register', 'store');
});
