<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "titre"=> fake()->name(),
            "description"=>fake()->words(3),
            "localisation"=>fake()->address(),
            "date"=> now()->addDays()->toString(),
            "heure"=> "20:00",
            "status"=> Event::STATUS_FINISHED,
            "user_id"=> 1,
            "tag_id"=> 1,
            "image"=> null
        ];
    }
}
