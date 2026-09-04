<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('grns', function (Blueprint $table) {
            $table->string('purchase_order')->nullable()->after('reference_no');
            $table->unsignedBigInteger('purchase_order_id')->nullable()->after('purchase_order');
        });
    }

    public function down()
    {
        Schema::table('grns', function (Blueprint $table) {
            $table->dropColumn('purchase_order');
            $table->dropColumn('purchase_order_id');
        });
    }
};
