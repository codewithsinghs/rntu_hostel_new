<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('message_from'); // Admin/Staff Name
            $table->text('message'); // Notice Content
            $table->date('from_date'); // Start Date
            $table->date('to_date'); // End Date
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
        });
    }

    public function down() {
        Schema::dropIfExists('notices');
    }
};
