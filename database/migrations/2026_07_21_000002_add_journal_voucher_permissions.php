<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissionMap = [
        'manage journal voucher' => 'manage journal entry',
        'create journal voucher' => 'create journal entry',
        'show journal voucher' => 'show journal entry',
        'edit journal voucher' => 'edit journal entry',
        'delete journal voucher' => 'delete journal entry',
        'print journal voucher' => 'show journal entry',
        'submit journal voucher' => 'edit journal entry',
        'approve journal voucher' => 'edit journal entry',
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->permissionMap as $permissionName => $sourcePermissionName) {
            $permissionId = DB::table('permissions')
                ->where('name', $permissionName)
                ->where('guard_name', 'web')
                ->value('id');

            if (!$permissionId) {
                $permissionId = DB::table('permissions')->insertGetId([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $this->copyRolePermissions($sourcePermissionName, $permissionId);
            $this->copyDirectUserPermissions($sourcePermissionName, $permissionId);
            $this->assignToCompany($permissionId);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function copyRolePermissions(string $sourcePermissionName, int $permissionId): void
    {
        $sourcePermissionId = DB::table('permissions')
            ->where('name', $sourcePermissionName)
            ->where('guard_name', 'web')
            ->value('id');

        if (!$sourcePermissionId) {
            return;
        }

        $roleIds = DB::table('role_has_permissions')
            ->where('permission_id', $sourcePermissionId)
            ->pluck('role_id');

        foreach ($roleIds as $roleId) {
            $exists = DB::table('role_has_permissions')
                ->where('permission_id', $permissionId)
                ->where('role_id', $roleId)
                ->exists();

            if (!$exists) {
                DB::table('role_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }
    }

    private function copyDirectUserPermissions(string $sourcePermissionName, int $permissionId): void
    {
        $sourcePermissionId = DB::table('permissions')
            ->where('name', $sourcePermissionName)
            ->where('guard_name', 'web')
            ->value('id');

        if (!$sourcePermissionId) {
            return;
        }

        $directUserPermissions = DB::table('model_has_permissions')
            ->where('permission_id', $sourcePermissionId)
            ->get(['model_type', 'model_id']);

        foreach ($directUserPermissions as $directUserPermission) {
            $exists = DB::table('model_has_permissions')
                ->where('permission_id', $permissionId)
                ->where('model_type', $directUserPermission->model_type)
                ->where('model_id', $directUserPermission->model_id)
                ->exists();

            if (!$exists) {
                DB::table('model_has_permissions')->insert([
                    'permission_id' => $permissionId,
                    'model_type' => $directUserPermission->model_type,
                    'model_id' => $directUserPermission->model_id,
                ]);
            }
        }
    }

    private function assignToCompany(int $permissionId): void
    {
        $companyRoleId = DB::table('roles')
            ->where('name', 'company')
            ->where('guard_name', 'web')
            ->value('id');

        if (!$companyRoleId) {
            return;
        }

        $exists = DB::table('role_has_permissions')
            ->where('permission_id', $permissionId)
            ->where('role_id', $companyRoleId)
            ->exists();

        if (!$exists) {
            DB::table('role_has_permissions')->insert([
                'permission_id' => $permissionId,
                'role_id' => $companyRoleId,
            ]);
        }
    }
};
