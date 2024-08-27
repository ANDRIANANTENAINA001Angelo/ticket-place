<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * @OA\Get(
     *      path="/api/users",
     *      tags={"Users"},
     *      summary="Get List of users",
     *      description="return list of all users",
     *          @OA\Response(
     *              response=200,
     *              description="successful operation"
     *          ),
     *          @OA\Response(
     *              response=404,
     *              description="aucun résultat trouvé"
     *          )
     *)  
     */
    public function index()
    {
        // return response()->json(User::all());
        return ApiResponse::success(User::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *      path="/api/users/{user_id}",
     *      tags={"Users"},
     *      summary="Get One User",
     *      description="return the users",
     *          @OA\Response(
     *              response=200,
     *              description="successful operation"
     *          ),
     *          @OA\Response(
     *              response=404,
     *              description="aucun user trouvé"
     *          ),
     *          @OA\Response(
     *              response=500,
     *              description="erreur interne serveur"
     *          ),
     *)  
     */
    public function show(string $id)
    {
        $user= User::find($id);
        if(!$user){
            return ApiResponse::error("User nout found",404);
        }
        else{
            return ApiResponse::success($user);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
/**
     * @OA\Put(
     *      path="/api/users/{user_id}",
     *      tags={"Users"},
     *      summary="Update one user",
     *      description="update information of one user",
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
    *                      property="phone",
    *                      type="string",
    *                      example="0345992047"
    *                  ),
    *                  @OA\Property(
    *                      property="type",
    *                      type="string",
    *                      example="administrator"
    *                  ),
    *              )
    *          )
    *      ),
     *          @OA\Response(
     *              response=200,
     *              description="successful operation"
     *          ),
     *          @OA\Response(
     *              response=404,
     *              description="aucun user trouvé"
     *          ),
     *          @OA\Response(
     *              response=400,
     *              description="donné incomplet"
     *          ),
     *)  
     */

    public function update(Request $request, string $id)
    {
        $data =  $request->validate([
            'fname' => ['nullable', 'string', 'max:255'],
            'lname' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['nullable', 'max:255','min:4'],
            'phone' => ['nullable', 'max:10','min:10',"string",'unique:'.User::class],
            'type' => ['nullable','string',Rule::in("customer, organiser, administrator")],
        ]);
        $user = User::find($id);

        if(!$user){
            return ApiResponse::error("User not found",404);
        }
        else{
            try{
                $user->update($data);
                $user->save();

                return ApiResponse::success($user,"User updated!");
            }
            catch(Exception $e){
                return ApiResponse::error("Error updating",400,$e->getMessage());
            }
        }

    }

    /**
     * Remove the specified resource from storage.
     */

     /**
     * @OA\Delete(
     *      path="/api/users/{user_id}",
     *      tags={"Users"},
     *      summary="Delete one user",
     *      description="Delete the user",
     *          @OA\Response(
     *              response=200,
     *              description="successful operation"
     *          ),
     *          @OA\Response(
     *              response=404,
     *              description="aucun user trouvé"
     *          ),
     *          @OA\Response(
     *              response=500,
     *              description="Erreur interne serveur"
     *          )
     *)  
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        if(!$user){
            return ApiResponse::error("User not found!",404);
        }
        else{
            try{
                $user->delete();

                return ApiResponse::success([],"User deleted");
            }
            catch(Exception $e){
                return ApiResponse::error("Error Deleting user",500,$e->getMessage());
            }
        }
    }
}
