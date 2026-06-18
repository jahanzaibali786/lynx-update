<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('grns', function (Blueprint $table) {
            $table->string('purchase_order_id')->nullable()->after('reference_no');
        });
    }

    public function down()
    {
        Schema::table('grns', function (Blueprint $table) {
            $table->dropColumn('purchase_order_id');
        });
    }
};
