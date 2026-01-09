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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('salute',5)->after('user_id')->nullable();
            $table->string('f_name',35)->after('name')->nullable();
            $table->string('cnic',15)->after('phone')->nullable();
            $table->string('religion',15)->after('employee_id')->nullable();
            $table->string('blood_group',5)->after('religion')->nullable();
            $table->text('present_address')->after('address')->nullable();
            $table->string('area',50)->after('branch_id')->nullable();
            $table->string('category',12)->after('area')->nullable();
            $table->tinyInteger('probation_period')->after('company_doj')->nullable();
            $table->date('probation_end')->after('probation_period')->nullable();
            $table->tinyInteger('security')->after('probation_end')->nullable();
            $table->tinyInteger('pessi')->after('security')->nullable();
            $table->tinyInteger('pessi_employer')->after('pessi')->nullable();
            $table->tinyInteger('eobi')->after('pessi_employer')->nullable();
            $table->tinyInteger('eobi_employer')->after('eobi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('salute');
            $table->dropColumn('f_name');
            $table->dropColumn('cnic');
            $table->dropColumn('religion');
            $table->dropColumn('blood_group');
            $table->dropColumn('present_address');
            $table->dropColumn('category');
            $table->dropColumn('probation_period');
            $table->dropColumn('probation_end');
            $table->dropColumn('annual_total');
            $table->dropColumn('annual_consumed');
            $table->dropColumn('casual_total');
            $table->dropColumn('casual_consumed');
            $table->dropColumn('security');
            $table->dropColumn('pessi');
            $table->dropColumn('pessi_employer');
            $table->dropColumn('eobi');
            $table->dropColumn('eobi_employer');
        });
    }
};
