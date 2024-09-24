<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Cart;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\TypePlace;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DashboardController extends Controller
{

        
    /**
     * @OA\Get(
     *      path="/api/dashboard/resume",
     *      tags={"Dashborad"},
     *      summary="Dashbord Info",
     *      description="return the resume stats info",
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
    public function getResume(){
        try{
            /** @var User $user description */
            $user= Auth::user();
    
            if($user->IsCustomer()){
                return ApiResponse::error("error permission",403,"You can't access this ressource");
            }
    
            $result=[];
            // total income, count all event, all ticket, all customer paid ticket
            if($user->IsAdministrator()){
                $tickets= Ticket::all();
    
                $result["events"]=count(Event::all());
    
                $result["tickets"]= count($tickets);
    
                $result["users"]= count($tickets->groupBy("user_id")->toArray());
    
                $result["income"]= 0;
                $carts= Cart::where("status","purchased")->get();
                if(!$carts->isEmpty()){
                    foreach($carts as $cart){
                        $result["income"] += $cart->montant; 
                    }
                }
    
                return ApiResponse::success($result);
            }
    
            if($user->IsOrganiser()){
                $events = Event::where("user_id",$user->id)->get();
    
                $result["events"]=0;
                $result["tickets"]= 0;
                $result["users"]= 0;
                $result["income"]= 0;
            
                if(!$events->isEmpty()){
                    $result["events"]= count($events);
    
                    $users=[];
                    foreach ($events as $event) {
                        $type_places = $event->type_places;

                        if(!$type_places->isEmpty()){
                            foreach ($type_places as $type_place) {
                                $prix= $type_place->prix;
    
                                $tickets= $type_place->tickets;
                                if(!$tickets->isEmpty()){
                                    $result["tickets"] += count($tickets);
                                    // $result["users"] += count($tickets->groupBy("user_id")->toArray());
                                    $result["income"] += $prix*count($tickets);
                                    
                                    foreach ($tickets as $ticket) {
                                        if(! in_array($ticket->user_id,$users)){
                                            array_push($users,$ticket->user_id);
                                        }
                                    }
                                    
                                }
                                
                            }
                        }
                    }
                    $result["users"] = count($users);
                    
                }
    
                return ApiResponse::success($result);
        }
    }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    
    }

    

    /**
     * @OA\Get(
     *      path="/api/dashboard/sales",
     *      tags={"Dashborad"},
     *      summary="Dashbord Sale's Info",
     *      description="return the resume sale's info",
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
    public function getGetSales(){
        /** @var User $user description */
        $user = Auth::user();

        if($user->IsCustomer()){
            return ApiResponse::error("error authorization",403,"You can't access this resource");
        }

        
        $carts = Cart::where("status","purchased")->with("user")->get();
        if($carts->isEmpty()){
            return ApiResponse::error("Pas de vente effectué pour le moment !",404);
        }
        
        if($user->IsAdministrator()){
            return ApiResponse::success($carts);
        }

        if($user->IsOrganiser()){
            $lines=[];//user (cart), date (cart), income (items current user's)

            /** @var Cart $cart description */
            foreach ($carts as $cart) {
                // dd($cart);
                /** @var Item $item description */
                foreach ($cart->items as $item) {
                    $line=[];
                    // dd($item);
                    /** @var TypePlace $type_place description */
                        // dd($item->type_place->event);
                        /** @var Event $event description */
                        if($item->type_place->event->user_id == $user->id){
                            $line["user"]= User::where("id",$cart->user_id)->get()[0];
                            $line["updated_at"] = $cart->updated_at;
                            $line["montant"]= ($item->type_place->prix * $item->nombre);
                        }
                    array_push($lines,$line);
                }
            }
            
            if(count($lines)==0){
                return ApiResponse::error("Pas de vente effectué pour le moment !",404);
            }

            return ApiResponse::success($lines);
        }

    }


    /**
     * @OA\Get(
     *      path="/api/dashboard/year-stat/{year}",
     *      tags={"Dashborad"},
     *      summary="Dashbord Stat year Info",
     *      description="return the stat year",
     *       @OA\Parameter(
     *              name="year",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="integer"
     *              ),
     *              description="The Year"
     *            ),
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
    public function getStatYears(int $year)
    {

        // Validation de l'année
        $validator = Validator::make(
            ['year' => $year],
            ['year' => 'required|integer|digits:4|min:1900|max:' . date('Y')] // Validation pour vérifier si c'est une année valide
        );

        // Si la validation échoue, on renvoie une erreur
        if ($validator->fails()) {
            return ApiResponse::error("Invalid year format", 422, $validator->errors());
        }

        // Mois en français
    $months = [
        1 => "Janvier",
        2 => "Février",
        3 => "Mars",
        4 => "Avril",
        5 => "Mais",
        6 => "Juin",
        7 => "Juillet",
        8 => "Aout",
        9 => "Septembre",
        10 => "Octobre",
        11 => "Novembre",
        12 => "Décembre"
    ];

    // Requête pour récupérer la somme des ventes par mois pour l'année donnée
    $sales = DB::table('carts')
        ->selectRaw('MONTH(updated_at) as month, SUM(montant) as total_sales')
        ->whereYear('updated_at', $year)  // Filtrer par l'année reçue en paramètre
        ->groupByRaw('MONTH(updated_at)')  // Grouper par mois
        ->get();

    // Initialiser un tableau avec les 12 mois et 0 comme revenu par défaut
    $chartData = [];
    for ($i = 1; $i <= 12; $i++) {
        $chartData[] = [
            'month' => $months[$i],
            'revenu' => 0
        ];
    }

    // Mettre à jour les revenus avec les données existantes
    foreach ($sales as $sale) {
        $chartData[$sale->month - 1]['revenu'] = $sale->total_sales;
    }

    // Retourner les données dans le format requis via ApiResponse
    return ApiResponse::success($chartData);
    }

}


