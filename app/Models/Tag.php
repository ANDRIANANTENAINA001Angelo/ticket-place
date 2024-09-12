<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{
    use HasFactory;

    protected $fillable=[
        "label"
    ];


    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
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
