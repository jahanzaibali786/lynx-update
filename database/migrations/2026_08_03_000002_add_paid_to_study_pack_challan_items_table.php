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
        Schema::table('study_pack_challan_items', function (Blueprint $table) {
            if (!Schema::hasColumn('study_pack_challan_items', 'paid')) {
                $table->decimal('paid', 12, 2)->default(0)->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('study_pack_challan_items', function (Blueprint $table) {
            if (Schema::hasColumn('study_pack_challan_items', 'paid')) {
                $table->dropColumn('paid');
            }
        });
    }
};
