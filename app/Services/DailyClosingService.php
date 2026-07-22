<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankTransfer;
use App\Models\DailyClosing;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DailyClosingService
{
    private const DENOMINATIONS = [5000, 1000, 500, 100, 50, 20, 10, 5, 2, 1];

    public function employeeOptions(): array
    {
        return Employee::where('owned_by', Auth::user()->creatorId())
            ->where('is_res_ter', 0)
            ->orderBy('name', 'asc')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function transferSummary(string $fromDate, string $toDate, ?int $excludeId = null): array
    {
        $overlap = $this->overlap($fromDate, $toDate, $excludeId);
        if ($overlap) {
            return [
                'overlap' => true,
                'message' => __('Selected date range overlaps with an existing Daily Closing (from :from to :to)', [
                    'from' => $overlap->from_date->format('d-M-Y'),
                    'to' => $overlap->to_date->format('d-M-Y'),
                ]),
                'transfers' => [],
                'total_received' => 0,
            ];
        }

        $transfers = $this->transferQuery($fromDate, $toDate)->get();
        $bankAccounts = BankAccount::whereIn('id', $transfers->pluck('from_account')->filter()->unique())
            ->get(['id', 'type', 'holder_name', 'bank_name'])
            ->keyBy('id');
        $branches = User::whereIn('id', $transfers->pluck('owned_by')->filter()->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        $rows = [];
        $totalReceived = 0;

        foreach ($transfers as $index => $transfer) {
            $rows[] = [
                'sr_no' => $index + 1,
                'branch_name' => optional($branches->get($transfer->owned_by))->name ?? '-',
                'period' => Carbon::parse($transfer->date)->format('d-M-y'),
                'slip_no' => $transfer->reference ?? '-',
                'ac' => $this->accountAbbreviation($bankAccounts->get($transfer->from_account)),
                'amount' => (float) $transfer->amount,
            ];

            $totalReceived += (float) $transfer->amount;
        }

        return [
            'overlap' => false,
            'transfers' => $rows,
            'total_received' => $totalReceived,
        ];
    }

    public function create(array $payload): DailyClosing
    {
        return DB::transaction(function () use ($payload) {
            return DailyClosing::create($this->closingData($payload));
        });
    }

    public function update(DailyClosing $dailyClosing, array $payload): DailyClosing
    {
        return DB::transaction(function () use ($dailyClosing, $payload) {
            $dailyClosing->update($this->closingData($payload, $dailyClosing->id, false));

            return $dailyClosing->fresh();
        });
    }

    public function delete(DailyClosing $dailyClosing): void
    {
        DB::transaction(function () use ($dailyClosing) {
            $dailyClosing->delete();
        });
    }

    public function setApprovalStatus(DailyClosing $dailyClosing, ?string $status = null): DailyClosing
    {
        $dailyClosing->status = in_array($status, ['approved', 'pending'], true)
            ? $status
            : ($dailyClosing->status === 'approved' ? 'pending' : 'approved');
        $dailyClosing->save();

        return $dailyClosing;
    }

    public function printRows(DailyClosing $dailyClosing): array
    {
        $transfers = $this->transferQuery($dailyClosing->from_date->toDateString(), $dailyClosing->to_date->toDateString())->get();
        $bankAccounts = BankAccount::whereIn('id', $transfers->pluck('from_account')->filter()->unique())
            ->get(['id', 'type', 'holder_name', 'bank_name'])
            ->keyBy('id');
        $branches = User::whereIn('id', $transfers->pluck('owned_by')->filter()->unique())
            ->get(['id', 'name'])
            ->keyBy('id');

        return $transfers->map(function ($transfer, $index) use ($bankAccounts, $branches) {
            return (object) [
                'sr_no' => $index + 1,
                'branch_name' => optional($branches->get($transfer->owned_by))->name ?? '-',
                'period' => Carbon::parse($transfer->date)->format('d-M-Y'),
                'slip_no' => $transfer->reference ?? '-',
                'ac' => $this->accountAbbreviation($bankAccounts->get($transfer->from_account)),
                'amount' => (float) $transfer->amount,
            ];
        })->all();
    }

    public function overlap(string $fromDate, string $toDate, ?int $excludeId = null): ?DailyClosing
    {
        $user = Auth::user();

        $query = DailyClosing::where(function ($q) use ($fromDate, $toDate) {
            $q->where('from_date', '<=', $toDate)
                ->where('to_date', '>=', $fromDate);
        });

        if ($user->type === 'company') {
            $query->where('created_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }

    private function closingData(array $payload, ?int $excludeId = null, bool $isCreate = true): array
    {
        if ((int) ($payload['issued_by_id'] ?? 0) === (int) ($payload['received_by_id'] ?? 0)) {
            throw new InvalidArgumentException(__('Issued By and Received By cannot be the same employee.'), 422);
        }

        $overlap = $this->overlap($payload['from_date'], $payload['to_date'], $excludeId);
        if ($overlap) {
            throw new InvalidArgumentException(__('Selected date range overlaps with an existing Daily Closing (from :from to :to)', [
                'from' => $overlap->from_date->format('d-M-Y'),
                'to' => $overlap->to_date->format('d-M-Y'),
            ]), 422);
        }

        $user = Auth::user();
        $issuedEmployee = Employee::find($payload['issued_by_id']);
        $receivedEmployee = Employee::find($payload['received_by_id']);
        $totalReceived = $this->transferQuery($payload['from_date'], $payload['to_date'])->sum('amount');
        $totalDeposited = 0;

        $data = [
            'from_date' => $payload['from_date'],
            'to_date' => $payload['to_date'],
            'deposit_date' => $payload['deposit_date'],
            'issued_by_id' => $payload['issued_by_id'],
            'received_by_id' => $payload['received_by_id'],
            'note' => $payload['note'] ?? null,
            'issued_by' => optional($issuedEmployee)->name,
            'received_by' => optional($receivedEmployee)->name,
        ];

        foreach (self::DENOMINATIONS as $denomination) {
            $count = (int) ($payload['note_' . $denomination] ?? 0);
            $data['note_' . $denomination] = $count;
            $totalDeposited += $count * $denomination;
        }

        $data['total_income_received'] = $totalReceived;
        $data['total_income_deposited'] = $totalDeposited;
        $data['difference'] = $totalReceived - $totalDeposited;

        if ($isCreate) {
            $data['status'] = 'pending';
            $data['created_by'] = $user->creatorId();
            $data['owned_by'] = $user->type === 'company' ? $user->creatorId() : $user->ownedId();
        }

        return $data;
    }

    private function transferQuery(string $fromDate, string $toDate)
    {
        $user = Auth::user();
        $query = BankTransfer::whereBetween('date', [$fromDate, $toDate]);

        if ($user->type === 'company') {
            $query->where('created_by', $user->creatorId());
        } else {
            $query->where('owned_by', $user->ownedId());
        }

        return $query->orderBy('date')->orderBy('id');
    }

    private function accountAbbreviation(?BankAccount $bankAccount): string
    {
        if (!$bankAccount) {
            return 'RV';
        }

        $type = strtolower($bankAccount->type ?? '');
        if ($type === 'head_imprest') {
            return 'Imp';
        }

        $holder = strtolower($bankAccount->holder_name ?? '');
        $bank = strtolower($bankAccount->bank_name ?? '');

        if (str_contains($holder, 'canteen') || str_contains($bank, 'canteen')) {
            return 'Canteen';
        }

        if (str_contains($holder, 'sp') || str_contains($bank, 'sp')) {
            return 'SP';
        }

        return 'RV';
    }
}
