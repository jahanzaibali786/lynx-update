<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employee_advances', function (Blueprint $table) {
            $table->date('approval_date')->nullable()->after('status');
            $table->integer('bank_id')->nullable()->after('approval_date');
            $table->integer('chartaccount_id')->nullable()->after('bank_id');
            $table->string('reference')->nullable()->after('chartaccount_id');
            $table->string('payment_method')->nullable()->after('reference');
            $table->integer('voucher_id')->nullable()->after('payment_method');
            $table->integer('approved_by')->nullable()->after('voucher_id');
            $table->integer('deducted_salary_id')->nullable()->after('approved_by');
        });
    }

    public function down()
    {
        Schema::table('employee_advances', function (Blueprint $table) {
            $table->dropColumn([
                'approval_date',
                'bank_id',
                'chartaccount_id',
                'reference',
                'payment_method',
                'voucher_id',
                'approved_by',
                'deducted_salary_id',
            ]);
        });
    }
};
