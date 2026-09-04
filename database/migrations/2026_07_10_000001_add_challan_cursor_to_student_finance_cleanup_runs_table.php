<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_finance_cleanup_runs', function (Blueprint $table) {
            if (!Schema::hasColumn('student_finance_cleanup_runs', 'cleanup_type')) {
                $table->string('cleanup_type', 30)->default('receipt')->after('id');
            }

            if (!Schema::hasColumn('student_finance_cleanup_runs', 'last_challan_id')) {
                $table->unsignedBigInteger('last_challan_id')->default(0)->after('last_receipt_id');
            }
        });

        Schema::table('challans', function (Blueprint $table) {
            $table->index(['challan_date', 'id'], 'challans_cleanup_date_id_idx');
            $table->index('fee_month', 'challans_cleanup_fee_month_idx');
        });

        Schema::table('challan_sec_adjustments', function (Blueprint $table) {
            $table->index('voucher_id', 'csa_cleanup_voucher_idx');
        });
    }

    public function down()
    {
        Schema::table('challan_sec_adjustments', function (Blueprint $table) {
            $table->dropIndex('csa_cleanup_voucher_idx');
        });

        Schema::table('challans', function (Blueprint $table) {
            $table->dropIndex('challans_cleanup_date_id_idx');
            $table->dropIndex('challans_cleanup_fee_month_idx');
        });

        Schema::table('student_finance_cleanup_runs', function (Blueprint $table) {
            if (Schema::hasColumn('student_finance_cleanup_runs', 'last_challan_id')) {
                $table->dropColumn('last_challan_id');
            }

            if (Schema::hasColumn('student_finance_cleanup_runs', 'cleanup_type')) {
                $table->dropColumn('cleanup_type');
            }
        });
    }
};
