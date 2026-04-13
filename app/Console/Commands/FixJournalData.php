<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixJournalData extends Command
{
    protected $signature = 'fix:journal-data-fast';
    protected $description = 'Fast fix of journal_items and journal_entries from student_receipts';

    public function handle()
    {
        $chunkSize = 1000;

        $minId = DB::table('student_receipts')
            ->whereDate('recipt_date', '>=', '2025-09-01')
            ->min('id');

        $maxId = DB::table('student_receipts')
            ->whereDate('recipt_date', '>=', '2025-09-01')
            ->max('id');

        if (!$minId || !$maxId) {
            $this->info('No receipts found.');
            return;
        }

        $this->info("Processing receipts from ID $minId to $maxId in chunks of $chunkSize");

        for ($start = $minId; $start <= $maxId; $start += $chunkSize) {
            $end = $start + $chunkSize - 1;

            $this->info("Processing chunk: $start → $end");

            // Step 1: Pull all receipts in this chunk
            $receipts = DB::table('student_receipts')
                ->whereDate('recipt_date', '>=', '2025-09-01')
                ->whereBetween('id', [$start, $end])
                ->get();

            if ($receipts->isEmpty()) {
                $this->info("No receipts in this chunk.");
                continue;
            }

            $voucherIds = $receipts->pluck('voucher_id')->toArray();

            // Step 2: Update journal_entries in bulk
            foreach ($receipts as $receipt) {
                DB::table('journal_entries')
                    ->where('id', $receipt->voucher_id)
                    ->update([
                        'date' => $receipt->recipt_date,
                        'created_at' => DB::raw("TIMESTAMP('$receipt->recipt_date', TIME(created_at))"),
                        'updated_at' => DB::raw("TIMESTAMP('$receipt->recipt_date', TIME(updated_at))"),
                    ]);
            }

            // Step 3: Update journal_items in bulk
            $journalItems = DB::table('journal_items')
                ->whereIn('journal', $voucherIds)
                ->get();

            foreach ($journalItems as $item) {
                $receipt = $receipts->firstWhere('voucher_id', $item->journal);
                $bankAccount = DB::table('bank_accounts')->where('id', $receipt->bank_id)->first();
                $fee_head = DB::table('fee_heads')->where('id', $item->head)->first();
                $chartAccountId = $bankAccount->chart_account_id ?? null;

                $newAccount = ($item->head == 0 && $item->credit == 0 && $chartAccountId)
                    ? $chartAccountId
                    : $fee_head->receivable_account_id;

                DB::table('journal_items')
                    ->where('id', $item->id)
                    ->update([
                        'created_at' => DB::raw("TIMESTAMP('$receipt->recipt_date', TIME(created_at))"),
                        'updated_at' => DB::raw("TIMESTAMP('$receipt->recipt_date', TIME(updated_at))"),
                        'account' => $newAccount,
                    ]);
            }

            $this->info("Chunk $start → $end processed ✅");
        }

        $this->info("All receipts processed successfully ✅");
    }
}