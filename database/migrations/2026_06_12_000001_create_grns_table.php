<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('grns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grn_no')->default(0);
            $table->unsignedBigInteger('vendor_id')->nullable()->index();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->date('grn_date');
            $table->string('reference_no')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->unsignedBigInteger('owned_by')->default(0)->index();
            $table->unsignedBigInteger('created_by')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grns');
    }
};
