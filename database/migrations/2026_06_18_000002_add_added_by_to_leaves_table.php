<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('leaves', 'added_by')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->unsignedBigInteger('added_by')->nullable()->after('status')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('leaves', 'added_by')) {
            Schema::table('leaves', function (Blueprint $table) {
                $table->dropIndex(['added_by']);
                $table->dropColumn('added_by');
            });
        }
    }
};
