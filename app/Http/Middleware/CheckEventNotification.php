<?php

namespace App\Http\Middleware;

use App\ApiResponse;
use App\Models\Cart;
use App\Models\Event;
use App\Models\User;
use App\Notifications\AppNotification;
use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class CheckEventNotification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try{
            
            if(Cache::has("last_check_notification")){
                /** @var Carbon $date description */
                $date = Cache::get("last_check_notification");
                
                // if($date->addSeconds(5) <= now()){
                if($date->addDays(1) <= now()){
                        
                    // Log::info("update needed");
                    $events = Event::where("status",Event::STATUS_PUBLISHED)->get();
                    foreach ($events as $event) {
                        // Log::info("one events published");
                        $title= Str::upper($event->titre);
                        $content= "N'oubliez pas ". $event->titre . " après demain ". $event->date . " vers ". $event->heure . " à " . $event->localisation;
                        
                        if ($event->raw_date < now()->addDays(2)) {                            
                            // Log::info("one events in 3 days");
                            $carts= Cart::where("event_id",$event->id)->where("status","purchased")->get();
                            foreach ($carts as $cart){
                                /** @var User $user description */
                                $user = User::find($cart->user_id);
                                // Log::info("one users buy the event");
                                
                                $user->notify(new AppNotification($title,$content));
                                
                            }
                            // Log::info("update done");
                        }
                    }
                    Cache::put("last_check_notification",now());
                }
                else{
                    Log::info("update notif not needed");
                }
            }
            else{
                Log::info("first update done");
                Cache::add("last_check_notification",now());
            }
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
        
        $response = $next($request);
        return $response;
    }
}
