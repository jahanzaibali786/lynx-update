<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\ProductService;
use App\Models\StudyPack;
use App\Models\StudyPackItem;
use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudyPackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $studypacks = StudyPack::paginate(25);
        return view('students.studypack.index', compact('studypacks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $session = Session::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('year', 'id');
        $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
            ->where('created_by', \Auth::user()->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');
        $class = array(
            "DAYCARE" => "DAYCARE",
            "PLAY GROUP" => "PLAY GROUP",
            "PRE-NURSERY" => "PRE-NURSERY",
            "NURSERY" => "NURSERY",
            "KG" => "KG",
            "GRADE-1" => "GRADE-1",
            "GRADE-2" => "GRADE-2",
            "GRADE-3" => "GRADE-3",
            "GRADE-4" => "GRADE-4",
            "GRADE-5" => "GRADE-5",
            "GRADE-6" => "GRADE-6",
            "GRADE-7" => "GRADE-7",
            "MATRIC-8" => "MATRIC-8",
            "MATRIC-9" => "MATRIC-9",
            "MATRIC-10" => "MATRIC-10",
            "IGCSE-8" => "IGCSE-8",
            "IGCSE-9" => "IGCSE-9",
            "IGCSE-10" => "IGCSE-10"
        );
        
        return view('students.studypack.create', compact('session', 'product_services', 'class'));
    }

    public function product(Request $request)
    {
        $data['product'] = $product = ProductService::find($request->product_id);

        $data['unit'] = (!empty($product->unit())) ? $product->unit()->name : '';
        $data['taxRate'] = $taxRate = !empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0;
        $data['taxes'] = !empty($product->tax_id) ? $product->tax($product->tax_id) : 0;
        $salePrice = $product->sale_price;
        $quantity = 1;
        $taxPrice = ($taxRate / 100) * ($salePrice * $quantity);
        $data['totalAmount'] = ($salePrice * $quantity);

        return json_encode($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {

            $validatedData = $request->validate([
                'date' => 'required|date',
                'session' => 'required',
                'title' => 'required|string|max:255',
                'class' => 'required',
                'study_pack_cost' => 'required',
            ]);
            $studypack = new StudyPack();
            $studypack->title = $validatedData['title'];
            $studypack->class = $validatedData['class'];
            $studypack->date = $validatedData['date'];
            $studypack->session_id = $validatedData['session'];
            $studypack->study_pack_cost = $validatedData['study_pack_cost'];
            $studypack->owned_by = \Auth::user()->ownedId();
            $studypack->created_by = \Auth::user()->creatorId();
            $studypack->save();
            $updatePrice = 0;
            $products = $request->items;
            for ($i = 0; $i < count($products); $i++) {
                $invoiceProduct = new StudyPackItem();
                $invoiceProduct->study_pack_id = $studypack->id;
                $invoiceProduct->product_id = $products[$i]['item'];
                $invoiceProduct->quantity = $products[$i]['quantity'];
                $invoiceProduct->tax = $products[$i]['tax'];
                // $invoiceProduct->discount    = isset($products[$i]['discount']) ? $products[$i]['discount'] : 0;
                $invoiceProduct->discount = $products[$i]['discount'];
                $invoiceProduct->price = $products[$i]['price'];
                // $invoiceProduct->description = $products[$i]['description'];
                $invoiceProduct->save();
                $updatePrice += ($products[$i]['price'] * $products[$i]['quantity']) + ($products[$i]['itemTaxPrice']) - ($products[$i]['discount']);
            }
            // dd($updatePrice);
            $studypack->study_pack_cost = $updatePrice;
            $studypack->save();
            DB::commit();
            return redirect()->route('studypack.index')->with('success', 'StudyPack created successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            dd($e);
            return redirect()->back()->with('error', $e);
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

        if (\Auth::user()->can('show invoice')) {
            $invoice = StudyPack::find($id);
            if (!empty($invoice->created_by) == \Auth::user()->creatorId()) {

                // $customer             = $invoice->customer;
                $iteams = $invoice->items;
                $user = \Auth::user();

                // start for storage limit note
                $invoice_user = User::find($invoice->created_by);
                $user_plan = Plan::find($invoice_user->plan);
                // end for storage limit note



                return view('students.studypack.view', compact('invoice', 'iteams', 'user', 'invoice_user', 'user_plan'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (\Auth::user()->can('edit invoice')) {
            $invoice = StudyPack::find($id);
            $session = Session::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('year', 'id');
            $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
                ->where('created_by', \Auth::user()->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');
            $class = array(
                "DAYCARE" => "DAYCARE",
                "PLAY GROUP" => "PLAY GROUP",
                "PRE-NURSERY" => "PRE-NURSERY",
                "NURSERY" => "NURSERY",
                "KG" => "KG",
                "GRADE-1" => "GRADE-1",
                "GRADE-2" => "GRADE-2",
                "GRADE-3" => "GRADE-3",
                "GRADE-4" => "GRADE-4",
                "GRADE-5" => "GRADE-5",
                "GRADE-6" => "GRADE-6",
                "GRADE-7" => "GRADE-7",
                "MATRIC-8" => "MATRIC-8",
                "MATRIC-9" => "MATRIC-9",
                "MATRIC-10" => "MATRIC-10",
                "IGCSE-8" => "IGCSE-8",
                "IGCSE-9" => "IGCSE-9",
                "IGCSE-10" => "IGCSE-10"
            );

            return view('students.studypack.edit', compact('product_services', 'session', 'invoice', 'class'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
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
        if (\Auth::user()->can('edit invoice')) {
            DB::beginTransaction();
            try {
                $invoice = StudyPack::find($id);
                if ($invoice->created_by == \Auth::user()->creatorId()) {
                    $validator = \Validator::make(
                        $request->all(),
                        [
                            'date' => 'required|date',
                            'session' => 'required',
                            'title' => 'required|string|max:255',
                            'class' => 'required',
                            'study_pack_cost' => 'required',
                        ]
                    );
                    if ($validator->fails()) {
                        $messages = $validator->getMessageBag();

                        return redirect()->route('studypack.edit')->with('error', $messages->first());
                    }

                    $studypack = StudyPack::find($id);
                    $studypack->date = $request['date'];
                    $studypack->session_id = $request['session'];
                    $studypack->title = $request['title'];
                    $studypack->class = $request['class'];
                    $studypack->study_pack_cost = $request['study_pack_cost'];
                    $studypack->save();
                    $updatePrice = 0;
                    $products = $request->items;

                    for ($i = 0; $i < count($products); $i++) {
                        $invoiceProduct = StudyPackItem::find($products[$i]['id']);

                        if ($invoiceProduct == null) {
                            $invoiceProduct = new StudyPackItem();
                            $invoiceProduct->study_pack_id = $studypack->id;

                        }

                        if (isset($products[$i]['item'])) {
                            $invoiceProduct->product_id = $products[$i]['item'];
                        }

                        $invoiceProduct->quantity = $products[$i]['quantity'];
                        $invoiceProduct->tax = $products[$i]['tax'];
                        //                    $invoiceProduct->discount    = isset($products[$i]['discount']) ? $products[$i]['discount'] : 0;
                        $invoiceProduct->discount = $products[$i]['discount'];
                        $invoiceProduct->price = $products[$i]['price'];
                        $invoiceProduct->save();
                        $updatePrice += ($products[$i]['price'] * $products[$i]['quantity']) + ($products[$i]['itemTaxPrice']) - ($products[$i]['discount']);

                    }
                    $studypack->study_pack_cost = $updatePrice;
                    $studypack->save();

                    DB::commit();
                    return redirect()->route('studypack.index')->with('success', __('StudyPack successfully updated.'));
                } else {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }
            } catch (\Exception $e) {
                DB::rollback();
                return redirect()->back()->with('error', $e);
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $studypack = StudyPack::findOrFail($id);
        $studypack->delete();
        return redirect()->back()->with('success', 'Study Pack Deleted successfully!');
    }

    public function items(Request $request)
    {
        $items = StudyPackItem::where('study_pack_id', $request->invoice_id)->where('product_id', $request->product_id)->first();
        return json_encode($items);
    }


    public function challanform()
    {
        if (\Auth::user()->type == 'company') {
            $branch = User::where('type', '=', 'company')->get()->pluck('name', 'id');
        }
        return view('students.studypack.challanform', compact('branch'));
    }
}
