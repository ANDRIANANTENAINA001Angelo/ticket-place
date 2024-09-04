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

class Cart extends Model
{
    use HasFactory;

    protected $fillable= [
        "status",
        "montant",
        "user_id"
    ];

    protected $hidden=[
        'user_id',
    ];



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
