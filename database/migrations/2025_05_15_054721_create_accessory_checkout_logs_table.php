<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accessory_checkout_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkout_id')->constrained('checkouts')->onDelete('cascade');
            $table->foreignId('accessory_head_id')->constrained('accessory')->onDelete('cascade');
            $table->boolean('is_returned');
            $table->decimal('debit_amount', 10, 2)->default(0);
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accessory_checkout_logs');
    }
};
