<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stock_reports', function (Blueprint $table) {
            $table->integer('warehouse_id')->nullable()->default(0)->before('type');
            $table->decimal('unit_price', 15, 2)->nullable()->default(0)->after('warehouse_id');
            $table->decimal('sale_price', 15, 2)->nullable()->default(0)->after('unit_price');
            $table->decimal('remaining_qty', 15, 2)->nullable()->default(0)->after('sale_price');
            $table->string('condition')->nullable()->after('remaining_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_reports', function (Blueprint $table) {
            $table->dropColumn(['warehouse_id', 'unit_price', 'sale_price', 'remaining_qty', 'condition']);
        });
    }
};
