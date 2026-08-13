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
        Schema::table('study_pack_challans', function (Blueprint $table) {
            if (!Schema::hasColumn('study_pack_challans', 'section_id')) {
                $table->integer('section_id')->nullable()->after('class_id');
            }
        });

        Schema::table('challans', function (Blueprint $table) {
            if (!Schema::hasColumn('challans', 'section_id')) {
                $table->integer('section_id')->nullable()->after('class_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('study_pack_challans', function (Blueprint $table) {
            if (Schema::hasColumn('study_pack_challans', 'section_id')) {
                $table->dropColumn('section_id');
            }
        });

        Schema::table('challans', function (Blueprint $table) {
            if (Schema::hasColumn('challans', 'section_id')) {
                $table->dropColumn('section_id');
            }
        });
    }
};
