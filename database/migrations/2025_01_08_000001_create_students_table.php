<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('student_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('guardian_id')->nullable()->constrained('guardians')->onDelete('set null');
            $table->integer('current_grade_level')->default(1);
            $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('set null');
            $table->string('status')->default('active');
            $table->date('enrollment_date')->nullable();
            $table->text('qr_code_data')->nullable();
            $table->boolean('qr_code_active')->default(true);
            $table->timestamp('qr_code_regenerated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
