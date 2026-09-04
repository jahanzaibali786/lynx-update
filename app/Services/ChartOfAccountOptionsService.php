<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\DB;

class ChartOfAccountOptionsService
{
    public function forCreator(int $creatorId): array
    {
        $accounts = ChartOfAccount::from('chart_of_accounts as coa')
            ->leftJoin(
                'chart_of_account_parents as cap',
                'coa.parent',
                '=',
                'cap.id'
            )
            ->leftJoin(
                'chart_of_account_types as coat',
                'coa.type',
                '=',
                'coat.id'
            )
            ->leftJoin(
                'chart_of_account_sub_types as coast',
                'coa.sub_type',
                '=',
                'coast.id'
            )
            ->where('coa.created_by', $creatorId)
            ->select([
                'coa.id',
                'coa.code',
                'coa.name',
                'coa.parent',
                'coa.category',
                'coat.name as type_name',
                'coast.name as sub_type_name',
                DB::raw('CONCAT(coa.code, " - ", coa.name) AS code_name'),
                DB::raw('
                    CASE
                        WHEN coa.parent = 0 THEN 0
                        ELSE COALESCE(cap.account, 0)
                    END AS parent_account_id
                '),
            ])
            ->orderBy('coa.code')
            ->get();

        $accountsByParent = $accounts->groupBy(function ($account) {
            return (int) $account->parent_account_id;
        });
        $accountOptions = [];
        $visitedAccounts = [];

        $buildAccountTree = function (
            int $parentAccountId = 0,
            int $level = 0,
            string $parentPath = ''
        ) use (
            &$buildAccountTree,
            &$accountOptions,
            &$visitedAccounts,
            $accountsByParent
        ): void {
            $children = $accountsByParent->get($parentAccountId, collect());

            foreach ($children as $account) {
                $accountId = (int) $account->id;

                if (isset($visitedAccounts[$accountId])) {
                    continue;
                }

                $visitedAccounts[$accountId] = true;
                $currentPath = $parentPath === ''
                    ? $account->code_name
                    : $parentPath.' > '.$account->code_name;

                $accountOptions[] = [
                    'id' => $accountId,
                    'code' => $account->code,
                    'name' => $account->name,
                    'code_name' => $account->code_name,
                    'category' => $account->category ?: 'general',
                    'level' => $level,
                    'path' => $currentPath,
                    'type_name' => $account->type_name,
                    'sub_type_name' => $account->sub_type_name,
                ];

                $buildAccountTree($accountId, $level + 1, $currentPath);
            }
        };

        $buildAccountTree();

        return $accountOptions;
    }
}
