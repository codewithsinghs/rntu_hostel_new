<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
        
        public function up() {
            Schema::create('student_accessory', function (Blueprint $table) {
                $table->id();
                $table->foreignId('resident_id')->constrained('residents')->onDelete('cascade');
                $table->foreignId('accessory_head_id')->constrained('accessory')->onDelete('cascade'); // Ensure table name is correct
                $table->decimal('price', 8, 2);
                $table->decimal('total_amount', 10, 2)->default(0); // Add total_amount column
                $table->date('from_date'); 
                $table->date('to_date'); 
                $table->date('due_date'); 
                $table->timestamps();
                $table->unsignedBigInteger('created_by')->nullable();
            });
            
        }
        

    public function down() {
        Schema::dropIfExists('student_accessory');
    }
};

