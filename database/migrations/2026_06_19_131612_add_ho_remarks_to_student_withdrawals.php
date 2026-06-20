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
        Schema::table('student_withdrawals', function (Blueprint $table) {
            $table->text('ho_remarks')->nullable()->after('remark');
        });
    }

    public function down()
    {
        Schema::table('student_withdrawals', function (Blueprint $table) {
            $table->dropColumn('ho_remarks');
        });
    }
};
