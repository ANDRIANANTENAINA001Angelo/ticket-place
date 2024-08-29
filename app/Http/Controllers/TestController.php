<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Notifications\TestNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestController extends Controller
{
    public function CreateNotification(Request $request){
        try{

            /** @user User $user description */
            $user = Auth::user();
            $user->notify(new TestNotification(["content"=>"event content"]));
            return ApiResponse::success([],"Notification database sent");
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }
}
