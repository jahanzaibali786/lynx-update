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
    public function up(): void
    {
        Schema::table('product_services', function (Blueprint $table) {
            $table->float('used_quantity')->default(0.0)->after('quantity');
            $table->float('damaged_quantity')->default(0.0)->after('used_quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('product_services', function (Blueprint $table) {
            $table->dropColumn(['used_quantity', 'damaged_quantity']);
        });
    }
};
