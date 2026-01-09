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
        Schema::create('employee_monthly_salaries', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->integer('department_id')->nullable();
            $table->integer('scale_no')->nullable();
            $table->integer('sal_days')->nullable();
            $table->date('salary_date');
            $table->decimal('basics', 15, 2)->nullable();
            $table->decimal('conv', 15, 2)->nullable();
            $table->decimal('drns', 15, 2)->nullable();
            $table->decimal('misc', 15, 2)->nullable();
            $table->decimal('chaild_con', 15, 2)->nullable();
            $table->decimal('stop_sal', 15, 2)->nullable();
            $table->decimal('other', 15, 2)->nullable();
            $table->decimal('gross', 15, 2)->nullable();
            $table->decimal('emp_sec', 15, 2)->nullable();
            $table->decimal('pessi', 15, 2)->nullable();
            $table->decimal('pessi_employer', 15, 2)->nullable();
            $table->decimal('it', 15, 2)->nullable();
            $table->decimal('eobi', 15, 2)->nullable();
            $table->decimal('eobi_employer', 15, 2)->nullable();
            $table->decimal('dedu', 15, 2)->nullable();
            $table->decimal('loan', 15, 2)->nullable();
            $table->decimal('tra_course', 15, 2)->nullable();
            $table->decimal('sal_advance', 15, 2)->nullable();
            $table->boolean('prc_final')->default(false);
            $table->boolean('sal_final')->default(false);
            $table->boolean('on_hold')->default(false);
            $table->string('status')->default('unpaid');
            $table->integer('voucher_id')->nullable();
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
        Schema::dropIfExists('employee_monthly_salaries');
    }
};
