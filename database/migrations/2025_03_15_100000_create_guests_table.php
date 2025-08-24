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
            $table->string('scholar_number')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('mobile')->nullable();
            $table->string('password')->nullable();
            $table->string('gender')->nullable();

            $table->string('fathers_name')->nullable();
            $table->string('mothers_name')->nullable();
             $table->string('local_guardian_name')->nullable();
            $table->string('guardian_contact')->nullable();

            $table->string('parent_contact')->nullable();
            $table->string('emergency_contact');
            $table->unsignedTinyInteger('months')->default(3)->comment('Duration of stay in months');
            $table->unsignedTinyInteger('days')->nullable(); //addded
           
            $table->boolean('fee_waiver')->default(false);
            $table->string('attachment_path')->nullable();
            $table->text('remarks')->nullable();
            $table->text('admin_remarks')->nullable();
            
            $table->string('room_preference')->nullable();
            $table->string('food_preference')->nullable();
            
            $table->unsignedBigInteger('faculty')->nullable();
            $table->unsignedBigInteger('department')->nullable();
            $table->unsignedBigInteger('course')->nullable();
            $table->string('token')->nullable();
            $table->string('token_expiry')->nullable();
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
