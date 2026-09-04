<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchase_products', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_products', 'received_quantity')) {
                $table->decimal('received_quantity', 15, 2)->default(0)->after('quantity');
            }
        });

        Schema::table('grn_items', function (Blueprint $table) {
            if (!Schema::hasColumn('grn_items', 'purchase_id')) {
                $table->unsignedBigInteger('purchase_id')->nullable()->after('grn_id')->index();
            }
            if (!Schema::hasColumn('grn_items', 'purchase_product_id')) {
                $table->unsignedBigInteger('purchase_product_id')->nullable()->after('purchase_id')->index();
            }
            if (!Schema::hasColumn('grn_items', 'purchase_order_no')) {
                $table->string('purchase_order_no')->nullable()->after('purchase_product_id');
            }
            if (!Schema::hasColumn('grn_items', 'ordered_quantity')) {
                $table->decimal('ordered_quantity', 15, 2)->default(0)->after('condition');
            }
        });
    }

    public function down()
    {
        Schema::table('grn_items', function (Blueprint $table) {
            if (Schema::hasColumn('grn_items', 'ordered_quantity')) {
                $table->dropColumn('ordered_quantity');
            }
            if (Schema::hasColumn('grn_items', 'purchase_order_no')) {
                $table->dropColumn('purchase_order_no');
            }
            if (Schema::hasColumn('grn_items', 'purchase_product_id')) {
                $table->dropColumn('purchase_product_id');
            }
            if (Schema::hasColumn('grn_items', 'purchase_id')) {
                $table->dropColumn('purchase_id');
            }
        });

        Schema::table('purchase_products', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_products', 'received_quantity')) {
                $table->dropColumn('received_quantity');
            }
        });
    }
};
