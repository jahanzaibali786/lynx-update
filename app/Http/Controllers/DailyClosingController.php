<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankTransfer;
use App\Models\DailyClosing;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DailyClosingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->type == 'company') {
            $query = DailyClosing::where('created_by', $user->creatorId());
        } else {
            $query = DailyClosing::where('owned_by', $user->ownedId());
        }

        if (!empty($request->from_date)) {
            $query->where('from_date', '>=', $request->from_date);
        }
        if (!empty($request->to_date)) {
            $query->where('to_date', '<=', $request->to_date);
        }

        $closings = $query->orderBy('from_date', 'desc')->get();

        return view('dailyClosing.index', compact('closings'));
    }

    public function create()
    {
        $hoEmployees = \App\Models\Employee::where('owned_by', \Auth::user()->creatorId())
            ->where('is_res_ter', 0)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();

        return view('dailyClosing.create', compact('hoEmployees'));
    }

    public function getTransfers(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        if (!$fromDate || !$toDate) {
            return response()->json(['error' => 'Invalid dates'], 400);
        }

        // Check if overlap exists
        $overlap = $this->checkOverlap($fromDate, $toDate, $request->exclude_id);
        if ($overlap) {
            return response()->json([
                'overlap' => true,
                'message' => "Selected date range overlaps with an existing Daily Closing (from " . $overlap->from_date->format('d-M-Y') . " to " . $overlap->to_date->format('d-M-Y') . ")"
            ]);
        }

        $user = Auth::user();
        $query = BankTransfer::whereBetween('date', [$fromDate, $toDate]);
        if ($user->type == 'company') {
            $query->where('created_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }

        $transfers = $query->get();

        $rows = [];
        $totalReceived = 0;

        foreach ($transfers as $index => $bt) {
            // Get from account
            $fromAcc = BankAccount::find($bt->from_account);
            $acAbbr = 'RV';
            if ($fromAcc) {
                $type = strtolower($fromAcc->type ?? '');
                if ($type === 'head_imprest') {
                    $acAbbr = 'Imp';
                } else {
                    $holder = strtolower($fromAcc->holder_name ?? '');
                    $bank = strtolower($fromAcc->bank_name ?? '');
                    if (str_contains($holder, 'canteen') || str_contains($bank, 'canteen')) {
                        $acAbbr = 'Canteen';
                    } elseif (str_contains($holder, 'sp') || str_contains($bank, 'sp')) {
                        $acAbbr = 'SP';
                    }
                }
            }

            $branch = User::find($bt->owned_by);
            $branchName = $branch ? $branch->name : '-';

            $rows[] = [
                'sr_no' => $index + 1,
                'branch_name' => $branchName,
                'period' => Carbon::parse($bt->date)->format('d-M-y'),
                'slip_no' => $bt->reference ?? '-',
                'ac' => $acAbbr,
                'amount' => (float)$bt->amount
            ];

            $totalReceived += (float)$bt->amount;
        }

        return response()->json([
            'overlap' => false,
            'transfers' => $rows,
            'total_received' => $totalReceived
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'deposit_date' => 'required|date',
            'issued_by_id' => 'required|integer',
            'received_by_id' => 'required|integer',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        if ($request->issued_by_id === $request->received_by_id && !empty($request->issued_by_id)) {
            return response()->json([
                'success' => false,
                'error' => __('Issued By and Received By cannot be the same employee.')
            ]);
        }

        // Check overlap
        $overlap = $this->checkOverlap($request->from_date, $request->to_date);
        if ($overlap) {
            return response()->json([
                'success' => false,
                'error' => "Selected date range overlaps with an existing Daily Closing (from " . $overlap->from_date->format('d-M-Y') . " to " . $overlap->to_date->format('d-M-Y') . ")"
            ]);
        }

        // Fetch transfers to compute total_income_received
        $query = BankTransfer::whereBetween('date', [$request->from_date, $request->to_date]);
        if ($user->type == 'company') {
            $query->where('created_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }
        $totalReceived = $query->sum('amount');

        $issuedEmployee = \App\Models\Employee::find($request->issued_by_id);
        $receivedEmployee = \App\Models\Employee::find($request->received_by_id);

        // Calculate note total
        $denoms = [5000, 1000, 500, 100, 50, 20, 10, 5, 2, 1];
        $totalDeposited = 0;
        $data = $request->only([
            'from_date', 'to_date', 'deposit_date', 'issued_by_id', 'received_by_id', 'note'
        ]);
        $data['issued_by'] = $issuedEmployee ? $issuedEmployee->name : null;
        $data['received_by'] = $receivedEmployee ? $receivedEmployee->name : null;

        foreach ($denoms as $denom) {
            $count = (int)$request->input('note_' . $denom, 0);
            $data['note_' . $denom] = $count;
            $totalDeposited += $count * $denom;
        }

        $difference = $totalReceived - $totalDeposited;

        $data['total_income_received'] = $totalReceived;
        $data['total_income_deposited'] = $totalDeposited;
        $data['difference'] = $difference;
        $data['status'] = 'pending';
        $data['created_by'] = $user->creatorId();
        $data['owned_by'] = ($user->type == 'company') ? $user->creatorId() : $user->ownedId();

        DailyClosing::create($data);

        return response()->json([
            'success' => true,
            'message' => __('Daily Closing successfully created.')
        ]);
    }

    public function edit($id)
    {
        $dailyClosing = DailyClosing::findOrFail($id);
        $hoEmployees = \App\Models\Employee::where('owned_by', \Auth::user()->creatorId())
            ->where('is_res_ter', 0)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();

        return view('dailyClosing.edit', compact('dailyClosing', 'hoEmployees'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $dailyClosing = DailyClosing::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'deposit_date' => 'required|date',
            'issued_by_id' => 'required|integer',
            'received_by_id' => 'required|integer',
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        if ($request->issued_by_id === $request->received_by_id && !empty($request->issued_by_id)) {
            return response()->json([
                'success' => false,
                'error' => __('Issued By and Received By cannot be the same employee.')
            ]);
        }

        // Check overlap (excluding current ID)
        $overlap = $this->checkOverlap($request->from_date, $request->to_date, $id);
        if ($overlap) {
            return response()->json([
                'success' => false,
                'error' => "Selected date range overlaps with an existing Daily Closing (from " . $overlap->from_date->format('d-M-Y') . " to " . $overlap->to_date->format('d-M-Y') . ")"
            ]);
        }

        // Fetch transfers to compute total_income_received
        $query = BankTransfer::whereBetween('date', [$request->from_date, $request->to_date]);
        if ($user->type == 'company') {
            $query->where('created_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }
        $totalReceived = $query->sum('amount');

        $issuedEmployee = \App\Models\Employee::find($request->issued_by_id);
        $receivedEmployee = \App\Models\Employee::find($request->received_by_id);

        // Calculate note total
        $denoms = [5000, 1000, 500, 100, 50, 20, 10, 5, 2, 1];
        $totalDeposited = 0;
        $data = $request->only([
            'from_date', 'to_date', 'deposit_date', 'issued_by_id', 'received_by_id', 'note'
        ]);
        $data['issued_by'] = $issuedEmployee ? $issuedEmployee->name : null;
        $data['received_by'] = $receivedEmployee ? $receivedEmployee->name : null;

        foreach ($denoms as $denom) {
            $count = (int)$request->input('note_' . $denom, 0);
            $data['note_' . $denom] = $count;
            $totalDeposited += $count * $denom;
        }

        $difference = $totalReceived - $totalDeposited;

        $data['total_income_received'] = $totalReceived;
        $data['total_income_deposited'] = $totalDeposited;
        $data['difference'] = $difference;

        $dailyClosing->update($data);

        return response()->json([
            'success' => true,
            'message' => __('Daily Closing successfully updated.')
        ]);
    }

    public function destroy($id)
    {
        $dailyClosing = DailyClosing::findOrFail($id);
        $dailyClosing->delete();

        return redirect()->route('daily-closing.index')->with('success', __('Daily Closing successfully deleted.'));
    }

    public function approve($id)
    {
        $dailyClosing = DailyClosing::findOrFail($id);
        $dailyClosing->status = ($dailyClosing->status === 'approved') ? 'pending' : 'approved';
        $dailyClosing->save();

        return response()->json([
            'success' => true,
            'message' => __('Approval status updated successfully.'),
            'status' => $dailyClosing->status
        ]);
    }

    public function show($id)
    {
        $dailyClosing = DailyClosing::findOrFail($id);

        $user = Auth::user();
        $query = BankTransfer::whereBetween('date', [$dailyClosing->from_date, $dailyClosing->to_date]);
        if ($user->type == 'company') {
            $query->where('created_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }
        $transfers = $query->get();

        $transferRows = [];
        foreach ($transfers as $index => $bt) {
            $fromAcc = BankAccount::find($bt->from_account);
            $acAbbr = 'RV';
            if ($fromAcc) {
                $type = strtolower($fromAcc->type ?? '');
                if ($type === 'head_imprest') {
                    $acAbbr = 'Imp';
                } else {
                    $holder = strtolower($fromAcc->holder_name ?? '');
                    $bank = strtolower($fromAcc->bank_name ?? '');
                    if (str_contains($holder, 'canteen') || str_contains($bank, 'canteen')) {
                        $acAbbr = 'Canteen';
                    } elseif (str_contains($holder, 'sp') || str_contains($bank, 'sp')) {
                        $acAbbr = 'SP';
                    }
                }
            }

            $branch = User::find($bt->owned_by);
            $branchName = $branch ? $branch->name : '-';

            $transferRows[] = (object)[
                'sr_no' => $index + 1,
                'branch_name' => $branchName,
                'period' => Carbon::parse($bt->date)->format('d-M-y'),
                'slip_no' => $bt->reference ?? '-',
                'ac' => $acAbbr,
                'amount' => (float)$bt->amount
            ];
        }

        return view('dailyClosing.show', compact('dailyClosing', 'transferRows'));
    }

    private function checkOverlap($fromDate, $toDate, $excludeId = null)
    {
        $user = Auth::user();
        
        $query = DailyClosing::where(function ($q) use ($fromDate, $toDate) {
            $q->where('from_date', '<=', $toDate)
              ->where('to_date', '>=', $fromDate);
        });

        if ($user->type == 'company') {
            $query->where('created_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }
}
