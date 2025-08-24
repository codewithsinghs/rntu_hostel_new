<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGrievanceResponsesTable extends Migration
{
    public function up()
    {
        Schema::create('grievance_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grievance_id')->constrained('grievances')->onDelete('cascade'); // Foreign key to the grievance
            $table->foreignId('responded_by')->constrained('users')->onDelete('cascade'); // Foreign key to the user who responded
            $table->text('description'); // Description of the response
            $table->timestamps(); // Created and updated timestamps
            $table->unsignedBigInteger('created_by')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grievance_responses');
    }
}
