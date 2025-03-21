<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Code;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
    * @OA\Get(
    *      path="/api/codes",
    *      tags={"Codes"},
    *      summary="Get All Info Code",
    *      description="return list of the all code",
    *          @OA\Response(
    *              response=200,
    *              description="successful operation"
    *          ),
    *          @OA\Response(
    *              response=404,
    *              description="aucun résultat trouvé"
    *          ),
     *          @OA\Response(
     *              response=403,
     *              description="action forbiden"
     *          ),
     *          @OA\Response(
     *              response=401,
     *              description="action unauthorized"
     *          ),
    *          @OA\Response(
    *              response=500,
    *              description="erreur serveur"
    *          )
    *)  
    */
    public function index()
    {
        try{
            $codes= Code::all();
            if(count($codes)==0){
                return ApiResponse::error("No code found",404);
            }

            return ApiResponse::success($codes);
        }
        catch(Exception $e){
            return ApiResponse::error("Server Error",500,$e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {        
        try {
            /** @var User $user description */
            $user = Auth::user();
            if($user->IsCustomer()){
                return ApiResponse::error("Customer can't create code promo",400);
            }

            $data = $request->validate([
                "code"=>["nullable","string","min:6","max:20","unique:".Code::class],
                "price"=>["required","number","min:0.01","max:1"],
                "expire_at"=>["nullable","date"],
            ]);

            $data["user_id"]= $user->id;

            if(count($data)==0){
                return ApiResponse::error("Error creating",400,"Aucun donnée valide reçue");
            }

            if(!isset($dat["code"])){
                $data["code"] = $this->GenerateRandomUniqueCode();
            }

            $code= Code::create($data);            
            return ApiResponse::success($code,"Your code is generated successfull");
        }
        catch(Exception $e){
            return ApiResponse::error("Error creating Code",500,$e->getMessage());
        }
        
    }

    /**
     * Display the specified resource.
     */

     /**
     * @OA\Get(
     *      path="/api/codes/{code}",
     *      tags={"Codes"},
     *      summary="Get One Code Info",
     *      description="return the info of the code",
     *       @OA\Parameter(
     *              name="code",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="string"
     *              ),
     *              description="ID of the code to show"
     *            ),
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
     *              description="erreur serveur"
     *          )
     *)  
     */
    public function show(string $code)
    {
        try{
            $code = Code::where("code","=",$code)->get();
            if(!$code){
                return ApiResponse::error("Code not found",404);
            }
            else{
                return ApiResponse::success($code[0]);
            }
        }
        catch(Exception $e){
            return ApiResponse::error("Error showing resource",500,$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *      path="/api/codes/{code}",
     *      tags={"Codes"},
     *      summary="Update code",
     *      description="Update reduction code",
     *       @OA\Parameter(
     *              name="code",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="string"
     *              ),
     *              description="ID of the code to update"
     *            ),
     *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(
    *                  type="object",
    *                  @OA\Property(
    *                      property="price",
    *                      type="number",
    *                      example="0.01",
    *                       description="Pourcentage prix réduction"
    *                  ),
    *                  @OA\Property(
    *                      property="expire_at",
    *                      type="date",
    *                      example="2024-04-30"
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
     *              response=401,
     *              description="action unauthorized"
     *          ),
     *          @OA\Response(
     *              response=403,
     *              description="action forbiden"
     *          ),
     *          @OA\Response(
     *              response=500,
     *              description="erreur serveur"
     *          )
     *)  
     */
    public function update(Request $request, string $id)
    {
        try{

            $code= Code::find($id);
            if(!$code){
                return ApiResponse::error("Code Not found",404);
            }

            /** @var User $actor_user description */
            $actor_user = Auth::user();
            if($actor_user->id != $code->user_id){
                return ApiResponse::error("Error Permission",401,"You can't update other's code promo");
            }
    
            $data = $request->validate([
                "price"=>["nullable","number","min:0.01","max:1"],
                "expire_at"=>["nullable","date",'after_or_equal:' . Carbon::now()->addDays(7)->toDateString()],
            ]);
    
            if(count($data)==0){
                return ApiResponse::error("Error updating",400,"Aucun donnée valide reçue");
            }
    
            $code->update($data);            
            return ApiResponse::success($code,"You code is updated successfull");
        }
        catch(Exception $e){
            return ApiResponse::error("Error updating code info",500,$e->getMessage());
        }

    }


    /**
     * @OA\Delete(
     *      path="/api/codes/{code_id}",
     *      tags={"Auth"},
     *      summary="Delete code",
     *      description="Delete reduction code",
     *       @OA\Parameter(
     *              name="code_id",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="string"
     *              ),
     *              description="ID of the code to delete"
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
     *              response=500,
     *              description="erreur serveur"
     *          )
     *)  
     */
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,string $id)
    {
        /** @var User $user description */
        $user = Auth::user();
        if($user->IsCustomer()){
            return ApiResponse::error("Customer can't delete code promo",400);
        }

        $code= Code::find($id);
        
        if(!$code){
            return ApiResponse::error("Code Not found",404);
        }
        
        if($user->id !=$code->user_id && !$user->IsAdministrator()){
            return ApiResponse::error("You are not allowed to delete ohters's code",403);
        }
        
        $code->delete();
        return ApiResponse::success([],"your reduction code is deleted");
    }



    /**
     * @OA\Post(
     *      path="/api/generate-code",
     *      tags={"Codes"},
     *      summary="Generate new random code",
     *      description="Generate new random reduction code",
     *          @OA\Response(
     *              response=200,
     *              description="successful operation"
     *          ),
     *          @OA\Response(
     *              response=500,
     *              description="erreur serveur"
     *          )
     *)  
     */
    public function GenerateRandomUniqueCode($api_response=true):string{
        $codes = Code::pluck("code","id")->toArray();
        
        $new_code = Str::random($length=10);
        // $new_code = Str::uuid();
        
        if(count($codes)>0){
            while (in_array($new_code,$codes)){
                $new_code = Str::random($length=10);
                // $new_code = Str::uuid();
            }
        }

        if(!$api_response){
            return $new_code;
        }
        else{
            return ApiResponse::success(["code"=>$new_code],"code promo generated");
        }
    }


    /**
     * @OA\Post(
     *      path="/api/codes",
     *      tags={"Codes"},
     *      summary="Create new code",
     *      description="Create new reduction code",
     *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(
    *                  type="object",
    *                  @OA\Property(
    *                      property="code",
    *                      type="string",
    *                      example="qskj34qsd"
    *                  ),
    *                  @OA\Property(
    *                      property="price",
    *                      type="number",
    *                      example="0.01",
    *                      description="Pourcentage montant réduction"
    *                  ),
    *                  @OA\Property(
    *                      property="expire_at",
    *                      type="date",
    *                      example="2024-04-30"
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
     *              response=401,
     *              description="action unauthorized"
     *          ),
     *          @OA\Response(
     *              response=403,
     *              description="action forbiden"
     *          ),
     *          @OA\Response(
     *              response=500,
     *              description="erreur serveur"
     *          )
     *)  
     */
    public function CreateCode(Request $request){
        try{
            /** @var User $user description */
            $user =Auth::user();
            
            if($user->IsCustomer()){
                return ApiResponse::error("Customer can't create code promo",403);
            }
            
            $data = $request->validate([
                "code"=>["nullable","string","min:5"],
                "price"=>["required","number","min:0.01","max:1"],
                "expire_at"=>["required","date",'after_or_equal:' . Carbon::now()->addDays(7)->toDateString()]
            ]);

            if(count($data)==0){
                return ApiResponse::error("Error creating",400,"Aucun donnée valide reçue");
            }


            $data["user_id"] = $user->id;
   
            if(!isset($data["code"])){
                $data["code"]=$this->GenerateRandomUniqueCode(false);
            }
    
            $code = Code::create($data);
    
            return ApiResponse::success($code,"You code promo is created");
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }

}
