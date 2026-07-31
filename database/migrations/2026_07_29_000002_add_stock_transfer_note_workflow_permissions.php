<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $companyPermissions = [
        'manage stock transfer note',
        'create stock transfer note',
        'show stock transfer note',
        'edit stock transfer note',
        'delete stock transfer note',
        'forward stock transfer note',
        'approve stock transfer note',
        'reject stock transfer note',
        'issue stock transfer note',
    ];

    private array $branchPermissions = [
        'manage stock transfer note',
        'create stock transfer note',
        'show stock transfer note',
        'edit stock transfer note',
        'delete stock transfer note',
        'forward stock transfer note',
        'issue stock transfer note',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $companyRoleIds = $this->roleIds('company');
            $branchRoleIds = $this->roleIds('branch');

            foreach ($this->companyPermissions as $permissionName) {
                $permissionId = $this->permissionId($permissionName);
                $this->assignToRoles($permissionId, $companyRoleIds);

                if (in_array($permissionName, $this->branchPermissions, true)) {
                    $this->assignToRoles($permissionId, $branchRoleIds);
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
