<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $existingPermissionId = DB::table('permissions')
            ->where('name', 'create journal entry')
            ->where('guard_name', 'web')
            ->value('id');

        $permissionId = DB::table('permissions')
            ->where('name', 'create journal voucher')
            ->where('guard_name', 'web')
            ->value('id');

        if (!$permissionId) {
            $permissionId = DB::table('permissions')->insertGetId([
                'name' => 'create journal voucher',
                'guard_name' => 'web',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if (!$existingPermissionId) {
            return;
        }

        $roleIds = DB::table('role_has_permissions')
            ->where('permission_id', $existingPermissionId)
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

        $directUserPermissions = DB::table('model_has_permissions')
            ->where('permission_id', $existingPermissionId)
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

    public function down(): void
    {
        $permissionId = DB::table('permissions')
            ->where('name', 'create journal voucher')
            ->where('guard_name', 'web')
            ->value('id');

        if (!$permissionId) {
            return;
        }

        DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
        DB::table('model_has_permissions')->where('permission_id', $permissionId)->delete();
        DB::table('permissions')->where('id', $permissionId)->delete();
    }
};
