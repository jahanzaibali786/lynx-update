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
        Schema::table('student_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('student_registrations', 'reg_branch_id')) {
                $table->unsignedInteger('reg_branch_id')->nullable()->after('branch');
            }

            if (!Schema::hasColumn('student_registrations', 'added_by')) {
                $table->unsignedInteger('added_by')->nullable()->after('owned_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('student_registrations', 'reg_branch_id')) {
                $table->dropColumn('reg_branch_id');
            }

            if (Schema::hasColumn('student_registrations', 'added_by')) {
                $table->dropColumn('added_by');
            }
        });
    }
};
