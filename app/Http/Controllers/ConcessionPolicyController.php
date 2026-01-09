<?php

namespace App\Http\Controllers;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountType;
use Auth;
use App\Models\ConcessionPolicy;
use App\Models\ConcessionPolicyHead;
use App\Models\FeeHead;
use Illuminate\Http\Request;

class ConcessionPolicyController extends Controller
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

            $concessions = ConcessionPolicy::where('created_by',Auth::user()->creatorId())->get();
            return view('students.concession_policy.index', compact('concessions'));
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
            // $fee_heads = FeeHead::get();
            // $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->where('name', 'Income')->first();
            // $chart_accounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
            // ->where('type', $types->id)
            // ->where('created_by', \Auth::user()->creatorId())->get();
            $heads= FeeHead::where('created_by', \Auth::user()->creatorId())->get();
            return view('students.concession_policy.create',compact('heads'));
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
                                    'title' => 'required',
                                   ]
                );

                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();
                    return redirect()->back()->with('error', $messages->first());
                }

                $concession              = new ConcessionPolicy();
                $concession->order_no    = $this->policyNumber();;
                $concession->title       = $request->title;
                $concession->description  = $request->description;
                $concession->owned_by    = \Auth::user()->ownedId();
                $concession->created_by  = \Auth::user()->creatorId();
                $concession->save();
                for($i=0; $i<count($request->title_id); $i++){
                    if($request->concession[$i] != 0){
                        $concession_head                 = new ConcessionPolicyHead();
                        $concession_head->concession_id  = $concession->id;
                        $concession_head->head_id        = $request->title_id[$i];
                        $concession_head->percentage     = $request->concession[$i] ?? 0;
                        $concession_head->save();
                    }
                }

                return redirect()->route('concession_policy.index')->with('Concession Policy has been created successfully');
            // }
            // else
            // {
            //     return redirect()->back()->with('error', 'Permission denied.');
            // }
    }

    function policyNumber()
    {

        $latest = ConcessionPolicy::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 100;
        }

        return $latest->order_no + 1;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ConcessionPolicy  $concessionPolicy
     * @return \Illuminate\Http\Response
     */
    public function show(ConcessionPolicy $concessionPolicy)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ConcessionPolicy  $concessionPolicy
     * @return \Illuminate\Http\Response
     */
    public function edit(ConcessionPolicy $concessionPolicy)
    {
        // if(\Auth::user()->can('edit session'))
        // {
            // $types = ChartOfAccountType::where('created_by', \Auth::user()->creatorId())->where('name', 'Income')->first();
            // $chart_accounts = ChartOfAccount::select(\DB::raw('CONCAT(code, " - ", name) AS code_name, id'))
            // ->where('type', $types->id)
            // ->where('created_by', \Auth::user()->creatorId())->get();
            $heads= FeeHead::where('created_by', \Auth::user()->creatorId())->get();
            $concession_head = ConcessionPolicyHead::where('concession_id',$concessionPolicy->id)->select('head_id','percentage')->get()->toArray();
            return view('students.concession_policy.edit', compact('concessionPolicy','heads','concession_head'));
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
     * @param  \App\Models\ConcessionPolicy  $concessionPolicy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ConcessionPolicy $concessionPolicy)
    {
        // if(\Auth::user()->can('edit session'))
        // {
            // dd($request->all());
            $validator = \Validator::make(
                $request->all(), [
                                'title' => 'required',
                               ]
            );

            if($validator->fails())
            {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $concessionPolicy->title       = $request->title;
            $concessionPolicy->description  = $request->description;
            $concessionPolicy->save();
            for($i=0; $i<count($request->title_id); $i++){
                $concession_head = ConcessionPolicyHead::where('concession_id', $concessionPolicy->id)->where('head_id', $request->title_id[$i])->first();
                if($request->concession[$i] != 0){
                    if($concession_head){
                    }else{
                        $concession_head                 = new ConcessionPolicyHead();
                        $concession_head->concession_id  = $concessionPolicy->id;
                        $concession_head->head_id        = $request->title_id[$i];
                        $concession_head->percentage     = $request->concession[$i] ?? 0;
                        $concession_head->save();
                    }

                    $concession_head->percentage = $request->concession[$i] ?? 0;
                    $concession_head->save();
                }else{
                    if($concession_head){
                        $concession_head->delete();
                    }
                }
            }

            return redirect()->route('concession_policy.index')->with( 'Concession Policy has been updated successfully');
        // }
        // else
        // {
        //     return redirect()->back()->with('error', 'Permission denied.');
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ConcessionPolicy  $concessionPolicy
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConcessionPolicy $concessionPolicy)
    {
        //
    }
}
