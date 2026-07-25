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
        $companyIds = \DB::table('chart_of_account_types')
            ->select('created_by', 'owned_by')
            ->distinct()
            ->get();

        foreach ($companyIds as $company) {
            $companyId = $company->created_by;
            $ownedId = $company->owned_by ?: $companyId;

            // Check if 'Head & Press' category already exists
            $categoryType = \DB::table('chart_of_account_types')
                ->where('created_by', $companyId)
                ->where('name', 'Head Imprest')
                ->first();

            if (!$categoryType) {
                $categoryTypeId = \DB::table('chart_of_account_types')->insertGetId([
                    'name' => 'Head Imprest',
                    'owned_by' => $ownedId,
                    'created_by' => $companyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $categoryTypeId = $categoryType->id;
            }

            // Insert 'Head & Press' subtype under 'Head & Press' category
            $subTypeExists = \DB::table('chart_of_account_sub_types')
                ->where('type', $categoryTypeId)
                ->where('name', 'Head Imprest')
                ->exists();

            if (!$subTypeExists) {
                \DB::table('chart_of_account_sub_types')->insert([
                    'name' => 'Head Imprest',
                    'type' => $categoryTypeId,
                    'created_by' => $companyId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Find existing 'Expenses' category for this company
            $expenseType = \DB::table('chart_of_account_types')
                ->where('created_by', $companyId)
                ->where('name', 'Expenses')
                ->first();

            if ($expenseType) {
                $expenseSubTypeExists = \DB::table('chart_of_account_sub_types')
                    ->where('type', $expenseType->id)
                    ->where('name', 'Head Imprest')
                    ->exists();

                if (!$expenseSubTypeExists) {
                    \DB::table('chart_of_account_sub_types')->insert([
                        'name' => 'Head Imprest',
                        'type' => $expenseType->id,
                        'created_by' => $companyId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
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
        \DB::table('chart_of_account_sub_types')->where('name', 'Head Imprest')->delete();
        \DB::table('chart_of_account_types')->where('name', 'Head Imprest')->delete();
    }
};
