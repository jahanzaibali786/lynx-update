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
        // Schema::table('revenues', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('description');           
        // });
        // Schema::table('payments', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('add_receipt');           
        // });
        // Schema::table('product_service_categories', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('color');           
        // });
        // Schema::table('taxes', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('rate');           
        // });
        // Schema::table('product_service_units', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('bank_accounts', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('bank_address');           
        // });
        // Schema::table('bills', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('category_id');           
        // });
        // Schema::table('goals', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('is_display');           
        // });
        // Schema::table('venders', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('avatar');           
        // });
        // Schema::table('transactions', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('date');           
        // });
        // Schema::table('product_services', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('pro_image');           
        // });
        // Schema::table('space_types', function (Blueprint $table) {
        //     $table->integer('tax_id')->after('name')->default(0);           
        //     $table->integer('account_head')->after('tax_id')->default(0);           
        // });
        // Schema::table('product_services', function (Blueprint $table) {
        //     $table->integer('space_id')->after('unit_id');           
        // });

        // Schema::table('employees', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('is_active');           
        // });
        // Schema::table('payslip_types', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('allowance_options', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('loan_options', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('deduction_options', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('leaves', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('status');           
        // });
        // Schema::table('chart_of_accounts', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('description');           
        // });
        // Schema::table('chart_of_account_types', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('bank_transfers', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('description');           
        // });
        // Schema::table('branches', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('departments', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('designations', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('leave_types', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('days');           
        // });
        // Schema::table('payslip_types', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('allowance_options', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('loan_options', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('deduction_options', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('goal_types', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('training_types', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('award_types', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('termination_types', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('job_categories', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('title');           
        // });
        // Schema::table('performance_types', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('competencies', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('type');           
        // });
        // Schema::table('allowances', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('type');           
        // });
        // Schema::table('commissions', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('type');           
        // });
        // Schema::table('loans', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('reason');           
        // });
        // Schema::table('saturation_deductions', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('type');           
        // });
        // Schema::table('other_payments', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('type');           
        // });
        // Schema::table('overtimes', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('type');           
        // });
        // Schema::table('indicators', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('created_user');           
        // });
        // Schema::table('appraisals', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('remark');           
        // });
        // Schema::table('goal_trackings', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('progress');           
        // });
        // Schema::table('trainings', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('remarks');           
        // });
        // Schema::table('trainers', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('expertise');           
        // });
        // Schema::table('jobs', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('custom_question');           
        // });
        // Schema::table('custom_questions', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('is_required');           
        // });
        // Schema::table('awards', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('description');           
        // });
        // Schema::table('transfers', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('description');           
        // });
        // Schema::table('proposals', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('converted_invoice_id');           
        // });
        // Schema::table('budgets', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('expense_data');           
        // });
        // Schema::table('contract_types', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('name');           
        // });
        // Schema::table('resignations', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('description');           
        // });
        // Schema::table('travels', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('description');           
        // });
        // Schema::table('promotions', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('description');           
        // });
        // Schema::table('complaints', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('description');           
        // });
        // Schema::table('warnings', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('description');           
        // });
        // Schema::table('terminations', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('description');           
        // });
        // Schema::table('announcements', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('description');           
        // });
        // Schema::table('announcement_employees', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('employee_id');           
        // });
        // Schema::table('holidays', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('occasion');           
        // });
        // Schema::table('events', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('description');           
        // });
        // Schema::table('event_employees', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('employee_id');           
        // });
        // Schema::table('meetings', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('note');           
        // });
        // Schema::table('meeting_employees', function (Blueprint $table) {
        //     $table->integer('owned_by')->after('employee_id');           
        // });
        Schema::table('bill_accounts', function (Blueprint $table) {
            $table->integer('bill_product_id')->after('type');           
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('revenues', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('payments', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');           
        // });
        // Schema::table('product_service_categories', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');          
        // });
        // Schema::table('taxes', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('product_service_units', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');          
        // });
        // Schema::table('bank_accounts', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');           
        // });
        // Schema::table('bills', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');        
        // });
        // Schema::table('goals', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');        
        // });
        // Schema::table('venders', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');    
        // });
        // Schema::table('transactions', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');           
        // });
        // Schema::table('product_services', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');           
        // });
        // Schema::table('space_types', function (Blueprint $table) {
        //     $table->dropColumn('tax_id');
        //     $table->dropColumn('account_head');
        // });
        // Schema::table('product_services', function (Blueprint $table) {
        //     $table->dropColumn('space_id');          
        // });
  
        // Schema::table('employees', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('deduction_options', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('loan_options', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('allowance_options', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('payslip_types', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('leaves', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('chart_of_accounts', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('chart_of_account_types', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('bank_transfers', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('branches', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('departments', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('designations', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('leave_types', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('payslip_types', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('allowance_options', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('loan_options', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('deduction_options', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('deduction_options', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('goal_types', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('training_types', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('award_types', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('termination_types', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('job_categories', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('performance_types', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('competencies', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('allowances', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('commissions', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('loans', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('saturation_deductions', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('other_payments', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('overtimes', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('indicators', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('appraisals', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('goal_trackings', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('trainings', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('trainers', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('jobs', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('awards', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('custom_questions', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('transfers', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('proposals', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('budgets', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('contract_types', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('resignations', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('travels', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('promotions', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('complaints', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('warnings', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('terminations', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('announcements', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('announcement_employees', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('holidays', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('events', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('event_employees', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('meeting_employees', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        // Schema::table('meetings', function (Blueprint $table) {
        //     $table->dropColumn('owned_by');         
        // });
        Schema::table('bill_accounts', function (Blueprint $table) {
            $table->dropColumn('bill_product_id');         
        });
            

        }
};
