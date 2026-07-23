<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChallanHead;
use App\Models\Challans;
use App\Models\ChartOfAccount;
use App\Models\Classes;
use App\Models\Concession;
use App\Models\ConcessionPolicyHead;
use App\Models\FeeHead;
use App\Models\Session;
use App\Models\StudentFeeStructure;
use App\Models\StudentReceipt;
use App\Models\StudentRegistration;
use App\Models\User;
use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BulkBillingController extends Controller
{
    public function create()
    {
        $userCreatorId = Auth::user()->creatorId();
        $userType = Auth::user()->type;

        if ($userType == 'company') {
            $branches = User::where('type', 'branch')
                ->where('created_by', $userCreatorId)
                ->where('is_active', 1)
                ->pluck('name', 'id');
            $branches->prepend(Auth::user()->name, Auth::user()->id);
        } else {
            $branches = User::where('id', Auth::user()->ownedId())
                ->where('is_active', 1)
                ->pluck('name', 'id');
        }

        $accounts = BankAccount::select('*', DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
            ->where('created_by', $userCreatorId)
            ->get()->pluck('name', 'id');

        $bankAccounts = BankAccount::with('chartAccount:id,name')
            ->where('created_by', $userCreatorId)
            ->get();

        $accountsData = [];
        foreach ($bankAccounts as $ba) {
            $accountsData[$ba->id] = [
                'name' => $ba->bank_name . ' ' . $ba->holder_name,
                'chart_account' => $ba->chartAccount ? strtolower($ba->chartAccount->name) : '',
            ];
        }

        $feeHeads = FeeHead::where('created_by', $userCreatorId)->where('active_status', 1)->get();

        $sessions = Session::where('created_by', $userCreatorId)
            ->where('active_status', 1)
            ->orderBy('id', 'desc')
            ->pluck('year', 'id');

        return view('bulk-billing.create', compact('branches', 'accounts', 'accountsData', 'feeHeads', 'sessions'));
    }

     public function getStudents($branchId)
    {
        $students = StudentRegistration::where('owned_by', $branchId)
            ->whereIn('student_status', ['Enrolled', 'Withdrawal'])
            // ->whereHas('enrollment', function ($q) {
            //     $q->where('active_status', 1);
            // })
            ->orderBy('stdname')
            ->get(['id', 'stdname', 'roll_no']);

        $list = $students->map(function ($s) {
            return [
                'id' => $s->id,
                'text' => $s->stdname . ' - ' . $s->roll_no,
            ];
        });

        return response()->json($list);
    }


    public function getFeeStructure($studentId, $month)
    {
        $student = StudentRegistration::with('enrollment')->findOrFail($studentId);

        $existingAdmissionChallan = Challans::where('student_id', $studentId)
            ->where('challan_type', 'Admission')
            ->exists();

        if ($existingAdmissionChallan) {
            return response()->json(['error' => 'Admission Challan already exists for this student.'], 422);
        }

        $feeMonth = Carbon::parse($month)->startOfMonth();
        $applyJunJulFeeExemption = $this->appliesJunJulFeeExemption($student, $feeMonth);

        $studentFeeStructures = StudentFeeStructure::where('reg_id', $studentId)
            ->orderBy('id', 'desc')
            ->get()
            ->unique('head_id');

        if ($studentFeeStructures->isEmpty()) {
            return response()->json([]);
        }

        $feeHeadIds = $studentFeeStructures->pluck('head_id');
        $feeHeads = FeeHead::whereIn('id', $feeHeadIds)
            ->where('active_status', 1)
            ->get()
            ->keyBy('id');

        $concessiondata = Concession::where('student_id', $studentId)
            ->where('end_date', '>=', date('Y-m-d'))
            ->where('status', 'Approved')
            ->first();

        if (!$concessiondata) {
            $concessiondata = Concession::where('student_id', $studentId)
                ->whereNull('end_date')
                ->where('status', 'Approved')
                ->first();
        }

        $heads = [];
        foreach ($studentFeeStructures as $feeStructure) {
            $head = $feeHeads->get($feeStructure->head_id);
            if (!$head) {
                continue;
            }

            $baseAmount = $applyJunJulFeeExemption ? 0 : (float) $feeStructure->amount;
            $concessionPct = $feeStructure->discount ? (float) $feeStructure->discount : 0;

            if (!$applyJunJulFeeExemption && $concessiondata) {
                $policyHead = ConcessionPolicyHead::where('head_id', $head->id)
                    ->where('concession_id', $concessiondata->concession_id)
                    ->first();
                if ($policyHead) {
                    $concessionPct = (float) $policyHead->percentage;
                }
            }

            $concessionAmount = $baseAmount > 0 ? round(($baseAmount / 100) * $concessionPct) : 0;
            $payableAmount = $baseAmount > 0 ? $baseAmount - $concessionAmount : 0;

            $headName = strtolower($head->fee_head);
            $autoCheckKeywords = ['admission', 'security', 'tuition', 'annual'];
            $shouldAutoCheck = false;
            foreach ($autoCheckKeywords as $keyword) {
                if (str_contains($headName, $keyword)) {
                    $shouldAutoCheck = true;
                    break;
                }
            }

            $heads[] = [
                'head_id' => $head->id,
                'head_name' => $head->fee_head,
                'base_amount' => $baseAmount,
                'concession_pct' => $concessionPct,
                'concession_amount' => $concessionAmount,
                'payable_amount' => $payableAmount,
                'checked' => $shouldAutoCheck && $baseAmount > 0,
            ];
        }

        usort($heads, function ($a, $b) {
            return strcasecmp($a['head_name'], $b['head_name']);
        });
        $heads = array_values($heads);

        return response()->json($heads);
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|integer',
            'student_id' => 'required|integer',
            'session_id' => 'required|integer',
            'billing_month' => 'required|date_format:Y-m',
            'challan_date' => 'required|date',
            'issue_date' => 'required|date',
            'due_date' => 'required|date',
            'payment_date' => 'required|date',
            'bank_id' => 'required|integer',
            'payment_type' => 'required|string|in:DD,OL,CHQ,CD',
            'reference' => 'nullable|string|max:255',
            'heads' => 'required|array|min:1',
            'heads.*.head_id' => 'required|integer',
            'heads.*.base_amount' => 'required|numeric|min:0',
            'heads.*.concession_amount' => 'required|numeric|min:0',
            'heads.*.payable_amount' => 'required|numeric|min:0',
        ]);

        $existingAdmissionChallan = Challans::where('student_id', $request->student_id)
            ->where('challan_type', 'Admission')
            ->exists();

        if ($existingAdmissionChallan) {
            return redirect()->back()->with('error', 'Admission Challan already exists for this student.');
        }

        DB::beginTransaction();
        try {
            $student = StudentRegistration::with('enrollment')->findOrFail($request->student_id);
            $branch = User::findOrFail($request->branch_id);

            $totalAmount = 0;
            $totalConcession = 0;
            $itemIndex = 0;
            $item = [];

            $challan = new Challans;
            $challan->student_id = $request->student_id;
            $challan->class_id = $student->class_id;
            $challan->rollno = $student->roll_no;
            $challan->session_id = $request->session_id;
            $challan->challanNo = $this->challanNo();
            $challan->fee_month = $request->billing_month . '-01';
            $challan->challan_date = $request->challan_date;
            $challan->challan_type = 'Admission';
            $challan->remarks = 'Bulk Admission Import';
            $challan->issue_date = $request->issue_date;
            $challan->due_date = $request->due_date;
            $challan->status = 'Issued';
            $challan->owned_by = $request->branch_id;
            $challan->created_by = Auth::user()->creatorId();
            $challan->timestamps = false;
            $challan->created_at = $request->challan_date . ' 00:00:00';
            $challan->updated_at = $request->challan_date . ' 00:00:00';
            $challan->save();

            foreach ($request->heads as $row) {
                if (empty($row['checked'])) {
                    continue;
                }

                $baseAmount = (float) $row['base_amount'];
                $concessionAmount = (float) $row['concession_amount'];
                $payableAmount = (float) $row['payable_amount'];

                $challanHead = new ChallanHead;
                $challanHead->challan_id = $challan->id;
                $challanHead->head_id = $row['head_id'];
                $challanHead->price = $baseAmount;
                $challanHead->concession = $concessionAmount;
                $challanHead->save();

                $totalAmount += $baseAmount;
                $totalConcession += $concessionAmount;

                $item[$itemIndex] = [
                    'prod_id' => $challanHead->id,
                    'head' => $row['head_id'],
                    'price' => $baseAmount,
                    'quantity' => 1,
                    'concession' => $concessionAmount,
                    'total' => $totalAmount,
                ];
                $itemIndex++;
            }

            $challan->total_amount = $totalAmount;
            $challan->concession_amount = $totalConcession;
            $challan->save();

            $jvData = [
                'id' => $challan->id,
                'no' => $challan->challanNo,
                'date' => $challan->challan_date,
                'reference' => $challan->student_id,
                'category' => 'Admission',
                'user_id' => $challan->student_id,
                'std_name' => $student->stdname,
                'branch_name' => $branch->name,
                'fee_month' => $challan->fee_month,
                'bank_name' => '',
                'user_type' => 'Student',
                'owned_by' => $challan->owned_by,
                'created_by' => $challan->created_by,
                'items' => $item,
            ];

            $voucherId = Utility::jrentry($jvData);
            $challan->voucher_id = $voucherId;
            $challan->save();

            $netPayable = $totalAmount - $totalConcession;

            $receipt = StudentReceipt::create([
                'recipt_date' => $request->payment_date,
                'challan_id' => $challan->id,
                'student_id' => $challan->student_id,
                'recipt_amount' => $netPayable,
                'challan_amount' => $netPayable,
                'late_amount' => 0,
                'arrears' => 0,
                'bank_id' => $request->bank_id,
                'referance' => $request->reference ?? '',
                'receive_type' => $request->payment_type,
                'received_by' => Auth::user()->id,
                'owned_by' => $challan->owned_by,
                'created_by' => Auth::user()->creatorId(),
            ]);

            $challan->paid_amount = $netPayable;
            $challan->paid_date = $request->payment_date;
            $challan->status = 'Paid';
            $challan->save();

            foreach ($challan->heads as $chHead) {
                $chHead->paid = $chHead->price - $chHead->concession;
                $chHead->save();
            }

            $bankAccount = BankAccount::findOrFail($request->bank_id);

            $receiptData = [
                'id' => $challan->id,
                'challan_id' => $challan->id,
                'no' => $challan->challanNo,
                'prod_id' => $challan->id,
                'date' => $receipt->recipt_date,
                'reference' => $request->reference ?? '',
                'description' => '',
                'user_id' => $challan->student_id,
                'bank_id' => $request->bank_id,
                'bank_name' => $bankAccount->bank_name,
                'branch_id' => $challan->owned_by,
                'user_type' => 'Student',
                'amount' => $netPayable,
                'category' => $challan->challan_type,
                'owned_by' => $challan->owned_by,
                'created_by' => Auth::user()->creatorId(),
                'account_id' => $bankAccount->chart_account_id,
                'recipt' => $receipt->id,
                'receipt_date' => $receipt->recipt_date,
                'created_at' => $receipt->recipt_date . ' ' . date('H:i:s'),
                'items' => $item,
                'total' => $netPayable,
            ];

            $feeMonth = $challan->fee_month;
            $isBeforeFeb2026 = $feeMonth && strtotime($feeMonth) < strtotime('2026-02-01');

            if ($isBeforeFeb2026) {
                $bankTransfer = \App\Models\BankTransfer::where(function ($q) use ($request) {
                    $q->where('from_account', $request->bank_id);
                })
                    ->whereYear('date', 2026)
                    ->whereMonth('date', 2)
                    ->orderBy('date')
                    ->orderBy('id')
                    ->first();

                if ($bankTransfer) {
                    $bankTransfer->amount = ($bankTransfer->amount ?? 0) + $netPayable;
                    $bankTransfer->previous_balance = ($bankTransfer->previous_balance ?? 0) + $netPayable;
                    $bankTransfer->save();

                    $journalItems = \App\Models\JournalItem::where('journal', $bankTransfer->voucher_id)->get();
                    foreach ($journalItems as $ji) {
                        if ($ji->debit > 0) {
                            $ji->debit += $netPayable;
                        }
                        if ($ji->credit > 0) {
                            $ji->credit += $netPayable;
                        }
                        $ji->save();
                    }
                }
            }

            if (ucwords($request->payment_type) == 'CD') {
                Utility::crv_entry($receiptData, !$isBeforeFeb2026);
            } else {
                Utility::brv_entry($receiptData, !$isBeforeFeb2026);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Challan #' . $challan->challanNo . ' and Receipt created successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    private function challanNo()
    {
        $latest = Challans::orderByRaw('CAST(challanNo AS UNSIGNED) DESC')->first();
        if (!$latest || !$latest->challanNo) {
            return 1;
        }
        return (int) $latest->challanNo + 1;
    }

    private function appliesJunJulFeeExemption($student, Carbon $feeMonth)
    {
        if (!$student->fee_exempt_jun_jul) {
            return false;
        }
        $month = $feeMonth->month;
        if (!in_array($month, [6, 7])) {
            return false;
        }
        $admYear = optional($student->enrollment)->adm_date
            ? Carbon::parse($student->enrollment->adm_date)->year
            : now()->year;
        return $feeMonth->year == $admYear;
    }
}
