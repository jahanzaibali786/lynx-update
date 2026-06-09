<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVendorContactFieldsToVendersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('venders', function (Blueprint $table) {
            // Company and name fields
            $table->string('company_name')->nullable()->after('vender_id');
            $table->string('name_prefix')->nullable()->after('company_name');
            $table->string('first_name')->nullable()->after('name_prefix');
            $table->string('middle_initial')->nullable()->after('first_name');
            $table->string('last_name')->nullable()->after('middle_initial');
            $table->string('job_title')->nullable()->after('last_name');
            
            // Phone fields (only 2 fields)
            $table->string('main_phone_type')->nullable()->after('contact');
            $table->string('main_phone')->nullable()->after('main_phone_type');
            $table->string('work_phone_type')->nullable()->after('main_phone');
            $table->string('work_phone')->nullable()->after('work_phone_type');
            
            // Email fields
            $table->string('main_email_type')->nullable()->after('email');
            $table->string('cc_email_type')->nullable()->after('main_email_type');
            $table->string('cc_email')->nullable()->after('cc_email_type');
            
            // Other contact fields
            $table->string('website_type')->nullable()->after('cc_email');
            $table->string('website')->nullable()->after('website_type');
            $table->string('other1_type')->nullable()->after('website');
            $table->string('other1')->nullable()->after('other1_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('venders', function (Blueprint $table) {
            $table->dropColumn([
                'company_name',
                'name_prefix',
                'first_name',
                'middle_initial',
                'last_name',
                'job_title',
                'main_phone_type',
                'main_phone',
                'work_phone_type',
                'work_phone',
                'main_email_type',
                'cc_email_type',
                'cc_email',
                'website_type',
                'website',
                'other1_type',
                'other1',
            ]);
        });
    }
}
