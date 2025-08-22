<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeeExceptionsTable extends Migration
{
    public function up()
    {
        Schema::create('fee_exceptions', function (Blueprint $table) {

            $table->id();
            $table->foreignId('guest_id')->constrained('guests')->onDelete('cascade');
            $table->decimal('hostel_fee', 10, 2)->nullable();
            $table->decimal('caution_money', 10, 2)->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('facility')->nullable();
            $table->string('approved_by')->nullable();
            $table->text('remarks')->nullable();
            $table->string('document_path')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->text('account_remark')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fee_exceptions');
    }
}
