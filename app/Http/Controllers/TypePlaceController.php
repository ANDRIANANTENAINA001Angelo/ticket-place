<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\TypePlace;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TypePlaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */


    /**
    * @OA\Get(
    *      path="/api/event-type-place",
    *      tags={"TypePlaces"},
    *      summary="Get All Type place of events",
    *      description="return list of the all Type place of events",
    *          @OA\Response(
    *              response=200,
    *              description="successful operation"
    *          ),
     *          @OA\Response(
     *              response=401,
     *              description="action unauthorized"
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
            $type_places = TypePlace::all();
            if(count($type_places)==0){
                return ApiResponse::error("No Type place found",404);
            }
            return ApiResponse::success($type_places);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *      path="/api/event-type-place/{type_place_id}",
     *      tags={"TypePlaces"},
     *      summary="Show the type place ",
     *      description="Show type place and his event",
     *      @OA\Parameter(
     *         name="type_place_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID du types de place"
     *       ),
     *       @OA\Response(
     *           response=200,
     *           description="opération succès"
     *       ),
     *       @OA\Response(
     *           response=404,
     *           description="Type Place non trouvé"
     *       ),
     *       @OA\Response(
     *           response=500,
     *           description="server error"
     *       )
     *     )
     * 
     */
    public function show(string $id)
    {
        try{
            $type_place = TypePlace::find($id);
            
            if(!$type_place){
                return ApiResponse::error("The Type place not found",404);
            }
            // dd($type_place);
            return ApiResponse::success($type_place);
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }   
    }

    /**
     * update for editing the specified resource.
     */

    /**
     * @OA\Put(
     *      path="/api/event-type-place/{type_place_id}",
     *      tags={"TypePlaces"},
     *      summary="Update type place ",
     *      description="Update type place info of a event",
     *      @OA\Parameter(
     *         name="type_event_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID du types de place"
     *     ),    
     *     @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(
    *                  type="object",           
    *                   @OA\Property(
     *                      property="nom",
     *                      type="string",
     *                      description="Le nom du type de place"
     *                  ),
     *                  @OA\Property(
     *                      property="nombre",
     *                      type="integer",
     *                      description="Le nombre de places pour ce type"
     *                     ),
     *                   @OA\Property(
     *                       property="prix",
     *                       type="integer",
     *                       description="Le prix de ce type de place"
     *                    )
     *                  )
     *              )
     *          ),
     *          @OA\Response(
     *              response=200,
     *              description="Types de place ajoutés avec succès"
     *          ),
     *          @OA\Response(
     *              response=400,
     *              description="Données invalides"
     *          ),
     *          @OA\Response(
     *              response=404,
     *              description="Événement non trouvé"
     *          )
     *      )
     * 
     */
    public function update(Request $request,string $id)
    {
        try{

            $type_place = TypePlace::find($id);
            
            
            if(!$type_place){
                return ApiResponse::error("Type Place not found",404);
            }
         
            if($type_place->event->status =="published"){
                return ApiResponse::error("Event published's info, can't be updated",403);
            }
    
            $user_id= Auth::user()->id;
            $event = $type_place->event;
    
            // dd($user_id,$type_place->event->user->id);
            if($user_id != $type_place->event->user->id){
                return ApiResponse::error("You can't update other's event info",403);
            }
    
            $data = $request->validate([
                "nom"=>[
                    "nullable",
                    "string",
                    "max:200",
                    Rule::unique('type_places', 'nom')->where(function ($query) use ($event) {
                        return $query->where('event_id', $event->id);
                    }),
                ],
                "nombre"=>["nullable","integer","min:0"],
                "prix"=>["nullable","integer","min:0"]
            ]);
    
            if(count($data)==0){
                return ApiResponse::error("No data valide received",400);
            }
    
            if(isset($data["nombre"]) && $data["nombre"]!=0){
                $data["is_limited"]= true;
            }
    
            $type_place->update($data);
            $type_place->save();
    
            return ApiResponse::success($type_place,"Type place update");
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
        
    }

    /**
     * Edit the specified resource in storage.
     */
    public function edit(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */

         /**
     * @OA\Delete(
     *      path="/api/event-type-place/{type_event_id}",
     *      tags={"TypePlaces"},
     *      summary="Delete type place event",
     *      description="Delete type place event",
     *       @OA\Parameter(
     *              name="type_event_id",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="string"
     *              ),
     *              description="ID of the event to delete"
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
            $type_place = TypePlace::find($id);
            
            if(!$type_place){
                return ApiResponse::error("Type Place not found",404);
            }
            
            $user_id= Auth::user()->id;
    
            if($user_id != $type_place->event->user->id){
                return ApiResponse::error("You can't delete other's event info",403);
            }
    
            if($type_place->event->status == "published" ){
                return ApiResponse::error("Event published's info, can't be deleted",403);            
            }
    
            $type_place->delete();
            return ApiResponse::success([],"Type place deleted");
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }

    
}
