<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountParent;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\ProductServiceCategory;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;

class HeadImprestVoucherController extends Controller
{
    private function findHeadImprestVoucher($id): ?JournalEntry
    {
        return JournalEntry::whereKey($id)
            ->where('category', 'Head Imprest')
            ->where('created_by', \Auth::user()->creatorId())
            ->first();
    }

    private function setModelValueIfColumn($model, $column, $value): void
    {
        if ($value !== null && \Schema::hasColumn($model->getTable(), $column)) {
            $model->{$column} = $value;
        }
    }

    private function setNullableModelValueIfColumn($model, $column, $value): void
    {
        if (\Schema::hasColumn($model->getTable(), $column)) {
            $model->{$column} = $value === '' ? null : $value;
        }
    }

    private function storeJournalAttachment(Request $request, $oldPath = null)
    {
        if (!$request->hasFile('attachment')) {
            return $oldPath;
        }

        if ($oldPath && file_exists(public_path($oldPath))) {
            @unlink(public_path($oldPath));
        }

        $file = $request->file('attachment');
        $dir = 'uploads/journal_attachments';
        if (!is_dir(public_path($dir))) {
            mkdir(public_path($dir), 0755, true);
        }
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $file->getClientOriginalName());
        $fileName = time() . '_' . uniqid() . '_' . $safeName;
        $file->move(public_path($dir), $fileName);

        return $dir . '/' . $fileName;
    }

    private function normalizeVoucherType($voucherType): string
    {
        $type = strtoupper($voucherType ?: 'JV');
        $allowedTypes = ['JV', 'CPV', 'BPV', 'CRV', 'BRV'];

        return in_array($type, $allowedTypes) ? $type : 'JV';
    }

    private function voucherNumber($voucherType = 'JV', $ownedBy = null): int
    {
        $latest = JournalEntry::where('owned_by', '=', $ownedBy ?: \Auth::user()->ownedId())
            ->where('voucher_type', $this->normalizeVoucherType($voucherType))
            ->latest()
            ->first();

        return $latest ? $latest->journal_id + 1 : 1;
    }

    private function headImprestBankAccountQuery($branchId = null)
    {
        $user = \Auth::user();
        $query = BankAccount::query()
            ->where('type', 'head_imprest')
            ->where('created_by', $user->creatorId());

        if (\Schema::hasColumn('bank_accounts', 'owned_by')) {
            if ($user->type == 'branch') {
                $query->where('owned_by', $user->ownedId());
            } elseif ($branchId !== null && $branchId !== '') {
                $query->where('owned_by', $branchId);
            }
        }

        return $query;
    }

    private function headImprestBankOptions($branchId = null)
    {
        return $this->headImprestBankAccountQuery($branchId)
            ->orderBy('bank_name')
            ->orderBy('holder_name')
            ->get()
            ->mapWithKeys(function ($bank) {
                $labelParts = array_filter([
                    $bank->bank_name ?? null,
                    $bank->holder_name ?? null,
                    $bank->account_number ?? null,
                    '(' . __('Bal') . ': ' . \Auth::user()->priceFormat($bank->opening_balance) . ')',
                ]);

                return [$bank->id => implode(' - ', $labelParts)];
            });
    }

    private function expenseVoucherBankAmount(JournalEntry $journal): float
    {
        if (!empty($journal->amount)) {
            return (float) $journal->amount;
        }

        return (float) $journal->items()
            ->where('credit', '>', 0)
            ->sum('credit');
    }

    private function hasSufficientHeadImprestBalance(BankAccount $bankAccount, $amount): bool
    {
        return round((float) $bankAccount->opening_balance, 2) >= round((float) $amount, 2);
    }

    private function reduceHeadImprestBankBalance(BankAccount $bankAccount, $amount): void
    {
        $bankAccount->opening_balance = round((float) $bankAccount->opening_balance - (float) $amount, 2);
        $bankAccount->save();
    }

    private function restoreHeadImprestBankBalance(BankAccount $bankAccount, $amount): void
    {
        $bankAccount->opening_balance = round((float) $bankAccount->opening_balance + (float) $amount, 2);
        $bankAccount->save();
    }

    private function branchOptions($includeAll = false)
    {
        $user = \Auth::user();

        if ($user->type == 'company') {
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend($includeAll ? __('All Branches') : $user->name, $includeAll ? '' : $user->id);

            return $branches;
        }

        return User::where('id', '=', $user->ownedId())->get()->pluck('name', 'id');
    }

    private function headImprestExpenseAccountIds(): array
    {
        $creatorId = \Auth::user()->creatorId();
        $expenseType = ChartOfAccountType::where('created_by', $creatorId)
            ->where('name', 'Expenses')
            ->first();

        if (!$expenseType) {
            return [];
        }

        $subType = ChartOfAccountSubType::where('created_by', $creatorId)
            ->where('type', $expenseType->id)
            ->where('name', 'Head Imprest')
            ->first();

        if (!$subType) {
            return [];
        }

        $allAccountIds = ChartOfAccount::where('created_by', $creatorId)
            ->where('type', $expenseType->id)
            ->where('sub_type', $subType->id)
            ->pluck('id')
            ->toArray();

        $currentParentIds = $allAccountIds;
        while (!empty($currentParentIds)) {
            $parentRecordIds = ChartOfAccountParent::whereIn('account', $currentParentIds)->pluck('id')->toArray();
            if (empty($parentRecordIds)) {
                break;
            }

            $childIds = ChartOfAccount::where('created_by', $creatorId)
                ->whereIn('parent', $parentRecordIds)
                ->pluck('id')
                ->toArray();

            $newChildIds = array_diff($childIds, $allAccountIds);
            if (empty($newChildIds)) {
                break;
            }

            $allAccountIds = array_merge($allAccountIds, $newChildIds);
            $currentParentIds = $newChildIds;
        }

        return $allAccountIds;
    }

    private function headImprestExpenseAccountOptions(): array
    {
        $allAccountIds = $this->headImprestExpenseAccountIds();
        if (empty($allAccountIds)) {
            return [];
        }

        $accountsList = ChartOfAccount::whereIn('id', $allAccountIds)->get();
        $parentRecordIds = $accountsList->pluck('parent')->filter()->unique()->toArray();
        $parentRecords = ChartOfAccountParent::whereIn('id', $parentRecordIds)->get()->pluck('account', 'id')->toArray();
        $byParent = [];

        foreach ($accountsList as $account) {
            $parentCoaId = 0;
            if ($account->parent > 0 && isset($parentRecords[$account->parent])) {
                $parentCoaId = $parentRecords[$account->parent];
            }
            $byParent[$parentCoaId][] = $account;
        }

        $allAccountIdsSet = array_flip($allAccountIds);
        $roots = [];
        foreach ($accountsList as $account) {
            $parentCoaId = 0;
            if ($account->parent > 0 && isset($parentRecords[$account->parent])) {
                $parentCoaId = $parentRecords[$account->parent];
            }
            if ($parentCoaId == 0 || !isset($allAccountIdsSet[$parentCoaId])) {
                $roots[] = $account;
            }
        }

        $treeOptions = [];
        $formatTree = function ($parentItems, $depth = 0) use (&$formatTree, $byParent, &$treeOptions) {
            foreach ($parentItems as $item) {
                $indent = str_repeat('-- ', $depth);
                $treeOptions[$item->id] = $indent . $item->code . ' - ' . $item->name;

                if (isset($byParent[$item->id])) {
                    $formatTree($byParent[$item->id], $depth + 1);
                }
            }
        };

        $formatTree($roots);

        return $treeOptions;
    }

    public function getHeadImprestBankAccounts(Request $request)
    {
        if (!\Auth::user()->can('manage head imprest') && !\Auth::user()->can('create head imprest') && !\Auth::user()->can('edit head imprest')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $banks = $this->headImprestBankAccountQuery($request->branch_id)
            ->orderBy('bank_name')
            ->orderBy('holder_name')
            ->get(['id', 'bank_name', 'holder_name', 'account_number', 'opening_balance']);

        return response()->json([
            'success' => true,
            'banks' => $banks->map(function ($bank) {
                $labelParts = array_filter([
                    $bank->bank_name ?? null,
                    $bank->holder_name ?? null,
                    $bank->account_number ?? null,
                    '(' . __('Bal') . ': ' . \Auth::user()->priceFormat($bank->opening_balance) . ')',
                ]);

                return [
                    'id' => $bank->id,
                    'text' => implode(' - ', $labelParts),
                    'balance' => (float) $bank->opening_balance,
                    'formatted_balance' => \Auth::user()->priceFormat($bank->opening_balance),
                ];
            })->values(),
            'total_balance' => (float) $banks->sum('opening_balance'),
            'formatted_total_balance' => \Auth::user()->priceFormat($banks->sum('opening_balance')),
        ]);
    }

    public function create()
    {
        if (!\Auth::user()->can('create head imprest')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $user = \Auth::user();
        $ownedId = $user->ownedId();
        $bankAccounts = $this->headImprestBankOptions($ownedId);
        $chartAccounts = $this->headImprestExpenseAccountOptions();
        $branches = $this->branchOptions(false);
        $voucherCategoryTypes = ProductServiceCategory::where('type', 'voucher')
            ->where('created_by', $user->creatorId())
            ->orderBy('name')
            ->pluck('name', 'id');
        $voucherCategoryTypes->prepend('Select Voucher Category Type', '');
        $displayVoucherNumber = $user->BPVNumberFormat($this->voucherNumber('BPV', $ownedId));

        if (request()->ajax()) {
            return view('headImprest.create-modal', compact(
                'bankAccounts',
                'chartAccounts',
                'branches',
                'voucherCategoryTypes',
                'displayVoucherNumber'
            ));
        }

        return view('headImprest.create', compact(
            'bankAccounts',
            'chartAccounts',
            'branches',
            'voucherCategoryTypes',
            'displayVoucherNumber'
        ));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('create head imprest')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 401);
        }

        $validator = \Validator::make($request->all(), [
            'date' => 'required|date',
            'bank_id' => 'required|integer',
            'expense_account_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'attachment' => 'required|file|max:2560',
            'description' => 'nullable|string',
            'payment_mode' => 'required|string|in:dd,cd,bank-transfer,chq,others',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->getMessageBag()->first()], 422);
        }

        $ownedBy = $request->branches ?: \Auth::user()->ownedId();
        $bankAccount = $this->headImprestBankAccountQuery($ownedBy)->where('id', $request->bank_id)->first();
        if (!$bankAccount) {
            return response()->json(['success' => false, 'message' => __('Selected bank account is invalid or not of type Head Imprest.')], 422);
        }
        if (empty($bankAccount->chart_account_id)) {
            return response()->json(['success' => false, 'message' => __('Selected bank account is not linked with a chart of account.')], 422);
        }
        if (!in_array((int) $request->expense_account_id, array_map('intval', $this->headImprestExpenseAccountIds()), true)) {
            return response()->json(['success' => false, 'message' => __('Selected expense account is invalid or not of type Head Imprest.')], 422);
        }

        \DB::beginTransaction();
        try {
            $bankAccount = $this->headImprestBankAccountQuery($ownedBy)
                ->where('id', $request->bank_id)
                ->lockForUpdate()
                ->first();

            if (!$bankAccount || empty($bankAccount->chart_account_id)) {
                \DB::rollBack();
                return response()->json(['success' => false, 'message' => __('Selected bank account is invalid or not linked with a chart of account.')], 422);
            }

            if (!$this->hasSufficientHeadImprestBalance($bankAccount, $request->amount)) {
                \DB::rollBack();
                return response()->json(['success' => false, 'message' => __('Expense amount cannot be greater than the selected Head Imprest bank balance.')], 422);
            }

            $attachment = $this->storeJournalAttachment($request);
            $voucherType = ($request->payment_mode === 'cd') ? 'CPV' : 'BPV';
            $headPressCategory = ProductServiceCategory::where('created_by', \Auth::user()->creatorId())
                ->where('type', 'voucher')
                ->where('name', 'Head Imprest')
                ->first();
            $transactionDateTime = \Carbon\Carbon::parse($request->date)->setTimeFrom(now());

            $journal = new JournalEntry();
            $journal->journal_id = $this->voucherNumber($voucherType, $ownedBy);
            $journal->date = $request->date;
            $journal->reference = $request->reference;
            $journal->description = $request->description;
            $journal->voucher_type = $voucherType;
            $this->setModelValueIfColumn($journal, 'payment_mode', $request->payment_mode);
            $this->setModelValueIfColumn($journal, 'amount', $request->amount);
            $this->setNullableModelValueIfColumn($journal, 'bank_id', $bankAccount->id);
            $journal->category = 'Head Imprest';
            $journal->category_type_id = $headPressCategory ? $headPressCategory->id : null;
            $journal->attachment = $attachment;
            $journal->created_by = \Auth::user()->creatorId();
            $journal->owned_by = $ownedBy;
            $this->setModelValueIfColumn($journal, 'status', 'Draft');
            $this->setNullableModelValueIfColumn($journal, 'added_at', now());
            $journal->added_by = \Auth::id();
            $journal->added_at = now();
            $journal->created_at = $transactionDateTime;
            $journal->updated_at = $transactionDateTime;
            $journal->save();

            $debitItem = new JournalItem();
            $debitItem->journal = $journal->id;
            $debitItem->account = $request->expense_account_id;
            $debitItem->debit = $request->amount;
            $debitItem->credit = 0;
            $debitItem->description = $request->description;
            $debitItem->branch_id = $journal->owned_by;
            $debitItem->added_by = \Auth::id();
            $debitItem->created_at = $transactionDateTime;
            $debitItem->updated_at = $transactionDateTime;
            $debitItem->added_at = now();
            $debitItem->save();

            $creditItem = new JournalItem();
            $creditItem->journal = $journal->id;
            $creditItem->account = $bankAccount->chart_account_id;
            $creditItem->debit = 0;
            $creditItem->credit = $request->amount;
            $creditItem->description = $request->description;
            $creditItem->added_by = \Auth::id();
            $creditItem->branch_id = $journal->owned_by;
            $creditItem->added_at = now();
            $creditItem->save();

            $this->reduceHeadImprestBankBalance($bankAccount, $request->amount);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => __('Expense Voucher successfully created.'),
                'redirect' => route('journal-entry.index'),
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['success' => false, 'message' => __('Something went wrong: ') . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        if (!\Auth::user()->can('show head imprest')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $journalEntry = $this->findHeadImprestVoucher($id);
        if (!$journalEntry) {
            return redirect()->back()->with('error', __('Voucher not found or permission denied.'));
        }

        $accounts = $journalEntry->accounts;
        $settings = Utility::settings();

        return view('headImprest.show', compact('journalEntry', 'accounts', 'settings'));
    }

    public function edit($id)
    {
        if (!\Auth::user()->can('edit head imprest')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $journalEntry = $this->findHeadImprestVoucher($id);
        if (!$journalEntry) {
            return response()->json(['error' => __('Voucher not found or permission denied.')], 404);
        }
        if ($journalEntry->status == 'Approved' || $journalEntry->status == 'Posted') {
            return response()->json(['error' => __('Approved or Posted vouchers cannot be edited.')], 403);
        }

        $branches = $this->branchOptions(false);
        $bankAccounts = $this->headImprestBankOptions($journalEntry->owned_by);
        $treeOptions = $this->headImprestExpenseAccountOptions();
        $debitItem = $journalEntry->items()->where('debit', '>', 0)->first();
        $selectedExpenseAccountId = $debitItem ? $debitItem->account : null;

        return view('headImprest.edit-modal', compact('journalEntry', 'branches', 'bankAccounts', 'treeOptions', 'selectedExpenseAccountId'));
    }

    public function update(Request $request, $id)
    {
        if (!\Auth::user()->can('edit head imprest')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $journal = $this->findHeadImprestVoucher($id);
        if (!$journal) {
            return response()->json(['success' => false, 'message' => __('Voucher not found or permission denied.')], 404);
        }
        if ($journal->status == 'Approved' || $journal->status == 'Posted') {
            return response()->json(['success' => false, 'message' => __('Approved or Posted vouchers cannot be edited.')], 403);
        }

        $validator = \Validator::make($request->all(), [
            'date' => 'required|date',
            'bank_id' => 'required|integer',
            'expense_account_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'attachment' => 'nullable|file|max:2560',
            'description' => 'nullable|string',
            'payment_mode' => 'required|string|in:dd,cd,bank-transfer,chq,others',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->getMessageBag()->first()], 422);
        }

        $ownedBy = $request->branches ?: \Auth::user()->ownedId();
        $bankAccount = $this->headImprestBankAccountQuery($ownedBy)->where('id', $request->bank_id)->first();
        if (!$bankAccount) {
            return response()->json(['success' => false, 'message' => __('Selected bank account is invalid or not of type Head Imprest.')], 422);
        }
        if (!in_array((int) $request->expense_account_id, array_map('intval', $this->headImprestExpenseAccountIds()), true)) {
            return response()->json(['success' => false, 'message' => __('Selected expense account is invalid or not of type Head Imprest.')], 422);
        }

        \DB::beginTransaction();
        try {
            $oldBankId = $journal->bank_id;
            $oldOwnedBy = $journal->owned_by;
            $oldAmount = $this->expenseVoucherBankAmount($journal);

            if ($oldBankId && $oldAmount > 0) {
                $oldBankAccount = $this->headImprestBankAccountQuery($oldOwnedBy)
                    ->where('id', $oldBankId)
                    ->lockForUpdate()
                    ->first();

                if (!$oldBankAccount) {
                    \DB::rollBack();
                    return response()->json(['success' => false, 'message' => __('Previous Head Imprest bank account could not be found for balance reversal.')], 422);
                }

                $this->restoreHeadImprestBankBalance($oldBankAccount, $oldAmount);
            }

            $bankAccount = $this->headImprestBankAccountQuery($ownedBy)
                ->where('id', $request->bank_id)
                ->lockForUpdate()
                ->first();

            if (!$bankAccount || empty($bankAccount->chart_account_id)) {
                \DB::rollBack();
                return response()->json(['success' => false, 'message' => __('Selected bank account is invalid or not linked with a chart of account.')], 422);
            }

            if (!$this->hasSufficientHeadImprestBalance($bankAccount, $request->amount)) {
                \DB::rollBack();
                return response()->json(['success' => false, 'message' => __('Expense amount cannot be greater than the selected Head Imprest bank balance.')], 422);
            }

            if ($request->hasFile('attachment')) {
                $journal->attachment = $this->storeJournalAttachment($request);
            }

            $voucherType = ($request->payment_mode === 'cd') ? 'CPV' : 'BPV';
            if ($voucherType !== $journal->voucher_type || (int) $ownedBy !== (int) $journal->owned_by) {
                $journal->journal_id = $this->voucherNumber($voucherType, $ownedBy);
            }

            $transactionDateTime = \Carbon\Carbon::parse($request->date)->setTimeFrom(now());
            $journal->date = $request->date;
            $journal->reference = $request->reference;
            $journal->description = $request->description;
            $journal->voucher_type = $voucherType;
            $this->setModelValueIfColumn($journal, 'payment_mode', $request->payment_mode);
            $this->setModelValueIfColumn($journal, 'amount', $request->amount);
            $this->setNullableModelValueIfColumn($journal, 'bank_id', $bankAccount->id);
            $journal->owned_by = $ownedBy;
            $journal->updated_by = \Auth::id();
            $journal->updated_at = now();
            $journal->created_at = $transactionDateTime;
            $journal->save();

            JournalItem::where('journal', $journal->id)->delete();

            $debitItem = new JournalItem();
            $debitItem->journal = $journal->id;
            $debitItem->account = $request->expense_account_id;
            $debitItem->debit = $request->amount;
            $debitItem->credit = 0;
            $debitItem->description = $request->description;
            $debitItem->branch_id = $journal->owned_by;
            $debitItem->added_by = \Auth::id();
            $debitItem->updated_by = \Auth::id();
            $debitItem->created_at = $transactionDateTime;
            $debitItem->updated_at = $transactionDateTime;
            $debitItem->added_at = now();
            $debitItem->save();

            $creditItem = new JournalItem();
            $creditItem->journal = $journal->id;
            $creditItem->account = $bankAccount->chart_account_id;
            $creditItem->debit = 0;
            $creditItem->credit = $request->amount;
            $creditItem->description = $request->description;
            $creditItem->branch_id = $journal->owned_by;
            $creditItem->added_by = \Auth::id();
            $creditItem->updated_by = \Auth::id();
            $creditItem->created_at = $transactionDateTime;
            $creditItem->updated_at = $transactionDateTime;
            $creditItem->added_at = now();
            $creditItem->save();

            $this->reduceHeadImprestBankBalance($bankAccount, $request->amount);

            \DB::commit();

            return response()->json([
                'status' => 'success',
                'success' => true,
                'message' => __('Voucher successfully updated.'),
                'redirect' => route('head-imprest-vouchers.index'),
            ]);
        } catch (\Exception $e) {
            \DB::rollback();
            return response()->json(['success' => false, 'message' => __('Something went wrong: ') . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        if (!\Auth::user()->can('delete head imprest')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        \DB::beginTransaction();
        try {
            $journal = JournalEntry::whereKey($id)
                ->where('category', 'Head Imprest')
                ->where('created_by', \Auth::user()->creatorId())
                ->lockForUpdate()
                ->first();

            if (!$journal) {
                \DB::rollback();
                return redirect()->back()->with('error', __('Voucher not found or permission denied.'));
            }

            if (in_array($journal->status, ['Approved', 'Posted', 'Reversed'], true)) {
                \DB::rollback();
                return redirect()->back()->with('error', __('Approved, posted or reversed vouchers cannot be deleted.'));
            }

            $amount = $this->expenseVoucherBankAmount($journal);
            $bankAccount = BankAccount::whereKey($journal->bank_id)
                ->where('type', 'head_imprest')
                ->lockForUpdate()
                ->first();

            if ($amount > 0 && !$bankAccount) {
                \DB::rollback();
                return redirect()->back()->with('error', __('Head Imprest bank account could not be found for balance restoration.'));
            }

            if ($bankAccount && $amount > 0) {
                $this->restoreHeadImprestBankBalance($bankAccount, $amount);
            }

            JournalItem::where('journal', $journal->id)->delete();
            $journal->delete();

            \DB::commit();

            return redirect()->route('head-imprest-vouchers.index')->with('success', __('Head Imprest voucher successfully deleted.'));
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', __('Something went wrong: ') . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        if (!\Auth::user()->can('manage head imprest')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $user = \Auth::user();
        $creatorId = $user->creatorId();
        $fromDate = $request->from_date ?: now()->subDays(30)->toDateString();
        $toDate = $request->to_date ?: now()->toDateString();
        $voucherSeriesFilter = $request->filled('voucher_series') ? strtoupper($request->voucher_series) : 'MANUAL';
        $selectedBranchId = $user->type == 'branch' ? $user->ownedId() : $request->branch_id;
        $selectedBankId = $request->bank_id;

        $query = JournalEntry::query()
            ->where('created_by', '=', $creatorId)
            ->where('category', 'Head Imprest');

        if ($user->type == 'branch') {
            $query->where('owned_by', '=', $user->ownedId());
        } elseif (!empty($selectedBranchId)) {
            $query->where('owned_by', '=', $selectedBranchId);
        }

        $query->whereDate('date', '>=', $fromDate)
            ->whereDate('date', '<=', $toDate);

        if (!empty($voucherSeriesFilter)) {
            if ($voucherSeriesFilter === 'SYSTEM') {
                $query->where(function ($seriesQuery) {
                    $seriesQuery->where('voucher_series', 'SYSTEM')
                        ->orWhereNull('voucher_series')
                        ->orWhere('voucher_series', '');
                });
            } elseif ($voucherSeriesFilter === 'MANUAL') {
                $query->where('voucher_series', 'MANUAL');
            }
        }

        if (!empty($request->expense_account_id)) {
            $query->whereHas('items', function ($itemQuery) use ($request) {
                $itemQuery->where('account', $request->expense_account_id);
            });
        }

        if (!empty($selectedBankId)) {
            $selectedBank = $this->headImprestBankAccountQuery($selectedBranchId)
                ->where('id', $selectedBankId)
                ->first();

            $query->where(function ($bankFilter) use ($selectedBankId, $selectedBank) {
                $bankFilter->where('bank_id', $selectedBankId);

                if ($selectedBank && $selectedBank->chart_account_id) {
                    $bankFilter->orWhereHas('items', function ($itemQuery) use ($selectedBank) {
                        $itemQuery->where('account', $selectedBank->chart_account_id)
                            ->where('credit', '>', 0);
                    });
                }
            });
        }

        $journalEntries = $query->orderBy('id', 'desc')->get();
        $branches = $this->branchOptions(true);
        $chartAccounts = ['' => __('All Expense Heads')] + $this->headImprestExpenseAccountOptions();
        $bankAccounts = ['' => __('All Head Imprest Banks')] + $this->headImprestBankOptions($selectedBranchId)->toArray();
        $bankQuery = $this->headImprestBankAccountQuery($selectedBranchId);
        if (!empty($selectedBankId)) {
            $bankQuery->where('id', $selectedBankId);
        }
        $totalBankBalance = $bankQuery->sum('opening_balance');

        return view('headImprest.index', compact('journalEntries', 'totalBankBalance', 'branches', 'chartAccounts', 'bankAccounts', 'fromDate', 'toDate', 'voucherSeriesFilter', 'selectedBranchId', 'selectedBankId'));
    }

    public function approve($id)
    {
        if (!\Auth::user()->can('approve head imprest') || \Auth::user()->type != 'company') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $journal = $this->findHeadImprestVoucher($id);
        if (!$journal) {
            return redirect()->back()->with('error', __('Voucher not found or permission denied.'));
        }
        if ($journal->status == 'Approved') {
            return redirect()->back()->with('error', __('Voucher is already approved.'));
        }

        \DB::beginTransaction();
        try {
            $journal->status = 'Approved';
            $this->setNullableModelValueIfColumn($journal, 'approved_by', \Auth::id());
            $this->setNullableModelValueIfColumn($journal, 'approved_at', now());
            $journal->save();

            \DB::commit();
            return redirect()->back()->with('success', __('Voucher successfully approved.'));
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', __('Something went wrong: ') . $e->getMessage());
        }
    }

    public function sendToHO($id)
    {
        if (!\Auth::user()->can('submit head imprest')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $journal = $this->findHeadImprestVoucher($id);
        if (!$journal) {
            return redirect()->back()->with('error', __('Voucher not found or permission denied.'));
        }
        if (in_array($journal->status, ['Approved', 'Posted', 'Reversed'])) {
            return redirect()->back()->with('error', __('Voucher is already approved, posted or reversed.'));
        }

        $journal->status = 'Submitted';
        $journal->save();

        return redirect()->back()->with('success', __('Voucher successfully sent to HO for approval.'));
    }
}
