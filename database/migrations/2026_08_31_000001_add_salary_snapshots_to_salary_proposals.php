<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_proposals', function (Blueprint $table) {
            $table->json('prev_salary_snapshot')->nullable()->after('bank_account');
            $table->json('new_salarysnapshot')->nullable()->after('prev_salary_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('salary_proposals', function (Blueprint $table) {
            $table->dropColumn(['prev_salary_snapshot', 'new_salarysnapshot']);
        });
    }
};
