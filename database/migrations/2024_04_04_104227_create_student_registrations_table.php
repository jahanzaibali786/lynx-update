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
        Schema::create('student_registrations', function (Blueprint $table) {
            $table->increments('id');
            $table->date('regdate');
            $table->string('stdname', 155);
            $table->date('dob');
            $table->string('religion', 25);
            $table->string('gender', 12);
            $table->string('prevschool');
            $table->string('prevclass', 155);
            $table->string('fathername', 155);
            $table->string('fathercnic', 15);
            $table->string('fatherphone', 15);
            $table->string('fathercell', 15)->nullable();
            $table->string('fatherprofession')->nullable();
            $table->string('mothername', 155);
            $table->string('mothercnic', 15)->nullable();
            $table->string('motherprofession')->nullable();
            $table->string('email', 155);
            $table->string('city',155)->nullable();
            $table->string('district',155)->nullable();
            $table->text('address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->integer('branch')->unsigned();
            $table->integer('class_id')->unsigned();
            $table->integer('session_id')->unsigned();
            $table->tinyInteger('active_status')->default(1);
            $table->string('student_status',55)->nullable();
            $table->integer('registrationfee')->nullable();
            $table->integer('reg_type')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('owned_by')->nullable()->unsigned();
            $table->integer('created_by')->nullable()->unsigned();
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
        Schema::dropIfExists('student_registrations');
    }
};
