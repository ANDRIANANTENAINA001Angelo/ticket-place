<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\FileManip;
use App\Models\Event;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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
     *              response=401,
     *              description="action unauthorized"
     *          ),
     *          @OA\Response(
     *              response=403,
     *              description="action forbiden"
     *          ),
     *          @OA\Response(
     *              response=404,
     *              description="aucun résultat trouvé"
     *          )
     *)  
     */
    public function index()
    {
        try{
            return ApiResponse::success(User::all());
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
        // return response()->json(User::all());
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
     *      @OA\Parameter(
 *          name="user_id",
 *          in="path",
 *          required=true,
 *          @OA\Schema(
 *              type="string"
 *          ),
 *          description="ID of the user to show"
 *      ),
     *     @OA\Response(
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
     *              response=500,
     *              description="erreur interne serveur"
     *          ),
     *)  
     */
    public function show(string $id)
    {
        try{
            $user= User::find($id);
            if(!$user){
                return ApiResponse::error("User nout found",404);
            }
            return ApiResponse::success($user);
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
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
     *      @OA\Parameter(
 *          name="user_id",
 *          in="path",
 *          required=true,
 *          @OA\Schema(
 *              type="string"
 *          ),
 *          description="ID of the user to update"
 *      ),
     *       @OA\RequestBody(
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
     *)  
     */

    public function update(Request $request, string $id)
    {
        
        try{
            $user = User::find($id);

            if(!$user){
                return ApiResponse::error("User not found",404);
            }

            /** @var User $actor_user description */
            $actor_user = Auth::user();
            if(!$actor_user->IsAdministrator() && $actor_user->id != $id ){
                return ApiResponse::error("Erro permission",403,"Only Administrator can update info of other user");
            }

            $data =  $request->validate([
                'fname' => ['nullable', 'string', 'max:255'],
                'lname' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['nullable', 'max:255','min:4'],
                'phone' => ['nullable', 'max:10','min:10',"string",'unique:'.User::class],
                'type' => ['nullable','string',Rule::in("customer", "organiser", "administrator")],
                "image"=>["nullable","file","max:10240"]
            ]);

            if($request->hasFile("image")){
                $data["image"]= $this->saveImage($request);
            }
            
            $user->update($data);
            $user->save();

            return ApiResponse::success($user,"User updated!");
                
        }
        catch(Exception $e){
            return ApiResponse::error("Error updating",400,$e->getMessage());
        }
    }

    private function saveImage(Request $request){
        try{
            $image = $request->file("image");
            $file_name = Str::uuid() . time() . "." . $image->getClientOriginalExtension();
            $image_path = $image->storeAs("/profile", $file_name, "public");
            return $image_path;
        }
        catch(Exception $e){
            return ApiResponse::error("server error",$e->getMessage());
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
     *       @OA\Parameter(
     *              name="user_id",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="string"
     *              ),
     *              description="ID of the user to delete"
     *            ),
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
     *              response=500,
     *              description="Erreur interne serveur"
     *          )
     *)  
     */
    public function destroy(string $id)
    {
        try{

            $user = User::find($id);

            if(!$user){
                return ApiResponse::error("User not found!",404);
            }

            /** @var User $actor_user description */
            $actor_user = Auth::user();
            if(!$actor_user->IsAdministrator() && $actor_user->id != $id ){
                return ApiResponse::error("Error Updating",403,"Only Administrator can delete other user account");
            }

            if(!is_null($user->image)){
                Storage::delete(FileManip::UrlToPath($user->image));
            }

            $user->delete();
            return ApiResponse::success([],"User deleted");
            
        }
        catch(Exception $e){
            return ApiResponse::error("Error Deleting user",500,$e->getMessage());
        }
    }


    /**
     * @OA\Get(
     *      path="/api/profile",
     *      tags={"Users"},
     *      summary="Get profile info",
     *      description="return the current user info",
     *          @OA\Response(
     *              response=200,
     *              description="successful operation"
     *          ),
     *          @OA\Response(
     *              response=404,
     *              description="aucun résultat trouvé"
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
     *              response=500,
     *              description="Erreur serveur"
     *          )
     *)  
     */
    public function profile(Request $request){
        try
        {
            /** @var User $user description */
            $user = Auth::user();
            if($user->IsOrganiser()){
                $user["events"]= Event::where("user_id","=",$user->id)
                                ->with(["type_places","codes"])
                                ->get();
            }

            if(!$user->IsAdministrator()){
                // $user["tickets"]=$user->tickets;
                $user["cart"]=$user->getCart();
            }

            return ApiResponse::success($user,"profile info returned");
        }
        catch(Exception $e){
            return ApiResponse::error("Server error",500,$e->getMessage());
        }
    }


    /**
     * @OA\Get(
     *      path="/api/my-notifications",
     *      tags={"Users"},
     *      summary="Get notifications",
     *      description="return the user's notifications",
     *          @OA\Response(
     *              response=200,
     *              description="successful operation"
     *          ),
     *          @OA\Response(
     *              response=404,
     *              description="aucun résultat trouvé"
     *          ),
     *          @OA\Response(
     *              response=401,
     *              description="action unauthorized"
     *          ),
     *          @OA\Response(
     *              response=500,
     *              description="Erreur serveur"
     *          )
     *)  
     */
    public function notifications(Request $request){
        /** @user User $user description */
        try{
            $user= Auth::user();
            
            $notifications = $user->notifications->toArray();
            
            if(count($notifications)==0){
                return ApiResponse::error("No Notification found",404);
            }

            $notifications = array_map(function ($array){
                return ["id"=>$array["id"],"data"=> $array["data"]];
            },$notifications);
            
            return ApiResponse::success($notifications,"User notifications ");
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }

    }


}
