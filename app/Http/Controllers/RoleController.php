<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Auth;

class RoleController extends Controller
{

    public function index()
    {
        if(\Auth::user()->can('manage role'))
        {

            $roles = Role::where('created_by', '=', \Auth::user()->creatorId())->where('created_by', '=', \Auth::user()->creatorId())->get();

            return view('role.index')->with('roles', $roles);
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }

    }


    public function create()
    {
        if(\Auth::user()->can('create role'))
        {
            $user = \Auth::user();
            if($user->type == 'super admin' || $user->type == 'company')
            {
                $permissions = Permission::all()->pluck('name', 'id')->toArray();
            }
            else
            {
                $permissions = new Collection();
                foreach($user->roles as $role)
                {
                    $permissions = $permissions->merge($role->permissions);
                }
                $permissions = $permissions->pluck('name', 'id')->toArray();
            }

            $customPermissions = $this->customPermissions($permissions);

            return view('role.create', compact('permissions', 'customPermissions'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }

    }


    public function store(Request $request)
    {
        if(\Auth::user()->can('create role'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:100|unique:roles,name,NULL,id,created_by,' . \Auth::user()->creatorId(),
                                   'permissions' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $name             = $request['name'];
            $role             = new Role();
            $role->name       = $name;
            $role->created_by = \Auth::user()->creatorId();
            $permissions      = $request['permissions'];
            $role->save();

            foreach($permissions as $permission)
            {
                $p = Permission::where('id', '=', $permission)->firstOrFail();
                $role->givePermissionTo($p);
            }

            return redirect()->route('roles.index')->with(
                'Role successfully created.', 'Role ' . $role->name . ' added!'
            );
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }


    }

    public function edit(Role $role)
    {
        if(\Auth::user()->can('edit role'))
        {

            $user = \Auth::user();
            if($user->type == 'super admin' || $user->type == 'company')
            {
                $permissions = Permission::all()->pluck('name', 'id')->toArray();
            }
            else
            {
                $permissions = new Collection();
                foreach($user->roles as $role1)
                {
                    $permissions = $permissions->merge($role1->permissions);
                }
                $permissions = $permissions->pluck('name', 'id')->toArray();
            }

            $customPermissions = $this->customPermissions($permissions);

            return view('role.edit', compact('role', 'permissions', 'customPermissions'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }


    }

    public function update(Request $request, Role $role)
    {

        if(\Auth::user()->can('edit role'))
        {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required|max:100|unique:roles,name,' . $role['id'] . ',id,created_by,' . \Auth::user()->creatorId(),
                                   'permissions' => 'required',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $input       = $request->except(['permissions']);
            $permissions = $request['permissions'];
            $role->fill($input)->save();

            $p_all = Permission::all();

            foreach($p_all as $p)
            {
                $role->revokePermissionTo($p);
            }

            foreach($permissions as $permission)
            {

                $p = Permission::where('id', '=', $permission)->firstOrFail();
                $role->givePermissionTo($p);
            }

            return redirect()->route('roles.index')->with(
                'Role successfully updated.', 'Role ' . $role->name . ' updated!'
            );
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }

    }


    public function destroy(Role $role)
    {
        if(\Auth::user()->can('delete role'))
        {
            $role->delete();

            return redirect()->route('roles.index')->with(
                'success', 'Role successfully deleted.'
            );
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }


    }

    private function customPermissions(array $permissions): array
    {
        $modules = [
            'user', 'role', 'client', 'product & service', 'constant unit', 'constant tax', 'constant category',
            'company settings', 'permission', 'language', 'crm dashboard', 'lead', 'pipeline', 'lead stage',
            'source', 'label', 'deal', 'stage', 'task', 'form builder', 'form response', 'contract',
            'contract type', 'project dashboard', 'project', 'milestone', 'grant chart', 'project stage',
            'timesheet', 'expense', 'project task', 'activity', 'CRM activity', 'project task stage',
            'bug report', 'bug status', 'hrm dashboard', 'employee', 'employee profile', 'department',
            'designation', 'branch', 'document type', 'document', 'payslip type', 'allowance', 'commission',
            'allowance option', 'loan option', 'deduction option', 'loan', 'saturation deduction',
            'other payment', 'overtime', 'set salary', 'pay slip', 'company policy', 'appraisal',
            'goal tracking', 'goal type', 'indicator', 'event', 'meeting', 'training', 'trainer',
            'training type', 'award', 'award type', 'resignation', 'travel', 'promotion', 'complaint',
            'warning', 'termination', 'termination type', 'job application', 'job application note',
            'job onBoard', 'job category', 'job', 'job stage', 'custom question', 'interview schedule',
            'estimation', 'holiday', 'transfer', 'announcement', 'leave', 'leave type', 'attendance',
            'account dashboard', 'proposal', 'invoice', 'bill', 'revenue', 'payment', 'proposal product',
            'invoice product', 'bill product', 'goal', 'credit note', 'debit note', 'bank account',
            'bank transfer', 'transaction', 'customer', 'vender', 'constant custom field', 'assets',
            'chart of account', 'journal entry', 'journal voucher', 'report', 'warehouse', 'purchase', 'pos', 'barcode',
            'companybranch', 'space', 'spacetype', 'chair', 'clientuser', 'ismail', 'vistor',
        ];

        $actions = [
            'view', 'add', 'move', 'manage', 'create', 'edit', 'delete', 'show', 'send',
            'create payment', 'delete payment', 'income', 'expense', 'income vs expense',
            'loss & profit', 'tax', 'invoice', 'bill', 'duplicate', 'balance sheet',
            'ledger', 'trial balance',
        ];

        $standardPermissions = [];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $standardPermissions[] = $action . ' ' . $module;
            }
        }

        return collect($permissions)
            ->reject(function ($permissionName) use ($standardPermissions) {
                return in_array($permissionName, $standardPermissions, true);
            })
            ->toArray();
    }
}
