<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\StudentRegistration;
use App\Models\StudentIncome;
use App\Models\Classes;
use App\Models\User;
use Illuminate\Http\Request;

class StudentIncomeController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->can('manage journal entry')) {
            $user = \Auth::user();
            $creatorId = $user->creatorId();
            $ownedId = $user->ownedId();
            
            $query = StudentIncome::where('created_by', $creatorId);
            
            $filterDate = $request->has('date') ? $request->date : date('Y-m-d');
            if (!empty($filterDate)) {
                $query->where('date', $filterDate);
            }
            if ($request->has('branch_id') && !empty($request->branch_id)) {
                $query->where('owned_by', $request->branch_id);
            }
            if ($request->has('class_id') && !empty($request->class_id)) {
                $query->whereIn('student_id', function($q) use ($request) {
                    $q->select('id')->from('student_registrations')->where('class_id', $request->class_id);
                });
            }

            $incomes = $query->with(['student', 'bank', 'coa', 'receivedBy', 'branch', 'journalEntry'])->orderBy('date', 'desc')->get();

            // Calculate pending totals matching current filters
            $pendingQuery = StudentIncome::where('created_by', $creatorId)
                ->whereNull('journal_entry_id');
            if (!empty($filterDate)) {
                $pendingQuery->where('date', $filterDate);
            }
            if ($request->has('branch_id') && !empty($request->branch_id)) {
                $pendingQuery->where('owned_by', $request->branch_id);
            }
            if ($request->has('class_id') && !empty($request->class_id)) {
                $pendingQuery->whereIn('student_id', function($q) use ($request) {
                    $q->select('id')->from('student_registrations')->where('class_id', $request->class_id);
                });
            }

            $pendingCount = $pendingQuery->count();
            $pendingSum = $pendingQuery->sum('amount');

            // Fetch branches for filters
            if ($user->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend($user->name, $user->id);
            } else {
                $branches = User::where('id', '=', $ownedId)->get()->pluck('name', 'id');
            }
            $branches->prepend('Select Branch', '');

            // Fetch classes for filters
            $classes = Classes::where('created_by', $creatorId)->pluck('name', 'id');
            $classes->prepend('Select Class', '');

            return view('studentIncome.index', compact('incomes', 'pendingCount', 'pendingSum', 'branches', 'classes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create journal entry')) {
            $user = \Auth::user();
            $creatorId = $user->creatorId();
            $ownedId = $user->ownedId();

            // 1. Fetch Branches
            if ($user->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend($user->name, $user->id);
            } else {
                $branches = User::where('id', '=', $ownedId)->get()->pluck('name', 'id');
            }
            $branches->prepend('Select Branch', '');

            // 2. Bank Accounts are now loaded via AJAX based on the selected branch
            $bankAccounts = [];

            // 3. Fetch Income Chart Accounts
            $incomeType = ChartOfAccountType::where('created_by', $creatorId)
                ->where('name', 'Income')
                ->first();
            $chartAccounts = [];
            if ($incomeType) {
                $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
                    ->where('type', $incomeType->id)
                    ->where('created_by', $creatorId)
                    ->get()
                    ->pluck('code_name', 'id');
            }

            return view('studentIncome.create', compact('branches', 'bankAccounts', 'chartAccounts'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    public function getClasses(Request $request)
    {
        $classes = Classes::where('owned_by', $request->branch_id)
            ->where('active_status', 1)
            ->pluck('name', 'id');
        return response()->json($classes);
    }

    public function getStudents(Request $request)
    {
        $studentsQuery = StudentRegistration::query()
            ->where('owned_by', $request->branch_id)
            ->where('class_id', $request->class_id);
        
        if (\Schema::hasColumn('student_registrations', 'student_status')) {
            $studentsQuery->where('student_status', 'Enrolled');
        }
        
        $students = $studentsQuery->orderBy('stdname')->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => trim(($student->roll_no ? $student->roll_no . ' - ' : '') . $student->stdname . ' s/d/o ' . $student->fathername)
                ];
            });
        return response()->json($students);
    }

    public function getBankAccounts(Request $request)
    {
        $bankAccounts = BankAccount::where('owned_by', $request->branch_id)
            ->where('type', 'head_imprest')
            ->pluck('holder_name', 'id');
        return response()->json($bankAccounts);
    }

    public function store(Request $request)
    {
        if (\Auth::user()->can('create journal entry')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required|date',
                    'entries' => 'required|array|min:1',
                    'entries.*.student_id' => 'required|integer',
                    'entries.*.amount' => 'required|numeric|min:0.01',
                    'income_type' => 'required|string|max:255',
                    'bank_id' => 'required|integer',
                    'coa_id' => 'required|integer',
                    'description' => 'nullable|string',
                ]
            );

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->getMessageBag()->first()
                ], 422);
            }

            // Get bank account to find/verify details
            $bankAccount = BankAccount::where('id', $request->bank_id)
                ->where('type', 'head_imprest')
                ->first();
            if (!$bankAccount) {
                return response()->json([
                    'success' => false,
                    'message' => __('Selected bank account is invalid or not of type Head Imprest.')
                ], 422);
            }

            try {
                $creatorId = \Auth::user()->creatorId();
                $ownedBy = $request->branch_id;
                
                \DB::beginTransaction();

                foreach ($request->entries as $entry) {
                    $income = new StudentIncome();
                    $income->date = $request->date;
                    $income->student_id = $entry['student_id'];
                    $income->income_type = $request->income_type;
                    $income->amount = $entry['amount'];
                    $income->bank_id = $request->bank_id;
                    $income->coa_id = $request->coa_id;
                    $income->description = $request->description;
                    $income->created_by = $creatorId;
                    $income->owned_by = $ownedBy;
                    $income->received_by = \Auth::user()->id;
                    $income->journal_entry_id = null;
                    $income->save();
                }

                \DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => __('Student Income successfully recorded (will be processed on Day End).')
                ]);
            } catch (\Exception $e) {
                \DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => __('Something went wrong: ') . $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => __('Permission denied.')
            ], 401);
        }
    }

    public function processDayEnd(Request $request)
    {
        if (\Auth::user()->can('create journal entry')) {
            $query = StudentIncome::where('created_by', \Auth::user()->creatorId())
                ->whereNull('journal_entry_id');

            $filterDate = $request->has('date') ? $request->date : date('Y-m-d');
            if (!empty($filterDate)) {
                $query->where('date', $filterDate);
            }
            if ($request->has('branch_id') && !empty($request->branch_id)) {
                $query->where('owned_by', $request->branch_id);
            }
            if ($request->has('class_id') && !empty($request->class_id)) {
                $query->whereIn('student_id', function($q) use ($request) {
                    $q->select('id')->from('student_registrations')->where('class_id', $request->class_id);
                });
            }

            $pendingIncomes = $query->get();

            if ($pendingIncomes->isEmpty()) {
                return redirect()->back()->with('error', __('No pending student incomes found to process.'));
            }

            // Group pending incomes by date, owned_by (branch), and bank_id
            $grouped = $pendingIncomes->groupBy(function($item) {
                return $item->date . '_' . $item->owned_by . '_' . $item->bank_id;
            });

            \DB::beginTransaction();
            try {
                $creatorId = \Auth::user()->creatorId();
                $voucherType = 'CRV';
                $journalController = new JournalEntryController();
                $processedCount = 0;

                foreach ($grouped as $key => $items) {
                    $firstItem = $items->first();
                    $date = $firstItem->date;
                    $bankId = $firstItem->bank_id;
                    $totalAmount = $items->sum('amount');
                    $branch = User::find($firstItem->owned_by);
                    $branchName = $branch ? $branch->name : __('Branch');
                    
                    $transactionDateTime = \Carbon\Carbon::parse($date)->setTimeFrom(now());

                    // Check if an existing JournalEntry for this branch, date, type, category, and bank already exists
                    $journal = JournalEntry::where('owned_by', $firstItem->owned_by)
                        ->where('date', $date)
                        ->where('voucher_type', $voucherType)
                        ->where('category', 'student_other_income')
                        ->where('bank_id', $bankId)
                        ->first();

                    if ($journal) {
                        // Keep or set bank_id
                        if (empty($journal->bank_id)) {
                            $journal->bank_id = $bankId;
                        }
                        $journal->save();
                    } else {
                        // 1. Create one JournalEntry for the branch, date, and bank
                        $journalNumber = $journalController->voucherNumber($voucherType, $firstItem->owned_by);

                        $journal = new JournalEntry();
                        $journal->journal_id = $journalNumber;
                        $journal->date = $date;
                        $journal->description = __('Consolidated Student Incomes Day End for ') . $branchName . ' - ' . $date;
                        $journal->voucher_type = $voucherType;
                        $journal->category = 'student_other_income';
                        $journal->bank_id = $bankId;
                        $journal->created_by = $creatorId;
                        $journal->owned_by = $firstItem->owned_by;
                        $journal->added_at = now();
                        $journal->added_by = \Auth::id();
                        $journal->created_at = $transactionDateTime;
                        $journal->updated_at = $transactionDateTime;
                        $journal->save();
                    }

                    // 2. Debit entry for the bank account in this group
                    $bankAccount = BankAccount::find($bankId);
                    if ($bankAccount) {
                        // Check if a debit line for this bank account already exists on the journal entry
                        $debitItem = JournalItem::where('journal', $journal->id)
                            ->where('account', $bankAccount->chart_account_id)
                            ->where('debit', '>', 0)
                            ->first();

                        if ($debitItem) {
                            // Update the existing debit line
                            $debitItem->debit += $totalAmount;
                            $debitItem->bank_id = $bankAccount->id;
                            $debitItem->save();
                        } else {
                            // Create new debitItem
                            $debitItem = new JournalItem();
                            $debitItem->journal = $journal->id;
                            $debitItem->account = $bankAccount->chart_account_id;
                            $debitItem->debit = $totalAmount;
                            $debitItem->credit = 0;
                            $debitItem->bank_id = $bankAccount->id;
                            $debitItem->description = __('Consolidated Deposit: ') . $bankAccount->bank_name;
                            $debitItem->branch_id = $journal->owned_by;
                            $debitItem->added_by = \Auth::id();
                            $debitItem->added_at = now();
                            $debitItem->created_at = $transactionDateTime;
                            $debitItem->updated_at = $transactionDateTime;
                            $debitItem->save();
                        }

                        $journalController->updateBankAccountBalance($debitItem->account, $totalAmount, 0);
                    }

                    // 3. Credit entry for each individual student income item
                    foreach ($items as $item) {
                        $creditItem = new JournalItem();
                        $creditItem->journal = $journal->id;
                        $creditItem->account = $item->coa_id;
                        $creditItem->debit = 0;
                        $creditItem->credit = $item->amount;
                        $creditItem->user_id = $item->student_id;
                        $creditItem->user_type = 'Student';
                        $creditItem->model_id = $item->id;
                        $creditItem->model_type = 'StudentIncome';
                        $studentInfo = optional($item->student)->stdname ?? '-';
                        if (optional($item->student)->roll_no) {
                            $studentInfo .= ' (' . $item->student->roll_no . ')';
                        }
                        
                        $creditItem->memo = $studentInfo . ' - ' . $item->income_type;
                        $creditItem->description = (!empty($item->description) ?  $item->description : '');
                        $creditItem->branch_id = $journal->owned_by;
                        $creditItem->added_by = \Auth::id();
                        $creditItem->added_at = now();
                        $creditItem->created_at = $transactionDateTime;
                        $creditItem->updated_at = $transactionDateTime;
                        $creditItem->save();

                        // Update chart of account balance (non-bank account, so this usually does nothing but good to run)
                        $journalController->updateBankAccountBalance($creditItem->account, $creditItem->debit, $creditItem->credit);

                        // Mark income as processed
                        $item->journal_entry_id = $journal->id;
                        $item->save();
                    }

                    $processedCount += $items->count();
                }

                \DB::commit();
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => sprintf(__('Day End processing completed. %d income entries consolidated.'), $processedCount)
                    ]);
                }
                return redirect()->back()->with('success', sprintf(__('Day End processing completed. %d income entries consolidated.'), $processedCount));
            } catch (\Exception $e) {
                \DB::rollback();
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Something went wrong: ') . $e->getMessage()
                    ], 500);
                }
                return redirect()->back()->with('error', __('Something went wrong: ') . $e->getMessage());
            }
        } else {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Permission denied.')
                ], 403);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function destroy($id)
    {
        if (\Auth::user()->can('delete journal entry')) {
            $income = StudentIncome::find($id);
            if (!$income) {
                return redirect()->back()->with('error', __('Record not found.'));
            }

            if (!empty($income->journal_entry_id)) {
                return redirect()->back()->with('error', __('This student income has already been processed and linked to a voucher. It cannot be deleted.'));
            }

            $income->delete();

            return redirect()->back()->with('success', __('Student income record successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
