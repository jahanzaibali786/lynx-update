<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('student_finance_cleanup_runs', function (Blueprint $table) {
            $table->id();
            $table->date('from_date');
            $table->date('to_date');
            $table->unsignedSmallInteger('current_year');
            $table->unsignedBigInteger('last_receipt_id')->default(0);
            $table->unsignedInteger('chunk_size')->default(500);
            $table->string('status', 20)->default('scheduled');
            $table->json('totals')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('execute_after')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['status', 'execute_after'], 'sf_cleanup_due_idx');
        });

        Schema::table('student_receipts', function (Blueprint $table) {
            $table->index(['recipt_date', 'id'], 'sr_cleanup_date_id_idx');
            $table->index('voucher_id', 'sr_cleanup_voucher_idx');
            $table->index('challan_id', 'sr_cleanup_challan_idx');
        });

        Schema::table('journal_items', function (Blueprint $table) {
            $table->index('journal', 'ji_cleanup_journal_idx');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index(
                ['reference_id', 'voucher_type', 'category'],
                'je_cleanup_reference_idx'
            );
            $table->index('challan_id', 'je_cleanup_challan_idx');
        });

        Schema::table('challan_heads', function (Blueprint $table) {
            $table->index('challan_id', 'ch_cleanup_challan_idx');
        });

        Schema::table('challans', function (Blueprint $table) {
            $table->index('voucher_id', 'challans_cleanup_voucher_idx');
        });
    }

    public function down()
    {
        Schema::table('challans', function (Blueprint $table) {
            $table->dropIndex('challans_cleanup_voucher_idx');
        });

        Schema::table('challan_heads', function (Blueprint $table) {
            $table->dropIndex('ch_cleanup_challan_idx');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex('je_cleanup_reference_idx');
            $table->dropIndex('je_cleanup_challan_idx');
        });

        Schema::table('journal_items', function (Blueprint $table) {
            $table->dropIndex('ji_cleanup_journal_idx');
        });

        Schema::table('student_receipts', function (Blueprint $table) {
            $table->dropIndex('sr_cleanup_date_id_idx');
            $table->dropIndex('sr_cleanup_voucher_idx');
            $table->dropIndex('sr_cleanup_challan_idx');
        });

        Schema::dropIfExists('student_finance_cleanup_runs');
    }
};
