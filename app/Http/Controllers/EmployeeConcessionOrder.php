<?php

namespace App\Http\Controllers;

use App\Models\Concession;
use App\Models\ConcessionPolicy;
use App\Models\ConcessionPolicyHead;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;
class EmployeeConcessionOrder extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $loans = Concession::with('student','class')->where('created_by', \Auth::user()->creatorId())->get();
        return view('employee.concessionorder.index',compact('loans'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(\Auth::user()->can('create session'))
        {
            return view('employee.concessionorder.create');
        }
        else
        {
            return response()->json(['error' => __('Permission denied.')], 401);
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
        //store the concession order
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // dd($id);
        $concession = Concession::with('student','class')->find($id);
        if(!$concession){
            return redirect()->back()->with('error', __('Concession Not Found.'));
        }
        $concession_policy = ConcessionPolicy::with('concession','concession.student','concession.student.enrollment','concession.class','concession.student.session')->findOrFail( $concession->concession_id );
        $concession_heads = ConcessionPolicyHead::where(    'concession_id', $concession_policy->id)->where('percentage' , '!=',0)->get();
        // dd($concession_policy,$concession_heads);
        $html = view('employee.concessionorder.template', ['data' => $concession_heads,'student'=>@$concession->student,'class'=>$concession->class
            ])->render();
            $headerHtml = view('employee.emp_salary_detail.pdf.header')->render();
            $footerHtml = view('employee.emp_salary_detail.pdf.footer')->render();
            $html = '<html><head>
            <style>
                @page {
                    margin-top: 100px;
                    margin-bottom: 100px;
                }
                .header { position: fixed; top: -60px; left: 0px; right: 0px; height: 100px; text-align: center;  }
                .footer { position: fixed; bottom: -60px; height: 50px; left:0px; right:0px; }
            </style>
            </head><body>
            <div class="header">' . $headerHtml . '</div>
            <div class="footer">' . $footerHtml . '</div>
            ' . $html . '
            </body></html>';
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true); // To load images, fonts, etc.
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $pdfContent = $dompdf->output();
            $base64Pdf = base64_encode($pdfContent);
            $pdfDecoded = base64_decode($base64Pdf);
            return response($pdfDecoded)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="concession_order.pdf"')
                ->header('Content-Length', strlen($pdfDecoded));

            // return response($pdfDecoded)
            //     ->header('Content-Type', 'application/pdf')
            //     ->header('Content-Disposition', 'attachment; filename="concession_order.pdf"')
            //     ->header('Content-Length', strlen($pdfDecoded));
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
