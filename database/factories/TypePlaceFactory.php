<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TypePlace>
 */
class TypePlaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [  
            "nom"=>fake()->name(),
            "nombre"=> 0,
            "is_limited"=>false,
            "prix"=>5000,
            "event_id"=>1
        ];
    }
}
