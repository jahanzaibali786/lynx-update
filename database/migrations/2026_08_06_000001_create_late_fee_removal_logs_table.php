<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('late_fee_removal_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('challan_id');
            $table->string('challan_no');
            $table->unsignedBigInteger('challan_head_id')->nullable();
            $table->decimal('old_amount', 10, 2);
            $table->decimal('removed_amount', 10, 2);
            $table->decimal('updated_amount', 10, 2)->nullable();
            $table->decimal('old_total_amount', 10, 2);
            $table->decimal('new_total_amount', 10, 2);
            $table->unsignedBigInteger('user_id');
            $table->timestamp('removal_date');
            $table->timestamps();

            $table->foreign('challan_id')->references('id')->on('challans')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('late_fee_removal_logs');
    }
};
