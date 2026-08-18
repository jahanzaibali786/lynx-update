<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (
            !Schema::hasColumn('employee_payscale_details', 'branch_head_user_id') ||
            !Schema::hasColumn('employee_payscale_details', 'branch_head_name') ||
            !Schema::hasColumn('employee_payscale_details', 'branch_head_designation')
        ) {
            return;
        }

        DB::statement("
            UPDATE employee_payscale_details epd
            INNER JOIN employees e ON e.id = epd.employee_id
            LEFT JOIN school_details sd
                ON sd.branch_id = COALESCE(NULLIF(epd.owned_by, 0), NULLIF(e.owned_by, 0), NULLIF(e.branch_id, 0))
            SET
                epd.owned_by = COALESCE(NULLIF(epd.owned_by, 0), NULLIF(e.owned_by, 0), NULLIF(e.branch_id, 0)),
                epd.branch_head_user_id = sd.headmaster
            WHERE sd.headmaster IS NOT NULL
              AND (
                    epd.branch_head_user_id IS NULL
                 OR epd.branch_head_user_id = 0
              )
        ");
    }

    public function down()
    {
        // Keep saved snapshot data intact on rollback.
    }
};
