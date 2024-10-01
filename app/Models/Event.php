<?php

namespace App\Models;

use App\FileManip;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    public const STATUS_CREATED="Créer";
    public const STATUS_PENDING="En Attente";
    public const STATUS_PUBLISHED="Publier";
    public const STATUS_UNAPPROVAL="Non Approuvé";
    public const STATUS_FINISHED="Terminer";




    protected $fillable=[
        "titre",
        "description",
        "localisation",
        "date",
        "heure",
        "status",
        "user_id",
        "tag_id",
        "image"
    ];

    
// Casts pour gérer les types (conversions automatiques)
protected $casts = [
    'date' => 'date', // Par défaut, cast en objet Carbon
    'heure' => 'datetime:H:i' // Convertit en objet Carbon pour l'heure
    
];

// protected $appends =["tickets"];

// Accessor pour formater la date
public function getDateAttribute($value)
{
    return Carbon::parse($value)->translatedFormat('d F Y'); // "24 Aout 2024"
}



// Accessor pour formater l'heure
public function getHeureAttribute($value)
{
    return Carbon::parse($value)->format('H:i'); // "19:00"
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


    /**
     * Get all of the Codes for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function codes(): HasMany
    {
        return $this->hasMany(Code::class);
    }

    protected $hidden=[
        // "status"
    ];
    /**
     * Get the user that owns the Event
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The Tags that belong to the Event
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }

    /**
     * Get all of the typePlaces for the Event
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function type_places(): HasMany
    {
        return $this->hasMany(TypePlace::class);
    }

    public function IsPublished():bool{
        if($this->status==$this::STATUS_PUBLISHED){
            return true;
        }
        return false;
    }

    public function IsFinished():bool{
        if($this->status==$this::STATUS_FINISHED){
            return true;
        }
        return false;
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? FileManip::PathToUrl($value) : null,
        );
    }

    public function getTicketsAttribute(){
        $tickets=[];
        /** @var TypePlace $type_place description */
        $type_places = $this->type_places;
        foreach ($type_places as $type_place) {
            if(count($type_place->tickets)!=0){
                array_push($tickets,$type_place->tickets);
            }
        }

        return $tickets;
    }

}
