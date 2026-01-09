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
        Schema::create('employee_monthly_salary_attendances', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('working_days');
            $table->integer('absents');
            $table->integer('total_annual');
            $table->integer('bal_annual');
            $table->integer('total_casual');
            $table->integer('bal_casual');
            $table->integer('leave');
            $table->integer('month_days');
            $table->date('for_month_of');
            $table->tinyInteger('gm_final')->default('0');
            $table->tinyInteger('sal_final')->default('0');
            $table->tinyInteger('adm_final')->default('0');
            $table->tinyInteger('lock_status')->default('0');
            $table->tinyInteger('accountant_finalize')->default('0');
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
        Schema::dropIfExists('employee_monthly_salary_attendances');
    }
};
