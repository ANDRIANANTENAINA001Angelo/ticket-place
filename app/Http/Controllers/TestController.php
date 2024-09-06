<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\TypePlace;
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

    public function reference(Request $request, string $id){
        try{
            $type_place= TypePlace::find($id);
            if(!$type_place){
                return ApiResponse::error("type place not found",404);
            }
    
            $reference= $type_place->generateReference();
    
            return ApiResponse::success(["reference"=>$reference]);
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }
}
