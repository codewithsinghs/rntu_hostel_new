<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('resident_id');
            $table->string('facility_name');
            $table->text('feedback');
            $table->text('suggestion')->nullable();
            $table->string('photo_path')->nullable(); // <-- Add this line
            $table->foreign('resident_id')->references('id')->on('residents')->onDelete('cascade');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedbacks');
    }
};
