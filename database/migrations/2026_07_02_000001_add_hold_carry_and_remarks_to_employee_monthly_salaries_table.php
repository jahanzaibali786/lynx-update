<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employee_monthly_salaries', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_monthly_salaries', 'remarks')) {
                $table->text('remarks')->nullable()->after('status');
            }
            if (!Schema::hasColumn('employee_monthly_salaries', 'carried_to_salary_id')) {
                $table->unsignedBigInteger('carried_to_salary_id')->nullable()->after('on_hold');
            }
            if (!Schema::hasColumn('employee_monthly_salaries', 'carried_at')) {
                $table->timestamp('carried_at')->nullable()->after('carried_to_salary_id');
            }
        });
    }

    public function down()
    {
        Schema::table('employee_monthly_salaries', function (Blueprint $table) {
            if (Schema::hasColumn('employee_monthly_salaries', 'remarks')) {
                $table->dropColumn('remarks');
            }
            if (Schema::hasColumn('employee_monthly_salaries', 'carried_at')) {
                $table->dropColumn('carried_at');
            }
            if (Schema::hasColumn('employee_monthly_salaries', 'carried_to_salary_id')) {
                $table->dropColumn('carried_to_salary_id');
            }
        });
    }
};
