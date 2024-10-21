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
            return Socialite::driver('google')
            ->stateless()
            ->redirectUrl(env('GOOGLE_REDIRECT_URI'))
            ->redirect();        
            // return Socialite::driver('google')->stateless()->redirect();
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

            $fullName = $googleUser->getName();  // Récupère le nom complet

            // Séparer le prénom et le nom
            $nameParts = explode(' ', $fullName, 2);  // Divise le nom en deux parties

            $firstName = $nameParts[0];  // Première partie, le prénom
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';  // Deuxième partie, le nom, ou une chaîne vide si pas de nom de famille


            // Rechercher l'utilisateur ou le créer
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'fname' => $lastName,
                    'lname' => $firstName,
                    'google_id' => $googleUser->getId(),
                    'image' => $googleUser->getAvatar(),
                ]
            );

            // Générer un token JWT ou API pour Next.js
            $token = $user->createToken(Str::random(8))->plainTextToken;

            $token = urlencode($token);
            $frontendUrl = env("FRONTEND_URL", "http://localhost:3000");
            
            return redirect("{$frontendUrl}/google-success?token={$token}");            

            // return response()->json(['token' => $token]);
            // return ApiResponse::success(['token' => $token],"auth google success");
        } catch (\Exception $e) {
            return ApiResponse::error("server error",500,$e->getMessage());
            // return response()->json(['error' => 'Authentication failed'], 500);
        }
    }
    
}
