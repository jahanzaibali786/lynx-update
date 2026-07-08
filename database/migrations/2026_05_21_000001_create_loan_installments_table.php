<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->integer('installment_no');
            $table->date('due_month');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->integer('status')->default(0);
            $table->unsignedBigInteger('salary_id')->nullable();
            $table->unsignedBigInteger('salary_deduction_detail_id')->nullable();
            $table->date('paid_at')->nullable();
            $table->integer('owned_by')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->unique(['loan_id', 'installment_no']);
            $table->index(['loan_id', 'status']);
            $table->index('due_month');
            $table->index('salary_id');
            $table->index('salary_deduction_detail_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_installments');
    }
};
