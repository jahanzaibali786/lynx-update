<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use Auth;
use App\Models\FeeHead;
use Illuminate\Http\Request;

class FeeHeadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // if(\Auth::user()->can('manage session'))
        // {

            $heads = FeeHead::with('account_head','receivable_head')->where('created_by',Auth::user()->creatorId())->paginate(25);
            return view('students.fee_head.index', compact('heads'));
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // if(\Auth::user()->can('create session'))
        // {

            // $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->where('name', 'Income')->first();
            // $ChartofAccounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
            // ->where('type', $types->id)
            // ->where('created_by', \Auth::user()->creatorId())->get()->pluck('code_name','id');
            $ChartofAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name, " (", chart_of_account_sub_types.name, ")") AS code_name, chart_of_accounts.id'))
            ->join('chart_of_account_sub_types', 'chart_of_accounts.sub_type', '=', 'chart_of_account_sub_types.id')
            ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())->orderBy('chart_of_account_sub_types.id')
            ->get()->pluck('code_name', 'id');


            // $types = ChartOfAccountType::where('created_by', '=', \Auth::user()->creatorId())->where('name','Assets')->first();
            // if($types){
            //     $sub_type = ChartOfAccountSubType::where('type', $types->id)->where('name','Receivables')->first();
            // }
            // $ChartofAccountsReceiveable = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
            // ->where('type', $types->id)->where('sub_type', $sub_type->id)
            // ->where('created_by', \Auth::user()->creatorId())->get()->pluck('code_name','id');
            // dd($ChartofAccountsReceiveable);
            return view('students.fee_head.create',compact('ChartofAccounts'));
        // }
        // else
        // {
        //     return response()->json(['error' => __('Permission denied.')], 401);
        // }
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
         // if(\Auth::user()->can('create session'))
        // {
            $validator = \Validator::make(
                $request->all(), [
                                   'name' => 'required',
                                   'account_id' => 'required',
                                   'receivableaccount_id' => 'required',
                                   'discountaccount_id' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $class             = new FeeHead();
            $class->fee_head       = $request->name;
            $class->account_id     = $request->account_id;
            $class->receivable_account_id     = $request->receivableaccount_id;
            $class->discount_account_id     = $request->discountaccount_id;
            $class->status     = $request->status;
            $class->owned_by = \Auth::user()->ownedId();
            $class->created_by = \Auth::user()->creatorId();
            $class->save();
            return redirect()->route('fee_head.index')->with('Fee Head has been created successfully');
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FeeHead  $feeHead
     * @return \Illuminate\Http\Response
     */
    public function show(FeeHead $feeHead)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FeeHead  $feeHead
     * @return \Illuminate\Http\Response
     */
    public function edit(FeeHead $feeHead)
    {
        // if(\Auth::user()->can('edit session'))
        // {
            // $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->where('name', 'Income')->first();
            // $ChartofAccounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
            // ->where('type', $types->id)
            // ->where('created_by', \Auth::user()->creatorId())->get()->pluck('code_name','id');

            // $types = ChartOfAccountType::where('created_by', '=', \Auth::user()->creatorId())->where('name','Assets')->first();
            // if($types){
            //     $sub_type = ChartOfAccountSubType::where('type', $types->id)->where('name','Receivables')->first();
            // }
            // $ChartofAccountsReceiveable = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
            // ->where('type', $types->id)->where('sub_type', $sub_type->id)
            // ->where('created_by', \Auth::user()->creatorId())->get()->pluck('code_name','id');
            $ChartofAccounts = ChartOfAccount::select(\DB::raw('CONCAT(chart_of_accounts.code, " - ", chart_of_accounts.name, " (", chart_of_account_sub_types.name, ")") AS code_name, chart_of_accounts.id'))
            ->join('chart_of_account_sub_types', 'chart_of_accounts.sub_type', '=', 'chart_of_account_sub_types.id')
            ->where('chart_of_accounts.created_by', \Auth::user()->creatorId())->orderBy('chart_of_account_sub_types.id')
            ->get()->pluck('code_name', 'id');

            return view('students.fee_head.edit', compact('feeHead','ChartofAccounts'));
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FeeHead  $feeHead
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FeeHead $feeHead)
    {

        // if(\Auth::user()->can('edit session'))
        // {
            $validator = \Validator::make(
                $request->all(), [
                                'fee_head' => 'required',
                                'account_id' => 'required',
                                'receivable_account_id' => 'required',
                                'discountaccount_id' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $feeHead->fee_head       = $request->fee_head;
            $feeHead->account_id       = $request->account_id;
            $feeHead->receivable_account_id       = $request->receivable_account_id;
            $feeHead->discount_account_id       = $request->discountaccount_id;
            $feeHead->status       = $request->status;
            $feeHead->save();

            return redirect()->route('fee_head.index')->with( 'Fee Head has been updated successfully');
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FeeHead  $feeHead
     * @return \Illuminate\Http\Response
     */
    public function destroy(FeeHead $feeHead)
    {
        //
    }
}
