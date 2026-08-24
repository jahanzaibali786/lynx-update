<?php

namespace App\Http\Controllers;

use App\Exports\StudentReceiptExport;
use App\Models\BankAccount;
use App\Models\ChallanHead;
use App\Models\Classes;
use App\Models\ClassWiseFee;
use App\Models\Customer;
use App\Models\FeeHead;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\Session;
use App\Models\StudentEnrollments;
use App\Models\StudentRegistration;
use App\Models\StudyPackChallans;
use App\Models\StudyPackChallanItems;
use App\Models\StudyPack;
use App\Models\StudypackReceipts;
use App\Models\StudyPackItem;
use App\Models\StudypackPayment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utility;
use App\Models\Vender;
use App\Models\warehouse;
use DB;
use Dompdf\Options;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
class StudyPackChallanController extends Controller
{
    public static function getValidItemPaymentAmount($amount, $item)
    {
        $amount = (float) ($amount ?? 0);
        $price = (float) ($item->price ?? 0);
        $qty = (int) ($item->qty ?? 1);
        $discount = (float) ($item->discount ?? 0);
        $alreadyPaid = (float) ($item->paid ?? 0);

        $payableAmount = ($price * $qty) - $discount - $alreadyPaid;
        if ($payableAmount <= 0) {
            return 0.0;
        }

        return min(max($amount, 0.0), $payableAmount);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = \Auth::user();

        $isCompany = $user->type === 'company';

        $creatorId = $user->creatorId();
        $ownedId = $user->ownedId();
        $selectedBranch = null;

        /*
        |--------------------------------------------------------------------------
        | Branches
        |--------------------------------------------------------------------------
        */
        if ($isCompany) {

            $branches = User::where('type', 'branch')
                ->where('created_by', $creatorId)
                ->where('is_active', 1)
                ->pluck('name', 'id');

            $branches->prepend(
                $user->name,
                $user->id
            );

            $branches->prepend(
                'Select Branch',
                ''
            );

        } else {

            /*
             * Branch user can only see their own branch.
             */
            $selectedBranch = $ownedId;
            $branches = User::where('id', $ownedId)
                ->where('is_active', 1)
                ->pluck('name', 'id');
        }

        /*
        |--------------------------------------------------------------------------
        | Sessions
        /*
        |--------------------------------------------------------------------------
        | Sessions
        |--------------------------------------------------------------------------
        */
        $session = Session::get()
            ->pluck('year', 'id');

        $session->prepend(
            'Select Session',
            ''
        );


        /*
        |--------------------------------------------------------------------------
        | Classes
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Your Classes table uses `owned_by`
        | for branch ownership.
        |
        | Therefore:
        |
        | Company + no branch:
        |     All company classes.
        |
        | Company + branch selected:
        |     classes.owned_by = selected branch.
        |
        | Branch user:
        |     classes.owned_by = their ownedId().
        |
        */
        $classQuery = Classes::query()
            ->where(
                'created_by',
                $creatorId
            )
            ->where(
                'active_status',
                1
            );


        /*
        |--------------------------------------------------------------------------
        | Apply Branch Ownership To Classes
        |--------------------------------------------------------------------------
        */
        if (
            $selectedBranch !== null &&
            $selectedBranch !== ''
        ) {

            $classQuery->where(
                'owned_by',
                $selectedBranch
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Class Dropdown
        |--------------------------------------------------------------------------
        */
        $class = $classQuery
            ->pluck(
                'name',
                'id'
            );

        $class->prepend(
            'Select Class',
            ''
        );


        /*
        |--------------------------------------------------------------------------
        | StudyPack Challans Query
        |--------------------------------------------------------------------------
        */
        $studypacksQuery = StudyPackChallans::with([
            'student',
            'receipts',
        ]);


        /*
        |--------------------------------------------------------------------------
        | StudyPack Ownership
        |--------------------------------------------------------------------------
        |
        | Company:
        |     created_by = company
        |
        | Branch:
        |     owned_by = current branch
        |
        */
        if ($isCompany) {

            $studypacksQuery->where(
                'created_by',
                $creatorId
            );

        } else {

            $studypacksQuery->where(
                'owned_by',
                $ownedId
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Branch Filter For StudyPack Challans
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | StudyPackChallans also uses `owned_by`.
        |
        */
        if (
            $selectedBranch !== null &&
            $selectedBranch !== ''
        ) {

            $studypacksQuery->where(
                'owned_by',
                $selectedBranch
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {

            $search = $request->search;

            $studypacksQuery->where(function ($q) use ($search) {

                $q->where(
                    'challanNo',
                    'like',
                    '%' . $search . '%'
                )

                    ->orWhere(
                        'status',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhere(
                        'fee_month',
                        'like',
                        '%' . $search . '%'
                    )

                    ->orWhereHas(
                        'student',
                        function ($s) use ($search) {

                            $s->where(
                                'stdname',
                                'like',
                                '%' . $search . '%'
                            )

                                ->orWhere(
                                    'roll_no',
                                    'like',
                                    '%' . $search . '%'
                                );
                        }
                    );
            });
        }


        /*
        |--------------------------------------------------------------------------
        | Class Filter
        |--------------------------------------------------------------------------
        |
        | When a class is selected, make sure the class
        | belongs to the selected branch through `owned_by`.
        |
        */
        if ($request->filled('class') && $request->class != 'all') {

            /*
             * Main StudyPack class filter.
             */
            $studypacksQuery->where(
                'class_id',
                $request->class
            );


            /*
             * Extra security:
             *
             * Verify the selected class belongs to
             * the currently selected branch.
             */
            if (
                $selectedBranch !== null &&
                $selectedBranch !== ''
            ) {

                $studypacksQuery->whereHas(
                    'class',
                    function ($q) use ($selectedBranch) {

                        $q->where(
                            'owned_by',
                            $selectedBranch
                        );
                    }
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Month Filter
        |--------------------------------------------------------------------------
        */
        if ($request->filled('month')) {

            $monthTimestamp = strtotime(
                $request->month
            );

            if ($monthTimestamp !== false) {

                $studypacksQuery
                    ->whereMonth(
                        'fee_month',
                        date(
                            'm',
                            $monthTimestamp
                        )
                    )
                    ->whereYear(
                        'fee_month',
                        date(
                            'Y',
                            $monthTimestamp
                        )
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Fetch StudyPack Challans
        |--------------------------------------------------------------------------
        */
        $studypacks = $studypacksQuery
            ->orderBy(
                'id',
                'desc'
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Other Variables
        |--------------------------------------------------------------------------
        */
        $stdy_pack = [];


        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */
        return view(
            'students.studypackChallan.index',
            compact(
                'branches',
                'session',
                'class',
                'stdy_pack',
                'studypacks',
                'selectedBranch'
            )
        );
    }


    public function create()
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('is_active', 1)->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $selectedBranch = '';
        } else {
            $selectedBranch = \Auth::user()->ownedId();
            $branches = User::where('id', '=', $selectedBranch)->where('is_active', 1)->get()->pluck('name', 'id');
        }
        $session = Session::get()->pluck('year', 'id');
        $session->prepend('Select Session', '');
        $classQuery = Classes::where('created_by', \Auth::user()->creatorId())
            ->where('active_status', 1);
        if (\Auth::user()->type !== 'company') {
            $classQuery->where('owned_by', $selectedBranch);
        }
        $class = $classQuery->pluck('name', 'id');
        $class->prepend('Select Class', '');
        $stdy_pack = [];
        return view('students.studypackChallan.challanform', compact('branches', 'session', 'class', 'stdy_pack', 'selectedBranch'));
    }
    public function store(Request $request)
{
    \DB::beginTransaction();

    try {

        /*
        |--------------------------------------------------------------------------
        | Logged In User
        |--------------------------------------------------------------------------
        */

        $user = \Auth::user();
        $creatorId = $user->creatorId();

        /*
        |--------------------------------------------------------------------------
        | Dates
        |--------------------------------------------------------------------------
        */

        $feeMonth = Carbon::parse(
            $request->fee_month ??
            $request->challan_date ??
            now()
        )->startOfMonth();

        $issueDate = Carbon::parse(
            $request->issue_date ?? now()
        )->toDateString();

        $dueDate = Carbon::parse(
            $request->due_date ?? now()->addWeek()
        )->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Selected StudyPack
        |--------------------------------------------------------------------------
        |
        | Supports both:
        |
        | name="Studypack"
        | name="studypack"
        |
        */

        $selectedStudyPackId = $request->input(
            'Studypack',
            $request->input('studypack')
        );

        if (
            empty($selectedStudyPackId) ||
            $selectedStudyPackId === 'all'
        ) {
            $selectedStudyPackId = null;
        } else {
            $selectedStudyPackId = (int) $selectedStudyPackId;
        }

        /*
        |--------------------------------------------------------------------------
        | Counters
        |--------------------------------------------------------------------------
        */

        $createdCount = 0;
        $existingCount = 0;

        $missingStudyPackClasses = [];
        $missingStudentsClasses = [];
        $missingStudyPackItems = [];

        /*
        |--------------------------------------------------------------------------
        | Determine Allowed Branch IDs
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Branch ownership is ONLY based on owned_by.
        |
        | We do NOT use:
        |
        | StudentRegistration.branch
        | StudyPack.branch_id
        | Classes.branch
        |
        */

        $branchIds = [];

        /*
        |--------------------------------------------------------------------------
        | Company Login
        |--------------------------------------------------------------------------
        */

        if ($user->type === 'company') {

            /*
             * Get branches belonging to this company.
             */
            $allowedBranchIds = User::where('type', 'branch')
                ->where('is_active', 1)
                ->where('created_by', $creatorId)
                ->pluck('id')
                ->map(function ($id) {
                    return (int) $id;
                })
                ->toArray();

            /*
             * Specific branch selected
             */
            if (
                $request->filled('branches') &&
                $request->branches !== 'all'
            ) {

                $requestedBranchId = (int) $request->branches;

                /*
                 * Make sure company is not trying to access
                 * another company's branch.
                 */
                if (
                    !in_array(
                        $requestedBranchId,
                        $allowedBranchIds,
                        true
                    )
                ) {
                    throw new \Exception(
                        'Selected branch is not allowed for this company.'
                    );
                }

                $branchIds = [
                    $requestedBranchId
                ];

            } else {

                /*
                 * All branches
                 */
                $branchIds = $allowedBranchIds;
            }

        }

        /*
        |--------------------------------------------------------------------------
        | Branch Login
        |--------------------------------------------------------------------------
        */

        elseif ($user->type === 'branch') {

            /*
             * Branch's own user ID is treated as owned_by.
             */
            $branchIds = [
                (int) $user->id
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Other User Types
        |--------------------------------------------------------------------------
        */

        else {

            $ownedId = (int) $user->ownedId();

            if ($ownedId > 0) {
                $branchIds = [
                    $ownedId
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Clean Branch IDs
        |--------------------------------------------------------------------------
        */

        $branchIds = array_values(
            array_unique(
                array_filter($branchIds)
            )
        );

        if (empty($branchIds)) {

            throw new \Exception(
                'No valid branch was found for StudyPack challan generation.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Selected Class
        |--------------------------------------------------------------------------
        */

        $selectedClassId = null;

        if (
            $request->filled('class') &&
            $request->class !== 'all'
        ) {

            $selectedClassId = (int) $request->class;
        }

        /*
        |--------------------------------------------------------------------------
        | Important Validation
        |--------------------------------------------------------------------------
        |
        | If StudyPack is manually selected, a specific class should also
        | be selected.
        |
        | Otherwise one StudyPack could accidentally be applied to
        | multiple classes.
        |
        */

        if (
            $selectedStudyPackId &&
            !$selectedClassId
        ) {

            \DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Please select a specific class when selecting a StudyPack.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Get Classes
        |--------------------------------------------------------------------------
        |
        | Branch filtering ONLY with owned_by.
        |
        */

        $classQuery = Classes::query()
            ->where('created_by', $creatorId)
            ->where('active_status', 1)
            ->whereIn('owned_by', $branchIds);

        /*
         * Specific class
         */
        if ($selectedClassId) {

            $classQuery->where(
                'id',
                $selectedClassId
            );
        }

        $classIds = $classQuery
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | No Classes
        |--------------------------------------------------------------------------
        */

        if (empty($classIds)) {

            \DB::rollBack();

            return back()
                ->withInput()
                ->with(
                    'error',
                    'No active class was found for the selected branch.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Process Classes
        |--------------------------------------------------------------------------
        */

        foreach ($classIds as $classId) {

            /*
            |--------------------------------------------------------------------------
            | Get StudyPack
            |--------------------------------------------------------------------------
            */

            $studyPack = null;

            /*
            |--------------------------------------------------------------------------
            | Manually Selected StudyPack
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | When user manually selects StudyPack we DO NOT apply:
            |
            | session_id filter
            | class JSON filter
            |
            | We trust selected StudyPack ID.
            |
            | We only verify owned_by for security.
            |
            */

            if ($selectedStudyPackId) {

                $studyPack = StudyPack::query()
                    ->where(
                        'id',
                        $selectedStudyPackId
                    )
                    // ->whereIn(
                    //     'owned_by',
                    //     $branchIds
                    // )
                    ->first();
            }

            /*
            |--------------------------------------------------------------------------
            | Automatically Find StudyPack
            |--------------------------------------------------------------------------
            |
            | Only used when StudyPack was NOT manually selected.
            |
            */

            else {

                $studyPackQuery = StudyPack::query()
                    // ->whereIn(
                    //     'owned_by',
                    //     $branchIds
                    // )
                    ->where(
                        'session_id',
                        $request->session
                    );

                /*
                 * Handle JSON class values stored as:
                 *
                 * [1,2,3]
                 *
                 * OR:
                 *
                 * ["1","2","3"]
                 */
                $studyPackQuery->where(
                    function ($query) use ($classId) {

                        $query
                            ->whereJsonContains(
                                'class',
                                (int) $classId
                            )
                            ->orWhereJsonContains(
                                'class',
                                (string) $classId
                            );
                    }
                );

                $studyPack = $studyPackQuery->first();
            }

            /*
            |--------------------------------------------------------------------------
            | StudyPack Not Found
            |--------------------------------------------------------------------------
            */

            if (!$studyPack) {

                $missingStudyPackClasses[] = $classId;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Get StudyPack Items
            |--------------------------------------------------------------------------
            */

            $studyPackItems = StudyPackItem::where(
                'study_pack_id',
                $studyPack->id
            )->get();

            /*
             * Don't create empty challan.
             */
            if ($studyPackItems->isEmpty()) {

                $missingStudyPackItems[] = $classId;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Get Students
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | ONLY owned_by is used for branch filtering.
            |
            */

            $studentQuery = StudentRegistration::query()
                ->whereIn(
                    'owned_by',
                    $branchIds
                )
                ->where(
                    'class_id',
                    $classId
                )
                ->where(
                    'active_status',
                    1
                )
                ->where(
                    'student_status',
                    'Enrolled'
                );

            /*
             * Specific student
             */
            if (
                $request->filled('student') &&
                $request->student !== 'all'
            ) {

                $studentQuery->where(
                    'id',
                    (int) $request->student
                );
            }

            /*
             * Get complete student objects.
             */
            $students = $studentQuery->get();

            /*
            |--------------------------------------------------------------------------
            | No Students
            |--------------------------------------------------------------------------
            */

            if ($students->isEmpty()) {

                $missingStudentsClasses[] = $classId;

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Process Students
            |--------------------------------------------------------------------------
            */

            foreach ($students as $studentdata) {

                /*
                |--------------------------------------------------------------------------
                | Check Existing Challan
                |--------------------------------------------------------------------------
                |
                | IMPORTANT:
                |
                | Existing challan is completely skipped.
                |
                */

                $existingChallan = StudyPackChallans::query()
                    ->where(
                        'student_id',
                        $studentdata->id
                    )
                    ->where(
                        'class_id',
                        $classId
                    )
                    ->where(
                        'challan_type',
                        'Studypack'
                    )
                    ->whereDate(
                        'fee_month',
                        $feeMonth->toDateString()
                    )
                    ->where(
                        'owned_by',
                        $studentdata->owned_by
                    )
                    ->first();

                if ($existingChallan) {

                    $existingCount++;

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | Student Enrollment / Section
                |--------------------------------------------------------------------------
                */

                $sectionId = null;

                $enrollment = StudentEnrollments::where(
                    'regId',
                    $studentdata->id
                )->first();

                if ($enrollment) {

                    $sectionId = $enrollment->section_id ?? null;
                }

                /*
                |--------------------------------------------------------------------------
                | Create StudyPack Challan
                |--------------------------------------------------------------------------
                */

                $studypackchallan = new StudyPackChallans();

                $studypackchallan->student_id =
                    $studentdata->id;

                $studypackchallan->studypack_id =
                    $studyPack->id;

                $studypackchallan->challanNo =
                    $this->challanNo();

                $studypackchallan->fee_month =
                    $feeMonth->toDateString();

                /*
                 * branch_id intentionally NOT used.
                 *
                 * owned_by is the source of truth.
                 */

                $studypackchallan->class_id =
                    $classId;

                $studypackchallan->section_id =
                    $sectionId;

                $studypackchallan->session_id =
                    $request->session;

                $studypackchallan->challan_type =
                    'Studypack';

                $studypackchallan->year =
                    $feeMonth->toDateString();

                $studypackchallan->challan_date =
                    $feeMonth->toDateString();

                $studypackchallan->issue_date =
                    $issueDate;

                $studypackchallan->due_date =
                    $dueDate;

                $studypackchallan->status =
                    'Assigned';

                /*
                 * Branch ownership
                 */
                $studypackchallan->owned_by =
                    $studentdata->owned_by;

                $studypackchallan->created_by =
                    $studentdata->created_by;

                $studypackchallan->save();

                /*
                |--------------------------------------------------------------------------
                | Challan Items
                |--------------------------------------------------------------------------
                */

                $newItems = [];

                $totalAmount = 0;

                foreach ($studyPackItems as $item) {

                    $challanItem =
                        new StudyPackChallanItems();

                    $challanItem->challan_id =
                        $studypackchallan->id;

                    $challanItem->studypack_id =
                        $studyPack->id;

                    $challanItem->product_id =
                        $item->product_id;

                    $challanItem->qty =
                        $item->quantity;

                    $challanItem->tax =
                        $item->tax;

                    $challanItem->discount =
                        $item->discount;

                    $challanItem->price =
                        $item->price;

                    $challanItem->owned_by =
                        $studentdata->owned_by;

                    $challanItem->created_by =
                        $studentdata->created_by;

                    $challanItem->save();

                    /*
                     * Calculate total.
                     *
                     * Keeping same calculation as your original method.
                     */
                    $totalAmount +=
                        (float) $item->price *
                        (float) $item->quantity;

                    /*
                     * Data required by Utility::studypackjv()
                     */
                    $newItems[] = [

                        'prod_id' =>
                            $challanItem->id,

                        'product_id' =>
                            $item->product_id,

                        'quantity' =>
                            $item->quantity,

                        'price' =>
                            $item->price,

                        'discount' =>
                            $item->discount,
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | StudyPack JV
                |--------------------------------------------------------------------------
                */

                $data = [];

                $data['id'] =
                    $studypackchallan->id;

                $data['no'] =
                    $studypackchallan->studypack_id;

                $data['user_id'] =
                    $studypackchallan->student_id;

                $data['date'] =
                    $studypackchallan->fee_month;

                $data['reference'] =
                    $studypackchallan->fee_month;

                $data['category'] =
                    'Studypack';

                $data['owned_by'] =
                    $studypackchallan->owned_by;

                $data['created_by'] =
                    $studypackchallan->created_by;

                $data['items'] =
                    $newItems;

                /*
                 * Create Journal Voucher
                 */
                $voucherId =
                    Utility::studypackjv($data);

                /*
                |--------------------------------------------------------------------------
                | Update Challan
                |--------------------------------------------------------------------------
                */

                $studypackchallan->update([

                    'total_amount' =>
                        $totalAmount,

                    'voucher_id' =>
                        $voucherId,
                ]);

                /*
                 * Successfully created.
                 */
                $createdCount++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Commit Transaction
        |--------------------------------------------------------------------------
        */

        \DB::commit();

        /*
        |--------------------------------------------------------------------------
        | Nothing Created
        |--------------------------------------------------------------------------
        */

        if ($createdCount === 0) {

            $message =
                'No new StudyPack Challan was created.';

            /*
             * Existing challans
             */
            if ($existingCount > 0) {

                $message .=
                    ' Existing challans: ' .
                    $existingCount .
                    '.';
            }

            /*
             * StudyPack missing
             */
            if (
                !empty(
                    $missingStudyPackClasses
                )
            ) {

                $message .=
                    ' StudyPack not found for class IDs: ' .
                    implode(
                        ', ',
                        array_unique(
                            $missingStudyPackClasses
                        )
                    ) .
                    '.';

                /*
                 * If user actually selected a StudyPack,
                 * provide more useful information.
                 */
                if ($selectedStudyPackId) {

                    $message .=
                        ' Selected StudyPack ID: ' .
                        $selectedStudyPackId .
                        '. Please verify its owned_by matches the logged-in branch.';
                }
            }

            /*
             * No students
             */
            if (
                !empty(
                    $missingStudentsClasses
                )
            ) {

                $message .=
                    ' No enrolled students found for class IDs: ' .
                    implode(
                        ', ',
                        array_unique(
                            $missingStudentsClasses
                        )
                    ) .
                    '.';
            }

            /*
             * StudyPack contains no items
             */
            if (
                !empty(
                    $missingStudyPackItems
                )
            ) {

                $message .=
                    ' StudyPack has no items for class IDs: ' .
                    implode(
                        ', ',
                        array_unique(
                            $missingStudyPackItems
                        )
                    ) .
                    '.';
            }

            return back()
                ->withInput()
                ->with(
                    'error',
                    $message
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Success Message
        |--------------------------------------------------------------------------
        */

        $message =
            $createdCount .
            ' StudyPack Challan(s) Created Successfully.';

        /*
         * Existing challans skipped
         */
        if ($existingCount > 0) {

            $message .=
                ' ' .
                $existingCount .
                ' existing challan(s) skipped.';
        }

        /*
         * Classes without StudyPack
         */
        if (
            !empty(
                $missingStudyPackClasses
            )
        ) {

            $message .=
                ' StudyPack missing for class IDs: ' .
                implode(
                    ', ',
                    array_unique(
                        $missingStudyPackClasses
                    )
                ) .
                '.';
        }

        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return back()->with(
            'success',
            $message
        );

    } catch (\Exception $e) {

        /*
        |--------------------------------------------------------------------------
        | Rollback
        |--------------------------------------------------------------------------
        */

        \DB::rollBack();

        /*
        |--------------------------------------------------------------------------
        | Log Error
        |--------------------------------------------------------------------------
        */

        \Log::error(
            'StudyPack Challan Creation Failed',
            [
                'message' =>
                    $e->getMessage(),

                'file' =>
                    $e->getFile(),

                'line' =>
                    $e->getLine(),

                'user_id' =>
                    \Auth::id(),

                'request' =>
                    $request->except([
                        '_token'
                    ]),
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Return Error
        |--------------------------------------------------------------------------
        */

        return back()
            ->withInput()
            ->with(
                'error',
                'StudyPack Challan creation failed: ' .
                $e->getMessage()
            );
    }
}

    public function challanNo()
    {
        $latest = StudyPackChallans::orderByRaw('CAST(challanNo AS UNSIGNED) DESC')
            ->first();

        if (!$latest || !$latest->challanNo) {
            return 1;
        }

        return (int) $latest->challanNo + 1;
    }
    public function download($id)
    {
        try {
            $challan = StudyPackChallans::findOrFail($id);
            $pathToFile = public_path('temp_pdfs/studypack_challan.pdf');
            return response()->download($pathToFile);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to download challan: ' . $e->getMessage());
        }
    }

    public function print($id)
    {

        try {
            $challan = StudyPackChallans::findOrFail($id);
            $html = view('students.studypackChallan.print', compact('challan'))->render();
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A3', 'landscape');
            $dompdf->render();

            return $dompdf->stream('studypack_challan.pdf', ['Attachment' => false]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Failed to generate print view: ' . $e->getMessage());
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        // dd($request->all());
        $challan = StudyPackChallans::with('student')->where('id', $id)->first();
        if (!$challan) {
            abort(404, 'Challan not found');
        }
        $items = $challan->items ?? collect();
        $previousUnpaidChallans = StudyPackChallans::with('items.product', 'items')
            ->where('student_id', $challan->student_id)
            ->where('id', '!=', $challan->id)
            ->where('status', '!=', 'Paid')
            ->orderBy('id', 'desc')
            ->get();

        // If type is set, generate PDF using Dompdf - now using challanpdf.blade.php
        if ($request->has('type') && in_array($request->type, ['print', 'download'])) {
            $data = [
                'challan' => $challan,
                'items' => $items,
                'previousUnpaidChallans' => $previousUnpaidChallans,
            ];
            $html = view('students.studypackChallan.challanpdf', $data)->render();
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $pdfContent = $dompdf->output();
            $filename = 'studypack_challan.pdf';
            if ($request->type === 'print') {
                return $dompdf->stream($filename, ['Attachment' => false]);
            } else {
                return $dompdf->stream($filename);
            }
        }
        // dd($challan);
        return view('students.studypackChallan.show', compact('challan'));
    }

    /**
     * Display the booklist for the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function booklist($id, Request $request)
    {
        $challan = StudyPackChallans::with('student', 'items.product', 'session')->where('id', $id)->first();
        if (!$challan) {
            abort(404, 'Challan not found');
        }
        // If type is set, generate PDF using Dompdf - using print.blade.php (booklist)
        if ($request->has('type') && in_array($request->type, ['print', 'download'])) {
            $html = view('students.studypackChallan.print', compact('challan'))->render();
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $pdfContent = $dompdf->output();
            $filename = 'studypack_booklist.pdf';
            if ($request->type === 'print') {
                return $dompdf->stream($filename, ['Attachment' => false]);
            } else {
                return $dompdf->stream($filename);
            }
        }
        return view('students.studypackChallan.booklist', compact('challan'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $purchase = StudyPackChallans::find($id);
        // dd($purchase);
        $challan = StudyPackChallans::with('student')->where('id', $id)->first();
        $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
            ->where('created_by', \Auth::user()->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');
        return view('students.studypackChallan.edit', compact('product_services', 'purchase'));
        // return view('students.studypackChallan.edit', compact('challan', 'invoice', 'items'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        \DB::beginTransaction();
        try {
            $challan = StudyPackChallans::findOrFail($id);

            if (!empty($request->deleted_items)) {
                foreach ($request->deleted_items as $item) {
                    // Delete challan item
                    StudyPackChallanItems::where('id', $item)->delete();
                    // Also delete corresponding JournalItem
                    JournalItem::where('entry_id', $item)->delete();
                }
            }

            $allChallanItems = [];
            if (!empty($request->items)) {
                foreach ($request->items as $item) {
                    if (isset($item['id']) && $item['id']) {
                        // Update existing item
                        $challanitem = StudyPackChallanItems::find($item['id']);
                        if ($challanitem) {
                            $challanitem->qty = $item['quantity'];
                            $challanitem->price = $item['price'];
                            $challanitem->save();
                            // Update corresponding JournalItem (credit, description, etc.)
                            $journalItem = JournalItem::where('entry_id', $challanitem->id)->where('types', 'Studypack')->first();
                            if ($journalItem) {
                                $product = ProductService::find($challanitem->product_id);
                                $itemPrice = $challanitem->qty * $challanitem->price;
                                $journalItem->credit = $itemPrice;
                                $journalItem->description = $product ? $product->name : $journalItem->description;
                                $journalItem->save();
                                // Update tax JournalItem if exists
                                if ($product && $product->tax_id) {
                                    $taxes = \App\Models\Tax::where('id', $product->tax_id)->first();
                                    if ($taxes) {
                                        $itemTax = ($itemPrice * $taxes->rate) / 100;
                                        $taxJournalItem = JournalItem::where('journal', $journalItem->journal)
                                            ->where('types', 'Studypack')
                                            ->where('description', 'Tax on ' . $product->id)
                                            ->where('head_ids', $product->id)
                                            ->first();
                                        if ($taxJournalItem) {
                                            $taxJournalItem->credit = $itemTax;
                                            $taxJournalItem->save();
                                        }
                                    }
                                }
                            }
                            $allChallanItems[] = [
                                'prod_id' => $challanitem->id,
                                'product_id' => $challanitem->product_id,
                                'quantity' => $challanitem->qty,
                                'price' => $challanitem->price
                            ];
                        }
                    } else {
                        // Add new item
                        $challanitem = new StudyPackChallanItems();
                        $challanitem->challan_id = $challan->id;
                        $challanitem->studypack_id = $challan->studypack_id;
                        $challanitem->product_id = $item['item'];
                        $challanitem->qty = $item['quantity'];
                        $challanitem->price = $item['price'];
                        $challanitem->owned_by = $challan->owned_by;
                        $challanitem->created_by = $challan->created_by;
                        $challanitem->save();
                        $allChallanItems[] = [
                            'prod_id' => $challanitem->id,
                            'product_id' => $challanitem->product_id,
                            'quantity' => $challanitem->qty,
                            'price' => $challanitem->price
                        ];
                    }
                }
            }
            $challan->total_amount = $request->total_amount;
            $challan->save();

            // Update or create JV for all items
            $journal = JournalEntry::where('reference_id', $challan->id)
                ->where('voucher_type', 'JV')
                ->first();
            if ($journal) {
                // Update JV header info
                $journal->date = $challan->fee_month;
                $journal->reference = $challan->fee_month;
                $journal->description = 'Studypack no : ' . $challan->studypack_id;
                $journal->save();

                // Remove all receivable and tax JournalItems (to recalculate)
                JournalItem::where('journal', $journal->id)
                    ->where(function ($q) {
                        $q->Where('description', 'like', '% Studypack Receivables %');
                    })->delete();

                $receivable = 0;
                $totalTax = 0;
                foreach ($allChallanItems as $item) {
                    $product = ProductService::where('id', $item['product_id'])->first();
                    if (!$product)
                        continue;
                    $itemPrice = ($item['quantity'] * $item['price']);
                    $receivable += $itemPrice;
                    $journalItem = JournalItem::where('journal', $journal->id)
                        ->where('entry_id', $item['prod_id'])
                        ->where('types', 'Studypack')
                        ->first();
                    if (!$journalItem) {
                        $journalItem = new JournalItem();
                        $journalItem->journal = $journal->id;
                        $journalItem->account = @$product->sale_chartaccount_id;
                        $journalItem->entry_id = @$item['prod_id'];
                        $journalItem->types = 'Studypack';
                        $journalItem->description = $product->name;
                        $journalItem->head_ids = $product->id;
                        $journalItem->branch_id = $challan->owned_by;
                        $journalItem->debit = 0;
                        $journalItem->credit = $itemPrice;
                        $journalItem->save();
                    }

                }
                $types = \App\Models\ChartOfAccountType::where('created_by', $challan->created_by)
                    ->where('name', 'Assets')
                    ->first();
                if ($types) {
                    $sub_type = \App\Models\ChartOfAccountSubType::where('type', $types->id)
                        ->where('name', 'Current Asset')
                        ->first();
                    $account = \App\Models\ChartOfAccount::where('type', $types->id)
                        ->where('sub_type', $sub_type->id)
                        ->where('name', 'Studypack Receivables')
                        ->first();
                    if (!$account) {
                        $account = new \App\Models\ChartOfAccount();
                        $account->name = 'Studypack Receivables';
                        $account->code = '0';
                        $account->type = $types->id;
                        $account->sub_type = $sub_type->id;
                        $account->description = 'Studypack Receivables';
                        $account->is_enabled = 1;
                        $account->created_by = $challan->created_by;
                        $account->save();
                    }
                    // Add receivable journal item
                    $journalItem = new JournalItem();
                    $journalItem->journal = $journal->id;
                    $journalItem->account = @$account->id;
                    $journalItem->description = 'Account Receivable: Roll no ' . $challan->roll_no . ' Challan no ' . $challan->challanNo . ' - ' . @$challan->student->stdname . ' - ' . @$challan->fee_month . ' - ' . @$challan->student->branches->name;
                    $journalItem->debit = $receivable + $totalTax;
                    $journalItem->branch_id = $challan->owned_by;
                    $journalItem->save();
                }
            } else {
                // If no JV exists, create as before
                $data['id'] = $challan->id;
                $data['no'] = $challan->studypack_id;
                $data['date'] = $challan->fee_month;
                $data['reference'] = $challan->fee_month;
                $data['category'] = 'Studypack';
                $data['owned_by'] = $challan->owned_by;
                $data['created_by'] = $challan->created_by;
                $data['items'] = $allChallanItems;
                $dataret = Utility::studypackjv($data);
            }

            \DB::commit();
            return redirect()->route('studypackchallan.index')->with('success', 'Challan updated successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            dd($e); // Only for debugging. Remove this in production.
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function rollback(Request $request)
    {
        try {
            $rows = $request->input('rows', []);
            if (!is_array($rows)) {
                $rows = json_decode($rows, true) ?: [];
            }

            if (empty($rows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No StudyPack challans selected.'
                ]);
            }

            $challans = StudyPackChallans::whereIn('id', $rows)->get();
            if ($challans->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected StudyPack challans not found.'
                ]);
            }

            \Illuminate\Support\Facades\DB::beginTransaction();
            $deleted = 0;

            foreach ($challans as $challan) {
                if ($challan->status != "Assigned") {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only StudyPack challans with status "Assigned" can be rolled back.'
                    ]);
                }
                if (!empty($challan->voucher_id)) {
                    JournalItem::where('journal', $challan->voucher_id)->delete();
                    JournalEntry::where('id', $challan->voucher_id)->delete();
                }

                StudyPackChallanItems::where('challan_id', $challan->id)->delete();
                $challan->delete();
                $deleted++;
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'success' => true,
                'message' => $deleted . ' StudyPack challan(s) rolled back successfully.'
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function printChallans(Request $request)
    {
        try {
            $challanIds = $request->input('rowsdata', []);
            $printType = $request->input('printType', 'single');
            $documentType = $request->input('documentType', 'challan');
            if (!is_array($challanIds)) {
                $challanIds = json_decode($challanIds, true) ?: [];
            }

            if (empty($challanIds)) {
                return response()->json(['error' => 'No StudyPack challans selected.'], 400);
            }

            $maxChallans = 1500;
            if (count($challanIds) > $maxChallans) {
                return response()->json([
                    'error' => 'Maximum ' . $maxChallans . ' challans can be printed at once. You selected ' . count($challanIds) . '.'
                ], 400);
            }

            $batchSize = $this->resolveBatchSize((int) $request->input('batchSize', 10));
            $batchIndex = (int) $request->input('batchIndex', 0);

            if ($printType === 'single') {
                $pdfContentsArray = [];

                foreach ($challanIds as $challanId) {
                    $challan = StudyPackChallans::with('student', 'items.product', 'items')->find($challanId);
                    if (!$challan) {
                        continue;
                    }

                    $items = $challan->items ?? collect();
                    $previousUnpaidChallans = StudyPackChallans::with('items.product', 'items')
                        ->where('student_id', $challan->student_id)
                        ->where('id', '!=', $challan->id)
                        ->where('status', '!=', 'Paid')
                        ->orderBy('id', 'desc')
                        ->get();

                    $pdfContentsArray[] = $this->generateStudyPackPDF([
                        'challan' => $challan,
                        'items' => $items,
                        'previousUnpaidChallans' => $previousUnpaidChallans,
                    ], $documentType);
                }

                $mergedPdfContent = $this->mergeStudyPackPdfs($pdfContentsArray, $documentType);

                return response()->json([
                    'pdfs' => [base64_encode($mergedPdfContent)],
                    'processedCount' => count($challanIds),
                    'totalCount' => count($challanIds),
                    'hasMoreBatches' => false,
                    'printType' => 'single',
                    'message' => 'Processing ' . count($challanIds) . '/' . count($challanIds) . ' study pack challans...'
                ]);
            }

            $totalBatches = ceil(count($challanIds) / $batchSize);
            $startIndex = $batchIndex * $batchSize;
            $endIndex = min($startIndex + $batchSize, count($challanIds));
            $currentBatchIds = array_slice($challanIds, $startIndex, $batchSize);

            $pdfContentsArray = [];
            foreach ($currentBatchIds as $challanId) {
                $challan = StudyPackChallans::with('student', 'items.product', 'items')->find($challanId);
                if (!$challan) {
                    continue;
                }

                $items = $challan->items ?? collect();
                $previousUnpaidChallans = StudyPackChallans::with('items.product', 'items')
                    ->where('student_id', $challan->student_id)
                    ->where('id', '!=', $challan->id)
                    ->where('status', '!=', 'Paid')
                    ->orderBy('id', 'desc')
                    ->get();
                // dd($documentType);
                $pdfContentsArray[] = $this->generateStudyPackPDF([
                    'challan' => $challan,
                    'items' => $items,
                    'previousUnpaidChallans' => $previousUnpaidChallans,
                ], $documentType);
            }

            $hasMoreBatches = ($batchIndex < $totalBatches - 1);
            return response()->json([
                'pdfs' => array_map('base64_encode', $pdfContentsArray),
                'batchIndex' => $batchIndex,
                'totalBatches' => $totalBatches,
                'processedCount' => $endIndex,
                'totalCount' => count($challanIds),
                'hasMoreBatches' => $hasMoreBatches,
                'printType' => 'separate',
                'message' => 'Downloading ' . $endIndex . '/' . count($challanIds) . ' study pack challans...'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    private function resolveBatchSize(int $batchSize): int
    {
        return max(10, $batchSize);
    }

    private function generateStudyPackPDF(array $data, string $documentType = 'challan')
    {
        $view = $documentType === 'challan'
            ? 'students.studypackChallan.challanpdf'
            : 'students.studypackChallan.print';

        $html = view($view, $data)->render();

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);

        if ($documentType == 'sp') {
            $dompdf->setPaper('A4', 'landscape');
        } else {
            $dompdf->setPaper('A3', 'landscape');
        }

        $dompdf->render();

        return $dompdf->output();
    }

    private function mergeStudyPackPdfs(array $pdfContentsArray, string $documentType = 'challan')
    {
        $pdf = new Fpdi();

        if ($documentType === 'sp') {
            $orientation = 'P';
            $customWidth = 595.28;
            $customHeight = 841.89;
        } else {
            $orientation = 'L';
            $customWidth = 1190.89;
            $customHeight = 841.89;
        }

        foreach ($pdfContentsArray as $pdfContent) {
            try {
                $pageCount = $pdf->setSourceFile(StreamReader::createByString($pdfContent));

                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $pdf->AddPage($orientation, [$customWidth, $customHeight]);
                    $pdf->useTemplate($templateId, 0, 0, $customWidth, $customHeight);
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $pdf->output('', 'S');
    }

    public function deleteChallanItems(Request $request)
    {
        $challanId = $request->input('challan_id');
        $productIds = $request->input('product_ids');
        StudyPackChallanItems::where('challan_id', $challanId)
            ->whereIn('product_id', $productIds)
            ->delete();
        return response()->json(['success' => true]);
    }

    public function payment(Request $request, $id)
    {
        if (\Auth::user()->can('create payment invoice')) {
            $invoice = StudyPackChallans::where('id', $id)->first();
            if (\Auth::user()->type == 'company') {
                $customers = Customer::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $categories = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            } else {
                $customers = Customer::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $categories = ProductServiceCategory::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }
            // dd($invoice);
            return view('students.studypackChallan.payment', compact('customers', 'categories', 'accounts', 'invoice'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function addpayment(Request $request, $id)
    {
        if (\Auth::user()->can('create payment invoice')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required',
                    'amount' => 'required',
                    'account_from' => 'required',
                    'account_to' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $invoice = StudyPackChallans::where('id', $id)->first();
            DB::beginTransaction();
            $data = [];
            try {
                $invoicePayment = new StudypackPayment();
                $invoicePayment->challan_id = $id;
                $invoicePayment->date = $request->date;
                $invoicePayment->amount = $request->amount;
                $invoicePayment->payment_method = 0;
                $invoicePayment->reference = $request->reference;
                $invoicePayment->description = $request->description;
                $invoicePayment->owned_by = $invoice->owned_by;
                $invoicePayment->created_by = $invoice->created_by;

                if (!empty($request->add_receipt)) {
                    //storage limit
                    $image_size = $request->file('add_receipt')->getSize();
                    $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
                    if ($result == 1) {
                        $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                        $request->add_receipt->storeAs('uploads/payment', $fileName);
                        $invoicePayment->add_receipt = $fileName;
                    }
                }

                $invoicePayment->save();
                Transaction::addTransaction($invoicePayment);
                Utility::bankAccountBalance($request->account_id, $request->amount, 'credit');

                $bankAccount = BankAccount::find($request->account_id);
                $data['id'] = $id;
                $data['date'] = $invoicePayment->date;
                $data['reference'] = $invoicePayment->reference;
                $data['description'] = $invoicePayment->description;
                $data['amount'] = $invoicePayment->amount;
                $data['category'] = 'Studypack';
                $data['user_id'] = $invoice->student_id;
                $data['user_type'] = 'Student';
                $data['owned_by'] = $invoice->owned_by;
                $data['created_by'] = $invoice->created_by;
                $data['account_id'] = $bankAccount->id;
                $dataret = Utility::strv_entry($data);
                $invoicePayment->update(['voucher_id' => $dataret]);
                DB::commit();
                return redirect()->back()->with('success', __('Payment successfully added.'));
            } catch (\Exception $e) {
                DB::rollback();
                dd($e);
                return redirect()->back()->with('error', $e);
            }
        }
    }

    public function product(Request $request)
    {

        $data['product'] = $product = ProductService::find($request->product_id);
        $data['unit'] = !empty($product->unit()) ? $product->unit()->name : '';
        $data['taxRate'] = $taxRate = !empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0;
        $data['taxes'] = !empty($product->tax_id) ? $product->tax($product->tax_id) : 0;
        $salePrice = $product->purchase_price;
        $quantity = 1;
        $taxPrice = ($taxRate / 100) * ($salePrice * $quantity);
        $data['totalAmount'] = ($salePrice * $quantity);

        return json_encode($data);
    }

    public function items(Request $request)
    {
        $items = StudyPackChallanItems::where('challan_id', $request->purchase_id)->where('product_id', $request->product_id)->first();

        return json_encode($items);

    }

    public function dailyReceipts(Request $request)
    {
        return $this->renderReceiptsPage($request, true);
    }

    public function Studypackreceipts(Request $request)
    {
        return $this->renderReceiptsPage($request, false);
    }

    private function renderReceiptsPage(Request $request, bool $isDailyEntry = false)
    {
        if (\Auth::user()->type == 'company') {
            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                ->where('created_by', \Auth::user()->creatorId())
                ->get()
                ->pluck('name', 'id');
        } else {
            $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                ->where('owned_by', \Auth::user()->ownedId())
                ->get()
                ->pluck('name', 'id');
        }

        $defaultBankId = null;
        if (\Auth::user()->type != 'company') {
            $branchAccounts = BankAccount::where('owned_by', \Auth::user()->ownedId())->get();
            $cashAccount = $branchAccounts->first(function ($item) {
                return str_contains(strtolower($item->bank_name), 'cash')
                    || str_contains(strtolower($item->holder_name), 'cash')
                    || str_contains(strtolower($item->bank_name), 'csh')
                    || str_contains(strtolower($item->holder_name), 'csh');
            });
            $defaultBankId = $cashAccount ? $cashAccount->id : ($branchAccounts->first()->id ?? null);
        } else {
            $defaultBankId = $accounts->keys()->first() ?? null;
        }

        $query = StudypackReceipts::with('challan');

        if (\Auth::user()->type == 'company') {
            $query->where(function ($q) {
                $q->where('created_by', \Auth::user()->creatorId())
                    ->orWhere('received_by', \Auth::user()->id);
            });
        } else {
            $query->where(function ($q) {
                $q->where('owned_by', \Auth::user()->ownedId());
                // ->orWhere('received_by', \Auth::user()->id);
            });
        }

        if (!empty($request->date)) {
            $query->whereDate('recipt_date', $request->date);
        } else {
            $query->whereDate('recipt_date', date("Y-m-d"));
        }

        $recipts = $query->get();

        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(new StudentReceiptExport($recipts, $request->all()), 'student_receipt.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            return Excel::download(new StudentReceiptExport($recipts, $request->all()), 'student_receipt.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        return view('students.studypackChallan.receipts', compact('accounts', 'recipts', 'isDailyEntry', 'defaultBankId'));
    }
    public function challandata_for_studypackreceipt(Request $request)
    {
        try {
            $challandata = StudyPackChallans::with(['student', 'items', 'items.product'])->where('challanNo', $request->challan_no)->first();
            // dd($challandata);
            if (!$challandata) {
                return response()->json(['error' => 'Challan not found.'], 404);
            }

            if ($challandata && $challandata->items) {
                $headsData = [];
                foreach ($challandata->items as $head) {
                    // dd($head);
                    // Safely cast to float to prevent non-numeric issues
                    $price = (float) ($head->price ?? 0);
                    $concession = (float) ($head->discount ?? 0);
                    $amount = ($price * (int) ($head->qty ?? 1)) - $concession - $head->paid;

                    if ($amount != 0) {
                        $headsData[] = [
                            'head_id' => $head->id ?? '',
                            'head_name' => $head->product->name ?? 'Unknown Product ',
                            'amount' => $amount
                        ];
                    }
                }
            } else {
                echo 'No Items   found for this challan.';
            }


            $previousUnpaidChallans = StudyPackChallans::with('items.product', 'items')
                ->where('student_id', $challandata->student_id)
                ->where('status', '!=', 'Paid')
                ->where('id', '!=', $challandata->id)
                ->wheredate('fee_month', '<', date('Y-m-01', strtotime($challandata->fee_month)))
                ->get();

            $defaultBankId = null;
            if (Auth::user()->type == 'company') {
                $account_all = BankAccount::select('id', 'bank_name', 'holder_name', 'chart_account_id')
                    ->with('chartAccount:id,name')
                    ->where('created_by', \Auth::user()->creatorId())
                    ->get();

                $accounts = BankAccount::select('id', 'bank_name', 'holder_name', 'chart_account_id')
                    ->with('chartAccount:id,name')
                    ->where('owned_by', Auth::user()->ownedId())
                    ->get();
            } else {
                if ($challandata->owned_by != Auth::user()->ownedId()) {
                    $accounts = BankAccount::select('id', 'bank_name', 'holder_name', 'chart_account_id')
                        ->with('chartAccount:id,name')
                        ->where('owned_by', \Auth::user()->ownedId())
                        ->get();

                    $account_all = BankAccount::select('id', 'bank_name', 'holder_name', 'chart_account_id')
                        ->with('chartAccount:id,name')
                        ->where('created_by', \Auth::user()->creatorId())
                        ->get();
                } else {
                    $accounts = BankAccount::select('id', 'bank_name', 'holder_name', 'chart_account_id')
                        ->with('chartAccount:id,name')
                        ->where('owned_by', \Auth::user()->ownedId())
                        ->get();

                    $account_all = BankAccount::select('id', 'bank_name', 'holder_name', 'chart_account_id')
                        ->with('chartAccount:id,name')
                        ->where('created_by', \Auth::user()->creatorId())
                        ->get();
                }

                $branchAccounts = BankAccount::where('owned_by', \Auth::user()->ownedId())->get();
                $cashAccount = $branchAccounts->first(function ($item) {
                    return str_contains(strtolower($item->bank_name), 'cash')
                        || str_contains(strtolower($item->holder_name), 'cash')
                        || str_contains(strtolower($item->bank_name), 'csh')
                        || str_contains(strtolower($item->holder_name), 'csh');
                });
                $defaultBankId = $cashAccount ? $cashAccount->id : ($branchAccounts->first()->id ?? null);
            }

            $accountsFormatted = [];
            $accountsData = [];
            foreach ($accounts as $account) {
                $accountsFormatted[$account->id] = $account->bank_name . ' ' . $account->holder_name;
                $accountsData[$account->id] = [
                    'name' => $account->bank_name . ' ' . $account->holder_name,
                    'chart_account' => $account->chartAccount ? strtolower($account->chartAccount->name) : '',
                ];
            }

            $accountAllFormatted = [];
            $accountAllData = [];
            foreach ($account_all as $account) {
                $accountAllFormatted[$account->id] = $account->bank_name . ' ' . $account->holder_name;
                $accountAllData[$account->id] = [
                    'name' => $account->bank_name . ' ' . $account->holder_name,
                    'chart_account' => $account->chartAccount ? strtolower($account->chartAccount->name) : '',
                ];
            }

            return response()->json([
                'challandetail' => $challandata,
                'previousUnpaidChallans' => $previousUnpaidChallans,
                'headsData' => $headsData,
                'accounts' => $accountsFormatted,
                'account_all' => $accountAllFormatted,
                'accounts_data' => $accountsData,
                'account_all_data' => $accountAllData,
                'default_bank_id' => $defaultBankId,
            ]);
        } catch (\Exception $e) {
            dd($e);
            \Log::error('Error fetching challan data: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching challan data.'], 500);
        }
    }


    public function paidstudypackchallan(Request $request)
    {
        // dd($request->all());
        \DB::beginTransaction();
        $data = [];
        try {
            $invoicePayment = StudyPackChallans::where('challanNo', $request->challan_id)->first();
            $recipt_challan_amount = $invoicePayment->total_amount;
            $invoicePayment->paid_date = $request->recipt_date;
            $invoicePayment->paid_amount += $request->recipt_amt;
            if ($invoicePayment->paid_amount >= $invoicePayment->total_amount) {
                $invoicePayment->status = 'Paid';
            } else {
                $invoicePayment->status = 'Partial Paid';
            }
            $invoicePayment->save();

            $itemPayments = [];
            foreach ($request->head_id as $key => $head_id) {
                $challanitem = StudyPackChallanItems::find($head_id);
                if (!$challanitem) {
                    throw new \Exception('Challan item not found.');
                }

                $requestedAmount = (float) ($request->ramount[$key] ?? 0);
                $allowedAmount = self::getValidItemPaymentAmount($requestedAmount, $challanitem);
                if ($allowedAmount < $requestedAmount) {
                    throw new \Exception('Payment amount cannot exceed the payable amount of the item.');
                }

                $challanitem->paid += $allowedAmount;
                $challanitem->save();
                $itemPayments[] = $allowedAmount;
            }

            $Bank = BankAccount::find($request->bank);
            $recipt = new StudypackReceipts();
            $recipt->recipt_date = $request->recipt_date;
            $recipt->challan_id = $invoicePayment->id;
            $recipt->student_id = $invoicePayment->student_id;
            $recipt->challan_amount = $recipt_challan_amount;
            $recipt->remaining_fee = $invoicePayment->total_amount - $invoicePayment->concession_amount - $invoicePayment->paid_amount;
            $recipt->recipt_amount = $request->recipt_amt;
            $recipt->bank_id = $request->bank;
            $recipt->account_id = $Bank->chart_account_id;
            $recipt->referance = $request->ref;
            $recipt->receive_type = $request->receive_type;
            $recipt->received_by = Auth::user()->id;
            $recipt->owned_by = $invoicePayment->owned_by;
            $recipt->created_by = \Auth::user()->creatorId();
            $recipt->save();
            $data['id'] = $invoicePayment->id;
            $data['no'] = $invoicePayment->challanNo;
            $data['date'] = $recipt->recipt_date;
            $data['recipt'] = $recipt->id;
            $data['user_id'] = $invoicePayment->student_id;
            $data['bank_id'] = $request->bank;
            $data['reference'] = $recipt->referance;
            $data['description'] = 'Payment for Challan No ' . $invoicePayment->challanNo;
            $data['amount'] = $recipt->recipt_amount;
            $data['category'] = 'Studypack';
            $data['owned_by'] = $invoicePayment->owned_by;
            $data['created_by'] = $invoicePayment->created_by;
            $data['account_id'] = $request->bank;
            $data['amount'] = $recipt->recipt_amount;
            $data['total'] = $recipt->recipt_amount;
            $dataret = Utility::strv_entry($data);
            $recipt->update(['voucher_id' => $dataret]);
            if (\Auth::user()->type == 'company') {
                $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            } else {
                $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }
            $recipts = $recipt;
            // dd($recipts);
            $data = view('students.studypackChallan.data_row', compact('recipts', 'accounts'))->render();
            // dd($data);
            \DB::commit();
            return response()->json(['success' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            \DB::rollback();
            dd($e);
            return response()->json(['error' => 'An error occurred while processing the payment.'], 500);
        }
    }
}
