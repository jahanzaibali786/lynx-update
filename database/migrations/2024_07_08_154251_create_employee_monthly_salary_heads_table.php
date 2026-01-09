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
        Schema::create('employee_monthly_salary_heads', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('sal_id')->nullable();
            $table->integer('scale_no')->nullable();
            $table->date('salary_date');
            $table->integer('head_id')->nullable();
            $table->integer('head_value')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('owned_by')->nullable();
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
        Schema::dropIfExists('employee_monthly_salary_heads');
    }
};
