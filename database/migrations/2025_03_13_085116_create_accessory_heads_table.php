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
        Schema::create('accessory_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., Mattress, Table, Fan
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessory_heads');
    }
};
