<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_fee_revision_batches', function (Blueprint $table) {
            $table->id();
            $table->string('revision_type', 50)->default('promotion');
            $table->unsignedBigInteger('promotion_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('reg_id')->nullable();
            $table->unsignedInteger('session_from_id')->nullable();
            $table->unsignedInteger('session_to_id')->nullable();
            $table->unsignedInteger('branch_from_id')->nullable();
            $table->unsignedInteger('branch_to_id')->nullable();
            $table->unsignedInteger('class_from_id')->nullable();
            $table->unsignedInteger('class_to_id')->nullable();
            $table->unsignedInteger('section_from_id')->nullable();
            $table->unsignedInteger('section_to_id')->nullable();
            $table->date('effective_from')->nullable();
            $table->string('status', 30)->default('applied');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('owned_by')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_fee_revision_batches');
    }
};
