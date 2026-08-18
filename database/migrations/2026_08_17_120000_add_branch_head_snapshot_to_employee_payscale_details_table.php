<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employee_payscale_details', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_payscale_details', 'branch_head_user_id')) {
                $table->unsignedBigInteger('branch_head_user_id')->nullable()->after('owned_by');
            }

        });
    }

    public function down()
    {
        Schema::table('employee_payscale_details', function (Blueprint $table) {
            $dropColumns = [];

            foreach (['branch_head_user_id', 'branch_head_name', 'branch_head_designation'] as $column) {
                if (Schema::hasColumn('employee_payscale_details', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
