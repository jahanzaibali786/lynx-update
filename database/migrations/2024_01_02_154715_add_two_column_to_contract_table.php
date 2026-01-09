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
        Schema::table('contracts', function (Blueprint $table) {
            $table->integer('contract_id')->after('id');           
            $table->dateTime('close_date')->after('status')->nullable();
            $table->integer('service_id')->after('company_id')->nullable();           
            $table->integer('service_price')->after('service_id')->default(0);  
            $table->integer('security_deposit_id')->after('service_price')->nullable();           
            $table->integer('security_deposit_price')->after('security_deposit_id')->default(0);  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('contract_id');  
            $table->dropColumn('close_date');  
            $table->dropColumn('service_id');          
            $table->dropColumn('service_price'); 
            $table->dropColumn('security_deposit_id');          
            $table->dropColumn('security_deposit_price'); ; 
        });
    }
};
