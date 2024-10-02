<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Cart;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'fname' => 'Admin',
            "lname"=>"SuperAdmin",
            'email' => 'admin@admin.com',
            'type' => "administrator"
        ]);

        // $customer= \App\Models\User::factory()->create([
        //     'fname' => fake()->name(),
        //     'email' => 'customer@customer.com',
        //     'type' => "customer"
        // ]);

        // $organiser = \App\Models\User::factory()->create([
        //     'fname' => fake()->name(),
        //     'email' => 'organiser@organiser.com',
        //     'type' => "organiser"
        // ]);

        // $cart = Cart::create([
        //     "status"=>"created",
        //     "montant"=>0,
        //     "user_id"=>$customer->id
        // ]);

        // $cart = Cart::create([
        //     "status"=>"created",
        //     "montant"=>0,
        //     "user_id"=>$organiser->id
        // ]);

        \App\Models\Tag::factory()->create([
            "label"=>"Théatre"
        ]);
        
        \App\Models\Tag::factory()->create([
            "label"=>"Spéctacle"
        ]);
        \App\Models\Tag::factory()->create([
            "label"=>"Concert"
        ]);
        \App\Models\Tag::factory()->create([
            "label"=>"Cinéma"
        ]);
        \App\Models\Tag::factory()->create([
            "label"=>"Foire"
        ]);
        \App\Models\Tag::factory()->create([
            "label"=>"Séminaire"
        ]);
        \App\Models\Tag::factory()->create([
            "label"=>"Culture"
        ]);
        \App\Models\Tag::factory()->create([
            "label"=>"Sport et Loisir"
        ]);
        \App\Models\Tag::factory()->create([
            "label"=>"Festival"
        ]);
        \App\Models\Tag::factory()->create([
            "label"=>"Autre"
        ]);

    }
}
