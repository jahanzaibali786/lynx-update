<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_payscale_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('paymode')->nullable();
            $table->string('account_number')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('pay_scale_id')->nullable();
            $table->date('effect_from')->nullable();
            $table->decimal('drns', 10, 2)->nullable();
            $table->decimal('conv', 10, 2)->nullable();
            $table->decimal('misc', 10, 2)->nullable();
            $table->decimal('chaild_concession', 10, 2)->nullable();
            $table->decimal('emp_sec', 10, 2)->nullable();
            $table->unsignedBigInteger('security_receive_account')->nullable();
            $table->decimal('itax', 10, 2)->nullable();
            $table->unsignedBigInteger('tax_payable_account')->nullable();
            $table->decimal('eobi', 10, 2)->nullable();
            $table->decimal('eobi_employer', 10, 2)->nullable();
            $table->unsignedBigInteger('eobi_payable_account')->nullable();
            $table->decimal('pessi', 10, 2)->nullable();
            $table->decimal('pessi_employer', 10, 2)->nullable();
            $table->unsignedBigInteger('pessi_payable_account')->nullable();
            $table->decimal('other_deduction', 10, 2)->nullable();
            $table->unsignedBigInteger('other_dedu_payable_account')->nullable();
            $table->decimal('advance', 10, 2)->nullable();
            $table->unsignedBigInteger('advance_payable_account')->nullable();
            $table->decimal('net', 10, 2)->nullable();
            $table->unsignedBigInteger('net_payable_account')->nullable();
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
        Schema::dropIfExists('employee_payscale_details');
    }
};
