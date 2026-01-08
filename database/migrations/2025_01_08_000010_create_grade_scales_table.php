<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('academic_year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('grade_scale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_scale_id')->constrained()->onDelete('cascade');
            $table->string('letter_grade');
            $table->decimal('min_percentage', 5, 2);
            $table->decimal('max_percentage', 5, 2);
            $table->decimal('gpa_points', 3, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['grade_scale_id', 'letter_grade']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_scale_items');
        Schema::dropIfExists('grade_scales');
    }
};
