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

    protected $fillable=[
        "titre",
        "description",
        "localisation",
        "date",
        "heure",
        "status",
        "user_id",
        "image"
    ];

    
// Casts pour gérer les types (conversions automatiques)
protected $casts = [
    'date' => 'date', // Par défaut, cast en objet Carbon
    'heure' => 'datetime:H:i' // Convertit en objet Carbon pour l'heure
    
];

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
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
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
        if($this->status=="published"){
            return true;
        }
        return false;
    }

    public function isFinished():bool{
        if($this->status=="finished"){
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

}
