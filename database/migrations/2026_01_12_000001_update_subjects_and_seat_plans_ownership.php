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
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->onDelete('cascade');
        });

        Schema::table('seat_plans', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade');
            // Make section_id nullable if we want to allow seat plans directly on subjects without sections
            $table->foreignId('section_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
            $table->dropColumn('teacher_id');
        });

        Schema::table('seat_plans', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
            // We can't easily revert nullable change in SQLite generally, but for structure it's fine.
        });
    }
};
