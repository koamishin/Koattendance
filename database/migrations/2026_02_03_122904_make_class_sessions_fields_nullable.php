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
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->change();
            $table->foreignId('section_id')->nullable()->change();
            
            if (!Schema::hasColumn('class_sessions', 'subject_id')) {
                $table->foreignId('subject_id')->nullable()->constrained()->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable(false)->change();
            $table->foreignId('section_id')->nullable(false)->change();
            $table->dropConstrainedForeignId('subject_id');
        });
    }
};
