<?php

namespace App\Http\Controllers\Auth;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Validator;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

     /**
     * @OA\Post(
     *      path="/api/register",
     *      tags={"Auth"},
     *      summary="Register User",
     *      description="enregistre user",
     *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(
    *                  type="object",
    *                  @OA\Property(
    *                      property="fname",
    *                      type="string",
    *                      example="John"
    *                  ),
    *                  @OA\Property(
    *                      property="lname",
    *                      type="string",
    *                      example="Doe"
    *                  ),
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
    *                  ),
    *                  @OA\Property(
    *                      property="type",
    *                      type="string",
    *                      example="customer"
    *                  )
    *              )
    *          )
    *      ),
     *          @OA\Response(
     *              response=200,
     *              description="successful operation"
     *          ),
     *          @OA\Response(
     *              response=400,
     *              description="donné incomplet"
     *          ),
     *          @OA\Response(
     *              response=500,
     *              description="erreur serveur"
     *          )
     *)  
     */
    public function store(Request $request)
    {
        try{
            $request->validate([
                'fname' => ['required', 'string', 'max:255'],
                'lname' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'max:255','min:4'],
                'phone' => ['nullable', 'max:10','min:10',"string",'unique:'.User::class],
                'type' => ['nullable','string',Rule::in(["customer", "organiser", "administrator"])],
            ]);
            
            $user = User::create([
                'fname' => $request->fname,
                'lname' => $request->lname,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'type'=> $request->type
            ]);
    
            // create user's cart (first)
            // if($request->type =="customer"){
            // }
                $cart = Cart::create([
                    "status"=>"created",
                    "montant"=>0,
                    "user_id"=>$user->id
                ]);

            return ApiResponse::success($user,"User registered");
        }
        catch(Exception $e){
            return ApiResponse::error("Server error",500,$e->getMessage());
        }
        
    }
}
