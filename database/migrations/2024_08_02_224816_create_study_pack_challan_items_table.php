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
        Schema::create('study_pack_challan_items', function (Blueprint $table) {
            $table->id();
            $table->integer('challan_id');
            $table->integer('studypack_id');
            $table->integer('product_id');
            $table->integer('qty');
            $table->integer('tax')->nullable();
            $table->float('discount',2)->nullable();
            $table->float('price',2)->nullable;
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
        Schema::dropIfExists('study_pack_challan_items');
    }
};
