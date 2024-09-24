<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    
    public function getResume(){
        /** @var User $user description */
        $user= Auth::user();

        if($user->IsCustomer()){
            return ApiResponse::error("error permission",403,"You can't access this ressource");
        }

        $result=[];
        // total income, count all event, all ticket, all customer paid ticket
        if($user->IsAdministrator()){
            $result["event"]=count(Event::all());

            

        }



    }

}
