<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    protected $fillable=[
        "nombre",
        "cart_id",
        "type_place_id"
    ];

    /**
     * Get the cart that owns the Item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    
    /**
     * Get the type_place that owns the Item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type_place(): BelongsTo
    {
        return $this->belongsTo(TypePlace::class);
    }


}
