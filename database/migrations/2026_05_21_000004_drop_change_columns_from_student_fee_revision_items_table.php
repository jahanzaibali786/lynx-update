<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_fee_revision_items', function (Blueprint $table) {
            if (Schema::hasColumn('student_fee_revision_items', 'change_amount')) {
                $table->dropColumn('change_amount');
            }

            if (Schema::hasColumn('student_fee_revision_items', 'change_payable_amount')) {
                $table->dropColumn('change_payable_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('student_fee_revision_items', function (Blueprint $table) {
            if (!Schema::hasColumn('student_fee_revision_items', 'change_amount')) {
                $table->decimal('change_amount', 15, 2)->default(0)->after('new_payable_amount');
            }

            if (!Schema::hasColumn('student_fee_revision_items', 'change_payable_amount')) {
                $table->decimal('change_payable_amount', 15, 2)->default(0)->after('change_amount');
            }
        });
    }
};
