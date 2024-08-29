<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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


    /**
     * Get the event that owns the TypePlace
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

}
