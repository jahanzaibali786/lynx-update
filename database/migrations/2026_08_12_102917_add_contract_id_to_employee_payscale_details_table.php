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
        if (!Schema::hasColumn('employee_payscale_details', 'contract_id')) {
            Schema::table('employee_payscale_details', function (Blueprint $table) {
                $table->unsignedBigInteger('contract_id')->nullable()->after('employee_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('employee_payscale_details', 'contract_id')) {
            Schema::table('employee_payscale_details', function (Blueprint $table) {
                $table->dropColumn('contract_id');
            });
        }
    }
};
