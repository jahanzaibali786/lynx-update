<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_withdrawals', function (Blueprint $table) {
            $table->json('branch_snapshot')->nullable()->after('ho_remarks');
            $table->json('ho_snapshot')->nullable()->after('branch_snapshot');
        });
    }

    public function down()
    {
        Schema::table('student_withdrawals', function (Blueprint $table) {
            $table->dropColumn(['branch_snapshot', 'ho_snapshot']);
        });
    }
};
