<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Ticket extends Model
{
    use HasFactory;


    protected $fillable= [
        "reference",
        "type_place_id",
        "user_id"
    ];


    protected $hidden= [
        "id",
        // "created_at",
        "updated_at",        
        "type_place_id",
        "type_place",
        "user_id"
    ];

    protected $appends=[
        "ticket",
        // "event"
        "title",
        "date",
        "heure"
    ];

    protected $casts=[
        "created_at"=>"date"
    ];


    

    public function getTicketAttribute(){
        return $this->type_place->nom;
    }

    // public function getEventAttribute(){
    //     return Event::find($this->type_place->event_id);
    // }

    public function getTitleAttribute(){
        $event= Event::find($this->type_place->event_id);
        return $event->titre;
    }

    public function getDateAttribute(){
        $event= Event::find($this->type_place->event_id);
        return $event->date;
    }

    public function getHeureAttribute(){
        $event= Event::find($this->type_place->event_id);
        return $event->heure;
    }

    

    /**
     * Get the type_place that owns the Ticket
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type_place(): BelongsTo
    {
        return $this->belongsTo(TypePlace::class);
    }

    /**
     * Get the user that owns the Ticket
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    // Accessor pour formater created_at
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->translatedFormat('d F Y'); 
    }

}
