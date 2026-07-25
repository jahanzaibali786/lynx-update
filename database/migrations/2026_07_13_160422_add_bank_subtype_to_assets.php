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
        $assetTypes = \DB::table('chart_of_account_types')->where('name', 'Assets')->get();
        foreach ($assetTypes as $type) {
            $exists = \DB::table('chart_of_account_sub_types')
                ->where('type', $type->id)
                ->where('name', 'Bank')
                ->exists();
            if (!$exists) {
                \DB::table('chart_of_account_sub_types')->insert([
                    'name' => 'Bank',
                    'type' => $type->id,
                    'created_by' => $type->created_by,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $assetTypes = \DB::table('chart_of_account_types')->where('name', 'Assets')->get();
        foreach ($assetTypes as $type) {
            \DB::table('chart_of_account_sub_types')
                ->where('type', $type->id)
                ->where('name', 'Bank')
                ->delete();
        }
    }
};
