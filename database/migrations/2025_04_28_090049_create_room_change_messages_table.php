<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomChangeMessagesTable extends Migration
{

    public function up()
    {
        Schema::create('room_change_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_change_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // who sent the message
            $table->enum('sender', ['admin', 'resident']);
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('room_change_messages');
    }
}
