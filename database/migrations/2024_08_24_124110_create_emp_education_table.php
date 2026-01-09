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
        Schema::create('emp_education', function (Blueprint $table) {
            $table->id();
            $table->integer('emp_id');
            $table->string('institute');
            $table->string('degree');
            $table->string('title');
            $table->string('subject');
            $table->date('adm_date');
            $table->date('pass_date');
            $table->string('grade');
            $table->text('reason');
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
        Schema::dropIfExists('emp_education');
    }
};
