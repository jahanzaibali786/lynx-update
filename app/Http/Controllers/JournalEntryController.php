<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\Designation;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\StudentRegistration;
use App\Models\User;
use App\Models\Utility;
use App\Models\Vender;
use Illuminate\Http\Request;

class JournalEntryController extends Controller
{

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage journal entry')) {
            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $query = JournalEntry::where('created_by', '=', \Auth::user()->creatorId())->where('voucher_type', 'JV');
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = JournalEntry::where('owned_by', '=', \Auth::user()->ownedId())->where('voucher_type', 'JV');
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            $journalEntries = $query->orderBy('id', 'desc')->paginate(25);
            // dd($journalEntries);
            return view('journalEntry.index', compact('journalEntries', 'branches'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if (\Auth::user()->can('create journal entry')) {
            $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name,chart_of_accounts.id, chart_of_accounts.code,  chart_of_accounts.parent'))
                ->where('parent', '=', 0)
                ->where('created_by', \Auth::user()->creatorId())->get()
                ->toarray();

            $subAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name , chart_of_accounts.id, chart_of_accounts.code , chart_of_account_parents.account'));
            $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $subAccounts->where('chart_of_accounts.parent', '!=', 0);
            $subAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
            $subAccounts = $subAccounts->get()->toArray();

            $journalId = $this->journalNumber();

            return view('journalEntry.create', compact('chartAccounts', 'subAccounts', 'journalId'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function store(Request $request)
    {
        if (\Auth::user()->can('create journal entry')) {
            \DB::beginTransaction();
            try {
                $validator = \Validator::make(
                    $request->all(),
                    [
                        'date' => 'required',
                        'voucher_type' => 'nullable|in:jv,cpv,bpv,crv,brv,JV,CPV,BPV,CRV,BRV',
                        'accounts' => 'required|array|min:1',
                    ]
                );
                if ($validator->fails()) {
                    \DB::rollback();
                    return response()->json([
                        'status' => 'error',
                        'message' => $validator->getMessageBag()->first()
                    ], 422);
                }

                $accounts = $request->accounts;
                $voucherType = $this->normalizeVoucherType($request->voucher_type);
                $ownedBy = $request->branches ?: \Auth::user()->ownedId();

                $totalDebit = 0;
                $totalCredit = 0;
                for ($i = 0; $i < count($accounts); $i++) {
                    $debit = isset($accounts[$i]['debit']) ? (float) $accounts[$i]['debit'] : 0;
                    $credit = isset($accounts[$i]['credit']) ? (float) $accounts[$i]['credit'] : 0;
                    $accountId = $accounts[$i]['account_id'] ?? $accounts[$i]['account'] ?? null;

                    if (empty($accountId)) {
                        \DB::rollback();
                        return response()->json([
                            'status' => 'error',
                            'message' => __('Please select an account for every line.')
                        ], 422);
                    }

                    if (($debit <= 0 && $credit <= 0) || ($debit > 0 && $credit > 0)) {
                        \DB::rollback();
                        return response()->json([
                            'status' => 'error',
                            'message' => __('Each line must have either debit or credit amount.')
                        ], 422);
                    }

                    $totalDebit += $debit;
                    $totalCredit += $credit;
                }

                if (round($totalCredit, 2) != round($totalDebit, 2)) {
                    \DB::rollback();
                    return response()->json([
                        'status' => 'error',
                        'message' => __('Debit and Credit must be Equal.')
                    ], 400);
                }

                $journal = new JournalEntry();
                $journal->journal_id = $this->voucherNumber($voucherType, $ownedBy);
                $journal->date = $request->date;
                $journal->reference = $request->reference;
                $journal->description = $request->narration ?? $request->description;
                $journal->voucher_type = $voucherType;
                $journal->owned_by = $ownedBy;
                $journal->created_by = \Auth::user()->creatorId();
                $this->setModelValueIfColumn($journal, 'amount', $request->amount);
                $this->setModelValueIfColumn($journal, 'mode', $request->mode);
                $journal->save();

                for ($i = 0; $i < count($accounts); $i++) {
                    $account = $accounts[$i];
                    $accountId = $account['account_id'] ?? $account['account'];
                    $debit = isset($account['debit']) ? (float) $account['debit'] : 0;
                    $credit = isset($account['credit']) ? (float) $account['credit'] : 0;
                    $manualDescription = trim($account['description'] ?? '');
                    $lineMemo = trim($account['memo'] ?? '');
                    $journalItem = new JournalItem();
                    $journalItem->journal = $journal->id;
                    $journalItem->account = $accountId;
                    $journalItem->description = $manualDescription;
                    $this->setModelValueIfColumn($journalItem, 'memo', $lineMemo);
                    $journalItem->debit = $debit;
                    $journalItem->credit = $credit;
                    $journalItem->types = $account['types'] ?? $voucherType;
                    $journalItem->branch_id = $account['branch_id'] ?? $ownedBy;
                    $journalItem->user_type = $account['user_type'] ?? null;
                    $journalItem->user_id = $account['user_id'] ?? null;
                    $this->setModelValueIfColumn($journalItem, 'category', $account['category'] ?? null);
                    $this->setModelValueIfColumn($journalItem, 'department_id', $account['dept_id'] ?? null);
                    $this->setModelValueIfColumn($journalItem, 'designation_id', $account['designation_id'] ?? null);
                    $journalItem->save();

                    $bankAccounts = BankAccount::where('chart_account_id', '=', $accountId)->get();
                    if (!empty($bankAccounts)) {
                        foreach ($bankAccounts as $bankAccount) {
                            $old_balance = $bankAccount->opening_balance;
                            $new_balance = null;
                            if ($journalItem->debit > 0) {
                                $new_balance = $old_balance - $journalItem->debit;
                            }
                            if ($journalItem->credit > 0) {
                                $new_balance = $old_balance + $journalItem->credit;
                            }
                            if (isset($new_balance)) {
                                $bankAccount->opening_balance = $new_balance;
                                $bankAccount->save();
                            }
                        }
                    }

                }


                \DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => __('Voucher successfully created.'),
                    'redirect' => route('journal-entry.show', $journal->id)
                ]);
                // return redirect()->route('journal-entry.index')->with('success', __('Journal entry successfully created.'));
            } catch (\Exception $e) {
                \DB::rollback();
                return response()->json([
                    'status' => 'error',
                    'message' => __('Something went wrong: ') . $e->getMessage()
                ], 500);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => __('Permission denied.')], 403);
        }
    }


    public function show(JournalEntry $journalEntry)
    {
        if (\Auth::user()->can('show journal entry')) {
            if ($journalEntry->created_by == \Auth::user()->creatorId()) {
                $accounts = $journalEntry->accounts;
                $settings = Utility::settings();

                return view('journalEntry.view', compact('journalEntry', 'accounts', 'settings'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit(JournalEntry $journalEntry)
    {
        if (\Auth::user()->can('edit journal entry')) {
            if ($journalEntry->created_by != \Auth::user()->creatorId()) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

            $formData = $this->voucherFormData();
            $journalItems = $this->voucherItemsForEditor($journalEntry);
            $voucherNumber = $this->formatVoucherNumber($journalEntry->journal_id, $journalEntry->voucher_type);

            return view('journalEntry.createvoucher', array_merge($formData, compact('journalEntry', 'journalItems', 'voucherNumber')));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function update(Request $request, JournalEntry $journalEntry)
    {
        if (\Auth::user()->can('edit journal entry')) {
            if ($journalEntry->created_by == \Auth::user()->creatorId()) {
                \DB::beginTransaction();
                try {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'date' => 'required',
                            'voucher_type' => 'nullable|in:jv,cpv,bpv,crv,brv,JV,CPV,BPV,CRV,BRV',
                            'accounts' => 'required|array|min:1',
                        ]
                    );
                    if ($validator->fails()) {
                        \DB::rollback();
                        return response()->json([
                            'status' => 'error',
                            'message' => $validator->getMessageBag()->first()
                        ], 422);
                    }

                    $accounts = $request->accounts;
                    $totals = $this->validateVoucherItems($accounts);

                    if ($totals['error']) {
                        \DB::rollback();
                        return response()->json([
                            'status' => 'error',
                            'message' => $totals['message']
                        ], $totals['code']);
                    }

                    $voucherType = $this->normalizeVoucherType($request->voucher_type ?: $journalEntry->voucher_type);
                    $ownedBy = $request->branches ?: $journalEntry->owned_by;
                    if ($voucherType !== $journalEntry->voucher_type || (int) $ownedBy !== (int) $journalEntry->owned_by) {
                        $journalEntry->journal_id = $this->voucherNumber($voucherType, $ownedBy);
                    }

                    $journalEntry->date = $request->date;
                    $journalEntry->reference = $request->reference;
                    $journalEntry->description = $request->narration ?? $request->description;
                    $journalEntry->voucher_type = $voucherType;
                    $journalEntry->owned_by = $ownedBy;
                    $journalEntry->created_by = \Auth::user()->creatorId();
                    $this->setModelValueIfColumn($journalEntry, 'amount', $request->amount);
                    $this->setModelValueIfColumn($journalEntry, 'mode', $request->mode);
                    $journalEntry->save();

                    JournalItem::where('journal', $journalEntry->id)->delete();
                    $this->saveVoucherItems($journalEntry, $accounts, $ownedBy, $voucherType, false);

                    \DB::commit();
                    return response()->json([
                        'status' => 'success',
                        'message' => __('Voucher successfully updated.'),
                        'redirect' => route('journal-entry.show', $journalEntry->id)
                    ]);
                } catch (\Exception $e) {
                    \DB::rollback();
                    return response()->json([
                        'status' => 'error',
                        'message' => __('Something went wrong: ') . $e->getMessage()
                    ], 500);
                }
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(JournalEntry $journalEntry)
    {


        if (\Auth::user()->can('delete journal entry')) {
            if ($journalEntry->created_by == \Auth::user()->creatorId()) {
                $journalEntry->delete();


                JournalItem::where('journal', '=', $journalEntry->id)->delete();

                return redirect()->route('journal-entry.index')->with('success', __('Journal entry successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function journalNumber()
    {
        return $this->voucherNumber('JV', \Auth::user()->ownedId());
    }

    private function voucherNumber($voucherType = 'JV', $ownedBy = null)
    {
        $latest = JournalEntry::where('owned_by', '=', $ownedBy ?: \Auth::user()->ownedId())
            ->where('voucher_type', $this->normalizeVoucherType($voucherType))
            ->latest()
            ->first();

        return $latest ? $latest->journal_id + 1 : 1;
    }

    private function normalizeVoucherType($voucherType)
    {
        $type = strtoupper($voucherType ?: 'JV');
        $allowedTypes = ['JV', 'CPV', 'BPV', 'CRV', 'BRV'];

        return in_array($type, $allowedTypes) ? $type : 'JV';
    }

    private function validateVoucherItems($accounts)
    {
        $totalDebit = 0;
        $totalCredit = 0;

        for ($i = 0; $i < count($accounts); $i++) {
            $debit = isset($accounts[$i]['debit']) ? (float) $accounts[$i]['debit'] : 0;
            $credit = isset($accounts[$i]['credit']) ? (float) $accounts[$i]['credit'] : 0;
            $accountId = $accounts[$i]['account_id'] ?? $accounts[$i]['account'] ?? null;

            if (empty($accountId)) {
                return [
                    'error' => true,
                    'message' => __('Please select an account for every line.'),
                    'code' => 422,
                ];
            }

            if (($debit <= 0 && $credit <= 0) || ($debit > 0 && $credit > 0)) {
                return [
                    'error' => true,
                    'message' => __('Each line must have either debit or credit amount.'),
                    'code' => 422,
                ];
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        if (round($totalCredit, 2) != round($totalDebit, 2)) {
            return [
                'error' => true,
                'message' => __('Debit and Credit must be Equal.'),
                'code' => 400,
            ];
        }

        return [
            'error' => false,
            'debit' => $totalDebit,
            'credit' => $totalCredit,
        ];
    }

    private function saveVoucherItems(JournalEntry $journal, $accounts, $ownedBy, $voucherType, $updateBankBalance = true)
    {
        for ($i = 0; $i < count($accounts); $i++) {
            $account = $accounts[$i];
            $accountId = $account['account_id'] ?? $account['account'];
            $debit = isset($account['debit']) ? (float) $account['debit'] : 0;
            $credit = isset($account['credit']) ? (float) $account['credit'] : 0;

            $journalItem = new JournalItem();
            $journalItem->journal = $journal->id;
            $journalItem->account = $accountId;
            $journalItem->description = trim($account['description'] ?? '');
            $this->setModelValueIfColumn($journalItem, 'memo', trim($account['memo'] ?? ''));
            $journalItem->debit = $debit;
            $journalItem->credit = $credit;
            $journalItem->types = $account['types'] ?? $voucherType;
            $journalItem->branch_id = $account['branch_id'] ?? $ownedBy;
            $journalItem->user_type = $account['user_type'] ?? null;
            $journalItem->user_id = $account['user_id'] ?? null;
            $this->setModelValueIfColumn($journalItem, 'category', $account['category'] ?? null);
            $this->setModelValueIfColumn($journalItem, 'department_id', $account['dept_id'] ?? null);
            $this->setModelValueIfColumn($journalItem, 'designation_id', $account['designation_id'] ?? null);
            $journalItem->save();

            if ($updateBankBalance) {
                $this->updateBankAccountBalance($accountId, $journalItem->debit, $journalItem->credit);
            }
        }
    }

    private function updateBankAccountBalance($accountId, $debit, $credit)
    {
        $bankAccounts = BankAccount::where('chart_account_id', '=', $accountId)->get();
        if (!empty($bankAccounts)) {
            foreach ($bankAccounts as $bankAccount) {
                $oldBalance = $bankAccount->opening_balance;
                $newBalance = null;
                if ($debit > 0) {
                    $newBalance = $oldBalance - $debit;
                }
                if ($credit > 0) {
                    $newBalance = $oldBalance + $credit;
                }
                if (isset($newBalance)) {
                    $bankAccount->opening_balance = $newBalance;
                    $bankAccount->save();
                }
            }
        }
    }

    private function voucherFormData()
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->where('is_active', '1')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->where('is_active', '1')->get()->pluck('name', 'id');
        }

        $departments = Department::all();
        $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name,chart_of_accounts.id, chart_of_accounts.code, chart_of_accounts.parent, chart_of_accounts.category'))
            ->where('parent', '=', 0)
            ->where('created_by', \Auth::user()->creatorId())->get()
            ->toarray();

        $subAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name) AS code_name, chart_of_accounts.id, chart_of_accounts.code, chart_of_accounts.category, chart_of_account_parents.account'));
        $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
        $subAccounts->where('chart_of_accounts.parent', '!=', 0);
        $subAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
        $subAccounts = $subAccounts->get()->toArray();

        $journalId = $this->journalNumber();

        return compact('branches', 'departments', 'chartAccounts', 'subAccounts', 'journalId');
    }

    private function voucherItemsForEditor(JournalEntry $journalEntry)
    {
        $journalEntry->load('accounts.accounts');

        return $journalEntry->accounts->map(function ($item) use ($journalEntry) {
            $account = $item->accounts;
            $userType = $item->user_type;
            $category = $account->category ?: $this->categoryFromUserType($userType);
            $branchId = $item->branch_id ?: $journalEntry->owned_by;
            $memo = $item->memo ?? '';
            $description = $item->description ?? '';
            $meta = $this->voucherItemMeta($category, $branchId, $userType, $item->user_id);
            $generatedMemoLabel = $this->voucherMemoLabelFromMeta($category, $meta);
            $generatedMemo = $generatedMemoLabel ? '→ ' . $generatedMemoLabel : '';

            if ($memo === '' && preg_match('/^\s*(→|â†’)\s*/u', $description)) {
                $parts = explode(' - ', $description, 2);
                $memo = $parts[0] ?? '';
                $description = $parts[1] ?? '';
            }

            if ($memo === '' && $generatedMemo !== '') {
                $partyNames = array_filter([
                    $meta['employee_name'] ?? null,
                    $meta['student_name'] ?? null,
                    $meta['vendor_name'] ?? null,
                ]);

                if (in_array(trim($description), $partyNames, true)) {
                    $description = '';
                }

                $memo = $generatedMemo;
            }

            return [
                'id' => $item->id,
                'account_id' => $item->account,
                'path' => $account ? '[' . ($account->code ?? $account->id) . '] ' . $account->code . ' - ' . $account->name : '',
                'cat' => $category ?: 'general',
                'meta' => $meta,
                'metaLabel' => preg_replace('/^\s*→\s*/u', '', $memo),
                'lineMemo' => $memo,
                'userType' => $userType,
                'userId' => $item->user_id,
                'types' => $item->types ?: $journalEntry->voucher_type,
                'debit' => (float) $item->debit,
                'credit' => (float) $item->credit,
                'desc' => $description,
            ];
        })->values();
    }

    private function voucherItemMeta($category, $branchId, $userType, $userId)
    {
        $branchName = optional(User::find($branchId))->name;
        $meta = [
            'branch_id' => $branchId,
            'branch_name' => $branchName,
        ];

        if ($category === 'hr' || strtolower($userType ?? '') === 'employee') {
            $employee = Employee::with(['department', 'designation'])->find($userId);
            $meta['dept_id'] = $employee->department_id ?? '';
            $meta['dept_name'] = optional($employee->department)->name;
            $meta['designation_id'] = $employee->designation_id ?? '';
            $meta['designation_name'] = optional($employee->designation)->name;
            $meta['employee_id'] = $employee->id ?? $userId;
            $meta['employee_name'] = $employee->name ?? '';
        } elseif ($category === 'student' || strtolower($userType ?? '') === 'student') {
            $student = StudentRegistration::find($userId);
            $meta['student_id'] = $student->id ?? $userId;
            $meta['student_name'] = $student ? trim($student->roll_no . ' - ' . $student->stdname . ' s/d/o ' . $student->fathername) : '';
        } elseif ($category === 'inventory' || strtolower($userType ?? '') === 'vender') {
            $vendor = Vender::find($userId);
            $meta['vendor_id'] = $vendor->id ?? $userId;
            $meta['vendor_name'] = $vendor->name ?? '';
        }

        return $meta;
    }

    private function voucherMemoLabelFromMeta($category, $meta)
    {
        if ($category === 'hr') {
            $parts = [
                $meta['branch_name'] ?? '',
                $meta['dept_name'] ?? '',
                $meta['designation_name'] ?? '',
                $meta['employee_name'] ?? '',
            ];
        } elseif ($category === 'student') {
            $parts = [
                $meta['branch_name'] ?? '',
                $meta['student_name'] ?? '',
            ];
        } elseif ($category === 'inventory') {
            $parts = [
                $meta['branch_name'] ?? '',
                $meta['vendor_name'] ?? '',
            ];
        } else {
            $parts = [];
        }

        return implode(' › ', array_values(array_filter($parts)));
    }

    private function categoryFromUserType($userType)
    {
        $type = strtolower($userType ?? '');
        if ($type === 'employee') {
            return 'hr';
        }
        if ($type === 'student') {
            return 'student';
        }
        if ($type === 'vender' || $type === 'vendor') {
            return 'inventory';
        }

        return 'general';
    }

    private function formatVoucherNumber($number, $voucherType)
    {
        $methodMap = [
            'BRV' => 'BRVNumberFormat',
            'BPV' => 'BPVNumberFormat',
            'CRV' => 'CRVNumberFormat',
            'CPV' => 'CPVNumberFormat',
        ];
        $type = $this->normalizeVoucherType($voucherType);
        $method = $methodMap[$type] ?? 'journalNumberFormat';

        return \Auth::user()->$method($number);
    }

    private function setModelValueIfColumn($model, $column, $value)
    {
        if ($value !== null && \Schema::hasColumn($model->getTable(), $column)) {
            $model->{$column} = $value;
        }
    }

    public function accountDestroy(Request $request)
    {

        if (\Auth::user()->can('delete journal entry')) {
            JournalItem::where('id', '=', $request->id)->delete();

            return redirect()->back()->with('success', __('Journal entry account successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function journalDestroy($item_id)
    {
        if (\Auth::user()->can('delete journal entry')) {
            $journal = JournalItem::find($item_id);
            $journal->delete();

            return redirect()->back()->with('success', __('Journal account successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    //create new voucher
    public function createVoucher()
    {
        if (\Auth::user()->can('create journal entry')) {
            return view('journalEntry.createvoucher', $this->voucherFormData());
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }
    public function getVoucherNumber(Request $request)
    {
        $user = \Auth::user();
        $branchId = $request->branch_id ?: $user->ownedId();
        $type = $this->normalizeVoucherType($request->voucher_type);

        $latest = JournalEntry::where('owned_by', $branchId)
            ->where('voucher_type', $type)
            ->latest()
            ->first();

        $nextId = $latest ? $latest->journal_id + 1 : 1;
        $methodMap = [
            'BRV' => 'BRVNumberFormat',
            'BPV' => 'BPVNumberFormat',
            'CRV' => 'CRVNumberFormat',
            'CPV' => 'CPVNumberFormat',
        ];
        $method = $methodMap[$type] ?? 'journalNumberFormat';
        $voucherNo = $user->$method($nextId);
        return response()->json([
            'voucher_number' => $voucherNo
        ]);
    }
}
