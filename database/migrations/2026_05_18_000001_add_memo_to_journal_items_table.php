<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('journal_items', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_items', 'memo')) {
                $table->text('memo')->nullable()->after('description');
            }
        });
    }

    public function down()
    {
        Schema::table('journal_items', function (Blueprint $table) {
            if (Schema::hasColumn('journal_items', 'memo')) {
                $table->dropColumn('memo');
            }
        });
    }
};
