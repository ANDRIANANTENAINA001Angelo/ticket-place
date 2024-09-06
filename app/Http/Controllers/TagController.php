<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Tag;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
    * @OA\Get(
    *      path="/api/tags",
    *      tags={"Tags"},
    *      summary="Get All Tags List",
    *      description="return list of the all event tag",
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
            $tags = Tag::all();
            if(count($tags)==0){
                return ApiResponse::error("No tag found",404);
            }
            return ApiResponse::success($tags);
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }

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
    
    /**
    * @OA\Post(
    *      path="/api/tags",
    *      tags={"Tags"},
    *      summary="Create new Tag",
    *      description="Create new Tag",
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(
    *                  type="object",
    *                  @OA\Property(
    *                      property="label",
    *                      type="string",
    *                      example="concert"
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
    public function store(Request $request)
    {
        try{
            $data = $request->validate([
                "label"=>["required","string","max:50","min:5"]
            ]);
    
            $tag = Tag::create($data);
            return ApiResponse::success($tag,"Tag created");
        }
        catch(Exception $e){
            return ApiResponse::error("Error creating tag",500,$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    
     /**
     * @OA\Get(
     *      path="/api/tags/{tag_id}",
     *      tags={"Tags"},
     *      summary="Get One Tag Info",
     *      description="return info of the tag",
     *       @OA\Parameter(
     *              name="tag_id",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="string"
     *              ),
     *              description="ID of the tag to show"
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
     *              description="aucun résultat trouvé"
     *          ),
     *          @OA\Response(
     *              response=500,
     *              description="erreur serveur"
     *          )
     *)  
     */
    public function show(string $id)
    {
        try{
            $tag = Tag::find($id);
            if(!$tag){
                return ApiResponse::error("Tag nout found",404);
            }
            $tag["events"]=$tag->events;
            return ApiResponse::success($tag);
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
    *      path="/api/tags/{tag_id}",
    *      tags={"Tags"},
    *      summary="Update Tag",
    *      description="Update Tag label",
    *       @OA\Parameter(
    *              name="tag_id",
    *              in="path",
    *              required=true,
    *              @OA\Schema(
    *                  type="string"
    *              ),
    *              description="ID of the tag to update"
    *            ),
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(
    *                  type="object",
    *                  @OA\Property(
    *                      property="label",
    *                      type="string",
    *                      example="bal masqué"
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
            /** @var User $actor_user description */
            $actor_user = Auth::user();
            if(!$actor_user->IsAdministrator()){
                return ApiResponse::error("Error Permission",401,"Only administrator can update Tag");
            }

            $tag = Tag::find($id);
            if(!$tag){
                return ApiResponse::error("Tag nout found",404);
            }

            $data = $request->validate([
                "label"=>["required","string","max:50","min:5"]
            ]);
    
            $tag->update($data);
            $tag->save();
            return ApiResponse::success($tag,"Tag updated");
        }
        catch(Exception $e){
            return ApiResponse::error("Error updating tag",500,$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    
     /**
     * @OA\Delete(
     *      path="/api/tags/{tag_id}",
     *      tags={"Tags"},
     *      summary="Delete tag",
     *      description="Delete tag",
     *       @OA\Parameter(
     *              name="tag_id",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="string"
     *              ),
     *              description="ID of the tag to delete"
     *            ),
     *          @OA\Response(
     *              response=200,
     *              description="successful operation"
     *          ),
     *          @OA\Response(
     *              response=401,
     *              description="action not authorized"
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
    public function destroy(string $id)
    {
        try{
            /** @var User $actor_user description */
            $actor_user = Auth::user();
            if(!$actor_user->IsAdministrator()){
                return ApiResponse::error("Error Permission",403,"Only administrator can delete Tag");
            }

            $tag = Tag::find($id);
            if(!$tag){
                return ApiResponse::error("Tag nout found",404);
            }

            $tag->delete();
            return ApiResponse::success([],"Tag deleted");
        }   
        catch(Exception $e){
            return ApiResponse::error("Error deleting tag",500,$e->getMessage());
        }     
    }
}
