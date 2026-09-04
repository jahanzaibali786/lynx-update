<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_readmissions', function (Blueprint $table) {
            if (!Schema::hasColumn('student_readmissions', 'flow_type')) {
                $table->string('flow_type', 30)->nullable()->after('status');
            }

            if (!Schema::hasColumn('student_readmissions', 'target_section_id')) {
                $table->unsignedBigInteger('target_section_id')->nullable()->after('session_id');
            }

            if (!Schema::hasColumn('student_readmissions', 'generate_gap_months')) {
                $table->boolean('generate_gap_months')->default(false)->after('target_section_id');
            }

            if (!Schema::hasColumn('student_readmissions', 'snapshot')) {
                $table->longText('snapshot')->nullable()->after('generate_gap_months');
            }

            if (!Schema::hasColumn('student_readmissions', 'approval_snapshot')) {
                $table->longText('approval_snapshot')->nullable()->after('snapshot');
            }

            if (!Schema::hasColumn('student_readmissions', 'challan_ids')) {
                $table->longText('challan_ids')->nullable()->after('approval_snapshot');
            }

            if (!Schema::hasColumn('student_readmissions', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('challan_ids');
            }

            if (!Schema::hasColumn('student_readmissions', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
        });
    }

    public function down()
    {
        Schema::table('student_readmissions', function (Blueprint $table) {
            $columns = [
                'flow_type',
                'target_section_id',
                'generate_gap_months',
                'snapshot',
                'approval_snapshot',
                'challan_ids',
                'approved_by',
                'approved_at',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('student_readmissions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
