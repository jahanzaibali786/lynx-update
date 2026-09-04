<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\User;
use App\Models\Vender;
use App\Models\VendorAdvance;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class VendorAdvanceController extends Controller
{
    private function normalizeMonthDate($value)
    {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value)) {
            return $value . '-01';
        }

        return $value;
    }

    private function createVendorAdvanceVoucher(VendorAdvance $advance, BankAccount $bankAccount)
    {
        $vendor = Vender::findOrFail($advance->vender_id);

        if (empty($vendor->account_id)) {
            throw new \Exception(__('Selected vendor does not have a COA account attached.'));
        }
        if (empty($bankAccount->chart_account_id)) {
            throw new \Exception(__('Selected bank account does not have a COA account attached.'));
        }

        $voucherType = $advance->payment_method === 'cash' ? 'CPV' : 'BPV';
        $latest = JournalEntry::where('owned_by', $advance->owned_by)
            ->where('voucher_type', $voucherType)
            ->latest()
            ->first();

        $journal = new JournalEntry();
        $journal->journal_id = $latest ? $latest->journal_id + 1 : 1;
        $journal->date = $advance->approval_date;
        $journal->reference = $advance->reference;
        $journal->description = 'Vendor advance id : ' . $advance->id;
        $journal->reference_id = $advance->id;
        $journal->category = 'Vendor Advance';
        $journal->voucher_type = $voucherType;
        $journal->user_id = $advance->vender_id;
        $journal->user_type = 'Vender';
        $journal->owned_by = $advance->owned_by;
        $journal->created_by = $advance->created_by;
        $journal->save();

        JournalItem::create([
            'journal' => $journal->id,
            'account' => $bankAccount->chart_account_id,
            'description' => $advance->advance_reason,
            'user_id' => $advance->vender_id,
            'user_type' => 'Vender',
            'bank_id' => $bankAccount->id,
            'credit' => $advance->advance_amount,
            'debit' => 0,
            'entry_id' => $advance->id,
            'types' => 'Vendor Advance Payment',
            'branch_id' => $advance->owned_by,
        ]);

        JournalItem::create([
            'journal' => $journal->id,
            'account' => $vendor->account_id,
            'description' => $advance->advance_reason,
            'user_id' => $advance->vender_id,
            'user_type' => 'Vender',
            'credit' => 0,
            'debit' => $advance->advance_amount,
            'entry_id' => $advance->id,
            'types' => 'Vendor Advance Payment',
            'branch_id' => $advance->owned_by,
        ]);

        return $journal->id;
    }

    private function applyFilters($query, Request $request)
    {
        if ($request->filled('vender_id')) {
            $query->where('vender_id', $request->vender_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_month')) {
            $fromMonth = Carbon::parse($this->normalizeMonthDate($request->from_month))->startOfMonth();
            $query->whereDate('advance_date', '>=', $fromMonth->format('Y-m-d'));
        }
        if ($request->filled('to_month')) {
            $toMonth = Carbon::parse($this->normalizeMonthDate($request->to_month))->endOfMonth();
            $query->whereDate('advance_date', '<=', $toMonth->format('Y-m-d'));
        }

        return $query;
    }

    public function index(Request $request)
    {
        if (!\Auth::user()->can('manage vender')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $query = VendorAdvance::with(['vendor.ChartAccount', 'approvedBy', 'bank'])
            ->where('created_by', \Auth::user()->creatorId());

        if (\Auth::user()->type != 'company') {
            $query->where('owned_by', \Auth::user()->ownedId());
        }

        $advances = $this->applyFilters($query, $request)
            ->orderByDesc('advance_date')
            ->orderByDesc('id')
            ->paginate(25);

        $vendors = Vender::optionsForCreator(\Auth::user()->creatorId());

        return view('vendor_advance.index', compact('advances', 'vendors'));
    }

    public function create()
    {
        if (!\Auth::user()->can('create vender')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $vendors = Vender::optionsForCreator(\Auth::user()->creatorId());

        return view('vendor_advance.create', compact('vendors'));
    }

    public function store(Request $request)
    {
        if (!\Auth::user()->can('create vender')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->merge([
            'advance_date' => $this->normalizeMonthDate($request->input('advance_date')),
        ]);

        $request->validate([
            'vender_id' => 'required|integer|exists:venders,id',
            'advance_amount' => 'required|numeric|min:0.01',
            'advance_date' => 'required|date',
            'advance_reason' => 'nullable|string',
        ]);

        $vendor = Vender::where('id', $request->vender_id)
            ->where('created_by', \Auth::user()->creatorId())
            ->firstOrFail();

        if (empty($vendor->account_id)) {
            return redirect()->back()->with('error', __('Selected vendor does not have a COA account attached.'));
        }

        VendorAdvance::create([
            'vender_id' => $vendor->id,
            'advance_amount' => $request->advance_amount,
            'advance_date' => $request->advance_date,
            'advance_reason' => $request->advance_reason,
            'status' => 0,
            'owned_by' => \Auth::user()->ownedId(),
            'created_by' => \Auth::user()->creatorId(),
        ]);

        return redirect()->route('vendor-advance.index')->with('success', __('Vendor advance successfully created.'));
    }

    public function edit($id)
    {
        if (!\Auth::user()->can('edit vender')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $advance = VendorAdvance::with('vendor')->findOrFail($id);

        if ($advance->status == 1) {
            return response()->json(['error' => __('Approved vendor advance cannot be edited.')], 401);
        }

        $vendors = Vender::optionsForCreator(\Auth::user()->creatorId(), null);

        return view('vendor_advance.edit', compact('advance', 'vendors'));
    }

    public function show($id)
    {
        return redirect()->route('vendor-advance.index');
    }

    public function printAdvance(Request $request, $id)
    {
        $advance = VendorAdvance::with(['vendor.ChartAccount', 'bank', 'approvedBy'])->findOrFail($id);

        if ($request->boolean('download') || $request->boolean('preview') || $request->boolean('print')) {
            return $this->advancePdf($advance, $request->boolean('download'));
        }

        return view('vendor_advance.pdf', compact('advance'));
    }

    private function advancePdf(VendorAdvance $advance, $download = false)
    {
        $html = view('vendor_advance.pdf', [
            'advance' => $advance,
            'isPdf' => true,
        ])->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', public_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $font = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');
        $dompdf->getCanvas()->page_text(500, 815, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 8, [0, 0, 0]);

        return $dompdf->stream('vendor_advance_' . $advance->id . '.pdf', ['Attachment' => $download]);
    }

    public function update(Request $request, $id)
    {
        if (!\Auth::user()->can('edit vender')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $advance = VendorAdvance::findOrFail($id);
        if ($advance->status == 1) {
            return redirect()->back()->with('error', __('Approved vendor advance cannot be edited.'));
        }

        $request->merge([
            'advance_date' => $this->normalizeMonthDate($request->input('advance_date')),
        ]);

        $request->validate([
            'vender_id' => 'required|integer|exists:venders,id',
            'advance_amount' => 'required|numeric|min:0.01',
            'advance_date' => 'required|date',
            'advance_reason' => 'nullable|string',
        ]);

        $vendor = Vender::where('id', $request->vender_id)
            ->where('created_by', \Auth::user()->creatorId())
            ->firstOrFail();

        if (empty($vendor->account_id)) {
            return redirect()->back()->with('error', __('Selected vendor does not have a COA account attached.'));
        }

        $advance->vender_id = $vendor->id;
        $advance->advance_amount = $request->advance_amount;
        $advance->advance_date = $request->advance_date;
        $advance->advance_reason = $request->advance_reason;
        $advance->save();

        return redirect()->route('vendor-advance.index')->with('success', __('Vendor advance successfully updated.'));
    }

    public function destroy($id)
    {
        if (!\Auth::user()->can('delete vender')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $advance = VendorAdvance::findOrFail($id);
        if ($advance->status == 1) {
            return redirect()->back()->with('error', __('Approved vendor advance cannot be deleted.'));
        }

        $advance->delete();

        return redirect()->back()->with('success', __('Vendor advance successfully deleted.'));
    }

    public function status($id)
    {
        if (!\Auth::user()->can('edit vender')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $advance = VendorAdvance::with(['vendor.ChartAccount'])->findOrFail($id);
        $bankAccounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' - ',holder_name) AS name"))
            ->where('created_by', \Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'id');

        return view('vendor_advance.status', compact('advance', 'bankAccounts'));
    }

    public function statusChange(Request $request, $id)
    {
        if (!\Auth::user()->can('edit vender')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        \DB::beginTransaction();
        try {
            $advance = VendorAdvance::with('vendor')->findOrFail($id);

            if ($advance->status != 0) {
                \DB::rollback();
                return redirect()->back()->with('error', __('Vendor advance status is already updated.'));
            }

            if ((int) $request->status == 1) {
                $request->validate([
                    'approval_date' => 'required|date',
                    'bank_id' => 'required|integer|exists:bank_accounts,id',
                    'payment_method' => 'required|in:cash,online,cheque,check',
                    'account_number' => 'required_if:payment_method,online|nullable|string',
                    'cheque_no' => 'required_if:payment_method,cheque|required_if:payment_method,check|nullable|string',
                    'reference' => 'nullable|string',
                ]);

                if (empty($advance->vendor) || empty($advance->vendor->account_id)) {
                    \DB::rollback();
                    return redirect()->back()->with('error', __('Selected vendor does not have a COA account attached.'));
                }

                $advance->status = 1;
                $advance->approval_date = $request->approval_date;
                $advance->bank_id = $request->bank_id;
                $advance->reference = $request->payment_method == 'online'
                    ? $request->account_number
                    : (in_array($request->payment_method, ['cheque', 'check']) ? $request->cheque_no : $request->reference);
                $advance->payment_method = $request->payment_method;
                $advance->approved_by = \Auth::id();
                $advance->save();

                $bankAccount = BankAccount::findOrFail($request->bank_id);
                $advance->voucher_id = $this->createVendorAdvanceVoucher($advance, $bankAccount);
                $advance->save();

                $vendor = Vender::findOrFail($advance->vender_id);
                $vendor->balance = (float) ($vendor->balance ?? 0) + (float) $advance->advance_amount;
                $vendor->save();
            } elseif ((int) $request->status == 2) {
                $advance->status = 2;
                $advance->approved_by = \Auth::id();
                $advance->save();
            } else {
                \DB::rollback();
                return redirect()->back()->with('error', __('Invalid status.'));
            }

            \DB::commit();

            return redirect()->back()->with('success', __('Vendor advance status successfully updated.'));
        } catch (\Exception $e) {
            \DB::rollback();

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
