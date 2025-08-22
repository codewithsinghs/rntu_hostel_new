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
        Schema::create('accessory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accessory_head_id')->constrained('accessory_heads')->onDelete('cascade');
            $table->decimal('price', 8, 2);
            $table->boolean('is_default')->default(false);
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
        
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accessory');
    }
};
