<?php

namespace App\Http\Controllers\Auth;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */

     /**
     * @OA\Post(
     *      path="/api/login",
     *      tags={"Auth"},
     *      summary="Loged In user",
     *      description="Connect user",
     *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(
    *                  type="object",
    *                  @OA\Property(
    *                      property="email",
    *                      type="string",
    *                      example="john.doe@example.com"
    *                  ),
    *                  @OA\Property(
    *                      property="password",
    *                      type="string",
    *                      format="password",
    *                      example="password123"
    *                  )
    *              )
    *          )
    *      ),
     *          @OA\Response(
     *              response=200,
     *              description="successful operation"
     *          ),
     *          @OA\Response(
     *              response=404,
     *              description="user nout found"
     *          )
     *)  
     */
    // public function store(LoginRequest $request): Response
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where("email", "=", $data["email"])->limit(1)->get();
        
        if ($user->isEmpty()) {
            return ApiResponse::error("Login failed",404,"User with this email not found.");
        } else {
            $user= $user->first();
            if(Hash::check($data["password"],$user->password)){
                $token = $user->createToken("token");
                
                return ApiResponse::success([
                    'token' => $token->plainTextToken,
                    "user"=>$user
                ],"User connected successfull");       
            }
            else{
                return ApiResponse::error("Login Failed",400,"Password is wrong");
            }
        }
        
    }

    /**
     * Destroy an authenticated session.
     */

     /**
     * @OA\Post(
     *      path="/api/logout",
     *      tags={"Auth"},
     *      summary="Logout user",
     *      description="DéConnecter user",
     *      security={{"bearerAuth": {}}},
     *          @OA\Response(
     *              response=200,
     *              description="successful operation"
     *          ),
     *          @OA\Response(
     *              response=404,
     *              description="user nout found"
     *          ),
     *          @OA\Response(
     *              response=400,
     *              description="donnée incomplète"
     *          )
     *)  
     */
    public function destroy(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success([],"User deconnected !");
    }
}
