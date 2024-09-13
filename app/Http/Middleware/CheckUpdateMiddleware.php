<?php

namespace App\Http\Middleware;

use App\ApiResponse;
use App\Models\Event;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckUpdateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try{
            
            if(Cache::has("last_update")){
                /** @var Carbon $date description */
                $date = Cache::get("last_update");
                // Log::info((string)$date);
                // Log::info((string)$date->addMinutes(1));
                // Log::info((string)now());
                // if($date->addMinutes(1) <= now()){
                if($date->addDays(1) <= now()){
                    $events = Event::where("status","=","published")->get();
                    foreach ($events as $event) {
                        if($event->date < now()->toDateString()){
                            $event->status= "finished";
                            $event->save();
                        }
                    }
                    Log::info("update done");
                    Cache::put("last_update",now());
                }
                else{
                    Log::info("update not needed");
                }
            }
            else{
                Log::info("first update done");
                Cache::add("last_update",now());
            }
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
        
        $response = $next($request);
        return $response;

    }
}
