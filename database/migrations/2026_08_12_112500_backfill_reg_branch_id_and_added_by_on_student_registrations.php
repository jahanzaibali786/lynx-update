<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Backfill reg_branch_id and added_by from the registration class (reg_class) branch ownership.
        DB::statement("
            UPDATE student_registrations sr
            INNER JOIN classes c ON c.id = sr.reg_class
            SET sr.reg_branch_id = c.owned_by,
                sr.added_by = c.owned_by
            WHERE sr.reg_class IS NOT NULL
              AND c.owned_by IS NOT NULL
              AND (
                    sr.reg_branch_id IS NULL OR sr.reg_branch_id = 0
                    OR sr.added_by IS NULL OR sr.added_by = 0
              )
        ");

        // Fallback: if reg_class is empty, use class_id branch ownership.
        DB::statement("
            UPDATE student_registrations sr
            INNER JOIN classes c ON c.id = sr.class_id
            SET sr.reg_branch_id = c.owned_by,
                sr.added_by = c.owned_by
            WHERE (sr.reg_class IS NULL OR sr.reg_class = 0)
              AND c.owned_by IS NOT NULL
              AND (
                    sr.reg_branch_id IS NULL OR sr.reg_branch_id = 0
                    OR sr.added_by IS NULL OR sr.added_by = 0
              )
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No destructive rollback for backfilled historical values.
    }
};
