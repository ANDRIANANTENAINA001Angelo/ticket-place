<?php

namespace App\Http\Middleware;

use App\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdministratorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User $user description */
        $user = Auth::user();
        if(!$user){
            return ApiResponse::error("This action need authentication.",401);
        }

        if($user->IsAdministrator()){
            return $next($request);
        }
        else{
            return ApiResponse::error("This action is deserve to Admin only.",403);
        }
    }
}
