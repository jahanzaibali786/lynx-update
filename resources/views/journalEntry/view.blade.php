@extends('layouts.admin')
@section('page-title')
    {{ __('Journal Detail') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('journal-entry.index') }}">{{ __('Journal Entry') }}</a></li>
    <li class="breadcrumb-item">
        {{ $journalEntry->getVoucherNumber() }}
    </li>
@endsection
@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <script>
        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
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
            html2pdf().set(opt).from(element).save();
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
 
    <div class="float-end" style='display:flex; gap:5px;'>
        <a href="{{ route('journal-entry.voucher-print', $journalEntry->id) }}" target="_blank" class="btn btn-sm btn-primary"
            title="{{ __('Voucher Print') }}" data-original-title="{{ __('Voucher Print') }}">
            <span class="btn-inner--icon"><i class="ti ti-printer"></i> {{ __('Voucher Print') }}</span>
        </a>
        <a href="#" class="btn btn-sm btn-primary" onclick="printDiv()"
            title="{{ __('Print') }}" data-original-title="{{ __('Print') }}">
            <span class="btn-inner--icon">Pdf / Print</span>
        </a>

    </div>
@endsection
@section('content')
    @php
        $voucherType = strtoupper($journalEntry->voucher_type ?? 'JV');
        $voucherNumber = $journalEntry->getVoucherNumber();
    @endphp
    <div class="row" id="printableArea">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="invoice">
                        <div class="invoice-print">
                            <div class="row invoice-title mt-2">
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12">
                                    <h2>{{ __($voucherType . ' Voucher') }}</h2>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 text-end">
                                    <h3 class="invoice-number">
                                        {{ $voucherNumber }}</h3>
                                </div>
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="font-style">
                                        <strong>{{__('To')}} :</strong><br>
                                        {{!empty($settings['company_name'])?$settings['company_name']:''}}<br>
                                        {{!empty($settings['company_telephone'])?$settings['company_telephone']:''}}<br>
                                        {{!empty($settings['company_address'])?$settings['company_address']:''}}<br>
                                        {{!empty($settings['company_city'])?$settings['company_city']:'' .', '}}  {{!empty($settings['company_state'])?$settings['company_state']:'' .', '}}  {{!empty($settings['company_country'])?$settings['company_country']:'' .'.'}}
                                    </small>
                                </div>
                                <div class="col-md-6 text-end">
                                    <small>
                                        <strong>{{ __('Voucher No') }} :</strong>
                                        {{ $voucherNumber }}
                                    </small><br>
                                    <small>
                                        <strong>{{ __('Invoice No / Reference') }} :</strong>
                                        {{ $journalEntry->reference }}
                                    </small> <br>
                                    <small>
                                        <strong>{{ __('Voucher Date') }} :</strong>
                                        {{ \Auth::user()->dateFormat($journalEntry->date) }}
                                    </small>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <small><strong>{{ __('Status') }} :</strong>
                                        {{ $journalEntry->status ?? __('Draft') }}</small>
                                </div>
                                <div class="col-md-3">
                                    <small><strong>{{ __('Payment Mode') }} :</strong>
                                        {{ $journalEntry->payment_mode ?? ($journalEntry->mode ?? '-') }}</small>
                                </div>
                                <div class="col-md-3">
                                    <small><strong>{{ __('Bank Account') }} :</strong>
                                        {{ optional($journalEntry->bank)->bank_name ?? '-' }}</small>
                                </div>
                                <div class="col-md-3">
                                    <small><strong>{{ __('Transaction No') }} :</strong>
                                        {{ $journalEntry->transaction_no ?? '-' }}</small>
                                </div>
                                <div class="col-md-3 mt-1">
                                    <small><strong>{{ __('Cheque No') }} :</strong>
                                        {{ $journalEntry->cheque_no ?? '-' }}</small>
                                </div>
                                <div class="col-md-3 mt-1">
                                    <small><strong>{{ __('Cheque Date') }} :</strong>
                                        {{ !empty($journalEntry->cheque_date) ? \Auth::user()->dateFormat($journalEntry->cheque_date) : '-' }}</small>
                                </div>
                                <div class="col-md-3 mt-1">
                                    <small><strong>{{ __('Approved At') }} :</strong>
                                        {{ !empty($journalEntry->approved_at) ? \Auth::user()->dateFormat($journalEntry->approved_at) : '-' }}</small>
                                </div>
                                <div class="col-md-3 mt-1">
                                    <small><strong>{{ __('Attachment') }} :</strong>
                                        @if (!empty($journalEntry->attachment))
                                            <a href="{{ asset($journalEntry->attachment) }}" target="_blank">{{ __('View') }}</a>
                                        @else
                                            -
                                        @endif
                                    </small>
                                </div>
                            </div>

                            <!-- Payee and Receiver Details -->
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="card bg-light border-0">
                                        <div class="card-body p-3">
                                            <h6 class="text-primary font-weight-bold mb-2">{{ __('Payee Details') }}</h6>
                                            <ul class="list-unstyled mb-0" style="font-size: 0.85rem; line-height: 1.6;">
                                                <li><strong>{{ __('Account Title') }}:</strong> {{ $journalEntry->payee_account_title ?? '-' }}</li>
                                                <li><strong>{{ __('Account No') }}:</strong> {{ $journalEntry->payee_account_no ?? '-' }}</li>
                                                <li><strong>{{ __('CNIC') }}:</strong> {{ $journalEntry->payee_cnic ?? '-' }}</li>
                                                <li><strong>{{ __('Contact') }}:</strong> {{ $journalEntry->payee_contact ?? '-' }}</li>
                                                <li><strong>{{ __('Email') }}:</strong> {{ $journalEntry->payee_email ?? '-' }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light border-0">
                                        <div class="card-body p-3">
                                            <h6 class="text-primary font-weight-bold mb-2">{{ __('Receiver Details') }}</h6>
                                            <ul class="list-unstyled mb-0" style="font-size: 0.85rem; line-height: 1.6;">
                                                <li><strong>{{ __('Name') }}:</strong> {{ $journalEntry->receiver_name ?? '-' }}</li>
                                                <li><strong>{{ __('CNIC') }}:</strong> {{ $journalEntry->receiver_cnic ?? '-' }}</li>
                                                <li><strong>{{ __('Contact') }}:</strong> {{ $journalEntry->receiver_contact ?? '-' }}</li>
                                                <li><strong>{{ __('Email') }}:</strong> {{ $journalEntry->receiver_email ?? '-' }}</li>
                                                <li><strong>{{ __('Payment Date') }}:</strong> {{ !empty($journalEntry->payment_date) ? \Auth::user()->dateFormat($journalEntry->payment_date) : '-' }}</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="font-weight-bold">{{ __('Journal Voucher Account Summary') }}</div>
                                    <div class="table-responsive mt-2">
                                        <table class="table mb-0 ">
                                            <tr>
                                                <th data-width="40" class="text-dark">#</th>
                                                <th class="text-dark">{{ __('Account') }}</th>
                                                <th class="text-dark">{{ __('Ref No') }}</th>
                                                <th class="text-dark">{{ __('Date') }}</th>
                                                <th class="text-dark wrap-td">{{ __('Memo') }}</th>
                                                <th class="text-dark wrap-td">{{ __('Description') }}</th>
                                                <th class="text-dark">{{ __('Debit') }}</th>
                                                <th class="text-dark">{{ __('Credit') }}</th>
                                                {{-- <th></th> --}}
                                            </tr>

                                            @foreach ($accounts as $key => $account)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ !empty($account->accounts) ? $account->accounts->code . ' - ' . $account->accounts->name : '' }}
                                                    </td>
                                                    <td>
                                                        {{ !empty($account->ref_no) ? $account->ref_no : '-' }}
                                                    </td>
                                                    <td>
                                                        {{ !empty($account->tra_date) ? \Auth::user()->dateFormat($account->tra_date) : '-' }}
                                                    </td>
                                                    <td class="wrap-td">
                                                        {{ !empty($account->memo) ? $account->memo : '-' }}
                                                    </td>
                                                    <td class="wrap-td">
                                                        {{ !empty($account->description) ? $account->description : '-' }}
                                                    </td>
                                                    <td>{{ \Auth::user()->priceFormat($account->debit) }}</td>
                                                    <td>{{ \Auth::user()->priceFormat($account->credit) }}</td>
                                                    {{-- <td>
                                                        <div class="action-btn bg-danger ms-2">
                                                            {!! Form::open(['method' => 'DELETE', 'route' => array('journal.destroy', $account->id),'id'=>'delete-form-'.$account->id]) !!}

                                                            <a href="#" class="mx-3 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$account->id}}').submit();">
                                                                <span class="btn-inner--icon"> <i class="ti ti-trash"></i> </span>
                                                            </a>
                                                            {!! Form::close() !!}

                                                        </div>
                                                    </td> --}}
                                                </tr>
                                            @endforeach

                                            <tfoot>

                                                <tr>
                                                    <td colspan="5"></td>
                                                    <td><b>{{ __('Total') }}</b></td>
                                                    <td><b>{{ \Auth::user()->priceFormat($journalEntry->totalDebit()) }}</b></td>
                                                    <td><b>{{ \Auth::user()->priceFormat($journalEntry->totalCredit()) }}</b></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div class="font-bold mt-2">
                                        {{ __('Note') }} : <br>
                                    </div>
                                    <small>{{ $journalEntry->description }}</small>
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
                                        {{ __('Approval By ') }} <br><br>
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
