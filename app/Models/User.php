<?php

namespace App\Models;

use App\ApiResponse;
use Exception;
use Illuminate\Auth\MustVerifyEmail as AuthMustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, AuthMustVerifyEmail;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fname',
        'lname',
        "phone",
        "type",
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];



    /**
     * Get all of the Codes for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function codes(): HasMany
    {
        return $this->hasMany(Code::class);
    }

    /**
     * Get all of the carts for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }


    // public function getCart(){
    //     return $this->carts->sortBy("created_at")->last()->toArray();
    // }
    public function getCart()
    {
        // Obtenez le dernier panier avec ses items
        $cart=  $this->carts()
                    ->with('items') // Charger les items associés
                    ->orderBy('created_at', 'desc')// Trier par date de création décroissante
                    ->first(); // Récupérer le dernier panier

        // $cart["items"]= $cart->items;
        return $cart ;
        // return $cart ? $cart->toArray() : null;
    }
 
    // Accessor pour formater created_at
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans(); 
    }

    // Accessor pour formater updated_at
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->diffForHumans(); 
    }



    public function IsOrganiser():bool{
        if($this->type=="organiser"){
            return true;
        }
        else{
            return false;
        }
    }
     
    public function IsCustomer():bool{
        if($this->type=="customer"){
            return true;
        }
        else{
            return false;
        }
    }

    public function IsAdministrator():bool{
        if($this->type=="administrator"){
            return true;
        }
        else{
            return false;
        }
    }




}
