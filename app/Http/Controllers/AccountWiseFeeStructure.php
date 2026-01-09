<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Models\ClassWiseFee;
use App\Models\FeeHead;
use App\Models\Session;
use App\Models\StudentFeeStructure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountWiseFeeStructure extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
{
    $heads = FeeHead::where('created_by', \Auth::user()->creatorId())->get()->pluck('fee_head', 'id');
    $heads->prepend('Select Head', '');
    $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
    $branches->prepend(\Auth::user()->name, \Auth::user()->id);
    $branches->prepend('Select Branch', '');
    $classes = collect();
    // $classes->prepend('Select Class', '');
    $class_wise_fee = new LengthAwarePaginator([], 0, 25); // Empty paginator as fallback
    if (!empty($request->branches) && !empty($request->head_id)) {
        $query = ClassWiseFee::where('owned_by', $request->branches)
        ->where('head_id', $request->head_id);
        // dd($query->get(),$request->head_id,$request->branches);
        $classes = Classes::where('owned_by', $request->branches)->pluck('name', 'id');
        $classes->prepend('Select Class', '');

        if (!empty($request->class_id)) {
            $query->where('class_id', $request->class_id);
        }
        $class_wise_fee = $query->get();
    }else{
        $classes->prepend('Select Class', '');
    }

    return view('students.accountwisestructure.index', compact('branches', 'class_wise_fee','heads', 'classes'));
}


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // if (\Auth::user()->type == 'company') {
            $session = Session::where('created_by',\Auth::user()->creatorId())->get()->pluck('year','id');
            $heads = FeeHead::where('created_by', \Auth::user()->creatorId())->get()->pluck('fee_head', 'id');
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        // } else {
        //     $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        //     $heads = FeeHead::where('owned_by', \Auth::user()->ownedId())->get()->pluck('fee_head', 'id');
        // }
        $branches->prepend('Select Branch', '');
        $session->prepend('Select Session', '');
        return view('students.accountwisestructure.create', compact('branches', 'heads','session'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $fee_heads_query = ClassWiseFee::where('head_id', $request->head_id)
            ->where('session_id', $request->session_id)
            ->where('owned_by', $request->branches);
        if ($request->class_id != '') {
            $fee_head = $fee_heads_query->where('class_id', $request->class_id)->first();
            if ($fee_head) {
                $fee_head->amount = $request->amount;
                $fee_head->save();
            } else {
                ClassWiseFee::create([
                    'head_id' => $request->head_id,
                    'session_id' => $request->session_id,
                    'owned_by' => $request->branches,
                    'class_id' => $request->class_id,
                    'amount' => $request->amount,
                ]);
            }
        } else {
            $classes = Classes::where('owned_by', $request->branches)->get();
            foreach ($classes as $class) {
                $fee_head = $fee_heads_query->where('class_id', $class->id)->first();
                if ($fee_head) {
                    $fee_head->amount = $request->amount;
                    $fee_head->save();
                } else {
                    ClassWiseFee::create([
                        'head_id' => $request->head_id,
                        'session_id' => $request->session_id,
                        'owned_by' => $request->branches,
                        'class_id' => $class->id,
                        'amount' => $request->amount,
                    ]);
                }
            }
        }
        // dd($fee_heads_query->get());
        return redirect()->back()->with('success', 'Account Wise Fee Attached Successfully');
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
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        dd($request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function update_student_fee_str(Request $request)
    {
        try {
            // dd($request->all());
            $data = $request->all();
            foreach ($data as $item) {
                if($item['studentId'] != '' && $item['studentId'] != 0 && $item['studentId'] != '0'){
                    $studentfeestr = StudentFeeStructure::where('student_id', $item['studentId'])
                        ->where('class_id', $item['classId'])->where('branch_id', $item['branchId'])
                        ->where('head_id',$item['headId'])->first();
                }else{
                    $studentfeestr = StudentFeeStructure::where('reg_id', $item['regId'])
                        ->where('class_id', $item['classId'])->where('branch_id', $item['branchId'])
                        ->where('head_id',$item['headId'])->first();
                }
                if ($studentfeestr) {
                    $studentfeestr->amount = $item['amount'];
                    $studentfeestr->discount = $item['discount'];
                    $studentfeestr->save();
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Data saved successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
