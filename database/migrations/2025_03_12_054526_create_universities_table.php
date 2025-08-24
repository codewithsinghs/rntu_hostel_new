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
        Schema::create('universities', function (Blueprint $table) {
            $table->id()->unique(); 
            $table->string('name'); 
            $table->string('location'); 
            $table->string('state')->index(); 
            $table->string('district')->index(); 
            $table->string('pincode', 10)->index()->unique(); 
            $table->text('address'); 
            $table->string('mobile', 15)->unique(); 
            $table->string('email')->unique(); 
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable(); 
        });  
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};
