<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('class_wise_fees', function (Blueprint $table) {
            $table->string('type', 20)->default('regular')->after('head_id');
        });

        DB::table('registring_options')->updateOrInsert(
            ['name' => 'TEACHER CHILD'],
            ['discount' => 0, 'owned_by' => 0, 'created_by' => 1]
        );
    }

    public function down()
    {
        Schema::table('class_wise_fees', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        DB::table('registring_options')->where('name', 'TEACHER CHILD')->delete();
    }
};
