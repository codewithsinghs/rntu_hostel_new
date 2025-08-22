<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {

        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('gender');
            $table->string('scholar_no')->unique();
            $table->string('number')->nullable();
            $table->string('parent_no')->nullable();
            $table->string('guardian_no')->nullable();
            $table->string('fathers_name');
            $table->string('mothers_name');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // The resident user
            $table->foreignId('bed_id')->nullable()->constrained('beds')->onDelete('set null');
            $table->enum('status', ['pending', 'active', 'inactive', 'checkout'])->default('pending'); // Resident status
            $table->foreignId('guest_id')->nullable()->constrained('guests')->onDelete('set null'); // Track origin guest
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
