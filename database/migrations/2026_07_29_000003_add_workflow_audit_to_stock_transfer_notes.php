<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_transfer_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_transfer_notes', 'forwarded_by')) {
                $table->unsignedBigInteger('forwarded_by')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('stock_transfer_notes', 'forwarded_at')) {
                $table->timestamp('forwarded_at')->nullable()->after('forwarded_by');
            }
            if (!Schema::hasColumn('stock_transfer_notes', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('forwarded_at');
            }
            if (!Schema::hasColumn('stock_transfer_notes', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (!Schema::hasColumn('stock_transfer_notes', 'issued_by')) {
                $table->unsignedBigInteger('issued_by')->nullable()->after('rejected_at');
            }
            if (!Schema::hasColumn('stock_transfer_notes', 'issued_at')) {
                $table->timestamp('issued_at')->nullable()->after('issued_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_transfer_notes', function (Blueprint $table) {
            $columns = array_filter([
                'forwarded_by',
                'forwarded_at',
                'rejected_by',
                'rejected_at',
                'issued_by',
                'issued_at',
            ], fn (string $column) => Schema::hasColumn('stock_transfer_notes', $column));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
