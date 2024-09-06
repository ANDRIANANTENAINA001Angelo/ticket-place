<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        "created_at",
        "updated_at",        
        "type_place_id",
        "user_id"
    ];

    

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

}
