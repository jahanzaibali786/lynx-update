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
            if (!Schema::hasColumn('journal_entries', 'voucher_series')) {
            $table->string('voucher_series')->default('SYSTEM')->after('voucher_type');
            }
            if (!Schema::hasColumn('journal_entries', 'manual_series_no')) {
            $table->integer('manual_series_no')->nullable()->after('voucher_series');
            }
            if (!Schema::hasColumn('journal_entries', 'manual_reference')) {
            $table->string('manual_reference')->nullable()->after('manual_series_no');
            }
            if (!Schema::hasColumn('journal_entries', 'payee_account_title')) {
                $table->string('payee_account_title')->nullable()->after('payment_mode');
            }
            if (!Schema::hasColumn('journal_entries', 'payee_account_no')) {
                $table->string('payee_account_no')->nullable()->after('payee_account_title');
            }
            if (!Schema::hasColumn('journal_entries', 'payee_contact')) {
                $table->string('payee_contact')->nullable()->after('payee_account_no');
            }
            if (!Schema::hasColumn('journal_entries', 'payee_email')) {
                $table->string('payee_email')->nullable()->after('payee_contact');
            }
            if (!Schema::hasColumn('journal_entries', 'payee_cnic')) {
                $table->string('payee_cnic')->nullable()->after('payee_email');
            }
            if (!Schema::hasColumn('journal_entries', 'receiver_name')) {
                $table->string('receiver_name')->nullable()->after('payee_cnic');
            }
            if (!Schema::hasColumn('journal_entries', 'receiver_cnic')) {
                $table->string('receiver_cnic')->nullable()->after('receiver_name');
            }
            if (!Schema::hasColumn('journal_entries', 'receiver_contact')) {
                $table->string('receiver_contact')->nullable()->after('receiver_cnic');
            }
            if (!Schema::hasColumn('journal_entries', 'receiver_email')) {
                $table->string('receiver_email')->nullable()->after('receiver_contact');
            }
            if (!Schema::hasColumn('journal_entries', 'payment_date')) {
                $table->date('payment_date')->nullable()->after('receiver_email');
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
            'voucher_series',
            'manual_series_no',
            'manual_reference',
            'payee_account_title',
            'payee_account_no',
            'payee_contact',
            'payee_email',
            'payee_cnic',
            'receiver_name',
            'receiver_cnic',
            'receiver_contact',
            'receiver_email',
            'payment_date',
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
