<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Code extends Model
{
    use HasFactory;

    protected $fillable= [
        "code",
        "price",
        "expire_at",
        "user_id"
    ];

    protected $hidden=[
        "created_at",
        "updated_at",
        "user_id"      
    ];

    protected $appends = ["is_expire"];


    public function getIsExpireAttribute():bool
    {
        // dd($this->expire_at > Carbon::now()->toDateString());
        if($this->expire_at < Carbon::now()->toDateString()){
            return true;
        }
        return false;
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
     * Get the user that owns the Code
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
