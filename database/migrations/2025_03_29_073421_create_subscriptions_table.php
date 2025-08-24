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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained()->onDelete('cascade');
            $table->foreignId('fee_head_id')->constrained('fees')->onDelete('cascade');
            $table->string('subscription_type');
            $table->decimal('price', 10, 2); // Editable for 'Other' fee
            $table->decimal('total_amount', 10, 2); // Total = price * duration or just price for 'Other'
            $table->date('start_date')->nullable(); // Made nullable for 'Other'
            $table->date('end_date')->nullable();   // Made nullable for 'Other'
            $table->enum('status', ['Pending', 'Active', 'Expired'])->default('Pending');
            $table->timestamps();
            $table->unsignedBigInteger('created_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
