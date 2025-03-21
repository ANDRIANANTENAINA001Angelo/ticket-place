<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Event;
use App\Models\TypePlace;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\FileManip;
use App\Models\Code;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            // $events = Event::all();
            $events = Event::where("status","=",Event::STATUS_PUBLISHED)
            ->with([
                'tag',
                'type_places' => function ($query) {
                    $query->select('id', 'event_id', 'nom',"nombre","prix"); // Sélectionner uniquement les colonnes nécessaires
                }
            ])
            ->orderBy('date', 'asc')
            ->paginate(5);
                
            if(count($events)==0){
                return ApiResponse::error("No event found",404);
            }
            return response()->json($events);
            // return ApiResponse::success($events);
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }


         
    /**
    * @OA\Get(
    *      path="/api/events-all",
    *      tags={"Events"},
    *      summary="Get All Events List",
    *      description="return list of the all event Event not published",
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
    public function all()
    {
        try{
            /** @var User $user description */
            $user = Auth::user();

            if($user->IsCustomer()){
                $events = Event::where("status","=",Event::STATUS_PUBLISHED)
                ->with([
                    'tag',"codes",
                    'type_places' => function ($query) {
                        $query->select('id', 'event_id', 'nom',"nombre","prix"); // Sélectionner uniquement les colonnes nécessaires
                    }
                ])
                ->orderBy('date', 'asc')
                ->get();
            }
            else if($user->IsOrganiser()){
                $events = Event::where("user_id",$user->id)
                ->with([
                    'tag',"codes",
                    'type_places' => function ($query) {
                        $query->select('id', 'event_id', 'nom',"nombre","prix"); // Sélectionner uniquement les colonnes nécessaires
                    }
                ])
                ->orderBy('date', 'asc')
                ->get();
                
            }
            else{
                $events = Event::with(["tag","type_places","codes","user"])
                            ->orderBy('date', 'asc')            
                            ->get();
            }

                
            if(count($events)==0){
                return ApiResponse::success([],"No event found");
            }
            // return response()->json($events);
            return ApiResponse::success($events);
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }


    /**
    * @OA\Get(
    *      path="/api/grouped-event",
    *      tags={"Events"},
    *      summary="Get All Events Grouped by tag",
    *      description="return list of the all event Event group by tag",
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
    public function grouped()
    {
        try{
            // $events = Event::all();
            $events = Event::where("status","=",Event::STATUS_PUBLISHED)->with(["tag","type_places"])->get();
            if($events->isEmpty()){
                return ApiResponse::error("No event found",404);
            }

            
            $groupedEvent= $events->groupBy("tag.label");

            return ApiResponse::success($groupedEvent);
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
    *                      property="heure",
    *                      type="time",
    *                      example="19:00"
    *                  ),
    *                  @OA\Property(
    *                      property="tag_id",
    *                      type="integer",
    *                      example="1",
    *                      description="ID tag"
    *                      ),
    *                  @OA\Property(
    *                      property="image",
    *                      type="string",
    *                      format="binary",
    *                      nullable=true,
    *                      description="Image associée à l'événement"
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

            /** @var User $user description */
            $user = Auth::user();
            if(!$user->IsOrganiser()){
                return ApiResponse::error("Only Organiser can create event.",401);
            }

            $data = $request->validate([
                "titre"=>["required","string","max:150","min:10"],
                "description"=>["required","string","min:20"],
                "localisation"=>["required","string","min:5"],
                "date"=>["required","date",'after_or_equal:' . Carbon::now()->addDays(3)->toDateString()],
                "heure"=>["required","date_format:H:i"],
                "tag_id"=>["required","integer","exists:tags,id"],
                "image"=>["nullable","file","max:10240"]
            ]);

            if($request->hasFile("image")){
                $data["image"]= $this->saveImage($request);
            }

 
            // $data["status"]= Event::STATUS_CREATED;
            $data["status"]= Event::STATUS_PENDING;
            $data["user_id"]= $user->id;
            


            $event = Event::create($data);
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

            $event["tag"]= $event->tag;
            $event["type_places"]= $event->type_places;
            $event["codes"]= $event->codes;
            
            
            return ApiResponse::success($event);
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }


     /**
     * @OA\Post(
     *      path="/api/events/{event_id}/unapprouval",
     *      tags={"Events"},
     *      summary="Unapprouval an pending event",
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
    public function unapproval(string $id)
    {
        try{
            /** @var User $user description */            
            $user= Auth::user();

            if(!$user->IsAdministrator()){
                return ApiResponse::error("Action Interdit",403,"seulement admin peut faire ça.");
            }

            $event = Event::find($id);

            if(!$event){
                return ApiResponse::success([],"Event nout found");
            }

            if(!$event->status == Event::STATUS_PENDING){
                return ApiResponse::error("Action non authoriser",401,"seulement event en attent peut ne pas être approuvé.");
            }

            $event->update(["status"=>Event::STATUS_UNAPPROVAL]);
            $event->save();

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
    * @OA\Post(
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
    *                      property="heure",
    *                      type="time",
    *                      example="19:00"
    *                  ),
    *                  @OA\Property(
    *                      property="tag_id",
    *                      type="integer",
    *                      example="1",
    *                      description="ID tag"
    *                      ),
    *                  @OA\Property(
    *                      property="image",
    *                      type="string",
    *                      format="binary",
    *                      nullable=true,
    *                      description="Image associée à l'événement"
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
            // Log::info($request->all());
            // Log::info('Request method: ' . $request->getMethod());
            // Log::info('Request content type: ' . $request->header('Content-Type'));
            // Log::info('Request body: ' . $request->getContent());
            $Event = Event::find($id);
            
            if(!$Event){
                return ApiResponse::error("Event nout found",404);
            }
            
            if($Event->IsPublished()){
                return ApiResponse::error("Event published can't be updated",401);
            }

            /** @var User $actor_user description */
            $actor_user= Auth::user();
            if(!$actor_user->IsOrganiser()){
                return ApiResponse::error("Only Organiser can update event.",401);
            }

            if($actor_user->id != $Event->user_id){
                return ApiResponse::error("You can't update other's event",403);
            }
            
            $data = $request->validate([
                "titre"=>["nullable","string","max:150","min:10"],
                "description"=>["nullable","string","min:20"],
                "localisation"=>["nullable","string","min:10"],
                "date"=>["nullable","date",'after_or_equal:' . Carbon::now()->addDays(3)->toDateString()],
                "heure"=>["nullable","date_format:H:i"],
                "tag_id"=>["nullable","integer","exists:tags,id"],
                "image"=>["nullable","file","max:10240"]
                
            ]);
            
            
            // dd($request->hasFile("image"));
            if($request->hasFile("image")){
                $file = $request->file('image');
                // dd($file->getClientOriginalName(), $file->getSize(), $file->getMimeType());
                $oldImagePath= $Event->image;
                $data["image"]= $this->saveImage($request);
                
                if($oldImagePath!=null){
                    $this->deleteOldImage($oldImagePath);
                }
            }

            if(count($data)==0){
                return ApiResponse::error("Aucun donné validé pour l'update",400);
            }

            
            $Event->update($data);
            
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

            /** @var User $actor_user description */
            $actor_user= Auth::user();
            if($actor_user->id != $event->user_id && !$actor_user->IsAdministrator()){
                return ApiResponse::error("You can't delete other's event",403);
            }

            if($event->IsPublished()){
                return ApiResponse::error("Only events created or finished can be deleted.",400);
            }

            $event->delete();
            return ApiResponse::success([],"event deleted");
        }   
        catch(Exception $e){
            return ApiResponse::error("Error deleting event",500,$e->getMessage());
        }     
    }


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
    *         name="min_price",
    *         in="query",
    *         required=false,
    *         @OA\Schema(
    *             type="integer",
    *             minimum=0,
    *             example=1
    *         ),
    *         description="Minimum Price"
    *     ),
    *     @OA\Parameter(
    *         name="max_price",
    *         in="query",
    *         required=false,
    *         @OA\Schema(
    *             type="integer"
    *         ),
    *         description="Max Price"
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
            $min_price= (int)$request->input("min_price");
            $max_price= (int)$request->input("max_price");
           
            
            $query = Event::query();
            $query->with("type_places","tag")->where("status","=",Event::STATUS_PUBLISHED);
    
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

            if (!empty($min_price) && !empty($max_price)) {
                $query->whereHas('tag', function ($q) use ($min_price, $max_price) {
                    $q->whereBetween('prix', [$min_price, $max_price]);
                });
            } elseif (!empty($min_price)) {
                $query->whereHas('tag', function ($q) use ($min_price) {
                    $q->where('prix', '>=', $min_price);
                });
            } elseif (!empty($max_price)) {
                $query->whereHas('tag', function ($q) use ($max_price) {
                    $q->where('prix', '<=', $max_price);
                });
            }
    
           
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

            if($event->IsPublished() || $event->IsFinished()){
                return ApiResponse::error("You can't add place to event published or finished",401);
            }

            $user_id= Auth::user()->id;
            if($user_id!= $event->user_id){
                return ApiResponse::error("You can't update other's event info",403);
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

            $type_places= [];
            // create all type_places
            for($i=0;$i<count($data["type_places"]);$i++){
                $data["type_places"][$i]["event_id"]= $event->id;
                if($data["type_places"][$i]["nombre"]!=0){
                    $data["type_places"][$i]["is_limited"]=true;
                }
                $typePlace= TypePlace::create($data["type_places"][$i]);
                array_push($type_places,$typePlace);
            }

            return ApiResponse::success($type_places,"TypePlace added to the event");

        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
        
    }


        
    /**
     * @OA\Post(
     *     path="/api/events/{event_id}/add-code-promo",
     *     tags={"Events"},
     *     summary="Ajouter des code promo à un événement",
     *     description="Permet d'ajouter un ou plusieurs code promo à un événement existant.",
     *     @OA\Parameter(
     *         name="event_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         ),
     *         description="ID de l'événement auquel ajouter des codes promo"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="codes",
     *                 type="array",
     *                 minItems=1,
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="code",
     *                         type="string",
     *                         description="CodeXXXdDDSS"
     *                     ),
     *                     @OA\Property(
     *                         property="price",
     *                         type="integer",
     *                         description="Le pourcentage de réduction."
     *                     ),
     *                     @OA\Property(
     *                         property="expire_at",
     *                         type="date",
     *                         description="date expiration, not required, default value 30 days."
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

     public function addCodePromo(Request $request,string $id){
        try{
            $event = Event::find($id);
            if(!$event){
                return ApiResponse::success([],"Event not found");
            }

            if( $event->IsFinished()){
                return ApiResponse::error("You can't add code promo to event finished",401);
            }

            $user_id= Auth::user()->id;
            if($user_id!= $event->user_id){
                return ApiResponse::error("You can't update other's event info",403);
            }

            $data = $request->validate([
                'codes' => 'required|array|min:1',
                'codes.*.code' => 
                [
                    'nullable',
                    'string',
                    Rule::unique('codes', 'code')->where(function ($query) use ($event) {
                        return $query->where('event_id', $event->id);
                    }),
                ],
                'codes.*.price' => ['required',"integer",'min:1','max:98'],
                'codes.*.expire_at' => ["nullable","date",'after_or_equal:' . Carbon::now()->addDays(3)->toDateString()],
            ]);

            $codes= [];
            // create all codes promo
            for($i=0;$i<count($data["codes"]);$i++){
                $data["codes"][$i]["event_id"]= $event->id;
                $data["codes"][$i]["price"]= $data["codes"][$i]["price"] / 100;

                if(!isset($data["codes"][$i]["code"])){
                    $data["codes"][$i]["code"] = $this->GenerateRandomUniqueCode();
                }


                $code= Code::create($data["codes"][$i]);
                array_push($codes,$code);
            }

            return ApiResponse::success($codes,"Code promo added to the event");

        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
        
    }

    private function GenerateRandomUniqueCode():string{
        $codes = Code::pluck("code","id")->toArray();
        
        $new_code = Str::random($length=10);
        // $new_code = Str::uuid();
        
        if(count($codes)>0){
            while (in_array($new_code,$codes)){
                $new_code = Str::random($length=10);
                // $new_code = Str::uuid();
            }
        }

        return $new_code;

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

            /** @var User $actor_user description */
            $actor_user= Auth::user();
            if($actor_user->id != $event->user_id && !$actor_user->IsAdministrator()){
                return ApiResponse::error("You can't publish other's event",403);
            }


            if($event->IsPublished()){
                return ApiResponse::error("Event already published",400);
            }

            if($event->IsFinished()){
                return ApiResponse::error("Event finished, can't be published",400);
            }

            if(count($event->type_places)==0){
                return ApiResponse::error("Error published",400,"Event Must have at least one type place before publishement");
            }

            if($actor_user->IsAdministrator()){
                $event->status=Event::STATUS_PUBLISHED;
            }
            else{
                $event->status=Event::STATUS_PENDING;
            }

            $event->save();
            
            return ApiResponse::success($event,"Event published");
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }        
    }

    private function saveImage(Request $request){
        try{
            $image = $request->file("image");
            $file_name = Str::uuid() . time() . "." . $image->getClientOriginalExtension();
            $image_path = $image->storeAs("/event/image", $file_name, "public");
            return $image_path;
        }
        catch(Exception $e){
            return ApiResponse::error("server error",$e->getMessage());
        }
    }

    private function deleteOldImage(string $imagePath){
        try{
            Storage::delete(FileManip::UrlToPath($imagePath));
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }


    /**
    * @OA\Get(
    *      path="/api/search-event-price",
    *      tags={"Events"},
    *      summary="Search Events by price",
    *      description="Search Events published and not finished by price",
    *     @OA\Parameter(
    *         name="min_price",
    *         in="query",
    *         required=false,
    *         @OA\Schema(
    *             type="integer",
    *             minimum=0,
    *             example=1
    *         ),
    *         description="Prix Minimum "
    *     ),
    *     @OA\Parameter(
    *         name="max_price",
    *         in="query",
    *         required=false,
    *         @OA\Schema(
    *             type="integer"
    *         ),
    *         description="Max Price"
    *         ),
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
    public function searchPrice(Request $request)
    {
        try {
            $min_price = (int) $request->input("min_price");
            $max_price = (int) $request->input("max_price");
            
            $query = Event::with("type_places", "tag")->where("status", "=", Event::STATUS_PUBLISHED);
    
            if (!empty($min_price) && !empty($max_price)) {
                $query->whereHas('type_places', function ($q) use ($min_price, $max_price) {
                    $q->whereBetween('prix', [$min_price, $max_price]);
                });
            } elseif (!empty($min_price)) {
                $query->whereHas('type_places', function ($q) use ($min_price) {
                    $q->where('prix', '>=', $min_price);
                });
            } elseif (!empty($max_price)) {
                $query->whereHas('type_places', function ($q) use ($max_price) {
                    $q->where('prix', '<=', $max_price);
                });
            }
    
            $events = $query->get();
    
            if (count($events) == 0) {
                return ApiResponse::error("No Event found for this filter", 404);
            } else {
                return ApiResponse::success($events);
            }
        } catch (Exception $e) {
            return ApiResponse::error("server error", 500, $e->getMessage());
        }
    }
    



/**
    * @OA\Get(
    *      path="/api/search-event-text",
    *      tags={"Events"},
    *      summary="Search Events by text",
    *      description="Search Events published and not finished by text content",
    *       @OA\Parameter(
    *         name="text",
    *         in="query",
    *         required=true,
    *         @OA\Schema(
    *             type="string"
    *         ),
    *         description="Text of the event"
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
    public function searchText(Request $request){
        try{

            $text = (string)$request->input('text');
            
            $query = Event::query();
            $query->with("type_places","tag")->where("status","=",Event::STATUS_PUBLISHED);
    
            if (!empty($text)) {
                $query->where(function ($q) use ($text) {
                    $q->where('titre', 'LIKE', '%' . $text . '%')
                      ->orWhere('description', 'LIKE', '%' . $text . '%')
                      ->orWhere('localisation', 'LIKE', '%' . $text . '%')
                      ->orWhereHas('tag', function ($q) use ($text) {
                          $q->where('label', 'LIKE', '%' . $text . '%');
                      });
                });
            }
               
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
    * @OA\Get(
    *      path="/api/finished-events",
    *      tags={"Events"},
    *      summary="Get All Events Finished",
    *      description="return list of the all event Event finished",
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
    public function finished()
    {
        try{
            // $events = Event::all();
            $events = Event::where("status","=",Event::STATUS_FINISHED)->with(["tag","type_places"])->paginate(5);
            // $events = Event::where("status","=","published")->where("status","!=","finished")->with(["tag","type_places"])->get();
            if(count($events)==0){
                return ApiResponse::error("No event found",404);
            }
            return response()->json($events);
            // return ApiResponse::success($events);
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }


    
    /**
    * @OA\Get(
    *      path="/api/created-events",
    *      tags={"Events"},
    *      summary="Get All Events created",
    *      description="return list of the all event Event finished",
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
    public function created()
    {
        try{
            // $events = Event::all();
            $events = Event::where("status","=",Event::STATUS_CREATED)->with(["tag","type_places"])->paginate(5);
            // $events = Event::where("status","=","published")->where("status","!=","finished")->with(["tag","type_places"])->get();
            if(count($events)==0){
                return ApiResponse::error("No event found",404);
            }
            return response()->json($events);
            // return ApiResponse::success($events);
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }


    /**
    * @OA\Get(
    *      path="/api/famous-events",
    *      tags={"Events"},
    *      summary="Get 3 Most Famous Events",
    *      description="return the top 3 Famous Event",
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
    public function famousEvent(Request $request)
    {
        try {
            // Récupérer les 3 événements les plus populaires (plus de tickets vendus)
            $events = Event::where('status', Event::STATUS_PUBLISHED)
                ->with(['type_places' => function ($query) {
                    // Charger uniquement l'ID et les colonnes nécessaires des 'type_places'
                    $query->select('id', 'event_id',"nom","nombre","prix"); // Sélectionne uniquement 'id' et 'event_id' des 'type_places'
                }])
                ->get()
                ->sortByDesc(function ($event) {
                    // Calculer le nombre total de tickets vendus pour chaque event
                    return $event->type_places->sum(function ($typePlace) {
                        return $typePlace->tickets()->count(); // Compter les tickets sans les charger
                    });
                })
                ->take(3); // Limiter à 3 événements

            if ($events->isEmpty()) {
                return ApiResponse::error("No popular events found", 404);
            }

            // Mapper les données pour retourner uniquement ce qui est nécessaire
            $response = $events->map(function ($event) {
                return [
                    // 'event_id' => $event->id,
                    // 'title' => $event->title,
                    "event"=>$event,
                    'total_tickets_sold' => $event->type_places->sum(function ($typePlace) {
                        return $typePlace->tickets()->count(); // Calculer le nombre total de tickets vendus
                    }),
                ];
            });

            return ApiResponse::success($response->toArray());
        } catch (Exception $e) {
            return ApiResponse::error("Server error", 500, $e->getMessage());
        }
    }

    



}



