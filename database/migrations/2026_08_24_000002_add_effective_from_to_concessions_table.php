<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('concessions', function (Blueprint $table) {
            $table->date('effective_from')
                ->nullable()
                ->after('apply_date');
        });
    }

    public function down()
    {
        Schema::table('concessions', function (Blueprint $table) {
            $table->dropColumn('effective_from');
        });
    }
};
