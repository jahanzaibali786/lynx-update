<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Chair;
use App\Models\ChartOfAccount;
use Auth;
use App\Models\User;
use App\Models\Space;
use App\Models\Client;
use App\Models\Company;
use App\Models\Project;
use App\Models\Utility;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Roomassign;
use App\Models\ContractType;
use Illuminate\Http\Request;
use App\Models\ContractNotes;
use App\Models\ContractComment;
use App\Models\UserDefualtView;
use PhpParser\Node\Stmt\TryCatch;
use App\Models\ContractSpaceHoure;
use Illuminate\Support\Facades\DB;
use App\Models\Contract_attachment;
use App\Models\ProductService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;


class ContractController extends Controller
{

    public function index(Request $request)
    {
        if (\Auth::user()->can('manage contract')) {

            if (\Auth::user()->type == 'company') {
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
                $company = Company::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');       
                $company->prepend('Select Company', '');
                $branches->prepend('Select Branch', ''); 
                $query = Contract::where('created_by', '=', \Auth::user()->creatorId());

                $contracts   = Contract::where('created_by', '=', \Auth::user()->creatorId())->get();
                $curr_month  = Contract::where('created_by', '=', \Auth::user()->creatorId())->whereMonth('start_date', '=', date('m'))->get();
                $curr_week   = Contract::where('created_by', '=', \Auth::user()->creatorId())->whereBetween(
                    'start_date',
                    [
                        \Carbon\Carbon::now()->startOfWeek(),
                        \Carbon\Carbon::now()->endOfWeek(),
                    ]
                )->get();
                $last_30days = Contract::where('created_by', '=', \Auth::user()->creatorId())->whereDate('start_date', '>', \Carbon\Carbon::now()->subDays(30))->get();

                // Contracts Summary
                $cnt_contract                = [];
                $cnt_contract['total']       = \App\Models\Contract::getContractSummary($contracts);
                $cnt_contract['this_month']  = \App\Models\Contract::getContractSummary($curr_month);
                $cnt_contract['this_week']   = \App\Models\Contract::getContractSummary($curr_week);
                $cnt_contract['last_30days'] = \App\Models\Contract::getContractSummary($last_30days);

                // return view('contract.index', compact('contracts', 'cnt_contract'));
            } else {
                $contracts   = Contract::where('owned_by', '=', \Auth::user()->ownedId())->get();
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $company = Company::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $company->prepend('Select Company', '');
                $query = Contract::where('owned_by', '=', \Auth::user()->ownedId());   
                $curr_month  = Contract::where('owned_by', '=', \Auth::user()->ownedId())->whereMonth('start_date', '=', date('m'))->get();
                $curr_week   = Contract::where('owned_by', '=', \Auth::user()->ownedId())->whereBetween(
                    'start_date',
                    [
                        \Carbon\Carbon::now()->startOfWeek(),
                        \Carbon\Carbon::now()->endOfWeek(),
                    ]
                )->get();
                $last_30days = Contract::where('owned_by', '=', \Auth::user()->ownedId())->whereDate('start_date', '>', \Carbon\Carbon::now()->subDays(30))->get();

                // Contracts Summary
                $cnt_contract                = [];
                $cnt_contract['total']       = \App\Models\Contract::getContractSummary($contracts);
                $cnt_contract['this_month']  = \App\Models\Contract::getContractSummary($curr_month);
                $cnt_contract['this_week']   = \App\Models\Contract::getContractSummary($curr_week);
                $cnt_contract['last_30days'] = \App\Models\Contract::getContractSummary($last_30days);

                // return view('contract.index', compact('contracts', 'cnt_contract'));
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
                $company = Company::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
                $company->prepend('Select Company', '');
            }
            
            if (!empty($request->company)) {
                $query->where('company_id', '=', $request->company);
            }
            if (count(explode('to', $request->issue_date)) > 1) {
                $date_range = explode(' to ', $request->issue_date);
                $query->whereBetween('start_date', $date_range);
            } elseif (!empty($request->issue_date)) {
                $date_range = [$request->issue_date, $request->issue_date];
                $query->whereBetween('start_date', $date_range);
            }
            if (!empty($request->status)) {
                if($request->status == 'open'){
                    $query->where('close_date', '=', null);
                }else{
                    $query->where('close_date', '!=', null);
                }
            }
            $contracts = $query->get();

            return view('contract.index', compact('contracts','cnt_contract','company','branches'));
            // return view('contract.index', compact('contracts'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    
    public function branchCompany(Request $request)
    {
        $company = Company::where('owned_by', '=', $request->id)->get();
        // dd($request->id);
        if($company == null){
            $result = [
                'status' => 'error',
                'company' => 'null',   
            ];
            return response()->json($result);
        }else{
            $result = [
                'status' => 'success',
                'company' => $company,
            ];
            return response()->json($result);
        }
    }

    public function create()
    {
        $type ='office';
        // $clients       = User::where('type', 'client')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        // $project       = Project::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('project_name', 'id');
        if (\Auth::user()->type == 'company') {
            $company = Company::where('created_by', '=', \Auth::user()->creatorId())->pluck('name', 'id');
            $spaces       = Space::with('type')->where('created_by', '=', \Auth::user()->creatorId())->get();
            // $space->prepend(__('Select Space'),0);
            $ismeeting   = Space::with('type')->where('created_by', '=', \Auth::user()->creatorId())->where('meeting', 'yes')->get();
            
            $contractTypes = ContractType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $services   = ProductService::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'services')->first();
            $security   = ProductService::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'security services')->first();
        } else {
            $company = Company::where('owned_by', '=', \Auth::user()->ownedId())->pluck('name', 'id');
            // dd($company);
            $spaces       = Space::with('type')->where('owned_by', '=', \Auth::user()->ownedId())->get();
            $ismeeting   = Space::with('type')->where('owned_by', '=', \Auth::user()->ownedId())->where('meeting', 'yes')->get();
            // $space->prepend(__('Select Space'),0);
            
            $contractTypes = ContractType::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $services   = ProductService::where('owned_by', '=', \Auth::user()->ownedId())->where('type', 'services')->first();
            $security   = ProductService::where('owned_by', '=', \Auth::user()->ownedId())->where('type', 'security services')->first();
        }
       

        return view('contract.create', compact('contractTypes', 'spaces', 'ismeeting', 'company','services','security','type'));
    }

    public function createvirtualoffice()
    {
        $type ='virtual';
        // $clients       = User::where('type', 'client')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        // $project       = Project::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('project_name', 'id');
       
        if (\Auth::user()->type == 'company') {
            $company = Company::where('created_by', '=', \Auth::user()->creatorId())->pluck('name', 'id');
            $spaces       = Space::join('space_types', 'spaces.type_id', '=', 'space_types.id')->where('space_types.name', 'Virtual Office')->where('spaces.created_by', '=', \Auth::user()->creatorId())->select('spaces.id','spaces.name','space_types.name as space_types_name')->get();
            // $space->prepend(__('Select Space'),0);
            $ismeeting   = Space::with('type')->where('created_by', '=', \Auth::user()->creatorId())->where('meeting', 'yes')->get();
            $contractTypes = ContractType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $services   = ProductService::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'services')->first();
            $security   = ProductService::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'security services')->first();
        } else {
            $company = Company::where('owned_by', '=', \Auth::user()->ownedId())->pluck('name', 'id');
            $spaces       = Space::join('space_types', 'spaces.type_id', '=', 'space_types.id')->where('space_types.name', 'Virtual Office')->where('spaces.owned_by', '=', \Auth::user()->ownedId())->select('spaces.id','spaces.name','space_types.name as space_types_name')->get();
            // $space->prepend(__('Select Space'),0);
            $ismeeting   = Space::with('type')->where('owned_by', '=', \Auth::user()->ownedId())->where('meeting', 'yes')->get();
            $contractTypes = ContractType::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $services   = ProductService::where('owned_by', '=', \Auth::user()->ownedId())->where('type', 'services')->first();
            $security   = ProductService::where('owned_by', '=', \Auth::user()->ownedId())->where('type', 'security services')->first();
        }

        return view('contract.create', compact('contractTypes', 'spaces', 'ismeeting', 'company','services','security','type'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        if (\Auth::user()->can('create contract')) {
            if($request->create_type == 'virtual'){
                $rules = [
                    'subject' => 'required',
                    'type' => 'required',
                    'services_charges' => 'required',
                    'value' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'space' => 'required',
                    'room_hours' => 'required',
                    'hourly_rate' => 'required',
                    'security_deposit_id' => 'required',
                    'security_deposit_price' => 'required',
                ];
            }else{
                $rules = [
                    'subject' => 'required',
                    'services_charges' => 'required',
                    'type' => 'required',
                    'value' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'space' => 'required',
                    'chair' => 'required',
                    'room_hours' => 'required',
                    'hourly_rate' => 'required',
                    'security_deposit_id' => 'required',
                    'security_deposit_price' => 'required',                    
                ];
            }


            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('contract.index')->with('error', $messages->first());
            }
            DB::beginTransaction();
            try {
                $company  = new Company();
                if ($request->has('new') && $request->input('new') == '1') {
                    $company->name      = $request->newcompany;
                    $company->owned_by  = \Auth::user()->ownedId();
                    $company->created_by  = \Auth::user()->creatorId();
                    $company->save();
                } else {
                    $company  = Company::where('id', $request->company)->first();
                }

                $contract              = new Contract();
                $contract->contract_id     = $this->contractNumber();
                $contract->company_id      = $company->id;
                $contract->subject     = $request->subject;
                // $contract->project_id  =$request->project_id;
                $contract->type        = $request->type;
                $contract->value       = $request->value;
                $contract->start_date  = $request->start_date;
                $contract->end_date    = $request->end_date;
                $contract->description = $request->description;
                $contract->service_id = $request->services_id;
                $contract->service_price = $request->services_charges;
                $contract->security_deposit_id = $request->security_deposit_id;
                $contract->security_deposit_price = $request->security_deposit_price;
                $contract->owned_by  = \Auth::user()->ownedId();
                $contract->created_by  = \Auth::user()->creatorId();
                $contract->save();

                if ($request->has('new') && $request->input('new') == '1') {
                    if (\Auth::user()->type == 'company') {

                        $latest = Customer::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
                    } else {
                        $latest = Customer::where('owned_by', '=', \Auth::user()->ownedId())->latest()->first();
                    }
                    if (!$latest) {
                        $customer_id = 1;
                    }else{
                        $customer_id = $latest->customer_id + 1;
                    }

                    $default_language           = DB::table('settings')->select('value')->where('name', 'default_language')->first();
                    
                    $customers                  = new Customer();
                    $customers->contact         = $request->phone_no;
                    $customers->customer_id     = $customer_id;
                    $customers->email           = $request->email;
                    $customers->ntn             = $request->ntn;
                    $customers->company_id      = $company->id;
                    $customers->owned_by        = \Auth::user()->ownedId();
                    $customers->created_by      = \Auth::user()->creatorId();
                    $customers->name            = $company->name . ' customer';
                    $customers->lang = !empty($default_language) ? $default_language->value : '';
                    $customers->billing_name    = $company->name . ' customer';
                    $customers->billing_address = $request->email;
                    $customers->billing_phone   =  $request->phone_no;
    
                    $customers->shipping_name    = \Auth::user()->name;
                    $customers->shipping_address =  \Auth::user()->email;
                    $customers->save();
                }
                
                if($request->create_type == 'virtual'){
                        $assign_room   = new Roomassign();
                        $assign_room->company_id     = $company->id;
                        $assign_room->contract_id     = $contract->id;
                        $assign_room->space_id        = $request->space;
                        $assign_room->chair_id        = 0;
                        $assign_room->save();
                }else{
                    for ($i = 0; $i < count($request->chair); $i++) {
                        $assign_room   = new Roomassign();
                        $assign_room->company_id     = $company->id;
                        $assign_room->contract_id     = $contract->id;
                        $assign_room->space_id        = $request->space;
                        $assign_room->chair_id        = $request->chair[$i];
                        $assign_room->save();
                    }
                }

                for ($j = 0; $j < count($request->room_hours_ids); $j++) {
                    $contract_space_hour = new ContractSpaceHoure;
                    $contract_space_hour->contract_id   = $contract->id;
                    $contract_space_hour->space_id      = $request->room_hours_ids[$j];
                    $contract_space_hour->company_id    = $company->id;
                    $contract_space_hour->assign_hour   = $request->room_hours[$j];
                    $contract_space_hour->hourly_rate   = $request->hourly_rate[$j];
                    $contract_space_hour->save();
                }


                $contract->created_by  = \Auth::user()->creatorId();
                $contract->save();

                //Send Email
                $setings = Utility::settings();
                if($setings['new_contract'] == 1) {

                    $customer = \App\Models\Customer::where('company_id', '=', $contract->company_id)->first();
                    $contractArr = [
                        'contract_subject' => $request->subject,
                        'contract_client' => $customer->name,
                        'contract_value' => \Auth::user()->priceFormat($request->value),
                        'contract_start_date' => \Auth::user()->dateFormat($request->start_date),
                        'contract_end_date' => \Auth::user()->dateFormat($request->end_date),
                        'contract_description' => $request->description,
                    ];

                    // Send Email
                    $resp = Utility::sendEmailTemplate('new_contract', [$customer->id => $customer->email], $contractArr);

                }

                //For Notification
                $setting  = Utility::settings(\Auth::user()->creatorId());
                $customer = \App\Models\Customer::where('company_id', '=', $contract->company_id)->first();
                $contractNotificationArr = [
                    'contract_subject' => $request->subject,
                    'contract_client' => $customer->name,
                    'contract_value' => \Auth::user()->priceFormat($request->value),
                    'contract_start_date' => \Auth::user()->dateFormat($request->start_date),
                    'contract_end_date' =>\Auth::user()->dateFormat($request->end_date),
                    'user_name' => \Auth::user()->name,
                ];
                //Slack Notification
                // if(isset($setting['contract_notification']) && $setting['contract_notification'] ==1)
                // {
                //     Utility::send_slack_msg('new_contract', $contractNotificationArr);
                // }
                // //Telegram Notification
                // if(isset($setting['telegram_contract_notification']) && $setting['telegram_contract_notification'] ==1)
                // {
                //     Utility::send_telegram_msg('new_contract', $contractNotificationArr);
                // }

                //webhook
                // $module = 'New Contract';
                // $webhook =  Utility::webhookSetting($module);
                // if ($webhook) {
                //     $parameter = json_encode($contract);
                //     $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);

                //     if ($status == true) {
                //         return redirect()->back()->with('success', __('Contract successfully created!') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
                //     } else {
                //         return redirect()->back()->with('error', __('Webhook call failed.'));
                //     }
                // }
                DB::commit();

                return redirect()->back()->with('success', __('Contract successfully created!') . ((!empty($resp) && $resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
            } catch (\Exception $e) {
                dd($e);
                DB::rollback();
                return redirect()->back()->with('error', $e);
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function contractNumber()
    {
        // if(\Auth::user()->type == ('company')){
            // $latest = Contract::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        // }else{
            $latest = Contract::where('owned_by', '=', \Auth::user()->ownedId())->latest()->first();
        // }
        if(!$latest)
        {
            return 1;
        }

        return $latest->contract_id + 1;
    }

    public function show($ids)
    {
        $id      = Crypt::decrypt($ids);
        if (\Auth::user()->can('show contract')) {
            $contract = Contract::find($id);
            if ($contract->created_by == \Auth::user()->creatorId() || $contract->owned_by == \Auth::user()->ownedId()) {
                $client   = $contract->client;
                return view('contract.show', compact('contract', 'client'));
            } else {
                return redirect()->back()->with('error', __('Permission Denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function edit(Contract $contract)
    {
        $space_type = Roomassign::where('contract_id',$contract->id)->first();

        // $clients       = User::where('type', 'client')->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
        // $project       = Project::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('project_name', 'id');
        if (\Auth::user()->type == 'company') {
            if($space_type->chair_id == 0){
                $type ='virtual';
                $spaces       = Space::join('space_types', 'spaces.type_id', '=', 'space_types.id')->where('space_types.name', 'Virtual Office')->where('spaces.created_by', '=', \Auth::user()->creatorId())->select('spaces.id','spaces.name','space_types.name as space_types_name')->get();
            }else{
                $type ='office';
                $spaces       = Space::with('type')->where('created_by', '=', \Auth::user()->creatorId())->get();
            }
            $contractTypes = ContractType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $services   = ProductService::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'services')->first();
            $security   = ProductService::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'security services')->first();
            $ismeeting   = Space::with('type')->where('created_by', '=', \Auth::user()->creatorId())->where('meeting', 'yes')->get();
        } else {
            if($space_type->chair_id == 0){
                $type ='virtual';
                $spaces  = Space::join('space_types', 'spaces.type_id', '=', 'space_types.id')->where('space_types.name', 'Virtual Office')->where('spaces.owned_by', '=', \Auth::user()->ownedId())->select('spaces.id','spaces.name','space_types.name as space_types_name')->get();
            }else{
                $type ='office';
                $spaces      = Space::with('type')->where('owned_by', '=', \Auth::user()->ownedId())->get();
            }
            $contractTypes = ContractType::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $services   = ProductService::where('owned_by', '=', \Auth::user()->ownedId())->where('type', 'services')->first();
            $security   = ProductService::where('owned_by', '=', \Auth::user()->ownedId())->where('type', 'security services')->first();
            $ismeeting   = Space::with('type')->where('owned_by', '=', \Auth::user()->ownedId())->where('meeting', 'yes')->get();

        }
        $roomassign = Roomassign::where('contract_id',$contract->id)->get();
            $chairs = Chair::where('space_id',@$roomassign[0]->space_id)->get();
            $chairget =$roomassign->pluck('chair_id')->toArray();
            $chairused =Roomassign::where('space_id',@$roomassign[0]->space_id)->pluck('chair_id')->toArray();
        return view('contract.edit', compact('contractTypes','chairs', 'chairused','chairget','spaces', 'contract', 'ismeeting','roomassign','type','services','security'));
    }

    public function update(Request $request, Contract $contract)
    {
        if (\Auth::user()->can('edit contract')) {
            if($request->create_type == 'virtual'){
                $rules = [
                    'subject' => 'required',
                    'type' => 'required',
                    'services_charges' => 'required',
                    'value' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'space' => 'required',
                    'room_hours' => 'required',
                    'hourly_rate' => 'required',
                ];
            }else{
                $rules = [
                    'subject' => 'required',
                    'services_charges' => 'required',
                    // 'email' => 'required',
                    // 'phone_no' => 'required',
                    'type' => 'required',
                    'value' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'space' => 'required',
                    'chair' => 'required',
                    'room_hours' => 'required',
                    'hourly_rate' => 'required',
                ];
            }

            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('contract.index')->with('error', $messages->first());
            }
            DB::beginTransaction();
            try {
                    // $contract              = new Contract();
                    // // $contract = Contract::findOrFail($contract->id);
                    // $contract->company_id      = $request->company_id;
                    $contract->subject     = $request->subject;
                    // $contract->project_id  =$request->project_id;
                    $contract->type        = $request->type;
                    $contract->value       = $request->value;
                    $contract->start_date  = $request->start_date;
                    $contract->end_date    = $request->end_date;
                    $contract->service_price    = $request->services_charges;
                    $contract->security_deposit_id = $request->security_deposit_id;
                    $contract->security_deposit_price = $request->security_deposit_price;
                    $contract->description = $request->description;
                    // $contract->owned_by  = \Auth::user()->ownedId();
                    // $contract->created_by  = \Auth::user()->creatorId();
                    $contract->save();
            
                    if($request->create_type == 'office'){
                        // Get the original chair IDs assigned to the contract
                        $originalChairIds = Roomassign::where('contract_id', $contract->id)->pluck('chair_id')->toArray();
                        // Iterate over the submitted chair IDs
                        foreach ($request->chair as $chairId) {
                            $existingRoomAssign = Roomassign::where('contract_id', $contract->id)
                                ->where('chair_id', $chairId)
                                ->first();

                            if ($existingRoomAssign) {
                                // Updating existing record
                            } else {
                                // Creating new record
                                Roomassign::create([
                                    'company_id' => $contract->company_id,
                                    'contract_id' => $contract->id,
                                    'space_id' => $request->space,
                                    'chair_id' => $chairId,
                                ]);
                            }
                        }

                        // Find and delete records associated with chairs that were removed
                        $removedChairIds = array_diff($originalChairIds, $request->chair);
                        Roomassign::where('contract_id', $contract->id)->whereIn('chair_id', $removedChairIds)->delete();                
                    }

                    for ($j = 0; $j < count($request->room_hours_ids); $j++) {
                        $existingContractSpaceHour = ContractSpaceHoure::where('contract_id', $contract->id)
                            ->where('space_id', $request->room_hours_ids[$j])
                            ->first();
                        if ($existingContractSpaceHour) {
                            // dd($existingContractSpaceHour);
                            $existingContractSpaceHour->assign_hour   = $request->room_hours[$j];
                            $existingContractSpaceHour->hourly_rate   = $request->hourly_rate[$j];
                            $existingContractSpaceHour->save();
                        } else {
                            // $contract_space_hour = new ContractSpaceHoure;
                            // $contract_space_hour->contract_id = $contract->id;
                            // $contract_space_hour->space_id = $request->room_hours_ids[$j];
                            // $contract_space_hour->company_id = $request->company_id;
                            // $contract_space_hour->assign_hour   = $request->room_hours[$j];
                            // $contract_space_hour->hourly_rate   = $request->hourly_rate[$j];
                            // $contract_space_hour->save();
                        }
                    }

                    DB::commit();

                    return redirect()->route('contract.index')->with('success', __('Contract successfully updated.'));
                } catch (\Exception $e) {
                dd($e);

                    DB::rollback();
                    return redirect()->back()->with('error', $e);
                }

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(Contract $contract)
    {
        if (\Auth::user()->can('delete contract')) {
            $contract->delete();

            return redirect()->route('contract.index')->with('success', __('Contract successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function description($id)
    {
        $contract = Contract::find($id);

        return view('contract.description', compact('contract'));
    }

    public function grid()
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client' || \Auth::user()->type == 'branch') {
            if (\Auth::user()->type == 'company') {
                $contracts = Contract::where('created_by', '=', \Auth::user()->creatorId())->get();
            } elseif (\Auth::user()->type == 'client') {
                $contracts = Contract::where('client_name', '=', \Auth::user()->id)->get();
            }else{
                $contracts = Contract::where('owned_by', '=', \Auth::user()->ownedId())->get();
            }

            /*   $defualtView         = new UserDefualtView();
            $defualtView->route  = \Request::route()->getName();
            $defualtView->module = 'contract';
            $defualtView->view   = 'grid';
            User::userDefualtView($defualtView);*/
            return view('contract.grid', compact('contracts'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function fileUpload($id, Request $request)
    {


        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'client' || \Auth::user()->type == 'branch') {
            $contract = Contract::find($id);
            $request->validate(['file' => 'required']);

            //storage limit
            $image_size = $request->file('file')->getSize();
            $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);

            if ($result == 1) {
                $files = $id . $request->file->getClientOriginalName();
                $file   = Contract_attachment::create(
                    [
                        'contract_id' => $request->contract_id,
                        'user_id' => \Auth::user()->id,
                        'files' => $files,
                    ]
                );
                $request->file->storeAs('contract_attechment', $files);

                $dir = 'contract_attechment/';
                $files = $request->file->getClientOriginalName();
                $path = Utility::upload_file($request, 'file', $files, $dir, []);
                if ($path['flag'] == 1) {
                    $file = $path['url'];
                } else {

                    return redirect()->back()->with('error', __($path['msg']));
                }
                $return               = [];
                $return['is_success'] = true;
                $return['download']   = route(
                    'contracts.file.download',
                    [
                        $contract->id,
                        $file->id,
                    ]
                );

                $return['delete']     = route(
                    'contracts.file.delete',
                    [
                        $contract->id,
                        $file->id,
                    ]
                );
            } else {

                $return               = [];
                $return['is_success'] = true;
                $return['status'] = 1;
                $return['success_msg'] = ((isset($result) && $result != 1) ? '<br> <span class="text-danger">' . $result . '</span>' : '');
            }



            return response()->json($return);
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }
    public function fileDownload($id, $file_id)
    {

        $contract        = Contract::find($id);
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'branch') {
            $file = Contract_attachment::find($file_id);
            if ($file) {
                $file_path = storage_path('contract_attechment/' . $file->files);


                return \Response::download(
                    $file_path,
                    $file->files,
                    [
                        'Content-Length: ' . filesize($file_path),
                    ]
                );
            } else {
                return redirect()->back()->with('error', __('File is not exist.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function fileDelete($id, $file_id)
    {
        $contract = Contract::find($id);

        $file =  Contract_attachment::find($file_id);
        if ($file) {
            $path = storage_path('contract_attechment/' . $file->files);
            if (file_exists($path)) {
                \File::delete($path);
            }
            $file->delete();

            return redirect()->back()->with('success', __('contract file successfully deleted.'));
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('File is not exist.'),
                ],
                200
            );
        }
    }

    public function contract_status_edit(Request $request, $id)
    {
        // dd($request->all());
        $contract = Contract::find($id);
        $contract->status   = $request->status;
        $contract->save();
    }
    public function commentStore(Request $request, $id)
    {
        $contract              = new ContractComment();
        $contract->comment     = $request->comment;
        $contract->contract_id = $request->id;
        $contract->user_id     = \Auth::user()->id;
        $contract->save();
        // dd($contract);


        return redirect()->back()->with('success', __('comments successfully created!') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''))->with('status', 'comments');
    }
    //    public function contract_descriptionStore($id, Request $request)
    //    {
    //        if(\Auth::user()->type == 'company')
    //        {
    //            $contract        =Contract::find($id);
    //            $contract->contract_description = $request->contract_description;
    //            $contract->save();
    //            return redirect()->back()->with('success', __('Contact Description successfully saved.'));
    //
    //        }
    //        else
    //        {
    //            return redirect()->back()->with('error', __('Permission denied'));
    //
    //        }
    //    }

    public function contract_descriptionStore($id, Request $request)
    {
        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'branch') {
            $contract        = Contract::find($id);
            if ($contract->created_by == \Auth::user()->creatorId()) {
                $contract->contract_description = $request->contract_description;
                $contract->save();

                return response()->json(
                    [
                        'is_success' => true,
                        'success' => __('Contract description successfully saved!'),
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'is_success' => false,
                        'error' => __('Permission Denied.'),
                    ],
                    401
                );
            }
        } else {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('Permission Denied.'),
                ],
                401
            );
        }
    }

    public function commentDestroy($id)
    {
        $contract = ContractComment::find($id);

        $contract->delete();

        return redirect()->back()->with('success', __('Comment successfully deleted!'));
    }
    public function noteStore($id, Request $request)
    {
        $contract              = Contract::find($id);
        $notes                 = new ContractNotes();
        $notes->contract_id    = $contract->id;
        $notes->notes           = $request->notes;
        $notes->user_id        = \Auth::user()->id;
        $notes->save();
        return redirect()->back()->with('success', __('Note successfully saved.'));
    }
    public function noteDestroy($id)
    {
        $contract = ContractNotes::find($id);
        $contract->delete();

        return redirect()->back()->with('success', __('Note successfully deleted!'));
    }
    public function clientwiseproject($id)

    {
        $projects = Project::where('client_id', $id)->get();


        $users = [];
        foreach ($projects as $key => $value) {
            $users[] = [
                'id' => $value->id,
                'name' => $value->project_name,
            ];
        }
        // dd($users);

        return \Response::json($users);
    }

    public function printContract($id)
    {
        $contract  = Contract::findOrFail($id);
        $settings = Utility::settings();

        // $client   = $contract->clients->first();
        //Set your logo
        $logo         = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo');
        $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));


        if ($contract) {
            $color      = '#' . $settings['invoice_color'];
            $font_color = Utility::getFontColor($color);

            return view('contract.preview', compact('contract', 'color', 'img', 'settings', 'font_color'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function copycontract($id)
    {
        $contract = Contract::find($id);        
        $space_type = Roomassign::where('contract_id',$contract->id)->first();
        if (\Auth::user()->type == 'company') {
            if($space_type->chair_id == 0){
                $type ='virtual';
                $spaces       = Space::join('space_types', 'spaces.type_id', '=', 'space_types.id')->where('space_types.name', 'Virtual Office')->where('spaces.created_by', '=', \Auth::user()->creatorId())->select('spaces.id','spaces.name','space_types.name as space_types_name')->get();
            }else{
                $type ='office';
                $spaces       = Space::with('type')->where('created_by', '=', \Auth::user()->creatorId())->get();
            }
            $company = Company::where('created_by', '=', \Auth::user()->creatorId())->pluck('name', 'id');
            $contractTypes = ContractType::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $services   = ProductService::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'services')->first();
            $security   = ProductService::where('created_by', '=', \Auth::user()->creatorId())->where('type', 'security services')->first();
            $ismeeting   = Space::with('type')->where('created_by', '=', \Auth::user()->creatorId())->where('meeting', 'yes')->get();
        } else {
            if($space_type->chair_id == 0){
                $type ='virtual';
                $spaces  = Space::join('space_types', 'spaces.type_id', '=', 'space_types.id')->where('space_types.name', 'Virtual Office')->where('spaces.owned_by', '=', \Auth::user()->ownedId())->select('spaces.id','spaces.name','space_types.name as space_types_name')->get();
            }else{
                $type ='office';
                $spaces      = Space::with('type')->where('owned_by', '=', \Auth::user()->ownedId())->get();
            }
            $company = Company::where('owned_by', '=', \Auth::user()->ownedId())->pluck('name', 'id');
            $contractTypes = ContractType::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $services   = ProductService::where('owned_by', '=', \Auth::user()->ownedId())->where('type', 'services')->first();
            $security   = ProductService::where('owned_by', '=', \Auth::user()->ownedId())->where('type', 'security services')->first();
            $ismeeting   = Space::with('type')->where('owned_by', '=', \Auth::user()->ownedId())->where('meeting', 'yes')->get();

        }
            $date         = $contract->start_date . ' to ' . $contract->end_date;
            $contract->setAttribute('date', $date);
            $roomassign = Roomassign::where('contract_id',$contract->id)->get();
            $chairs = Chair::where('space_id',@$roomassign[0]->space_id)->get();
            $chairused =Roomassign::where('space_id',@$roomassign[0]->space_id)->pluck('chair_id')->toArray();
        return view('contract.copy', compact('contractTypes','chairs', 'chairused','spaces', 'contract', 'ismeeting','roomassign','type','services','security','company'));

    }

    public function copycontractstore(Request $request)
    {
        if (\Auth::user()->type == 'company') {
            if($request->create_type == 'virtual'){
                $rules = [
                    'subject' => 'required',
                    'type' => 'required',
                    'services_charges' => 'required',
                    'value' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'space' => 'required',
                    'room_hours' => 'required',
                    'hourly_rate' => 'required',
                    'services_id' => 'required',
                    'services_charges' => 'required',
                    'security_deposit_id' => 'required',
                    'security_deposit_price' => 'required',
                ];
            }else{
                $rules = [
                    'subject' => 'required',
                    'services_charges' => 'required',
                    'type' => 'required',
                    'value' => 'required',
                    'start_date' => 'required',
                    'end_date' => 'required',
                    'space' => 'required',
                    'chair' => 'required',
                    'room_hours' => 'required',
                    'hourly_rate' => 'required',
                    'services_id' => 'required',
                    'services_charges' => 'required',
                    'security_deposit_id' => 'required',
                    'security_deposit_price' => 'required',                    
                ];
            }


            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('contract.index')->with('error', $messages->first());
            }
            DB::beginTransaction();
            try {
                    $company  = Company::where('id', $request->company)->first();

                    $contract              = new Contract();
                    $contract->contract_id     = $this->contractNumber();
                    $contract->company_id      = $company->id;
                    $contract->subject     = $request->subject;
                    $contract->type        = $request->type;
                    $contract->value       = $request->value;
                    $contract->start_date  = $request->start_date;
                    $contract->end_date    = $request->end_date;
                    $contract->description = $request->description;
                    $contract->service_id = $request->services_id;
                    $contract->service_price = $request->services_charges;
                    $contract->security_deposit_id = $request->security_deposit_id;
                    $contract->security_deposit_price = $request->security_deposit_price;
                    $contract->owned_by  = \Auth::user()->ownedId();
                    $contract->created_by  = \Auth::user()->creatorId();
                    $contract->save();
                
                if($request->create_type == 'virtual'){
                        $assign_room   = new Roomassign();
                        $assign_room->company_id     = $company->id;
                        $assign_room->contract_id     = $contract->id;
                        $assign_room->space_id        = $request->space;
                        $assign_room->chair_id        = 0;
                        $assign_room->save();
                }else{
                    for ($i = 0; $i < count($request->chair); $i++) {
                        $assign_room   = new Roomassign();
                        $assign_room->company_id     = $company->id;
                        $assign_room->contract_id     = $contract->id;
                        $assign_room->space_id        = $request->space;
                        $assign_room->chair_id        = $request->chair[$i];
                        $assign_room->save();
                    }
                }

                for ($j = 0; $j < count($request->room_hours_ids); $j++) {
                    $contract_space_hour = new ContractSpaceHoure;
                    $contract_space_hour->contract_id   = $contract->id;
                    $contract_space_hour->space_id      = $request->room_hours_ids[$j];
                    $contract_space_hour->company_id    = $company->id;
                    $contract_space_hour->assign_hour   = $request->room_hours[$j];
                    $contract_space_hour->hourly_rate   = $request->hourly_rate[$j];
                    $contract_space_hour->save();
                }

                DB::commit();
                
                //Send Email
                $setings = Utility::settings();
                if ($setings['new_contract'] == 1) {

                    $customer = Customer::where('company_id', '=', $contract->company_id)->first();
                    $contractArr = [
                        'contract_subject' => $request->subject,
                        'contract_client' => $customer->name,
                        'contract_value' => \Auth::user()->priceFormat($request->value),
                        'contract_start_date' => \Auth::user()->dateFormat($request->start_date),
                        'contract_end_date' => \Auth::user()->dateFormat($request->end_date),
                        'contract_description' => $request->description,
                    ];

                    // Send Email
                    $resp = Utility::sendEmailTemplate('new_contract', [$customer->id => $customer->email], $contractArr);

                    return redirect()->route('contract.index')->with('success', __('Contract successfully created.') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
                }


                //Slack Notification
                $setting  = Utility::settings(\Auth::user()->creatorId());
                if (isset($setting['contract_notification']) && $setting['contract_notification'] == 1) {
                    $msg = $request->subject . ' ' . __("created by") . ' ' . \Auth::user()->name . '.';
                    Utility::send_slack_msg($msg);
                }

                //Telegram Notification
                $setting  = Utility::settings(\Auth::user()->creatorId());
                if (isset($setting['telegram_contract_notification']) && $setting['telegram_contract_notification'] == 1) {
                    $msg = $request->subject . ' ' . __("created by") . ' ' . \Auth::user()->name . '.';
                    Utility::send_telegram_msg($msg);
                }

            return redirect()->route('contract.index')->with('success', __('Contract successfully created.'));
            } catch (\Exception $e) {
                dd($e);
                DB::rollback();
                return redirect()->back()->with('error', $e);
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function sendmailContract($id, Request $request)
    {

        $contract = Contract::find($id);
        $contractArr = [
            'contract_id' => $contract->id,
        ];
        $setings = Utility::settings();
        if ($setings['new_contract'] == 1) {
            
            $customer = Customer::where('company_id', '=', $contract->company_id)->first();

            $estArr = [
                'email' => $customer->email,
                'contract_subject' => $contract->subject,
                'contract_client' => $customer->name,
                'contract_start_date' => $contract->start_date,
                'contract_end_date' => $contract->end_date,
            ];
            $resp = Utility::sendEmailTemplate('new_contract', [$customer->id => $customer->email], $estArr);
            return redirect()->route('contract.show', Crypt::encrypt($contract->id))->with('success', __('Email Send successfully!') . (($resp['is_success'] == false && !empty($resp['error'])) ? '<br> <span class="text-danger">' . $resp['error'] . '</span>' : ''));
        }
    }

    public function signature($id)
    {
        $contract = Contract::find($id);
        return view('contract.signature', compact('contract'));
    }
    public function signatureStore(Request $request)
    {
        $contract              = Contract::find($request->contract_id);

        if (\Auth::user()->type == 'company' || \Auth::user()->type == 'branch') {
            $contract->company_signature       = $request->company_signature;
        }
        if (\Auth::user()->type == 'client') {
            $contract->client_signature       = $request->client_signature;
        }

        $contract->save();

        return response()->json(
            [
                'Success' => true,
                'message' => __('Contract Signed successfully'),
            ],
            200
        );
    }

    public function pdffromcontract($contract_id)
    {
        $id = \Illuminate\Support\Facades\Crypt::decrypt($contract_id);

        $contract  = Contract::findOrFail($id);

        return view('contract.template', compact('contract'));
    }
    
    public function contract_status($contract_id)
    {
        $id = \Illuminate\Support\Facades\Crypt::decrypt($contract_id);
        $contract  = Contract::findOrFail($id);
        $contract->close_date = date('Y-m-d H:i:s');
        $contract->save();

        Roomassign::where('contract_id', $id)->update(
            [
                'status' => 'close',
            ]); 

        return redirect()->back()->with('success', __('Contract successfully Close!'));
        // return view('contract.template', compact('contract'));
    }

    // public function contract_clear($contract_id)
    // {
    //     $id = $contract_id;
    //     if(\Auth::user()->type == ('company')){
    //         $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
    //                 ->where('created_by', \Auth::user()->creatorId())->get()
    //                 ->pluck('code_name', 'id');
    //     }else{
    //         $chartAccounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
    //                 ->where('owned_by', \Auth::user()->ownedId())->get()
    //                 ->pluck('code_name', 'id');
    //     }
    //     $contract  = Contract::findOrFail($id);
    //     $Id =$id;
    //     // $contract->cont_status = 'close';
    //     // $contract->save();
    //     // dd($id);
    //     return view('contract.contract_clear', compact('chartAccounts','Id','contract'));

    //     return redirect()->back()->with('success', __('Contract successfully Close!'));
    //     // return view('contract.template', compact('contract'));
    // }
}
