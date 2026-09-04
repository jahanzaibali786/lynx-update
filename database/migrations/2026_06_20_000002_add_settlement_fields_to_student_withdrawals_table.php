<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_withdrawals', function (Blueprint $table) {
            if (!Schema::hasColumn('student_withdrawals', 'beneficiary_name')) {
                $table->string('beneficiary_name')->nullable();
            }
            if (!Schema::hasColumn('student_withdrawals', 'bank_name')) {
                $table->string('bank_name')->nullable();
            }
            if (!Schema::hasColumn('student_withdrawals', 'cheque_no')) {
                $table->string('cheque_no')->nullable();
            }
            if (!Schema::hasColumn('student_withdrawals', 'cheque_date')) {
                $table->date('cheque_date')->nullable();
            }
            if (!Schema::hasColumn('student_withdrawals', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('student_withdrawals', function (Blueprint $table) {
            $columns = [
                'beneficiary_name',
                'bank_name',
                'cheque_no',
                'cheque_date',
                'approved_by',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('student_withdrawals', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
