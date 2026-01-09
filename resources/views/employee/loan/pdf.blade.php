@extends('layouts.admin')

@section('page-title')
{{ __('Employee Loan') }}
@endsection

@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        function generatePDF() {
            const element = document.getElementById('report-content');
            const opt = {
                filename: 'loan_installment_plan.pdf',
                html2canvas: {
                    scale: 1
                },
                jsPDF: {
                    unit: 'pt',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };
            html2pdf().from(element).set(opt).save();
        }

        function printPDF() {
            const element = document.getElementById('report-content');
            const opt = {
                filename: 'loan_installment_plan.pdf',
                html2canvas: {
                    scale: 1
                },
                jsPDF: {
                    unit: 'pt',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };
            html2pdf().from(element).set(opt).output('bloburl').then(function (pdf) {
                window.open(pdf);
            });
        }
    </script>
@endpush
@section('action-btn')
<div class="col text-end">
<a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"><span class="btn-inner--icon">Print Print</span></a>
</div>
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item">{{ __('Loan') }}</li>
@endsection

@section('content')
<div class="content mt-4" id="report-content">
    <div class="card p-4">
        <div style="width: 100%; position: relative; display: table;">
            <div style="display: table-cell; width: 20%; text-align: center; vertical-align: middle;"></div>
            <div style="display: table-cell; width: 55%; text-align: center; vertical-align: middle;">
                <h4 style="font-size: 1.9rem; margin: 0;">The Lynx School</h4>
                <h4 style="font-size: 1.2rem; font-weight: 800; margin: 0;">{{strtoupper(@$loan->emp_sec)}} LOAN DEDUCTION PLAN</h4>
            </div>
            <div style="display: table-cell; width: 25%; text-align: center; vertical-align: middle;">
                <div class="logo">
                    <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;"
                        alt="logo">
                </div>
            </div>
        </div>
        <div class="mt-4">
            <div class="row">
                <div class="col-md-6"><p> <b>Employee Name : </b>{{@$loan->employee->salute}} {{@$loan->employee->name}}</p></div>
                <div class="col-md-6 "style='text-align:right;'><p> <b>Date Of Joining : </b>{{ \Carbon\Carbon::parse(@$loan->employee->company_doj)->format('d-F-Y') }}</p></div>
                <div class="col-md-6"><p> <b>Employer Code : </b>{{@$loan->employee->id}}</p></div>
                <div class="col-md-6 "style='text-align:right;'><p> <b>Date Of Approval : </b>{{ \Carbon\Carbon::parse(@$loan->approval_date)->format('d F Y') }}</p></div>
                <div class="col-md-6"><p> <b>Branch : </b>{{\Auth::user()->getbranch(@$loan->employee->branch_id) ? \Auth::user()->getbranch(@$loan->employee->branch_id)->name: ''}} </p></div>
                <div class="col-md-6 "style='text-align:right;'><p> <b>{{ @$loan->status == 0 ? 'Pending' : 'Approved' }} Amount Of Loan : </b>
                  {{@$loan->amount}}
            </p></div>
            </div>
        </div>
        <div class="mt-4">
            <h4 style="text-align: center;"> Installment Plan</h4>
            <table style="text-align: center;">
                <thead style="text-align: center;">
                    <tr>
                        <th>Sr No.</th>
                        <th>Months</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody style="text-align: center;">
                @php
                    $total = 0;
                    $amount = $loan->amount;
                    $pay_period = $loan->pay_period;
                    $per_month_amount = $amount / $pay_period;
                @endphp
                @for ($i = 1; $i <= $pay_period; $i++)
                    @php
                        $startDate = \Carbon\Carbon::parse($loan->from_pay_month);
                        $date = $startDate->copy()->addMonths($i - 1)->format('d M Y');
                        $total += $per_month_amount;
                    @endphp
                    <tr >
                        <td style="text-align: center;">{{ $i }}</td>
                        <td style="text-align: center;">{{ $date }}</td>
                        <td style="text-align: center;">{{ number_format($per_month_amount, 2) }}</td>
                    </tr>
                @endfor
                    <tr>
                        <td style="text-align: center;"></td>
                        <td style="text-align: center;"><b>Total</b></td>
                        <td style="text-align: center;"><b>{{$total}} /-</b></td>
                    </tr>
                </tbody>
            </table>
            <div class="row mt-4">
                <div class="col-md-6 mt-4" style="text-align: center;"><p><span style="text-decoration:underline; ">1. Prepared by</span><br>Account's Officer</p></div>
                <div class="col-md-6 mt-4" style="text-align: center;"><p><span style="text-decoration:underline; ">2. Checked by</span><br>Manager Finance</p></div>
                <div class="col-md-6 mt-5" style="text-align: center;"><p><span style="text-decoration:underline; ">3. Received by</span><br>{{@$loan->employee->name}}</p></div>
                <div class="col-md-6 mt-5" style="text-align: center;"><p><span style="text-decoration:underline; ">4. Approved by</span><br>Managing Director
                </p></div>
            </div>

        </div>

    </div>
</div>
@endsection
