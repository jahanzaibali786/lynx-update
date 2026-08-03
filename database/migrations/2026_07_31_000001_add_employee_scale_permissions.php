<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        'manage employee scale',
        'create employee scale',
        'edit employee scale',
        'delete employee scale',
        'show employee scale',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $companyRoleIds = DB::table('roles')
                ->where('name', 'company')
                ->where('guard_name', 'web')
                ->pluck('id');

            foreach ($this->permissions as $permissionName) {
                $permissionId = DB::table('permissions')
                    ->where('name', $permissionName)
                    ->where('guard_name', 'web')
                    ->value('id');

                if (!$permissionId) {
                    $permissionId = DB::table('permissions')->insertGetId([
                        'name' => $permissionName,
                        'guard_name' => 'web',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                foreach ($companyRoleIds as $roleId) {
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
};
