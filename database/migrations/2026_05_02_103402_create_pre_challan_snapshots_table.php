<?php
// database/migrations/xxxx_create_pre_challan_snapshots_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pre_challan_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_reference_id'); // pre_challan_reports.id
            $table->unsignedBigInteger('student_id');
            $table->string('month', 7);                        // 'Y-m'  e.g. 2026-05
            $table->unsignedBigInteger('owned_by');            // branch user id
            $table->unsignedBigInteger('created_by');          // company user id
            $table->decimal('gross', 10, 2)->default(0);
            $table->decimal('arrears', 10, 2)->default(0);
            $table->decimal('net_receivable', 10, 2)->default(0);
            $table->decimal('prev_month_amount', 10, 2)->default(0);
            $table->decimal('difference', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['report_reference_id', 'student_id']); // no duplicates on re-approval
            $table->index(['month', 'owned_by']);
            $table->index('student_id');

            $table->foreign('report_reference_id')->references('id')->on('pre_challan_reports')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_challan_snapshots');
    }
};