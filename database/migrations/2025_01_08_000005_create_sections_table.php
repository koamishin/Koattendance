<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('grade_level');
            $table->string('academic_year');
            $table->string('semester')->default('first');
            $table->foreignId('advisor_id')->nullable()->constrained('teachers')->onDelete('set null');
            $table->integer('max_students')->default(30);
            $table->json('schedule_template')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
