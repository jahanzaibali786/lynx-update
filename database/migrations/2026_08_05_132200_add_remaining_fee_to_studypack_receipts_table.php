<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studypack_receipts', function (Blueprint $table) {
            if (!Schema::hasColumn('studypack_receipts', 'remaining_fee')) {
            $table->float('remaining_fee')->default(0)->after('arrears');
            }
        });
    }

    public function down(): void
    {
        Schema::table('studypack_receipts', function (Blueprint $table) {
            $table->dropColumn('remaining_fee');
        });
    }
};
