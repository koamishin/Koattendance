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
        if (Schema::hasColumn('guardians', 'user_id')) {
            DB::statement('PRAGMA foreign_keys=off');
            
            DB::statement('CREATE TABLE guardians_temp AS SELECT * FROM guardians');
            
            Schema::dropIfExists('guardians');
            
            Schema::create('guardians', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('first_name');
                $table->string('last_name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('relationship')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->json('alert_preferences')->nullable();
                $table->timestamps();
            });
            
            DB::statement('INSERT INTO guardians SELECT * FROM guardians_temp');
            DB::statement('DROP TABLE guardians_temp');
            
            DB::statement('PRAGMA foreign_keys=on');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible for SQLite
    }
};
