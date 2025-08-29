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
            $table->string('category_id')->nullable();
            $table->string('frequency')->nullable();
            $table->string('unit_value')->nullable();
            $table->string('unit_type')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
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
