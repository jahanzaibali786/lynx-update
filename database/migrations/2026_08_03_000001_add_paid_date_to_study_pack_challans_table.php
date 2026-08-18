<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('study_pack_challans', function (Blueprint $table) {
            if (!Schema::hasColumn('study_pack_challans', 'paid_date')) {
                $table->date('paid_date')->nullable()->after('paid_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('study_pack_challans', function (Blueprint $table) {
            if (Schema::hasColumn('study_pack_challans', 'paid_date')) {
                $table->dropColumn('paid_date');
            }
        });
    }
};
