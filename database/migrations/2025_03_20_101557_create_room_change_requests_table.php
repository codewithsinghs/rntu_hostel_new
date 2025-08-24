<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('room_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('residents')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // who created this request
            $table->text('reason');
            $table->string('preference')->nullable();
            $table->string('token')->unique(); // token to track conversation securely
            $table->enum('action', ['pending', 'available', 'not_available', 'completed'])->default('pending');
            $table->text('remark')->nullable();
            $table->boolean('resident_agree')->nullable();
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('room_change_requests');
    }
};
