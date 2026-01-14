<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we need to recreate the table to change column constraints
        if (Schema::hasColumn('students', 'user_id')) {
            DB::statement('PRAGMA foreign_keys=off');
            
            DB::statement('CREATE TABLE students_temp AS SELECT * FROM students');
            
            Schema::dropIfExists('students');
            
            Schema::create('students', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
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
            
            DB::statement('INSERT INTO students SELECT * FROM students_temp');
            DB::statement('DROP TABLE students_temp');
            
            DB::statement('PRAGMA foreign_keys=on');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible for SQLite
        // Would need similar table recreation to make user_id NOT NULL again
    }
};
