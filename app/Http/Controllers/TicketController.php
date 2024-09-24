<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    
    /**
     * @OA\Get(
     *      path="/api/my-tickets",
     *      tags={"Users"},
     *      summary="Get user's tickets",
     *      description="return the list of user's tickets",
     *      @OA\Response(
     *          response=200,
     *          description="successful operation"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="aucun résultat trouvé"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="erreur serveur"
     *      )
     *)  
     */
    public function userTickets(Request $request){

        /** @var User $user description */
        $user = Auth::user();

        if($user->IsAdministrator()){
            return ApiResponse::error("Administrator have not Tickets",400);
        }

        $tickets =$user->tickets;
        if(count($tickets)==0){
            return ApiResponse::error("No ticket found",404);
        }

        // Groupement des tickets par la clé 'created_at'
        $groupedTickets = $tickets->groupBy('created_at');

        return ApiResponse::success(["tickets"=>$groupedTickets]);
    }
    // public function userTickets(Request $request){
    //     /** @var User $user description */
    //     $user = Auth::user();

    //     if($user->IsAdministrator()){
    //         return ApiResponse::error("Administrator have not Tickets",400);
    //     }

    //     $tickets_id= [];
    //     foreach ($user->tickets as $ticket) {
    //         array_push($tickets_id,$ticket->id);
    //     }

    //     $type_places_id= [];
    //     $events_id=[];
    //     foreach ($user->tickets->type_place as $type_place) {
    //         array_push($type_places_id,$type_place->id);
    //         array_push($events_id,$type_place->event_id);
    //     }

    //     $events=[];
    //     foreach ($events_id as $id) {
    //         array_push($events,Event::find($events_id));
    //     }
        

    // } 




     /**
     * @OA\Get(
     *      path="/api/events/{event_id}/tickets",
     *      tags={"Events"},
     *      summary="Get Event's Tickets",
     *      description="return list of Event's tickets",
     *       @OA\Parameter(
     *              name="event_id",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="string"
     *              ),
     *              description="ID of the Event"
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
     *              response=404,
     *              description="aucun résultat trouvé"
     *          ),
     *          @OA\Response(
     *              response=500,
     *              description="erreur serveur"
     *          )
     *)  
     */
    public function eventTickets(Request $request,string $id){
        /** @var User $actor_user description */
        $actor_user = Auth::user();
        
        $event = Event::with("type_places")->find($id)->get();
        $event = $event[0];
        
        if(!$event){
            return ApiResponse::error("Event nout found",404);
        }

        if(($actor_user->id != $event->user_id) && ! $actor_user->IsAdministrator()){
            return ApiResponse::error("Error Access",401,"Only administrator can see other's event ticket");
        }
        
        $tickets=[];

        foreach($event->type_places as $type_place){
            foreach ($type_place->tickets as $ticket) {
                array_push($tickets,$ticket);  
            }            
        }

        if(count($tickets)==0){
            return ApiResponse::error("No ticket found for this event",404);
        }

        return ApiResponse::success(["tickets"=>$tickets]);

    }




}
