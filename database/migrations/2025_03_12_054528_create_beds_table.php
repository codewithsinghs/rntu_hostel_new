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
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->string('bed_number');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['available', 'occupied', 'maintenance'])->default('available'); // New status field
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};


