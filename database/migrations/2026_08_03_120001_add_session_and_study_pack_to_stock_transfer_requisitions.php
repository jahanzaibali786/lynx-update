<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('branch_purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('branch_purchases', 'session_id')) {
                $table->unsignedInteger('session_id')->nullable()->after('warehouse_id');
                $table->index('session_id');
            }
        });

        Schema::table('branch_purchase_items', function (Blueprint $table) {
            if (!Schema::hasColumn('branch_purchase_items', 'study_pack_id')) {
                $table->unsignedBigInteger('study_pack_id')->nullable()->after('description');
            }

            if (!Schema::hasColumn('branch_purchase_items', 'study_pack_title')) {
                $table->string('study_pack_title')->nullable()->after('study_pack_id');
            }

            if (!Schema::hasColumn('branch_purchase_items', 'study_pack_class')) {
                $table->string('study_pack_class')->nullable()->after('study_pack_title');
            }
        });
    }

    public function down()
    {
        Schema::table('branch_purchases', function (Blueprint $table) {
            if (Schema::hasColumn('branch_purchases', 'session_id')) {
                $table->dropIndex(['session_id']);
                $table->dropColumn('session_id');
            }
        });

        Schema::table('branch_purchase_items', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('branch_purchase_items', 'study_pack_class')) {
                $columns[] = 'study_pack_class';
            }

            if (Schema::hasColumn('branch_purchase_items', 'study_pack_title')) {
                $columns[] = 'study_pack_title';
            }

            if (Schema::hasColumn('branch_purchase_items', 'study_pack_id')) {
                $columns[] = 'study_pack_id';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
