<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('product_services', function (Blueprint $table) {
            if (!Schema::hasColumn('product_services', 'item_type')) {
                $table->string('item_type', 50)->nullable()->after('type');
            }
            if (!Schema::hasColumn('product_services', 'is_subitem')) {
                $table->boolean('is_subitem')->default(false)->after('item_type');
            }
            if (!Schema::hasColumn('product_services', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('is_subitem');
            }
            if (!Schema::hasColumn('product_services', 'manufacturer_part_number')) {
                $table->string('manufacturer_part_number')->nullable()->after('parent_id');
            }
            if (!Schema::hasColumn('product_services', 'purchase_description')) {
                $table->text('purchase_description')->nullable()->after('description');
            }
            if (!Schema::hasColumn('product_services', 'sales_description')) {
                $table->text('sales_description')->nullable()->after('purchase_description');
            }
            if (!Schema::hasColumn('product_services', 'inventory_asset_account_id')) {
                $table->unsignedBigInteger('inventory_asset_account_id')->nullable()->after('expense_chartaccount_id');
            }
        });
    }

    public function down()
    {
        Schema::table('product_services', function (Blueprint $table) {
            foreach ([
                'inventory_asset_account_id',
                'sales_description',
                'purchase_description',
                'manufacturer_part_number',
                'parent_id',
                'is_subitem',
                'item_type',
            ] as $column) {
                if (Schema::hasColumn('product_services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
