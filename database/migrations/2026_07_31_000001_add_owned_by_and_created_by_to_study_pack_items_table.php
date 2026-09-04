<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_pack_items', function (Blueprint $table) {
            if (!Schema::hasColumn('study_pack_items', 'owned_by')) {
                $table->integer('owned_by')->nullable()->after('price');
            }
            if (!Schema::hasColumn('study_pack_items', 'created_by')) {
                $table->integer('created_by')->nullable()->after('owned_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('study_pack_items', function (Blueprint $table) {
            if (Schema::hasColumn('study_pack_items', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('study_pack_items', 'owned_by')) {
                $table->dropColumn('owned_by');
            }
        });
    }
};
