<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            if (!Schema::hasColumn('journal_entries', 'added_by')) {
                $table->unsignedBigInteger('added_by')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('journal_entries', 'added_at')) {
                $table->timestamp('added_at')->nullable()->after('added_by');
            }
            if (!Schema::hasColumn('journal_entries', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
            if (!Schema::hasColumn('journal_entries', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('updated_by');
            }
            if (!Schema::hasColumn('journal_entries', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('journal_entries', 'is_system_generated')) {
                $table->boolean('is_system_generated')->default(1)->after('approved_at');
            }
            if (!Schema::hasColumn('journal_entries', 'attachment')) {
                $table->string('attachment')->nullable()->after('is_system_generated');
            }
            if (!Schema::hasColumn('journal_entries', 'status')) {
                $table->enum('status', ['Draft', 'Submitted', 'Approved', 'Rejected'])->default('Approved')->after('attachment');
            }
            if (!Schema::hasColumn('journal_entries', 'bank_id')) {
                $table->unsignedBigInteger('bank_id')->nullable()->after('status');
            }
            if (!Schema::hasColumn('journal_entries', 'payment_mode')) {
                $table->string('payment_mode', 50)->nullable()->after('bank_id');
            }
            if (!Schema::hasColumn('journal_entries', 'cheque_no')) {
                $table->string('cheque_no', 100)->nullable()->after('payment_mode');
            }
            if (!Schema::hasColumn('journal_entries', 'cheque_date')) {
                $table->date('cheque_date')->nullable()->after('cheque_no');
            }
            if (!Schema::hasColumn('journal_entries', 'transaction_no')) {
                $table->string('transaction_no', 150)->nullable()->after('cheque_date');
            }
            if (!Schema::hasColumn('journal_entries', 'reversed_entry_id')) {
                $table->unsignedBigInteger('reversed_entry_id')->nullable()->after('transaction_no');
            }
            if (!Schema::hasColumn('journal_entries', 'reversed_timestamp')) {
                $table->timestamp('reversed_timestamp')->nullable()->after('reversed_entry_id');
            }
        });
    }

    public function down()
    {
        $columns = [
            'added_by',
            'added_at',
            'updated_by',
            'approved_by',
            'approved_at',
            'is_system_generated',
            'attachment',
            'status',
            'payment_mode',
            'cheque_no',
            'cheque_date',
            'transaction_no',
            'reversed_entry_id',
            'reversed_timestamp',
        ];

        Schema::table('journal_entries', function (Blueprint $table) use ($columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn('journal_entries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
