<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists($table, $indexName)
    {
        $database = DB::getDatabaseName();

        return !empty(DB::select(
            'select 1 from information_schema.statistics where table_schema = ? and table_name = ? and index_name = ? limit 1',
            [$database, $table, $indexName]
        ));
    }

    private function columnsExist($table, array $columns)
    {
        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }

    private function addIndexIfMissing($table, array $columns, $indexName)
    {
        if (!$this->columnsExist($table, $columns) || $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($columns, $indexName) {
            $tableBlueprint->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists($table, $indexName)
    {
        if (!$this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($indexName) {
            $tableBlueprint->dropIndex($indexName);
        });
    }

    public function up()
    {
        $this->addIndexIfMissing('journal_entries', ['owned_by', 'voucher_type', 'journal_id'], 'je_owned_type_no_idx');
        $this->addIndexIfMissing('journal_entries', ['owned_by', 'date'], 'je_owned_date_idx');
        $this->addIndexIfMissing('journal_entries', ['owned_by', 'voucher_type', 'date'], 'je_owned_type_date_idx');
        $this->addIndexIfMissing('journal_entries', ['created_by', 'date'], 'je_created_date_idx');
        $this->addIndexIfMissing('journal_entries', ['status'], 'je_status_idx');
        $this->addIndexIfMissing('journal_entries', ['bank_id'], 'je_bank_idx');
        $this->addIndexIfMissing('journal_entries', ['payment_mode'], 'je_payment_mode_idx');
        $this->addIndexIfMissing('journal_entries', ['approved_by'], 'je_approved_by_idx');
        $this->addIndexIfMissing('journal_entries', ['is_system_generated'], 'je_system_generated_idx');
        $this->addIndexIfMissing('journal_entries', ['transaction_no'], 'je_transaction_no_idx');
        $this->addIndexIfMissing('journal_entries', ['reversed_entry_id'], 'je_reversed_entry_idx');

        $this->addIndexIfMissing('journal_items', ['account'], 'ji_account_idx');
        $this->addIndexIfMissing('journal_items', ['journal', 'account'], 'ji_journal_account_idx');
        $this->addIndexIfMissing('journal_items', ['branch_id'], 'ji_branch_idx');
        $this->addIndexIfMissing('journal_items', ['branch_id', 'account'], 'ji_branch_account_idx');
        $this->addIndexIfMissing('journal_items', ['types'], 'ji_types_idx');
        $this->addIndexIfMissing('journal_items', ['category'], 'ji_category_idx');
        $this->addIndexIfMissing('journal_items', ['user_type', 'user_id'], 'ji_user_type_id_idx');
        $this->addIndexIfMissing('journal_items', ['department_id'], 'ji_department_idx');
        $this->addIndexIfMissing('journal_items', ['designation_id'], 'ji_designation_idx');
    }

    public function down()
    {
        $indexes = [
            'journal_entries' => [
                'je_owned_type_no_idx',
                'je_owned_date_idx',
                'je_owned_type_date_idx',
                'je_created_date_idx',
                'je_status_idx',
                'je_bank_idx',
                'je_payment_mode_idx',
                'je_approved_by_idx',
                'je_system_generated_idx',
                'je_transaction_no_idx',
                'je_reversed_entry_idx',
            ],
            'journal_items' => [
                'ji_account_idx',
                'ji_journal_account_idx',
                'ji_branch_idx',
                'ji_branch_account_idx',
                'ji_types_idx',
                'ji_category_idx',
                'ji_user_type_id_idx',
                'ji_department_idx',
                'ji_designation_idx',
            ],
        ];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $indexName) {
                $this->dropIndexIfExists($table, $indexName);
            }
        }
    }
};
