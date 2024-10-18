<?php

use App\ApiResponse;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CodeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TypePlaceController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckEventNotification;
use App\Http\Middleware\CheckUpdateMiddleware;
use App\Http\Middleware\IsAdministratorMiddleware;
use App\Http\Middleware\IsOrganiserMiddleware;
use App\Models\TypePlace;
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
Route::post('/register', [RegisteredUserController::class, 'store'])->middleware([CheckUpdateMiddleware::class]);
Route::post('/registerAdmin', [RegisteredUserController::class, 'storeAdmin'])->middleware([CheckUpdateMiddleware::class,"auth:sanctum",IsAdministratorMiddleware::class]);
Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware([CheckUpdateMiddleware::class]);
Route::post('/loginAdmin', [AuthenticatedSessionController::class, 'storeAdmin'])->middleware([CheckUpdateMiddleware::class]);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware(['auth:sanctum',CheckUpdateMiddleware::class]);
Route::get("/app/failed-auth",function(){
    return ApiResponse::error("Failed Authentication",401);
})->name('failed-auth');

// ------- email
Route::post("send-verification-mail",[EmailVerificationNotificationController::class,'store'])->middleware(["auth:sanctum"]);
Route::get("verify-email/{id}/{hash}",[VerifyEmailController::class,"verify"])->name("verification.verify");

Route::post("/forgot-password",[PasswordResetLinkController::class,"store"]);
Route::post("/reset-password",[NewPasswordController::class,"store"]);

Route::get("/email-verified",function(){
    return ApiResponse::success([],"You're email is now verified");
})->name("home");
Route::get("/email-already-verified",function(){
    return ApiResponse::success([],"You're email is already verified");
})->name("already-verified");


//USERS 
Route::get("/users",[UserController::class,"index"]) ;
Route::get("/users/{user}",[UserController::class,"show"]) ;
Route::put("/users/{user}",[UserController::class,"update"])->middleware(['auth:sanctum']);
Route::delete("/users/{user}",[UserController::class,"destroy"])->middleware(['auth:sanctum']);
Route::get('/profile', [UserController::class,"profile"])->middleware(['auth:sanctum',CheckUpdateMiddleware::class]);
Route::get("my-notifications",[UserController::class,"notifications"])->middleware(["auth:sanctum"]);
Route::get("my-tickets",[TicketController::class,"userTickets"])->middleware(["auth:sanctum"]);


// REDUCTION CODE
Route::post("/codes",[CodeController::class,"CreateCode"])->middleware(["auth:sanctum"]);
Route::get("/codes",[CodeController::class,"index"])->middleware(["auth:sanctum"]);
Route::get("/codes/{code}",[CodeController::class,"show"])->middleware(["auth:sanctum"]);
Route::put("/codes/{code}",[CodeController::class,"update"])->middleware(["auth:sanctum"]);
// Route::delete("/codes/{code}",[CodeController::class,"destroy"])->middleware(["auth:sanctum"]);
Route::post("/generate-code",[CodeController::class,"GenerateRandomUniqueCode"])->middleware(["auth:sanctum"]);


//TAGS
Route::resource("tags",TagController::class)->except(["index","show"])->middleware(["auth:sanctum"]);
Route::get("tags",[TagController::class,"index"])->middleware(CheckUpdateMiddleware::class);
Route::get("tags/{id}",[TagController::class,"show"]) ;


//EVENTS
Route::resource("events",EventController::class)->middleware(["auth:sanctum"])->except(["create","edit","update","index","show"]);
Route::get("events",[EventController::class,"index"]) ;
Route::get("events-all",[EventController::class,"all"])->middleware("auth:sanctum");
Route::get("events/{id}",[EventController::class,"show"]) ;
Route::post("events/{id}",[EventController::class,"update"])->middleware("auth:sanctum");
Route::post("events/{id}/unapprouval",[EventController::class,"unapproval"])->middleware(["auth:sanctum",IsAdministratorMiddleware::class]);
Route::get("/search-event",[EventController::class,"search"]) ;
Route::get("/grouped-event",[EventController::class,"grouped"]) ;
Route::get("/search-event-price",[EventController::class,"searchPrice"]) ;
Route::get("/search-event-text",[EventController::class,"searchText"]) ;
Route::get("/finished-events",[EventController::class,"finished"]) ;
Route::get("/created-events",[EventController::class,"created"]) ;
Route::get("/famous-events",[EventController::class,"famousEvent"]) ;
Route::post("/events/{id}/publish",[EventController::class,"publish"])->middleware(["auth:sanctum"]);    
Route::get("/events/{id}/tickets",[TicketController::class,"eventTickets"])->middleware(["auth:sanctum"]);


//Type events (vip, normale)
Route::post("/events/{id}/add-type-place",[EventController::class,"addTypePlace"])->middleware(["auth:sanctum"]);
Route::post("/events/{id}/add-code-promo",[EventController::class,"addCodePromo"])->middleware(["auth:sanctum"]);
Route::get("/event-type-place",[TypePlaceController::class,"index"]) ;
Route::get("/event-type-place/{id}",[TypePlaceController::class,"show"]) ;
Route::put("/event-type-place/{id}",[TypePlaceController::class,"update"])->middleware(["auth:sanctum"]);
Route::delete("/event-type-place/{id}",[TypePlaceController::class,"destroy"])->middleware(["auth:sanctum"]);


//CART
// Get user cart
Route::get("/cart",[CartController::class,"getUserCart"])->middleware("auth:sanctum");
// Add items to cart
Route::post("/cart/add",[CartController::class,"store"])->middleware(["auth:sanctum",CheckUpdateMiddleware::class]);
// Update items in cart
Route::put("/cart/update",[CartController::class,"update"])->middleware(["auth:sanctum",CheckUpdateMiddleware::class]);
// Clear all items from cart
Route::delete("/cart/clear",[CartController::class,"clear"])->middleware(["auth:sanctum",CheckUpdateMiddleware::class]);
// Remove specific item from cart
Route::delete("/cart/remove/item",[CartController::class,"removeItem"])->middleware(["auth:sanctum",CheckUpdateMiddleware::class]);
// Confirm and pay the cart
Route::post("/cart/pay",[CartController::class,"pay"])->middleware(["auth:sanctum",CheckUpdateMiddleware::class]);
Route::post("/cart/evaluation",[CartController::class,"evaluate"])->middleware("auth:sanctum");
Route::get("/pay/history",[CartController::class,"payHistory"])->middleware(["auth:sanctum"]);


//Test 
// Route::get("/create-notification",[TestController::class,"CreateNotification"])->middleware("auth:sanctum");
Route::get("/test/get-reference/{id}",[TestController::class,"reference"])->middleware("auth:sanctum");


//dashboard

Route::get("/dashboard/resume",[DashboardController::class,"getResume"])->middleware(["auth:sanctum" ]);
Route::get("/dashboard/sales",[DashboardController::class,"getGetSales"])->middleware(["auth:sanctum" ]);
Route::get("/dashboard/year-stat/{year}",[DashboardController::class,"getStatYears"])->middleware(["auth:sanctum" ]);
