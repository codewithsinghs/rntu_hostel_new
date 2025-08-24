<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('messes', function (Blueprint $table) {
            $table->id();
    
            // Relationships
            $table->unsignedBigInteger('user_id')->nullable(); // For residents (auth users)
            $table->unsignedBigInteger('resident_id')->nullable(); // Resident table FK
            $table->unsignedBigInteger('guest_id')->nullable(); // For guests
            $table->unsignedBigInteger('building_id')->nullable();
            $table->unsignedBigInteger('university_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
    
            // Mess Details
            $table->enum('food_preference', ['veg', 'non_veg']);
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->date('due_date')->nullable();
    
            $table->timestamps();
    
            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('resident_id')->references('id')->on('residents')->onDelete('cascade');
            $table->foreign('guest_id')->references('id')->on('guests')->onDelete('set null');
            $table->foreign('building_id')->references('id')->on('buildings')->onDelete('set null');
            $table->foreign('university_id')->references('id')->on('universities')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }
    


    public function down()
    {
        Schema::dropIfExists('messes');
    }
};
