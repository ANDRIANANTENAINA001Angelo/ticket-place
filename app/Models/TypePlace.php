<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypePlace extends Model
{
    use HasFactory;

    protected $fillable = [
        "nom",
        "nombre",
        "is_limited",
        "prix",
        "event_id"
    ];

    protected $hidden=[
        "is_limited"
    ];

    protected $appends = ['nombre_place_disponible'];

    public function getFreePlaceNumber():int{
        $type_place = TypePlace::find($this->id);
        if($type_place->is_limited){
            $freePlaceNumber = $type_place->nombre - count($type_place->tickets);
            return $freePlaceNumber;
        }
        else{
            return 9999999999999;
        }
    }

    public function getNombrePlaceDisponibleAttribute(){
        return $this->getFreePlaceNumber();
    }

    public function generateReference():string{
        $type_place = TypePlace::with(["event","tickets"])->where("id","=",$this->id)->limit(1)->get()[0];
        // dd($type_place);

        $ticketNumber="00";
        // count increment ticket
        if($type_place->is_limited){
            $ticketNumber .= ($type_place->nombre - $type_place->nombre_place_disponible) +1;
        }
        else{
            $ticketNumber .= count($type_place->tickets) +1;
        }


        return "T00" . $type_place->event->id . $type_place->nom . $ticketNumber ;
    }


    /**
     * Get all of the tickets for the TypePlace
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }



    /**
     * Get the event that owns the TypePlace
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
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
}
