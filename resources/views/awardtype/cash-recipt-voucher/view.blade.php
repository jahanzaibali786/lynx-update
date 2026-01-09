@extends('layouts.admin')
@section('page-title')
{{__('Cash Recipt Voucher Detail')}}
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Vouchers')}}</li>
<li class="breadcrumb-item"><a href="{{route('cash-recipt-voucher.index')}}">{{__('Cash Recipt Voucher Entry')}}</a>
</li>
<li class="breadcrumb-item">{{ Auth::user()->CRVNumberFormat($journalEntry->journal_id) }}</li>
@endsection

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <button onclick="generatePDF()" class="btn mx-1 btn-sm btn-outline-primary mb-4" style="float:right;">Print</button>
                <div class="invoice p-5" id="invoice">
                    <div class="invoice-print">
                        <div class="row invoice-title mt-2">
                            <div class="col-xs-12 col-sm-4 col-md-4 col-lg-4 col-12">
                                <p style="font-size:1.3rem;">
                                    <b>{{!empty($settings['company_name'])?$settings['company_name']:''}}</b>
                                </p>
                            </div>
                            <div class="col-xs-12 col-sm-4 col-md-6 col-lg-6 col-12 text-center">
                                <h2 class="invoice-number">{{ __('Cash Recipt Voucher') }}</h2>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-6">
                                    <p><u>Dated:</u> &emsp;{{\Auth::user()->dateFormat($journalEntry->date)}}</p>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-6">
                                    <h3 class="invoice-number">
                                        {{\Auth::user()->CRVNumberFormat($journalEntry->journal_id)}}</h3>
                                </div>
                            </div>
                        </div><br>
                        <div class="row">
                            <p class='col-md-3'><b>Student Name :</b> {{@$accounts['0']->receiptheads->challan->student->stdname}}</p>
                            <p class='col-md-3'><b>Father Name :</b> {{@$accounts['0']->receiptheads->challan->student->fathername}}</p>
                            <p class='col-md-3'><b>Roll No :</b> {{@$accounts['0']->receiptheads->challan->enrollstudent->enrollId}}</p>
                            <p class='col-md-3'><b>Challan No :</b> {{@$accounts['0']->receiptheads->challan->challanNo}}</p>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                <table class="full-width-table" style="width: 100%;">
                                    <tr>
                                        <th class="row_center">Account Details</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                    </tr>
                                    <tr>
                                        <td class="row_center"></td>
                                        <td colspan="2">Amount in PKR</td>
                                    </tr>
                                    @foreach ($accounts as $key => $account)
                                    <tr>
                                        <td class="row_center">
                                            {{ !empty($account->accounts) ? $account->accounts->code . ' - ' . $account->accounts->name : '' }}
                                        </td>
                                        <td class="row_left">{{ \Auth::user()->priceFormat($account->debit) }}</td>
                                        <td class="row_left">{{ \Auth::user()->priceFormat($account->credit) }}</td>
                                    </tr>
                                    @endforeach

                                    <tr>
                                        <td class="row_center"><b>Total</b></td>
                                        <td class="row_left">
                                            <b>{{ \Auth::user()->priceFormat($journalEntry->totalDebit()) }}</b>
                                        </td>
                                        <td class="row_left">
                                            <b>{{ \Auth::user()->priceFormat($journalEntry->totalCredit()) }}</b>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <br>
                        <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                            <div style="display: flex; flex-direction: column; font-weight: bold;">
                                {{__('Prepared by ')}} : <br><br>
                                <p>___________________</p>
                            </div>
                            <div style="display: flex; flex-direction: column; font-weight: bold;">
                                {{__('Reviewed by ')}} : <br><br>
                                <p>___________________</p>
                            </div>
                            <div style="display: flex; flex-direction: column; font-weight: bold;">
                                {{__('Approval by ')}} : <br><br>
                                <p>___________________</p>
                            </div>
                        </div>
                        <div class="font-bold mt-2">
                            {{__('Description')}} : <br>
                        </div>
                        <small>{{$journalEntry->description}}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
<script>
    function generatePDF() {
            console.log('generating');
            const element = document.getElementById('invoice');
            const opt = {
                filename: 'Cash_Recipt_Voucher.pdf',
                html2canvas: { scale: 1 },
                jsPDF: { unit: 'pt', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().from(element).set(opt).save();
        }

        function printPDF() {
            console.log('printing');
            const element = document.getElementById('invoice');
            const opt = {
                filename: 'Cash_Recipt_Voucher.pdf',
                html2canvas: { scale: 1 },
                jsPDF: { unit: 'pt', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().from(element).set(opt).output('bloburl').then(function (pdf) {
                window.open(pdf);
            });
        }
// function printInvoice() {
//     var printContents = document.getElementById('invoice').innerHTML;
//     var originalContents = document.body.innerHTML;

//     document.body.innerHTML = printContents;

//     window.print();

//     document.body.innerHTML = originalContents;
// }
</script>
@endsection