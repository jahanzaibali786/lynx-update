<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_deduction_details', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('salary_id');
            $table->unsignedBigInteger('employee_id');

            // loan, advance, security, fine, etc
            $table->string('type');
            // more detail (Laptop loan, Salary advance etc)
            $table->string('sub_type')->nullable();

            // link to loan table if exists
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->decimal('amount', 12, 2)->default(0);

            $table->text('note')->nullable();

            $table->unsignedBigInteger('coa_id')->nullable();

            $table->timestamps();

            // indexes for fast reporting
            $table->index('salary_id');
            $table->index('employee_id');
            $table->index('type');
            $table->index('reference_id');
            $table->index('coa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_deduction_details');
    }
};