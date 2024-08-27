<?php

use App\ApiResponse;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\UserController;
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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::get("/app/failed-auth",function(){
    return ApiResponse::error("Failed Authentication",401);
})->name('failed-auth');

Route::get("/users",[UserController::class,"index"]);
Route::get("/users/{user}",[UserController::class,"show"])->middleware(['auth:sanctum']);
Route::put("/users/{user}",[UserController::class,"update"])->middleware(['auth:sanctum']);
Route::delete("/users/{user}",[UserController::class,"destroy"])->middleware(['auth:sanctum']);

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware(['auth:sanctum']);
