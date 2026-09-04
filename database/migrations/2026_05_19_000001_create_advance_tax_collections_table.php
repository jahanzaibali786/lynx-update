<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('advance_tax_collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('tax_month');
            $table->date('collection_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 20)->nullable();
            $table->string('reference')->nullable();
            $table->text('remarks')->nullable();
            $table->date('approval_date')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 Pending, 1 Approved, 2 Rejected');
            $table->unsignedBigInteger('owned_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'tax_month']);
            $table->index(['owned_by', 'created_by']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('advance_tax_collections');
    }
};
