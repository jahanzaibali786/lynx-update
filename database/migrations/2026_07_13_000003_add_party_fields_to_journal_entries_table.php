<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists($indexName)
    {
        return !empty(DB::select(
            'select 1 from information_schema.statistics where table_schema = ? and table_name = ? and index_name = ? limit 1',
            [DB::getDatabaseName(), 'journal_entries', $indexName]
        ));
    }

    public function up()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_entries', 'user_type')) {
                $table->string('user_type', 30)->nullable()->after('bank_id');
            }
            if (!Schema::hasColumn('journal_entries', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('user_type');
            }
            if (!Schema::hasColumn('journal_entries', 'category_type_id')) {
                $table->unsignedBigInteger('category_type_id')->nullable()->after('voucher_type');
            }
        });

        if (!$this->indexExists('je_user_type_id_idx')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->index(['user_type', 'user_id'], 'je_user_type_id_idx');
            });
        }
    }

    public function down()
    {
        if ($this->indexExists('je_user_type_id_idx')) {
            Schema::table('journal_entries', function (Blueprint $table) {
                $table->dropIndex('je_user_type_id_idx');
            });
        }

        Schema::table('journal_entries', function (Blueprint $table) {
            if (Schema::hasColumn('journal_entries', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('journal_entries', 'user_type')) {
                $table->dropColumn('user_type');
            }
            if (Schema::hasColumn('journal_entries', 'category_type_id')) {
                $table->dropColumn('category_type_id');
            }
        });
    }
};
