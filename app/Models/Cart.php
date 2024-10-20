<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Carbon\Carbon;

class Cart extends Model
{
    use HasFactory;

    protected $fillable= [
        "status",
        "montant",
        "user_id",
        "code_id",
        "event_id"
    ];

    protected $hidden=[
        "id",
        'user_id',
        "created_at",
        // "updated_at",
        "status",
        // "montant_reduite",
        "code_id",
        "organiser_id",
        "event_id"
    ];

    
    protected $appends =["montant_reduite","organiser_id"];

    public function getMontantReduiteAttribute(){
        if(isset($this->code_id)){
            $code = Code::find($this->code_id);
            $price = $this->montant - ($this->montant * $code->price);
            return $price;
        }
        else{
            return $this->montant;
        }
    }

    // Accessor pour formater updated_at
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans(); 
    }

    /**
     * Get the user that owns the Cart
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Get the code that owns the Cart
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function code(): BelongsTo
    {
        return $this->belongsTo(Code::class);
    }
    /**
     * Get all of the items for the Cart
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function updatePrice(bool $refresh=false){
        if(!$refresh){
            $items= $this->items;
        }
        else{
            $cart = Cart::find($this->id);
            $items= $cart->items;
        }
        $price= 0;
        // dd($items);
        foreach ($items as $item) {
            /** @var Item $item description */
            // dd($item->type_place->prix * $item->nombre);
            $price += $item->type_place->prix * $item->nombre;
        }
        
        $this->update(["montant"=>$price]);
        $this->save();
        // dd($price);
        // dd($this);

    }

    public function getOrganiserIdAttribute(){
        if (count($this->items)>0){
            $event = Event::find($this->items[0]->type_place->event_id);
            return $event->user_id; 
        }
        else{
            return null;
        }
    }

    public function clear(){
        try{
            /** @var User $user description */
            $user = Auth::user();
            $cart = $user->getCart();
            $items= $cart->items;
            foreach ($items as $item) {
                /** @var Item $item description */
                $item->delete();
            }
            return true;
        }
        catch(Throwable $th){
            throw new Exception("Error deleting items's of cart",1,$th);
        }
    }

}
