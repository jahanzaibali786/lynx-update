<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserPermissionsRequest;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class UserPermissionController extends Controller
{
    public function edit()
    {
        if(!$this->canManageUserPermissions())
        {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $users = $this->availableUsers();
        $permissions = $this->availablePermissions();
        $userPermissions = $users->mapWithKeys(function ($user) {
            return [$user->id => $user->permissions->pluck('id')->values()];
        });

        return view('role.user_permissions', compact('users', 'permissions', 'userPermissions'));
    }

    public function update(UpdateUserPermissionsRequest $request)
    {
        $user = $this->availableUsers()->firstWhere('id', (int) $request->user_id);

        if(!$user)
        {
            return redirect()->back()->with('error', __('User not found or permission denied.'));
        }

        $allowedPermissionIds = $this->availablePermissions()->pluck('id');
        $selectedPermissionIds = collect($request->input('permissions', []))
            ->map(fn ($permissionId) => (int) $permissionId)
            ->intersect($allowedPermissionIds)
            ->values();

        $permissions = Permission::whereIn('id', $selectedPermissionIds)->get();
        $user->syncPermissions($permissions);

        return redirect()->route('roles.index')->with('success', __('User permissions updated successfully.'));
    }

    private function availableUsers(): Collection
    {
        $authUser = Auth::user();
        $creatorId = $authUser->creatorId();

        return User::select('id', 'name', 'email', 'type', 'created_by', 'owned_by')
            ->where(function ($query) use ($creatorId) {
                $query->where('created_by', $creatorId)
                    ->orWhere('id', $creatorId);
            })
            ->whereNotIn('type', ['client', 'clientuser', 'super admin'])
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get();
    }

    private function canManageUserPermissions(): bool
    {
        $user = Auth::user();

        return $user && in_array($user->type, ['company', 'super admin'], true) && $user->can('manage role');
    }

    private function availablePermissions(): Collection
    {
        $user = Auth::user();

        if($user->type == 'super admin' || $user->type == 'company')
        {
            return Permission::select('id', 'name')->orderBy('name')->get();
        }

        $permissions = new Collection();
        foreach($user->roles as $role)
        {
            $permissions = $permissions->merge($role->permissions);
        }

        return $permissions->unique('id')->sortBy('name')->values();
    }
}
