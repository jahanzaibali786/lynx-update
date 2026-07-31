<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('stock_transfer_notes')) {
            Schema::create('stock_transfer_notes', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('sto_id');
                $table->unsignedBigInteger('stn_id')->nullable();
                $table->unsignedBigInteger('store_from')->nullable();
                $table->unsignedBigInteger('store_to')->nullable();
                $table->date('issue_date');
                $table->date('due_date');
                $table->date('approve_date')->nullable();
                $table->integer('category_id')->nullable();
                $table->text('ref_number')->nullable();
                $table->integer('status')->default(0);
                $table->string('shipping_via',50)->nullable();
                $table->string('stn_type',50)->nullable();
                $table->integer('approved_by')->nullable();
                $table->integer('issue_to')->nullable();
                $table->integer('issue_by')->nullable();
                $table->integer('recived_by')->nullable();
                $table->integer('owned_by')->default(0);
                $table->integer('created_by')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('stock_transfer_note_items')) {
            Schema::create('stock_transfer_note_items', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->integer('stn_id');
                $table->integer('product_id');
                $table->string('type')->nullable();
                $table->integer('quantity');
                $table->decimal('price', 16, 2)->default('0.0');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

    }

    public function down()
    {
        Schema::dropIfExists('stock_transfer_note_items');
        Schema::dropIfExists('stock_transfer_notes');
    }
};
