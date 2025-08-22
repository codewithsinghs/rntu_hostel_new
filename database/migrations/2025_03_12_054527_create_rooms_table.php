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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number');
            $table->foreignId('building_id')->constrained()->onDelete('cascade');
            $table->integer('floor_no'); // New field for floor number
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available'); // New field for status
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
