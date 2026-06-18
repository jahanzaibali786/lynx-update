<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('branch_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_purchase_no')->default(0);
            $table->unsignedBigInteger('vender_id')->nullable()->index();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->date('purchase_date');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedTinyInteger('status')->default(0);
            $table->boolean('invoice_converted')->default(false);
            $table->unsignedBigInteger('created_by')->default(0)->index();
            $table->unsignedBigInteger('owned_by')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('branch_purchases');
    }
};
