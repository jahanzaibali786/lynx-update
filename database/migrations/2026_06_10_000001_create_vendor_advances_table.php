<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vendor_advances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vender_id');
            $table->decimal('advance_amount', 15, 2)->default(0);
            $table->date('advance_date');
            $table->string('advance_reason')->nullable();
            $table->integer('status')->default(0);
            $table->date('approval_date')->nullable();
            $table->integer('bank_id')->nullable();
            $table->string('reference')->nullable();
            $table->string('payment_method')->nullable();
            $table->integer('voucher_id')->nullable();
            $table->integer('approved_by')->nullable();
            $table->integer('owned_by')->nullable()->unsigned();
            $table->integer('created_by')->nullable()->unsigned();
            $table->timestamps();

            $table->index(['vender_id', 'status']);
            $table->index('voucher_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_advances');
    }
};
