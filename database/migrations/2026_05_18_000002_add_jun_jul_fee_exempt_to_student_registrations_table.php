<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('student_registrations', 'fee_exempt_jun_jul')) {
                $table->boolean('fee_exempt_jun_jul')->default(false)->after('remarks');
            }
        });
    }

    public function down()
    {
        Schema::table('student_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('student_registrations', 'fee_exempt_jun_jul')) {
                $table->dropColumn('fee_exempt_jun_jul');
            }
        });
    }
};
