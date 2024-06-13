<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {
    Route::get('index',[AuthController::class, 'index']);
    Route::post('login',[AuthController::class, 'login']);
    Route::post('register',[AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class,'me']);
    Route::post('update/{id}', [AuthController::class, 'update']);
});

Route::group([
    'middleware' => 'auth:api',
], function(){
    Route::group(['prefix' => 'post'], function(){
        Route::get('posts', [PostController::class, 'index']);
        Route::post('store', [PostController::class,  'store']);
        Route::get('show/{id}', [PostController::class,  'show']);
        Route::post('update/{id}', [PostController::class,  'update']);
        Route::post('distroy/{id}', [PostController::class,  'distroy']);
    });
    Route::group(['prefix' => 'like'],function(){
        Route::get('getLike/{id}', [LikeController::class, 'index']);
        Route::post('toggleLike/{id}', [LikeController::class, 'toggleLike']);
    });
    Route::group(['prefix' => 'comment'], function(){
        Route::get('getCommentByPost/{id}', [CommentController::class, 'index']);
        Route::post('commentByPost/{id}', [CommentController::class, 'store']);
        Route::post('updateComment/{id}', [CommentController::class, 'update']);
        Route::post('delete/{id}', [CommentController::class, 'destroy']);
    });
});

