<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('challans', function (Blueprint $table) {
            $table->index(['fee_month', 'id'], 'challans_cleanup_fee_month_id_idx');
        });
    }

    public function down()
    {
        Schema::table('challans', function (Blueprint $table) {
            $table->dropIndex('challans_cleanup_fee_month_id_idx');
        });
    }
};
