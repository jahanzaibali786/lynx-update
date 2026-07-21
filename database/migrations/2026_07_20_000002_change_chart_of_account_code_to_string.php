<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE chart_of_accounts MODIFY code VARCHAR(50) NOT NULL DEFAULT '0'");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE chart_of_accounts MODIFY code INT NOT NULL DEFAULT 0');
    }
};
