<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use App\Models\CustomField;
use App\Models\Plan;
use App\Models\SpaceType;
use App\Models\Tax;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class SpaceTypeController extends Controller
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
    public function index(Request $request)
    {
        if(\Auth::user()->can('view spacetype'))
        {
            if(\Auth::user()->type == 'company'){
            $user    = \Auth::user();
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
            $branches->prepend('Select Branch', '');
            $query = SpaceType::where('created_by', '=', $user->creatorId());

        }else{
            $user    = \Auth::user();
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = SpaceType::where('owned_by', '=', $user->ownedId());
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            $spacetype = $query->paginate(10);
            return view('spacetype.index', compact('spacetype','branches'));
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if(\Auth::user()->can('create spacetype'))
        {
            if($request->ajax)
            {
                return view('spacetype.createAjax');
            }
            else
            {
                $customFields = CustomField::where('module', '=', 'spacetype')->get();

                return view('spacetype.create', compact('customFields'));
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
        if(\Auth::user()->can('create spacetype'))
        {
            $user      = \Auth::user();
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'tax_name' => 'required|max:20',
                    'rate' => 'required|numeric',
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
            DB::beginTransaction();
            try
            {
                $branches = SpaceType::create(
                    [
                        'name' => $request->name,
                        'owned_by' => $user->ownedId(),
                        'created_by' => $user->creatorId(),
                    ]
                );
                
                $tax             = new Tax();
                $tax->name       = $request->tax_name;
                $tax->rate       = $request->rate;
                $tax->owned_by = \Auth::user()->ownedId();
                $tax->created_by = \Auth::user()->creatorId();
                $tax->save();
                // if(\Auth::user()->type == 'company'){                
                    $types = ChartOfAccountType::where('created_by', '=', \Auth::user()->creatorId())->where('name','Income')->first();
                // }else{
                //     $types = ChartOfAccountType::where('owend_by', '=', \Auth::user()->ownedId())->where('name','Income')->first();
                // }
                if($types){
                    $sub_type = ChartOfAccountSubType::where('type', $types->id)->where('name','Sales Revenue')->first();
                }
                
                $account              = new ChartOfAccount();
                $account->name        = $branches->name;
                $account->code        = $branches->id;
                $account->type        = @$types->id;
                $account->sub_type    = @$sub_type->id;
                $account->description = $branches->name.' Income ';
                $account->is_enabled  = 1;
                $account->owned_by  = \Auth::user()->ownedId();
                $account->created_by  = \Auth::user()->creatorId();
                $account->save();
                // dd($account);
                
                SpaceType::where('id',$branches->id)->update(
                    [
                        'tax_id' => $tax->id,
                        'account_head' => $account->id,
                        ]
                    );
                DB::commit();
                return redirect()->route('spacetype.index')->with('success', __('Spacetype successfully created.'));
            }
            catch(\Exception $e)
            {
                DB::rollback();
                return redirect()->route('spacetype.index')->with('error', $e);
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
    public function edit(SpaceType $spacetype)
    {
        if(\Auth::user()->can('edit spacetype'))
        {
            $user = \Auth::user();
            if($spacetype->created_by == $user->creatorId() || $spacetype->owned_by == $user->ownedId())
            {

                $spacetype->customField = CustomField::getData($spacetype, 'spacetype');
                $customFields        = CustomField::where('module', '=', 'spacetype')->get();

                return view('spacetype.edit', compact('spacetype', 'customFields'));
            }
            else
            {
                return response()->json(['error' => __('Invalid Spacetype.')], 401);
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
    public function update(SpaceType $spacetype, Request $request)
    {
        if(\Auth::user()->can('edit spacetype'))
        {
            $user = \Auth::user();
            if($spacetype->created_by == $user->creatorId() || $spacetype->owned_by == $user->ownedId())
            {
                $validation = [
                    'name' => 'required',
                ];

                $post         = [];
                $post['name'] = $request->name;
               

                $validator = \Validator::make($request->all(), $validation);
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $spacetype->update($post);

                CustomField::saveData($spacetype, $request->customField);

                return redirect()->back()->with('success', __('Spacetype Updated Successfully!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Invalid Spacetype.'));
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
    public function destroy(SpaceType $spacetype)
    {
        $user = \Auth::user();
        if($spacetype->created_by == $user->creatorId()  || $spacetype->owned_by == $user->ownedId())
        {
    
            $spacetype->delete();
            return redirect()->back()->with('success', __('Spacetype Deleted Successfully!'));

        }
        else
        {
            return redirect()->back()->with('error', __('Invalid Spacetype.'));
        }
    }


}
