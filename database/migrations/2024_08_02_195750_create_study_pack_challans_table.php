<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('study_pack_challans', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id');
            $table->integer('studypack_id');
            $table->integer('class_id');
            $table->string('challanNo');
            $table->integer('concession_id')->nullable();
            $table->date('challan_date');
            $table->date('fee_month')->nullable();
            $table->string('challan_type');
            $table->date('issue_date');
            $table->date('due_date');
            $table->integer('paid_amount')->default(0);
            $table->integer('total_amount')->default(0);
            $table->integer('branch_id')->default(0);
            $table->integer('owned_id')->default(0);
            $table->date('year')->nullable();
            $table->integer('concession_amount')->default(0);
            $table->string('status', 20)->default('pending');
            $table->integer('owned_by')->nullable()->unsigned();
            $table->integer('created_by')->nullable()->unsigned();
            $table->integer('session_id')->nullable()->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('study_pack_challans');
    }
};
