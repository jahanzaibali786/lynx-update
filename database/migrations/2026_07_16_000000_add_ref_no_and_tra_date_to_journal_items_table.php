<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRefNoAndTraDateToJournalItemsTable extends Migration
{
    public function up()
    {
        Schema::table('journal_items', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_items', 'ref_no')) {
                $table->string('ref_no')->nullable()->after('description');
            }
            if (!Schema::hasColumn('journal_items', 'tra_date')) {
                $table->date('tra_date')->nullable()->after('ref_no');
            }
        });
    }

    public function down()
    {
        Schema::table('journal_items', function (Blueprint $table) {
            if (Schema::hasColumn('journal_items', 'ref_no')) {
                $table->dropColumn('ref_no');
            }
            if (Schema::hasColumn('journal_items', 'tra_date')) {
                $table->dropColumn('tra_date');
            }
        });
    }
}
