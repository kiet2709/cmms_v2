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
        Schema::create('working_instructions', function (Blueprint $table) {
            $table->uuid();
            $table->string('code')->unique();  // mã code để user nhận diện
            $table->string('name')->nullable();
            $table->string('type');            // daily_inspection, maintenance_level_1, ...
            $table->json('schema');            // JSON form
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('working_instructions');
    }
};
