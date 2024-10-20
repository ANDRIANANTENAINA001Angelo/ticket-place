<?php

namespace App\Http\Controllers\Auth;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        try{
            return Socialite::driver('google')->stateless()->redirect();
        }
        catch (Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }

    // Callback Google
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Rechercher l'utilisateur ou le créer
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'fname' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]
            );

            // Générer un token JWT ou API pour Next.js
            $token = $user->createToken(Str::random(8))->plainTextToken;

            // return response()->json(['token' => $token]);
            return ApiResponse::success(['token' => $token],"auth google success");
        } catch (\Exception $e) {
            return ApiResponse::error("server error",500,$e->getMessage());
            // return response()->json(['error' => 'Authentication failed'], 500);
        }
    }
    
}
