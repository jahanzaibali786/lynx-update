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
        // 1. Update chart_of_account_types
        \DB::table('chart_of_account_types')
            ->where('name', 'Head & Press')
            ->update(['name' => 'Head Imprest']);

        // 2. Update chart_of_account_sub_types
        \DB::table('chart_of_account_sub_types')
            ->where('name', 'Head & Press')
            ->update(['name' => 'Head Imprest']);

        // 3. Update product_service_categories name and type
        \DB::table('product_service_categories')
            ->where('name', 'Head & Press')
            ->update(['name' => 'Head Imprest']);
        \DB::table('product_service_categories')
            ->where('type', 'head & press')
            ->update(['type' => 'head imprest']);

        // 4. Update bank_accounts type
        \DB::table('bank_accounts')
            ->where('type', 'head_press')
            ->update(['type' => 'head_imprest']);

        // 5. Update journal_entries category
        \DB::table('journal_entries')
            ->where('category', 'Head & Press')
            ->update(['category' => 'Head imprest']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // 1. Revert chart_of_account_types
        \DB::table('chart_of_account_types')
            ->where('name', 'Head Imprest')
            ->update(['name' => 'Head & Press']);

        // 2. Revert chart_of_account_sub_types
        \DB::table('chart_of_account_sub_types')
            ->where('name', 'Head Imprest')
            ->update(['name' => 'Head & Press']);

        // 3. Revert product_service_categories name and type
        \DB::table('product_service_categories')
            ->where('name', 'Head Imprest')
            ->update(['name' => 'Head & Press']);
        \DB::table('product_service_categories')
            ->where('type', 'head imprest')
            ->update(['type' => 'head & press']);

        // 4. Revert bank_accounts type
        \DB::table('bank_accounts')
            ->where('type', 'head_imprest')
            ->update(['type' => 'head_press']);

        // 5. Revert journal_entries category
        \DB::table('journal_entries')
            ->where('category', 'Head Imprest')
            ->update(['category' => 'Head & Press']);
    }
};
