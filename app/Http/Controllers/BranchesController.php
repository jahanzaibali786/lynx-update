<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CustomField;
use App\Models\Employee;
use App\Models\Plan;
use App\Models\SchoolDetails;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class BranchesController extends Controller
{

    public function __construct()
    {
        $this->middleware(
            [
                'auth',
                'XSS',
            ]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Auth::user()->type == 'company') {
           $user    = \Auth::user();
            $branches = User::where('created_by', '=', $user->creatorId())->where('type', '=', 'branch')->get();
        } else {
            $user    = \Auth::user();
            $branches = User::where('id', '=', $user->id)->where('type', '=', 'branch')->get();
        }
        // dd($branches);
        return view('branches.index', compact('branches'));
        // if(\Auth::user()->can('view companybranch'))
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if(\Auth::user()->can('create companybranch'))
        {
            if($request->ajax)
            {
                return view('branches.createAjax');
            }
            else
            {
                $customFields = CustomField::where('module', '=', 'client')->get();
                $users = Employee::where('created_by', \Auth::user()->creatorId())
                    ->where('is_active', 1)
                    ->orderBy('name')
                    ->get()
                    ->mapWithKeys(function ($employee) {
                        $employeeCode = trim((string) ($employee->employee_id ?? ''));
                        $employeeName = trim((string) ($employee->name ?? ''));
                        $label = trim($employeeCode . ' - ' . $employeeName, ' -');

                        return [$employee->user_id => $label];
                    });
                $bankAccount = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' (',holder_name,')') AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                return view('branches.create', compact('customFields','users','bankAccount'));
            }
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        if(\Auth::user()->can('create companybranch'))
        {
            $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->where('created_by', '=', \Auth::user()->creatorId())->first();
            DB::beginTransaction();
            try {
            $user      = \Auth::user();
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required',
                ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                if($request->ajax)
                {
                    return response()->json(['error' => $messages->first()], 401);
                }
                else
                {
                    return redirect()->back()->with('error', $messages->first());
                }
            }
            $objCustomer    = \Auth::user();
            $creator        = User::find($objCustomer->creatorId());
            $total_branches = User::where('created_by', '=', \Auth::user()->creatorId())->where('type','branch')->count();
            $plan           = Plan::find($creator->plan);

            if($total_branches < $plan->max_branch || $plan->max_branch == -1)
            {
                $branches = User::create(
                    [
                        'name' => $request->name,
                        'email' => $request->email,
                        'job_title' => $request->job_title,
                        'password' => Hash::make($request->password),
                        'type' => 'branch',
                        'lang' => !empty($default_language) ? $default_language->value : 'en',
                        'owned_by' => $user->ownedId(),
                        'created_by' => $user->creatorId(),
                        'email_verified_at' => date('Y-m-d H:i:s'),
                    ]
                );

                $role_r = Role::findByName('branch');
                $branches->assignRole($role_r);
                // $branches->password = $request->password;

                $user->branchDefaultBankAccount($branches->id,$branches->created_by);

                if($user){
                    $branch_school =new SchoolDetails();
                    $branch_school->branch_id = $branches->id;
                    $branch_school->name = $request->name;
                    $branch_school->branch_code=$request->b_code;
                    $branch_school->phone_no = $request->phone;
                    $branch_school->address = $request->address;
                    $branch_school->headmaster= $request->hod;
                    $branch_school->eobi_reg_no= $request->eobi_reg_no;
                    $branch_school->pessi_reg_no= $request->pessi_reg_no;
                    $branch_school->eobi_values= $request->eobi_values;
                    $branch_school->pessi_values= $request->pessi_values;
                    $branch_school->save();
                }
                // Utility::chartOfAccountTypeDataBranch($branches->id,$branches->created_by);
                // Utility::chartOfAccountData($user);
                // default chart of account for new company
                // Utility::chartOfAccountData1Branch($branches->id,$branches->created_by);
                // Utility::virtual_office_branch($branches->id,$branches->created_by);
                // Utility::services_branch($branches->id,$branches->created_by);
                // Utility::security_services_branch($branches->id,$branches->created_by);

                // //Send Email
                // $setings = Utility::settings();

                // if($setings['new_client'] == 1)
                // {

                //     $clientArr = [
                //         'client_name' => $client->name,
                //         'client_email' => $client->email,
                //         'client_password' =>  $client->password,
                //     ];
                //     $resp = Utility::sendEmailTemplate('new_client', [$client->email], $clientArr);
                //     return redirect()->route('clients.index')->with('success', __('Client successfully added.') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
                // }
                DB::commit();
                return redirect()->route('branches.index')->with('success', __('Branch successfully created.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
            }


        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return redirect()->back()->with('error', $e);
        }
        }
        else
        {
            if($request->ajax)
            {
                return response()->json(['error' => __('Permission Denied.')], 401);
            }
            else
            {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $branch,)
    {
        $school = SchoolDetails::where( 'branch_id', $branch->id)->first();
        if(\Auth::user()->can('edit companybranch'))
        {
            $user = \Auth::user();
            if($branch->created_by == $user->creatorId())
            {
                $users = Employee::where('created_by',\Auth::user()->creatorId())->get()->pluck('name','user_id');
                $bankAccount = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' (',holder_name,')') AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $branch->customField = CustomField::getData($branch, 'branch');
                $customFields        = CustomField::where('module', '=', 'branch')->get();

                return view('branches.edit', compact('branch', 'customFields','users','school','bankAccount'));
            }
            else
            {
                return response()->json(['error' => __('Invalid Branch.')], 401);
            }
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(User $branch, Request $request)
    {
        if(\Auth::user()->can('edit companybranch'))
        {
            $user = \Auth::user();
            if($branch->created_by == $user->creatorId())
            {
                $validation = [
                    'name' => 'required',
                    // 'email' => 'required|email|unique:users,email,' . $branch->id,
                ];

                $post         = [];
                $post['name'] = $request->name;
                if(!empty($request->password))
                {
                    $validation['password'] = 'required';
                    $post['password']       = Hash::make($request->password);
                }

                $validator = \Validator::make($request->all(), $validation);
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }
                $post['email'] = $request->email;

                $branch->update($post);
                CustomField::saveData($branch, $request->customField);
                $branch_school =SchoolDetails::where('branch_id', $branch->id)->first();
                if(!$branch_school){
                    $branch_school =new SchoolDetails();
                    $branch_school->branch_id = $branch->id;
                }
                $branch_school->name = $request->name;
                $branch_school->branch_code=$request->b_code;
                $branch_school->phone_no = $request->phone;
                $branch_school->address = $request->address;
                $branch_school->bank = $request->bank;
                $branch_school->headmaster= $request->hod;
                $branch_school->eobi_reg_no= $request->eobi_reg_no;
                $branch_school->pessi_reg_no= $request->pessi_reg_no;
                $branch_school->eobi_values= $request->eobi_values;
                $branch_school->pessi_values= $request->pessi_values;
                $branch_school->save();
                // dd($branch_school);
                return redirect()->back()->with('success', __('Branch Updated Successfully!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Invalid Branch.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $branch)
    {
        $user = \Auth::user();
        if($branch->created_by == $user->creatorId())
        {
            if($branch->delete_status == 0){
                $branch->delete_status = 1;
                $branch->save();
                return redirect()->back()->with('success', __('Branch Active Successfully!'));
            }else{
                $branch->delete_status = 0;
                $branch->save();
                return redirect()->back()->with('success', __('Branch Deactivate Successfully!'));
            }
            // $branch->delete();
            return redirect()->back()->with('success', __('Branch Deleted Successfully!'));

        }
        else
        {
            return redirect()->back()->with('error', __('Invalid Branch.'));
        }
    }

    public function branchPassword($id)
    {
        $eId        = \Crypt::decrypt($id);
        $user = User::find($eId);
        $client = User::where('created_by', '=', $user->creatorId())->where('type', '=', 'branch')->first();


        return view('branches.reset', compact('user', 'client'));
    }

    public function branchPasswordReset(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'password' => 'required|confirmed|same:password_confirmation',
                           ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $user = User::where('id', $id)->first();
        $user->forceFill([
                             'password' => Hash::make($request->password),
                         ])->save();

        return redirect()->route('branches.index')->with(
            'success', 'Branch Password successfully updated.'
        );


    }

}
