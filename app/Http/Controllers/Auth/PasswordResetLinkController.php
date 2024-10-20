<?php

namespace App\Http\Controllers\Auth;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    /**
     * @OA\Post(
     *      path="/api/forgot-password",
     *      tags={"Auth"},
     *      summary="Send Link reset Password",
     *      description="Send Link reset to Password",
     *       @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(
    *                  type="object",
    *                  @OA\Property(
    *                      property="email",
    *                      type="string",
    *                      example="john.doe@example.com"
    *                  )
    *              )
    *          )
    *      ),
     *          @OA\Response(
     *              response=200,
     *              description="successful operation"
     *          ),
     *          @OA\Response(
     *              response=401,
     *              description="action unauthorized"
     *          ),
     *          @OA\Response(
     *              response=403,
     *              description="action forbiden"
     *          ),
     *          @OA\Response(
     *              response=404,
     *              description="aucun user trouvé"
     *          ),
     *          @OA\Response(
     *              response=400,
     *              description="donné incomplet"
     *          ),
     *          @OA\Response(
     *              response=500,
     *              description="erreur serveur"
     *          ),
     *)  
     */
    // public function store(Request $request): JsonResponse
    // {

    //     try{

    //         $request->validate([
    //             'email' => ['required', 'email',"exists:users,email"],
    //         ]);
    
    //         // We will send the password reset link to this user. Once we have attempted
    //         // to send the link, we will examine the response then see the message we
    //         // need to show to the user. Finally, we'll send out a proper response.
    //         $status = Password::sendResetLink(
    //             $request->only('email')
    //         );
    
    //         if ($status != Password::RESET_LINK_SENT) {
    //             throw ValidationException::withMessages([
    //                 'email' => [__($status)],
    //             ]);
    //         }
    
    //         // return response()->json(['status' => __($status)]);
    //         return ApiResponse::success(['status' => __($status)],"Lien reset password send");
    //     }
    //     catch(Exception $e){
    //         return ApiResponse::error("Server error",500,$e->getMessage());
    //     }
    // }


    public function store(Request $request): JsonResponse
    {
        try {
            // Valider l'e-mail
            $request->validate([
                'email' => ['required', 'email', 'exists:users,email'],
            ]);

            // Trouver l'utilisateur avec cet e-mail
            $user = DB::table('users')->where('email', $request->email)->first();
            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['User not found.'],
                ]);
            }

            // Générer le token de reset password
            $token = Str::random(60);

            // Supprimer d'anciens tokens avant d'ajouter le nouveau
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();

            // Enregistrer le nouveau token dans la table password_resets
            DB::table('password_reset_tokens')->insert([
                'email' => $user->email,
                'token' => bcrypt($token),
                'created_at' => Carbon::now(),
            ]);

            // Construire l'URL personnalisée vers Next.js frontend
            $frontendUrl = env('FRONTEND_URL');
            $resetUrl = "{$frontendUrl}/reset-password/{$token}?email={$user->email}";

            // Envoyer l'e-mail avec le lien vers le frontend
            Mail::send('emails.password-reset', ['resetUrl' => $resetUrl], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Reset Password Notification');
            });

            return ApiResponse::success(['status' => 'Reset link sent successfully'], "Lien reset password send");
        } catch (Exception $e) {
            return ApiResponse::error("Server error", 500, $e->getMessage());
        }
    }

}
