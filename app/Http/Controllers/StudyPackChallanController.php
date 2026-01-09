<?php

namespace App\Http\Controllers;

use App\Exports\StudentReceiptExport;
use App\Models\BankAccount;
use App\Models\ChallanHead;
use App\Models\Classes;
use App\Models\ClassWiseFee;
use App\Models\Customer;
use App\Models\FeeHead;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\ProductService;
use App\Models\ProductServiceCategory;
use App\Models\Session;
use App\Models\StudentEnrollments;
use App\Models\StudentRegistration;
use App\Models\StudyPackChallans;
use App\Models\StudyPackChallanItems;
use App\Models\StudyPackItem;
use App\Models\StudypackPayment;
use App\Models\StudypackReceipts;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utility;
use App\Models\Vender;
use App\Models\warehouse;
use Dompdf\Options;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudyPackChallanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
        }
        $session = Session::get()->pluck('year', 'id');
        $session->prepend('Select Session', '');
        $class = array(
            '' => 'Select Class',
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
        $stdy_pack = [];
        $studypacks = StudyPackChallans::get();
        return view('students.studypackChallan.index', compact('studypacks', 'branches', 'class', 'stdy_pack', 'session'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (\Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(\Auth::user()->name, \Auth::user()->id);
            $branches->prepend('Select Branch', '');

        } else {
            $branches = User::where('id', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
        }
        return view('students.studypackChallan.challanform', compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        \DB::beginTransaction();

        try {
            $students = [];
            $issueDate = Carbon::now()->toDateString();
            $dueDate = Carbon::now()->addWeek()->toDateString();
            $challanDate = Carbon::parse($request->challan_date);
            $year = $challanDate->year;
            $month = $challanDate->month;
            $branchId = $request->branches;

            $classId = $request->class;
            if ($request->student == 'all') {
                $students = StudentRegistration::where('branch', $branchId)
                    ->where('class_id', $classId)
                    ->pluck('id');
            } else {
                $students[] = $request->student;
            }
            foreach ($students as $studentId) {
                $existingChallan = StudyPackChallans::where('student_id', $studentId)
                    ->where('owned_by', $branchId)
                    ->where('class_id', $classId)
                    ->whereMonth('challan_date', $month)
                    ->whereYear('challan_date', $year)
                    ->first();
                $studentdata = StudentRegistration::where('id', $studentId)->first();
                if (!$existingChallan) {
                    $studypackchallan = new StudyPackChallans();
                    $studypackchallan->student_id = $studentId;
                    $studypackchallan->studypack_id = $request->Studypack;
                    $studypackchallan->challanNo = mt_rand(100000, 999999);
                    $studypackchallan->fee_month = $request->challan_date;
                    $studypackchallan->branch_id = $branchId;
                    $studypackchallan->class_id = $classId;
                    $studypackchallan->challan_type = "Studypack";
                    $studypackchallan->year = $request->challan_date;
                    $studypackchallan->challan_date = $challanDate;
                    $studypackchallan->issue_date = $issueDate;
                    $studypackchallan->due_date = $dueDate;
                    $studypackchallan->status = 'Assigned';
                    $studypackchallan->owned_by = $studentdata->owned_by;
                    $studypackchallan->created_by = $studentdata->created_by;
                    $studypackchallan->save();
                }
                $studypackitems = StudyPackItem::where('study_pack_id', $request->Studypack)->get();
                $newitems = $studypackitems;
                $i = 0;
                $total_amnt = 0;
                foreach ($studypackitems as $item) {
                    if ($studypackchallan) {
                        $studypackchallanitems = new StudyPackChallanItems();
                        $studypackchallanitems->challan_id = $studypackchallan->id;
                        $studypackchallanitems->studypack_id = $request->Studypack;
                        $studypackchallanitems->product_id = $item->product_id;
                        $studypackchallanitems->qty = $item->quantity;
                        $studypackchallanitems->tax = $item->tax;
                        $studypackchallanitems->discount = $item->discount;
                        $studypackchallanitems->price = $item->price;
                        $total_amnt += $item->price;
                        $studypackchallanitems->owned_by = $studentdata->owned_by;
                        $studypackchallanitems->created_by = $studentdata->created_by;
                        $studypackchallanitems->save();
                        $newitems[$i]['prod_id'] = $studypackchallanitems->id;
                        $i++;
                    }
                }
                $data['id'] = $studypackchallan->id;
                $data['no'] = $studypackchallan->studypack_id;
                $data['user_id'] = $studypackchallan->student_id;
                $data['date'] = $studypackchallan->fee_month;
                $data['reference'] = $studypackchallan->fee_month;
                $data['category'] = 'Studypack';
                $data['owned_by'] = $studypackchallan->owned_by;
                $data['created_by'] = $studypackchallan->created_by;
                $data['items'] = $newitems;
                $dataret = Utility::studypackjv($data);
                $studypackchallan->update(['total_amount' => $total_amnt, 'voucher_id' => $dataret]);
            }
            \DB::commit();
            return back()->with('success', 'StudyPack Challan Created Successfully !!');
        } catch (\Exception $e) {
            \DB::rollback();
            dd($e);
            return response()->json(['error' => true, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    public function download($id)
    {
        try {
            $challan = StudyPackChallans::findOrFail($id);
            $pathToFile = public_path('temp_pdfs/studypack_challan.pdf');
            return response()->download($pathToFile);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to download challan: ' . $e->getMessage());
        }
    }

    public function print($id)
    {

        try {
            $challan = StudyPackChallans::findOrFail($id);
            $html = view('students.studypackChallan.challanpdf', compact('challan'))->render();
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A3', 'landscape');
            $dompdf->render();

            return $dompdf->stream('studypack_challan.pdf', ['Attachment' => false]);
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->with('error', 'Failed to generate print view: ' . $e->getMessage());
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        // dd($request->all());
        $challan = StudyPackChallans::with('student')->where('id', $id)->first();
        if (!$challan) {
            abort(404, 'Challan not found');
        }
        // If type is set, generate PDF using Dompdf
        if ($request->has('type') && in_array($request->type, ['print', 'download'])) {
            $html = view('students.studypackChallan.challanpdf', compact('challan'))->render();
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $pdfContent = $dompdf->output();
            $filename = 'studypack_challan.pdf';
            if ($request->type === 'print') {
                return $dompdf->stream($filename, ['Attachment' => false]);
            } else {
                return $dompdf->stream($filename);
            }
        }
        // dd($challan);
        return view('students.studypackChallan.print', compact('challan'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $purchase = StudyPackChallans::find($id);
        // dd($purchase);
        $challan = StudyPackChallans::with('student')->where('id', $id)->first();
        $product_services = ProductService::select(\DB::raw('CONCAT(sku, " - ", name) AS name, id'))
            ->where('created_by', \Auth::user()->creatorId())->where('type', '!=', 'service')->get()->pluck('name', 'id');
        return view('students.studypackChallan.edit', compact('product_services', 'purchase'));
        // return view('students.studypackChallan.edit', compact('challan', 'invoice', 'items'));
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
        // dd($request->all());
        \DB::beginTransaction();
        try {
            $challan = StudyPackChallans::findOrFail($id);

            if (!empty($request->deleted_items)) {
                foreach ($request->deleted_items as $item) {
                    // Delete challan item
                    StudyPackChallanItems::where('id', $item)->delete();
                    // Also delete corresponding JournalItem
                    JournalItem::where('entry_id', $item)->delete();
                }
            }

            $allChallanItems = [];
            if (!empty($request->items)) {
                foreach ($request->items as $item) {
                    if (isset($item['id']) && $item['id']) {
                        // Update existing item
                        $challanitem = StudyPackChallanItems::find($item['id']);
                        if ($challanitem) {
                            $challanitem->qty = $item['quantity'];
                            $challanitem->price = $item['price'];
                            $challanitem->save();
                            // Update corresponding JournalItem (credit, description, etc.)
                            $journalItem = JournalItem::where('entry_id', $challanitem->id)->where('types', 'Studypack')->first();
                            if ($journalItem) {
                                $product = ProductService::find($challanitem->product_id);
                                $itemPrice = $challanitem->qty * $challanitem->price;
                                $journalItem->credit = $itemPrice;
                                $journalItem->description = $product ? $product->name : $journalItem->description;
                                $journalItem->save();
                                // Update tax JournalItem if exists
                                if ($product && $product->tax_id) {
                                    $taxes = \App\Models\Tax::where('id', $product->tax_id)->first();
                                    if ($taxes) {
                                        $itemTax = ($itemPrice * $taxes->rate) / 100;
                                        $taxJournalItem = JournalItem::where('journal', $journalItem->journal)
                                            ->where('types', 'Studypack')
                                            ->where('description', 'Tax on ' . $product->id)
                                            ->where('head_ids', $product->id)
                                            ->first();
                                        if ($taxJournalItem) {
                                            $taxJournalItem->credit = $itemTax;
                                            $taxJournalItem->save();
                                        }
                                    }
                                }
                            }
                            $allChallanItems[] = [
                                'prod_id' => $challanitem->id,
                                'product_id' => $challanitem->product_id,
                                'quantity' => $challanitem->qty,
                                'price' => $challanitem->price
                            ];
                        }
                    } else {
                        // Add new item
                        $challanitem = new StudyPackChallanItems();
                        $challanitem->challan_id = $challan->id;
                        $challanitem->studypack_id = $challan->studypack_id;
                        $challanitem->product_id = $item['item'];
                        $challanitem->qty = $item['quantity'];
                        $challanitem->price = $item['price'];
                        $challanitem->save();
                        $allChallanItems[] = [
                            'prod_id' => $challanitem->id,
                            'product_id' => $challanitem->product_id,
                            'quantity' => $challanitem->qty,
                            'price' => $challanitem->price
                        ];
                    }
                }
            }
            $challan->total_amount = $request->total_amount;
            $challan->save();

            // Update or create JV for all items
            $journal = JournalEntry::where('reference_id', $challan->id)
                ->where('voucher_type', 'JV')
                ->first();
            if ($journal) {
                // Update JV header info
                $journal->date = $challan->fee_month;
                $journal->reference = $challan->fee_month;
                $journal->description = 'Studypack no : ' . $challan->studypack_id;
                $journal->save();

                // Remove all receivable and tax JournalItems (to recalculate)
                JournalItem::where('journal', $journal->id)
                    ->where(function($q){
                        $q->Where('description', 'like', '% Studypack Receivables %');
                    })->delete();

                $receivable = 0;
                $totalTax = 0;
                foreach ($allChallanItems as $item) {
                    $product = ProductService::where('id', $item['product_id'])->first();
                    if (!$product) continue;
                    $itemPrice = ($item['quantity'] * $item['price']);
                    $receivable += $itemPrice;
                    $journalItem = JournalItem::where('journal', $journal->id)
                        ->where('entry_id', $item['prod_id'])
                        ->where('types', 'Studypack')
                        ->first();
                    if (!$journalItem) {
                        $journalItem = new JournalItem();
                        $journalItem->journal = $journal->id;
                        $journalItem->account = @$product->sale_chartaccount_id;
                        $journalItem->entry_id = @$item['prod_id'];
                        $journalItem->types = 'Studypack';
                        $journalItem->description = $product->name;
                        $journalItem->head_ids = $product->id;
                        $journalItem->branch_id = $challan->owned_by;
                        $journalItem->debit = 0;
                        $journalItem->credit = $itemPrice;
                        $journalItem->save();
                    }
    
                } 
                $types = \App\Models\ChartOfAccountType::where('created_by', $challan->created_by)
                    ->where('name', 'Assets')
                    ->first();
                if ($types) {
                    $sub_type = \App\Models\ChartOfAccountSubType::where('type', $types->id)
                        ->where('name', 'Current Asset')
                        ->first();
                    $account = \App\Models\ChartOfAccount::where('type', $types->id)
                        ->where('sub_type', $sub_type->id)
                        ->where('name', 'Studypack Receivables')
                        ->first();
                    if (!$account) {
                        $account = new \App\Models\ChartOfAccount();
                        $account->name = 'Studypack Receivables';
                        $account->code = '0';
                        $account->type = $types->id;
                        $account->sub_type = $sub_type->id;
                        $account->description = 'Studypack Receivables';
                        $account->is_enabled = 1;
                        $account->created_by = $challan->created_by;
                        $account->save();
                    }
                    // Add receivable journal item
                    $journalItem = new JournalItem();
                    $journalItem->journal = $journal->id;
                    $journalItem->account = @$account->id;
                    $journalItem->description = 'Account Receivable: Roll no '.$challan->roll_no.' Challan no '.$challan->challanNo.' - '.@$challan->student->stdname.' - '.@$challan->fee_month.' - '.@$challan->student->branches->name;
                    $journalItem->debit = $receivable + $totalTax;
                    $journalItem->branch_id = $challan->owned_by;
                    $journalItem->save();
                }
            } else {
                // If no JV exists, create as before
                $data['id'] = $challan->id;
                $data['no'] = $challan->studypack_id;
                $data['date'] = $challan->fee_month;
                $data['reference'] = $challan->fee_month;
                $data['category'] = 'Studypack';
                $data['owned_by'] = $challan->owned_by;
                $data['created_by'] = $challan->created_by;
                $data['items'] = $allChallanItems;
                $dataret = Utility::studypackjv($data);
            }

            \DB::commit();
            return redirect()->route('studypackchallan.index')->with('success', 'Challan updated successfully.');
        } catch (\Exception $e) {
            \DB::rollback();
            dd($e); // Only for debugging. Remove this in production.
            return redirect()->back()->with('error', $e->getMessage());
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
        //
    }

    public function deleteChallanItems(Request $request)
    {
        $challanId = $request->input('challan_id');
        $productIds = $request->input('product_ids');
        StudyPackChallanItems::where('challan_id', $challanId)
            ->whereIn('product_id', $productIds)
            ->delete();
        return response()->json(['success' => true]);
    }

    public function payment(Request $request, $id)
    {
        if (\Auth::user()->can('create payment invoice')) {
            $invoice = StudyPackChallans::where('id', $id)->first();
            if (\Auth::user()->type == 'company') {
                $customers = Customer::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $categories = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
                $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            } else {
                $customers = Customer::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $categories = ProductServiceCategory::where('owned_by', '=', \Auth::user()->ownedId())->get()->pluck('name', 'id');
                $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }
            // dd($invoice);
            return view('students.studypackChallan.payment', compact('customers', 'categories', 'accounts', 'invoice'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function addpayment(Request $request, $id)
    {
        if (\Auth::user()->can('create payment invoice')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'date' => 'required',
                    'amount' => 'required',
                    'account_from' => 'required',
                    'account_to' => 'required',
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $invoice = StudyPackChallans::where('id', $id)->first();
            DB::beginTransaction();
            $data = [];
            try {
                $invoicePayment = new StudypackPayment();
                $invoicePayment->challan_id = $id;
                $invoicePayment->date = $request->date;
                $invoicePayment->amount = $request->amount;
                $invoicePayment->payment_method = 0;
                $invoicePayment->reference = $request->reference;
                $invoicePayment->description = $request->description;
                $invoicePayment->owned_by = $invoice->owned_by;
                $invoicePayment->created_by = $invoice->created_by;

                if (!empty($request->add_receipt)) {
                    //storage limit
                    $image_size = $request->file('add_receipt')->getSize();
                    $result = Utility::updateStorageLimit(\Auth::user()->creatorId(), $image_size);
                    if ($result == 1) {
                        $fileName = time() . "_" . $request->add_receipt->getClientOriginalName();
                        $request->add_receipt->storeAs('uploads/payment', $fileName);
                        $invoicePayment->add_receipt = $fileName;
                    }
                }

                $invoicePayment->save();
                Transaction::addTransaction($invoicePayment);
                Utility::bankAccountBalance($request->account_id, $request->amount, 'credit');

                $bankAccount = BankAccount::find($request->account_id);
                $data['id'] = $id;
                $data['date'] = $invoicePayment->date;
                $data['reference'] = $invoicePayment->reference;
                $data['description'] = $invoicePayment->description;
                $data['amount'] = $invoicePayment->amount;
                $data['category'] = 'Studypack';
                $data['user_id'] = $invoice->student_id;
                $data['user_type'] = 'Student';
                $data['owned_by'] = $invoice->owned_by;
                $data['created_by'] = $invoice->created_by;
                $data['account_id'] = $bankAccount->id;
                $dataret = Utility::strv_entry($data);
                $invoicePayment->update(['voucher_id' => $dataret]);
                DB::commit();
                return redirect()->back()->with('success', __('Payment successfully added.'));
            } catch (\Exception $e) {
                DB::rollback();
                dd($e);
                return redirect()->back()->with('error', $e);
            }
        }
    }

     public function product(Request $request)
    {

        $data['product'] = $product = ProductService::find($request->product_id);
        $data['unit'] = !empty($product->unit()) ? $product->unit()->name : '';
        $data['taxRate'] = $taxRate = !empty($product->tax_id) ? $product->taxRate($product->tax_id) : 0;
        $data['taxes'] = !empty($product->tax_id) ? $product->tax($product->tax_id) : 0;
        $salePrice = $product->purchase_price;
        $quantity = 1;
        $taxPrice = ($taxRate / 100) * ($salePrice * $quantity);
        $data['totalAmount'] = ($salePrice * $quantity);

        return json_encode($data);
    }

    public function items(Request $request)
    {
        $items = StudyPackChallanItems::where('challan_id', $request->purchase_id)->where('product_id', $request->product_id)->first();

        return json_encode($items);

    }

    public function Studypackreceipts(Request $request)
    {
        // dd('yes');
        $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
            ->where('created_by', \Auth::user()->creatorId())
            ->get()
            ->pluck('name', 'id');

        $query = StudypackReceipts::with('challan');

        if (\Auth::user()->type == 'company') {
            $query->where(function ($q) {
                $q->where('created_by', \Auth::user()->creatorId())
                    ->orWhere('received_by', \Auth::user()->id);
            });
        } else {
            $query->where(function ($q) {
                $q->where('owned_by', \Auth::user()->ownedId())
                    ->orWhere('received_by', \Auth::user()->id);
            });
        }

        if (!empty($request->date)) {
            $query->whereDate('recipt_date', $request->date);
        } else {
            $query->whereDate('recipt_date', date("Y-m-d"));
        }

        $recipts = $query->get();

        if ($request->has('export') && $request->export == 'excel') {
            return Excel::download(new StudentReceiptExport($recipts, $request->all()), 'student_receipt.xlsx');
        }
        if ($request->has('export') && $request->export == 'pdf') {
            return Excel::download(new StudentReceiptExport($recipts, $request->all()), 'student_receipt.pdf', \Maatwebsite\Excel\Excel::MPDF);
        }

        return view('students.studypackChallan.receipts', compact('accounts', 'recipts'));
    }
    public function challandata_for_studypackreceipt(Request $request)
    {
        try{
            $challandata = StudyPackChallans::with(['student', 'items','items.product'])->where('challanNo', $request->challan_no)->first();
            // dd($challandata);
            if (!$challandata) {
                return response()->json(['error' => 'Challan not found.'], 404);
            }

            if ($challandata && $challandata->items) {
                $headsData = [];
                foreach ($challandata->items as $head) {
                    // dd($head);
                    // Safely cast to float to prevent non-numeric issues
                    $price = (float) ($head->price ?? 0);
                    $concession = (float) ($head->discount ?? 0);
                    $amount = ($price * (int) ($head->qty ?? 1)) - $concession - $head->paid;

                    if ($amount != 0) {
                        $headsData[] = [
                            'head_id' => $head->id ?? '',
                            'head_name' => $head->product->name ?? 'Unknown Product ',
                            'amount' => $amount
                        ];
                    }
                }
            } else {
                echo 'No Items   found for this challan.';
            }


            $previousUnpaidChallans = StudyPackChallans::with('items.product','items')
                ->where('student_id', $challandata->student_id)
                ->where('status', '!=', 'Paid')
                ->where('id', '!=', $challandata->id)
                ->wheredate('fee_month', '<', date('Y-m-01', strtotime($challandata->fee_month)))
                ->get();

            if (Auth::user()->type == 'company') {
                $account_all = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                    ->where('created_by', \Auth::user()->creatorId())
                    ->get()
                    ->pluck('name', 'id');

                $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                    ->where('owned_by', Auth::user()->ownedId())
                    ->get()
                    ->pluck('name', 'id');
            } else {
                if ($challandata->owned_by != Auth::user()->ownedId()) {
                    $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                        ->where('owned_by', \Auth::user()->ownedId())
                        ->get()
                        ->pluck('name', 'id');

                    $account_all = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                        ->where('created_by', \Auth::user()->creatorId())
                        ->get()
                        ->pluck('name', 'id');
                } else {
                    $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                        ->where('owned_by', \Auth::user()->ownedId())
                        ->get()
                        ->pluck('name', 'id');

                    $account_all = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))
                        ->where('created_by', \Auth::user()->creatorId())
                        ->get()
                        ->pluck('name', 'id');
                }
            }

            return response()->json([
                'challandetail' => $challandata,
                'previousUnpaidChallans' => $previousUnpaidChallans,
                'headsData' => $headsData,
                'accounts' => $accounts,
                'account_all' => $account_all
            ]);
        }catch (\Exception $e) {
            dd($e);
            \Log::error('Error fetching challan data: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while fetching challan data.'], 500);
        }
    }


    public function paidstudypackchallan(Request $request)
    {
        // dd($request->all());
        \DB::beginTransaction();
        $data = [];
        try {
            $invoicePayment = StudyPackChallans::where('challanNo', $request->challan_id)->first();
            $invoicePayment->paid_date = $request->recipt_date;
            $invoicePayment->paid_amount += $request->recipt_amt;
            if($invoicePayment->paid_amount >= $invoicePayment->total_amount){
                $invoicePayment->status = 'Paid';
            }else{
                $invoicePayment->status = 'Partial Paid';
            }
            $invoicePayment->save();

            //items paid
            foreach ($request->head_id as $key => $head_id) {
                $challanitem = StudyPackChallanItems::find($head_id);
                $challanitem->paid += $request->ramount[$key];
                $challanitem->save();
            }

            $Bank = BankAccount::find($request->bank);
            $recipt = new StudypackReceipts();
            $recipt->recipt_date = $request->recipt_date;
            $recipt->challan_id = $invoicePayment->id;
            $recipt->student_id = $invoicePayment->student_id;
            $recipt->challan_amount = $invoicePayment->total_amount - $invoicePayment->paid_amount;
            $recipt->recipt_amount = $request->recipt_amt;
            $recipt->bank_id = $request->bank;
            $recipt->account_id = $Bank->chart_account_id;
            $recipt->referance = $request->ref;
            $recipt->receive_type = $request->receive_type;
            $recipt->received_by = Auth::user()->id;
            $recipt->owned_by = $invoicePayment->owned_by;
            $recipt->created_by = \Auth::user()->creatorId();
            $recipt->save();
            $data['id'] = $invoicePayment->id;
            $data['no'] = $invoicePayment->challanNo;
            $data['date'] = $recipt->recipt_date;
            $data['recipt'] = $recipt->id;
            $data['user_id'] = $invoicePayment->student_id;
            $data['bank_id'] = $request->bank;
            $data['reference'] = $recipt->referance;
            $data['description'] = 'Payment for Challan No ' . $invoicePayment->challanNo;
            $data['amount'] = $recipt->recipt_amount;
            $data['category'] = 'Studypack';
            $data['owned_by'] = $invoicePayment->owned_by;
            $data['created_by'] = $invoicePayment->created_by;
            $data['account_id'] = $request->bank;
            // dd($data);
            $dataret = Utility::strv_entry($data);
            $recipt->update(['voucher_id' => $dataret]);
            if (\Auth::user()->type == 'company') {
                $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            } else {
                $accounts = BankAccount::select('*', \DB::raw("CONCAT(bank_name,' ',holder_name) AS name"))->where('owned_by', \Auth::user()->ownedId())->get()->pluck('name', 'id');
            }
            $recipts = $recipt;
            // dd($recipts);
            $data = view('students.studypackChallan.data_row', compact('recipts', 'accounts'))->render();
            // dd($data);
            \DB::commit();
            return response()->json(['success' => 'success', 'data' => $data]);
        } catch (\Exception $e) {
            \DB::rollback();
            dd($e);
            return response()->json(['error' => 'An error occurred while processing the payment.'], 500);
        }
    }
}
