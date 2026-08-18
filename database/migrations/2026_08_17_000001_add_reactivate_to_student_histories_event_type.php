<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE student_histories MODIFY event_type ENUM('register', 'enroll', 'promote', 'demote', 'transfer', 'withdraw', 'reactivate') NOT NULL");
    }

    public function down()
    {
        DB::statement("UPDATE student_histories SET event_type = 'withdraw' WHERE event_type = 'reactivate'");
        DB::statement("ALTER TABLE student_histories MODIFY event_type ENUM('register', 'enroll', 'promote', 'demote', 'transfer', 'withdraw') NOT NULL");
    }
};
