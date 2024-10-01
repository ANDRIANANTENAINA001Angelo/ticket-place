<?php

use App\Models\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('type_places', function (Blueprint $table) {
            $table->id();
            $table->string("nom");
            $table->integer("nombre")->default(100);
            $table->boolean("is_limited")->default(false);
            $table->integer("prix");
            $table->foreignIdFor(Event::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('type_places');
    }

};
