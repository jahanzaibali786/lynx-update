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
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedInteger('owned_by')->default(0)->nullable()->after('order');
        });
        Schema::table('deals', function (Blueprint $table) {
            $table->unsignedInteger('owned_by')->default(0)->nullable()->after('order');
        });
        Schema::table('contracts', function (Blueprint $table) {
    
            $table->unsignedInteger('owned_by')->default(0)->nullable()->after('company_signature');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('owned_by');
        });
        Schema::table('deals', function (Blueprint $table) {
            $table->dropColumn('owned_by');
        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('owned_by');
        });
    }
};
