<?php

declare(strict_types=1);

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
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leg_id')->constrained()->cascadeOnDelete();
            $table->char('origin', 3);
            $table->char('destination', 3);
            $table->dateTime('departure');
            $table->dateTime('arrival');
            $table->string('cabin_class', 2);
            $table->char('airline', 2);
            $table->string('flight_number', 10);
            $table->unsignedSmallInteger('position');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segments');
    }
};
