<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Challans;
use Illuminate\Support\Facades\DB;

class DeleteOldChallans extends Command
{
    protected $signature = 'challans:delete-old {--dry-run}';
    protected $description = 'Delete challans before June 2025 with batch processing';

    public function handle()
    {
        $chunkSize = 500;
        $dryRun = $this->option('dry-run');

        $this->info('===== Challan Deletion Started =====');

        $query = Challans::with(['receipts.journalItems', 'receipts.journalEntry', 'journalItems', 'journalEntry', 'heads'])
            ->where('fee_month', '<', '2025-06-01') 
            // ->whereNotIn('challan_type', ['Admission', 'Registration'])
            ->orderBy('id');

        $total = $query->count();
        $this->info("Total Challans Found: $total");

        $processed = 0;

        $query->chunkById($chunkSize, function ($challans) use (&$processed, $dryRun) {

            DB::beginTransaction();

            try {
                foreach ($challans as $challan) {

                    $this->line("Processing Challan ID: {$challan->id}");

                    // ========================
                    // Receipts Deletion
                    // ========================
                    foreach ($challan->receipts as $receipt) {

                        $this->line("  Deleting Receipt: {$receipt->id}");

                        if (!$dryRun) {
                            $receipt->journalItems()->delete();
                            $receipt->journalEntry()->delete();
                            $receipt->delete();
                        }
                    }

                    // ========================
                    // Challan Journal
                    // ========================
                    if (!$dryRun) {
                        $challan->journalItems()->delete();
                        $challan->journalEntry()->delete();
                    }

                    // ========================
                    // Heads
                    // ========================
                    if (!$dryRun) {
                        $challan->heads()->delete();
                    }

                    // ========================
                    // Delete Challan
                    // ========================
                    if (!$dryRun) {
                        $challan->delete();
                    }

                    $processed++;
                }

                DB::commit();

                $this->info("Chunk processed ✅ Total so far: $processed");

            } catch (\Exception $e) {
                DB::rollBack();

                $this->error("Error in chunk: " . $e->getMessage());

                // Stop execution if something breaks
                return false;
            }
        });

        $this->info("===== Completed. Total Deleted: $processed =====");
    }
}