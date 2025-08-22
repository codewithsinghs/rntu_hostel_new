<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('guest_accessory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');
            $table->foreignId('accessory_head_id')->constrained('accessory')->onDelete('cascade');
            $table->decimal('price', 10, 2); // Price per unit
            $table->decimal('total_amount', 10, 2); // Total amount for the accessory
            $table->date('from_date'); // Start date of accessory usage
            $table->date('to_date'); // End date of accessory usage
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
        });
    }
    
    
    public function down()
    {
        Schema::dropIfExists('guest_accessory');
    }
    
};
