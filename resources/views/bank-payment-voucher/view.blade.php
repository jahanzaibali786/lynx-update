@extends('layouts.admin')
@section('page-title')
    {{__('Bank Payment Voucher Detail')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Vouchers')}}</li>
    <li class="breadcrumb-item"><a href="{{route('bank-payment-voucher.index')}}">{{__('Bank Payment Voucher Entry')}}</a></li>
    <li class="breadcrumb-item">{{ Auth::user()->BPVNumberFormat($journalEntry->journal_id) }}</li>
@endsection
@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var style = `
                <style>
                    table {
                        border-collapse: collapse;
                        width: 100%;
                        border: 1px solid gray !important;
                    }
                    th, td {
                        border: 1px solid gray !important;
                        padding: 5px !important;
                    }
                    th {
                        background-color: #f2f2f2 !important;
                    }
                </style>
            `;

            // Create a temporary wrapper div
            var wrapper = document.createElement('div');
            wrapper.innerHTML = style + element.innerHTML;
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A2'
                }
            };
            html2pdf().set(opt).from(wrapper).save();
        }
        function printDiv() {
            var element = document.getElementById('printableArea');

            var style = `
                <style>
                    table {
                        border-collapse: collapse;
                        width: 100%;
                        border: 1px solid gray !important;
                    }
                    th, td {
                        border: 1px solid gray !important;
                        padding: 5px !important;
                    }
                    th {
                        background-color: #f2f2f2 !important;
                    }
                </style>
            `;

            // Create a temporary wrapper div
            var wrapper = document.createElement('div');
            wrapper.innerHTML = style + element.innerHTML;

            var opt = {
                margin: 0.3,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A2'
                }
            };

            html2pdf().set(opt).from(wrapper).outputPdf('bloburl').then(function(pdfUrl) {
                window.open(pdfUrl, '_blank');
            });
        }
    </script>
@endpush
@section('action-btn')
<style>
    .wrap-td {
        max-width: 500px !important;
        text-wrap: auto !important;
    }
</style>
    <div class="float-end" style='display:flex; gap:5px;'>
        <a href="#" class="btn btn-sm btn-primary" onclick="printDiv()"
        title="{{ __('Print') }}" data-original-title="{{ __('Print') }}">
        <span class="btn-inner--icon">Pdf / Print</span>
    </a>
        <a href="#" class="btn btn-sm btn-primary" onclick="saveAsPDF()"
            title="{{ __('Download') }}" data-original-title="{{ __('Download') }}">
            <span class="btn-inner--icon">Pdf / Print</span>
        </a>

    </div>
@endsection
@section('content')

    <div class="row" id="printableArea">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="invoice">
                        <div class="invoice-print">
                            <div class="row invoice-title mt-2">
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12">
                                    <h2>{{__('Bank Payment Voucher')}}</h2>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 text-end">
                                    <h3 class="invoice-number">{{ \AUth::user()->BPVNumberFormat($journalEntry->journal_id) }}</h3>
                                </div>
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    {{-- <small class="font-style">
                                        <strong>{{__('To')}} :</strong><br>
                                        {{!empty($settings['company_name'])?$settings['company_name']:''}}<br>
                                        {{!empty($settings['company_telephone'])?$settings['company_telephone']:''}}<br>
                                        {{!empty($settings['company_address'])?$settings['company_address']:''}}<br>
                                        {{!empty($settings['company_city'])?$settings['company_city']:'' .', '}}  {{!empty($settings['company_state'])?$settings['company_state']:'' .', '}}  {{!empty($settings['company_country'])?$settings['company_country']:'' .'.'}}
                                    </small> --}}
                                </div>
                                <div class="col-md-6 text-end">
                                    <small>
                                        <strong>{{__('Voucher No')}} :</strong>
                                        {{\Auth::user()->BPVNumberFormat($journalEntry->journal_id)}}
                                    </small><br>
                                    <small>
                                        <strong>{{__('Voucher Ref')}} :</strong>
                                        {{$journalEntry->reference}}
                                    </small> <br>
                                    <small>
                                        <strong>{{__('Voucher Date')}} :</strong>
                                        {{\Auth::user()->dateFormat($journalEntry->date)}}
                                    </small>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="font-weight-bold">{{__('Bank Payment Voucher Account Summary')}}</div>
                                    <div class="table-responsive mt-2">
                                        <table class="table mb-0 ">
                                            <tr>
                                                <th data-width="40" class="text-dark">#</th>
                                                <th class="text-dark">{{__('Account')}}</th>
                                                <th class="text-dark wrap-td">{{__('Description')}}</th>
                                                <th class="text-dark">{{__('Debit')}}</th>
                                                <th class="text-dark">{{__('Credit')}}</th>
                                                <th class="text-dark">{{__('Amount')}}</th>
                                                {{-- <th></th> --}}
                                            </tr>

                                            @foreach($accounts as $key =>$account)

                                                <tr>
                                                    <td>{{$key+1}}</td>
                                                    <td>{{!empty($account->accounts)?$account->accounts->code.' - '.$account->accounts->name:''}}</td>
                                                    <td class="wrap-td">{{!empty($account->description)?$account->description:'-'}}</td>
                                                    <td>{{\Auth::user()->priceFormat($account->debit)}}</td>
                                                    <td>{{\Auth::user()->priceFormat($account->credit)}}</td>
                                                    <td >
                                                        @if($account->debit!=0)
                                                            {{\Auth::user()->priceFormat($account->debit)}}
                                                        @else
                                                            {{\Auth::user()->priceFormat($account->credit)}}
                                                        @endif
                                                    </td>
                                                    {{-- <td>
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => array('bpv.destroy', $account->id),'id'=>'delete-form-'.$account->id]) !!}

                                                            <a href="#" class="mx-3 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$account->id}}').submit();">
                                                               <span class="btn-inner--icon"> <i class="ti ti-trash"></i></span>
                                                            </a>
                                                            {!! Form::close() !!}

                                                        </div>
                                                    </td> --}}
                                                </tr>

                                            @endforeach

                                            <tfoot>

                                            <tr>
                                                <td colspan="4"></td>
                                                <td><b>{{__('Total Credit')}}</b></td>
                                                <td>{{\Auth::user()->priceFormat($journalEntry->totalCredit())}}</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td><b>{{__('Total Debit')}}</b></td>
                                                <td>{{\Auth::user()->priceFormat($journalEntry->totalDebit())}}</td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="font-bold mt-2">
                                        {{__('Note')}} : <br>
                                    </div>
                                    <small>{{$journalEntry->description}}</small>
                                </div>
                                <div style="display: flex; justify-content: space-between; margin-top: 22px;">
                                    <div
                                        style="display: flex; flex-direction: column; font-weight: bold; text-align:center">
                                        {{ __('Prepared By ') }}<br><br>
                                        <p>___________________</p>
                                    </div>
                                    <div
                                        style="display: flex; flex-direction: column; font-weight: bold; text-align:center">
                                        {{ __('Reviewed By ') }} <br><br>
                                        <p>___________________</p>
                                    </div>
                                    <div
                                        style="display: flex; flex-direction: column; font-weight: bold; text-align:center">
                                        {{ __('Approved By ') }} <br><br>
                                        <p>___________________</p>
                                    </div>
                                    <div
                                        style="display: flex; flex-direction: column; font-weight: bold; text-align:center">
                                        {{ __('Recicved By ') }} <br><br>
                                        <p>___________________</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
