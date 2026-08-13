<?php

namespace App\Http\Controllers;

use App\Exports\VenderExport;
use App\Imports\VenderImport;
use App\Models\ChartOfAccount;
use App\Models\CustomField;
use App\Models\Transaction;
use App\Models\Utility;
use App\Models\Vender;
use Auth;
use App\Models\User;
use App\Models\Plan;
use App\Models\Purchase;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;

class VenderController extends Controller
{

    public function dashboard()
    {
        $data['billChartData'] = \Auth::user()->billChartData();

        return view('vender.dashboard', $data);
    }

    public function index()
    {
        if (\Auth::user()->can('manage vender')) {
    
            // dropdown list (id => company name - vendor name)
            $vendorList = Vender::optionsForCreator(\Auth::user()->creatorId(), null);
    
            // base query
            $query = Vender::with(['ChartAccount' => function ($accountQuery) {
                $accountQuery->where('created_by', \Auth::user()->creatorId());
            }])->where('created_by', \Auth::user()->creatorId());
    
            // filter by vendor id from dropdown
            if (!empty($_GET['vender'])) {
                $query->where('id', $_GET['vender']);
            }
    
            // optional: text search by name
            if (!empty($_GET['name'])) {
                $search = $_GET['name'];
                $query->where(function ($vendorQuery) use ($search) {
                    $vendorQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('company_name', 'like', '%' . $search . '%');
                });
            }
    
            // paginate results
            $venders = $query->paginate(25);
    
            return view('vender.index', compact('venders', 'vendorList'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    


    public function create()
    {
        if(\Auth::user()->can('create vender'))
        {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'vendor')->get();
            $accounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.parent')
            ->where('parent', '=', 0)
            ->where('created_by', \Auth::user()->creatorId())->get()
            ->toarray();
            $subAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account');
            $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $subAccounts->where('chart_of_accounts.parent', '!=', 0);
            $subAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
            $subAccounts = $subAccounts->get()->toArray();
            return view('vender.create', compact('customFields','accounts','subAccounts'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function store(Request $request)
    {
        if(\Auth::user()->can('create vender'))
        {
            // Concatenate name fields before validation
            $fullName = trim(
                ($request->name_prefix ? $request->name_prefix . ' ' : '') .
                ($request->first_name ? $request->first_name . ' ' : '') .
                ($request->middle_initial ? $request->middle_initial . ' ' : '') .
                ($request->last_name ? $request->last_name : '')
            );

            // Merge the concatenated name into the request
            $request->merge([
                'name' => $fullName,
                'contact' => $request->main_phone // Use main_phone as contact
            ]);

            $rules = [
                'first_name' => 'required',
                'last_name' => 'required',
                'account_id' => 'required',
                'main_phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('venders')->where(function ($query) {
                        return $query->where('created_by', \Auth::user()->creatorId());
                    })
                ],
            ];

            $validator = \Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $messages->first()], 422);
                }

                return redirect()->route('vender.index')->with('error', $messages->first());
            }
                $objVendor    = \Auth::user();
                $creator      = User::find($objVendor->creatorId());
                $total_vendor = $objVendor->countVenders();
                $plan         = Plan::find($creator->plan);
                $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
                if($total_vendor < $plan->max_venders || $plan->max_venders == -1)
                {
                    $vender                   = new Vender();
                    $vender->vender_id        = $this->venderNumber();
                    $vender->name             = $request->name;
                    $vender->contact          = $request->contact;
                    $vender->email            = $request->email;
                    $vender->tax_number       =$request->tax_number;
                    $vender->owned_by         = \Auth::user()->ownedId();
                    $vender->created_by       = \Auth::user()->creatorId();

                    // New vendor fields
                    $vender->company_name     = $request->company_name;
                    $vender->name_prefix      = $request->name_prefix;
                    $vender->first_name       = $request->first_name;
                    $vender->middle_initial   = $request->middle_initial;
                    $vender->last_name        = $request->last_name;
                    $vender->job_title        = $request->job_title;
                    $vender->main_phone_type  = $request->main_phone_type;
                    $vender->main_phone       = $request->main_phone;
                    $vender->work_phone_type  = $request->work_phone_type;
                    $vender->work_phone       = $request->work_phone;
                    $vender->main_email_type  = $request->main_email_type;
                    $vender->cc_email_type    = $request->cc_email_type;
                    $vender->cc_email         = $request->cc_email;
                    $vender->website_type     = $request->website_type;
                    $vender->website          = $request->website;
                    $vender->other1_type      = $request->other1_type;
                    $vender->other1           = $request->other1;

                    $vender->billing_name     = $request->billing_name;
                    $vender->billing_country  = $request->billing_country;
                    $vender->billing_state    = $request->billing_state;
                    $vender->billing_city     = $request->billing_city;
                    $vender->billing_phone    = $request->billing_phone;
                    $vender->billing_zip      = $request->billing_zip;
                    $vender->billing_address  = $request->billing_address;
                    $vender->shipping_name    = $request->shipping_name;
                    $vender->shipping_country = $request->shipping_country;
                    $vender->shipping_state   = $request->shipping_state;
                    $vender->shipping_city    = $request->shipping_city;
                    $vender->shipping_phone   = $request->shipping_phone;
                    $vender->shipping_zip     = $request->shipping_zip;
                    $vender->shipping_address = $request->shipping_address;
                    $vender->lang             = !empty($default_language) ? $default_language->value : '';
                    $vender->account_id       = $request->account_id;
                    $vender->save();
                    CustomField::saveData($vender, $request->customField);
                }
                else
                {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => __('Your user limit is over, Please upgrade plan.')], 422);
                    }
                    return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
                }
                $role_r = Role::where('name', '=', 'vender')->firstOrFail();
                $vender->assignRole($role_r); //Assigning role to user
                $vender->type     = 'Vender';


            //For Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
            $vendorNotificationArr = [
                'user_name' => \Auth::user()->name,
                'vendor_name' => $vender->name,
                'vendor_email' => $vender->email,
            ];

            //Twilio Notification
            if(isset($setting['twilio_vender_notification']) && $setting['twilio_vender_notification'] ==1)
            {
                Utility::send_twilio_msg($request->contact,'new_vendor', $vendorNotificationArr);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Vendor successfully created.'),
                    'vendor' => [
                        'id' => $vender->id,
                        'name' => $vender->display_name,
                        'company_name' => $vender->company_name,
                        'vendor_name' => $vender->name,
                    ],
                ]);
            }

            return redirect()->route('vender.index')->with('success', __('Vendor successfully created.'));
        }
        else
        {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
            }
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($ids)
    {
        if (!\Auth::user()->can('show vender')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        try {
            $id       = Crypt::decrypt($ids);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', __('Vendor Not Found.'));
        }

        $vendor = Vender::with(['ChartAccount' => function ($accountQuery) {
            $accountQuery->where('created_by', \Auth::user()->creatorId());
        }])
            ->where('created_by', \Auth::user()->creatorId())
            ->findOrFail($id);

        return view('vender.show', compact('vendor'));
    }


    public function edit($id)
    {
        if(\Auth::user()->can('edit vender'))
        {
            $vender              = Vender::find($id);
            $vender->customField = CustomField::getData($vender, 'vendor');
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'vendor')->get();
            $accounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_accounts.parent')
            ->where('parent', '=', 0)
            ->where('created_by', \Auth::user()->creatorId())->get()->toarray();
            $subAccounts = ChartOfAccount::select('chart_of_accounts.id', 'chart_of_accounts.code', 'chart_of_accounts.name', 'chart_of_account_parents.account');
            $subAccounts->leftjoin('chart_of_account_parents', 'chart_of_accounts.parent', 'chart_of_account_parents.id');
            $subAccounts->where('chart_of_accounts.parent', '!=', 0);
            $subAccounts->where('chart_of_accounts.created_by', \Auth::user()->creatorId());
            $subAccounts = $subAccounts->get()->toArray();
            return view('vender.edit', compact('vender', 'customFields','accounts','subAccounts'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, Vender $vender)
    {
        if(\Auth::user()->can('edit vender'))
        {
            // Concatenate name fields before validation if they exist
            if($request->has('first_name') || $request->has('last_name')) {
                $fullName = trim(
                    ($request->name_prefix ? $request->name_prefix . ' ' : '') .
                    ($request->first_name ? $request->first_name . ' ' : '') .
                    ($request->middle_initial ? $request->middle_initial . ' ' : '') .
                    ($request->last_name ? $request->last_name : '')
                );

                $request->merge([
                    'name' => $fullName,
                    'contact' => $request->main_phone ? $request->main_phone : $request->contact
                ]);
            }

            $rules = [
                'account_id' => 'required',
            ];

            // Add validation for name fields if they are present
            if($request->has('first_name') || $request->has('last_name')) {
                $rules['first_name'] = 'required';
                $rules['last_name'] = 'required';
                $rules['main_phone'] = 'required|regex:/^([0-9\s\-\+\(\)]*)$/';
            } else {
                $rules['name'] = 'required';
                $rules['contact'] = 'required|regex:/^([0-9\s\-\+\(\)]*)$/';
            }

            $validator = \Validator::make($request->all(), $rules);

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->route('vender.index')->with('error', $messages->first());
            }

            $vender->name             = $request->name;
            $vender->contact          = $request->contact;
            $vender->tax_number      = $request->tax_number;
            $vender->created_by       = \Auth::user()->creatorId();

            // Update new vendor fields if present
            if($request->has('company_name')) {
                $vender->company_name     = $request->company_name;
                $vender->name_prefix      = $request->name_prefix;
                $vender->first_name       = $request->first_name;
                $vender->middle_initial   = $request->middle_initial;
                $vender->last_name        = $request->last_name;
                $vender->job_title        = $request->job_title;
                $vender->main_phone_type  = $request->main_phone_type;
                $vender->main_phone       = $request->main_phone;
                $vender->work_phone_type  = $request->work_phone_type;
                $vender->work_phone       = $request->work_phone;
                $vender->main_email_type  = $request->main_email_type;
                $vender->cc_email_type    = $request->cc_email_type;
                $vender->cc_email         = $request->cc_email;
                $vender->website_type     = $request->website_type;
                $vender->website          = $request->website;
                $vender->other1_type      = $request->other1_type;
                $vender->other1           = $request->other1;
            }

            $vender->billing_name     = $request->billing_name;
            $vender->billing_country  = $request->billing_country;
            $vender->billing_state    = $request->billing_state;
            $vender->billing_city     = $request->billing_city;
            $vender->billing_phone    = $request->billing_phone;
            $vender->billing_zip      = $request->billing_zip;
            $vender->billing_address  = $request->billing_address;
            $vender->shipping_name    = $request->shipping_name;
            $vender->shipping_country = $request->shipping_country;
            $vender->shipping_state   = $request->shipping_state;
            $vender->shipping_city    = $request->shipping_city;
            $vender->shipping_phone   = $request->shipping_phone;
            $vender->shipping_zip     = $request->shipping_zip;
            $vender->shipping_address = $request->shipping_address;
            $vender->account_id       = $request->account_id;
            $vender->save();
            CustomField::saveData($vender, $request->customField);

            return redirect()->route('vender.index')->with('success', __('Vendor successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(Vender $vender)
    {
        if(\Auth::user()->can('delete vender'))
        {
            if($vender->created_by == \Auth::user()->creatorId())
            {
                $data= Purchase::where('vender_id',$vender->id)->get();
                if($data->isEmpty()){
                    $vender->delete();
                }else{
                    return redirect()->back()->with('error', __('Data exist against this vendor'));
                }

                return redirect()->route('vender.index')->with('success', __('Vendor successfully deleted.'));
            }
            else
            {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function venderNumber()
    {
        if(\Auth::user()->type == ('company')){
        $latest = Vender::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        }else{
        $latest = Vender::where('owned_by', '=', \Auth::user()->ownedId())->latest()->first();
        }
        if(!$latest)
        {
            return 1;
        }

        return $latest->vender_id + 1;
    }

    public function venderLogout(Request $request)
    {
        \Auth::guard('vender')->logout();

        $request->session()->invalidate();

        return redirect()->route('vender.login');
    }

    public function payment(Request $request)
    {

        if(\Auth::user()->can('manage vender payment'))
        {
            $category = [
                'Bill' => 'Bill',
                'Deposit' => 'Deposit',
                'Sales' => 'Sales',
            ];

            $query = Transaction::where('user_id', \Auth::user()->id)->where('created_by', \Auth::user()->creatorId())->where('user_type', 'Vender')->where('type', 'Payment');
            if(!empty($request->date))
            {
                $date_range = explode(' - ', $request->date);
                $query->whereBetween('date', $date_range);
            }

            if(!empty($request->category))
            {
                $query->where('category', '=', $request->category);
            }
            $payments = $query->get();

            return view('vender.payment', compact('payments', 'category'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function transaction(Request $request)
    {

        if(\Auth::user()->can('manage vender transaction'))
        {

            $category = [
                'Bill' => 'Bill',
                'Deposit' => 'Deposit',
                'Sales' => 'Sales',
            ];

            $query = Transaction::where('user_id', \Auth::user()->id)->where('user_type', 'Vender');

            if(!empty($request->date))
            {
                $date_range = explode(' - ', $request->date);
                $query->whereBetween('date', $date_range);
            }

            if(!empty($request->category))
            {
                $query->where('category', '=', $request->category);
            }
            $transactions = $query->get();

            return view('vender.transaction', compact('transactions', 'category'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function profile()
    {
        $userDetail              = \Auth::user();
        $userDetail->customField = CustomField::getData($userDetail, 'vendor');
        $customFields            = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'vendor')->get();

        return view('vender.profile', compact('userDetail', 'customFields'));
    }

    public function editprofile(Request $request)
    {

        $userDetail = \Auth::user();
        $user       = Vender::findOrFail($userDetail['id']);
        $this->validate(
            $request, [
                        'name' => 'required|max:120',
                        'contact' => 'required',
                        'email' => 'required|email|unique:users,email,' . $userDetail['id'],
                    ]
        );
        if($request->hasFile('profile'))
        {
            $filenameWithExt = $request->file('profile')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('profile')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $dir        = storage_path('uploads/avatar/');
            $image_path = $dir . $userDetail['avatar'];

            if(File::exists($image_path))
            {
                File::delete($image_path);
            }

            if(!file_exists($dir))
            {
                mkdir($dir, 0777, true);
            }

            $path = $request->file('profile')->storeAs('uploads/avatar/', $fileNameToStore);

        }

        if(!empty($request->profile))
        {
            $user['avatar'] = $fileNameToStore;
        }
        $user['name']    = $request['name'];
        $user['email']   = $request['email'];
        $user['contact'] = $request['contact'];
        $user->save();
        CustomField::saveData($user, $request->customField);

        return redirect()->back()->with(
            'success', 'Profile successfully updated.'
        );
    }

    public function editBilling(Request $request)
    {

        $userDetail = \Auth::user();
        $user       = Vender::findOrFail($userDetail['id']);
        $this->validate(
            $request, [
                        'billing_name' => 'required',
                        'billing_country' => 'required',
                        'billing_state' => 'required',
                        'billing_city' => 'required',
                        'billing_phone' => 'required',
                        'billing_zip' => 'required',
                        'billing_address' => 'required',
                    ]
        );
        $input = $request->all();
        $user->fill($input)->save();

        return redirect()->back()->with(
            'success', 'Profile successfully updated.'
        );
    }

    public function editShipping(Request $request)
    {
        $userDetail = \Auth::user();
        $user       = Vender::findOrFail($userDetail['id']);
        $this->validate(
            $request, [
                        'shipping_name' => 'required',
                        'shipping_country' => 'required',
                        'shipping_state' => 'required',
                        'shipping_city' => 'required',
                        'shipping_phone' => 'required',
                        'shipping_zip' => 'required',
                        'shipping_address' => 'required',
                    ]
        );
        $input = $request->all();
        $user->fill($input)->save();

        return redirect()->back()->with(
            'success', 'Profile successfully updated.'
        );
    }

    public function changeLanquage($lang)
    {

        $user       = Auth::user();
        $user->lang = $lang;
        $user->save();

        return redirect()->back()->with('success', __('Language successfully change.'));

    }

    public function export()
    {
        $name = 'vendor_' . date('Y-m-d i:h:s');
        $data = Excel::download(new VenderExport(), $name . '.xlsx');

        return $data;
    }

    public function importFile()
    {
        return view('vender.import');
    }

    public function import(Request $request)
    {

        $rules = [
            'file' => 'required|mimes:csv,txt',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $vendors = (new VenderImport())->toArray(request()->file('file'))[0];

        $totalCustomer = count($vendors) - 1;
        $errorArray    = [];
        for($i = 1; $i <= count($vendors) - 1; $i++)
        {
            $vendor = $vendors[$i];

            $vendorByEmail = Vender::where('email', $vendor[2])->first();

            if(!empty($vendorByEmail))
            {
                $vendorData = $vendorByEmail;
            }
            else
            {
                $vendorData            = new Vender();
                $vendorData->vender_id = $this->venderNumber();
            }

            $vendorData->vender_id          =$vendor[0];
            $vendorData->name               = $vendor[1];
            $vendorData->email              = $vendor[2];
            $vendorData->contact            = $vendor[3];
            $vendorData->avatar             = $vendor[4];
            $vendorData->billing_name       = $vendor[5];
            $vendorData->billing_country    = $vendor[6];
            $vendorData->billing_state      = $vendor[7];
            $vendorData->billing_city       = $vendor[8];
            $vendorData->billing_phone      = $vendor[9];
            $vendorData->billing_zip        = $vendor[10];
            $vendorData->billing_address    = $vendor[11];
            $vendorData->shipping_name      = $vendor[12];
            $vendorData->shipping_country   = $vendor[13];
            $vendorData->shipping_state     = $vendor[14];
            $vendorData->shipping_city      = $vendor[15];
            $vendorData->shipping_phone     = $vendor[16];
            $vendorData->shipping_zip       = $vendor[17];
            $vendorData->shipping_address   = $vendor[18];
            $vendorData->owned_by           = \Auth::user()->ownedId();
            $vendorData->created_by         = \Auth::user()->creatorId();

            if(empty($vendorData))
            {
                $errorArray[] = $vendorData;
            }
            else
            {
                $vendorData->save();
            }
        }

        $errorRecord = [];
        if(empty($errorArray))
        {
            $data['status'] = 'success';
            $data['msg']    = __('Record successfully imported');
        }
        else
        {
            $data['status'] = 'error';
            $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalCustomer . ' ' . 'record');


            foreach($errorArray as $errorData)
            {

                $errorRecord[] = implode(',', $errorData);

            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }
    public function getVendors(Request $request)
    {
        $vendors = Vender::where('created_by', \Auth::user()->creatorId())
            ->orderBy('company_name')
            ->orderBy('name')
            ->get(['id', 'company_name', 'name'])
            ->map(function ($vendor) {
                return [
                    'id' => $vendor->id,
                    'name' => $vendor->display_name,
                    'company_name' => $vendor->company_name,
                    'vendor_name' => $vendor->name,
                ];
            })
            ->values();

        return response()->json($vendors);
    }
}
