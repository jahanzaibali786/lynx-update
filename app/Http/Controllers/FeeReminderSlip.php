<?php

namespace App\Http\Controllers;

use App\Models\Challans;
use App\Models\Classes;
use App\Models\StudentEnrollments;
use App\Models\StudentReceipt;
use App\Models\StudentRegistration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Dompdf\Options;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use setasign\Fpdi\Fpdi;
use Storage;
use Twilio\Rest\Client;

class FeeReminderSlip extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (Auth::user()->type == 'company') {
            $branches = User::where('type', '=', 'branch')->get()->pluck('name', 'id');
            $branches->prepend(Auth::user()->name, Auth::user()->id);
            $branches->prepend('Select Branch', '');
            $query = Challans::select(DB::raw('student_id,class_id,fee_month,id,((total_amount)-(paid_amount + concession_amount)) as total'))
                ->with('student', 'enrollstudent', 'class', 'enrollstudent.master', 'enrollstudent.section')
                ->where('status', '!=', 'Paid')
                ->where('created_by', Auth::user()->creatorId());
        } else {
            $branches = User::where('id', '=', Auth::user()->ownedId())->get()->pluck('name', 'id');
            $branches->prepend('Select Branch', '');
            $query = Challans::select(DB::raw('student_id,class_id,fee_month,id,((total_amount)-(paid_amount + concession_amount)) as total'))
                ->with('student', 'enrollstudent', 'class', 'enrollstudent.master', 'enrollstudent.section')
                ->where('status', '!=', 'Paid')
                ->where('owned_by', Auth::user()->ownedId());
        }

        $session = [];
        $class = [];
        $student = [];
        $filtersApplied = false;

        if (!empty($request->start_date)) {
            $query->whereDate('due_date', '>=', $request->start_date);
            $filtersApplied = true;
        }
        if (!empty($request->end_date)) {
            $query->whereDate('due_date', '<=', $request->end_date);
            $filtersApplied = true;
        }
        if (empty($request->start_date) && empty($request->end_date)) {
            $currentYear = date('Y');
            $currentMonth = date('m');
            $dateFrom = ($currentMonth >= 7) ? "$currentYear-07-01" : date('Y-07-01', strtotime('-1 year'));
            $dateTo = ($currentMonth >= 7) ? date('Y-06-30', strtotime('+1 year')) : "$currentYear-06-30";
            // $request->date_from = $dateFrom;
            $request->merge(['start_date' => $dateFrom]);
            $request->merge(['end_date' => $dateTo]);
        }
        if (!empty($request->branches)) {
            $query->where('owned_by', '=', $request->branches);
            $class = Classes::where('owned_by', '=', $request->branches)->get()->pluck('name', 'id');
            $filtersApplied = true;
        }
        if (!empty($request->class)) {
            $query->where('class_id', '=', $request->class);
            $student = StudentRegistration::where('class_id', '=', $request->class)->get()->pluck('stdname', 'id');
            $filtersApplied = true;
        }
        if (!empty($request->student)) {
            if ($request->student != 'all') {
                $query->where('student_id', '=', $request->student);
                $filtersApplied = true;
            }
        }

        // if ($filtersApplied) {
        //     $challans = $query->orderBy('student_id')->orderBy('fee_month')->get();
        //     // $challans = $query->havingRaw('total > 400')->get();
        // } else {
        //     $challans = collect();
        // }

        $challans = $filtersApplied
        ? $query->orderBy('student_id')->orderBy('fee_month')->get() // <-- Add pagination here
        : collect();

        return view('students.Feereminder.view', compact('class', 'session', 'branches', 'challans', 'student', 'request'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // dd($request->all());
        return view('students.Feereminder.template');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $dompdf = new Dompdf($options);

            $data = $request->data;
            $html = view('students.Feereminder.template', [
                'reminder' => $request->reminder,
                'data' => $data,
            ])->render();
            $footerHtml = view('students.classwisefee.report.pdf.footer')->render();

            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $challanContent = '';
            $div = $dom->getElementById('slip-content');
            if ($div) {
                $challanContent = $dom->saveHTML($div);
            }
            $html = '<html><head>
            <style>
                @page {
                    margin-bottom: 20px;
                }
                .footer { position: fixed; bottom: -40px; height: 50px; left:0px; right:0px; }
            </style>
            </head><body>
            <div class="footer">' . $footerHtml . '</div>
            ' . $challanContent . '
            </body></html>';

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4');
            $dompdf->render();
            $output = $dompdf->output();

            $reminderType = $request->reminder;
            $reminderType = str_replace(' ', '_', $reminderType);
            $studentName = isset($data['studentName']) ? $data['studentName'] : 'N/A';
            $filename = $studentName === 'N/A'
                ? 'Student Fee Reminder Slip.pdf'
                : "{$studentName} -({$reminderType}) Fee Reminder Slip.pdf";
            
            $filePath = public_path('temp_pdfs/' . $filename);

            if ($request->type === 'whatsapp') {
                if (!file_exists(public_path('temp_pdfs'))) {
                    mkdir(public_path('temp_pdfs'), 0755, true);
                }
                file_put_contents($filePath, $output);
                $fileUrl = asset('public/temp_pdfs/' . $filename);
                $sid = 'AC9e90302661b3e563b4e8dc7d806f9edb';
                $token = 'f50c0a608d8dc0cf7fb01fd5fba8bec1';
                $twilio = new Client($sid, $token);
                try {
                    $twilio->messages->create(
                        'whatsapp:+923363999481',
                        [
                            'from' => 'whatsapp:+14155238886',
                            'body' => 'Please find the fee reminder slip attached.',
                            'mediaUrl' => [$fileUrl]
                        ]
                    );
                    unlink($filePath);
                    return response()->json(['message' => 'WhatsApp message sent successfully!']);
                } catch (\Exception $e) {
                    Log::error('Failed to send WhatsApp message:', ['error' => $e->getMessage()]);
                    return response()->json(['message' => 'Failed to send WhatsApp message.'], 500);
                }
            }
            return response($output)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");

        } catch (\Exception $e) {
            Log::error("Error generating fee reminder slip: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['error' => 'There was an error generating the PDF.', 'message' => $e->getMessage()], 500);
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
        //
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
}
