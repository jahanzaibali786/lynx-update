<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_transfer_note_items', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_transfer_note_items', 'study_pack_id')) {
                $table->unsignedBigInteger('study_pack_id')->nullable()->after('description');
            }

            if (!Schema::hasColumn('stock_transfer_note_items', 'study_pack_title')) {
                $table->string('study_pack_title')->nullable()->after('study_pack_id');
            }

            if (!Schema::hasColumn('stock_transfer_note_items', 'study_pack_class')) {
                $table->string('study_pack_class')->nullable()->after('study_pack_title');
            }
        });
    }

    public function down()
    {
        Schema::table('stock_transfer_note_items', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('stock_transfer_note_items', 'study_pack_class')) {
                $columns[] = 'study_pack_class';
            }

            if (Schema::hasColumn('stock_transfer_note_items', 'study_pack_title')) {
                $columns[] = 'study_pack_title';
            }

            if (Schema::hasColumn('stock_transfer_note_items', 'study_pack_id')) {
                $columns[] = 'study_pack_id';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
