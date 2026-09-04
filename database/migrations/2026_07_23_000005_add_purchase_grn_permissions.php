<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $purchasePermissions = [
        'manage purchase',
        'create purchase',
        'show purchase',
        'edit purchase',
        'delete purchase',
        'send purchase',
        'convert purchase to grn',
    ];

    private array $grnPermissions = [
        'manage grn',
        'create grn',
        'show grn',
        'edit grn',
        'delete grn',
        'finalize grn',
        'show account grn',
        'account approve grn',
        'forward grn to accounts',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $companyRoleIds = $this->roleIds('company');
            $accountantRoleIds = $this->roleIds('accountant');

            foreach (array_merge($this->purchasePermissions, $this->grnPermissions) as $permissionName) {
                $permissionId = $this->permissionId($permissionName);
                $this->assignToRoles($permissionId, $companyRoleIds);

                if (in_array($permissionName, ['manage grn', 'show grn', 'approve grn', 'reject grn', 'show account grn',
                    'account approve grn',], true)) {
                    $this->assignToRoles($permissionId, $accountantRoleIds);
                }
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function permissionId(string $permissionName): int
    {
        $permissionId = DB::table('permissions')
            ->where('name', $permissionName)
            ->where('guard_name', 'web')
            ->value('id');

        if ($permissionId) {
            return (int) $permissionId;
        }

        return DB::table('permissions')->insertGetId([
            'name' => $permissionName,
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function roleIds(string $roleName)
    {
        return DB::table('roles')
            ->where('name', $roleName)
            ->where('guard_name', 'web')
            ->pluck('id');
    }

    private function assignToRoles(int $permissionId, $roleIds): void
    {
        foreach ($roleIds as $roleId) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $roleId,
            ]);
        }
    }
};
