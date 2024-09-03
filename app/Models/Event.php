<?php

namespace App\Models;

use App\FileManip;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Event extends Model
{
    use HasFactory;

    protected $fillable=[
        "titre",
        "description",
        "localisation",
        "date",
        "status",
        "user_id",
        "image"
    ];

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

    public function isPublished():bool{
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
