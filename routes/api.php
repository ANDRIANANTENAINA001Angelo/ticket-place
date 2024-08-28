<?php

use App\ApiResponse;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CodeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\IsOrganiserMiddleware;
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

// AUTH
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware(['auth:sanctum']);
Route::get("/app/failed-auth",function(){
    return ApiResponse::error("Failed Authentication",401);
})->name('failed-auth');


//USERS 
Route::get("/users",[UserController::class,"index"]);
Route::get("/users/{user}",[UserController::class,"show"]);
Route::put("/users/{user}",[UserController::class,"update"])->middleware(['auth:sanctum']);
Route::delete("/users/{user}",[UserController::class,"destroy"])->middleware(['auth:sanctum']);
Route::get('/profile', [UserController::class,"profile"])->middleware(['auth:sanctum']);


// REDUCTION CODE
Route::post("/codes",[CodeController::class,"store"])->middleware(["auth:sanctum",IsOrganiserMiddleware::class]);
Route::get("/codes",[CodeController::class,"index"])->middleware(["auth:sanctum"]);
Route::get("/codes/{code}",[CodeController::class,"show"])->middleware(["auth:sanctum"]);
Route::put("/codes/{code}",[CodeController::class,"update"])->middleware(["auth:sanctum",IsOrganiserMiddleware::class]);
Route::delete("/codes/{code}",[CodeController::class,"destroy"])->middleware(["auth:sanctum",IsOrganiserMiddleware::class]);
Route::post("/generate-code",[CodeController::class,"GenerateRandomUniqueCode"])->middleware(["auth:sanctum",IsOrganiserMiddleware::class]);


//TAGS
Route::resource("tags",TagController::class)->middleware(["auth:sanctum",IsOrganiserMiddleware::class]);


//EVENTS
Route::resource("events",EventController::class)->middleware(["auth:sanctum",IsOrganiserMiddleware::class]);


