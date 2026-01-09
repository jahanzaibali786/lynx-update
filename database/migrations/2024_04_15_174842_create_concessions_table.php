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
        Schema::create('concessions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('student_id')->unsigned();
            $table->integer('class_id')->nullable()->unsigned();
            $table->integer('concession_id')->unsigned();
            $table->string('concession_by')->nullable();
            $table->date('apply_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('remarks', 355);
            $table->string('status', 20)->default('draft');
            $table->tinyInteger('active_status')->default(0);
            $table->date('cancel_date');
            $table->string('cancel_remarks', 355);
            $table->integer('owned_by')->nullable()->unsigned();
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('session_id')->nullable()->unsigned();
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
        Schema::dropIfExists('concessions');
    }
};
