<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuestsTable extends Migration
{

    public function up()
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('gender');
            $table->string('scholar_no')->unique();
            $table->string('fathers_name')->nullable();
            $table->string('mothers_name')->nullable();
            $table->unsignedTinyInteger('months')->default(3)->comment('Duration of stay in months');
            $table->unsignedTinyInteger('days')->nullable(); //addded
            $table->string('local_guardian_name')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('remarks')->nullable();
            $table->text('admin_remarks')->nullable();
            $table->boolean('fee_waiver')->default(false);
            $table->string('emergency_no');
            $table->string('number')->nullable();
            $table->string('parent_no')->nullable();
            $table->string('guardian_no')->nullable();
            $table->string('room_preference')->nullable();
            $table->string('food_preference')->nullable();
            $table->unsignedBigInteger('faculty')->nullable();
            $table->unsignedBigInteger('department')->nullable();
            $table->unsignedBigInteger('course')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('guests');
    }
}
