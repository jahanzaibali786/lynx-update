<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use App\Models\Invoice;
use App\Models\Utility;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\warehouse;
use App\Models\PosPayment;
use App\Models\ReturnOrder;
use Illuminate\Http\Request;
use App\Models\InvoiceProduct;
use App\Models\ProductService;
use App\Models\WarehouseProduct;
use App\Models\ReturnOrderProduct;
use App\Exports\ReturnOrderExport;
use Illuminate\Support\Facades\Crypt;
use App\Models\ProductServiceCategory;
use Illuminate\Support\Facades\Storage;
use Dompdf\Dompdf;
use Dompdf\Options;
use Maatwebsite\Excel\Facades\Excel;

class ReturnOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $quotations      = ReturnOrder::where('created_by', \Auth::user()->creatorId())->with(['customer', 'warehouse'])->paginate(25);
        return view('returnorder.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (\Auth::user()->type == 'company') {
            $customers = warehouse::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $warehouse  = warehouse::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $quotation_number = \Auth::user()->quotationNumberFormat($this->quotationNumber());
        }else{
            $customers = warehouse::where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $warehouse  = warehouse::whereNotIn('id', $customers->keys())->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $quotation_number = \Auth::user()->quotationNumberFormat($this->quotationNumber());

        }
        return view('returnorder.create', compact('customers', 'warehouse', 'quotation_number'));
    }


    public function returnorderCreate(Request $request)
    {
        $customer = warehouse::find($request->customer_id);
        $warehouse = warehouse::find($request->warehouse_id);
        $quotation_date = $request->quotation_date;
        $quotation_number = $request->quotation_number;
        $store_id = $request->customer_id;

        $warehouseProducts = WarehouseProduct::where('created_by', '=', \Auth::user()->creatorId())->where('warehouse_id', $request->customer_id)->where('quantity', '>', '0')->get()->pluck('product_id')->toArray();
        $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))->where('created_by', \Auth::user()->creatorId())->whereIn('id', $warehouseProducts)->where('type', '!=', 'service')->get()->pluck('name', 'id');
        $product_services->prepend(' -- ', '');

        return view('returnorder.quotation_create', compact('customer', 'warehouse', 'quotation_date', 'quotation_number', 'product_services','store_id'));
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validator = \Validator::make(
            $request->all(),
            [
                'customer_id' => 'required',
                'warehouse_id' => 'required',
                'quotation_date' => 'required',
                'items' => 'required',
                // 'type' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }
        $customer = warehouse::where('name', $request->customer_id)->first();
        $warehouse = warehouse::where('name', $request->warehouse_id)->first();

        $quotations                 = new ReturnOrder();
        $quotations->quotation_id    = $this->quotationNumber();
        $quotations->customer_id      = $customer->id;
        $quotations->warehouse_id      = $warehouse->id;
        $quotations->quotation_date  = $request->quotation_date;
        $quotations->status         =  0;
        $quotations->category_id    =  0;
        $quotations->owned_by      = $warehouse->owned_by;
        $quotations->created_by     = \Auth::user()->creatorId();
        $quotations->save();

        $products = $request->items;

        for ($i = 0; $i < count($products); $i++) {
            $quotationItems              = new ReturnOrderProduct();
            $quotationItems->quotation_id    = $quotations->id;
            $quotationItems->product_id = $products[$i]['item'];
            $quotationItems->price      = $products[$i]['price'];
            $quotationItems->quantity   = $products[$i]['quantity'];
            $quotationItems->type   = $products[$i]['type'];
            $quotationItems->description       = $products[$i]['description'];
            // $quotationItems->discount        = $products[$i]['discount'];
            $quotationItems->save();
        }

        return redirect()->route('returnorder.index', $quotations->id)->with('success', __('ReturnOrder successfully created.'));
    }

    /**
     * Display the specified resource.
     */
    public function show($ids)
    {

        if (\Auth::user()->type == 'company') {
            try {
                $id = Crypt::decrypt($ids);
            } catch (\Throwable $th) {
                return redirect()->back()->with('error', __('ReturnOrder Not Found.'));
            }

            $id = Crypt::decrypt($ids);

            $quotation = ReturnOrder::find($id);

            if ($quotation->created_by == \Auth::user()->creatorId()) {
                $quotationPayment = PosPayment::where('pos_id', $quotation->id)->first();
                $customer = $quotation->customer;
                $iteams = $quotation->items;

                return view('returnorder.view', compact('quotation', 'customer', 'iteams', 'quotationPayment'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }

        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($ids)
    {

        $id   = Crypt::decrypt($ids);
        $quotation     = ReturnOrder::find($id);

        $customer = warehouse::where('id', $quotation->customer_id)->first();
        $warehouse = warehouse::where('id', $quotation->warehouse_id)->first();

        $warehouseProducts = WarehouseProduct::where('created_by', '=', \Auth::user()->creatorId())->where('quantity', '>', '0')->where('warehouse_id', $quotation->customer_id)->get()->pluck('product_id')->toArray();
        // dd($warehouseProducts);
        $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))->where('created_by', \Auth::user()->creatorId())->whereIn('id', $warehouseProducts)->where('type', '!=', 'service')->get()->pluck('name', 'id');
        $product_services->prepend(' -- ', '');
        // dd($warehouseProducts);
        $quotation_number = \Auth::user()->quotationNumberFormat($quotation->quotation_id);

        return view('returnorder.edit', compact('customer', 'product_services', 'warehouse', 'quotation_number', 'quotation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id, ReturnOrder $quotation)
    {
        $quotation     = ReturnOrder::find($id);

        if ($quotation->created_by == \Auth::user()->creatorId()) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'customer_id' => 'required',
                    'quotation_date' => 'required',
                    'items' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('returnorder.index')->with('error', $messages->first());
            }
            $customer = warehouse::where('name', $request->customer_id)->first();
            $warehouse = warehouse::where('name', $request->warehouse_id)->first();

            $quotation->customer_id      = $customer->id;
            $quotation->warehouse_id      = $warehouse->id;
            $quotation->quotation_date  = $request->quotation_date;
            $quotation->status         =  0;
            $quotation->category_id    =  0;
            // $quotation->created_by     = \Auth::user()->creatorId();
            $quotation->save();
            $products = $request->items;

            for ($i = 0; $i < count($products); $i++) {
                $quotationProduct = ReturnOrderProduct::find($products[$i]['id']);

                if ($quotationProduct == null) {
                    $quotationProduct             = new ReturnOrderProduct();
                    $quotationProduct->quotation_id    = $quotation->id;
                }
                if (isset($products[$i]['item'])) {
                    $quotationProduct->product_id = $products[$i]['item'];
                }

                $quotationProduct->quantity    = $products[$i]['quantity'];
                // $quotationProduct->tax         = $products[$i]['tax'];
                // $quotationProduct->discount    = $products[$i]['discount'];
                $quotationProduct->price       = $products[$i]['price'];
                $quotationProduct->type      = $products[$i]['type'];
                $quotationProduct->description = $products[$i]['description'];
                $quotationProduct->save();
            }

            return redirect()->route('returnorder.index')->with('success', __('ReturnOrder successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReturnOrder $quotation,$id)
    {
        $quotation     = ReturnOrder::find($id);
        if ($quotation->created_by == \Auth::user()->creatorId()) {


            $quotation->delete();
            ReturnOrderProduct::where('quotation_id', '=', $quotation->id)->delete();


            return redirect()->route('returnorder.index')->with('success', __('ReturnOrder successfully deleted.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function quotationNumber()
    {
        $latest = ReturnOrder::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->quotation_id + 1;
    }

    public function product(Request $request)
    {
        // dd($request->all());
        // $product_id = $request->input('product_id');
        // $store_id = Invoice::where('to_store',$request->store_id)->get();
        // dd($store_id);
        // $product = InvoiceProduct::where('product_id', $request->product_id)
        // ->where('invoice_id', $request->store_id)
        // ->get();
        // if ($product) {
        //     $salePrice = $product[0]['price'];
        //     // dd($salePrice);
        //     $quantity = 1;
        //     $totalAmount = $salePrice * $quantity;
        //     $totalAmount = $salePrice * $quantity;
        //     // if ($product) {
        //     //     $productquantity = $product->getQuantity();
        //     // }
        //     return response()->json([
        //         'success' => true,
        //         'price' => $product,
        //         // 'quantity' => $quantity,
        //         // 'totalAmount' => $totalAmount
        //     ]);
        // }
        $data['pro']  = ProductService::find($request->product_id);
        $data['product'] = $product = InvoiceProduct::join('invoices', 'invoices.id', '=', 'invoice_products.invoice_id')
        ->where('invoices.to_store', $request->store_id)
        ->where('invoice_products.product_id', $request->product_id)
        ->first();
        // dd($request->all(),$product);
        // $data['product']     = $product = InvoiceProduct::where('product_id', $request->product_id)
        // ->where('invoice_id', $request->store_id)
        // ->get();
        // $data['unit']        = (!empty($product->unit())) ? $product->unit()->name : '';
        // $data['taxRate']     = $taxRate = !empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0;
        // $data['taxes']       = !empty($product->tax_id) ? $product->tax($product->tax_id) : 0;
        $salePrice           = $product->price ?? 0;
        $quantity            = 1;
        // $quantity            = !empty($product->quantity) ? $product->quantity($product->quantity) : 0;
        // $taxPrice            = ($taxRate / 100) * ($salePrice * $quantity);
        $data['totalAmount'] = ($salePrice * $quantity);
// dd($data);
        return json_encode($data);
    }

    public function productDestroy(Request $request)
    {
        ReturnOrderProduct::where('id', '=', $request->id)->delete();

        return redirect()->back()->with('success', __('ReturnOrder product successfully deleted.'));
    }

    public function items(Request $request)
    {
        $items = ReturnOrderProduct::where('quotation_id', $request->quotation_id)->where('product_id', $request->product_id)->first();
        return json_encode($items);
    }

    public function productQuantity(Request $request)
    {
        // dd($request->all());
        $customer = WarehouseProduct::
        // find($request->customer_id);
                        // ->
                        where('warehouse_id',$request->store_id)
                        ->where('product_id', $request->item_id)
                        ->first();
        // $product = ProductService::find($request->item_id);
        // $product = ProductService::where('quantity',$request->quantity);
        // dd($product);
        // $productquantity = 0;

        // if ($customer) {
        //     // $productquantity = $product->getQuantity();
        //     $productquantity = $customer->quantity;
        // }
        // dd($product);
    return response()->json([
        'success' => true,
        'data' => $customer,
        // 'product' => $product,
    ]);

        // return json_encode($customer);
    }

    public function approved(Request $request)
    {
        if(!empty($request->checkedRowsData))
        {
            foreach($request->checkedRowsData as $data){
                $quotationProduct = ReturnOrderProduct::where('id',$data[0])->where('quotation_id',$request->order)->first();
                $quotationProduct->status = 'approved';
                $quotationProduct->save();
            }
        }
        return response()->json([
            'success' => true,
            'price' => 'status updated',
            // 'quantity' => $quantity,
            // 'totalAmount' => $totalAmount
        ]);
    }

    public function previewReturnOrder($template, $color)
    {
        $objUser = \Auth::user();
        $settings = Utility::settings();

        $quotation = new ReturnOrder();
        $quotationPayment = new posPayment();
        $quotationPayment->amount = 360;
        $quotationPayment->discount = 100;

        $customer = new \stdClass();
        $customer->email = '<Email>';
        $customer->shipping_name = '<Customer Name>';
        $customer->shipping_country = '<Country>';
        $customer->shipping_state = '<State>';
        $customer->shipping_city = '<City>';
        $customer->shipping_phone = '<Customer Phone Number>';
        $customer->shipping_zip = '<Zip>';
        $customer->shipping_address = '<Address>';
        $customer->billing_name = '<Customer Name>';
        $customer->billing_country = '<Country>';
        $customer->billing_state = '<State>';
        $customer->billing_city = '<City>';
        $customer->billing_phone = '<Customer Phone Number>';
        $customer->billing_zip = '<Zip>';
        $customer->billing_address = '<Address>';

        $totalTaxPrice = 0;
        $taxesData = [];
        $items = [];
        for ($i = 1; $i <= 3; $i++) {
            $item = new \stdClass();
            $item->name = 'Item ' . $i;
            $item->quantity = 1;
            $item->tax = 5;
            $item->discount = 50;
            $item->price = 100;

            $taxes = [
                'Tax 1',
                'Tax 2',
            ];

            $itemTaxes = [];
            foreach ($taxes as $k => $tax) {
                $taxPrice = 10;
                $totalTaxPrice += $taxPrice;
                $itemTax['name'] = 'Tax ' . $k;
                $itemTax['rate'] = '10 %';
                $itemTax['price'] = '$10';
                $itemTaxes[] = $itemTax;
                if (array_key_exists('Tax ' . $k, $taxesData)) {
                    $taxesData['Tax ' . $k] = $taxesData['Tax 1'] + $taxPrice;
                } else {
                    $taxesData['Tax ' . $k] = $taxPrice;
                }
            }
            $item->itemTax = $itemTaxes;
            $items[] = $item;
        }

        $quotation->quotation_id = 1;

        $quotation->issue_date = date('Y-m-d H:i:s');
        $quotation->itemData = $items;

        $quotation->totalTaxPrice = 60;
        $quotation->totalQuantity = 3;
        $quotation->totalRate = 300;
        $quotation->totalDiscount = 10;
        $quotation->taxesData = $taxesData;
        $quotation->created_by = $objUser->creatorId();

        $preview = 1;
        $color = '#' . $color;
        $font_color = Utility::getFontColor($color);

        $logo = asset(Storage::url('uploads/logo/'));

        $company_logo = Utility::getValByName('company_logo_dark');
        $settings_data = \App\Models\Utility::settingsById($quotation->created_by);
        $quotation_logo = isset($settings_data['quotation_logo']) ? $settings_data['quotation_logo'] : '';

        if (isset($quotation_logo) && !empty($quotation_logo)) {
            $img = Utility::get_file('quotation_logo/') . $quotation_logo;
        } else {
            $img = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }

        return view('returnorder.templates.' . $template, compact('quotation', 'preview', 'color', 'img', 'settings', 'customer', 'font_color', 'quotationPayment'));
    }

    public function saveReturnOrderTemplateSettings(Request $request)
    {

        $post = $request->all();
        unset($post['_token']);

        if (isset($post['quotation_template']) && (!isset($post['quotation_color']) || empty($post['quotation_color']))) {
            $post['quotation_color'] = "ffffff";
        }

        if ($request->quotation_logo) {
            $dir = 'quotation_logo/';
            $quotation_logo = \Auth::user()->id . '_quotation_logo.png';
            $validation = [
                'mimes:' . 'png',
                'max:' . '20480',
            ];
            $path = Utility::upload_file($request, 'quotation_logo', $quotation_logo, $dir, $validation);
            if ($path['flag'] == 0) {
                return redirect()->back()->with('error', __($path['msg']));
            }
            $post['quotation_logo'] = $quotation_logo;
        }

        foreach ($post as $key => $data) {
            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ',
                [
                    $data,
                    $key,
                    \Auth::user()->creatorId(),
                ]
            );
        }

        return redirect()->back()->with('success', __('ReturnOrder Setting updated successfully'));
    }

    public function printView(Request $request)
    {

        $sess = session()->get('pos');

        $user = Auth::user();
        $settings = Utility::settings();

        $customer = Customer::where('name', '=', $request->vc_name)->where('created_by', $user->creatorId())->first();
        $warehouse = warehouse::where('id', '=', $request->warehouse_name)->where('created_by', $user->creatorId())->first();

        $details = [
            'pos_id' => $user->quotationNumberFormat($this->quotationNumber()),
            'customer' => $customer != null ? $customer->toArray() : [],
            'warehouse' => $warehouse != null ? $warehouse->toArray() : [],
            'user' => $user != null ? $user->toArray() : [],
            'date' => date('Y-m-d'),
            'pay' => 'show',
        ];

        if (!empty($details['customer'])) {
            $warehousedetails = '<h7 class="text-dark">' . ucfirst($details['warehouse']['name']) . '</p></h7>';
            $details['customer']['billing_state'] = $details['customer']['billing_state'] != '' ? ", " . $details['customer']['billing_state'] : '';
            $details['customer']['shipping_state'] = $details['customer']['shipping_state'] != '' ? ", " . $details['customer']['shipping_state'] : '';
            $customerdetails = '<h6 class="text-dark">' . ucfirst($details['customer']['name']) . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['billing_phone'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['billing_address'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['billing_city'] . $details['customer']['billing_state'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['billing_country'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['billing_zip'] . '</p></h6>';
            $shippdetails = '<h6 class="text-dark"><b>' . ucfirst($details['customer']['name']) . '</b>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['shipping_phone'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['shipping_address'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['shipping_city'] . $details['customer']['shipping_state'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['shipping_country'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $details['customer']['shipping_zip'] . '</p></h6>';
        } else {
            $customerdetails = '<h2 class="h6"><b>' . __('Walk-in Customer') . '</b><h2>';
            $warehousedetails = '<h7 class="text-dark">' . ucfirst($details['warehouse']['name']) . '</p></h7>';
            $shippdetails = '-';
        }

        $settings['company_telephone'] = $settings['company_telephone'] != '' ? ", " . $settings['company_telephone'] : '';
        $settings['company_state'] = $settings['company_state'] != '' ? ", " . $settings['company_state'] : '';

        $userdetails = '<h6 class="text-dark"><b>' . ucfirst($details['user']['name']) . ' </b> <h2  class="font-weight-normal">' . '<p class="m-0 font-weight-normal">' . $settings['company_name'] . $settings['company_telephone'] . '</p>' . '<p class="m-0 font-weight-normal">' . $settings['company_address'] . '</p>' . '<p class="m-0 h6 font-weight-normal">' . $settings['company_city'] . $settings['company_state'] . '</p>' . '<p class="m-0 font-weight-normal">' . $settings['company_country'] . '</p>' . '<p class="m-0 font-weight-normal">' . $settings['company_zipcode'] . '</p></h2>';

        $details['customer']['details'] = $customerdetails;
        $details['warehouse']['details'] = $warehousedetails;
        //
        $details['customer']['shippdetails'] = $shippdetails;

        $details['user']['details'] = $userdetails;

        $mainsubtotal = 0;
        $sales = [];

        foreach ($sess as $key => $value) {

            $subtotal = $value['price'] * $value['quantity'];
            $tax = ($subtotal * $value['tax']) / 100;
            $sales['data'][$key]['name'] = $value['name'];
            $sales['data'][$key]['quantity'] = $value['quantity'];
            $sales['data'][$key]['price'] = Auth::user()->priceFormat($value['price']);
            $sales['data'][$key]['tax'] = $value['tax'] . '%';
            $sales['data'][$key]['product_tax'] = $value['product_tax'];
            $sales['data'][$key]['tax_amount'] = Auth::user()->priceFormat($tax);
            $sales['data'][$key]['subtotal'] = Auth::user()->priceFormat($value['subtotal']);
            $mainsubtotal += $value['subtotal'];
        }

        $discount = !empty($request->discount) ? $request->discount : 0;
        $sales['discount'] = Auth::user()->priceFormat($discount);
        $total = $mainsubtotal - $discount;
        $sales['sub_total'] = Auth::user()->priceFormat($mainsubtotal);
        $sales['total'] = Auth::user()->priceFormat($total);

        //for barcode

        $productServices = ProductService::where('created_by', '=', \Auth::user()->creatorId())->get();
        $barcode = [
            'barcodeType' => Auth::user()->barcodeType(),
            'barcodeFormat' => Auth::user()->barcodeFormat(),
        ];

        return view('returnorder.printview', compact('details', 'sales', 'customer', 'productServices', 'barcode'));
    }

    public function quotation($quotation_Id)
    {
        $settings = Utility::settings();
        $quotationId = Crypt::decrypt($quotation_Id);
        $quotation = ReturnOrder::where('id', $quotationId)->first();


        $data = \DB::table('settings');
        $data = $data->where('created_by', '=', $quotation->created_by);
        $data1 = $data->get();

        foreach ($data1 as $row) {
            $settings[$row->name] = $row->value;
        }

        $customer = $quotation->customer;

        $totalTaxPrice = 0;
        $totalQuantity = 0;
        $totalRate = 0;
        $totalDiscount = 0;
        $taxesData = [];
        $items = [];

        foreach ($quotation->items as $product) {

            $item = new \stdClass();
            $item->name = !empty($product->product()) ? $product->product()->name : '';
            $item->quantity = $product->quantity;
            $item->tax = $product->tax;
            $item->discount = $product->discount;
            $item->price = $product->price;
            $item->description = $product->description;
            $totalQuantity += $item->quantity;
            $totalRate += $item->price;
            $totalDiscount += $item->discount;
            $taxes = Utility::tax($product->tax);
            $itemTaxes = [];
            if (!empty($item->tax)) {
                foreach ($taxes as $tax) {
                    $taxPrice = Utility::taxRate($tax->rate, $item->price, $item->quantity);
                    $totalTaxPrice += $taxPrice;

                    $itemTax['name'] = $tax->name;
                    $itemTax['rate'] = $tax->rate . '%';
                    $itemTax['price'] = Utility::priceFormat($settings, $taxPrice);
                    $itemTaxes[] = $itemTax;

                    if (array_key_exists($tax->name, $taxesData)) {
                        $taxesData[$tax->name] = $taxesData[$tax->name] + $taxPrice;
                    } else {
                        $taxesData[$tax->name] = $taxPrice;
                    }
                }

                $item->itemTax = $itemTaxes;
            } else {
                $item->itemTax = [];
            }
            $items[] = $item;
        }

        $quotation->itemData = $items;
        $quotation->totalTaxPrice = $totalTaxPrice;
        $quotation->totalQuantity = $totalQuantity;
        $quotation->totalRate = $totalRate;
        $quotation->totalDiscount = $totalDiscount;
        $quotation->taxesData = $taxesData;

        $logo = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $quotation_logo = Utility::getValByName('quotation_logo');
        if (isset($quotation_logo) && !empty($quotation_logo)) {
            $img = Utility::get_file('quotation_logo/') . $quotation_logo;
        } else {
            $img = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));
        }

        if ($quotation) {
            $color = '#' . $settings['quotation_color'];
            $font_color = Utility::getFontColor($color);

            return view('returnorder.templates.' . $settings['quotation_template'], compact('quotation', 'color', 'settings', 'customer', 'img', 'font_color'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function report(Request $request)
    {
        $customers = warehouse::get()->pluck('name', 'id');
        $customers->prepend('Select Store', '');
        if (\Auth::user()->type == 'company') {
            $query = ReturnOrder::where('created_by', '=', \Auth::user()->creatorId());
        }else{
            $query = ReturnOrder::where('owned_by', '=', \Auth::user()->ownedId());
        }
        $store_rep = 'All Store';
        if (!empty($request->store)) {
            $query->where('warehouse_id',  $request->store);
            $store_rep =$customers[$request->store];
        }
        if (!empty($request->start_date)) {
            $query->whereDate('quotation_date', '>', $request->start_date);
        }
        if (!empty($request->end_date)) {
            $query->whereDate('quotation_date', '<', $request->end_date);
        }
        if(empty($request->start_date) || empty($request->end_date)){
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";

            $request->merge(['start_date' => $dateFrom]);
            $request->merge(['end_date' => $dateTo]);
                        $query->whereBetween('quotation_date', [$dateFrom, $dateTo]);
        }
        // if (!empty($request->status)) {
        //     $query->where('status', '=', $request->status);
        // }
        $purchases = $query->get();

        if ($request->has('export') && $request->export == 'excel') {
            $request->merge(['date_from' => $request->start_date]);
            $request->merge(['date_to' => $request->end_date]);
            $data = [
                'customers' => $customers,
                'purchases' => $purchases,
                'request' => $request,
            ];

            return Excel::download(new ReturnOrderExport($data, $request->all()), 'returnorder_report.xlsx');
        }

        if ($request->has('export') && $request->export == 'pdf') {
            $request->merge(['date_from' => $request->start_date]);
            $request->merge(['date_to' => $request->end_date]);
            $data = [
                'customers' => $customers,
                'purchases' => $purchases,
                'request' => $request,
            ];

            return Excel::download(new ReturnOrderExport($data, $request->all()), 'returnorder_report.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        // if ($request->has('print') && $request->print == 'pdf') {
        //     $report_name = "Return Order Report";
        //     $pdf = new Dompdf();
        //     $html = view('returnorder.returnorder_report_pdf', compact(
        //     'purchases','customers','request'
        //     ))->render();
        //     $headerHtml = view('invoice.report.pdf.header', compact('request','report_name','store_rep'))->render();
        //     $footerHtml = view('invoice.report.pdf.footer')->render();
        //     $html = '<html><head>
        //     <style>
        //         @page {
        //             margin-top: 100px;
        //             margin-bottom: 100px;
        //         }
        //         .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
        //         .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
        //     </style>
        //     </head><body>
        //     <div class="header">' . $headerHtml . '</div>
        //     <div class="footer">' . $footerHtml . '</div>
        //     ' . $html . '
        //     </body></html>';
        //     $options = new Options();
        //     $options->set('isHtml5ParserEnabled', true);
        //     $options->set('isRemoteEnabled', true);
        //     $dompdf = new Dompdf($options);
        //     $dompdf->loadHtml($html);
        //     $dompdf->setPaper('A4', 'potrait');
        //     $dompdf->render();

        //     return $dompdf->stream('returnorder_report.pdf');
        // }

        return view('returnorder.returnorder_report', compact('purchases','customers','request'));


    }
    
}
