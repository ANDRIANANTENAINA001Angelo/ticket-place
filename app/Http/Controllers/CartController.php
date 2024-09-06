<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Models\Cart;
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
            
            $items= [];
            for ($i=0;$i<count($data["items"]);$i++){
                // verify that event is published
                $type_place= TypePlace::find($data["items"][$i]["type_place_id"]);
                if($type_place->event->isPublished()){
                    
                    //update if place already in the cart
                    if($storedItems->contains("type_place_id",$data["items"][$i]["type_place_id"])){
                        foreach ($storedItems as $storedItem) {
                            // dd("contain's the id");
                            if($storedItem["type_place_id"]==$data["items"][$i]["type_place_id"]){
                                $item = Item::find($storedItem->id);

                                // $price += $item->type_place->prix * $item->nombre;

                                $item->update([
                                    "nombre"=> $item->nombre + $data["items"][$i]["nombre"]
                                ]);
                                $item->save();

                                
                                array_push($items,$item);
                            }
                        }
                    }
                    else{
                        // dd("don't contain's the id");
                        $item = Item::create([
                            "nombre"=>$data["items"][$i]["nombre"],
                            "type_place_id"=>$data["items"][$i]["type_place_id"],
                            "cart_id"=>$cart->id,
                        ]);
                        
                        array_push($items,$item);
                        // dd("don't contain's the id, and created");
                    }
                    
                }
                else{
                    return ApiResponse::error("Request not accepted",403,"You try to add cart event not published yet");
                }
                    
            }
            $cart->updatePrice(true);
            // dd("Price updated");
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
                    array_push($items,$item);
                }

                $cart->updatePrice();
                
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
    public function pay(Request $request){
        try{
            
            /** @var User $user description */
            $user = Auth::user();
            if($user->IsAdministrator()){
                return ApiResponse::error("Administrator have not Cart",400);
            }
            
            $cart = $user->getCart();
    
            if(count($cart->items)==0){
                return ApiResponse::error("You must add one or more item to cart before purchased it.",401);
            }

            if($this->validateItemsNumber($cart)){
                $tickets = $this->generateTicketEachItems($cart,$user->id);
        
                $cart->update(["status"=>"purchased"]);
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
                return ApiResponse::error("Error Purchase",401,"One of your type place event have less number free that you need!");
            }
        }
        catch(Exception $e){
            return ApiResponse::error("Error Purchase Cart",500,$e->getMessage());
        }

    }

    private function validateItemsNumber(Cart $cart):bool{
        try{
            $items = $cart->items;
    
            foreach ($items as $item) {
                if($item->nombre > $item->type_place->nombre_place_disponible){
                    return false;
                }
            }
    
            return true;

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


}
