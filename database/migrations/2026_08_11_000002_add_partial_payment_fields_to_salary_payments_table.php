<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('salary_payments', 'amount')) {
                $table->decimal('amount', 15, 2)->nullable()->after('salary_id');
            }

            if (!Schema::hasColumn('salary_payments', 'payment_date')) {
                $table->date('payment_date')->nullable()->after('payment_method');
            }
        });

        if (Schema::hasColumn('salary_payments', 'amount') && Schema::hasColumn('salary_payments', 'net_pay')) {
            DB::table('salary_payments')
                ->whereNull('amount')
                ->update([
                    'amount' => DB::raw('net_pay'),
                ]);
        }
    }

    public function down()
    {
        Schema::table('salary_payments', function (Blueprint $table) {
            if (Schema::hasColumn('salary_payments', 'payment_date')) {
                $table->dropColumn('payment_date');
            }

            if (Schema::hasColumn('salary_payments', 'amount')) {
                $table->dropColumn('amount');
            }
        });
    }
};
