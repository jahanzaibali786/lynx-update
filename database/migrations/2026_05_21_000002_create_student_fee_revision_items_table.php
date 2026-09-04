<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_fee_revision_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('batch_id');
            $table->unsignedBigInteger('student_fee_structure_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('reg_id')->nullable();
            $table->unsignedInteger('head_id');
            $table->decimal('percentage', 8, 2)->default(0);
            $table->decimal('prev_base_amount', 15, 2)->default(0);
            $table->decimal('new_base_amount', 15, 2)->default(0);
            $table->decimal('prev_payable_amount', 15, 2)->default(0);
            $table->decimal('new_payable_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['batch_id', 'head_id']);
            $table->index(['reg_id', 'head_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_fee_revision_items');
    }
};
