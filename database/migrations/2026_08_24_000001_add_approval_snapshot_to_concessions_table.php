<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalSnapshotToConcessionsTable extends Migration
{
    public function up()
    {
        Schema::table('concessions', function (Blueprint $table) {
            /*
             * longText is used for compatibility with older MySQL/MariaDB.
             * Concession model casts it to array.
             */
            $table->longText('approval_snapshot')
                ->nullable()
                ->after('type');
        });
    }

    public function down()
    {
        Schema::table('concessions', function (Blueprint $table) {
            $table->dropColumn('approval_snapshot');
        });
    }
}
