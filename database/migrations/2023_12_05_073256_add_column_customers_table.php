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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('ntn')->after('avatar'); 
            $table->unsignedInteger('company_id')->nullable()->after('ntn');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade'); 
            $table->integer('owned_by')->after('company_id');           
        });
        Schema::table('invoices', function (Blueprint $table) {

            $table->unsignedBigInteger('contract_id')->nullable()->after('discount_apply');           
            $table->foreign('contract_id')->references('id')->on('contracts')->onDelete('set null'); 
            $table->integer('owned_by')->after('contract_id');      

        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->unsignedInteger('company_id')->default(0)->nullable()->after('company_signature');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign('customers_company_id_foreign');
            $table->dropColumn('ntn');
            $table->dropColumn('company_id');
            $table->dropColumn('owned_by');
        });

        Schema::table('invoices', function (Blueprint $table) {

            $table->dropColumn('contract_id');
            $table->dropColumn('owned_by');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign('companies_company_id_foreign');
            $table->dropColumn('company_id');
        });
    }
};
