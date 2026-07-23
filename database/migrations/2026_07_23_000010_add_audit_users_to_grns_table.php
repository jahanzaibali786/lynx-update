<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grns', function (Blueprint $table) {
            if (!Schema::hasColumn('grns', 'added_by')) {
                $table->unsignedBigInteger('added_by')->nullable()->after('created_by')->index();
            }

            if (!Schema::hasColumn('grns', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('added_by')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('grns', function (Blueprint $table) {
            if (Schema::hasColumn('grns', 'approved_by')) {
                $table->dropColumn('approved_by');
            }

            if (Schema::hasColumn('grns', 'added_by')) {
                $table->dropColumn('added_by');
            }
        });
    }
};
