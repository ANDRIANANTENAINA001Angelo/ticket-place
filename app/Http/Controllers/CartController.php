<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Cart;
use App\Models\Code;
use App\Models\Item;
use App\Models\Ticket;
use App\Models\TypePlace;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Event\Code\Throwable;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
    public function index()
    {
        
        
    }

    /**
    * @OA\Get(
    *      path="/api/cart",
    *      tags={"Cart"},
    *      summary="Get The user's cart",
    *      description="return user's cart",
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
    public function getUserCart(Request $request){
        try{
            
            /** @var User $user description */
            $user = Auth::user();
            // dd($user);
            if($user->IsAdministrator()){
                return ApiResponse::error("Administrator have not Cart",400);
            }
            // dd($user->carts->sortBy("created_at")->last());
            $cart = $user->getCart();
            

            return ApiResponse::success($cart);
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }

    }


    /**
     * add item to cart.
     */
    /**
     * @OA\Post(
     *     path="/api/cart/add",
     *     tags={"Cart"},
     *     summary="Add items to cart",
     *     description="Add one or many place event to cart.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 minItems=1,
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="type_place_id",
     *                         type="integer",
    *                          example="1",
     *                         description="ID place à ajouter"
     *                     ),
     *                     @OA\Property(
     *                         property="nombre",
     *                         type="integer",
     *                         description="Le nombre de places à ajouter",
    *                          example="1"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ajoutés au panier avec succès"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Événement non trouvé"
     *     )
     * )
     */
    public function store(Request $request)
    {
        try {
            /** @var User $user description */
            $user= Auth::user();
            if($user->IsAdministrator()){
                return ApiResponse::error("Administrator have not Cart",400);
            }

            $data = $request->validate([
               "items"=>["required","array","min:1"],
               "items.*.type_place_id"=>["required","integer","exists:type_places,id"],
               "items.*.nombre"=>["required","integer","min:1"] 
            ]);
            // $price = 0;

            /** @var Cart $cart description */
            
            $cart = $user->getCart();
            $storedItems= $cart->items;
            
            $event_id=null;
            
            $items= [];
            for ($i=0;$i<count($data["items"]);$i++){
                // verify that event is published
                $type_place= TypePlace::find($data["items"][$i]["type_place_id"]);
                $event_id= $type_place->event->id;
                if($type_place->event->isPublished()){
                    
                    //update if place already in the cart
                    if($storedItems->contains("type_place_id",$data["items"][$i]["type_place_id"])){
                        foreach ($storedItems as $storedItem) {
                            // dd("contain's the id");
                            if($storedItem["type_place_id"]==$data["items"][$i]["type_place_id"]){
                                $item = Item::find($storedItem->id);

                                $item->update([
                                    "nombre"=> $item->nombre + $data["items"][$i]["nombre"]
                                ]);
                                $item->save();
                                
                                array_push($items,$item);
                            }
                        }
                    }
                    else{
                        $item = Item::create([
                            "nombre"=>$data["items"][$i]["nombre"],
                            "type_place_id"=>$data["items"][$i]["type_place_id"],
                            "cart_id"=>$cart->id,
                        ]);
                        
                        array_push($items,$item);
                    }
                    
                }
                else{
                    return ApiResponse::error("Request not accepted",403,"You try to add cart event not published yet");
                }
                    
            }

            $cart->updatePrice(true);

            $cart->event_id = $event_id;
            $cart->save();

            return ApiResponse::success([],"Items add to current cart");  
        } catch (Exception $e) {
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
     *     path="/api/cart/update",
     *     tags={"Cart"},
     *     summary="Update cart",
     *     description="Update one or many place event of cart.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 minItems=1,
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="type_place_id",
     *                         type="integer",
    *                          example="1",
     *                         description="ID place à modifier"
     *                     ),
     *                     @OA\Property(
     *                         property="nombre",
     *                         type="integer",
    *                          example="2",
     *                         description="Le nombre de places à modifier"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Modification panier succès"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Action non authoriser"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Action interdit"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Événement non trouvé"
     *     )
     * )
     */
    public function update(Request $request)
    {
        try {
            /** @var User $user description */
            $user= Auth::user();

            if($user->IsAdministrator()){
                return ApiResponse::error("Administrator have not Cart",400);
            }

            $data = $request->validate([
               "items"=>["required","array","min:1"],
               "items.*.type_place_id"=>["required","integer","exists:type_places,id"],
               "items.*.nombre"=>["required","integer","min:1"] 
            ]);


            /** @var Cart $cart description */
            $cart = $user->getCart();

            
            
            //clear cart items
            if($cart->clear()){
                $items= [];
                for ($i=0;$i<count($data["items"]);$i++){
                    // dd($data["items"][$i]);
                    $item = Item::create([
                        "nombre"=>$data["items"][$i]["nombre"],
                        "type_place_id"=>$data["items"][$i]["type_place_id"],
                        "cart_id"=>$cart->id,
                    ]);
                    $type_place = TypePlace::find($data["items"][$i]["type_place_id"]);
                    $event_id = $type_place->event->id;
                    array_push($items,$item);
                }

                $cart->updatePrice(true);
                
                $cart->event_id = $event_id;
                $cart->save();

                return ApiResponse::success($items,"Items updated successful");
            }
            else{
                return ApiResponse::error("Error updating card",500,"Suppression items failed");
            }
            
        } catch (Exception $e) {
            return ApiResponse::error("server error",500,$e->getMessage());
        }        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

        
    /**
    * @OA\Delete(
    *      path="/api/cart/clear",
    *      tags={"Cart"},
    *      summary="Clear The user's cart",
    *      description="Remove all item of user's cart",
    *          @OA\Response(
    *              response=200,
    *              description="successful operation"
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
    public function clear(Request $request){
        try{

            /** @var User $user description */
            $user = Auth::user();
            
            if($user->IsAdministrator()){
                return ApiResponse::error("Administrator have not Cart",400);
            }
            
            /** @var Cart $cart description */
            $cart= $user->getCart();
            $cart->clear();
            $cart->updatePrice();
            return ApiResponse::success([],"Cart clear successful");
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());     
        }
        
    }



    /**
     * @OA\Delete(
     *     path="/api/cart/remove/item",
     *     tags={"Cart"},
     *     summary="Remove Item",
     *     description="Remove Item in the user's cart.",
     *      @OA\RequestBody(
    *          required=true,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(
    *                  type="object",
    *                  @OA\Property(
    *                      property="type_place_id",
    *                      type="integer",
    *                      example="1"
    *                  )
    *              )
    *          )
    *      ),
     *     @OA\Response(
     *         response=200,
     *         description="operation succès"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Action non authoriser"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Action interdit"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type Place non trouvé"
     *     )
     * )
     */
    public function removeItem(Request $request){
        try{
            /** @var User $user description */
            $user = Auth::user();
            
            if($user->IsAdministrator()){
                return ApiResponse::error("Administrator have not Cart",400);
            }
            

            $data = $request->validate([
                "type_place_id"=>["required","integer","exists:type_places,id"]
            ]);

            // check if item in cart's item 
            /** @var Cart $cart description */
            $cart= $user->getCart();
            $storedItems= $cart->items;
            if($storedItems->contains("type_place_id",$data["type_place_id"])){
                foreach ($storedItems as $storedItem) {
                    if($storedItem["type_place_id"]==$data["type_place_id"]){
                        $item = Item::find($storedItem->id);
                        $item->delete();
                        break;
                    }
                }
                
                return ApiResponse::success([],"Item remove successfuly");
            }
            else{
                return ApiResponse::error("Type place not in cart",404);
            }
        }catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }

    /**
    * @OA\Post(
    *      path="/api/cart/pay",
    *      tags={"Cart"},
    *      summary="Pay the cart",
    *      description="Process Cart Payment",
    *      @OA\RequestBody(
    *          required=false,
    *          @OA\MediaType(
    *              mediaType="application/json",
    *              @OA\Schema(
    *                  type="object",
    *                  @OA\Property(
    *                      property="code",
    *                      type="string",
    *                      example="qskj34qsd"
    *                  )
    *              )
    *          ),
    *           ),
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
    *   )  
    */
    public function pay(Request $request){
        try{
            
            /** @var User $user description */
            $user = Auth::user();
            if($user->IsAdministrator()){
                return ApiResponse::error("Administrator have not Cart",400);
            }            
            
            $cart = $user->getCart();
            
            $cart->updatePrice(true);

            if(count($cart->items)==0){
                return ApiResponse::error("You must add one or more item to cart before purchased it.",401);
            }
            
            $event_id= $this->validateItemsNumber($cart);

            if($event_id != false){
                $dataUpdated["status"]="purchased";

                if(count($request->all())>0){
                    $reduction = $this->evaluate($request,false);
                    if($reduction["event_id"] == $event_id){
                        $dataUpdated["code_id"]= $reduction["code_id"];
                    }
                }

                $tickets = $this->generateTicketEachItems($cart,$user->id);
                
                $ticket= Ticket::with("type_place")->where("id",$tickets[0]->id)->first();
                
                $dataUpdated["event_id"]=(int)$event_id;

                // dd($dataUpdated);
                
                $cart->update($dataUpdated);
                $cart->save();
                
                // create new empty cart
                $newCart = Cart::create([
                    "status"=>"created",
                    "montant"=>0,
                    "user_id"=>$user->id
                ]);
        
                return ApiResponse::success(["tickets"=>$tickets],"Cart Purchased Successful.");
            }
            else{
                return ApiResponse::error("Error Purchase",401,"One of your type place event have less number free that you need! Or All Place come from one event.");
            }
        }
        catch(Exception $e){
            return ApiResponse::error("Error Purchase Cart",500,$e->getMessage());
        }

    }

    private function validateItemsNumber(Cart $cart):bool|string{
        try{
            $items = $cart->items;
            $events_id = [];
    
            foreach ($items as $item) {
                $event_id= $item->type_place->event_id;

                if(!in_array($event_id,$events_id)){
                    array_push($events_id,$event_id);
                }

                if($item->nombre > $item->type_place->nombre_place_disponible){
                    return false;
                }
            }
    

            if(count($events_id)>1){
                return false;
            }

            return $events_id[0];

            // return true;

        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }
    }


    private function generateTicketEachItems(Cart $cart,string $customer_id){
        try{
            $items = $cart->items;
    
            $tickets=[];
            foreach ($items as $item) {
                for($i=0;$i < $item->nombre;$i++){
                    $reference = $item->type_place->generateReference();
                    
                    $ticket = Ticket::create([
                        "user_id"=>$customer_id,
                        "type_place_id"=>$item->type_place->id,
                        "reference"=>$reference
                    ]);

                    array_push($tickets,$ticket);
                }
            }
    
            return $tickets;
        }
        
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }

    }



        
    /**
    * @OA\Post(
    *      path="/api/cart/evaluation",
    *      tags={"Cart"},
    *      summary="Evaluation Cart ",
    *      description="Evaluation Cart Before Payment with code promo",
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
    *                  )
    *              )
    *          )
    *      ),
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
    public function evaluate(Request $request,bool $api_response=true){
        try{
            
            $data = $request->validate([
                "code"=>["required","string","exists:codes,code"]
            ]);
    
            $code = Code::where("code",$data["code"])->get()[0];


            /** @var User $user description */
            $user = Auth::user();
            /** @var Cart $cart description */
            $cart= $user->getCart();

            if($code->event_id != $cart->event_id){
                return ApiResponse::error("Code Invalid, code for other event",401);
            }

            $res["montant"]=$cart->montant;
            $res["pourcentage"]=$code->price;
            $res["event_id"] = $code->event_id;
            $res["code_id"]=$code->id;
            $res["montant_bonus"]= ($cart->montant * ($code->price/100));
            $res["montant_reduite"]= $cart->montant - ($cart->montant * ($code->price/100));
    
            if($api_response){
                return ApiResponse::success($res,"Evaluation with code");
            }
            return $res;
        }
        catch(Exception $e){
            return ApiResponse::error("Error Evaluation",500,$e->getMessage());
        }

    }

        
    /**
    * @OA\Get(
    *      path="/api/pay/history",
    *      tags={"Cart"},
    *      summary="Get payment history",
    *      description="return user's payment history",
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
    public function payHistory(Request $request){
        try{
            
            /** @var User $user description */
            $user = Auth::user();

            if(!$user->IsCustomer()){
                $carts= Cart::where("status","purchased")
                        ->with("code","user","items","items.type_place","event")
                        ->orderBy('created_at', 'asc')        
                        ->get();
                

                if(!$carts->isEmpty()){
                    if($user->IsOrganiser()){
                        $res=[];
                        foreach ($carts as $cart) {
                            if($cart->organiser_id==$user->id){
                                array_push($res,$cart);
                            }
                        }
                        $carts= $res;
                    }

                }
                    
            }
            else{
                $carts= $carts= Cart::where("status","purchased")
                            ->orderBy('created_at', 'asc')
                            ->with("event")
                            ->where("user_id",$user->id)
                            ->get();
            }
            
            if(count($carts)==0){
                return ApiResponse::error("no payment done actually",404);
            }            
            
            return ApiResponse::success($carts);
            
        }
        catch(Exception $e){
            return ApiResponse::error("server error",500,$e->getMessage());
        }

    }



}

