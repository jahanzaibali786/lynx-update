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
        Schema::create('student_incomes', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->unsignedBigInteger('student_id');
            $table->string('income_type');
            $table->decimal('amount', 15, 2);
            $table->unsignedBigInteger('bank_id');
            $table->unsignedBigInteger('coa_id');
            $table->text('description')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('owned_by')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_incomes');
    }
};
