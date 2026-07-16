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
            [DB::getDatabaseName(), 'journal_items', $indexName]
        ));
    }

    public function up()
    {
        Schema::table('journal_items', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_items', 'added_by')) {
                $table->unsignedBigInteger('added_by')->nullable()->after('credit');
            }
            if (!Schema::hasColumn('journal_items', 'added_at')) {
                $table->timestamp('added_at')->nullable()->after('added_by');
            }
            if (!Schema::hasColumn('journal_items', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('added_at');
            }
            if (!Schema::hasColumn('journal_items', 'model_id')) {
                $table->unsignedBigInteger('model_id')->nullable()->after('user_type');
            }
            if (!Schema::hasColumn('journal_items', 'model_type')) {
                $table->string('model_type')->nullable()->after('model_id');
            }
            if (!Schema::hasColumn('journal_items', 'ref_no')) {
                $table->string('ref_no')->nullable()->after('description');
            }
            if (!Schema::hasColumn('journal_items', 'tra_date')) {
                $table->date('tra_date')->nullable()->after('ref_no');
            }
        });

        if (Schema::hasColumn('journal_items', 'added_by') && !$this->indexExists('ji_added_by_idx')) {
            Schema::table('journal_items', function (Blueprint $table) {
                $table->index('added_by', 'ji_added_by_idx');
            });
        }
    }

    public function down()
    {
        if ($this->indexExists('ji_added_by_idx')) {
            Schema::table('journal_items', function (Blueprint $table) {
                $table->dropIndex('ji_added_by_idx');
            });
        }

        Schema::table('journal_items', function (Blueprint $table) {
            if (Schema::hasColumn('journal_items', 'added_at')) {
                $table->dropColumn('added_at');
            }
            if (Schema::hasColumn('journal_items', 'added_by')) {
                $table->dropColumn('added_by');
            }
            if (Schema::hasColumn('journal_items', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('journal_items', 'model_id')) {
                $table->dropColumn('model_id');
            }
            if (Schema::hasColumn('journal_items', 'model_type')) {
                $table->dropColumn('model_type');
            }
             if (Schema::hasColumn('journal_items', 'ref_no')) {
                $table->dropColumn('ref_no');
            }
            if (Schema::hasColumn('journal_items', 'tra_date')) {
                $table->dropColumn('tra_date');
            }
        });
    }
};
