<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('grievances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('residents')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('responded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('type_of_complaint');
            $table->text('description');
            $table->enum('status', ['open', 'agreed_by_resident', 'closed'])->default('open');
            $table->string('token_id')->unique();
            $table->string('photo')->nullable();  // Add a column for storing photo (file path or URL)
            $table->timestamps();
        });
    }
    
    

    public function down() {
        Schema::dropIfExists('grievances');
    }
};
