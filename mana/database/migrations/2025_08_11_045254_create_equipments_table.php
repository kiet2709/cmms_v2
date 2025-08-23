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
        Schema::create('equipments', function (Blueprint $table) {
            $table->uuid();
            $table->string('machine_id'); // Unique machine identifier
            $table->string('family')->nullable(); // Product/Equipment family
            $table->string('model')->nullable(); // Machine model
            $table->string('cavity')->nullable(); // Number/type of cavities
            $table->string('manufacturer')->nullable(); // Manufacturer name
            $table->date('manufacturing_date')->nullable(); // Date of manufacturing
            $table->unsignedBigInteger('history_count')->nullable(); // Operation count
            $table->string('unit')->nullable(); // Unit of measurement (shot, hour...)
            $table->string('category_id')->nullable();
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
        Schema::dropIfExists('equipments');
    }
};
