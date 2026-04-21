<?php

use App\Http\Controllers\AccountWiseFeeStructure;
use App\Http\Controllers\ChallanController;
use App\Http\Controllers\ClearanceCertificate;
use App\Http\Controllers\EmployeeApraisalFroms;
use App\Http\Controllers\EmployeeMonthlySalaryAttendance;
use App\Http\Controllers\EmployeeSalaryDetail;
use App\Http\Controllers\EmployeeSalaryHeads;
use App\Http\Controllers\EmployeeSalaryProposal;
use App\Http\Controllers\EmployeeScaleHeads;
use App\Http\Controllers\EmployeeSettlementController;
use App\Http\Controllers\EobiAllocation;
use App\Http\Controllers\HealthInsuracnePlanSetup;
use App\Http\Controllers\HrDataImportController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Controllers\JunkController;
use App\Http\Controllers\LeaveAllocation;
use App\Http\Controllers\RegisterOptionController;
use App\Http\Controllers\SopController;
use App\Http\Controllers\StudentEnrollment;
use App\Http\Controllers\StudentPromotions;
use App\Http\Controllers\StudentRegistration;
use App\Http\Controllers\StudentReportController;
use App\Http\Controllers\StudentReportController2;
use App\Http\Controllers\StudyPackChallanController;
use App\Http\Controllers\WhatsappController;
use App\Models\EmployeeMonthlySalary;
use App\Models\EmployeeMonthlySalaryHeads;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Employee;
use App\Models\Utility;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\ProductServiceController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\VenderController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankTransferController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\ProductServiceCategoryController;
use App\Http\Controllers\ProductServiceUnitController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\DebitNoteController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\AppointmentLetter;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\StageController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadStageController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PayslipTypeController;
use App\Http\Controllers\SetSalaryController;
use App\Http\Controllers\AllowanceController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanOptionController;
use App\Http\Controllers\DeductionOptionController;
use App\Http\Controllers\SaturationDeductionController;
use App\Http\Controllers\OtherPaymentController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\AllowanceOptionController;
use App\Http\Controllers\PaySlipController;
use App\Http\Controllers\CompanyPolicyController;
use App\Http\Controllers\IndicatorController;
use App\Http\Controllers\AppraisalController;
use App\Http\Controllers\GoalTypeController;
use App\Http\Controllers\GoalTrackingController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\AwardTypeController;
use App\Http\Controllers\AwardController;
use App\Http\Controllers\ResignationController;
use App\Http\Controllers\TravelController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\WarningController;
use App\Http\Controllers\TerminationController;
use App\Http\Controllers\TerminationTypeController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\JobCategoryController;
use App\Http\Controllers\JobStageController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\CustomQuestionController;
use App\Http\Controllers\InterviewScheduleController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\DucumentUploadController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\AttendanceEmployeeController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskStageController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ContractTypeController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\ProjectstagesController;
use App\Http\Controllers\BugStatusController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DataImportController;
use App\Http\Controllers\LandingPageSectionController;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PaystackPaymentController;
use App\Http\Controllers\FlutterwavePaymentController;
use App\Http\Controllers\RazorpayPaymentController;
use App\Http\Controllers\PaytmPaymentController;
use App\Http\Controllers\MolliePaymentController;
use App\Http\Controllers\MercadoPaymentController;
use App\Http\Controllers\SkrillPaymentController;
use App\Http\Controllers\PaymentWallPaymentController;
use App\Http\Controllers\CoingatePaymentController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\CompetenciesController;
use App\Http\Controllers\PerformanceTypeController;
use App\Http\Controllers\PlanRequestController;
use App\Http\Controllers\TimeTrackerController;
use App\Http\Controllers\ZoomMeetingController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProjectReportController;
use App\Http\Controllers\ToyyibpayController;
use App\Http\Controllers\UserlogController;
use App\Http\Controllers\PayFastController;
use App\Http\Controllers\NotificationTemplatesController;
use App\Http\Controllers\BankTransferPaymentController;
use App\Http\Controllers\AiTemplateController;
use App\Http\Controllers\IyziPayController;
use App\Http\Controllers\SspayController;
use App\Http\Controllers\PaytabController;
use App\Http\Controllers\BenefitPaymentController;
use App\Http\Controllers\CashfreeController;
use App\Http\Controllers\AamarpayController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\SpaceTypeController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\ChairController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\IsMailController;
use App\Http\Controllers\ClientUserController;
use App\Http\Controllers\IsVisitorController;
use App\Http\Controllers\PaytrController;
use App\Http\Controllers\TrainingTypeController;
use App\Http\Controllers\EmployeeAdvanceController;
use App\Http\Controllers\BankReciptVoucherController;
use App\Http\Controllers\BankPaymentVoucherController;
use App\Http\Controllers\CashReciptVoucherController;
use App\Http\Controllers\CashPaymentVoucherController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\ClassesController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\FeeHeadController;
use App\Http\Controllers\ClassWiseFeeController;
use App\Http\Controllers\ConcessionPolicyController;
use App\Http\Controllers\ConcessionController;
use App\Http\Controllers\EmployeeConcessionOrder;
use App\Http\Controllers\EmployeeReportsController;
use App\Http\Controllers\EmployeeScaleController;
use App\Http\Controllers\StudentTransferController;
use App\Http\Controllers\StudentWithdrawalController;
use App\Http\Controllers\StudyPackController;
use App\Http\Controllers\StudentReceipt;
use App\Http\Controllers\FeeReminderSlip;
use App\Http\Controllers\ProductServiceSubCategoryController;
use App\Http\Controllers\WarehouseTransferController;
use App\Http\Controllers\EmployeeTransferController;
use App\Http\Controllers\StudentReadmissionController;
use App\Http\Controllers\ReturnOrderController;
use App\Http\Controllers\TaxSlabsController;

use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth'])->name('dashboard');

require __DIR__ . '/auth.php';


//Route:get('/', ['as' => 'home','uses' =>'HomeController@index'])->middleware(['XSS']);
//Route::get('/home', ['as' => 'home','uses' =>'HomeController@index'])->middleware(['auth','XSS']);

Route::get('/storage-link', function () {
    try {
        Artisan::call('storage:link');
        return 'Storage link created successfully.';
    } catch (\Exception $e) {
        return 'Failed to create storage link: ' . $e->getMessage();
    }
});

Route::get('/journal-items-test', [JournalEntryController::class, 'test'])->name('journal.test');

Route::get('/delete-receipts', [JunkController::class, 'deleteReceipts'])->name('delete.receipts');
Route::get('/remove-late', [JunkController::class, 'deleteLateFeeBulk'])->name('delete.deleteLateFeeBulk');

Route::get('/add-probation', function () {
    try {
        // $employees = Employee::where('probation_end', null)->get();
        $employees = Employee::where('company_doj', '>=','2025-01-01')->get();

        DB::beginTransaction();

        foreach ($employees as $employee) {
            try {
                $doj = Carbon::parse($employee->company_doj);
                if (strtolower(optional($employee->department)->name) === 'academic') {
                    $employee->probation_period = 12;
                    $employee->probation_end = $doj->copy()->addMonths(12);
                } else {
                    $employee->probation_end = 6;
                    $employee->probation_end = $doj->copy()->addMonths(6);
                }

                $employee->save();
            } catch (\Exception $e) {
                continue;
            }
        }

        DB::commit();
        return 'Success';
    } catch (\Exception $e) {
        DB::rollBack();
        dd($e->getMessage());
        return 'Error occurred during processing.';
    }
});
Route::post('/calculate-tax', [TaxSlabsController::class, 'calculateTax'])->name('calculate.tax');
Route::resource('tax-slab', TaxSlabsController::class)->middleware(['auth', 'XSS']);

Route::get('/test-roll', function () {
    // Step 1: Get the enrollId + owned_by combinations that are duplicated
    $duplicatePairs = DB::table('student_enrollments')
        ->select('enrollId', 'class_id', 'section_id', 'owned_by', DB::raw('COUNT(*) as count'))
        ->groupBy('enrollId', 'class_id', 'section_id', 'owned_by')
        ->having('count', '>', 1)
        ->get();

    // Step 2: Fetch the full records that match those duplicated pairs
    $duplicates = App\Models\StudentEnrollments::where(function ($query) use ($duplicatePairs) {
        foreach ($duplicatePairs as $pair) {
            $query->orWhere(function ($subQuery) use ($pair) {
                $subQuery->where('enrollId', $pair->enrollId)
                    //  ->where('class_id', $pair->class_id)
                    //  ->where('section_id', $pair->section_id)
                    ->where('owned_by', $pair->owned_by);
            });
        }
    })->get();
    // Step 3: Delete the duplicates

    dd($duplicates);
});
Route::get('/maintainenrollhis', [JunkController::class, 'maintainEnrollHis']);
Route::get('/hisstatus', [JunkController::class, 'HisStatus']);
Route::get('/regchange', [JunkController::class, 'Regchange']);
Route::get('/register/{lang?}', [RegisteredUserController::class, 'showRegistrationForm'])->name('register');

//company verification email
Route::get('/verify', [EmailVerificationPromptController::class, '__invoke'])
    ->name('verification.notice')->middleware('auth');

Route::get('/verify/{lang?}', [EmailVerificationPromptController::class, 'showVerifyForm'])
    ->name('verification.notice')->middleware('auth');

Route::get('/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])->name('verification.verify')->middleware('auth');

Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->name('verification.send');
Route::get('/', [DashboardController::class, 'home_dashboard_index'])->name('home')->middleware(['XSS', 'revalidate',]);

Route::get('/', [DashboardController::class, 'home_dashboard_index'])->name('home')->middleware(['XSS', 'revalidate',]);

Route::get('/home', [DashboardController::class, 'home_dashboard_index'])->name('home')->middleware(['XSS', 'revalidate',]);
Route::get('/', [DashboardController::class, 'home_dashboard_index'])->name('dashboard')->middleware(['XSS', 'revalidate',]);

// old
// Route::get('/', [DashboardController::class, 'account_dashboard_index'])->name('home')->middleware(['XSS', 'revalidate',]);

// Route::get('/home', [DashboardController::class, 'account_dashboard_index'])->name('home')->middleware(['XSS', 'revalidate',]);
// Route::get('/', [DashboardController::class, 'account_dashboard_index'])->name('dashboard')->middleware(['XSS', 'revalidate',]);
Route::get('/send-whatsapp', [WhatsappController::class, 'sendWhatsAppMessageWithPDF']);

//Route::get('/register/{lang?}', function () {
//    $settings = Utility::settings();
//    $lang = $settings['default_language'];
//
//    if($settings['enable_signup'] == 'on'){
//        return view("auth.register", compact('lang'));
//       // Route::get('/register', 'Auth\RegisteredUserController@showRegistrationForm')->name('register');
//    }else{
//        return Redirect::to('login');
//    }
//
//});


Route::post('register', [RegisteredUserController::class, 'store'])->name('register');
Route::get('/login/{lang?}', [AuthenticatedSessionController::class, 'showLoginForm'])->name('login');

///copy link
Route::get('/customer/invoice/{id}/', [InvoiceController::class, 'invoiceLink'])->name('invoice.link.copy');
Route::get('/vender/bill/{id}/', [BillController::class, 'invoiceLink'])->name('bill.link.copy');
Route::get('/vendor/purchase/{id}/', [PurchaseController::class, 'purchaseLink'])->name('purchase.link.copy');
Route::get('/customer/proposal/{id}/', [ProposalController::class, 'invoiceLink'])->name('proposal.link.copy');
Route::get('proposal/pdf/{id}', [ProposalController::class, 'proposal'])->name('proposal.pdf')->middleware(['XSS', 'revalidate']);


//================================= Invoice Payment Gateways  ====================================//

Route::post('/customer-pay-with-bank', [BankTransferPaymentController::class, 'customerPayWithBank'])->name('customer.pay.with.bank')->middleware(['XSS']);
Route::get('invoice/{id}/action', [BankTransferPaymentController::class, 'invoiceAction'])->name('invoice.action');
Route::post('invoice/{id}/changeaction', [BankTransferPaymentController::class, 'invoiceChangeStatus'])->name('invoice.changestatus');




Route::post('{id}/pay-with-paypal', [PaypalController::class, 'customerPayWithPaypal'])->name('customer.pay.with.paypal');
Route::get('{id}/get-payment-status/{amount}', [PaypalController::class, 'customerGetPaymentStatus'])->name('customer.get.payment.status')->middleware(['XSS',]);

Route::post('/customer-pay-with-paystack', [PaystackPaymentController::class, 'customerPayWithPaystack'])->name('customer.pay.with.paystack')->middleware(['XSS']);
Route::get('/customer/paystack/{pay_id}/{invoice_id}', [PaystackPaymentController::class, 'getInvoicePaymentStatus'])->name('customer.paystack');

Route::post('/customer-pay-with-paytm', [PaytmPaymentController::class, 'customerPayWithPaytm'])->name('customer.pay.with.paytm')->middleware(['XSS']);
Route::post('/customer/paytm/{invoice}/{amount}', [PaytmPaymentController::class, 'getInvoicePaymentStatus'])->name('customer.paytm');

Route::post('/customer-pay-with-flaterwave', [FlutterwavePaymentController::class, 'customerPayWithFlutterwave'])->name('customer.pay.with.flaterwave')->middleware(['XSS']);
Route::get('/customer/flaterwave/{txref}/{invoice_id}', [FlutterwavePaymentController::class, 'getInvoicePaymentStatus'])->name('customer.flaterwave');

Route::post('/customer-pay-with-razorpay', [RazorpayPaymentController::class, 'customerPayWithRazorpay'])->name('customer.pay.with.razorpay')->middleware(['XSS']);
Route::get('/customer/razorpay/{txref}/{invoice_id}', [RazorpayPaymentController::class, 'getInvoicePaymentStatus'])->name('customer.razorpay');

Route::post('/customer-pay-with-mercado', [MercadoPaymentController::class, 'customerPayWithMercado'])->name('customer.pay.with.mercado')->middleware(['XSS']);
Route::get('/customer/mercado/{invoice}', [MercadoPaymentController::class, 'getInvoicePaymentStatus'])->name('customer.mercado');

Route::post('/customer-pay-with-mollie', [MolliePaymentController::class, 'customerPayWithMollie'])->name('customer.pay.with.mollie')->middleware(['XSS']);
Route::get('/customer/mollie/{invoice}/{amount}', [MolliePaymentController::class, 'getInvoicePaymentStatus'])->name('customer.mollie');


Route::post('/customer-pay-with-skrill', [SkrillPaymentController::class, 'customerPayWithSkrill'])->name('customer.pay.with.skrill')->middleware(['XSS']);
Route::get('/customer/skrill/{invoice}/{amount}', [SkrillPaymentController::class, 'getInvoicePaymentStatus'])->name('customer.skrill');


Route::post('/customer-pay-with-coingate', [CoingatePaymentController::class, 'customerPayWithCoingate'])->name('customer.pay.with.coingate')->middleware(['XSS']);
Route::get('/customer/coingate/{invoice}/{amount}', [CoingatePaymentController::class, 'getInvoicePaymentStatus'])->name('customer.coingate');

Route::post('/paymentwall', [PaymentWallPaymentController::class, 'invoicepaymentwall'])->name('invoice.paymentwallpayment')->middleware(['XSS']);
Route::post('/invoice-pay-with-paymentwall/{invoice}', [PaymentWallPaymentController::class, 'invoicePayWithPaymentwall'])->name('invoice.pay.with.paymentwall')->middleware(['XSS']);
Route::get('/invoices/{flag}/{invoice}', [PaymentWallPaymentController::class, 'invoiceerror'])->name('error.invoice.show');

Route::post('/customer-pay-with-toyyibpay', [ToyyibpayController::class, 'invoicepaywithtoyyibpay'])->name('customer.pay.with.toyyibpay');
Route::get('/customer/toyyibpay/{invoice}/{amount}', [ToyyibpayController::class, 'getInvoicePaymentStatus'])->name('customer.toyyibpay');

Route::post('invoice-with-payfast', [PayFastController::class, 'invoicePayWithPayFast'])->name('invoice.with.payfast');
Route::get('invoice-payfast-status/{success}', [PayFastController::class, 'invoicepayfaststatus'])->name('invoice.payfast.status');

Route::post('/customer-pay-with-iyzipay', [IyziPayController::class, 'invoicepaywithiyzipay'])->name('customer.pay.with.iyzipay');
Route::post('iyzipay/callback/{invoice}/{amount}', [IyzipayController::class, 'getInvoiceiyzipayCallback'])->name('iyzipay.invoicepayment.callback');

Route::post('/customer-pay-with-sspay', [SspayController::class, 'invoicepaywithsspaypay'])->name('customer.pay.with.sspay');
Route::get('/customer/sspay/{invoice}/{amount}', [SspayController::class, 'getInvoicePaymentStatus'])->name('customer.sspay');

Route::post('/invoice-pay-with-paytab', [PaytabController::class, 'invoicePayWithpaytab'])->name('customer.pay.with.paytab');
Route::any('/invoice-paytab-success/{invoice}', [PaytabController::class, 'getInvoicePaymentStatus'])->name('invoice.paytab.success');

Route::any('invoice-with-benefit', [BenefitPaymentController::class, 'invoicepaywithbenefit'])->name('invoice.benefit.initiate');
Route::any('/invoice/benefit/{invoice_id}/{amount}', [BenefitPaymentController::class, 'getInvoicePaymentStatus'])->name('invoice.benefit.call_back');

Route::post('invoice-with-cashfree', [CashfreeController::class, 'invoicepaywithcashfree'])->name('customer.pay.with.cashfree');
Route::any('invoice-with-cashfree/cashfree', [CashfreeController::class, 'getInvoicePaymentStatus'])->name('invoice.cashfreePayment.success');

Route::post('invoice-with-aamarpay', [AamarpayController::class, 'invoicepaywithaamarpay'])->name('customer.pay.with.aamarpay');
Route::any('aamarpay-invoice/success/{data}', [AamarpayController::class, 'getInvoicePaymentStatus'])->name('invoice.pay.aamarpay.success');

Route::post('/invoice-with-paytr', [PaytrController::class, 'invoicepaywithpaytr'])->name('customer.pay.with.paytr');
Route::get('/invoice/paytr/status', [PaytrController::class, 'getInvoicePaymentStatus'])->name('invoice.paytr');


/***********************************************************************************************************************************************/



//career page
Route::get('career/{id}/{lang}', [JobController::class, 'career'])->name('career')->middleware(['XSS']);
Route::get('job/requirement/{code}/{lang}', [JobController::class, 'jobRequirement'])->name('job.requirement')->middleware(['XSS']);
Route::get('job/apply/{code}/{lang}', [JobController::class, 'jobApply'])->name('job.apply')->middleware(['XSS']);
Route::post('job/apply/data/{code}', [JobController::class, 'jobApplyData'])->name('job.apply.data')->middleware(['XSS']);

//project copy module
Route::get('/projects/copylink/{id}', [ProjectController::class, 'projectCopyLink'])->name('projects.copylink');
Route::any('/projects/link/{id}/{lang?}', [ProjectController::class, 'projectlink'])->name('projects.link')->middleware(['XSS']);
Route::get('timesheet-table-view', [TimesheetController::class, 'filterTimesheetTableView'])->name('filter.timesheet.table.view')->middleware(['XSS']);



// Invoice Payment Gateways
Route::post('customer/{id}/payment', [StripePaymentController::class, 'addpayment'])->name('customer.payment');
Route::get('invoice/pdf/{id}', [InvoiceController::class, 'invoice'])->name('invoice.pdf')->middleware(['XSS', 'revalidate']);


//================================= Invoice Payment Gateways  ====================================//
Route::group(['middleware' => ['verified']], function () {


    Route::get('/account-dashboard', [DashboardController::class, 'account_dashboard_index'])->name('dashboard')->middleware(['auth', 'XSS', 'revalidate']);
    Route::get('/project-dashboard', [DashboardController::class, 'project_dashboard_index'])->name('project.dashboard')->middleware(['auth', 'XSS', 'revalidate']);
    Route::get('/hrm-dashboard', [DashboardController::class, 'hrm_dashboard_index'])->name('hrm.dashboard')->middleware(['auth', 'XSS', 'revalidate']);
    Route::get('/crm-dashboard', [DashboardController::class, 'crm_dashboard_index'])->name('crm.dashboard')->middleware(['auth', 'XSS', 'revalidate']);
    Route::get('/pos-dashboard', [DashboardController::class, 'pos_dashboard_index'])->name('pos.dashboard')->middleware(['auth', 'XSS', 'revalidate']);
    Route::get('/workspace-dashboard', [DashboardController::class, 'workspace_dashboard_index'])->name('workspace.dashboard')->middleware(['auth', 'XSS', 'revalidate']);
    Route::get('/clientuser-dashboard', [DashboardController::class, 'clientuser_dashboard_index'])->name('clientuser.dashboard')->middleware(['auth', 'XSS', 'revalidate']);


    Route::get('profile', [UserController::class, 'profile'])->name('profile')->middleware(['auth', 'XSS', 'revalidate']);

    Route::any('edit-profile', [UserController::class, 'editprofile'])->name('update.account')->middleware(['auth', 'XSS', 'revalidate']);

    Route::resource('users', UserController::class)->middleware(['auth', 'XSS', 'revalidate']);

    Route::post('change-password', [UserController::class, 'updatePassword'])->name('update.password');

    Route::any('user-reset-password/{id}', [UserController::class, 'userPassword'])->name('users.reset');

    Route::post('user-reset-password/{id}', [UserController::class, 'userPasswordReset'])->name('user.password.update');

    Route::get('/change/mode', [UserController::class, 'changeMode'])->name('change.mode');

    Route::resource('roles', RoleController::class)->middleware(['auth', 'XSS', 'revalidate']);

    Route::resource('permissions', PermissionController::class)->middleware(['auth', 'XSS', 'revalidate']);

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('change-language/{lang}', [LanguageController::class, 'changeLanquage'])->name('change.language');

            Route::get('manage-language/{lang}', [LanguageController::class, 'manageLanguage'])->name('manage.language');

            Route::post('store-language-data/{lang}', [LanguageController::class, 'storeLanguageData'])->name('store.language.data');

            Route::get('create-language', [LanguageController::class, 'createLanguage'])->name('create.language');

            Route::any('store-language', [LanguageController::class, 'storeLanguage'])->name('store.language');

            Route::delete('/lang/{lang}', [LanguageController::class, 'destroyLang'])->name('lang.destroy');

        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('systems', SystemController::class);
            Route::post('email-settings', [SystemController::class, 'saveEmailSettings'])->name('email.settings');
            Route::post('company-email-settings', [SystemController::class, 'saveCompanyEmailSettings'])->name('company.email.settings');

            Route::post('company-settings', [SystemController::class, 'saveCompanySettings'])->name('company.settings');
            Route::post('system-settings', [SystemController::class, 'saveSystemSettings'])->name('system.settings');
            Route::post('zoom-settings', [SystemController::class, 'saveZoomSettings'])->name('zoom.settings');
            Route::post('tracker-settings', [SystemController::class, 'saveTrackerSettings'])->name('tracker.settings');
            Route::post('slack-settings', [SystemController::class, 'saveSlackSettings'])->name('slack.settings');
            Route::post('telegram-settings', [SystemController::class, 'saveTelegramSettings'])->name('telegram.settings');
            Route::post('twilio-settings', [SystemController::class, 'saveTwilioSettings'])->name('twilio.setting');
            Route::get('print-setting', [SystemController::class, 'printIndex'])->name('print.setting');
            Route::get('settings', [SystemController::class, 'companyIndex'])->name('settings');
            Route::post('business-setting', [SystemController::class, 'saveBusinessSettings'])->name('business.setting');
            Route::post('save-setting', [SystemController::class, 'saveSettings'])->name('save.setting');
            Route::post('company-payment-setting', [SystemController::class, 'saveCompanyPaymentSettings'])->name('company.payment.settings');

            Route::get('test-mail', [SystemController::class, 'testMail'])->name('test.mail');
            Route::post('test-mail', [SystemController::class, 'testMail'])->name('test.mail');
            Route::post('test-mail/send', [SystemController::class, 'testSendMail'])->name('test.send.mail');

            Route::post('stripe-settings', [SystemController::class, 'savePaymentSettings'])->name('payment.settings');
            Route::post('pusher-setting', [SystemController::class, 'savePusherSettings'])->name('pusher.setting');
            Route::post('recaptcha-settings', [SystemController::class, 'recaptchaSettingStore'])->name('recaptcha.settings.store')->middleware(['auth', 'XSS']);


            Route::post('seo-settings', [SystemController::class, 'seoSettings'])->name('seo.settings.store')->middleware(['auth', 'XSS']);
            Route::any('webhook-settings', [SystemController::class, 'webhook'])->name('webhook.settings')->middleware(['auth', 'XSS']);
            Route::get('webhook-settings/create', [SystemController::class, 'webhookCreate'])->name('webhook.create')->middleware(['auth', 'XSS']);
            Route::post('webhook-settings/store', [SystemController::class, 'webhookStore'])->name('webhook.store');
            Route::get('webhook-settings/{wid}/edit', [SystemController::class, 'webhookEdit'])->name('webhook.edit')->middleware(['auth', 'XSS']);
            Route::post('webhook-settings/{wid}/edit', [SystemController::class, 'webhookUpdate'])->name('webhook.update')->middleware(['auth', 'XSS']);
            Route::delete('webhook-settings/{wid}', [SystemController::class, 'webhookDestroy'])->name('webhook.destroy')->middleware(['auth', 'XSS']);

            Route::post('cookie-setting', [SystemController::class, 'saveCookieSettings'])->name('cookie.setting');

            Route::post('cache-settings', [SystemController::class, 'cacheSettingStore'])->name('cache.settings.store')->middleware(['auth', 'XSS']);

        }
    );

    Route::get('/printproductlist/{id?}', [ProductServiceController::class, 'printproductslist'])->name('printproductslist');

    //
    Route::get('productservice/index', [ProductServiceController::class, 'index'])->name('productservice.index');
    Route::get('/get-subcategories/{categoryId}', [ProductServiceController::class, 'getSubcategories']);
    Route::get('productservice/{id}/detail', [ProductServiceController::class, 'warehouseDetail'])->name('productservice.detail');
    Route::post('empty-cart', [ProductServiceController::class, 'emptyCart'])->middleware(['auth', 'XSS']);
    Route::post('warehouse-empty-cart', [ProductServiceController::class, 'warehouseemptyCart'])->name('warehouse-empty-cart')->middleware(['auth', 'XSS']);
    Route::resource('productservice', ProductServiceController::class)->middleware(['auth', 'XSS', 'revalidate']);


    //Product Stock
    Route::resource('productstock', ProductStockController::class)->middleware(['auth', 'XSS']);

    //Customer
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('customer/{id}/show', [CustomerController::class, 'show'])->name('customer.show');
            Route::resource('customer', CustomerController::class);
        }
    );

    //Vendor
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('vender/{id}/show', [VenderController::class, 'show'])->name('vender.show');
            Route::resource('vender', VenderController::class);
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('bank-account/{id}/statement', [BankAccountController::class, 'statement'])->name('bank-account.statement');
            Route::post('bank-account/{id}/statement/export', [BankAccountController::class, 'statementExport'])->name('bank-account.statement.export');
            Route::resource('bank-account', BankAccountController::class);
        }
    );
    Route::get('/bank-transfer/reference/{id}', [BankTransferController::class, 'getReference'])
        ->name('bank.transfer.reference');
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('bank-transfer/index', [BankTransferController::class, 'index'])->name('bank-transfer.index');
            Route::resource('bank-transfer', BankTransferController::class);
        }
    );

    Route::resource('taxes', TaxController::class)->middleware(['auth', 'XSS', 'revalidate']);

    Route::resource('product-category', ProductServiceCategoryController::class)->middleware(['auth', 'XSS', 'revalidate']);
    Route::resource('product-sub-category', ProductServiceSubCategoryController::class)->middleware(['auth', 'XSS', 'revalidate']);
    Route::post('sub_category', [ProductServiceSubCategoryController::class, 'sub_category'])->name('sub_category');

    Route::post('product-category/getaccount', [ProductServiceCategoryController::class, 'getAccount'])->name('productServiceCategory.getaccount')->middleware(['auth', 'XSS', 'revalidate']);



    Route::resource('product-unit', ProductServiceUnitController::class)->middleware(['auth', 'XSS', 'revalidate']);



    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('invoice/{id}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoice.duplicate');
            Route::get('invoice/{id}/shipping/print', [InvoiceController::class, 'shippingDisplay'])->name('invoice.shipping.print');
            Route::get('invoice/{id}/payment/reminder', [InvoiceController::class, 'paymentReminder'])->name('invoice.payment.reminder');
            Route::get('invoice/index', [InvoiceController::class, 'index'])->name('invoice.index');
            Route::post('invoice/product/destroy', [InvoiceController::class, 'productDestroy'])->name('invoice.product.destroy');
            Route::post('invoice/product', [InvoiceController::class, 'product'])->name('invoice.product');
            Route::post('invoice/customer', [InvoiceController::class, 'customer'])->name('invoice.customer');
            Route::get('invoice/{id}/sent', [InvoiceController::class, 'sent'])->name('invoice.sent');
            Route::get('invoice/{id}/resent', [InvoiceController::class, 'resent'])->name('invoice.resent');
            Route::get('invoice/{id}/payment', [InvoiceController::class, 'payment'])->name('invoice.payment');
            Route::post('invoice/{id}/payment', [InvoiceController::class, 'createPayment'])->name('invoice.payment');
            Route::post('invoice/{id}/payment/{pid}/destroy', [InvoiceController::class, 'paymentDestroy'])->name('invoice.payment.destroy');
            Route::get('invoice/items', [InvoiceController::class, 'items'])->name('invoice.items');



            Route::resource('invoice', InvoiceController::class);
            Route::get('invoice/create/{cid}', [InvoiceController::class, 'create'])->name('invoice.create');
            Route::post('company_contract', [InvoiceController::class, 'companycontract'])->name('company_contract');
            Route::post('company_contract_detail', [InvoiceController::class, 'companycontractdetail'])->name('company_contract_detail');
            Route::get('invoice_next/{id}', [InvoiceController::class, 'next'])->name('invoice_next');
            Route::get('invoice_back/{id}', [InvoiceController::class, 'back'])->name('invoice_back');
            Route::post('branch_customer', [InvoiceController::class, 'branchCustomer'])->name('branch.customer');
            Route::post('branch_vender', [InvoiceController::class, 'branchVender'])->name('branch.vendor');

            //report
            Route::get('invoice-report', [InvoiceController::class, 'inv_rep'])->name('invoice.report');
            Route::get('invoice_rep-report', [InvoiceController::class, 'invoiceReport'])->name('invoice_rep.report');
            Route::get('invoice-product-report', [InvoiceController::class, 'inv_rep_product'])->name('invoiceproduct.report');

        }
    );

    Route::get('/invoices/preview/{template}/{color}', [InvoiceController::class, 'previewInvoice'])->name('invoice.preview');
    Route::post('/invoices/template/setting', [InvoiceController::class, 'saveTemplateSettings'])->name('template.setting');

    Route::get('/invoices/bulk_create', [InvoiceController::class, 'bulk_invoice'])->name('invoice.bulk_create');

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('credit-note', [CreditNoteController::class, 'index'])->name('credit.note');
            Route::get('custom-credit-note', [CreditNoteController::class, 'customCreate'])->name('invoice.custom.credit.note');
            Route::post('custom-credit-note', [CreditNoteController::class, 'customStore'])->name('invoice.custom.credit.note');
            Route::get('credit-note/invoice', [CreditNoteController::class, 'getinvoice'])->name('invoice.get');
            Route::get('invoice/{id}/credit-note', [CreditNoteController::class, 'create'])->name('invoice.credit.note');
            Route::post('invoice/{id}/credit-note', [CreditNoteController::class, 'store'])->name('invoice.credit.note');
            Route::get('invoice/{id}/credit-note/edit/{cn_id}', [CreditNoteController::class, 'edit'])->name('invoice.edit.credit.note');
            Route::post('invoice/{id}/credit-note/edit/{cn_id}', [CreditNoteController::class, 'update'])->name('invoice.edit.credit.note');
            Route::delete('invoice/{id}/credit-note/delete/{cn_id}', [CreditNoteController::class, 'destroy'])->name('invoice.delete.credit.note');
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('debit-note', [DebitNoteController::class, 'index'])->name('debit.note');
            Route::get('custom-debit-note', [DebitNoteController::class, 'customCreate'])->name('bill.custom.debit.note');
            Route::post('custom-debit-note', [DebitNoteController::class, 'customStore'])->name('bill.custom.debit.note');
            Route::get('debit-note/bill', [DebitNoteController::class, 'getbill'])->name('bill.get');
            Route::get('bill/{id}/debit-note', [DebitNoteController::class, 'create'])->name('bill.debit.note');
            Route::post('bill/{id}/debit-note', [DebitNoteController::class, 'store'])->name('bill.debit.note');
            Route::get('bill/{id}/debit-note/edit/{cn_id}', [DebitNoteController::class, 'edit'])->name('bill.edit.debit.note');
            Route::post('bill/{id}/debit-note/edit/{cn_id}', [DebitNoteController::class, 'update'])->name('bill.edit.debit.note');
            Route::delete('bill/{id}/debit-note/delete/{cn_id}', [DebitNoteController::class, 'destroy'])->name('bill.delete.debit.note');
        }
    );

    Route::get('/bill/preview/{template}/{color}', [BillController::class, 'previewBill'])->name('bill.preview')->middleware(['auth', 'XSS',]);
    Route::post('/bill/template/setting', [BillController::class, 'saveBillTemplateSettings'])->name('bill.template.setting');

    Route::resource('taxes', TaxController::class)->middleware(['auth', 'XSS', 'revalidate']);

    Route::get('revenue/index', [RevenueController::class, 'index'])->name('revenue.index')->middleware(['auth', 'XSS', 'revalidate']);

    Route::resource('revenue', RevenueController::class)->middleware(['auth', 'XSS', 'revalidate']);
    Route::post('branch_revenue_data', [RevenueController::class, 'branch_revenue_data'])->name('branch.revenue_data')->middleware(['auth', 'XSS', 'revalidate']);

    Route::get('bill/pdf/{id}', [BillController::class, 'bill'])->name('bill.pdf')->middleware(['XSS', 'revalidate']);

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('bill/{id}/duplicate', [BillController::class, 'duplicate'])->name('bill.duplicate');
            Route::get('bill/{id}/shipping/print', [BillController::class, 'shippingDisplay'])->name('bill.shipping.print');
            Route::get('bill/index', [BillController::class, 'index'])->name('bill.index');
            Route::post('bill/product/destroy', [BillController::class, 'productDestroy'])->name('bill.product.destroy');
            Route::post('bill/product', [BillController::class, 'product'])->name('bill.product');
            Route::post('bill/vender', [BillController::class, 'vender'])->name('bill.vender');
            Route::get('bill/{id}/sent', [BillController::class, 'sent'])->name('bill.sent');
            Route::get('bill/{id}/resent', [BillController::class, 'resent'])->name('bill.resent');
            Route::get('bill/{id}/payment', [BillController::class, 'payment'])->name('bill.payment');
            Route::post('bill/{id}/payment', [BillController::class, 'createPayment'])->name('bill.payment');
            Route::post('bill/{id}/payment/{pid}/destroy', [BillController::class, 'paymentDestroy'])->name('bill.payment.destroy');
            Route::get('bill/items', [BillController::class, 'items'])->name('bill.items');
            Route::resource('bill', BillController::class);
            Route::get('bill/create/{cid}', [BillController::class, 'create'])->name('bill.create');
        }
    );

    Route::get('payment/index', [PaymentController::class, 'index'])->name('payment.index')->middleware(['auth', 'XSS', 'revalidate']);

    Route::resource('payment', PaymentController::class)->middleware(['auth', 'XSS', 'revalidate']);
    Route::post('branch_payment_data', [PaymentController::class, 'branch_payment_data'])->name('branch.payment_data')->middleware(['auth', 'XSS', 'revalidate']);

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('report/transaction', [TransactionController::class, 'index'])->name('transaction.index');
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('report/income-summary', [ReportController::class, 'incomeSummary'])->name('report.income.summary');
            Route::get('report/expense-summary', [ReportController::class, 'expenseSummary'])->name('report.expense.summary');
            Route::get('report/income-vs-expense-summary', [ReportController::class, 'incomeVsExpenseSummary'])->name('report.income.vs.expense.summary');
            Route::get('report/tax-summary', [ReportController::class, 'taxSummary'])->name('report.tax.summary');
            //        Route::get('report/profit-loss-summary', [ReportController::class, 'profitLossSummary'])->name('report.profit.loss.summary');
            Route::get('report/invoice-summary', [ReportController::class, 'invoiceSummary'])->name('report.invoice.summary');
            Route::get('report/bill-summary', [ReportController::class, 'billSummary'])->name('report.bill.summary');
            Route::get('report/product-stock-report', [ReportController::class, 'productStock'])->name('report.product.stock.report');
            Route::get('report/invoice-report', [ReportController::class, 'invoiceReport'])->name('report.invoice');
            Route::get('report/account-statement-report', [ReportController::class, 'accountStatement'])->name('report.account.statement');
            Route::get('report/balance-sheet/{view?}', [ReportController::class, 'balanceSheet'])->name('report.balance.sheet');
            Route::get('report/profit-loss/{view?}', [ReportController::class, 'profitLoss'])->name('report.profit.loss');

            Route::get('report/ledger/{account?}', [ReportController::class, 'ledgerSummary'])->name('report.ledger');
            Route::post('export/ledger', [ReportController::class, 'ledgerSummaryExport'])->name('ledger.export');
            Route::get('report/trial-balance', [ReportController::class, 'trialBalanceSummary'])->name('trial.balance');

            Route::get('reports-monthly-cashflow', [ReportController::class, 'monthlyCashflow'])->name('report.monthly.cashflow')->middleware(['auth', 'XSS']);
            Route::get('reports-quarterly-cashflow', [ReportController::class, 'quarterlyCashflow'])->name('report.quarterly.cashflow')->middleware(['auth', 'XSS']);
            Route::post('export/trial-balance', [ReportController::class, 'trialBalanceExport'])->name('trial.balance.export');
            Route::post('export/balance-sheet', [ReportController::class, 'balanceSheetExport'])->name('balance.sheet.export');
            Route::post('print/balance-sheet/{view?}', [ReportController::class, 'balanceSheetPrint'])->name('balance.sheet.print');
            Route::post('print/trial-balance', [ReportController::class, 'trialBalancePrint'])->name('trial.balance.print');
            Route::post('export/profit-loss', [ReportController::class, 'profitLossExport'])->name('profit.loss.export');
            Route::post('print/profit-loss/{view?}', [ReportController::class, 'profitLossPrint'])->name('profit.loss.print');
            Route::get('report/sales', [ReportController::class, 'salesReport'])->name('report.sales');
            Route::post('export/sales', [ReportController::class, 'salesReportExport'])->name('sales.export');
            Route::post('print/sales-report', [ReportController::class, 'salesReportPrint'])->name('sales.report.print');
            Route::get('report/receivables', [ReportController::class, 'ReceivablesReport'])->name('report.receivables');
            Route::post('export/receivables', [ReportController::class, 'ReceivablesExport'])->name('receivables.export');
            Route::post('print/receivables', [ReportController::class, 'ReceivablesPrint'])->name('receivables.print');
        }
    );

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('proposal/{id}/status/change', [ProposalController::class, 'statusChange'])->name('proposal.status.change');
            Route::get('proposal/{id}/convert', [ProposalController::class, 'convert'])->name('proposal.convert');
            Route::get('proposal/{id}/duplicate', [ProposalController::class, 'duplicate'])->name('proposal.duplicate');
            Route::post('proposal/product/destroy', [ProposalController::class, 'productDestroy'])->name('proposal.product.destroy');
            Route::post('proposal/customer', [ProposalController::class, 'customer'])->name('proposal.customer');
            Route::post('proposal/product', [ProposalController::class, 'product'])->name('proposal.product');
            Route::get('proposal/items', [ProposalController::class, 'items'])->name('proposal.items');
            Route::get('proposal/{id}/sent', [ProposalController::class, 'sent'])->name('proposal.sent');
            Route::get('proposal/{id}/resent', [ProposalController::class, 'resent'])->name('proposal.resent');
            Route::resource('proposal', ProposalController::class);
            Route::get('proposal/create/{cid}', [ProposalController::class, 'create'])->name('proposal.create');

        }
    );

    Route::get('/proposal/preview/{template}/{color}', [ProposalController::class, 'previewProposal'])->name('proposal.preview');
    Route::post('/proposal/template/setting', [ProposalController::class, 'saveProposalTemplateSettings'])->name('proposal.template.setting');


    Route::resource('goal', GoalController::class)->middleware(['auth', 'XSS', 'revalidate']);

    //Budget Planner //
    Route::resource('budget', BudgetController::class)->middleware(['auth', 'XSS', 'revalidate']);

    Route::resource('account-assets', AssetController::class)->middleware(['auth', 'XSS', 'revalidate']);

    Route::resource('custom-field', CustomFieldController::class)->middleware(['auth', 'XSS', 'revalidate']);

    Route::post('chart-of-account/subtype', [ChartOfAccountController::class, 'getSubType'])->name('charofAccount.subType')->middleware(['auth', 'XSS', 'revalidate']);
    //invoice reports
    Route::get('invoice-report', [InvoiceController::class, 'inv_rep'])->name('invoice_report');
    Route::get('invoice_product_rep/report', [InvoiceController::class, 'invoiceProductReport'])->name('invoice_product_rep.report');
    Route::get('invoice-product-report', [InvoiceController::class, 'inv_pro_rep'])->name('invoice_product_report');
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('chart-of-account', ChartOfAccountController::class);
        }
    );
    // vouchers
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {

            Route::post('journal-entry/account/destroy', [JournalEntryController::class, 'accountDestroy'])->name('journal.account.destroy');

            Route::delete('journal-entry/journal/destroy/{item_id}', [JournalEntryController::class, 'journalDestroy'])->name('journal.destroy');
            Route::resource('journal-entry', JournalEntryController::class);


            Route::post('bank-recipt-voucher/account/destroy', [BankReciptVoucherController::class, 'brvDestroy'])->name('brv.account.destroy');
            Route::delete('bank-recipt-voucher/journal/destroy/{item_id}', [BankReciptVoucherController::class, 'brvDestroy'])->name('brv.destroy');
            Route::resource('bank-recipt-voucher', BankReciptVoucherController::class);
            Route::post('bank-payment-voucher/account/destroy', [BankPaymentVoucherController::class, 'bpvaccountDestroy'])->name('bpv.account.destroy');
            Route::delete('bank-payment-voucher/journal/destroy/{item_id}', [BankPaymentVoucherController::class, 'bpvjournalDestroy'])->name('bpv.destroy');
            Route::resource('bank-payment-voucher', BankPaymentVoucherController::class);

            Route::post('cash-recipt-voucher/account/destroy', [CashReciptVoucherController::class, 'crvDestroy'])->name('crv.account.destroy');
            Route::delete('cash-recipt-voucher/journal/destroy/{item_id}', [CashReciptVoucherController::class, 'crvDestroy'])->name('crv.destroy');
            Route::resource('cash-recipt-voucher', CashReciptVoucherController::class);
            Route::post('cash-payment-voucher/account/destroy', [CashPaymentVoucherController::class, 'cpvDestroy'])->name('cpv.account.destroy');
            Route::delete('cash-payment-voucher/journal/destroy/{item_id}', [CashPaymentVoucherController::class, 'cpvDestroy'])->name('cpv.destroy');
            Route::resource('cash-payment-voucher', CashPaymentVoucherController::class);
        }
    );
    //Is Mail
    Route::resource('ismail', IsMailController::class)->middleware(['auth', 'XSS']);
    //IsVsitor
    Route::resource('isvisitor', IsVisitorController::class)->middleware(['auth', 'XSS']);

    // Space Module
    Route::resource('spacetype', SpaceTypeController::class)->middleware(['auth', 'XSS']);
    Route::resource('space', SpaceController::class)->middleware(['auth', 'XSS']);
    Route::resource('chair', ChairController::class)->middleware(['auth', 'XSS']);
    Route::get('space_chair/{id}/{con?}', [ChairController::class, 'space_chair'])->name('space_chair');
    Route::get('space_details/{type}', [SpaceController::class, 'space_details'])->name('space_details');

    // Branch Module
    Route::resource('clientuser', ClientUserController::class)->middleware(['auth', 'XSS']);
    // Branch Module
    Route::resource('branches', BranchesController::class)->middleware(['auth', 'XSS']);
    Route::any('branch-reset-password/{id}', [BranchesController::class, 'branchPassword'])->name('branch.reset');
    Route::post('branch-reset-password/{id}', [BranchesController::class, 'branchPasswordReset'])->name('branch.password.update');

    // Client Module

    Route::resource('clients', ClientController::class)->middleware(['auth', 'XSS']);

    Route::any('client-reset-password/{id}', [ClientController::class, 'clientPassword'])->name('clients.reset');
    Route::post('client-reset-password/{id}', [ClientController::class, 'clientPasswordReset'])->name('client.password.update');

    // Deal Module

    Route::post('/deals/user', [DealController::class, 'jsonUser'])->name('deal.user.json');
    Route::post('/deals/order', [DealController::class, 'order'])->name('deals.order')->middleware(['auth', 'XSS']);
    Route::post('/deals/change-pipeline', [DealController::class, 'changePipeline'])->name('deals.change.pipeline')->middleware(['auth', 'XSS']);
    Route::post('/deals/change-deal-status/{id}', [DealController::class, 'changeStatus'])->name('deals.change.status')->middleware(['auth', 'XSS']);
    Route::get('/deals/{id}/labels', [DealController::class, 'labels'])->name('deals.labels')->middleware(['auth', 'XSS']);
    Route::post('/deals/{id}/labels', [DealController::class, 'labelStore'])->name('deals.labels.store')->middleware(['auth', 'XSS']);
    Route::get('/deals/{id}/users', [DealController::class, 'userEdit'])->name('deals.users.edit')->middleware(['auth', 'XSS']);
    Route::put('/deals/{id}/users', [DealController::class, 'userUpdate'])->name('deals.users.update')->middleware(['auth', 'XSS']);
    Route::delete('/deals/{id}/users/{uid}', [DealController::class, 'userDestroy'])->name('deals.users.destroy')->middleware(['auth', 'XSS']);
    Route::get('/deals/{id}/clients', [DealController::class, 'clientEdit'])->name('deals.clients.edit')->middleware(['auth', 'XSS']);
    Route::put('/deals/{id}/clients', [DealController::class, 'clientUpdate'])->name('deals.clients.update')->middleware(['auth', 'XSS']);
    Route::delete('/deals/{id}/clients/{uid}', [DealController::class, 'clientDestroy'])->name('deals.clients.destroy')->middleware(['auth', 'XSS']);
    Route::get('/deals/{id}/products', [DealController::class, 'productEdit'])->name('deals.products.edit')->middleware(['auth', 'XSS']);
    Route::put('/deals/{id}/products', [DealController::class, 'productUpdate'])->name('deals.products.update')->middleware(['auth', 'XSS']);
    Route::delete('/deals/{id}/products/{uid}', [DealController::class, 'productDestroy'])->name('deals.products.destroy')->middleware(['auth', 'XSS']);
    Route::get('/deals/{id}/sources', [DealController::class, 'sourceEdit'])->name('deals.sources.edit')->middleware(['auth', 'XSS']);
    Route::put('/deals/{id}/sources', [DealController::class, 'sourceUpdate'])->name('deals.sources.update')->middleware(['auth', 'XSS']);
    Route::delete('/deals/{id}/sources/{uid}', [DealController::class, 'sourceDestroy'])->name('deals.sources.destroy')->middleware(['auth', 'XSS']);
    Route::post('/deals/{id}/file', [DealController::class, 'fileUpload'])->name('deals.file.upload')->middleware(['auth', 'XSS']);
    Route::get('/deals/{id}/file/{fid}', [DealController::class, 'fileDownload'])->name('deals.file.download')->middleware(['auth', 'XSS']);
    Route::delete('/deals/{id}/file/delete/{fid}', [DealController::class, 'fileDelete'])->name('deals.file.delete')->middleware(['auth', 'XSS']);
    Route::post('/deals/{id}/note', [DealController::class, 'noteStore'])->name('deals.note.store')->middleware(['auth']);
    Route::get('/deals/{id}/task', [DealController::class, 'taskCreate'])->name('deals.tasks.create')->middleware(['auth', 'XSS']);
    Route::post('/deals/{id}/task', [DealController::class, 'taskStore'])->name('deals.tasks.store')->middleware(['auth', 'XSS']);
    Route::get('/deals/{id}/task/{tid}/show', [DealController::class, 'taskShow'])->name('deals.tasks.show')->middleware(['auth', 'XSS']);
    Route::get('/deals/{id}/task/{tid}/edit', [DealController::class, 'taskEdit'])->name('deals.tasks.edit')->middleware(['auth', 'XSS']);
    Route::put('/deals/{id}/task/{tid}', [DealController::class, 'taskUpdate'])->name('deals.tasks.update')->middleware(['auth', 'XSS']);
    Route::put('/deals/{id}/task_status/{tid}', [DealController::class, 'taskUpdateStatus'])->name('deals.tasks.update_status')->middleware(['auth', 'XSS']);
    Route::delete('/deals/{id}/task/{tid}', [DealController::class, 'taskDestroy'])->name('deals.tasks.destroy')->middleware(['auth', 'XSS']);
    Route::get('/deals/{id}/discussions', [DealController::class, 'discussionCreate'])->name('deals.discussions.create')->middleware(['auth', 'XSS']);
    Route::post('/deals/{id}/discussions', [DealController::class, 'discussionStore'])->name('deals.discussion.store')->middleware(['auth', 'XSS']);
    Route::get('/deals/{id}/permission/{cid}', [DealController::class, 'permission'])->name('deals.client.permission')->middleware(['auth', 'XSS']);
    Route::put('/deals/{id}/permission/{cid}', [DealController::class, 'permissionStore'])->name('deals.client.permissions.store')->middleware(['auth', 'XSS']);
    Route::get('/deals/list', [DealController::class, 'deal_list'])->name('deals.list')->middleware(['auth', 'XSS']);


    // Deal Calls

    Route::get('/deals/{id}/call', [DealController::class, 'callCreate'])->name('deals.calls.create')->middleware(['auth', 'XSS']);
    Route::post('/deals/{id}/call', [DealController::class, 'callStore'])->name('deals.calls.store')->middleware(['auth']);
    Route::get('/deals/{id}/call/{cid}/edit', [DealController::class, 'callEdit'])->name('deals.calls.edit')->middleware(['auth']);
    Route::put('/deals/{id}/call/{cid}', [DealController::class, 'callUpdate'])->name('deals.calls.update')->middleware(['auth']);
    Route::delete('/deals/{id}/call/{cid}', [DealController::class, 'callDestroy'])->name('deals.calls.destroy')->middleware(['auth', 'XSS']);


    // Deal Email

    Route::get('/deals/{id}/email', [DealController::class, 'emailCreate'])->name('deals.emails.create')->middleware(['auth', 'XSS']);
    Route::post('/deals/{id}/email', [DealController::class, 'emailStore'])->name('deals.emails.store')->middleware(['auth', 'XSS']);


    Route::resource('deals', DealController::class)->middleware(['auth', 'XSS']);

    // end Deal Module

    Route::get('/search', [UserController::class, 'search'])->name('search.json');
    Route::post('/stages/order', [StageController::class, 'order'])->name('stages.order');
    Route::post('/stages/json', [StageController::class, 'json'])->name('stages.json');

    Route::resource('stages', StageController::class);
    Route::resource('pipelines', PipelineController::class);
    Route::resource('labels', LabelController::class);
    Route::resource('sources', SourceController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('custom_fields', CustomFieldController::class);


    // Leads Module

    Route::post('/lead_stages/order', [LeadStageController::class, 'order'])->name('lead_stages.order');

    Route::resource('lead_stages', LeadStageController::class)->middleware(['auth']);

    Route::post('/leads/json', [LeadController::class, 'json'])->name('leads.json');
    Route::post('/leads/order', [LeadController::class, 'order'])->name('leads.order')->middleware(['auth', 'XSS']);
    Route::get('/leads/list', [LeadController::class, 'lead_list'])->name('leads.list')->middleware(['auth', 'XSS']);
    Route::post('/leads/{id}/file', [LeadController::class, 'fileUpload'])->name('leads.file.upload')->middleware(['auth', 'XSS']);
    Route::get('/leads/{id}/file/{fid}', [LeadController::class, 'fileDownload'])->name('leads.file.download')->middleware(['auth', 'XSS']);
    Route::delete('/leads/{id}/file/delete/{fid}', [LeadController::class, 'fileDelete'])->name('leads.file.delete')->middleware(['auth', 'XSS']);
    Route::post('/leads/{id}/note', [LeadController::class, 'noteStore'])->name('leads.note.store')->middleware(['auth']);
    Route::get('/leads/{id}/labels', [LeadController::class, 'labels'])->name('leads.labels')->middleware(['auth', 'XSS']);
    Route::post('/leads/{id}/labels', [LeadController::class, 'labelStore'])->name('leads.labels.store')->middleware(['auth', 'XSS']);
    Route::get('/leads/{id}/users', [LeadController::class, 'userEdit'])->name('leads.users.edit')->middleware(['auth', 'XSS']);
    Route::put('/leads/{id}/users', [LeadController::class, 'userUpdate'])->name('leads.users.update')->middleware(['auth', 'XSS']);
    Route::delete('/leads/{id}/users/{uid}', [LeadController::class, 'userDestroy'])->name('leads.users.destroy')->middleware(['auth', 'XSS']);
    Route::get('/leads/{id}/products', [LeadController::class, 'productEdit'])->name('leads.products.edit')->middleware(['auth', 'XSS']);
    Route::put('/leads/{id}/products', [LeadController::class, 'productUpdate'])->name('leads.products.update')->middleware(['auth', 'XSS']);
    Route::delete('/leads/{id}/products/{uid}', [LeadController::class, 'productDestroy'])->name('leads.products.destroy')->middleware(['auth', 'XSS']);
    Route::get('/leads/{id}/sources', [LeadController::class, 'sourceEdit'])->name('leads.sources.edit')->middleware(['auth', 'XSS']);
    Route::put('/leads/{id}/sources', [LeadController::class, 'sourceUpdate'])->name('leads.sources.update')->middleware(['auth', 'XSS']);
    Route::delete('/leads/{id}/sources/{uid}', [LeadController::class, 'sourceDestroy'])->name('leads.sources.destroy')->middleware(['auth', 'XSS']);
    Route::get('/leads/{id}/discussions', [LeadController::class, 'discussionCreate'])->name('leads.discussions.create')->middleware(['auth', 'XSS']);
    Route::post('/leads/{id}/discussions', [LeadController::class, 'discussionStore'])->name('leads.discussion.store')->middleware(['auth', 'XSS']);
    Route::get('/leads/{id}/show_convert', [LeadController::class, 'showConvertToDeal'])->name('leads.convert.deal')->middleware(['auth', 'XSS']);
    Route::post('/leads/{id}/convert', [LeadController::class, 'convertToDeal'])->name('leads.convert.to.deal')->middleware(['auth', 'XSS']);


    // Lead Calls
    Route::get('/leads/{id}/call', [LeadController::class, 'callCreate'])->name('leads.calls.create')->middleware(['auth', 'XSS']);
    Route::post('/leads/{id}/call', [LeadController::class, 'callStore'])->name('leads.calls.store')->middleware(['auth']);
    Route::get('/leads/{id}/call/{cid}/edit', [LeadController::class, 'callEdit'])->name('leads.calls.edit')->middleware(['auth', 'XSS']);
    Route::put('/leads/{id}/call/{cid}', [LeadController::class, 'callUpdate'])->name('leads.calls.update')->middleware(['auth']);
    Route::delete('/leads/{id}/call/{cid}', [LeadController::class, 'callDestroy'])->name('leads.calls.destroy')->middleware(['auth', 'XSS']);


    // Lead Email

    Route::get('/leads/{id}/email', [LeadController::class, 'emailCreate'])->name('leads.emails.create')->middleware(['auth', 'XSS']);
    Route::post('/leads/{id}/email', [LeadController::class, 'emailStore'])->name('leads.emails.store')->middleware(['auth']);

    Route::resource('leads', LeadController::class)->middleware(['auth', 'XSS']);

    // end Leads Module

    Route::get('user/{id}/plan', [UserController::class, 'upgradePlan'])->name('plan.upgrade')->middleware(['auth', 'XSS']);
    Route::get('user/{id}/plan/{pid}', [UserController::class, 'activePlan'])->name('plan.active')->middleware(['auth', 'XSS']);
    Route::get('/{uid}/notification/seen', [UserController::class, 'notificationSeen'])->name('notification.seen');


    // Email Templates
    Route::get('email_template_lang/{id}/{lang?}', [EmailTemplateController::class, 'manageEmailLang'])->name('manage.email.language')->middleware(['auth', 'XSS']);
    Route::any('email_template_store', [EmailTemplateController::class, 'updateStatus'])->name('status.email.language')->middleware(['auth']);
    Route::any('email_template_store/{pid}', [EmailTemplateController::class, 'storeEmailLang'])->name('store.email.language')->middleware(['auth']);
    Route::resource('email_template', EmailTemplateController::class)->middleware(['auth', 'XSS']);
    // End Email Templates

    // HRM
    Route::resource('user', UserController::class)->middleware(['auth', 'XSS']);
    Route::post('employee/json', [EmployeeController::class, 'json'])->name('employee.json')->middleware(['auth', 'XSS']);
    Route::post('branch/employee/json', [EmployeeController::class, 'employeeJson'])->name('branch.employee.json')->middleware(['auth', 'XSS']);
    Route::get('employee-profile', [EmployeeController::class, 'profile'])->name('employee.profile')->middleware(['auth', 'XSS']);
    Route::get('show-employee-profile/{id}', [EmployeeController::class, 'profileShow'])->name('show.employee.profile')->middleware(['auth', 'XSS']);
    Route::put('employee/{id}/update', [EmployeeController::class, 'update'])->name('employee.update')->middleware(['auth', 'XSS']);

    Route::get('lastlogin', [EmployeeController::class, 'lastLogin'])->name('lastlogin')->middleware(['auth', 'XSS']);

    Route::resource('employee', EmployeeController::class)->middleware(['auth', 'XSS']);

    Route::post('employee/getdepartment', [EmployeeController::class, 'getDepartment'])->name('employee.getdepartment')->middleware(['auth', 'XSS']);

    Route::resource('department', DepartmentController::class)->middleware(['auth', 'XSS']);
    Route::post('/department/{departmentId}/update-status', [DepartmentController::class, 'updatedepartmentStatus'])->name('update_department_status');

    Route::resource('designation', DesignationController::class)->middleware(['auth', 'XSS']);
    Route::resource('document', DocumentController::class)->middleware(['auth', 'XSS']);
    Route::resource('branch', BranchController::class)->middleware(['auth', 'XSS']);

    Route::post('employee-designation', [EmployeeController::class, 'getempDesignation'])->name('employee.designation');
    Route::post('employee-department', [EmployeeController::class, 'getempDepartment'])->name('employee.department');
    Route::post('employee-data', [EmployeeController::class, 'employeeData'])->name('employee.data');

    Route::post('employee-details', [EmployeeTransferController::class, 'employeeDetail'])->name('employee.detail');
    Route::post('employee-dep', [EmployeeTransferController::class, 'employeedep'])->name('employee.dep');

    // Hrm EmployeeController

    Route::get('employee/salary/{eid}', [SetSalaryController::class, 'employeeBasicSalary'])->name('employee.basic.salary')->middleware(['auth', 'XSS']);


    //payslip

    Route::resource('paysliptype', PayslipTypeController::class)->middleware(['auth', 'XSS']);
    Route::resource('allowance', AllowanceController::class)->middleware(['auth', 'XSS']);
    Route::resource('commission', CommissionController::class)->middleware(['auth', 'XSS']);
    Route::resource('allowanceoption', AllowanceOptionController::class)->middleware(['auth', 'XSS']);
    Route::resource('loanoption', LoanOptionController::class)->middleware(['auth', 'XSS']);
    Route::resource('deductionoption', DeductionOptionController::class)->middleware(['auth', 'XSS']);
    Route::resource('loan', LoanController::class)->middleware(['auth', 'XSS']);
    Route::resource('saturationdeduction', SaturationDeductionController::class)->middleware(['auth', 'XSS']);
    Route::resource('otherpayment', OtherPaymentController::class)->middleware(['auth', 'XSS']);
    Route::resource('overtime', OvertimeController::class)->middleware(['auth', 'XSS']);


    Route::get('employee/salary/{eid}', [SetSalaryController::class, 'employeeBasicSalary'])->name('employee.basic.salary')->middleware(['auth', 'XSS']);
    Route::post('employee/update/sallary/{id}', [SetSalaryController::class, 'employeeUpdateSalary'])->name('employee.salary.update')->middleware(['auth', 'XSS']);
    Route::get('salary/employeeSalary', [SetSalaryController::class, 'employeeSalary'])->name('employeesalary')->middleware(['auth', 'XSS']);
    Route::resource('setsalary', SetSalaryController::class)->middleware(['auth', 'XSS']);

    Route::get('emp-final-settlement/finalize/{id}', [EmployeeSettlementController::class, 'finalize'])->name('emp-final-settlement.finalize');
    Route::post('emp-final-settlement-adjust', [EmployeeSettlementController::class, 'adjustchallan'])->name('EmployeeSettlement.adjustchallan');
    Route::post('rollback-challan-adjustment', [EmployeeSettlementController::class, 'rollbackChallanAdjustment'])->name('rollback.challan.adjustment');
    Route::post('rollback-payment-voucher', [EmployeeSettlementController::class, 'rollbackPaymentVoucher'])->name('rollback.payment.voucher');
    Route::get('emp-final-settlement-create/{id}', [EmployeeSettlementController::class, 'create'])->name('EmployeeSettlement.create');
    Route::resource('emp-final-settlement', EmployeeSettlementController::class);


    Route::get('allowances/create/{eid}', [AllowanceController::class, 'allowanceCreate'])->name('allowances.create')->middleware(['auth', 'XSS']);
    Route::get('commissions/create/{eid}', [CommissionController::class, 'commissionCreate'])->name('commissions.create')->middleware(['auth', 'XSS']);
    Route::get('loans/create/{eid}', [LoanController::class, 'loanCreate'])->name('loans.create')->middleware(['auth', 'XSS']);
    Route::get('printloan/{id}', [LoanController::class, 'printloan'])->name('printloan')->middleware(['auth', 'XSS']);
    Route::get('loanstatus/{id}', [LoanController::class, 'loanstatus'])->name('loan.status')->middleware(['auth', 'XSS']);
    Route::put('loanstatuschange/{id}', [LoanController::class, 'loanstatuschange'])->name('loan.loanstatuschange')->middleware(['auth', 'XSS']);
    Route::get('get-employee-serv-sec/{id}', [LoanController::class, 'emp_sec_tenure'])->name('emp_sec_tenure')->middleware(['auth', 'XSS']);
    Route::get('saturationdeductions/create/{eid}', [SaturationDeductionController::class, 'saturationdeductionCreate'])->name('saturationdeductions.create')->middleware(['auth', 'XSS']);
    Route::get('otherpayments/create/{eid}', [OtherPaymentController::class, 'otherpaymentCreate'])->name('otherpayments.create')->middleware(['auth', 'XSS']);
    Route::get('overtimes/create/{eid}', [OvertimeController::class, 'overtimeCreate'])->name('overtimes.create')->middleware(['auth', 'XSS']);
    Route::get('payslip/paysalary/{id}/{date}', [PaySlipController::class, 'paysalary'])->name('payslip.paysalary')->middleware(['auth', 'XSS']);
    Route::get('payslip/bulk_pay_create/{date}', [PaySlipController::class, 'bulk_pay_create'])->name('payslip.bulk_pay_create')->middleware(['auth', 'XSS']);
    Route::post('payslip/bulkpayment/{date}', [PaySlipController::class, 'bulkpayment'])->name('payslip.bulkpayment')->middleware(['auth', 'XSS']);
    Route::post('payslip/search_json', [PaySlipController::class, 'search_json'])->name('payslip.search_json')->middleware(['auth', 'XSS']);
    Route::get('payslip/employeepayslip', [PaySlipController::class, 'employeepayslip'])->name('payslip.employeepayslip')->middleware(['auth', 'XSS']);
    Route::get('payslip/showemployee/{id}', [PaySlipController::class, 'showemployee'])->name('payslip.showemployee')->middleware(['auth', 'XSS']);
    Route::get('payslip/editemployee/{id}', [PaySlipController::class, 'editemployee'])->name('payslip.editemployee')->middleware(['auth', 'XSS']);
    Route::post('payslip/editemployee/{id}', [PaySlipController::class, 'updateEmployee'])->name('payslip.updateemployee')->middleware(['auth', 'XSS']);
    Route::get('payslip/pdf/{id}/{m}', [PaySlipController::class, 'pdf'])->name('payslip.pdf')->middleware(['auth', 'XSS']);
    Route::get('payslip/payslipPdf/{id}', [PaySlipController::class, 'payslipPdf'])->name('payslip.payslipPdf')->middleware(['auth', 'XSS']);
    Route::get('payslip/send/{id}/{m}', [PaySlipController::class, 'send'])->name('payslip.send')->middleware(['auth', 'XSS']);
    Route::get('payslip/delete/{id}', [PaySlipController::class, 'destroy'])->name('payslip.delete')->middleware(['auth', 'XSS']);
    Route::resource('payslip', PaySlipController::class)->middleware(['auth', 'XSS']);


    Route::resource('company-policy', CompanyPolicyController::class)->middleware(['auth', 'XSS']);
    Route::resource('indicator', IndicatorController::class)->middleware(['auth', 'XSS']);
    Route::resource('appraisal', AppraisalController::class)->middleware(['auth', 'XSS']);

    Route::post('branch/employee/json', [EmployeeController::class, 'employeeJson'])->name('branch.employee.json')->middleware(['auth', 'XSS']);

    Route::resource('goaltype', GoalTypeController::class)->middleware(['auth', 'XSS']);
    Route::resource('goaltracking', GoalTrackingController::class)->middleware(['auth', 'XSS']);
    Route::resource('account-assets', AssetController::class)->middleware(['auth', 'XSS']);


    Route::post('event/getdepartment', [EventController::class, 'getdepartment'])->name('event.getdepartment')->middleware(['auth', 'XSS']);
    Route::post('event/getemployee', [EventController::class, 'getemployee'])->name('event.getemployee')->middleware(['auth', 'XSS']);



    Route::resource('event', EventController::class)->middleware(['auth', 'XSS']);

    Route::post('meeting/getdepartment', [MeetingController::class, 'getdepartment'])->name('meeting.getdepartment')->middleware(['auth', 'XSS']);
    Route::post('meeting/getemployee', [MeetingController::class, 'getemployee'])->name('meeting.getemployee')->middleware(['auth', 'XSS']);



    Route::resource('meeting', MeetingController::class)->middleware(['auth', 'XSS']);
    Route::resource('trainingtype', TrainingTypeController::class)->middleware(['auth', 'XSS']);
    Route::resource('trainer', TrainerController::class)->middleware(['auth', 'XSS']);

    Route::post('training/status', [TrainingController::class, 'updateStatus'])->name('training.status')->middleware(['auth', 'XSS']);

    Route::resource('training', TrainingController::class)->middleware(['auth', 'XSS']);


    // HRM - HR Module

    Route::resource('awardtype', AwardTypeController::class)->middleware(['auth', 'XSS']);
    Route::resource('award', AwardController::class)->middleware(['auth', 'XSS']);
    // resignation.approval
    Route::put('resignation/approval/{id}', [ResignationController::class, 'approval'])->name('resignation.approval')->middleware(['auth', 'XSS']);
    Route::resource('resignation', ResignationController::class)->middleware(['auth', 'XSS']);
    Route::resource('travel', TravelController::class)->middleware(['auth', 'XSS']);
    Route::resource('promotion', PromotionController::class)->middleware(['auth', 'XSS']);
    Route::resource('complaint', ComplaintController::class)->middleware(['auth', 'XSS']);
    Route::resource('warning', WarningController::class)->middleware(['auth', 'XSS']);

    Route::resource('termination', TerminationController::class)->middleware(['auth', 'XSS']);
    Route::get('termination/{id}/description', [TerminationController::class, 'description'])->name('termination.description');
    Route::resource('terminationtype', TerminationTypeController::class)->middleware(['auth', 'XSS']);

    Route::post('announcement/getdepartment', [AnnouncementController::class, 'getdepartment'])->name('announcement.getdepartment');
    Route::post('announcement/getemployee', [AnnouncementController::class, 'getemployee'])->name('announcement.getemployee');
    Route::resource('announcement', AnnouncementController::class)->middleware(['auth', 'XSS']);

    Route::resource('holiday', HolidayController::class)->middleware(['auth', 'XSS']);
    Route::get('holiday-calender', [HolidayController::class, 'calender'])->name('holiday.calender');


    // Recruitement

    Route::resource('job-category', JobCategoryController::class)->middleware(['auth', 'XSS']);

    Route::resource('job-stage', JobStageController::class)->middleware(['auth', 'XSS']);
    Route::post('job-stage/order', [JobStageController::class, 'order'])->name('job.stage.order');

    Route::resource('job', JobController::class)->middleware(['auth', 'XSS']);


    Route::get('candidates-job-applications', [JobApplicationController::class, 'candidate'])->name('job.application.candidate')->middleware(['auth', 'XSS']);

    Route::resource('job-application', JobApplicationController::class)->middleware(['auth', 'XSS']);
    Route::post('job-application/order', [JobApplicationController::class, 'order'])->name('job.application.order')->middleware(['XSS']);
    Route::post('job-application/{id}/rating', [JobApplicationController::class, 'rating'])->name('job.application.rating')->middleware(['XSS']);
    Route::delete('job-application/{id}/archive', [JobApplicationController::class, 'archive'])->name('job.application.archive')->middleware(['auth', 'XSS']);
    Route::post('job-application/{id}/skill/store', [JobApplicationController::class, 'addSkill'])->name('job.application.skill.store')->middleware(['auth', 'XSS']);
    Route::post('job-application/{id}/note/store', [JobApplicationController::class, 'addNote'])->name('job.application.note.store')->middleware(['auth', 'XSS']);
    Route::delete('job-application/{id}/note/destroy', [JobApplicationController::class, 'destroyNote'])->name('job.application.note.destroy')->middleware(['auth', 'XSS']);
    Route::post('job-application/getByJob', [JobApplicationController::class, 'getByJob'])->name('get.job.application')->middleware(['auth', 'XSS']);
    Route::get('job-onboard', [JobApplicationController::class, 'jobOnBoard'])->name('job.on.board')->middleware(['auth', 'XSS']);
    Route::get('job-onboard/create/{id}', [JobApplicationController::class, 'jobBoardCreate'])->name('job.on.board.create')->middleware(['auth', 'XSS']);
    Route::post('job-onboard/store/{id}', [JobApplicationController::class, 'jobBoardStore'])->name('job.on.board.store')->middleware(['auth', 'XSS']);
    Route::get('job-onboard/edit/{id}', [JobApplicationController::class, 'jobBoardEdit'])->name('job.on.board.edit')->middleware(['auth', 'XSS']);
    Route::post('job-onboard/update/{id}', [JobApplicationController::class, 'jobBoardUpdate'])->name('job.on.board.update')->middleware(['auth', 'XSS']);
    Route::delete('job-onboard/delete/{id}', [JobApplicationController::class, 'jobBoardDelete'])->name('job.on.board.delete')->middleware(['auth', 'XSS']);
    Route::get('job-onboard/convert/{id}', [JobApplicationController::class, 'jobBoardConvert'])->name('job.on.board.convert')->middleware(['auth', 'XSS']);
    Route::post('job-onboard/convert/{id}', [JobApplicationController::class, 'jobBoardConvertData'])->name('job.on.board.convert')->middleware(['auth', 'XSS']);
    Route::post('job-application/stage/change', [JobApplicationController::class, 'stageChange'])->name('job.application.stage.change')->middleware(['auth', 'XSS']);

    Route::resource('custom-question', CustomQuestionController::class)->middleware(['auth', 'XSS']);
    Route::resource('interview-schedule', InterviewScheduleController::class)->middleware(['auth', 'XSS']);
    Route::get('interview-schedule/create/{id?}', [InterviewScheduleController::class, 'create'])->name('interview-schedule.create')->middleware(['auth', 'XSS']);
    Route::get('taskboard/{view?}', [ProjectTaskController::class, 'taskBoard'])->name('taskBoard.view')->middleware(['auth', 'XSS']);
    Route::get('taskboard-view', [ProjectTaskController::class, 'taskboardView'])->name('project.taskboard.view')->middleware(['auth', 'XSS']);


    Route::resource('document-upload', DucumentUploadController::class)->middleware(['auth', 'XSS']);
    Route::resource('transfer', TransferController::class)->middleware(['auth', 'XSS']);
    Route::get('attendanceemployee/bulkattendance', [AttendanceEmployeeController::class, 'bulkAttendance'])->name('attendanceemployee.bulkattendance')->middleware(['auth', 'XSS']);
    Route::post('attendanceemployee/bulkattendance', [AttendanceEmployeeController::class, 'bulkAttendanceData'])->name('attendanceemployee.bulkattendance')->middleware(['auth', 'XSS']);
    Route::post('attendanceemployee/attendance', [AttendanceEmployeeController::class, 'attendance'])->name('attendanceemployee.attendance')->middleware(['auth', 'XSS']);

    Route::resource('attendanceemployee', AttendanceEmployeeController::class)->middleware(['auth', 'XSS']);
    Route::resource('leavetype', LeaveTypeController::class)->middleware(['auth', 'XSS']);
    Route::resource('sops', SopController::class)->middleware(['auth', 'XSS']);
    Route::resource('emp-leaves', LeaveAllocation::class)->middleware(['auth', 'XSS']);
    Route::resource('emp-eobi-allocation', EobiAllocation::class)->middleware(['auth', 'XSS']);
    Route::resource('health-insurance-plan', HealthInsuracnePlanSetup::class)->middleware(['auth', 'XSS']);
    Route::get('report/leave', [ReportController::class, 'leave'])->name('report.leave')->middleware(['auth', 'XSS']);
    Route::get('employee/{id}/leave/{status}/{type}/{month}/{year}', [ReportController::class, 'employeeLeave'])->name('report.employee.leave')->middleware(['auth', 'XSS']);
    Route::get('leave/{id}/action', [LeaveController::class, 'action'])->name('leave.action')->middleware(['auth', 'XSS']);
    Route::post('leave/changeaction', [LeaveController::class, 'changeaction'])->name('leave.changeaction')->middleware(['auth', 'XSS']);
    Route::post('leave/jsoncount', [LeaveController::class, 'jsoncount'])->name('leave.jsoncount')->middleware(['auth', 'XSS']);

    Route::resource('leave', LeaveController::class)->middleware(['auth', 'XSS']);


    Route::get('reports-leave', [ReportController::class, 'leave'])->name('report.leave')->middleware(['auth', 'XSS']);
    Route::get('employee/{id}/leave/{status}/{type}/{month}/{year}', [ReportController::class, 'employeeLeave'])->name('report.employee.leave')->middleware(['auth', 'XSS']);

    Route::get('reports-payroll', [ReportController::class, 'payroll'])->name('report.payroll')->middleware(['auth', 'XSS']);
    Route::post('reports-payroll/getdepartment', [ReportController::class, 'getPayrollDepartment'])->name('report.payroll.getdepartment')->middleware(['auth', 'XSS']);
    Route::post('reports-payroll/getemployee', [ReportController::class, 'getPayrollEmployee'])->name('report.payroll.getemployee')->middleware(['auth', 'XSS']);



    Route::get('reports-monthly-attendance', [ReportController::class, 'monthlyAttendance'])->name('report.monthly.attendance')->middleware(['auth', 'XSS']);
    Route::get('report/attendance/{month}/{branch}/{department}', [ReportController::class, 'exportCsv'])->name('report.attendance')->middleware(['auth', 'XSS']);

    //crm report
    Route::get('reports-lead', [ReportController::class, 'leadReport'])->name('report.lead')->middleware(['auth', 'XSS']);
    Route::get('reports-deal', [ReportController::class, 'dealReport'])->name('report.deal')->middleware(['auth', 'XSS']);

    //pos report
    Route::get('reports-warehouse', [ReportController::class, 'warehouseReport'])->name('report.warehouse')->middleware(['auth', 'XSS']);

    Route::get('reports-daily-purchase', [ReportController::class, 'purchaseDailyReport'])->name('report.daily.purchase')->middleware(['auth', 'XSS']);
    Route::get('reports-monthly-purchase', [ReportController::class, 'purchaseMonthlyReport'])->name('report.monthly.purchase')->middleware(['auth', 'XSS']);

    Route::get('reports-daily-pos', [ReportController::class, 'posDailyReport'])->name('report.daily.pos')->middleware(['auth', 'XSS']);
    Route::get('reports-monthly-pos', [ReportController::class, 'posMonthlyReport'])->name('report.monthly.pos')->middleware(['auth', 'XSS']);

    Route::get('reports-pos-vs-purchase', [ReportController::class, 'posVsPurchaseReport'])->name('report.pos.vs.purchase')->middleware(['auth', 'XSS']);


    // User Module

    Route::get('users/{view?}', [UserController::class, 'index'])->name('users')->middleware(['auth', 'XSS']);
    Route::get('users-view', [UserController::class, 'filterUserView'])->name('filter.user.view')->middleware(['auth', 'XSS']);
    Route::get('checkuserexists', [UserController::class, 'checkUserExists'])->name('user.exists')->middleware(['auth', 'XSS']);
    Route::get('profile', [UserController::class, 'profile'])->name('profile')->middleware(['auth', 'XSS']);
    Route::post('/profile', [UserController::class, 'updateProfile'])->name('update.profile')->middleware(['auth', 'XSS']);
    Route::get('user/info/{id}', [UserController::class, 'userInfo'])->name('users.info')->middleware(['auth', 'XSS']);
    Route::get('user/{id}/info/{type}', [UserController::class, 'getProjectTask'])->name('user.info.popup')->middleware(['auth', 'XSS']);
    Route::delete('users/{id}', [UserController::class, 'destroy'])->name('user.destroy')->middleware(['auth', 'XSS']);
    // End User Module


    // Search
    Route::get('/search', [UserController::class, 'search'])->name('search.json');
    // end


    // Milestone Module

    Route::get('projects/{id}/milestone', [ProjectController::class, 'milestone'])->name('project.milestone')->middleware(['auth', 'XSS']);


    //Route::delete(
    //    '/projects/{id}/users/{uid}', [
    //                                    'as' => 'projects.users.destroy',
    //                                    'uses' => 'ProjectController@userDestroy',
    //                                ]
    //)->middleware(
    //    [
    //        'auth',
    //        'XSS',
    //    ]
    //);
    Route::post('projects/{id}/milestone', [ProjectController::class, 'milestoneStore'])->name('project.milestone.store')->middleware(['auth', 'XSS']);
    Route::get('projects/milestone/{id}/edit', [ProjectController::class, 'milestoneEdit'])->name('project.milestone.edit')->middleware(['auth', 'XSS']);
    Route::post('projects/milestone/{id}', [ProjectController::class, 'milestoneUpdate'])->name('project.milestone.update')->middleware(['auth', 'XSS']);
    Route::delete('projects/milestone/{id}', [ProjectController::class, 'milestoneDestroy'])->name('project.milestone.destroy')->middleware(['auth', 'XSS']);
    Route::get('projects/milestone/{id}/show', [ProjectController::class, 'milestoneShow'])->name('project.milestone.show')->middleware(['auth', 'XSS']);

    // End Milestone

    // Project Module

    Route::get('invite-project-member/{id}', [ProjectController::class, 'inviteMemberView'])->name('invite.project.member.view')->middleware(['auth', 'XSS']);
    Route::post('invite-project-user-member', [ProjectController::class, 'inviteProjectUserMember'])->name('invite.project.user.member')->middleware(['auth', 'XSS']);

    Route::delete('projects/{id}/users/{uid}', [ProjectController::class, 'destroyProjectUser'])->name('projects.user.destroy')->middleware(['auth', 'XSS']);
    Route::get('project/{view?}', [ProjectController::class, 'index'])->name('projects.list')->middleware(['auth', 'XSS']);
    Route::get('projects-view', [ProjectController::class, 'filterProjectView'])->name('filter.project.view')->middleware(['auth', 'XSS']);
    Route::post('projects/{id}/store-stages/{slug}', [ProjectController::class, 'storeProjectTaskStages'])->name('project.stages.store')->middleware(['auth', 'XSS']);


    Route::patch('remove-user-from-project/{project_id}/{user_id}', [ProjectController::class, 'removeUserFromProject'])->name('remove.user.from.project')->middleware(['auth', 'XSS']);
    Route::get('projects-users', [ProjectController::class, 'loadUser'])->name('project.user')->middleware(['auth', 'XSS']);
    Route::get('projects/{id}/gantt/{duration?}', [ProjectController::class, 'gantt'])->name('projects.gantt')->middleware(['auth', 'XSS']);
    Route::post('projects/{id}/gantt', [ProjectController::class, 'ganttPost'])->name('projects.gantt.post')->middleware(['auth', 'XSS']);


    Route::resource('projects', ProjectController::class)->middleware(['auth', 'XSS']);

    // User Permission
    Route::get('projects/{id}/user/{uid}/permission', [ProjectController::class, 'userPermission'])->name('projects.user.permission')->middleware(['auth', 'XSS']);
    Route::post('projects/{id}/user/{uid}/permission', [ProjectController::class, 'userPermissionStore'])->name('projects.user.permission.store')->middleware(['auth', 'XSS']);

    // End Project Module

    // booking module
    Route::get('/bookingcalendar/{id}/show', [BookingController::class, 'calendarShow'])->name('booking.calendar.show')->middleware(['auth', 'XSS']);
    Route::post('/bookingcalendar/{id}/drag', [BookingController::class, 'calendarDrag'])->name('booking.calendar.drag');
    Route::get('bookingcalendar/{task}/{pid?}', [BookingController::class, 'calendarView'])->name('booking.calendar')->middleware(['auth', 'XSS']);
    Route::post('bookingcalendar/get_task_data', [BookingController::class, 'get_task_data'])->name('booking.calendar.get_task_data')->middleware(['auth', 'XSS']);
    Route::post('booking', [BookingController::class, 'get_task_data'])->name('booking.calendar.get_task_data')->middleware(['auth', 'XSS']);
    Route::resource('booking', BookingController::class)->middleware(['auth', 'XSS']);
    Route::get('/bookings/create/{id?}', [BookingController::class, 'bookingcreate'])->name('bookings.create')->middleware(['auth', 'XSS']);


    // Task Module

    Route::get('stage/{id}/tasks', [ProjectTaskController::class, 'getStageTasks'])->name('stage.tasks')->middleware(['auth', 'XSS']);

    // Project Task Module

    Route::get('/projects/{id}/task', [ProjectTaskController::class, 'index'])->name('projects.tasks.index')->middleware(['auth', 'XSS']);
    Route::get('/projects/{pid}/task/{sid}', [ProjectTaskController::class, 'create'])->name('projects.tasks.create')->middleware(['auth', 'XSS']);
    Route::post('/projects/{pid}/task/{sid}', [ProjectTaskController::class, 'store'])->name('projects.tasks.store')->middleware(['auth', 'XSS']);
    Route::get('/projects/{id}/task/{tid}/show', [ProjectTaskController::class, 'show'])->name('projects.tasks.show')->middleware(['auth', 'XSS']);
    Route::get('/projects/{id}/task/{tid}/edit', [ProjectTaskController::class, 'edit'])->name('projects.tasks.edit')->middleware(['auth', 'XSS']);
    Route::post('/projects/{id}/task/update/{tid}', [ProjectTaskController::class, 'update'])->name('projects.tasks.update')->middleware(['auth', 'XSS']);
    Route::delete('/projects/{id}/task/{tid}', [ProjectTaskController::class, 'destroy'])->name('projects.tasks.destroy')->middleware(['auth', 'XSS']);
    Route::patch('/projects/{id}/task/order', [ProjectTaskController::class, 'taskOrderUpdate'])->name('tasks.update.order')->middleware(['auth', 'XSS']);
    Route::patch('update-task-priority-color', [ProjectTaskController::class, 'updateTaskPriorityColor'])->name('update.task.priority.color')->middleware(['auth', 'XSS']);


    Route::post('/projects/{id}/comment/{tid}/file', [ProjectTaskController::class, 'commentStoreFile'])->name('comment.store.file')->middleware(['auth', 'XSS']);
    Route::delete('/projects/{id}/comment/{tid}/file/{fid}', [ProjectTaskController::class, 'commentDestroyFile'])->name('comment.destroy.file');
    Route::post('/projects/{id}/comment/{tid}', [ProjectTaskController::class, 'commentStore'])->name('task.comment.store');
    Route::delete('/projects/{id}/comment/{tid}/{cid}', [ProjectTaskController::class, 'commentDestroy'])->name('comment.destroy');
    Route::post('/projects/{id}/checklist/{tid}', [ProjectTaskController::class, 'checklistStore'])->name('checklist.store');
    Route::post('/projects/{id}/checklist/update/{cid}', [ProjectTaskController::class, 'checklistUpdate'])->name('checklist.update');
    Route::delete('/projects/{id}/checklist/{cid}', [ProjectTaskController::class, 'checklistDestroy'])->name('checklist.destroy');
    Route::post('/projects/{id}/change/{tid}/fav', [ProjectTaskController::class, 'changeFav'])->name('change.fav');
    Route::post('/projects/{id}/change/{tid}/complete', [ProjectTaskController::class, 'changeCom'])->name('change.complete');
    Route::post('/projects/{id}/change/{tid}/progress', [ProjectTaskController::class, 'changeProg'])->name('change.progress');
    Route::get('/projects/task/{id}/get', [ProjectTaskController::class, 'taskGet'])->name('projects.tasks.get')->middleware(['auth', 'XSS']);
    Route::get('/calendar/{id}/show', [ProjectTaskController::class, 'calendarShow'])->name('task.calendar.show')->middleware(['auth', 'XSS']);
    Route::post('/calendar/{id}/drag', [ProjectTaskController::class, 'calendarDrag'])->name('task.calendar.drag');
    Route::get('calendar/{task}/{pid?}', [ProjectTaskController::class, 'calendarView'])->name('task.calendar')->middleware(['auth', 'XSS']);

    Route::resource('project-task-stages', TaskStageController::class)->middleware(['auth', 'XSS']);
    Route::post('/project-task-stages/order', [TaskStageController::class, 'order'])->name('project-task-stages.order');

    Route::post('project-task-new-stage', [TaskStageController::class, 'storingValue'])->name('new-task-stage')->middleware(['auth', 'XSS']);
    // End Task Module

    // Project Expense Module
    Route::get('/projects/{id}/expense', [ExpenseController::class, 'index'])->name('projects.expenses.index')->middleware(['auth', 'XSS']);
    Route::get('/projects/{pid}/expense/create', [ExpenseController::class, 'create'])->name('projects.expenses.create')->middleware(['auth', 'XSS']);
    Route::post('/projects/{pid}/expense/store', [ExpenseController::class, 'store'])->name('projects.expenses.store')->middleware(['auth', 'XSS']);
    Route::get('/projects/{id}/expense/{eid}/edit', [ExpenseController::class, 'edit'])->name('projects.expenses.edit')->middleware(['auth', 'XSS']);
    Route::post('/projects/{id}/expense/{eid}', [ExpenseController::class, 'update'])->name('projects.expenses.update')->middleware(['auth', 'XSS']);
    Route::delete('/projects/{eid}/expense/', [ExpenseController::class, 'destroy'])->name('projects.expenses.destroy')->middleware(['auth', 'XSS']);
    Route::get('/expense-list', [ExpenseController::class, 'expenseList'])->name('expense.list')->middleware(['auth', 'XSS']);


    // contract type
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('contractType', ContractTypeController::class);
        }
    );


    // Project Timesheet
    Route::get('append-timesheet-task-html', [TimesheetController::class, 'appendTimesheetTaskHTML'])->name('append.timesheet.task.html')->middleware(['auth', 'XSS']);
    //    Route::get('timesheet-table-view', [TimesheetController::class, 'filterTimesheetTableView'])->name('filter.timesheet.table.view')->middleware(['auth', 'XSS']);
    Route::get('timesheet-view', [TimesheetController::class, 'filterTimesheetView'])->name('filter.timesheet.view')->middleware(['auth', 'XSS']);
    Route::get('timesheet-list', [TimesheetController::class, 'timesheetList'])->name('timesheet.list')->middleware(['auth', 'XSS']);
    Route::get('timesheet-list-get', [TimesheetController::class, 'timesheetListGet'])->name('timesheet.list.get')->middleware(['auth', 'XSS']);
    Route::get('/project/{id}/timesheet', [TimesheetController::class, 'timesheetView'])->name('timesheet.index')->middleware(['auth', 'XSS']);
    Route::get('/project/{id}/timesheet/create', [TimesheetController::class, 'timesheetCreate'])->name('timesheet.create')->middleware(['auth', 'XSS']);
    Route::post('/project/timesheet', [TimesheetController::class, 'timesheetStore'])->name('timesheet.store')->middleware(['auth', 'XSS']);
    Route::get('/project/timesheet/{project_id}/edit/{timesheet_id}', [TimesheetController::class, 'timesheetEdit'])->name('timesheet.edit')->middleware(['auth', 'XSS']);
    Route::any('/project/timesheet/update/{timesheet_id}', [TimesheetController::class, 'timesheetUpdate'])->name('timesheet.update')->middleware(['auth', 'XSS']);

    Route::delete('/project/timesheet/{timesheet_id}', [TimesheetController::class, 'timesheetDestroy'])->name('timesheet.destroy')->middleware(['auth', 'XSS']);

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
            ],
        ],
        function () {
            Route::resource('projectstages', ProjectstagesController::class);
            Route::post('/projectstages/order', [ProjectstagesController::class, 'order'])->name('projectstages.order')->middleware(['auth', 'XSS']);
            Route::post('projects/bug/kanban/order', [ProjectController::class, 'bugKanbanOrder'])->name('bug.kanban.order');
            Route::get('projects/{id}/bug/kanban', [ProjectController::class, 'bugKanban'])->name('task.bug.kanban');
            Route::get('projects/{id}/bug', [ProjectController::class, 'bug'])->name('task.bug');
            Route::get('projects/{id}/bug/create', [ProjectController::class, 'bugCreate'])->name('task.bug.create');
            Route::post('projects/{id}/bug/store', [ProjectController::class, 'bugStore'])->name('task.bug.store');
            Route::get('projects/{id}/bug/{bid}/edit', [ProjectController::class, 'bugEdit'])->name('task.bug.edit');
            Route::post('projects/{id}/bug/{bid}/update', [ProjectController::class, 'bugUpdate'])->name('task.bug.update');
            Route::delete('projects/{id}/bug/{bid}/destroy', [ProjectController::class, 'bugDestroy'])->name('task.bug.destroy');
            Route::get('projects/{id}/bug/{bid}/show', [ProjectController::class, 'bugShow'])->name('task.bug.show');
            Route::post('projects/{id}/bug/{bid}/comment', [ProjectController::class, 'bugCommentStore'])->name('bug.comment.store');
            Route::post('projects/bug/{bid}/file', [ProjectController::class, 'bugCommentStoreFile'])->name('bug.comment.file.store');
            Route::delete('projects/bug/comment/{id}', [ProjectController::class, 'bugCommentDestroy'])->name('bug.comment.destroy');
            Route::delete('projects/bug/file/{id}', [ProjectController::class, 'bugCommentDestroyFile'])->name('bug.comment.file.destroy');

            Route::resource('bugstatus', BugStatusController::class);
            Route::post('/bugstatus/order', [BugStatusController::class, 'order'])->name('bugstatus.order');
            Route::get('bugs-report/{view?}', [ProjectTaskController::class, 'allBugList'])->name('bugs.view')->middleware(['auth', 'XSS']);
        }
    );

    // User_Todo Module
    Route::post('/todo/create', [UserController::class, 'todo_store'])->name('todo.store')->middleware(['auth', 'XSS']);
    Route::post('/todo/{id}/update', [UserController::class, 'todo_update'])->name('todo.update')->middleware(['auth', 'XSS']);
    Route::delete('/todo/{id}', [UserController::class, 'todo_destroy'])->name('todo.destroy')->middleware(['auth', 'XSS']);
    Route::get('/change/mode', [UserController::class, 'changeMode'])->name('change.mode')->middleware(['auth', 'XSS']);
    Route::get('dashboard-view', [DashboardController::class, 'filterView'])->name('dashboard.view')->middleware(['auth', 'XSS']);
    Route::get('dashboard', [DashboardController::class, 'clientView'])->name('client.dashboard.view')->middleware(['auth', 'XSS']);


    // saas
    Route::resource('users', UserController::class)->middleware(['auth', 'XSS', 'revalidate']);
    Route::resource('plans', PlanController::class)->middleware(['auth', 'XSS', 'revalidate']);
    Route::resource('coupons', CouponController::class)->middleware(['auth', 'XSS', 'revalidate']);

    // Orders

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('/orders', [StripePaymentController::class, 'index'])->name('order.index');
            Route::get('/stripe/{code}', [StripePaymentController::class, 'stripe'])->name('stripe');
            Route::post('/stripe', [StripePaymentController::class, 'stripePost'])->name('stripe.post');
        }
    );

    Route::get('/apply-coupon', [CouponController::class, 'applyCoupon'])->name('apply.coupon')->middleware(['auth', 'XSS', 'revalidate']);


    //================================= Form Builder ====================================//


    // Form Builder
    Route::resource('form_builder', FormBuilderController::class)->middleware(['auth', 'XSS']);


    // Form link base view
    Route::get('/form/{code}', [FormBuilderController::class, 'formView'])->name('form.view')->middleware(['XSS']);
    Route::post('/form_view_store', [FormBuilderController::class, 'formViewStore'])->name('form.view.store')->middleware(['XSS']);

    // Form Field
    Route::get('/form_builder/{id}/field', [FormBuilderController::class, 'fieldCreate'])->name('form.field.create')->middleware(['auth', 'XSS']);
    Route::post('/form_builder/{id}/field', [FormBuilderController::class, 'fieldStore'])->name('form.field.store')->middleware(['auth', 'XSS']);
    Route::get('/form_builder/{id}/field/{fid}/show', [FormBuilderController::class, 'fieldShow'])->name('form.field.show')->middleware(['auth', 'XSS']);
    Route::get('/form_builder/{id}/field/{fid}/edit', [FormBuilderController::class, 'fieldEdit'])->name('form.field.edit')->middleware(['auth', 'XSS']);
    Route::post('/form_builder/{id}/field/{fid}', [FormBuilderController::class, 'fieldUpdate'])->name('form.field.update')->middleware(['auth', 'XSS']);
    Route::delete('/form_builder/{id}/field/{fid}', [FormBuilderController::class, 'fieldDestroy'])->name('form.field.destroy')->middleware(['auth', 'XSS']);


    // Form Response
    Route::get('/form_response/{id}', [FormBuilderController::class, 'viewResponse'])->name('form.response')->middleware(['auth', 'XSS']);
    Route::get('/response/{id}', [FormBuilderController::class, 'responseDetail'])->name('response.detail')->middleware(['auth', 'XSS']);


    // Form Field Bind
    Route::get('/form_field/{id}', [FormBuilderController::class, 'formFieldBind'])->name('form.field.bind')->middleware(['auth', 'XSS']);
    Route::post('/form_field_store/{id}}', [FormBuilderController::class, 'bindStore'])->name('form.bind.store')->middleware(['auth', 'XSS']);


    // contract

    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('contract/{id}/description', [ContractController::class, 'description'])->name('contract.description');
            Route::get('contract/grid', [ContractController::class, 'grid'])->name('contract.grid');
            Route::resource('contract', ContractController::class);
            Route::post('branch_company', [ContractController::class, 'branchCompany'])->name('branch.company');

        }
    );
    // for virtule office contract
    Route::get('/create_virtual_office', [ContractController::class, 'createvirtualoffice'])->name('createvirtualoffice')->middleware(['auth', 'XSS']);

    Route::post('/contract/{id}/file', [ContractController::class, 'fileUpload'])->name('contract.file.upload')->middleware(['auth', 'XSS']);
    Route::get('contract/pdf/{id}', [ContractController::class, 'pdffromcontract'])->name('contract.download.pdf')->middleware(['auth']);
    Route::get('contract/{id}/get_contract', [ContractController::class, 'printContract'])->name('get.contract')->middleware(['auth']);
    Route::post('/contract_status_edit/{id}', [ContractController::class, 'contract_status_edit'])->name('contract.status')->middleware(['auth', 'XSS']);
    Route::post('contract/{id}/contract_description', [ContractController::class, 'contract_descriptionStore'])->name('contract.contract_description.store')->middleware(['auth']);
    Route::get('/contract/{id}/file/{fid}', [ContractController::class, 'fileDownload'])->name('contracts.file.download')->middleware(['auth', 'XSS']);
    Route::delete('/contract/{id}/file/delete/{fid}', [ContractController::class, 'fileDelete'])->name('contracts.file.delete')->middleware(['auth', 'XSS']);
    Route::get('/contract/copy/{id}', [ContractController::class, 'copycontract'])->name('contract.copy')->middleware(['auth', 'XSS']);
    Route::post('/contract/copy/store', [ContractController::class, 'copycontractstore'])->name('contract.copy.store')->middleware(['auth', 'XSS']);
    Route::get('/contract/{id}/mail', [ContractController::class, 'sendmailContract'])->name('send.mail.contract');
    Route::get('/signature/{id}', [ContractController::class, 'signature'])->name('signature')->middleware(['auth']);
    Route::post('/signaturestore', [ContractController::class, 'signatureStore'])->name('signaturestore')->middleware(['auth', 'XSS']);
    Route::post('/contract/{id}/comment', [ContractController::class, 'commentStore'])->name('comment.store');
    Route::post('/contract/{id}/notes', [ContractController::class, 'noteStore'])->name('note_store.store')->middleware(['auth']);
    Route::delete('/contract/{id}/notes', [ContractController::class, 'noteDestroy'])->name('note_store.destroy')->middleware(['auth']);
    Route::delete('/contract/{id}/comment', [ContractController::class, 'commentDestroy'])->name('comment_store.destroy');
    Route::get('get-projects/{client_id}', [ContractController::class, 'clientByProject'])->name('project.by.user.id')->middleware(['auth', 'XSS']);


    // client wise project show in modal

    Route::any('/contract/clients/select/{bid}', [ContractController::class, 'clientwiseproject'])->name('contract.clients.select');

    // copy contract

    Route::get('/contract/copy/{id}', [ContractController::class, 'copycontract'])->name('contract.copy')->middleware(['auth', 'XSS']);
    Route::post('contract/copy/store', [ContractController::class, 'copycontractstore'])->name('contract.copy.store')->middleware(['auth', 'XSS']);

    Route::get('contract_status/{id}', [ContractController::class, 'contract_status'])->name('contract_status')->middleware(['auth', 'XSS']);
    Route::get('contract_clear/{id}', [ContractController::class, 'contract_clear'])->name('contract_clear')->middleware(['auth', 'XSS']);


    // Custom Landing Page

    //    Route::get('/landingpage', [LandingPageSectionController::class, 'index'])->name('custom_landing_page.index')->middleware(['auth', 'XSS']);
//    Route::get('/LandingPage/show/{id}', [LandingPageSectionController::class, 'show']);
//
//    Route::post('/LandingPage/setConetent', [LandingPageSectionController::class, 'setConetent'])->middleware(['auth', 'XSS']);
//
//
//    Route::get(
//        '/get_landing_page_section/{name}', function ($name) {
//        $plans = \DB::table('plans')->get();
//
//        return view('custom_landing_page.' . $name, compact('plans'));
//    }
//    );
//
//    Route::post('/LandingPage/removeSection/{id}', [LandingPageSectionController::class, 'removeSection'])->middleware(['auth', 'XSS']);
//    Route::post('/LandingPage/setOrder', [LandingPageSectionController::class, 'setOrder'])->middleware(['auth', 'XSS']);
//    Route::post('/LandingPage/copySection', [LandingPageSectionController::class, 'copySection'])->middleware(['auth', 'XSS']);

    // Plan Payment Gateways
    Route::post('plan-pay-with-bank', [BankTransferPaymentController::class, 'planPayWithBank'])->name('plan.pay.with.bank')->middleware(['auth', 'XSS', 'revalidate']);

    Route::post('plan-pay-with-paypal', [PaypalController::class, 'planPayWithPaypal'])->name('plan.pay.with.paypal')->middleware(['auth', 'XSS', 'revalidate']);
    Route::get('{id}/plan-get-payment-status', [PaypalController::class, 'planGetPaymentStatus'])->name('plan.get.payment.status')->middleware(['auth', 'XSS', 'revalidate']);

    Route::post('/plan-pay-with-paystack', [PaystackPaymentController::class, 'planPayWithPaystack'])->name('plan.pay.with.paystack')->middleware(['auth', 'XSS']);
    Route::get('/plan/paystack/{pay_id}/{plan_id}', [PaystackPaymentController::class, 'getPaymentStatus'])->name('plan.paystack');

    Route::post('/plan-pay-with-flaterwave', [FlutterwavePaymentController::class, 'planPayWithFlutterwave'])->name('plan.pay.with.flaterwave')->middleware(['auth', 'XSS']);
    Route::get('/plan/flaterwave/{txref}/{plan_id}', [FlutterwavePaymentController::class, 'getPaymentStatus'])->name('plan.flaterwave');

    Route::post('/plan-pay-with-razorpay', [RazorpayPaymentController::class, 'planPayWithRazorpay'])->name('plan.pay.with.razorpay')->middleware(['auth', 'XSS']);
    Route::get('/plan/razorpay/{txref}/{plan_id}', [RazorpayPaymentController::class, 'getPaymentStatus'])->name('plan.razorpay');

    Route::post('/plan-pay-with-paytm', [PaytmPaymentController::class, 'planPayWithPaytm'])->name('plan.pay.with.paytm')->middleware(['auth', 'XSS']);
    Route::post('/plan/paytm/{plan}', [PaytmPaymentController::class, 'getPaymentStatus'])->name('plan.paytm');

    Route::post('/plan-pay-with-mercado', [MercadoPaymentController::class, 'planPayWithMercado'])->name('plan.pay.with.mercado')->middleware(['auth', 'XSS']);
    Route::get('/plan/mercado/{plan}/{amount}', [MercadoPaymentController::class, 'getPaymentStatus'])->name('plan.mercado');

    Route::post('/plan-pay-with-mollie', [MolliePaymentController::class, 'planPayWithMollie'])->name('plan.pay.with.mollie')->middleware(['auth', 'XSS']);
    Route::get('/plan/mollie/{plan}', [MolliePaymentController::class, 'getPaymentStatus'])->name('plan.mollie');

    Route::post('/plan-pay-with-skrill', [SkrillPaymentController::class, 'planPayWithSkrill'])->name('plan.pay.with.skrill')->middleware(['auth', 'XSS']);
    Route::get('/plan/skrill/{plan}', [SkrillPaymentController::class, 'getPaymentStatus'])->name('plan.skrill');

    Route::post('/plan-pay-with-coingate', [CoingatePaymentController::class, 'planPayWithCoingate'])->name('plan.pay.with.coingate')->middleware(['auth', 'XSS']);
    Route::get('/plan/coingate/{plan}', [CoingatePaymentController::class, 'getPaymentStatus'])->name('plan.coingate');

    Route::post('/toyyibpay', [ToyyibpayController::class, 'planPayWithToyyibpay'])->name('plan.toyyibpaypayment');
    Route::get('/plan-pay-with-toyyibpay/{id}/{status}/{coupon}', [ToyyibpayController::class, 'getPaymentStatus'])->name('plan.status');

    Route::post('payfast-plan', [PayFastController::class, 'planPayWithPayfast'])->name('payfast.payment');
    Route::get('payfast-plan/{success}', [PayFastController::class, 'getPaymentStatus'])->name('payfast.payment.success');

    Route::post('iyzipay/prepare', [IyziPayController::class, 'initiatePayment'])->name('iyzipay.payment.init');
    Route::post('iyzipay/callback/plan/{id}/{amount}/{coupan_code?}', [IyzipayController::class, 'iyzipayCallback'])->name('iyzipay.payment.callback');

    Route::post('/sspay', [SspayController::class, 'SspayPaymentPrepare'])->name('plan.sspaypayment');
    Route::get('sspay-payment-plan/{plan_id}/{amount}/{couponCode}', [SspayController::class, 'SspayPlanGetPayment'])->middleware(['auth'])->name('plan.sspay.callback');

    Route::post('plan-pay-with-paytab', [PaytabController::class, 'planPayWithpaytab'])->middleware(['auth'])->name('plan.pay.with.paytab');
    Route::any('paytab-success/plan', [PaytabController::class, 'PaytabGetPayment'])->middleware(['auth'])->name('plan.paytab.success');

    Route::any('/payment/initiate', [BenefitPaymentController::class, 'initiatePayment'])->name('plan.pay.with.benefit');
    Route::any('call_back', [BenefitPaymentController::class, 'call_back'])->name('benefit.call_back');

    Route::post('cashfree/payments/store', [CashfreeController::class, 'cashfreePaymentStore'])->name('plan.pay.with.cashfree');
    Route::any('cashfree/payments/success', [CashfreeController::class, 'cashfreePaymentSuccess'])->name('cashfreePayment.success');

    Route::post('/aamarpay/payment', [AamarpayController::class, 'pay'])->name('plan.pay.with.aamarpay');
    Route::any('/aamarpay/success/{data}', [AamarpayController::class, 'aamarpaysuccess'])->name('pay.aamarpay.success');

    Route::post('/paytr/payment/{plan_id}', [PaytrController::class, 'PlanpayWithPaytr'])->name('plan.pay.with.paytr');
    Route::get('/paytr/sussess/', [PaytrController::class, 'paytrsuccess'])->name('pay.paytr.success');



    //plan-order
    Route::post('order/{id}/changeaction', [BankTransferPaymentController::class, 'changeStatus'])->name('order.changestatus');
    Route::delete('order/{id}', [BankTransferPaymentController::class, 'orderDestroy'])->name('order.destroy');
    Route::get('order/{id}/action', [BankTransferPaymentController::class, 'action'])->name('order.action');





    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('order', [StripePaymentController::class, 'index'])->name('order.index');
            Route::get('/stripe/{code}', [StripePaymentController::class, 'stripe'])->name('stripe');
            Route::post('/stripe', [StripePaymentController::class, 'stripePost'])->name('stripe.post');

        }
    );
    //    Route::post('plan-pay-with-paypal', [PaypalController::class, 'planPayWithPaypal'])->name('plan.pay.with.paypal')->middleware(['auth', 'XSS', 'revalidate']);
//    Route::get('{id}/plan-get-payment-status', [PaypalController::class, 'planGetPaymentStatus'])->name('plan.get.payment.status')->middleware(['auth', 'XSS', 'revalidate']);







    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('support/{id}/reply', [SupportController::class, 'reply'])->name('support.reply');
            Route::post('support/{id}/reply', [SupportController::class, 'replyAnswer'])->name('support.reply.answer');
            Route::get('support/grid', [SupportController::class, 'grid'])->name('support.grid');
            Route::resource('support', SupportController::class);

        }
    );

    Route::resource('competencies', CompetenciesController::class)->middleware(['auth', 'XSS']);


    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::resource('performanceType', PerformanceTypeController::class);
        }
    );


    // Plan Request Module
    Route::get('plan_request', [PlanRequestController::class, 'index'])->name('plan_request.index')->middleware(['auth', 'XSS']);
    Route::get('request_frequency/{id}', [PlanRequestController::class, 'requestView'])->name('request.view')->middleware(['auth', 'XSS']);
    Route::get('request_send/{id}', [PlanRequestController::class, 'userRequest'])->name('send.request')->middleware(['auth', 'XSS']);
    Route::get('request_response/{id}/{response}', [PlanRequestController::class, 'acceptRequest'])->name('response.request')->middleware(['auth', 'XSS']);
    Route::get('request_cancel/{id}', [PlanRequestController::class, 'cancelRequest'])->name('request.cancel')->middleware(['auth', 'XSS']);
    //QR Code Module


    // Import/Export Data Route

    Route::get('export/productservice', [ProductServiceController::class, 'export'])->name('productservice.export');
    Route::get('import/productservice/file', [ProductServiceController::class, 'importFile'])->name('productservice.file.import');
    Route::post('import/productservice', [ProductServiceController::class, 'import'])->name('productservice.import');
    Route::get('export/customer', [CustomerController::class, 'export'])->name('customer.export');
    Route::get('import/customer/file', [CustomerController::class, 'importFile'])->name('customer.file.import');
    Route::post('import/customer', [CustomerController::class, 'import'])->name('customer.import');
    Route::get('export/vender', [VenderController::class, 'export'])->name('vender.export');
    Route::get('import/vender/file', [VenderController::class, 'importFile'])->name('vender.file.import');
    Route::post('import/vender', [VenderController::class, 'import'])->name('vender.import');
    Route::get('export/invoice', [InvoiceController::class, 'export'])->name('invoice.export');
    Route::get('export/proposal', [ProposalController::class, 'export'])->name('proposal.export');
    Route::get('export/bill', [BillController::class, 'export'])->name('bill.export');


    Route::get('export/employee', [EmployeeController::class, 'export'])->name('employee.export');
    Route::get('import/employee/file', [EmployeeController::class, 'importFile'])->name('employee.file.import');
    Route::post('import/employee', [EmployeeController::class, 'import'])->name('employee.import');


    Route::get('import/attendance/file', [AttendanceEmployeeController::class, 'importFile'])->name('attendance.file.import');
    Route::post('import/attendance', [AttendanceEmployeeController::class, 'import'])->name('attendance.import');



    Route::get('export/transaction', [TransactionController::class, 'export'])->name('transaction.export');
    Route::get('export/accountstatement', [ReportController::class, 'export'])->name('accountstatement.export');
    Route::get('export/productstock', [ReportController::class, 'stock_export'])->name('productstock.export');
    Route::get('export/payroll', [ReportController::class, 'PayrollReportExport'])->name('payroll.export');
    Route::get('export/leave', [ReportController::class, 'LeaveReportExport'])->name('leave.export');

    Route::post('export/payslip', [PaySlipController::class, 'export'])->name('payslip.export');


    // Time-Tracker
    Route::post('stop-tracker', [DashboardController::class, 'stopTracker'])->name('stop.tracker')->middleware(['auth', 'XSS']);
    Route::get('time-tracker', [TimeTrackerController::class, 'index'])->name('time.tracker')->middleware(['auth', 'XSS']);
    Route::delete('tracker/{tid}/destroy', [TimeTrackerController::class, 'Destroy'])->name('tracker.destroy');
    Route::post('tracker/image-view', [TimeTrackerController::class, 'getTrackerImages'])->name('tracker.image.view');
    Route::delete('tracker/image-remove', [TimeTrackerController::class, 'removeTrackerImages'])->name('tracker.image.remove');
    Route::get('projects/time-tracker/{id}', [ProjectController::class, 'tracker'])->name('projecttime.tracker')->middleware(['auth', 'XSS']);


    // Zoom Meeting
    Route::resource('zoom-meeting', ZoomMeetingController::class)->middleware(['auth', 'XSS']);
    Route::any('/zoom-meeting/projects/select/{bid}', [ZoomMeetingController::class, 'projectwiseuser'])->name('zoom-meeting.projects.select');
    Route::get('zoom-meeting-calender', [ZoomMeetingController::class, 'calender'])->name('zoom-meeting.calender')->middleware(['auth', 'XSS']);


    // PaymentWall

    Route::post('/paymentwalls', [PaymentWallPaymentController::class, 'paymentwall'])->name('plan.paymentwallpayment')->middleware(['XSS']);
    Route::post('/plan-pay-with-paymentwall/{plan}', [PaymentWallPaymentController::class, 'planPayWithPaymentWall'])->name('plan.pay.with.paymentwall')->middleware(['auth', 'XSS']);
    Route::get('/plan/{flag}', [PaymentWallPaymentController::class, 'planeerror'])->name('error.plan.show');

    //POS System

    Route::get('returnorder/items', [ReturnOrderController::class, 'items'])->name('returnorder.items');
    Route::resource('returnorder', ReturnOrderController::class)->middleware(['auth', 'XSS', 'revalidate']);
    Route::any('returnorders/create/{cid}', [ReturnOrderController::class, 'returnorderCreate'])->name('returnorders.create')->middleware(['auth', 'XSS', 'revalidate']);
    Route::post('returnorder/product', [ReturnOrderController::class, 'product'])->name('returnorder.product');
    Route::post('returnorder/product/destroy', [ReturnOrderController::class, 'productDestroy'])->name('returnorder.product.destroy');
    Route::get('returnorder/convert/{id}', [ReturnOrderController::class, 'convert'])->name('returnorder.convert');
    Route::post('quantity/product', [ReturnOrderController::class, 'productQuantity'])->name('product.quantity');
    Route::post('/returnorder/template/setting', [ReturnOrderController::class, 'saveQuotationTemplateSettings'])->name('returnorder.template.setting');
    Route::get('returnorder/preview/{template}/{color}', [ReturnOrderController::class, 'previewQuotation'])->name('returnorder.preview')->middleware(['auth', 'XSS']);
    Route::get('printview/returnorder', [ReturnOrderController::class, 'printView'])->name('returnorder.printview');
    Route::get('returnorder/pdf/{id}', [ReturnOrderController::class, 'returnorder'])->name('returnorder.pdf')->middleware(['auth', 'XSS', 'revalidate']);
    Route::post('returnorder_approved', [ReturnOrderController::class, 'approved'])->name('returnorder_approved')->middleware(['auth', 'XSS']);

    Route::get('returnorder-report', [ReturnOrderController::class, 'report'])->name('returnorder.reports');
    Route::get('returnorderproduct-report', [ReturnOrderController::class, 'reportprducts'])->name('returnorderproduct.report');

    Route::get('staff-turnover', [EmployeeReportsController::class, 'staffTurnOver'])->name('staff.turnover');
    Route::get('emp-loan', [EmployeeReportsController::class, 'empLoan'])->name('empLoan');

    //POS Systempayment
    Route::post('branch_store', [WarehouseController::class, 'branch_store'])->name('branch.store')->middleware(['auth', 'XSS']);
    Route::get('print/{id}', [WarehouseController::class, 'print'])->name('branch.print')->middleware(['auth', 'XSS']);
    Route::resource('store', WarehouseController::class)->middleware(['auth', 'XSS', 'revalidate']);
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('purchase/items', [PurchaseController::class, 'items'])->name('purchase.items');
            Route::resource('purchase', PurchaseController::class);


            //    Route::get('/bill/{id}/', 'PurchaseController@purchaseLink')->name('purchase.link.copy');
            Route::get('purchase/{id}/payment', [PurchaseController::class, 'payment'])->name('purchase.payment');
            Route::post('purchase/{id}/payment', [PurchaseController::class, 'createPayment'])->name('purchase.payment');
            Route::post('purchase/{id}/payment/{pid}/destroy', [PurchaseController::class, 'paymentDestroy'])->name('purchase.payment.destroy');
            Route::post('purchase/product/destroy', [PurchaseController::class, 'productDestroy'])->name('purchase.product.destroy');
            Route::post('purchase/vender', [PurchaseController::class, 'vender'])->name('purchase.vender');
            Route::post('purchase/product', [PurchaseController::class, 'product'])->name('purchase.product');
            Route::get('purchase/create/{cid}', [PurchaseController::class, 'create'])->name('purchase.create');
            Route::get('purchase/{id}/sent', [PurchaseController::class, 'sent'])->name('purchase.sent');
            Route::get('purchase/{id}/resent', [PurchaseController::class, 'resent'])->name('purchase.resent');

        }

    );
    Route::get('pos-print-setting', [SystemController::class, 'posPrintIndex'])->name('pos.print.setting')->middleware(['auth', 'XSS']);
    Route::get('purchase/preview/{template}/{color}', [PurchaseController::class, 'previewPurchase'])->name('purchase.preview')->middleware(['auth', 'XSS']);
    Route::get('pos/preview/{template}/{color}', [PosController::class, 'previewPos'])->name('pos.preview')->middleware(['auth', 'XSS']);


    //
    Route::get('purchase-report', [PurchaseController::class, 'pur_rep'])->name('purchase.report');
    Route::get('purchase/report/pdf', [PurchaseController::class, 'purchaseReport'])->name('purchase_pdf.report');
    Route::get('purchase-product-report', [PurchaseController::class, 'pur_pro_rep'])->name('purchaseproduct.report');
    Route::get('purchase-product-vendor-report', [PurchaseController::class, 'purchaseProductByVendorReport'])->name('purchaseproductbyvendor.report');
    Route::get('purchase-product/report', [PurchaseController::class, 'purchaseProductReport'])->name('purch_product.report');
    Route::get('purchase-vendor-summry', [PurchaseController::class, 'purchaseVendorSummary'])->name('purchase_vendor_summary');
    //sales
    Route::get('sales-by-customer', [InventoryReportController::class, 'sales_by_customer'])->name('sales_by_customer');

    // Route::post('vendor_store', [PurchaseController::class, 'vendor_store'])->name('vendor.store');


    Route::post('/purchase/template/setting', [PurchaseController::class, 'savePurchaseTemplateSettings'])->name('purchase.template.setting');
    Route::post('/pos/template/setting', [PosController::class, 'savePosTemplateSettings'])->name('pos.template.setting');

    Route::get('purchase/pdf/{id}', [PurchaseController::class, 'purchase'])->name('purchase.pdf')->middleware(['auth', 'XSS', 'revalidate']);
    Route::get('pos/pdf/{id}', [PosController::class, 'pos'])->name('pos.pdf')->middleware(['auth', 'XSS', 'revalidate']);
    Route::get('pos/data/store', [PosController::class, 'store'])->name('pos.data.store')->middleware(['auth', 'XSS', 'revalidate']);

    //for pos print
    Route::get('printview/pos', [PosController::class, 'printView'])->name('pos.printview')->middleware(['auth', 'XSS', 'revalidate']);


    Route::resource('pos', PosController::class)->middleware(['auth', 'XSS', 'revalidate']);

    Route::get('product-categories', [ProductServiceCategoryController::class, 'getProductCategories'])->name('product.categories')->middleware(['auth', 'XSS']);
    Route::get('add-to-cart/{id}/{session}', [ProductServiceController::class, 'addToCart'])->middleware(['auth', 'XSS']);
    Route::patch('update-cart', [ProductServiceController::class, 'updateCart'])->middleware(['auth', 'XSS']);
    Route::delete('remove-from-cart', [ProductServiceController::class, 'removeFromCart'])->middleware(['auth', 'XSS']);

    Route::get('name-search-products', [ProductServiceCategoryController::class, 'searchProductsByName'])->name('name.search.products')->middleware(['auth', 'XSS']);
    Route::get('search-products', [ProductServiceController::class, 'searchProducts'])->name('search.products')->middleware(['auth', 'XSS']);
    Route::any('report/pos', [PosController::class, 'report'])->name('pos.report')->middleware(['auth', 'XSS']);


    //warehouse-transfer
    Route::resource('store-transfer', WarehouseTransferController::class)->middleware(['auth', 'XSS', 'revalidate']);
    Route::post('warehouse-transfer/getproduct', [WarehouseTransferController::class, 'getproduct'])->name('warehouse-transfer.getproduct')->middleware(['auth', 'XSS']);
    Route::post('warehouse-transfer/getquantity', [WarehouseTransferController::class, 'getquantity'])->name('warehouse-transfer.getquantity')->middleware(['auth', 'XSS']);




    //pos barcode
    Route::get('barcode/pos', [PosController::class, 'barcode'])->name('pos.barcode')->middleware(['auth', 'XSS']);
    Route::get('setting/pos', [PosController::class, 'setting'])->name('pos.setting')->middleware(['auth', 'XSS']);
    Route::post('barcode/settings', [PosController::class, 'BarcodesettingStore'])->name('barcode.setting');
    Route::get('print/pos', [PosController::class, 'printBarcode'])->name('pos.print')->middleware(['auth', 'XSS']);
    Route::post('pos/getproduct', [PosController::class, 'getproduct'])->name('pos.getproduct')->middleware(['auth', 'XSS']);
    Route::any('pos-receipt', [PosController::class, 'receipt'])->name('pos.receipt')->middleware(['auth', 'XSS']);
    Route::post('/cartdiscount', [PosController::class, 'cartdiscount'])->name('cartdiscount')->middleware(['auth', 'XSS']);


    //Storage Setting

    Route::post('storage-settings', [SystemController::class, 'storageSettingStore'])->name('storage.setting.store')->middleware(['auth', 'XSS']);


    //appricalStar

    Route::post('/appraisals', [AppraisalController::class, 'empByStar'])->name('empByStar')->middleware(['auth', 'XSS']);
    Route::post('/appraisals1', [AppraisalController::class, 'empByStar1'])->name('empByStar1')->middleware(['auth', 'XSS']);
    Route::post('/getemployee', [AppraisalController::class, 'getemployee'])->name('getemployee');


    //offer Letter

    Route::post('setting/offerlatter/{lang?}', [SystemController::class, 'offerletterupdate'])->name('offerlatter.update');
    Route::get('setting/offerlatter', [SystemController::class, 'companyIndex'])->name('get.offerlatter.language');
    Route::get('job-onboard/pdf/{id}', [JobApplicationController::class, 'offerletterPdf'])->name('offerlatter.download.pdf');
    Route::get('job-onboard/doc/{id}', [JobApplicationController::class, 'offerletterDoc'])->name('offerlatter.download.doc');


    //joining Letter
    Route::post('setting/joiningletter/{lang?}', [SystemController::class, 'joiningletterupdate'])->name('joiningletter.update');
    Route::get('setting/joiningletter/', [SystemController::class, 'companyIndex'])->name('get.joiningletter.language');
    Route::get('employee/pdf/{id}', [EmployeeController::class, 'joiningletterPdf'])->name('joiningletter.download.pdf');
    Route::get('employee/doc/{id}', [EmployeeController::class, 'joiningletterDoc'])->name('joininglatter.download.doc');


    //Experience Certificate

    Route::post('setting/exp/{lang?}', [SystemController::class, 'experienceCertificateupdate'])->name('experiencecertificate.update');
    Route::get('setting/exp', [SystemController::class, 'companyIndex'])->name('get.experiencecertificate.language');
    Route::get('employee/exppdf/{id}', [EmployeeController::class, 'ExpCertificatePdf'])->name('exp.download.pdf');
    Route::get('employee/expdoc/{id}', [EmployeeController::class, 'ExpCertificateDoc'])->name('exp.download.doc');

    //NOC

    Route::post('setting/noc/{lang?}', [SystemController::class, 'NOCupdate'])->name('noc.update');
    Route::get('setting/noc', [SystemController::class, 'companyIndex'])->name('get.noc.language');
    Route::get('employee/nocpdf/{id}', [EmployeeController::class, 'NocPdf'])->name('noc.download.pdf');
    Route::get('employee/nocdoc/{id}', [EmployeeController::class, 'NocDoc'])->name('noc.download.doc');


    Route::get('appointment-letter', [AppointmentLetter::class, 'index'])->name('appointment-letter');
    Route::get('appointment-letter-create', [AppointmentLetter::class, 'create'])->name('appointment-letter-create');
    Route::post('appointment-letter-store', [AppointmentLetter::class, 'store'])->name('appointment-letter-store');
    Route::get('appointment-letter-edit/{id}', [AppointmentLetter::class, 'edit'])->name('appointment-letter-edit');
    Route::put('appointment-letter-update/{id}', [AppointmentLetter::class, 'update'])->name('appointment-letter-update');


    //Project Reports

    Route::resource('/project_report', ProjectReportController::class)->middleware(['auth', 'XSS']);
    Route::post('/project_report_data', [ProjectReportController::class, 'ajax_data'])->name('projects.ajax')->middleware(['auth', 'XSS']);
    Route::post('/project_report/tasks/{id}', [ProjectReportController::class, 'ajax_tasks_report'])->name('tasks.report.ajaxdata')->middleware(['auth', 'XSS']);
    Route::get('export/task_report/{id}', [ProjectReportController::class, 'export'])->name('project_report.export');


    //project copy module
    Route::get('/project/copy/{id}', [ProjectController::class, 'copyproject'])->name('project.copy')->middleware(['auth', 'XSS']);
    Route::post('/project/copy/store/{id}', [ProjectController::class, 'copyprojectstore'])->name('project.copy.store')->middleware(['auth', 'XSS']);


    //Google Calendar
    Route::any('event/get_event_data', [EventController::class, 'get_event_data'])->name('event.get_event_data')->middleware(['auth', 'XSS']);

    Route::post('setting/google-calender', [SystemController::class, 'saveGoogleCalenderSettings'])->name('google.calender.settings');
    Route::any('holiday/get_holiday_data', [HolidayController::class, 'get_holiday_data'])->name('holiday.get_holiday_data')->middleware(['auth', 'XSS']);
    Route::any('interview-schedule/get_interview_data', [InterviewScheduleController::class, 'get_interview_data'])->name('holiday.get_interview_data')->middleware(['auth', 'XSS']);
    Route::post('calendar/get_task_data', [ProjectTaskController::class, 'get_task_data'])->name('task.calendar.get_task_data')->middleware(['auth', 'XSS']);
    Route::any('zoom-meeting/get_zoom_meeting_data', [ZoomMeetingController::class, 'get_zoom_meeting_data'])->name('zoom-meeting.get_zoom_meeting_data')->middleware(['auth', 'XSS']);

    Route::any('meeting/get_meeting_data', [MeetingController::class, 'get_meeting_data'])->name('meeting.get_meeting_data')->middleware(['auth', 'XSS']);
    Route::get('meeting-calender', [MeetingController::class, 'calender'])->name('meeting.calender')->middleware(['auth', 'XSS']);

    Route::any('event/get_dashboard_event_data', [EventController::class, 'get_dashboard_event_data'])->name('event.get_dashboard_event_data')->middleware(['auth', 'XSS']);

    //branch wise department get in attendance report
    Route::post('reports-monthly-attendance/getdepartment', [ReportController::class, 'getdepartment'])->name('report.attendance.getdepartment')->middleware(['auth', 'XSS']);
    Route::post('reports-monthly-attendance/getemployee', [ReportController::class, 'getemployee'])->name('report.attendance.getemployee')->middleware(['auth', 'XSS']);


    //shared project & copy link
    Route::any('/projects/copy/link/{id}', [ProjectController::class, 'copylinksetting'])->name('projects.copy.link');
    Route::any('/projects{id}/settingcreate', [ProjectController::class, 'copylink_setting_create'])->name('projects.copylink.setting.create');
    Route::get('/shareproject/{lang?}', [ProjectController::class, 'shareproject'])->name('shareproject');


    //User Log
    Route::get('/userlogs', [UserController::class, 'userLog'])->name('user.userlog')->middleware(['auth', 'XSS']);
    Route::get('userlogs/{id}', [UserController::class, 'userLogView'])->name('user.userlogview')->middleware(['auth', 'XSS']);
    Route::delete('userlogs/{id}', [UserController::class, 'userLogDestroy'])->name('user.userlogdestroy')->middleware(['auth', 'XSS']);


    //notification Template
    Route::get('notification_templates/{id?}/{lang?}', [NotificationTemplatesController::class, 'index'])->name('notification_templates.index')->middleware(['auth', 'XSS',]);
    Route::resource('notification-templates', NotificationTemplatesController::class)->middleware(['auth', 'XSS',]);

    //Proposal/Invoice/Bill/Purchase/POS - footer notes
    Route::post('system-settings/note', [SystemController::class, 'footerNoteStore'])->name('system.settings.footernote')->middleware(['auth', 'XSS']);

    //AI module
    Route::post('chatgpt-settings', [SystemController::class, 'chatgptSetting'])->name('chatgpt.settings');
    Route::get('generate/{template_name}', [AiTemplateController::class, 'create'])->name('generate');
    Route::post('generate/keywords/{id}', [AiTemplateController::class, 'getKeywords'])->name('generate.keywords');
    Route::post('generate/response', [AiTemplateController::class, 'AiGenerate'])->name('generate.response');

    //AI module for grammar check
    Route::get('grammar/{template}', [AiTemplateController::class, 'grammar'])->name('grammar')->middleware(['auth', 'XSS']);
    Route::post('grammar/response', [AiTemplateController::class, 'grammarProcess'])->name('grammar.response')->middleware(['auth', 'XSS']);

    //IP-Restrication settings
    Route::get('create/ip', [SystemController::class, 'createIp'])->name('create.ip')->middleware(['auth', 'XSS']);
    Route::post('create/ip', [SystemController::class, 'storeIp'])->name('store.ip')->middleware(['auth', 'XSS']);
    Route::get('edit/ip/{id}', [SystemController::class, 'editIp'])->name('edit.ip')->middleware(['auth', 'XSS']);
    Route::post('edit/ip/{id}', [SystemController::class, 'updateIp'])->name('update.ip')->middleware(['auth', 'XSS']);
    Route::delete('destroy/ip/{id}', [SystemController::class, 'destroyIp'])->name('destroy.ip')->middleware(['auth', 'XSS']);

    //lang enable / disable
    Route::post('disable-language', [LanguageController::class, 'disableLang'])->name('disablelanguage')->middleware(['auth', 'XSS']);

    //Expense Module
    Route::get('expense/pdf/{id}', [ExpenseController::class, 'expense'])->name('expense.pdf')->middleware(['XSS', 'revalidate']);
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::get('expense/index', [ExpenseController::class, 'index'])->name('expense.index');
            Route::any('expense/customer', [ExpenseController::class, 'customer'])->name('expense.customer');
            Route::post('expense/vender', [ExpenseController::class, 'vender'])->name('expense.vender');
            Route::post('expense/employee', [ExpenseController::class, 'employee'])->name('expense.employee');

            Route::post('expense/product/destroy', [ExpenseController::class, 'productDestroy'])->name('expense.product.destroy');

            Route::post('expense/product', [ExpenseController::class, 'product'])->name('expense.product');
            Route::get('expense/{id}/payment', [ExpenseController::class, 'payment'])->name('expense.payment');
            Route::get('expense/items', [ExpenseController::class, 'items'])->name('expense.items');

            Route::resource('expense', ExpenseController::class);
            Route::post('branch_category', [ExpenseController::class, 'branch_category'])->name('branch.category');
            Route::get('expense/create/{cid}', [ExpenseController::class, 'create'])->name('expense.create');

        }
    );

    //  Student Modules
    Route::group(
        [
            'middleware' => [
                'auth',
                'XSS',
                'revalidate',
            ],
        ],
        function () {
            Route::post('/session_branch', [SessionController::class, 'session_branch'])->name('session_branch');
            Route::resource('/session', SessionController::class);
            Route::post('/sessions/{sessionId}/update-status', [SessionController::class, 'updateSessionStatus'])->name('update_session_status');
            Route::resource('/section', SectionController::class);
            Route::resource('/classes', ClassesController::class);
            Route::post('/student-promotion/heads', [StudentPromotions::class, 'feeheads'])->name('student-promotion.headsupdate');
            Route::resource('/student-promotion', StudentPromotions::class);
            Route::resource('/registration', StudentRegistration::class);
            Route::get('/SiblingonFathercnic', [StudentRegistration::class, 'SiblingonFathercnic'])->name('SiblingonFathercnic');
            Route::get('/get-concession', [StudentRegistration::class, 'getconcession'])->name('get.concession');
            Route::get('/registration-receipt/{id}', [StudentRegistration::class, 'receipt'])->name('reg.receipt');
            Route::post('account-wise-fee/detach', [AccountWiseFeeStructure::class, 'detach_student_fee_head'])->name('account-wise-fee.detach');
            Route::post('account-wise-fee/detach-bulk', [AccountWiseFeeStructure::class, 'detach_student_fee_bulk'])->name('account-wise-fee.detach-bulk');
            Route::post('update-student-fee-str', [AccountWiseFeeStructure::class, 'update_student_fee_str'])->name('account-wise-fee.save')->middleware(['auth', 'XSS']);
            Route::resource('/account-wise-fee', AccountWiseFeeStructure::class);
            Route::get('/admission_order/{id}', [StudentRegistration::class, 'admission_order'])->name('admission.order');
            Route::get('/student-fee-generate/{id}', [ClassWiseFeeController::class, 'student_fee_generate'])->name('student.fee_generate');
            Route::get('/class_wise_fee/report', [ClassWiseFeeController::class, 'classWiseFeeReport'])->name('class_wise_fee.report');
            Route::get('/feestructurelisting/report', [ClassWiseFeeController::class, 'feestructurelisting'])->name('feestructurelisting');
            Route::get('/feestructurelisting/export', [ClassWiseFeeController::class, 'feeStructureReport'])->name('feestructurelisting.export');
            Route::get('/fee_structure/report', [ClassWiseFeeController::class, 'feeStructureReport'])->name('fee_structure.report');
            Route::resource('/class_wise_fee', ClassWiseFeeController::class);
            Route::resource('/registerOption', RegisterOptionController::class)->name('registerOption', 'registerOption');
            Route::resource('/student_receipt', StudentReceipt::class)->name('studentreceipt', 'studentreceipt');
            Route::get('/student-receipt', [StudentReceipt::class, 'list'])->name('student_receipt.list');
            Route::resource('/feereminderslip', FeeReminderSlip::class)->name('feereminderslip', 'feereminderslip');

            Route::post('/branch-session-class', [ClassWiseFeeController::class, 'sessionclass'])->name('branch.session_class');
            Route::post('/get-class-students', [ClassWiseFeeController::class, 'getClassStudents'])->name('class.students');
            Route::post('/get-class-withdraw-students', [ClassWiseFeeController::class, 'getClasswithdrawStudents'])->name('class.withdrawstudents');
            Route::post('/student-fee', [ClassWiseFeeController::class, 'studentfeestructure'])->name('branch.student-fee');
            Route::get('/enrollment', [StudentEnrollment::class, 'index'])->name('enrollment.index');
            Route::get('/enrollment/create/{id?}', [StudentEnrollment::class, 'create'])->name('enrollment.create');
            Route::post('/enrollment/store', [StudentEnrollment::class, 'store'])->name('enrollment.store');
            Route::get('/enrollment/edit/{id}', [StudentEnrollment::class, 'edit'])->name('enrollment.update');
            Route::post('/enrollment/edit/{id}', [StudentEnrollment::class, 'update'])->name('enrollment.edit');
            Route::get('/challan/detail/ajax', [ChallanController::class, 'ajaxDetail'])->name('challan.detail.ajax');
            Route::post('/class-students', [ClassWiseFeeController::class, 'classStudents'])->name('class_students');
            Route::get('/challan/{id?}', [ChallanController::class, 'index'])->name('challan.index');
            Route::get('/challan/create/{id?}', [ChallanController::class, 'generate'])->name('challan.create');
            Route::get('/challan/show/{id}', [ChallanController::class, 'show'])->name('challan.show');
            Route::post('/challan', [ChallanController::class, 'store'])->name('challan.store');
            Route::get('/challan-fine', [ChallanController::class, 'challanLateFine'])->name('challan.challanLateFine');
            Route::get('/challanlist', [ChallanController::class, 'challanlist'])->name('challan.challanlist');
            Route::get('/challans/{id}', [ChallanController::class, 'challanedit'])->name('challan.challanedit');
            Route::put('/challans/{id}', [ChallanController::class, 'challanupdate'])->name('challan.update');
            Route::get('/challan-edit/{id}', [ChallanController::class, 'edit'])->name('challan.edit');
            Route::post('/challan-update/{id}', [ChallanController::class, 'update'])->name('challan.update');
            Route::get('/challan-pay/{id}', [ChallanController::class, 'challanpay'])->name('challan.pay');
            Route::post('/challan-pay/{id}', [ChallanController::class, 'challanpaid'])->name('challan.paid');
            Route::post('/printchallans', [ChallanController::class, 'printchallans'])->name('printchallans');
            Route::get('/challandata_for_receipt', [ChallanController::class, 'challandata_for_receipt'])->name('challandata_for_receipt');
            Route::post('/bulkchallan', [ChallanController::class, 'bulkchallan'])->name('bulkchallan');
            Route::post('/challan/bulk-delete', [ChallanController::class, 'destroy'])->name('challan.rollback');

            Route::post('/generateChallan', [ChallanController::class, 'generateChallan'])->name('generateChallan');
            Route::post('/paidchallan', [ChallanController::class, 'paidchallan'])->name('paidchallan');


            // challan lists
            Route::get('/admission-challan', [ChallanController::class, 'admissionchallanlist'])->name('admissionchallanlist');
            Route::get('/registration-challan', [ChallanController::class, 'registrationchallanlist'])->name('registrationchallanlist');
            Route::get('/regular-challan', [ChallanController::class, 'regularchallanlist'])->name('regularchallanlist');
            Route::get('/advance-challan', [ChallanController::class, 'advancechallanlist'])->name('advancechallanlist');
            Route::get('/create-challan', [ChallanController::class, 'createChallan'])->name('createChallan');
            Route::post('/store-challan', [ChallanController::class, 'storeChallan'])->name('storeChallan');
            Route::get('/transfer-challan', [ChallanController::class, 'transferchallanlist'])->name('transferchallanlist');
            Route::get('/readmission-challan', [ChallanController::class, 'readmissionchallanlist'])->name('readmissionchallanlist');
            Route::get('/installments/{id?}', [ChallanController::class, 'installmentview'])->name('installmentview');
            Route::post('/installment-challan/{id}', [ChallanController::class, 'installment_challan'])->name('installment-challan');
            Route::get('/installments/readmission/{id?}', [ChallanController::class, 'installmentviewreadmission'])->name('installmentviewreadmission');
            Route::post('/installment-challan/readmission/{id}', [ChallanController::class, 'installment_challan_readmission'])->name('installment-challan-readmission');
            Route::get('readmission/change-status/{id}/{status}', [StudentReadmissionController::class, 'changeStatus'])->name('readmission.change_status');

            Route::get('/withdraw-challan', [ChallanController::class, 'withdrawchallanlist'])->name('withdrawchallanlist');

            Route::get('/fetch-class-id', [ChallanController::class, 'fetchClassId'])->name('fetch_class_id');
            Route::get('/fetch-heads-and-amounts', [ChallanController::class, 'fetchHeadsAndAmounts'])->name('fetch_heads_and_amounts');
            Route::resource('/fee_head', FeeHeadController::class);
            Route::get('/class_fee/{id}', [ClassWiseFeeController::class, 'index'])->name('class_fee');
            Route::get('/class_fee_create/{id}', [ClassWiseFeeController::class, 'create'])->name('class_fee_create');
            // Route::resource('/class_wise_fee',ClassWiseFeeController::class);
            Route::resource('/concession_policy', ConcessionPolicyController::class);
            Route::get('/concession/report', [ConcessionController::class, 'concessionReport'])->name('concession.report');
            Route::resource('/concession', ConcessionController::class);
            Route::resource('/transferstudent', StudentTransferController::class);
            Route::get('/transferapplication/{id}', [StudentTransferController::class, 'transferapplication'])->name('transferapplication');
            Route::get('/adminsend/{id}', [StudentTransferController::class, 'adminsend'])->name('adminsend');
            Route::get('transfer/change-status/{id}/{status}', [StudentTransferController::class, 'changeStatus'])->name('transfer.change_status');
            Route::post('/transfer-balance-calculate', [StudentTransferController::class, 'calculateBalance'])->name('transfer_balance.calculate');
            Route::post('/transfer-add-head', [StudentTransferController::class, 'challanheadadd'])->name('transfer.add_head');
            Route::resource('/withdrawlstudent', StudentWithdrawalController::class);
            Route::get('/withdrawlapplication/{id}', [StudentWithdrawalController::class, 'withdrawlapplication'])->name('withdrawlapplication');
            Route::get('/fwd-to-ho/{id}', [StudentWithdrawalController::class, 'fwdtoho'])->name('fwdtoho');
            Route::post('/withdrawlapplication/{id}/store', [StudentWithdrawalController::class, 'withdrawlapplicationstore'])->name('withdrawlapplicationstore');
            Route::post('/calculate-balance', [StudentWithdrawalController::class, 'calculateBalance'])->name('calculate.balance');
            Route::get('/clearance-certificate/{id}', [StudentWithdrawalController::class, 'clearance_certificate'])->name('clearance_certificate');
            Route::get('/clearanceCertificate', [ClearanceCertificate::class, 'index'])->name('clearance.index');
            Route::get('/student-challan-detail', [StudentWithdrawalController::class, 'studentchallandetail'])->name('student-challan-detail');
            Route::get('/student-adjust-detail', [StudentWithdrawalController::class, 'studentadjustdetail'])->name('student-adjust-detail');
            Route::get('/student-challan-detail', [StudentWithdrawalController::class, 'studentchallandetail'])->name('student-challan-detail');
            Route::get('/student-adjust-detail', [StudentWithdrawalController::class, 'studentadjustdetail'])->name('student-adjust-detail');
            Route::get('studypack/items', [StudyPackController::class, 'items'])->name('studypack.items');
            Route::resource('/studypack', StudyPackController::class);
            Route::get('studypack/items', [StudyPackController::class, 'items'])->name('studypack.items');
            Route::post('studypack/product', [StudyPackController::class, 'product'])->name('studypack.product');
            Route::get('studypack/{id}/payment', [StudyPackChallanController::class, 'payment'])->name('studypack.payment');
            Route::post('studypack/{id}/payment', [StudyPackChallanController::class, 'addpayment'])->name('studypack.addpayment');
            // Route::get('studypackpayment',[StudyPackChallanController::class, 'addpayment'])->name('studypack.payment');
            Route::post('/studypackpaid', [StudyPackChallanController::class, 'paidstudypackchallan'])->name('studypackpaid');
            Route::get('/challandata_for_studypackreceipt', [StudyPackChallanController::class, 'challandata_for_studypackreceipt'])->name('challandata_for_studypackreceipt');
            Route::get('/studypackreceipts', [StudyPackChallanController::class, 'Studypackreceipts'])->name('studypackreceipts');
            Route::post('studypackchallan/product', [StudyPackChallanController::class, 'product'])->name('studypackchallan.product');
            Route::post('/deletestudypackChallanItems', [StudyPackChallanController::class, 'deleteChallanItems'])->name('deleteChallanItems');
            Route::get('/studypackchallan/items', [StudyPackChallanController::class, 'items'])->name('studypackchallan.items');
            Route::resource('/studypackchallan', StudyPackChallanController::class);
            Route::post('/studypackchallan/{id}/download', [StudyPackChallanController::class, 'show'])->name('studypackchallan.download');
            Route::get('/studypackchallan/{id}/print', [StudyPackChallanController::class, 'show'])->name('studypackchallan.print');
            Route::get('/studypackchallan/pdf/{id}', [StudyPackChallanController::class, 'print'])->name('studypackchallans.challanpdf');
            Route::post('/submit-adjustment', [StudentWithdrawalController::class, 'submit_adjustment'])->name('submit_adjustment');
            Route::post('/delete-adjustment', [StudentWithdrawalController::class, 'delete_adjustment'])->name('delete_adjustment');

            Route::resource('/readmissionstudent', StudentReadmissionController::class);
            Route::post('class_readmission', [StudentReadmissionController::class, 'readmission'])->name('class.readmission')->middleware(['auth', 'XSS']);

            ///***********************Student Reports**************************************** */
            Route::get('/admissionlist', [StudentReportController::class, 'admissionlist'])->name('admissionlist.index');
            Route::get('/studenttransferin', [StudentReportController::class, 'transferinindex'])->name('transferin.index');
            Route::get('/studenttransferin/report', [StudentReportController::class, 'transferinReport'])->name('transferin.report');
            Route::get('/studenttransferout', [StudentReportController::class, 'transferoutindex'])->name('transferout.index');
            Route::get('/studenttransferout/report', [StudentReportController::class, 'transferoutReport'])->name('transferout.report');
            Route::get('/classwisefeereport', [StudentReportController::class, 'classwisefeereportindex'])->name('classwisefeereport.index');
            Route::get('/classwisefee_structure_report/report', [StudentReportController::class, 'classwisefeeStructurereport'])->name('classwisefee_structure_report.report');

            Route::get('/admissionwithdrawalreport', [StudentReportController::class, 'admissionwithdrawal'])->name('admissionwithdrawal.index');
            Route::get('/admissionwithdrawal/pdf/report', [StudentReportController::class, 'admissionwithdrawalPdfReport'])->name('admissionwithdrawalPdf.report');
            Route::get('/studentstrengthreport', [StudentReportController::class, 'studentstrength'])->name('studentstrength.index');
            Route::get('/studentstatisticreport', [StudentReportController::class, 'studentstatistic'])->name('studentstatistic.index');
            // glance reports
            Route::get('/sessionWise', [StudentReportController::class, 'sessionWise'])->name('sessionWisereport');
            Route::get('/sessionBranchWise', [StudentReportController::class, 'sessionBranchWise'])->name('sessionBranchWise');
            Route::get('/sessionMonthWise', [StudentReportController::class, 'sessionMonthWise'])->name('sessionMonthWisereport');
            Route::get('/sessionMonthBranchWise', [StudentReportController::class, 'sessionMonthBranchWise'])->name('sessionMonthBranchWise');
            Route::get('/tuition_fee', [StudentReportController::class, 'tuition_feereport'])->name('tuition_feereport');
            Route::get('/monthlystatistics', [StudentReportController::class, 'monthlystatistics'])->name('monthlystatistics');
            Route::get('/monthlybranchwisereport', [StudentReportController::class, 'monthBranchWiseReport'])->name('monthlybranchwisereport');
            Route::get('/monthlychallanreport', [StudentReportController::class, 'monthlychallanreport'])->name('monthlychallanreport');
            Route::get('/advancechallanreport', [StudentReportController::class, 'advancechallanreport'])->name('advancechallanreport');
            Route::get('/monthlyperchallanreport', [StudentReportController::class, 'monthlyprechallanreport'])->name('monthlyprechallanreport');
            //
            Route::get('/sibling-students', [StudentReportController2::class, 'sibling_students'])->name('sibling_students');
            Route::get('/staff-child', [StudentReportController2::class, 'staff_child'])->name('staff_child');
            Route::get('/student-data-analysis', [StudentReportController2::class, 'student_data_analysis'])->name('student_data_analysis');
            Route::get('/studypack-student', [StudentReportController2::class, 'studypackStudent'])->name('studypackStudent');
            Route::get('/profession-wise-listing', [StudentReportController2::class, 'profession_wise_listing'])->name('profession_wise_listing');
            Route::get('/registrationdetailreport', [StudentReportController::class, 'registrationdetailreport'])->name('registrationdetailreport');
            Route::get('/studentSecurityReport', [StudentReportController::class, 'student_security_report'])->name('student_security_report');
            Route::get('/studentSecurityReport/pdf', [StudentReportController::class, 'student_security_reportPdf'])->name('student_security_report.report');
            Route::get('/fee-receipt-summary', [StudentReportController::class, 'fee_receipt_summary'])->name('fee_receipt_summary');
            Route::get('/fee-receipt-summary/report', [StudentReportController::class, 'fee_receipt_summary_report'])->name('fee_receipt_summary.report');
            Route::get('/admissionlisting', [StudentReportController::class, 'admissionlisting'])->name('admissionlisting');
            Route::get('/admissionlisting/report', [StudentReportController::class, 'admissionlistingReport'])->name('admissionlisting.report');
            Route::get('/student-fee-receipt-detail', [StudentReportController::class, 'student_fee_receipt_detail'])->name('student_fee_receipt_detail');
            Route::get('/student-fee-receipt-detail/report', [StudentReportController::class, 'student_fee_receipt_detail_report'])->name('student_fee_receipt_detail.report');
            Route::get('/student-defaulter', [StudentReportController::class, 'student_defaulter'])->name('student_defaulter');
            Route::get('/student-defaulter/report', [StudentReportController::class, 'student_defaulter_report'])->name('student-defaultert.report');
            Route::get('/student-defaulter-sm', [StudentReportController::class, 'student_defaulter_sm'])->name('student_defaulter_sm');
            Route::get('/student-single-account', [StudentReportController::class, 'student_single_account'])->name('student_single_account');
            Route::get('/student-single-account-detail', [StudentReportController::class, 'student_single_account_details'])->name('student_single_account_details');
            Route::get('/withdrawl_notice', [StudentReportController::class, 'withdarawl_notice'])->name('withdarawl_notice');
            Route::get('/student-withdarawl-listing', [StudentReportController::class, 'student_withdarawl_listing'])->name('student_withdarawl_listing');
            Route::get('/student-withdarawl-listing/report', [StudentReportController::class, 'student_withdarawl_listingReport'])->name('student_withdarawl_listing.report');
            Route::get('/period-wise-statistic-report', [StudentReportController::class, 'period_wise_statistic_report'])->name('period_wise_statistic_report');
            Route::get('/student-wise-statistic-report', [StudentReportController::class, 'student_wise_statistic_report'])->name('student_wise_statistic_report');
            Route::get('/student-wise-statistic-report/pdf', [StudentReportController::class, 'student_wise_statistic_report_pdf'])->name('student_wise_statistic.report');

            ///***********************Student Reports End**************************************** */
    
            // EmployeeScale
            Route::get('/get-dept-wise-scales', [EmployeeScaleController::class, 'getDeptWiseScales'])->name('get-dept-wise-scales');
            Route::get('/emp-scale-report/report', [EmployeeScaleController::class, 'generateReport'])->name('employee_scale.report');
            Route::resource('/employee_scale', EmployeeScaleController::class);
            Route::resource('/employee-scale-heads', EmployeeSalaryHeads::class);
            Route::get('/employee-assign-leave/{id}', [EmployeeController::class, 'AssignLeaveAfterProbation'])->name('AssignLeaveAfterProbation');
            Route::get('/employee-rejoin/{id}', [EmployeeController::class, 'emp_rejoin_show'])->name('emp-rejoin');
            Route::post('/employee-rejoin_post', [EmployeeController::class, 'emp_rejoin'])->name('emp-rejoin_save');
            Route::post('/branch-employee', [LeaveController::class, 'branchemployees'])->name('branch.employees');
            Route::post('/employee-job-info/{id}', [EmployeeController::class, 'employee_job_info'])->name('employee_job_info');
            Route::post('/employee-personal-info/{id}', [EmployeeController::class, 'employee_personal_info'])->name('employee-personal-info');
            Route::post('/employee-exp-info/{id}', [EmployeeController::class, 'employee_exp_info'])->name('employee_exp_info');
            Route::post('/employee-edu-info/{id}', [EmployeeController::class, 'employee_edu_info'])->name('employee_edu_info');
            Route::post('/employee-facility-info/{id}', [EmployeeController::class, 'employee_facility_info'])->name('employee_facility_info');
            Route::post('/employee-child/{id}', [EmployeeController::class, 'employee_child'])->name('employee_child');
            Route::get('/salary-history/{id?}', [EmployeeSalaryDetail::class, 'salary_history'])->name('salary_history');
            Route::get('/salary-history-report/{id?}', [EmployeeSalaryDetail::class, 'salary_history_report'])->name('salary_history_report');
            Route::get('/salary_history_detail/{id}', [EmployeeSalaryDetail::class, 'salary_history_detail'])->name('salary_history_detail');
            Route::get('/deduction_sheet/{id?}', [EmployeeSalaryDetail::class, 'deduction_sheet'])->name('deduction_sheet');
            Route::get('/paymode_sheet/{id?}', [EmployeeSalaryDetail::class, 'paymode_sheet'])->name('paymode_sheet');
            Route::get('/salary_sheet/{id?}', [EmployeeSalaryDetail::class, 'salary_sheet'])->name('salary_sheet');
            Route::get('/salary_slip/{id?}', [EmployeeSalaryDetail::class, 'salary_slip'])->name('salary_slip');
            Route::get('/gross_sheet/{id?}', [EmployeeSalaryDetail::class, 'gross_sheet'])->name('gross_sheet');
            Route::get('/advance_sheet/{id?}', [EmployeeSalaryDetail::class, 'advance'])->name('advance_sheet');
            Route::get('/printsalarydetail/{id?}', [EmployeeSalaryDetail::class, 'printsalarydetail'])->name('printsalarydetail');
            Route::post('/emp-salary-hold', [EmployeeSalaryDetail::class, 'hold_unhold'])->name('salary_hold_unhold');
            Route::post('/delete-employee-salary', [EmployeeSalaryDetail::class, 'destroy'])->name('delete_salary');
            Route::get('/generate-appointment-letter/{id}', [EmployeeSalaryDetail::class, 'generate_appointment_letter'])->name('generate_appointment_letter');
            Route::get('/generate-anexture/{id}', [EmployeeSalaryDetail::class, 'generate_anexture'])->name('generate_anexture');
            Route::get('/final-settlement/{id}', [ResignationController::class, 'final_settlement'])->name('final_settlement');
            Route::resource('/employee-salary-detail', EmployeeSalaryDetail::class);
            Route::post('/get-pay-scale-heads', [EmployeeSalaryDetail::class, 'getPayScaleHeads'])->name('get-pay-scale-heads');
            Route::get('/final-attendance/{id?}', [EmployeeMonthlySalaryAttendance::class, 'final_attendance'])->name('final_attendance');
            Route::post('/finalize-attendance/{id?}', [EmployeeMonthlySalaryAttendance::class, 'finalize_attendance'])->name('finalize_attendance');
            Route::post('/finalize-adm-attendance/{id?}', [EmployeeMonthlySalaryAttendance::class, 'finalize_adm_attendance'])->name('finalize_adm_attendance');
            Route::post('/Unfinalize-adm-attendance/{id?}', [EmployeeMonthlySalaryAttendance::class, 'unfinalize_adm_attendance'])->name('unfinalize_adm_attendance');
            Route::get('/month_salary/{id?}', [EmployeeMonthlySalaryAttendance::class, 'month_salary'])->name('month_salary');
            Route::post('/month-salary-generate', [EmployeeMonthlySalaryAttendance::class, 'month_salary_generate'])->name('month_salary_generate');
            Route::delete('/emp-month-sal-attendance/bulk-delete', [EmployeeMonthlySalaryAttendance::class, 'destroy'])->name('emp-month-sal-attendance.bulkDelete');
            Route::resource('/emp-month-sal-attendance', EmployeeMonthlySalaryAttendance::class);
            Route::post('salary/payment', [EmployeeMonthlySalaryAttendance::class, 'payment'])->name('salary.payments');
            Route::post('salary/{id}/payment', [EmployeeMonthlySalaryAttendance::class, 'createPayment'])->name('salary.payment');
            Route::post('/finalize-salary', [EmployeeSalaryDetail::class, 'finalize_salary'])->name('finalize_salary');
            Route::resource('/emp-concession-order', EmployeeConcessionOrder::class);
            Route::get('/employee/{id}/emergency-contact', [EmployeeController::class, 'getEmergencyContacts'])->name('employee.emergency.list');
            Route::post('/employee/{id}/emergency-contact/save', [EmployeeController::class, 'saveEmergencyContacts'])->name('employee.emergency.save');
            Route::delete('/emergency-contact/{id}', [EmployeeController::class, 'deleteEmergencyContact'])->name('emergency.delete');
            //EmployeeChildrenCnic
            Route::get('/employee-childs', [EmployeeController::class, 'EmployeeChildrenCnic'])->name('employee.children_cnic.list'); 

            //employee salary proposal
            Route::get('/employee-scale-details/{id?}', [EmployeeSalaryProposal::class, 'emp_scale_detail'])->name('emp_scale_detail');
            Route::resource('/employee-salary-proporal', EmployeeSalaryProposal::class);
            Route::post('employee-salary-proporal/{id}/sendForApproval', [EmployeeSalaryProposal::class, 'sendForApproval'])->name('employee-salary-proporal.sendForApproval');
            Route::post('employee-salary-proporal/{id}/approve', [EmployeeSalaryProposal::class, 'approve'])->name('employee-salary-proporal.approve');
            Route::post('employee-salary-proporal/{id}/rollback', [EmployeeSalaryProposal::class, 'rollback'])->name('employee-salary-proporal.rollback');
            //Employeee Reports
    
            Route::get('emp-monthly-sec', [EmployeeReportsController::class, 'monthlysecurtiyded'])->name('monthlysecurtiyded');
            Route::get('emp-monthly-sec/report', [EmployeeReportsController::class, 'monthlysecurtiydedReport'])->name('monthlysecurtiyded.report');
            Route::get('emp-eobi-report', [EmployeeReportsController::class, 'employeeEobiReport'])->name('employeeEobiReport');
            Route::get('emp-eobi-report/pdf', [EmployeeReportsController::class, 'employeeEobiPdfReport'])->name('employeeEobipdf.report');
            Route::get('emp-pessi-report', [EmployeeReportsController::class, 'employeepessiReport'])->name('employeepessireport');
            Route::get('emp-pessi-report/pdf', [EmployeeReportsController::class, 'employeepessiReportPdf'])->name('employeepessipdf.report');
            Route::get('emp-retirement-report', [EmployeeReportsController::class, 'empRetirementReport'])->name('empRetirementReport');
            Route::get('emp-insurance-report', [EmployeeReportsController::class, 'empInsuranceReport'])->name('empInsuranceReport');
            Route::get('single-emp-sec-report', [EmployeeReportsController::class, 'singleEmpSecReport'])->name('singleEmpSecReport');
            Route::get('emp-annual-srs', [EmployeeReportsController::class, 'empAnnualSrs'])->name('empAnnualSrs');
            Route::get('periodwise-report', [EmployeeReportsController::class, 'periodwisepayroll'])->name('periodwisepayroll');
            Route::get('periodwise-report/pdf', [EmployeeReportsController::class, 'periodwisepayrollReport'])->name('periodwisepayroll.report');
            Route::get('emp-sec-report', [EmployeeReportsController::class, 'empsecreport'])->name('empsecreport');
            Route::get('emp-sec-report/pdf', [EmployeeReportsController::class, 'empsecreportPdf'])->name('empsecpdf.report');
            Route::get('emp-transfer-report', [EmployeeReportsController::class, 'transferreport'])->name('transferreport');
            Route::get('emp-employment-history', [EmployeeReportsController::class, 'employmentHistory'])->name('employmentHistory');
            Route::get('emp-monthly-reconsilation', [EmployeeReportsController::class, 'employeeMonthlyReconsilation'])->name('employeeMonthlyReconsilation');
            Route::get('staff-child-report', [EmployeeReportsController::class, 'staffChildReport'])->name('staffChildReport');
            Route::get('emp-tax-ded-rpt', [EmployeeReportsController::class, 'employeeTaxReport'])->name('employeeTaxReport');
            Route::get('salary-comparison-report', [EmployeeReportsController::class, 'salaryComparisonRpt'])->name('salaryComparisonRpt');
            Route::get('employee-birthday', [EmployeeReportsController::class, 'empBirthdayRpt'])->name('empBirthdayRpt');
            Route::get('employee-qualification-report', [EmployeeReportsController::class, 'empEduRpt'])->name('empEduRpt');
            Route::get('employee-withoutpayleave-report', [EmployeeReportsController::class, 'empwithoutPayleave'])->name('empwithoutPayleave');
            Route::get('employee-leave-report', [EmployeeReportsController::class, 'empleaveReport'])->name('empleaveReport');
            //employee Transfer
            Route::get('employee-transfer-approval/{id}', [EmployeeTransferController::class, 'approve'])->name('employee-transfer.approve');
            Route::resource('employee-transfer', EmployeeTransferController::class);
            Route::get('employee-transfer-print/{id}', [EmployeeTransferController::class, 'print'])->name('employee-transfer.print');
            //employee Advance
            Route::resource('employee-advance', EmployeeAdvanceController::class);

            // Export salary sheet
            Route::get('/export-salary-sheet', [EmployeeSalaryDetail::class, 'export_salary_sheet'])->name('export_salary_sheet');
            Route::get('/export-deduction-sheet', [EmployeeSalaryDetail::class, 'export_deduction_sheet'])->name('export_deduction_sheet');
            Route::get('/export-gross-sheet', [EmployeeSalaryDetail::class, 'export_gross_sheet'])->name('export_gross_sheet');
            Route::get('/export-paymode_sheet', [EmployeeSalaryDetail::class, 'export_paymode_sheet'])->name('export_paymode_sheet');
            Route::get('/export-advance-sheet', [EmployeeSalaryDetail::class, 'export_advance_sheet'])->name('export_advance_sheet');

            // Employee Apraisal Forms
            Route::get('/employee-apprforms', [EmployeeApraisalFroms::class, 'AppraisalFrom'])->name('AppraisalFrom');
            Route::get('/it-teacher-appraisal-from', [EmployeeApraisalFroms::class, 'itTeacherAppraisalFrom'])->name('itTeacherAppraisalFrom');
            Route::get('/domestic-Staff-appraisal-from', [EmployeeApraisalFroms::class, 'domesticStaff'])->name('domesticStaff');
            Route::get('/sports-teacher-appraisal-from', [EmployeeApraisalFroms::class, 'sportsTeacher'])->name('sportsTeacher');
            Route::get('/teacher-appraisal-from', [EmployeeApraisalFroms::class, 'Teacherappr'])->name('Teacherappr');
            Route::get('/head-appraisal-from', [EmployeeApraisalFroms::class, 'directorHead'])->name('directorHead');


        }
    );

});
Route::post('branch_class', [StudentTransferController::class, 'branch_class'])->name('branch.class')->middleware(['auth', 'XSS']);
Route::post('class_section', [StudentTransferController::class, 'class_section'])->name('class.section')->middleware(['auth', 'XSS']);
Route::post('class_student_head', [StudentTransferController::class, 'class_student_head'])->name('class.student_head')->middleware(['auth', 'XSS']);
Route::post('section-student', [StudentTransferController::class, 'section_student'])->name('section.student')->middleware(['auth', 'XSS']);
Route::get('/get-sections/{classId}', [StudentEnrollment::class, 'get_sections'])->name('getsections');
Route::post('class_student', [ConcessionController::class, 'class_student'])->name('class.student')->middleware(['auth', 'XSS']);
Route::get('concession/change-status/{id}/{status}', [ConcessionController::class, 'changeStatus'])->name('concession.change_status');
Route::get('concession/cancel/{id}/', [ConcessionController::class, 'cancelconcession'])->name('concession.cancel');
Route::post('concession/cancel/{id}/', [ConcessionController::class, 'removeconcession'])->name('concession.remove');
Route::get('concession/student-detail/{id}', [ConcessionController::class, 'concession_student_detail']);
Route::post('concession_list', [ConcessionController::class, 'concession_search'])->name('concession_list');
Route::get('concessionstatus/{id}', [ConcessionController::class, 'concessionstatus'])->name('concession.status')->middleware(['auth', 'XSS']);
Route::post('concession_rejection/{id}', [ConcessionController::class, 'concessionrejection'])->name('concession.reject_reason')->middleware(['auth', 'XSS']);
Route::post('concession_order/{id}', [ConcessionController::class, 'concessionorder'])->name('concession-order')->middleware(['auth', 'XSS']);

Route::any('/cookie-consent', [SystemController::class, 'CookieConsent'])->name('cookie-consent');

Route::get('/clear', function () {
    // Artisan::call('migrate');
    Artisan::call('optimize:clear');
    return redirect()->back()->with('message', 'Caches cleared successfully.');
});

Route::get('/git_pull', function () {
    shell_exec('git stash -u');
    shell_exec('git pull');
    dd('sads');
    return redirect()->back()->with('message', 'Caches cleared successfully.');
});
Route::get('/data-import', [DataImportController::class, 'showForm'])->name('data.import.form');
Route::post('/data-import', [DataImportController::class, 'importData'])->name('data.import');
Route::get('/data-hr-import', [HrDataImportController::class, 'showHrForm'])->name('data.import.hrform');
Route::post('/data-hr-import', [HrDataImportController::class, 'importHRData'])->name('data.hrimport');
Route::get('/fetch-student-amount', [EmployeeController::class, 'fetchStudentAmount'])->name('fetch_student_amount');

Route::get('/testonelink', function (Request $request) {
    $apiKey = 'f701ed8c6a76577a02256dc9f2d520e7';      // Your API Key
    $apiSecret = '4b38703c7f2431571771083813dbf3f2';   // Your API Secret Key

    try {
        // Step 1: Request access token from 1Link API
        $response = Http::timeout(30) // 30 seconds timeout
            ->asForm()
            ->post('https://sandboxapi.1link.net.pk/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $apiKey,
                'client_secret' => $apiSecret,
            ]);

        if (!$response->ok()) {
            return response()->json([
                'error' => 'Failed to get access token',
                'response' => $response->body(),
            ], $response->status());
        }

        $accessToken = $response->json('access_token');
        if (!$accessToken) {
            return response()->json(['error' => 'Access token missing in response'], 500);
        }

        // Step 2: Make Bill Payment API request
        $apiResponse = Http::timeout(30)
            ->withHeaders([
                'Authorization' => "Bearer $accessToken",
                'X-IBM-Client-Id' => $apiKey,  // Usually the client id (API key), not secret here
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post('https://sandboxapi.1link.net.pk/uat-1link/sandbox/ubps-rest-service/BillPayment', [
                "billerId" => "TEST123",
                "consumerNumber" => "00000001",
                "amount" => 1000,
                "transactionDateTime" => now()->format('YmdHis'),
                "channelId" => "WEB",
                "referenceNumber" => uniqid("ref_"),
            ]);

        if (!$apiResponse->ok()) {
            return response()->json([
                'error' => 'Bill Payment API request failed',
                'response' => $apiResponse->body(),
            ], $apiResponse->status());
        }

        return response()->json($apiResponse->json());

    } catch (Exception $e) {
        // Handle cURL connection errors (timeout, SSL issues, etc)
        return response()->json([
            'error' => 'Connection error: ' . $e->getMessage(),
        ], 500);
    } catch (Exception $e) {
        return response()->json([
            'error' => 'Unexpected error: ' . $e->getMessage(),
        ], 500);
    }
});

Route::get('/onelinkcallback', function (Request $request) {
    return 'success callback';
});

Route::get('/delete-salary-import', function (Request $request) {
    set_time_limit(0);
    // Fetch the last $count salaries by descending ID (or created_at)
    $salaries = EmployeeMonthlySalary::where('id', '>=', 2055)->get();
    if ($salaries->isEmpty()) {
        return 'No salary entries found to delete.';
    }
    $deleted = 0;
    $attendance = \App\Models\EmployeeMonthlySalaryAttendance::where('id', '>=', 2055)->delete();
    foreach ($salaries as $salary) {
        DB::transaction(function () use ($salary, &$deleted) {
            EmployeeMonthlySalaryHeads::where('sal_id', $salary->id)->delete();
            if ($salary->voucher_id) {
                JournalEntry::where('id', $salary->voucher_id)->delete();
                JournalItem::where('journal', $salary->voucher_id)->delete();
            }
            $salary->delete();
            $deleted++;
        });
    }
    return 'Successfully deleted ' . $deleted . ' records';
});

Route::get('/emp-salaries-id-update', function () {
    $updated = 0;
    set_time_limit(0);
    EmployeeMonthlySalary::with('employee')
        ->where('owned_by', 2)
        ->chunk(500, function ($salaries) use (&$updated) {
            foreach ($salaries as $salary) {
                $employee = $salary->employee;
                if (!$employee)
                    continue;
                $employeeOwnedId = $employee->owned_by;
                DB::transaction(function () use ($salary, $employeeOwnedId, $employee) {
                    EmployeeMonthlySalaryHeads::where('sal_id', $salary->id)
                        ->update(['owned_by' => $employeeOwnedId]);
                    if ($salary->voucher_id) {
                        JournalEntry::where('id', $salary->voucher_id)
                            ->update(['owned_by' => $employeeOwnedId]);

                        \App\Models\EmployeeMonthlySalaryAttendance::where('employee_id', $employee->id)
                            ->update(['owned_by' => $employeeOwnedId]);
                    }
                    $salary->update(['owned_by' => $employeeOwnedId]);
                });

                $updated++;
            }
        });

    return 'Successfully updated ' . $updated . ' records';
});



//new 
// secrtet 4b38703c7f2431571771083813dbf3f2
// key f701ed8c6a76577a02256dc9f2d520e7


Route::get('/clearance-certificate-pdf/{id}', [StudentWithdrawalController::class, 'certificatePdf'])->name('student_withdrawal.certificate_pdf');
// for print
Route::get('/clearance-certificate-print/{id}', [StudentWithdrawalController::class, 'certificatePrint'])->name('student_withdrawal.certificate_print');

Route::delete('employee_exp_info/{id}', [App\Http\Controllers\EmployeeController::class, 'destroyEmployeeExperience'])->name('employee_exp_info.destroy');

Route::get('/employee-experience/{id}', [EmployeeController::class, 'getExperience']);

Route::delete('/employee-education/{id}', [EmployeeController::class, 'destroyEmployeeEducation'])->name('employee_education.destroy');

Route::get('/employee-education/{id}', [EmployeeController::class, 'getEducation']);

Route::delete('/employee-facility/{id}', [EmployeeController::class, 'destroyEmployeeFacility'])->name('employee_facility.destroy');

Route::get('/employee-facility/{id}', [EmployeeController::class, 'getFacility']);