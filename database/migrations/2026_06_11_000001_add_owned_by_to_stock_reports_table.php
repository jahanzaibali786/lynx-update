<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_reports', 'owned_by')) {
                $table->integer('owned_by')->default(0)->after('created_by');
            }
        });
    }

    public function down()
    {
        Schema::table('stock_reports', function (Blueprint $table) {
            if (Schema::hasColumn('stock_reports', 'owned_by')) {
                $table->dropColumn('owned_by');
            }
        });
    }
};
