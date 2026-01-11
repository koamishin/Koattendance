<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('attendance_records')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                if (!Schema::hasColumn('attendance_records', 'student_id')) {
                    $table->foreignId('student_id')->nullable()->constrained('students')->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('attendance_records')) {
            Schema::table('attendance_records', function (Blueprint $table) {
                if (Schema::hasColumn('attendance_records', 'student_id')) {
                    $table->dropForeignIdFor('Student');
                    $table->dropColumn('student_id');
                }
            });
        }
    }
};
