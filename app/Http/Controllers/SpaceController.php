<?php

namespace App\Http\Controllers;

use App\Models\Chair;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\Plan;
use App\Models\ProductService;
use App\Models\Roomassign;
use App\Models\SpaceType;
use App\Models\Space;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class SpaceController extends Controller
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
        if (\Auth::user()->can('view space')) {
            if (\Auth::user()->type == 'company') {
                $user = \Auth::user();
                $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
                $branches->prepend(\Auth::user()->name, \Auth::user()->id);
                $branches->prepend('Select Branch', '');
                $query = Space::where('created_by', '=', \Auth::user()->creatorId());
            } else {
                $user = \Auth::user();
                $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $branches->prepend('Select Branch', '');
                $query = Space::where('owned_by', '=', \Auth::user()->ownedId());
            }

            if (!empty($request->branches)) {
                $query->where('owned_by', '=', $request->branches);
            }

            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $columns = ['name', 'type_id', 'capacity', 'price', 'meeting', 'window', 'description']; // Use 'type_id' instead of 'type'
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'like', "%$searchTerm%");
                    }
                    $q->orWhereHas('type', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', "%$searchTerm%");
                    });
                });
            }
            
            

            $perPage = 10;
            $spaces = $query->paginate($perPage);

            return view('space.index', compact('spaces', 'branches'));
        } else {
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
        if (\Auth::user()->can('create space')) {
            if ($request->ajax) {
                return view('space.createAjax');
            } else {
                $customFields = CustomField::where('module', '=', 'space')->get();

                if (\Auth::user()->type == 'company') {
                    $user = \Auth::user();
                    $spacetype = SpaceType::where('created_by', '=', $user->creatorId())->pluck('name', 'id');

                } else {
                    $user = \Auth::user();
                    $spacetype = SpaceType::where('owned_by', '=', $user->ownedId())->pluck('name', 'id');
                }

                return view('space.create', compact('customFields', 'spacetype'));
            }
        } else {
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
        if (\Auth::user()->can('create space')) {
            $user = \Auth::user();
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'type_id' => 'required',
                    'capacity' => 'required',
                    'price' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                if ($request->ajax) {
                    return response()->json(['error' => $messages->first()], 401);
                } else {
                    return redirect()->back()->with('error', $messages->first());
                }
            }
            $branches = Space::create(
                [
                    'name' => $request->name,
                    'type_id' => $request->type_id,
                    'capacity' => $request->capacity,
                    'price' => $request->price,
                    'window' => $request->window,
                    'meeting' => $request->meeting,
                    'description' => $request->description,
                    'owned_by' => $user->ownedId(),
                    'created_by' => $user->creatorId(),
                ]
            );
            if ($branches->capacity > 0) {
                for ($i = 1; $i <= $branches->capacity; $i++) {
                    Chair::create(
                        [
                            'name' => 'chair' . $i,
                            'space_id' => $branches->id,
                            'type' => 'none',
                            'price' => '0',
                            'owned_by' => $user->ownedId(),
                            'created_by' => $user->creatorId(),
                        ]
                    );
                }
            }
            $space_type = SpaceType::where('id', $request->type_id)->first();

            $productService = new ProductService();
            $productService->name = $branches->name;
            $productService->description = $branches->description;
            $productService->sku = $branches->id;
            $productService->sale_price = 0;
            $productService->purchase_price = 0;
            $productService->tax_id = $space_type->tax_id;
            $productService->unit_id = 0;
            $productService->space_id = $branches->id;
            $productService->quantity = 0;
            $productService->type = 'service';
            $productService->sale_chartaccount_id = $space_type->account_head;
            $productService->expense_chartaccount_id = 0;
            $productService->category_id = 0;
            $productService->owned_by = \Auth::user()->ownedId();

            $productService->created_by = \Auth::user()->creatorId();
            $productService->save();

            return redirect()->route('space.index')->with('success', __('Space successfully created.'));

        } else {
            if ($request->ajax) {
                return response()->json(['error' => __('Permission Denied.')], 401);
            } else {
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
    public function edit(Space $space)
    {
        if (\Auth::user()->can('edit space')) {
            $user = \Auth::user();
            if ($space->created_by == $user->creatorId() || $space->owned_by == $user->ownedId()) {

                $space->customField = CustomField::getData($space, 'space');
                $customFields = CustomField::where('module', '=', 'space')->get();

                if (\Auth::user()->type == 'company') {
                    $user = \Auth::user();
                    $spacetype = SpaceType::where('created_by', '=', $user->creatorId())->pluck('name', 'id');

                } else {
                    $user = \Auth::user();
                    $spacetype = SpaceType::where('owned_by', '=', $user->ownedId())->pluck('name', 'id');
                }

                return view('space.edit', compact('space', 'customFields', 'spacetype'));
            } else {
                return response()->json(['error' => __('Invalid Space.')], 401);
            }
        } else {
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
    public function update(Space $space, Request $request)
    {
        if (\Auth::user()->can('edit space')) {
            $user = \Auth::user();
            if ($space->created_by == $user->creatorId() || $space->owned_by == $user->ownedId()) {
                $validation = [
                    'name' => 'required',
                    'type_id' => 'required',
                    'capacity' => 'required',
                    'price' => 'required',
                ];

                $room = [];
                $room['name'] = $request->name;
                $room['type_id'] = $request->type_id;
                $room['capacity'] = $request->capacity;
                $room['price'] = $request->price;
                $room['meeting'] = $request->meeting;
                $room['window'] = $request->window;
                $room['description'] = $request->description;

                $validator = \Validator::make($request->all(), $validation);
                if ($validator->fails()) {
                    $messages = $validator->getMessageBag();

                    return redirect()->back()->with('error', $messages->first());
                }

                $space->update($room);

                $chair = Chair::where('space_id', $space->id)->count();
                if ($room['capacity'] > 0 && $room['capacity'] != $chair) {
                    if ($room['capacity'] > $chair) {
                        $diffchair = $room['capacity'] - $chair;
                        $a = $chair;
                        for ($i = 1; $i <= $diffchair; $i++) {
                            Chair::create(
                                [
                                    'name' => 'chair' . ++$a,
                                    'space_id' => $space->id,
                                    'type' => 'none',
                                    'price' => '0',
                                    'owned_by' => $user->ownedId(),
                                    'created_by' => $user->creatorId(),
                                ]
                            );
                        }

                    } else {
                        $diffchair = $chair - $room['capacity'];
                        $a = $chair;
                        for ($i = 1; $i <= $diffchair; $i++) {
                            Chair::where('name', 'chair' . $a--)->where('space_id', $space->id)->delete();
                        }

                    }
                }

                CustomField::saveData($space, $request->customField);

                return redirect()->back()->with('success', __('Space Updated Successfully!'));
            } else {
                return redirect()->back()->with('error', __('Invalid Space.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Space $space)
    {
        $user = \Auth::user();
        if ($space->created_by == $user->creatorId() || $space->owned_by == $user->ownedId()) {
            Chair::where('space_id', $space->id)->delete();
            $space->delete();
            return redirect()->back()->with('success', __('Space Deleted Successfully!'));

        } else {
            return redirect()->back()->with('error', __('Invalid Space.'));
        }
    }

    public function space_details($type)
    {
        if (\Auth::user()->type == 'company') {
            $user = \Auth::user();
            // $spaces = Space::where('created_by', '=', $user->creatorId())->get();
            $a = Company::where('created_by', '=', $user->creatorId())->pluck('id');
            $b = Roomassign::where('status', 'assign')->whereIN('company_id', $a)->groupBy('space_id')->pluck('space_id');
            if ($type == 'used') {
                $spaces = Space::where('created_by', '=', $user->creatorId())->whereIN('id', $b)->get();
            } else {
                $spaces = Space::where('created_by', '=', $user->creatorId())->whereNotIn('id', $b)->get();
            }
        } else {
            $user = \Auth::user();
            $a = Company::where('owned_by', '=', $user->ownedId())->pluck('id');
            $b = Roomassign::where('status', 'assign')->whereIN('company_id', $a)->groupBy('space_id')->pluck('space_id');
            if ($type == 'used') {
                $spaces = Space::where('owned_by', '=', $user->ownedId())->whereIN('id', $b)->get();
            } else {
                $spaces = Space::where('owned_by', '=', $user->ownedId())->whereNotIn('id', $b)->get();
            }
        }
        return view('space.details', compact('spaces', 'type'));
    }


}
