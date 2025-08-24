<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuildingsTable extends Migration
{
    public function up()
    {
        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('building_code')->unique();
            $table->foreignId('university_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedInteger('floors')->default(1);
            $table->unsignedBigInteger('created_by'); // Just store the user_id, no foreign key constraint
            $table->timestamps();
        });
    }
    

    public function down()
    {
        Schema::dropIfExists('buildings');
    }
}
