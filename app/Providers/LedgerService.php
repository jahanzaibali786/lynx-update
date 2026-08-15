<?php

namespace App\Providers;

use App\Models\Utility;
use App\Models\User;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
class LedgerService 
{
    /**
     * Register services.
     *
     * @return void
     */

    public static function newpriceFormat($price)
{
    static $settings = null;
    static $decimals = null;

    if ($settings === null) {
        $settings = Utility::settings();                    
        $decimals = Utility::getValByName('decimal_number');
    }

    $formatted = number_format($price, $decimals);

    return ($settings['site_currency_symbol_position'] == "pre"
            ? $settings['site_currency_symbol'] . $formatted
            : $formatted . $settings['site_currency_symbol']);
}
   
    public function register()
    {
        //
    }
    public static function formatVoucherNumber($number, $type = 'JV')
    {
        $prefixes = [
            'JV' =>  'JV',
            'BRV' => '#BRV',
            'BPV' => '#BPV',
            'CRV' => '#CRV',
            'CPV' => '#CPV',
        ];
        $prefix = $prefixes[$type] ?? '';

        return $prefix . sprintf("%05d", $number);
    }
    public static function VoucherRoute( $type = 'JV')
    {
       $routes = [
        'JV'  => 'journal-entry.show',
        'BRV' => 'bank-recipt-voucher.show',
        'BPV' => 'bank-payment-voucher.show',
        'CRV' => 'cash-recipt-voucher.show',
        'CPV' => 'cash-payment-voucher.show',
    ];
        return $routes[$type] ?? null;
    }
    

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
  public function buildLedgerRows(Collection $chartAccounts, string $type, $start, $end, $branch = null): Collection
{
    $start = Carbon::parse($start);
    $end = Carbon::parse($end);

    $rows = collect();
    $totals = ['balance' => 0];

    foreach ($chartAccounts as $account) {
        $data = Utility::getAccountData($account->id, $start, $end, null, $branch);

        // Skip if no journal data
        if (empty($data['journalItem'])) {
            continue;
        }

        $accountName = $account->name; // Avoid repeated DB lookup
        $balance = 0;
        $hasAddedOpening = false;

        foreach ($data['journalItem'] as $journalItemData) {
            if ($type === 'other' && !$hasAddedOpening) {
                if (in_array($journalItemData->type_name, ['Assets', 'Liabilities'])) {
                    $oldBalance = Utility::old_trialBalance($journalItemData->account, $start);
                    $openingBalance = ($oldBalance->totalDebit ?? 0) - ($oldBalance->totalCredit ?? 0);

                    if ($openingBalance != 0) {
                        $rows->push([
                            'account'   => '',
                            'category' => '',
                            'user_type' => '',
                            'user_name' => '',
                            'route' => null,
                            'memo'   => null,
                            'journal'   => null,
                            'voucher'   => 'Previous Balance',
                            'date'      => $start->format('d-M-Y'),
                            'debit'     => 0,
                            'credit'    => 0,
                            'balance'   => $openingBalance,
                        ]);
                        $balance = $openingBalance;
                        $totals['balance'] = $openingBalance;
                    }

                    $hasAddedOpening = true;
                }
            }

            // Update balance
            $balance += $journalItemData->debit - $journalItemData->credit;
            $totals['balance'] = $balance;

            $rows->push([
                'account' => $accountName,
                'category' => $journalItemData->category ?? '-',
                'user_type' => $journalItemData->ledger_user_type ?? '-',
                'user_name' => $journalItemData->ledger_user_name ?? '-',
                'memo' => $journalItemData->description,
                'route' => $this->VoucherRoute($journalItemData->voucher_type), 
                'journal' => $journalItemData->journal,
                'voucher' => $this->formatVoucherNumber($journalItemData->journal_id, $journalItemData->voucher_type),
                'date'    => $journalItemData->created_at->format('d-M-Y'),
                'debit'   => $journalItemData->debit,
                'credit'  => $journalItemData->credit,
                'balance' => $totals['balance'],
            ]);
        }
    }
// dd($rows);
    return $rows;
}

}
