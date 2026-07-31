<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByToChartOfAccountSubTypesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('chart_of_account_sub_types', 'created_by')) {
            Schema::table('chart_of_account_sub_types', function (Blueprint $table) {
                $table->integer('created_by')->default(0)->after('type');
            });
        }
    }

    public function down()
    {
        // Keep this column on rollback because older databases may already have it.
    }
}
