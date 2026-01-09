<?php

namespace App\Http\Controllers;

use App\Models\Chair;
use App\Models\CustomField;
use App\Models\Plan;
use App\Models\Roomassign;
use App\Models\SpaceType;
use App\Models\User;
use App\Models\Utility;
use App\Models\IsVisitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;


class IsVisitorController extends Controller
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
        if(\Auth::user()->can('manage vistor'))
        {
            if(\Auth::user()->type == 'company'){
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
                $branches->prepend('Select Branch', '');
                $user    = \Auth::user();
                $query = IsVisitor::where('created_by', '=', $user->creatorId());
            }
            else if(\Auth ::user()->type == 'clientuser')
            {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $user    = \Auth::user();
                $query = IsVisitor::where('company_id', '=', $user->company_id);
            }else{
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $user    = \Auth::user();
                $query = IsVisitor::where('owned_by', '=', $user->ownedId());
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            $isvisitors = $query->paginate(10);
            return view('isvisitor.index', compact('isvisitors','branches'));
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
        if(\Auth::user()->can('create vistor'))
        {
            if($request->ajax)
            {
                return view('isvisitor.createAjax');
            }
            else
            {
                $customFields = CustomField::where('module', '=', 'chair')->get();

                return view('isvisitor.create', compact('customFields'));
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
        if(\Auth::user()->can('create vistor'))
        {
            $user      = \Auth::user();
            $validator = \Validator::make(
                $request->all(), [
                    'name' => 'required',
                    'date' => 'required',
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
           
                $isvisitors = IsVisitor::create(
                    [
                        'name' => $request->name,
                        'company_id' => $user->company_id,
                        'cnic' => $request->cnic,
                        'date_time' => $request->date,
                        'user_id' => $user->id,
                        'owned_by' => $user->ownedId(),
                        'created_by' => $user->creatorId(),
                    ]
                );

                return redirect()->route('isvisitor.index')->with('success', __('IsVisitor successfully created.'));

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
    public function edit(IsVisitor $isvisitor)
    {
        if(\Auth::user()->can('edit vistor'))
        {
            $user = \Auth::user();
            if($isvisitor->created_by == $user->creatorId() || $isvisitor->owned_by == $user->ownedId())
            {

                $isvisitor->customField = CustomField::getData($isvisitor, 'isvistor');
                $customFields        = CustomField::where('module', '=', 'isvistor')->get();

                return view('isvisitor.edit', compact('isvisitor', 'customFields'));
            }
            else
            {
                return response()->json(['error' => __('Invalid Visitor.')], 401);
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
    public function update(IsVisitor $isvisitor, Request $request)
    {
        if(\Auth::user()->can('edit vistor'))
        {
            $user = \Auth::user();
            if($isvisitor->created_by == $user->creatorId() || $isvisitor->owned_by == $user->id)
            {
                $validation = [
                    'name' => 'required',
                    'date' => 'required',
                ];

                $post         = [];
                $post['name'] = $request->name;
                $post['date_time'] = $request->date;
                $post['cnic'] = $request->cnic;
                // $post['price'] = $request->price;
                // $post['type'] = $request->type;
               

                $validator = \Validator::make($request->all(), $validation);
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $isvisitor->update($post);

                CustomField::saveData($isvisitor, $request->customField);

                return redirect()->back()->with('success', __('Visitor Updated Successfully!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Invalid Visitor.'));
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
    public function destroy(IsVisitor $isvisitor)
    {
        if(\Auth::user()->can('delete vistor'))
            {
            $user = \Auth::user();
            if($isvisitor->created_by == $user->creatorId()  || $isvisitor->owned_by == $user->ownedId())
            {
                $isvisitor->delete();
                return redirect()->back()->with('success', __('Visitor Deleted Successfully!'));

            }
            else
            {
                return redirect()->back()->with('error', __('Invalid Visitor.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
