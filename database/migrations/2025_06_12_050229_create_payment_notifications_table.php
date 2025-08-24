<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Creates the 'payment_notifications' table.
        Schema::create('payment_notifications', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key.

            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->foreignId('resident_id')->constrained('residents')->onDelete('cascade');
            $table->string('notification_type');
            $table->string('sms_gateway_message_id')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->unique(['payment_id', 'notification_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drops the 'payment_notifications' table if the migration is rolled back.
        Schema::dropIfExists('payment_notifications');
    }
};

