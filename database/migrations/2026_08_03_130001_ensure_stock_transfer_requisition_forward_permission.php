<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $permissionId = $this->permissionId('forward stock transfer order');

            foreach (['company', 'branch'] as $roleName) {
                $roleIds = DB::table('roles')
                    ->where('name', $roleName)
                    ->where('guard_name', 'web')
                    ->pluck('id');

                foreach ($roleIds as $roleId) {
                    DB::table('role_has_permissions')->updateOrInsert([
                        'permission_id' => $permissionId,
                        'role_id' => $roleId,
                    ]);
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
};
