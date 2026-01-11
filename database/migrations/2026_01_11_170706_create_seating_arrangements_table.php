<?php

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
        Schema::create('seating_arrangements', function (Blueprint $table) {
            $table->id();
            $table->string('room')->unique()->default('301');
            $table->integer('rows')->default(4);
            $table->integer('columns')->default(4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seating_arrangements');
    }
};
