<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->integer('branches');
            $table->integer('employee_id');
            $table->string('title');
            $table->string('department');
            $table->string('service_tenure');
            $table->string('emp_sec');
            $table->decimal('amount', 15, 2);
            $table->decimal('maxamount', 15, 2);
            $table->date('from_pay_month');
            $table->date('apply_date');
            $table->date('approval_date')->nullable();
            $table->integer('pay_period');
            $table->date('loan_ended');
            $table->string('reason')->nullable();
            $table->decimal('per_month_amount', 15, 2);
            $table->decimal('received_amount', 15, 2)->default(0);
            $table->integer('status')->default(0);
            $table->integer('bank_id')->nullable();
            $table->integer('chartaccount_id')->nullable();
            $table->integer('referance_id')->nullable();
            $table->integer('voucher_id')->nullable();
            $table->integer('owned_by');
            $table->integer('created_by');
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
        Schema::dropIfExists('loans');
    }
}
