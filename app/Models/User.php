<?php

namespace App\Models;

use App\ApiResponse;
use App\FileManip;
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
use Illuminate\Database\Eloquent\Casts\Attribute;

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
        "image",
        "google_id",
        "avatar"
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


    public function isEmailVerified(){
        if(is_null($this->email_verified_at)){
            return false;
        }
        return true;
    }


    // protected function image(string $value): Attribute
    // {
    //     if(is_null($this->google_id)){
    //         return Attribute::make(
    //             get: fn (?string $value) => $value ? FileManip::PathToUrl($value) : null,
    //         );
    //     }
    //     return $value;
    // }
    protected function image(): Attribute
{
    return Attribute::make(
        get: function (?string $value) {
            // Si google_id est null, traiter l'image avec FileManip
            if (is_null($this->google_id)) {
                return $value ? FileManip::PathToUrl($value) : null;
            }
            // Sinon, retourner la valeur de l'image telle qu'elle est dans la base de données
            return $value;
        }
    );
}


    /**
     * Get all of the tickets for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
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
