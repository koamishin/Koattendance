<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $table) {
                if (!Schema::hasColumn('grades', 'student_name')) {
                    $table->string('student_name')->nullable();
                }
                if (!Schema::hasColumn('grades', 'grade')) {
                    $table->decimal('grade', 5, 2)->nullable();
                }
                if (!Schema::hasColumn('grades', 'subject_id')) {
                    $table->foreignId('subject_id')->nullable()->constrained('subjects')->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('grades')) {
            Schema::table('grades', function (Blueprint $table) {
                $table->dropForeignIdFor('Subject', 'subject_id');
                $table->dropColumn(['subject_id', 'student_name', 'grade']);
            });
        }
    }
};
