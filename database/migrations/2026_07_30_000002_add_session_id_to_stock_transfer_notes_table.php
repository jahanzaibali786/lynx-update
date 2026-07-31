<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_transfer_notes', function (Blueprint $table) {
            $table->unsignedInteger('session_id')->nullable()->after('store_to');
            $table->index('session_id');
        });
    }

    public function down()
    {
        Schema::table('stock_transfer_notes', function (Blueprint $table) {
            $table->dropIndex(['session_id']);
            $table->dropColumn('session_id');
        });
    }
};
