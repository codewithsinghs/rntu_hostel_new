<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Nullable because payments can be for guests (advance) or residents (monthly fees, fines, etc.)
            $table->foreignId('guest_id')->nullable()->constrained('guests')->onDelete('cascade');
            $table->foreignId('resident_id')->nullable()->constrained('residents')->onDelete('cascade');

            // Nullable to allow different types of fees (monthly, fines, accessories, etc.)
            $table->foreignId('fee_head_id')->nullable()->constrained('fees')->onDelete('cascade');
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->onDelete('cascade');
            $table->foreignId('student_accessory_id')->nullable()->constrained('student_accessory')->onDelete('cascade');

            // Total fee amount (fixed at the time of payment entry)
            $table->decimal('total_amount', 10, 2)->default(0);

            // Amount paid in this transaction
            $table->decimal('amount', 10, 2);

            // Remaining amount after this payment
            $table->decimal('remaining_amount', 10, 2)->default(0);

            // Nullable because offline payments won't have a transaction ID
            $table->string('transaction_id')->nullable()->unique();

            // Payment method
            $table->enum('payment_method', ['Cash', 'UPI', 'Bank Transfer', 'Card', 'Other', 'Null'])->default('Cash');

            // Payment status: pending until fully paid
            $table->enum('payment_status', ['Pending', 'Completed'])->default('Pending');

            $table->boolean('is_caution_money')->default(false);

            // Admin who entered the offline payment (if applicable)
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            // Due date for payment completion
            $table->date('due_date')->nullable();

            // Additional remarks
            $table->text('remarks')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
