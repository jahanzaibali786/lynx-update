<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('advance_tax_collections', function (Blueprint $table) {
            $table->string('proof_picture')->nullable()->after('remarks');
        });
    }

    public function down()
    {
        Schema::table('advance_tax_collections', function (Blueprint $table) {
            $table->dropColumn('proof_picture');
        });
    }
};
