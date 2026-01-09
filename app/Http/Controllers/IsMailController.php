<?php

namespace App\Http\Controllers;

use App\Models\Chair;
use App\Models\CustomField;
use App\Models\IsMail;
use App\Models\Plan;
use App\Models\Roomassign;
use App\Models\SpaceType;
use App\Models\User;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;


class IsMailController extends Controller
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
        if(\Auth::user()->can('manage ismail'))
        {
            
            if(\Auth::user()->type == 'company'){
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);               
                $branches->prepend('Select Branch', '');
                $user    = \Auth::user();
                $query = IsMail::where('created_by', '=', $user->creatorId());
            }else if(\Auth ::user()->type == 'clientuser')
            {
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $user    = \Auth::user();
                $query = IsMail::where('company_id', '=', $user->company_id);
            }
            else{
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $user    = \Auth::user();
                $query = IsMail::where('owned_by', '=', $user->ownedId());
            }
            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }
            $ismails = $query->paginate(10);
            return view('ismail.index', compact('ismails','branches'));
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
        if(\Auth::user()->can('create ismail'))
        {
            if($request->ajax)
            {
                return view('ismail.createAjax');
            }
            else
            {
                $customFields = CustomField::where('module', '=', 'ismail')->get();

                return view('ismail.create', compact('customFields'));
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
        if(\Auth::user()->can('create ismail'))
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
           
                $ismail = IsMail::create(
                    [
                        'company_id' => $user->company_id,
                        'name' => $request->name,
                        'date' => $request->date,
                        'user_id' => $user->id,
                        'owned_by' => $user->ownedId(),
                        'created_by' => $user->creatorId(),
                    ]
                );

                return redirect()->back()->with('success', __('Mail successfully created.'));

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
    public function edit(IsMail $ismail)
    {
        // dd($ismail);
        if(\Auth::user()->can('edit ismail'))
        {
            $user = \Auth::user();
            if($ismail->created_by == $user->creatorId() || $ismail->owned_by == $user->id)
            {

                $ismail->customField = CustomField::getData($ismail, 'ismail');
                $customFields        = CustomField::where('module', '=', 'chair')->get();

                return view('ismail.edit', compact('ismail', 'customFields'));
            }
            else
            {
                return response()->json(['error' => __('Invalid IsMail.')], 401);
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
    public function update(IsMail $ismail, Request $request)
    {
        if(\Auth::user()->can('edit ismail'))
        {
            $user = \Auth::user();
            if($ismail->created_by == $user->creatorId() || $ismail->owned_by == $user->ownedId())
            {
                $validation = [
                    'name' => 'required',
                    'date' => 'required',
                ];

                $post         = [];
                $post['name'] = $request->name;
                $post['date'] = $request->date;
                if(Auth::user()->type != 'clientuser'){
                    $post['price'] = $request->price;            
                }
                $validator = \Validator::make($request->all(), $validation);
                if($validator->fails())
                {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $ismail->update($post);

                CustomField::saveData($ismail, $request->customField);

                return redirect()->back()->with('success', __('Mail Updated Successfully!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Invalid IsMail.'));
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
    public function destroy(IsMail $ismail)
    {
        if(\Auth::user()->can('delete ismail'))
        {
            $user = \Auth::user();
            if($ismail->created_by == $user->creatorId()  || $ismail->owned_by ==  $user->ownedId())
            {
        
                $ismail->delete();
                return redirect()->back()->with('success', __('Mail Deleted Successfully!'));

            }
            else
            {
                return redirect()->back()->with('error', __('Invalid Mail.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }
}
