<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameDemandOrderPermissionsToStockTransferOrder extends Migration
{
    private array $map = [
        'manage demand order'            => 'manage stock transfer order',
        'create demand order'            => 'create stock transfer order',
        'show demand order'              => 'show stock transfer order',
        'edit demand order'              => 'edit stock transfer order',
        'delete demand order'            => 'delete stock transfer order',
        'forward demand order'           => 'forward stock transfer order',
        'approve demand order'           => 'approve stock transfer order',
        'reject demand order'            => 'reject stock transfer order',
        'convert demand order to invoice'=> 'convert stock transfer order to invoice',
    ];

    public function up()
    {
        foreach ($this->map as $old => $new) {
            // Rename the permission itself if the new name doesn't exist yet
            $existingNew = DB::table('permissions')->where('name', $new)->first();
            $existingOld = DB::table('permissions')->where('name', $old)->first();

            if ($existingOld && !$existingNew) {
                DB::table('permissions')
                    ->where('name', $old)
                    ->update(['name' => $new]);
            } elseif ($existingOld && $existingNew) {
                // Both exist — merge: move role_has_permissions references to new permission then delete old
                DB::table('role_has_permissions')
                    ->where('permission_id', $existingOld->id)
                    ->whereNotExists(function ($query) use ($existingNew) {
                        $query->from('role_has_permissions as rp2')
                            ->whereColumn('rp2.role_id', 'role_has_permissions.role_id')
                            ->where('rp2.permission_id', $existingNew->id);
                    })
                    ->update(['permission_id' => $existingNew->id]);

                DB::table('model_has_permissions')
                    ->where('permission_id', $existingOld->id)
                    ->whereNotExists(function ($query) use ($existingNew) {
                        $query->from('model_has_permissions as mp2')
                            ->whereColumn('mp2.model_id', 'model_has_permissions.model_id')
                            ->whereColumn('mp2.model_type', 'model_has_permissions.model_type')
                            ->where('mp2.permission_id', $existingNew->id);
                    })
                    ->update(['permission_id' => $existingNew->id]);

                // Remove orphaned old permission rows now
                DB::table('role_has_permissions')->where('permission_id', $existingOld->id)->delete();
                DB::table('model_has_permissions')->where('permission_id', $existingOld->id)->delete();
                DB::table('permissions')->where('id', $existingOld->id)->delete();
            }
            // If only new exists, nothing to do.
        }

        // Clear Spatie permission cache
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down()
    {
        // Reverse: rename stock transfer order permissions back to demand order
        $reverseMap = array_flip($this->map);
        foreach ($reverseMap as $new => $old) {
            DB::table('permissions')
                ->where('name', $new)
                ->update(['name' => $old]);
        }

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
