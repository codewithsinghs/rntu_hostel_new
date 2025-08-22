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
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resident_id')->constrained('residents')->onDelete('cascade');
            $table->date('date');
            $table->string('reason');
            $table->enum('admin_approval', ['pending', 'approved', 'denied'])->default('pending');
            $table->enum('account_approval', ['pending', 'approved', 'denied'])->default('pending');
            $table->text('remark')->nullable();
            $table->enum('action', ['completed', 'rejected', 'admin_checked', 'pending'])->default('pending');
            $table->decimal('deposited_amount', 10, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
