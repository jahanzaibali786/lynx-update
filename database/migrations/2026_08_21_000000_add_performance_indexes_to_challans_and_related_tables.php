<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Performance indexes for the Student Defaulter Report.
 *
 * The report runs, per branch:
 *   - a main challans query filtered on due_date range + status + a
 *     whereHas on student_registrations / student_enrollments,
 *   - a bulk arrears query (WHERE student_id IN (...) AND status <> 'Paid'
 *     AND due_date < :from),
 *   - a bulk tuition lookup on student_fee_structures.
 *
 * The challans table previously had no secondary indexes, so each of the
 * above did a full table scan. The indexes below cover exactly those
 * predicates. Every add is guarded so the migration is safe to re-run.
 */
return new class extends Migration
{
    /**
     * index name => [table, [columns...]]
     */
    private function indexes(): array
    {
        return [
            // Bulk arrears query + main query ordering (student_id leading).
            'idx_ch_student_status_due' => ['challans', ['student_id', 'status', 'due_date']],
            // Main query: due_date range scan + status filter (branch users).
            'idx_ch_due_status'         => ['challans', ['due_date', 'status']],
            // Main + arrears query for company users (filtered on created_by).
            'idx_ch_createdby_due_status' => ['challans', ['created_by', 'due_date', 'status']],
            // whereHas('student') -> active + branch filter.
            'idx_sr_owned_active'       => ['student_registrations', ['owned_by', 'active_status']],
            // Bulk tuition lookup.
            'idx_sfs_reg_owned_head'    => ['student_fee_structures', ['reg_id', 'owned_by', 'head_id']],
            // whereHas('enrollstudent') -> regId join + branch filter.
            'idx_se_regid_owned'        => ['student_enrollments', ['regId', 'owned_by']],
        ];
    }

    public function up()
    {
        foreach ($this->indexes() as $name => [$table, $columns]) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            // Skip if any target column is missing.
            $missingColumn = false;
            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    $missingColumn = true;
                    break;
                }
            }
            if ($missingColumn) {
                continue;
            }

            if ($this->indexExists($table, $name)) {
                continue;
            }

            $cols = implode('`, `', $columns);
            DB::statement("CREATE INDEX `{$name}` ON `{$table}` (`{$cols}`)");
        }
    }

    public function down()
    {
        foreach ($this->indexes() as $name => [$table, $columns]) {
            if (Schema::hasTable($table) && $this->indexExists($table, $name)) {
                DB::statement("DROP INDEX `{$name}` ON `{$table}`");
            }
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            [$indexName]
        );

        return !empty($result);
    }
};
