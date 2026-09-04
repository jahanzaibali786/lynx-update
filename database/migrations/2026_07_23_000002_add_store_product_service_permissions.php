<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permissions = [
        'manage warehouse',
        'create warehouse',
        'show warehouse',
        'edit warehouse',
        'delete warehouse',
        'manage product & service',
        'create product & service',
        'show product & service',
        'edit product & service',
        'delete product & service',
        'show sale price product & service',
        'show purchase price product & service',
        'show quantity product & service',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            $now = now();
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
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                foreach ($companyRoleIds as $companyRoleId) {
                    DB::table('role_has_permissions')->updateOrInsert([
                        'permission_id' => $permissionId,
                        'role_id' => $companyRoleId,
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
