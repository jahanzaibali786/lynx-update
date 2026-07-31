<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BackfillCreatedByOnChartOfAccountSubTypesTable extends Migration
{
    public function up()
    {
        if (
            !Schema::hasColumn('chart_of_account_sub_types', 'created_by') ||
            !Schema::hasTable('chart_of_account_types')
        ) {
            return;
        }

        DB::table('chart_of_account_sub_types')
            ->join('chart_of_account_types', 'chart_of_account_sub_types.type', '=', 'chart_of_account_types.id')
            ->where('chart_of_account_sub_types.created_by', 0)
            ->update([
                'chart_of_account_sub_types.created_by' => DB::raw('chart_of_account_types.created_by'),
            ]);
    }

    public function down()
    {
        //
    }
}
