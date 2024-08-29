<?php

namespace App\Http\Controllers\Auth;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator;

class NewPasswordController extends Controller
{
    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
     /**
     * @OA\Post(
     *      path="/api/reset-password",
     *      tags={"Auth"},
     *      summary="Reset Password",
     *      description="Reset user Password",
     *       @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(
    *                  type="object",
    *                  @OA\Property(
    *                      property="token",
    *                      type="string",
    *                      example="6877a453f843f88c025e2b4fe5f543ea1ce5d823a3bb132605c210d4f0d81d04"
    *                  ),
    *                  @OA\Property(
    *                      property="email",
    *                      type="string",
    *                      example="john.doe@example.com"
    *                  ),
    *                  @OA\Property(
    *                      property="password",
    *                      type="password",
    *                      example="****"
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

    public function store(Request $request): JsonResponse
    {
        try{

            $request->validate([
                'token' => ['required'],
                'email' => ['required', 'email','exists:users,email'],
                'password' => ['required','string','max:255','min:4'],
                // 'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
    
            // Here we will attempt to reset the user's password. If it is successful we
            // will update the password on an actual user model and persist it to the
            // database. Otherwise we will parse the error and return the response.
            $status = Password::reset(
                $request->only('email', 'password', 'token'),
                // $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user) use ($request) {
                    $user->forceFill([
                        'password' => Hash::make($request->password),
                        'remember_token' => Str::random(60),
                    ])->save();
    
                    event(new PasswordReset($user));
                }
            );
    
            if ($status != Password::PASSWORD_RESET) {
                throw ValidationException::withMessages([
                    'email' => [__($status)],
                ]);
            }
    
            // return response()->json(['status' => __($status)]);
            return ApiResponse::success(['status' => __($status)]);
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }
}
