<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_histories', function (Blueprint $table) {
         $table->id();

            $table->integer('reg_id');
            $table->integer('student_id');
            $table->enum('event_type', [
                'register', 'enroll', 'promote', 'demote', 'transfer', 'withdraw'
            ]);
            $table->unsignedInteger('from_session_id')->nullable();
            $table->unsignedInteger('from_class_id')->nullable();
            $table->unsignedInteger('from_section_id')->nullable();
            $table->unsignedBigInteger('from_branch_id')->nullable();
            //to
            $table->unsignedInteger('to_session_id')->nullable();
            $table->unsignedInteger('to_class_id')->nullable();
            $table->unsignedInteger('to_section_id')->nullable();
            $table->unsignedBigInteger('to_branch_id')->nullable();

            $table->date('effective_date')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('owned_by')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            // Foreign keys
            // $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('from_session_id')->references('id')->on('sessions')->nullOnDelete();
            $table->foreign('from_class_id')->references('id')->on('classes')->nullOnDelete();
            $table->foreign('from_section_id')->references('id')->on('sections')->nullOnDelete();
            $table->foreign('from_branch_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('to_session_id')->references('id')->on('sessions')->nullOnDelete();
            $table->foreign('to_class_id')->references('id')->on('classes')->nullOnDelete();
            $table->foreign('to_section_id')->references('id')->on('sections')->nullOnDelete();
            $table->foreign('to_branch_id')->references('id')->on('users')->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_histories');
    }
};
