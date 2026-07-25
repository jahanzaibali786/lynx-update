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
        Schema::create('daily_closings', function (Blueprint $table) {
            $table->id();
            $table->date('from_date');
            $table->date('to_date');
            $table->string('status')->default('pending');
            $table->decimal('total_income_received', 15, 2)->default(0.00);
            $table->decimal('total_income_deposited', 15, 2)->default(0.00);
            $table->decimal('difference', 15, 2)->default(0.00);
            $table->unsignedBigInteger('issued_by_id')->nullable();
            $table->unsignedBigInteger('received_by_id')->nullable();
            $table->date('deposit_date')->nullable();   
            
            // Note counts
            $table->integer('note_5000')->default(0);
            $table->integer('note_1000')->default(0);
            $table->integer('note_500')->default(0);
            $table->integer('note_100')->default(0);
            $table->integer('note_50')->default(0);
            $table->integer('note_20')->default(0);
            $table->integer('note_10')->default(0);
            $table->integer('note_5')->default(0);
            $table->integer('note_2')->default(0);
            $table->integer('note_1')->default(0);

            $table->integer('created_by');
            $table->integer('owned_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_closings');
    }
};
