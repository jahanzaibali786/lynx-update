<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Exports\VoucherPrintExport;
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
use App\Models\ProductServiceCategory;
use App\Http\Requests\StoreJournalVoucherRequest;
use App\Services\ChartOfAccountOptionsService;
use App\Services\JournalVoucherService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class JournalEntryController extends Controller
{

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage journal entry') || \Auth::user()->can('manage journal voucher')) {
            $canManageAllVoucherTypes = \Auth::user()->can('manage journal entry');
            $startDate = $request->start_date ?: now()->subDays(30)->toDateString();
            $endDate = $request->end_date ?: now()->toDateString();
            $voucherTypeFilter = $request->has('voucher_type') ? strtoupper((string) $request->voucher_type) : 'JV';
            $voucherSeriesFilter = $request->filled('voucher_series') ? strtoupper($request->voucher_series) : 'MANUAL';

            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $query = JournalEntry::where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = JournalEntry::where('owned_by', '=', \Auth::user()->ownedId());
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            if (!$canManageAllVoucherTypes) {
                $voucherTypeFilter = 'JV';
            }
            if (!empty($voucherTypeFilter)) {
                $query->where('voucher_type', $voucherTypeFilter);
            }
            $query->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate);

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
            $journalEntries = $query->orderBy('id', 'desc')->paginate(25);
            // dd($journalEntries);
            return view('journalEntry.index', compact('journalEntries', 'branches', 'startDate', 'endDate', 'voucherTypeFilter', 'voucherSeriesFilter', 'canManageAllVoucherTypes'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function create()
    {
        if ($this->canCreateJournalVoucher()) {
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

    public function voucherPrint(JournalEntry $journalEntry)
    {
        if (!$this->canUseJournalEntry($journalEntry, 'print', 'show journal entry')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        if ($journalEntry->created_by != \Auth::user()->creatorId()) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $journalEntry->load(['accounts.accounts', 'accounts.user', 'branch', 'bank']);
        $accounts = $journalEntry->accounts;
        $voucherType = strtoupper($journalEntry->voucher_type ?? 'JV');
        $voucherNumber = $this->formatVoucherNumber($journalEntry->journal_id, $voucherType, $journalEntry);
        $totalDebit = $accounts->sum('debit');
        $totalCredit = $accounts->sum('credit');
        $voucherAmount = max($totalDebit, $totalCredit);
        $paymentInfo = $this->voucherPaymentInfo($journalEntry, $accounts, $voucherType);
        $payeeInfo = $this->voucherPayeeInfo($journalEntry, $accounts);
        $amountWords = $this->amountToWords($voucherAmount);
        $voucherLogo = $this->voucherLogo();
        $watermarkLogo = $this->voucherWatermarkLogo();

        $voucherTitleMap = [
            'JV' => 'JOURNAL VOUCHER',
            'BPV' => 'BANK PAYMENT VOUCHER',
            'BRV' => 'BANK RECIPT VOUCHER',
            'CPV' => 'CASH PAYMENT VOUCHER',
            'CRV' => 'CASH RECIPT VOUCHER',
        ];

        $export = new VoucherPrintExport([
            'voucher_type' => $voucherType,
            'voucher_title' => $voucherTitleMap[$voucherType] ?? ($voucherType . ' VOUCHER'),
            'voucher_number' => $voucherNumber,
            'date' => \Carbon\Carbon::parse($journalEntry->date)->format('M,d Y D'),
            'accounts' => $accounts->map(function ($account) {
                return [
                    'account_head' => trim((optional($account->accounts)->code ? optional($account->accounts)->code . ': ' : '') . (optional($account->accounts)->name ?? '')),
                    'description' => $account->description ?: ($account->memo ?: ''),
                    'debit' => (float) $account->debit > 0 ? number_format($account->debit, 2) : '',
                    'credit' => (float) $account->credit > 0 ? number_format($account->credit, 2) : '',
                ];
            })->values()->all(),
            'total_debit' => number_format($totalDebit, 2),
            'total_credit' => number_format($totalCredit, 2),
            'payee' => $payeeInfo,
            'payment' => array_merge($paymentInfo, [
                'payment_date' => !empty($paymentInfo['payment_date'])
                    ? \Carbon\Carbon::parse($paymentInfo['payment_date'])->format('M,d Y D')
                    : '',
            ]),
            'receiver' => [
                'name' => $journalEntry->receiver_name ?? '',
                'cnic' => $journalEntry->receiver_cnic ?? '',
                'contact' => $journalEntry->receiver_contact ?? '',
                'email' => $journalEntry->receiver_email ?? '',
            ],
            'amount_words' => $amountWords,
            'note' => $journalEntry->description ?? '',
            'header_logo' => $voucherLogo,
            'watermark_logo' => $watermarkLogo,
        ]);

        return $export->view();
    }

    private function renderVoucherPdf(VoucherPrintExport $export, string $watermarkLogo): string
    {
        $tempDir = storage_path('app/mpdf-tmp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $pdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [210, 297],
            'orientation' => 'P',
            'tempDir' => $tempDir,
            'margin_left' => 6.35,
            'margin_right' => 6.35,
            'margin_top' => 6.35,
            'margin_bottom' => 6.35,
        ]);
        $pdf->SetDisplayMode('fullpage');
        $pdf->shrink_tables_to_fit = 1;

        if (!empty($watermarkLogo) && file_exists($watermarkLogo)) {
            $pdf->SetWatermarkImage($watermarkLogo, 0.4, [85, 66], [62, 106]);
            $pdf->showWatermarkImage = true;
            $pdf->watermarkImgBehind = true;
        }

        $pdf->SetTitle('Voucher Print');
        $pdf->WriteHTML($export->view()->render());

        return $pdf->Output('', 'S');
    }


    public function store(StoreJournalVoucherRequest $request, JournalVoucherService $journalVoucherService)
    {
        try {
            $journal = $journalVoucherService->create($request);

            return response()->json([
                'status' => 'success',
                'message' => __('Voucher successfully created.'),
                'redirect' => route('journal-entry.show', $journal->id),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        } catch (\Throwable $e) {
            \Log::error('Journal voucher create failed', [
                'error' => $e->getMessage(),
                'user_id' => \Auth::id(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => __('Something went wrong: ') . $e->getMessage(),
            ], 500);
        }
    }


    public function show(JournalEntry $journalEntry)
    {
        if ($this->canUseJournalEntry($journalEntry, 'show', 'show journal entry')) {
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
        if ($this->canUseJournalEntry($journalEntry, 'edit', 'edit journal entry')) {
            if ($journalEntry->created_by != \Auth::user()->creatorId() || $journalEntry->is_system_generated != 0) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

            $formData = $this->voucherFormData();
            $journalItems = $this->voucherItemsForEditor($journalEntry);
            $voucherNumber = $this->formatVoucherNumber($journalEntry->journal_id, $journalEntry->voucher_type, $journalEntry);

            return view('journalEntry.createvoucher', array_merge($formData, compact('journalEntry', 'journalItems', 'voucherNumber')));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }


    public function update(Request $request, JournalEntry $journalEntry)
    {
        if ($this->canUseJournalEntry($journalEntry, 'edit', 'edit journal entry')) {
            if ($journalEntry->created_by == \Auth::user()->creatorId()) {
                if ($journalEntry->is_system_generated != 0) {
                    return response()->json(['status' => 'error', 'message' => __('Permission denied.')], 403);
                }
                \DB::beginTransaction();
                try {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'date' => 'required',
                            'voucher_type' => 'nullable|in:jv,cpv,bpv,crv,brv,JV,CPV,BPV,CRV,BRV',
                            'status' => 'nullable|in:Draft,Submitted,Approved,Posted,Reversed',
                            'bank_id' => 'nullable|integer',
                            'payment_mode' => 'nullable|string|max:50',
                            'cheque_no' => 'nullable|string|max:100',
                            'cheque_date' => 'nullable|date',
                            'user_type' => 'nullable|in:Customer,Vender,Vendor,Employee,Student',
                            'user_id' => 'nullable|integer',
                            'transaction_no' => 'nullable|string|max:150',
                            'reversed_entry_id' => 'nullable|integer',
                            'reversed_timestamp' => 'nullable|date',
                            'attachment' => 'nullable|file|max:5120',
                            'accounts' => 'required|array|min:1',
                            'category_type_id' => 'nullable|integer',
                            'payee_account_title' => 'nullable|string|max:191',
                            'payee_account_no' => 'nullable|string|max:191',
                            'payee_contact' => 'nullable|string|max:191',
                            'payee_email' => 'nullable|email|max:191',
                            'payee_cnic' => 'nullable|string|max:191',
                            'receiver_name' => 'nullable|string|max:191',
                            'receiver_cnic' => 'nullable|string|max:191',
                            'receiver_contact' => 'nullable|string|max:191',
                            'receiver_email' => 'nullable|email|max:191',
                            'payment_date' => 'nullable|date',
                            'voucher_series' => 'nullable|in:SYSTEM,MANUAL,system,manual',
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
                    
                    $series = strtoupper($request->voucher_series ?: ($journalEntry->voucher_series ?: 'SYSTEM'));
                    
                    $seriesChanged = ($series !== $journalEntry->voucher_series);
                    $typeChanged = ($voucherType !== $journalEntry->voucher_type);
                    $branchChanged = ((int) $ownedBy !== (int) $journalEntry->owned_by);

                    if ($seriesChanged || $typeChanged || ($branchChanged && $series === 'SYSTEM')) {
                        $journalEntry->voucher_series = $series;
                        if ($series === 'MANUAL') {
                            if ($journalEntry->voucher_series !== 'MANUAL' || $typeChanged || empty($journalEntry->manual_reference)) {
                                $last = JournalEntry::where('voucher_type', $voucherType)
                                    ->where('voucher_series', 'MANUAL')
                                    ->where('created_by', \Auth::user()->creatorId())
                                    ->max('manual_series_no');
                                $next = $last + 1;
                                $journalEntry->manual_series_no = $next;
                                $journalEntry->manual_reference = 'M' . $voucherType . '-' . str_pad($next, 6, '0', STR_PAD_LEFT);
                                $journalEntry->journal_id = $next;
                            }
                        } else {
                            $journalEntry->manual_series_no = null;
                            $journalEntry->manual_reference = null;
                            $journalEntry->journal_id = $this->voucherNumber($voucherType, $ownedBy);
                        }
                    }

                    $transactionDateTime = \Carbon\Carbon::parse($request->date)->setTimeFrom(now());
                    $journalEntry->date = $request->date;
                    $journalEntry->reference = $request->reference;
                    $journalEntry->description = $request->narration ?? $request->description;
                    $journalEntry->voucher_type = $voucherType;
                    $journalEntry->owned_by = $ownedBy;
                    $journalEntry->created_by = \Auth::user()->creatorId();
                    $this->setModelValueIfColumn($journalEntry, 'amount', $request->amount);
                    $this->setModelValueIfColumn($journalEntry, 'category', 'manual');
                    $journalEntry->category_type_id = $request->category_type_id;
                    $this->fillJournalEntryExtraFields($journalEntry, $request, true);
                    $journalEntry->created_at = $transactionDateTime;
                    $journalEntry->updated_at = $transactionDateTime;
                    $journalEntry->save();

                    $isApproved = $journalEntry->status == 'Approved';
                    if ($isApproved) {
                        foreach ($journalEntry->items as $item) {
                            $this->reverseBankAccountBalance($item->account, $item->debit, $item->credit);
                        }
                    }

                    JournalItem::where('journal', $journalEntry->id)->delete();
                    $this->saveVoucherItems($journalEntry, $accounts, $ownedBy, $voucherType, $isApproved);

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


        if ($this->canUseJournalEntry($journalEntry, 'delete', 'delete journal entry')) {
            if ($journalEntry->created_by == \Auth::user()->creatorId()) {
                if ($journalEntry->status == 'Approved') {
                    foreach ($journalEntry->items as $item) {
                        $this->reverseBankAccountBalance($item->account, $item->debit, $item->credit);
                    }
                }
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

    public function voucherNumber($voucherType = 'JV', $ownedBy = null)
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
        $transactionDateTime = \Carbon\Carbon::parse($journal->date)->setTimeFrom(now());
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
            $journalItem->ref_no = $account['ref_no'] ?? null;
            $journalItem->tra_date = $account['tra_date'] ?? null;
            $journalItem->user_type = $account['user_type'] ?? null;
            $journalItem->user_id = $account['user_id'] ?? null;
            $this->setNullableModelValueIfColumn($journalItem, 'added_by', \Auth::id());
            $this->setNullableModelValueIfColumn($journalItem, 'added_at', now());
            $this->setNullableModelValueIfColumn($journalItem, 'updated_by', \Auth::id());
            $this->setModelValueIfColumn($journalItem, 'category', 'manual');
            $this->setModelValueIfColumn($journalItem, 'department_id', $account['dept_id'] ?? null);
            $this->setModelValueIfColumn($journalItem, 'designation_id', $account['designation_id'] ?? null);
            $journalItem->created_at = $transactionDateTime;
            $journalItem->updated_at = $transactionDateTime;
            $journalItem->save();

            if ($updateBankBalance && $journal->status == 'Approved') {
                $this->updateBankAccountBalance($accountId, $journalItem->debit, $journalItem->credit);
            }
        }
    }

    public function updateBankAccountBalance($accountId, $debit, $credit)
    {
        $bankAccounts = BankAccount::where('chart_account_id', '=', $accountId)->get();
        if (!empty($bankAccounts)) {
            foreach ($bankAccounts as $bankAccount) {
                $oldBalance = $bankAccount->opening_balance;
                $newBalance = null;
                if ($debit > 0) {
                    $newBalance = $oldBalance + $debit;
                }
                if ($credit > 0) {
                    $newBalance = $oldBalance - $credit;
                }
                if (isset($newBalance)) {
                    $bankAccount->opening_balance = $newBalance;
                    $bankAccount->save();
                }
            }
        }
    }
    
    private function voucherPaymentInfo(JournalEntry $journalEntry, $accounts, string $voucherType): array
    {
        $modeMap = [
            'dd' => 'DD',
            'cd' => 'CD',
            'online' => 'Online',
            'bank-transfer' => 'Bank Transfer',
            'chq' => 'CHQ',
            'cheque' => 'CHQ',
            'others' => 'Others',
        ];
        $mode = $journalEntry->payment_mode ?? null;
        $bankLine = $accounts->first(function ($item) use ($voucherType) {
            if (!$item->accounts) {
                return false;
            }

            $name = strtolower($item->accounts->name ?? '');
            $code = strtolower($item->accounts->code ?? '');

            return str_contains($name, 'bank')
                || str_contains($name, 'hbl')
                || str_contains($name, 'cash')
                || str_contains($code, 'bank')
                || in_array($voucherType, ['BPV', 'BRV']) && (($item->credit ?? 0) > 0 || ($item->debit ?? 0) > 0);
        });

        return [
            'mode' => $mode ? ($modeMap[strtolower($mode)] ?? strtoupper($mode)) : '',
            'reference' => $journalEntry->reference ?? '',
            'payment_date' => $journalEntry->payment_date ?: $journalEntry->date,
            'bank_name' => $bankLine && $bankLine->accounts ? $bankLine->accounts->name : '',
            'invoice_no' => $journalEntry->reference_id ?: '',
        ];
    }

    private function voucherPayeeInfo(JournalEntry $journalEntry, $accounts): array
    {
        if (!empty($journalEntry->payee_account_title)) {
            return [
                'name' => $journalEntry->payee_account_title,
                'account_no' => $journalEntry->payee_account_no ?? '',
                'contact' => $journalEntry->payee_contact ?? '',
                'email' => $journalEntry->payee_email ?? '',
                'ntn_cnic' => $journalEntry->payee_cnic ?? '',
            ];
        }

        $partyLine = $accounts->first(function ($item) {
            return !empty($item->user) || !empty($item->user_type) || !empty($item->user_id);
        });
        $party = $partyLine ? $partyLine->user : null;

        return [
            'name' => $party->stdname ?? $party->name ?? '',
            'account_no' => '',
            'contact' => $party->phone ?? $party->contact ?? '',
            'email' => $party->email ?? '',
            'ntn_cnic' => $party->cnic ?? $party->father_cnic ?? '',
        ];
    }

    private function voucherWatermarkLogo(): string
    {
        $paths = [
            public_path('assets/images/lynx2-watermark.png'),
            public_path('assets/images/graylynx.png'),
            public_path('assets/images/lynx2.jpg'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return str_replace('\\', '/', $path);
            }
        }

        return '';
    }

    private function voucherLogo(): string
    {
        $paths = [
            public_path('assets/images/lynx2-header.png'),
            public_path('assets/images/lynx2.jpg'),
            storage_path('uploads/logo/thelynxschool.png'),
            storage_path('uploads/logo/logo-dark.png'),
            public_path('uploads/logo/logo-dark.png'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return str_replace('\\', '/', $path);
            }
        }

        return '';
    }

    private function amountToWords($amount): string
    {
        $amount = (float) $amount;
        $whole = (int) floor($amount);
        $fraction = (int) round(($amount - $whole) * 100);
        $words = $this->numberToWords($whole);

        if ($fraction > 0) {
            return $words . ' and ' . $this->numberToWords($fraction) . ' Paisa Only';
        }

        return $words . ' Only';
    }

    private function numberToWords(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $units = [
            10000000 => 'Crore',
            100000 => 'Lac',
            1000 => 'Thousand',
            100 => 'Hundred',
        ];

        foreach ($units as $value => $label) {
            if ($number >= $value) {
                $prefix = $this->numberToWords((int) floor($number / $value));
                $remainder = $number % $value;

                return trim($prefix . ' ' . $label . ' ' . ($remainder ? $this->numberToWords($remainder) : ''));
            }
        }

        if ($number < 20) {
            return $ones[$number];
        }

        return trim($tens[(int) floor($number / 10)] . ' ' . $ones[$number % 10]);
    }

    public function reverseBankAccountBalance($accountId, $debit, $credit)
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
        $accountOptions = app(ChartOfAccountOptionsService::class)
            ->forCreator((int) \Auth::user()->creatorId());
        $journalId = $this->journalNumber();
        $bankAccountQuery = BankAccount::query()->where('created_by', \Auth::user()->creatorId());
        if (\Schema::hasColumn('bank_accounts', 'owned_by')) {
            $bankAccountQuery->orWhere('owned_by', \Auth::user()->ownedId());
        }
        $bankAccounts = $bankAccountQuery->orderBy('bank_name')
            ->get()
            ->mapWithKeys(function ($bank) {
                $labelParts = array_filter([
                    $bank->bank_name ?? null,
                    $bank->holder_name ?? null,
                    $bank->account_number ?? null,
                ]);

                return [$bank->id => implode(' - ', $labelParts)];
            });
        $bankAccounts->prepend('Select Bank Account', '');
        $customers = Customer::where('created_by', \Auth::user()->creatorId())
            ->orderBy('name')
            ->pluck('name', 'id');
        $customers->prepend('Select Customer', '');

        $vendors = Vender::optionsForCreator(\Auth::user()->creatorId());

        $employeeQuery = Employee::query();
        if (\Schema::hasColumn('employees', 'owned_by')) {
            $employeeQuery->where('owned_by', \Auth::user()->ownedId());
        } else {
            $employeeQuery->where('created_by', \Auth::user()->creatorId());
        }
        $employees = $employeeQuery->orderBy('name')->pluck('name', 'id');
        $employees->prepend('Select Employee', '');

        $studentQuery = StudentRegistration::query();
        if (\Schema::hasColumn('student_registrations', 'owned_by')) {
            $studentQuery->where('owned_by', \Auth::user()->ownedId());
        } elseif (\Schema::hasColumn('student_registrations', 'branch')) {
            $studentQuery->where('branch', \Auth::user()->ownedId());
        }
        $students = $studentQuery
            ->select(\DB::raw('CONCAT(COALESCE(roll_no, ""), " - ", stdname, " s/d/o ", fathername) AS student_name'), 'id')
            ->orderBy('stdname')
            ->pluck('student_name', 'id');
        $students->prepend('Select Student', '');

        $voucherCategoryTypes = ProductServiceCategory::where('type', 'voucher')
            ->where('created_by', \Auth::user()->creatorId())
            ->orderBy('name')
            ->pluck('name', 'id');
        $voucherCategoryTypes->prepend('Select Voucher Category Type', '');

        return compact('branches', 'departments', 'accountOptions', 'journalId', 'bankAccounts', 'customers', 'vendors', 'employees', 'students', 'voucherCategoryTypes');
    }

    private function canCreateVoucherType($voucherType): bool
    {
        $type = $this->normalizeVoucherType($voucherType);

        if ($type === 'JV') {
            return $this->canCreateJournalVoucher();
        }

        return \Auth::user()->can('create journal entry');
    }

    private function canCreateJournalVoucher(): bool
    {
        return \Auth::user()->can('create journal voucher');
    }

    private function isJournalVoucher(?JournalEntry $journalEntry): bool
    {
        return $journalEntry && $this->normalizeVoucherType($journalEntry->voucher_type ?? 'JV') === 'JV';
    }

    private function canUseJournalEntry(?JournalEntry $journalEntry, string $journalVoucherAction, string $legacyPermission): bool
    {
        if ($this->isJournalVoucher($journalEntry)) {
            return \Auth::user()->can($journalVoucherAction . ' journal voucher');
        }

        return \Auth::user()->can($legacyPermission);
    }

    private function voucherItemsForEditor(JournalEntry $journalEntry)
    {
        $journalEntry->load('accounts.accounts');

        return $journalEntry->accounts->map(function ($item) use ($journalEntry) {
            $account = $item->accounts;
            $userType = $item->user_type;
            $category = ($account?->category) ?: $this->categoryFromUserType($userType);
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
            $meta['dept_id'] = $employee?->department_id ?? '';
            $meta['dept_name'] = optional($employee?->department)->name;
            $meta['designation_id'] = $employee?->designation_id ?? '';
            $meta['designation_name'] = optional($employee?->designation)->name;
            $meta['employee_id'] = $employee?->id ?? $userId;
            $meta['employee_name'] = $employee?->name ?? '';
        } elseif ($category === 'student' || strtolower($userType ?? '') === 'student') {
            $student = StudentRegistration::find($userId);
            $meta['student_id'] = $student?->id ?? $userId;
            $meta['student_name'] = $student ? trim($student->roll_no . ' - ' . $student->stdname . ' s/d/o ' . $student->fathername) : '';
        } elseif ($category === 'inventory' || strtolower($userType ?? '') === 'vender') {
            $vendor = Vender::find($userId);
            $meta['vendor_id'] = $vendor?->id ?? $userId;
            $meta['vendor_name'] = $vendor?->name ?? '';
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

    private function formatVoucherNumber($number, $voucherType, $journalEntry = null)
    {
        if ($journalEntry instanceof JournalEntry) {
            return $journalEntry->getVoucherNumber();
        }

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

    private function setNullableModelValueIfColumn($model, $column, $value)
    {
        if (\Schema::hasColumn($model->getTable(), $column)) {
            $model->{$column} = $value === '' ? null : $value;
        }
    }

    private function fillJournalEntryExtraFields(JournalEntry $journal, Request $request, $isUpdate = false)
    {
        $paymentMode = $request->payment_mode ?? $request->mode;
        $status = $request->status ?: ($journal->status ?: 'Draft');

        if ($request->has('bank_id')) {
            $this->setNullableModelValueIfColumn($journal, 'bank_id', $request->bank_id);
        }
        $this->setNullableModelValueIfColumn($journal, 'payment_mode', $paymentMode);
        $this->setNullableModelValueIfColumn($journal, 'mode', $paymentMode);
        $this->setNullableModelValueIfColumn($journal, 'cheque_no', $request->cheque_no);
        $this->setNullableModelValueIfColumn($journal, 'cheque_date', $request->cheque_date);
        $partyType = $request->user_type === 'Vendor' ? 'Vender' : $request->user_type;
        $this->setNullableModelValueIfColumn($journal, 'user_type', $partyType);
        $this->setNullableModelValueIfColumn($journal, 'user_id', $request->user_id);
        $this->setNullableModelValueIfColumn($journal, 'transaction_no', $request->transaction_no);
        $this->setNullableModelValueIfColumn($journal, 'status', $status);
        $this->setNullableModelValueIfColumn($journal, 'reversed_entry_id', $request->reversed_entry_id);

        $this->setNullableModelValueIfColumn($journal, 'payee_account_title', $request->payee_account_title);
        $this->setNullableModelValueIfColumn($journal, 'payee_account_no', $request->payee_account_no);
        $this->setNullableModelValueIfColumn($journal, 'payee_contact', $request->payee_contact);
        $this->setNullableModelValueIfColumn($journal, 'payee_email', $request->payee_email);
        $this->setNullableModelValueIfColumn($journal, 'payee_cnic', $request->payee_cnic);
        
        $this->setNullableModelValueIfColumn($journal, 'receiver_name', $request->receiver_name);
        $this->setNullableModelValueIfColumn($journal, 'receiver_cnic', $request->receiver_cnic);
        $this->setNullableModelValueIfColumn($journal, 'receiver_contact', $request->receiver_contact);
        $this->setNullableModelValueIfColumn($journal, 'receiver_email', $request->receiver_email);
        
        $this->setNullableModelValueIfColumn($journal, 'payment_date', $request->payment_date);

        $reversedTimestamp = $request->reversed_timestamp;
        if ($status === 'Reversed' && !$reversedTimestamp && empty($journal->reversed_timestamp)) {
            $reversedTimestamp = now();
        }
        $this->setNullableModelValueIfColumn($journal, 'reversed_timestamp', $reversedTimestamp);

        if ($request->has('is_system_generated')) {
            $this->setNullableModelValueIfColumn($journal, 'is_system_generated', $request->boolean('is_system_generated'));
        }

        if (in_array($status, ['Approved', 'Posted'], true) && empty($journal->approved_by)) {
            $this->setNullableModelValueIfColumn($journal, 'approved_by', \Auth::id());
            $this->setNullableModelValueIfColumn($journal, 'approved_at', now());
        }

        if ($isUpdate) {
            $this->setNullableModelValueIfColumn($journal, 'updated_by', \Auth::id());
        }

        $this->setNullableModelValueIfColumn($journal, 'attachment', $this->storeJournalAttachment($request, $journal->attachment ?? null));
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
        if ($this->canCreateJournalVoucher()) {
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
        $series = strtoupper($request->voucher_series ?? 'SYSTEM');

        if ($series === 'MANUAL') {
            $last = JournalEntry::where('voucher_type', $type)
                ->where('voucher_series', 'MANUAL')
                ->where('created_by', $user->creatorId())
                ->max('manual_series_no');
            $next = $last + 1;
            $voucherNo = 'M-' . $type . '-' . str_pad($next, 6, '0', STR_PAD_LEFT);
        } else {
            $latest = JournalEntry::where('owned_by', $branchId)
                ->where('voucher_type', $type)
                ->where('voucher_series', 'SYSTEM')
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
        }

        return response()->json([
            'voucher_number' => $voucherNo
        ]);
    }

    public function getVoucherParties(Request $request)
    {
        $branchId = $request->branch_id ?: \Auth::user()->ownedId();

        $customerQuery = Customer::query()->where('created_by', \Auth::user()->creatorId());
        if (\Schema::hasColumn('customers', 'owned_by')) {
            $customerQuery->where('owned_by', $branchId);
        }
        $customers = $customerQuery->orderBy('name')->get(['id', 'name']);

        $vendorQuery = Vender::query()->where('created_by', \Auth::user()->creatorId());
        if (\Schema::hasColumn('venders', 'owned_by')) {
            $vendorQuery->where('owned_by', $branchId);
        }
        $vendors = $vendorQuery->orderBy('company_name')
            ->orderBy('name')
            ->get(['id', 'company_name', 'name'])
            ->map(function ($vendor) {
                return [
                    'id' => $vendor->id,
                    'name' => $vendor->display_name,
                ];
            })
            ->values();

        $employeeQuery = Employee::query();
        if (\Schema::hasColumn('employees', 'branch_id')) {
            $employeeQuery->where('branch_id', $branchId);
        } elseif (\Schema::hasColumn('employees', 'owned_by')) {
            $employeeQuery->where('owned_by', $branchId);
        } else {
            $employeeQuery->where('created_by', \Auth::user()->creatorId());
        }
        $employees = $employeeQuery->orderBy('name')->get(['id', 'name']);

        $studentQuery = StudentRegistration::query();
        if (\Schema::hasColumn('student_registrations', 'owned_by')) {
            $studentQuery->where('owned_by', $branchId);
        } elseif (\Schema::hasColumn('student_registrations', 'branch')) {
            $studentQuery->where('branch', $branchId);
        }
        if (\Schema::hasColumn('student_registrations', 'student_status')) {
            $studentQuery->where('student_status', 'Enrolled');
        }
        $students = $studentQuery
            ->orderBy('stdname')
            ->get(['id', 'roll_no', 'stdname', 'fathername'])
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => trim(($student->roll_no ? $student->roll_no . ' - ' : '') . $student->stdname . ' s/d/o ' . $student->fathername),
                ];
            })
            ->values();

        return response()->json([
            'customers' => $customers,
            'vendors' => $vendors,
            'employees' => $employees,
            'students' => $students,
        ]);
    }

    public function approveJournalEntry($id)
    {
        $journal = JournalEntry::find($id);

        if ($this->canUseJournalEntry($journal, 'approve', 'edit journal entry') && \Auth::user()->type == 'company') {
            if ($journal && $journal->created_by == \Auth::user()->creatorId()) {
                if ($journal->status == 'Approved') {
                    return redirect()->back()->with('error', __('Voucher is already approved.'));
                }

                \DB::beginTransaction();
                try {
                    $journal->status = 'Approved';
                    $this->setNullableModelValueIfColumn($journal, 'approved_by', \Auth::id());
                    $this->setNullableModelValueIfColumn($journal, 'approved_at', now());
                    $journal->save();

                    // Update bank balances for all items in this voucher
                    foreach ($journal->items as $item) {
                        $this->updateBankAccountBalance($item->account, $item->debit, $item->credit);
                    }

                    \DB::commit();
                    return redirect()->back()->with('success', __('Journal Entry successfully approved and bank balance updated.'));
                } catch (\Exception $e) {
                    \DB::rollback();
                    return redirect()->back()->with('error', __('Something went wrong: ') . $e->getMessage());
                }
            }
            return redirect()->back()->with('error', __('Voucher not found or permission denied.'));
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }

    public function sendToHO($id)
    {
        $journal = JournalEntry::find($id);

        if ($this->canUseJournalEntry($journal, 'submit', 'edit journal entry')) {
            if ($journal && $journal->created_by == \Auth::user()->creatorId()) {
                if (in_array($journal->status, ['Approved', 'Posted', 'Reversed'])) {
                    return redirect()->back()->with('error', __('Voucher is already approved, posted or reversed.'));
                }

                $journal->status = 'Submitted';
                $journal->save();

                return redirect()->back()->with('success', __('Voucher successfully sent to HO for approval.'));
            }
            return redirect()->back()->with('error', __('Voucher not found or permission denied.'));
        }
        return redirect()->back()->with('error', __('Permission denied.'));
    }
}
