<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Event;
use App\Models\TypePlace;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
        /**
    * @OA\Get(
    *      path="/api/events",
    *      tags={"Events"},
    *      summary="Get All Events List",
    *      description="return list of the all event Event",
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
            $events = Event::all();
            // $events = Event::with("tags")->get();
            if(count($events)==0){
                return ApiResponse::error("No event found",404);
            }
            return ApiResponse::success($events);
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
    *      path="/api/events",
    *      tags={"Events"},
    *      summary="Create new Event",
    *      description="Create new Event",
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(
    *                  type="object",
    *                  @OA\Property(
    *                      property="titre",
    *                      type="string",
    *                      example="concert"
    *                  ),
    *                  @OA\Property(
    *                      property="description",
    *                      type="string",
    *                      example="plus de detail sur l'event"
    *                  ),
    *                  @OA\Property(
    *                      property="localisation",
    *                      type="string",
    *                      example="Rex Anjoma"
    *                  ),
    *                  @OA\Property(
    *                      property="date",
    *                      type="date",
    *                      example="2024-08-23"
    *                  ),
    *                  @OA\Property(
    *                      property="tags",
    *                      type="array",
    *                      @OA\Items(
    *                           type="integer", 
    *                           example="1"
    *                           ),
    *                      example={"1", "3", "5", "6"}
    *                   )
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
                "titre"=>["required","string","max:150","min:10"],
                "description"=>["nullable","string","min:20"],
                "localisation"=>["required","string","min:10"],
                "date"=>["required","date",'after_or_equal:' . Carbon::now()->addDays(3)->toDateString()],
                "tags"=>["required","array","exists:tags,id"]
            ]);
            
            $event = Event::create($data);
            $event->tags()->sync($data["tags"]);
            return ApiResponse::success($event,"Event created");
        }
        catch(Exception $e){
            return ApiResponse::error("Error creating Event",500,$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    
     /**
     * @OA\Get(
     *      path="/api/events/{event_id}",
     *      tags={"Events"},
     *      summary="Get One Event Info",
     *      description="return info of the Event",
     *       @OA\Parameter(
     *              name="event_id",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="string"
     *              ),
     *              description="ID of the Event to show"
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
            $event = Event::find($id);
            
            if(!$event){
                return ApiResponse::error("Event nout found",404);
            }

            $event["tags"]= $event->tags;
            $event["type_places"]= $event->type_places;
            return ApiResponse::success($event);
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
    *      path="/api/events/{event_id}",
    *      tags={"Events"},
    *      summary="Update Event",
    *      description="Update Event info",
    *       @OA\Parameter(
    *              name="event_id",
    *              in="path",
    *              required=true,
    *              @OA\Schema(
    *                  type="string"
    *              ),
    *              description="ID of the Event to update"
    *            ),
    *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(
    *                  type="object",
    *                  @OA\Property(
    *                      property="titre",
    *                      type="string",
    *                      example="concert"
    *                  ),
    *                  @OA\Property(
    *                      property="description",
    *                      type="string",
    *                      example="plus de detail sur l'event"
    *                  ),
    *                  @OA\Property(
    *                      property="localisation",
    *                      type="string",
    *                      example="Rex Anjoma"
    *                  ),
    *                  @OA\Property(
    *                      property="date",
    *                      type="date",
    *                      example="2024-08-23"
    *                  ),
    *                  @OA\Property(
    *                      property="tags",
    *                      type="array",
    *                      @OA\Items(
    *                           type="integer", 
    *                           example="1"
    *                           ),
    *                      example={"1", "3", "5", "6"}
    *                   )
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
            $Event = Event::find($id);
            if(!$Event){
                return ApiResponse::error("Event nout found",404);
            }

            $data = $request->validate([
                "titre"=>["nullable","string","max:150","min:10"],
                "description"=>["nullable","string","min:20"],
                "localisation"=>["nullable","string","min:10"],
                "date"=>["nullable","date",'after_or_equal:' . Carbon::now()->addDays(3)->toDateString()],
                "tags"=>["nullable","array","exists:tags,id"]
            ]);

            if(count($data)==0){
                return ApiResponse::error("Aucun donné validé pour l'update",400);
            }
            
            
            $Event->update($data);
            $Event->tags()->sync($data["tags"]);
            $Event->save();
            return ApiResponse::success($Event,"Event updated");
        }
        catch(Exception $e){
            return ApiResponse::error("Error updating Event",500,$e->getMessage());
        }    
    }

    /**
     * Remove the specified resource from storage.
     */
    
     /**
     * @OA\Delete(
     *      path="/api/events/{event_id}",
     *      tags={"Events"},
     *      summary="Delete event",
     *      description="Delete event",
     *       @OA\Parameter(
     *              name="event_id",
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
            $event = Event::find($id);
            if(!$event){
                return ApiResponse::error("event nout found",404);
            }

            $event->delete();
            return ApiResponse::success([],"event deleted");
        }   
        catch(Exception $e){
            return ApiResponse::error("Error deleting event",500,$e->getMessage());
        }     
    }


    // "/search-event?title={title}&localisation={localisation}&start_date={start_date}&end_date={end_date}&tag={tag_name}"
    /**
    * @OA\Get(
    *      path="/api/search-event",
    *      tags={"Events"},
    *      summary="Search or Filter Events",
    *      description="Search or Filter Events published and not finished",
    *       @OA\Parameter(
    *         name="title",
    *         in="query",
    *         required=false,
    *         @OA\Schema(
    *             type="string"
    *         ),
    *         description="Title of the event"
    *     ),
    *     @OA\Parameter(
    *         name="localisation",
    *         in="query",
    *         required=false,
    *         @OA\Schema(
    *             type="string"
    *         ),
    *         description="Location of the event"
    *     ),
    *     @OA\Parameter(
    *         name="start_date",
    *         in="query",
    *         required=false,
    *         @OA\Schema(
    *             type="string",
    *             format="date"
    *         ),
    *         description="Start date of the event (format: YYYY-MM-DD)"
    *     ),
    *     @OA\Parameter(
    *         name="end_date",
    *         in="query",
    *         required=false,
    *         @OA\Schema(
    *             type="string",
    *             format="date"
    *         ),
    *         description="End date of the event (format: YYYY-MM-DD)"
    *     ),
    *     @OA\Parameter(
    *         name="tag",
    *         in="query",
    *         required=false,
    *         @OA\Schema(
    *             type="string"
    *         ),
    *         description="Tag associated with the event"
    *     ),
    *      @OA\Response(
    *              response=200,
    *              description="successful operation",
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
    public function search(Request $request){
        try{

            $title = (string)$request->input('title');
            $localisation = (string)$request->input('localisation');
            $start_date= (string)$request->input("start_date");
            $end_date= (string)$request->input("end_date");
            $tag_name= (string)$request->input("tags_name");

            $query = Event::query();
            $query->where("status","=","published")->where("status","!=","finished");
    
            if (!empty($title)) {
                $query->where('titre', 'LIKE', '%' . $title . '%')->orWhere("description","LIKE","%".$title."%");
            }
    
            if (!empty($localisation)) {
                $query->where('localisation', 'LIKE', '%' . $localisation . '%');
            }
    
            if(!empty($start_date)){
                $query->where("date",">",$start_date);
            }
    
            if(!empty($end_date)){
                $query->where("date","<",$end_date);
            }
    
            if(!empty($tag_name)){
                $query->whereHas('tags', function($query) use ($tag_name) {
                    $query->where('label', 'LIKE', '%' . $tag_name . '%');
                });
                // $query->with("tags")->where("label","LIKE","%".$tag_name."%");
            }
    
            $query->with("tags");
            $events = $query->get();
    
            if(count($events)==0){
                return ApiResponse::error("No Event found, for this filter",404);
            }
            else{
                return ApiResponse::success($events);
            }
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }



    /**
     * @OA\Post(
     *     path="/api/events/{event_id}/add-type-place",
     *     tags={"Events"},
     *     summary="Ajouter des types de place à un événement",
     *     description="Permet d'ajouter un ou plusieurs types de place à un événement existant.",
     *     @OA\Parameter(
     *         name="event_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID de l'événement auquel ajouter des types de place"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="type_places",
     *                 type="array",
     *                 minItems=1,
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="nom",
     *                         type="string",
     *                         description="Le nom du type de place"
     *                     ),
     *                     @OA\Property(
     *                         property="nombre",
     *                         type="integer",
     *                         description="Le nombre de places pour ce type"
     *                     ),
     *                     @OA\Property(
     *                         property="prix",
     *                         type="integer",
     *                         description="Le prix de ce type de place"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Types de place ajoutés avec succès"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Événement non trouvé"
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */

    public function addTypePlace(Request $request,string $id){
        try{
            $event = Event::find($id);
            if(!$event){
                return ApiResponse::error("Event not found",404);
            }


            $data = $request->validate([
                'type_places' => 'required|array|min:1',
                'type_places.*.nom' => 
                [
                    'required',
                    'string',
                    Rule::unique('type_places', 'nom')->where(function ($query) use ($event) {
                        return $query->where('event_id', $event->id);
                    }),
                ],
                'type_places.*.nombre' => 'required|integer',
                'type_places.*.prix' => 'required|integer',
            ]);

            // create all type_places
            for($i=0;$i<count($data["type_places"]);$i++){
                $data["type_places"][$i]["event_id"]= $event->id;
                if($data["type_places"][$i]["nombre"]!=0){
                    $data["type_places"][$i]["is_limited"]=true;
                }
                TypePlace::create($data["type_places"][$i]);
            }

            return ApiResponse::success([],"TypePlace added to the event");

        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
        
    }


    /**
     * @OA\Post(
     *      path="/api/events/{event_id}/publish",
     *      tags={"Events"},
     *      summary="Publish the event",
     *      description="Publish the Event, to end creation",
     *       @OA\Parameter(
     *              name="event_id",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="string"
     *              ),
     *              description="ID of the Event to show"
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
    public function publish(Request $request,string $id){
        try{
            $event = Event::find($id);
            
            if(!$event){
                return ApiResponse::error("Event nout found",404);
            }

            if($event->status=="published"){
                return ApiResponse::error("Event already published",400);
            }

            if(count($event->type_places)==0){
                return ApiResponse::error("Error published",400,"Event Must have type place before publishement");
            }

            $event->status="published";
            $event->save();
            
            return ApiResponse::success($event,"Event published");
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }        
    }




}
