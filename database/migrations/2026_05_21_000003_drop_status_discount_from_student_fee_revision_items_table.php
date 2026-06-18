<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_fee_revision_items', function (Blueprint $table) {
            $columns = [
                'prev_checked_status',
                'new_checked_status',
                'prev_discount',
                'new_discount',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('student_fee_revision_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down()
    {
        Schema::table('student_fee_revision_items', function (Blueprint $table) {
            if (!Schema::hasColumn('student_fee_revision_items', 'prev_checked_status')) {
                $table->tinyInteger('prev_checked_status')->default(0);
            }
            if (!Schema::hasColumn('student_fee_revision_items', 'new_checked_status')) {
                $table->tinyInteger('new_checked_status')->default(0);
            }
            if (!Schema::hasColumn('student_fee_revision_items', 'prev_discount')) {
                $table->decimal('prev_discount', 8, 2)->default(0);
            }
            if (!Schema::hasColumn('student_fee_revision_items', 'new_discount')) {
                $table->decimal('new_discount', 8, 2)->default(0);
            }
        });
    }
};
