<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('branch_purchases', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('id')->index();
        });
    }

    public function down()
    {
        Schema::table('branch_purchases', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });
    }
};
