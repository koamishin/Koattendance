<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seat_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->json('layout')->nullable();
            $table->timestamps();
        });

        Schema::create('seat_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seat_plan_id')->constrained('seat_plans')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->integer('row')->nullable();
            $table->integer('column')->nullable();
            $table->string('seat_label')->nullable();
            $table->timestamps();

            $table->unique(['seat_plan_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seat_allocations');
        Schema::dropIfExists('seat_plans');
    }
};
