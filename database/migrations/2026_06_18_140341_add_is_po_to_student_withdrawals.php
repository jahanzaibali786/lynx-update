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
            $table->boolean('is_po')->default(0)->after('other_reason');
        });
    }

    public function down()
    {
        Schema::table('student_withdrawals', function (Blueprint $table) {
            $table->dropColumn('is_po');
        });
    }
};
