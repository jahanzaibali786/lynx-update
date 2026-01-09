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
        // 'emp_no',
        // 'payscale',
        // 'income_tax',
        // 'other_deduction',
        // 'EOBI',
        // 'gross',
        // 'net_salary',
        // 'pay_method',
        // 'bank_name',
        // 'bank_account',
        Schema::create('salary_proposals', function (Blueprint $table) {
            $table->id();
            $table->string('emp_no');
            $table->string('payscale');
            $table->string('income_tax');
            $table->string('other_deduction');
            $table->string('EOBI');
            $table->string('gross');
            $table->string('net_salary');
            $table->string('pay_method');
            $table->string('bank_name');
            $table->string('bank_account');
            $table->string('status')->default('0');
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
        Schema::dropIfExists('salary_proposals');
    }
};
