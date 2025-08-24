<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up() {
            Schema::create('leave_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('resident_id')->constrained('residents')->onDelete('cascade');
                $table->date('from_date');
                $table->date('to_date');
                $table->text('reason');
                $table->string('photo')->nullable(); // Add this line
                $table->enum('hod_status', ['pending', 'approved', 'denied'])->default('pending');
                $table->enum('admin_status', ['pending', 'approved', 'denied'])->default('pending');
                $table->timestamps();
                $table->unsignedBigInteger('created_by')->nullable();
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
