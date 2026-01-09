@extends('layouts.admin')
@section('page-title')
    {{ __('Fee Receipt Summary') }}
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf2htmlEX/0.18.7/pdf2htmlEX.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        function generatePDF() {
            console.log('generating');
            const element = document.getElementById('studentfeereceipt');
            const opt = {
                filename: 'studentfeereceiptsummary.pdf',
                html2canvas: {
                    scale: 1
                },
                jsPDF: {
                    unit: 'pt',
                    format: [700, 900],
                    orientation: 'portrait'
                }
            };
            html2pdf().from(element).set(opt).save();
        }

        function printPDF() {
            console.log('printing');
            const element = document.getElementById('studentfeereceipt');
            const opt = {
                filename: 'studentfeereceiptsummary.pdf',
                html2canvas: {
                    scale: 1
                },
                jsPDF: {
                    unit: 'pt',
                    format: [700, 900],
                    orientation: 'portrait'
                }
            };
            html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
                window.open(pdf);
            });
        }

        // function printReport() {
        //         // alert('student')
        //     let form = document.getElementById('student_receipt_list');
        //     let formData = new FormData(form);
        //     let queryString = new URLSearchParams(formData).toString();
        //     window.location.href = "{{ route('fee_receipt_summary.report') }}?" + queryString;
        // }

      function printReport() {
    var form = document.getElementById('student_receipt_list');
    var formData = new FormData(form);
    var queryString = new URLSearchParams(formData).toString();

    $.ajax({
        url: "{{ route('fee_receipt_summary.report') }}?" + queryString,
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            const base64Pdf = response.base64Pdf;
            const byteCharacters = atob(base64Pdf);
            const byteNumbers = new Array(byteCharacters.length);
            for (let i = 0; i < byteCharacters.length; i++) {
                byteNumbers[i] = byteCharacters.charCodeAt(i);
            }
            const byteArray = new Uint8Array(byteNumbers);
            const blob = new Blob([byteArray], {
                type: 'application/pdf'
            });
            const blobUrl = URL.createObjectURL(blob);
            window.open(blobUrl, '_blank');
        },
        error: function(xhr) {
            console.log(xhr.responseText);
        }
    });
}

    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Fee Receipt Summary') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
    </div>
@endsection
@section('content')
    <style>
        .table_heads {
            line-height: 0.1rem !important;
        }
    </style>
    @php
        $fromDate = request()->get('start_date') ?? date('Y-m-d', strtotime('-1 month'));
        $toDate = request()->get('end_date') ?? date('Y-m-d');
        $selectedBranch = request()->get('branches');
    @endphp
    {{-- @dd($fromDate,$toDate) --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding: 12px;">
                        {{ Form::open(['route' => ['fee_receipt_summary'], 'method' => 'GET', 'id' => 'student_receipt_list']) }}
                        <div class="row d-flex justify-content-end" style="width: 100%">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : '', ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, $selectedBranch, ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('default_bank', __('Bank'), ['class' => 'form-label']) }}
                                    {{ Form::select('default_bank', $accounts, request()->get('default_bank'), ['class' => 'form-control select', 'id' => 'default_bank']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('student_receipt_list').submit(); return false;"
                                     data-bs-title="Search">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                {{-- <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"><span
                                    class="btn-inner--icon">Print</span>
                            </a> --}}

                            <a href="{{ route('fee_receipt_summary.report', array_merge(request()->all(), ['is_print' => 1])) }}" target="_blank" class="btn mx-1 btn-sm btn-outline-success" data-bs-title="Print">
                                <span class="btn-inner--icon">Print
                                </span>
                            </a>
                            {{-- excel button --}}
                            <a href="{{ route('fee_receipt_summary.report', array_merge(request()->all(), ['is_excel' => 1])) }}" target="_blank" class="btn mx-1 btn-sm btn-outline-success" data-bs-title="Export">
                                <span class="btn-inner--icon">Export
                                </span>
                            </a>
                        </div>

                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="card mt-2 p-4" id="studentfeereceipt">
    <div class="mt-4" style="margin: 0 auto; padding: 30px;">
        <div style="width: 100%; text-align: center;">
            <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School 
                    </b></p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">Fee Receipt Summary</p>
        </div>
        <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">PWD BRANCH ISLAMABAD</p>
        </div>
        <div class="d-flex justify-content-between">
            <p><b>From Date: </b>{{ $fromDate }}</p>
            <p>All Banks</p>
            <p style=" padding-left:100px;"><b>To Date: </b>{{ $toDate }}</p>
        </div>
        <div class="" style="width: 100%;">
            <table class="">
                <thead>
                    <tr class="table_heads thead2" style="font-size:0.8rem;">
                        <th>{{__('Date')}}</th>
                        @foreach ($branches as $key => $branch)
                        @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                        <th colspan="2" class="text-center">{{ $branch }}</th>
                        @endif
                        @endforeach
                        <th colspan="2" class="text-center">{{__('Total')}}</th>
                    </tr>
                    <tr class="table_heads" style="font-size:0.8rem;">
                        <th></th>
                        @foreach ($branches as $key => $branch)
                        @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                        <th>{{__('Receipts')}}</th>
                        <th>{{__('Amount')}}</th>
                        @endif
                        @endforeach
                        <th>{{__('Receipts')}}</th>
                        <th>{{__('Amount')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $startDate = \Carbon\Carbon::parse($fromDate);
                    $endDate = \Carbon\Carbon::parse($toDate);
                    @endphp

                    @for ($date = $startDate; $date <= $endDate; $date->addDay())
                        @php
                        $hasData = false;
                        $dateTotalReceipts = 0;
                        $dateTotalAmount = 0;
                        @endphp

                        @foreach ($branches as $key => $branch)
                        @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                        @php
                        $branchReceipts = $recipts->filter(function ($receipt) use ($date, $key) {
                        return \Carbon\Carbon::parse($receipt->recipt_date)->isSameDay($date) && $receipt->owned_by ==
                        $key;
                        });
                        @endphp
                        @if ($branchReceipts->isNotEmpty())
                        @php
                        $hasData = true;
                        @endphp
                        @endif
                        @endif
                        @endforeach

                        @if ($hasData)
                        <tr style="font-size:0.7rem;">
                            <td>{{ $date->format('d-M-Y') }}</td>
                            @foreach ($branches as $key => $branch)
                            @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                            @php
                            $branchReceipts = $recipts->filter(function ($receipt) use ($date, $key) {
                            return \Carbon\Carbon::parse($receipt->recipt_date)->isSameDay($date) && $receipt->owned_by
                            == $key;
                            });
                            $receiptCount = $branchReceipts->count();
                            $totalAmount = $branchReceipts->sum(function ($receipt) {
                            return $receipt->voucher->sum('credit');
                            });
                            $dateTotalReceipts += $receiptCount;
                            $dateTotalAmount += $totalAmount;
                            @endphp
                            <td>{{ $receiptCount  }}</td>
                            <td>{{ $totalAmount }}</td>
                            @endif
                            @endforeach
                            <td>{{ $dateTotalReceipts }}</td>
                            <td>{{ $dateTotalAmount }}</td>
                        </tr>
                        @endif
                        @endfor
                </tbody>
            </table>

        </div>
    </div>
</div> --}}
    <div class="card mt-2 p-4" id="studentfeereceipt" style="max-height: 500px; overflow-y: auto;">
        <div class="mt-4" style="margin: 0 auto; padding: 30px;">
            <div style="width: 100%; text-align: center;">
                <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;">
                    <b>The Lynx School </b>
                </p>
                <p style="font-size:1rem; text-align: center; font-weight: 800; margin-top : -22px;">
                    <b> </b>
                </p>
            </div>
            <div style="width: 100%; text-align: center; ">
                <p style="font-size:1rem; text-align: center; font-weight: 800; margin-top:-10;">Fee Receipt Summary</p>
            </div>
            {{-- <div style="width: 100%; text-align: center;">
            <p style="font-size:1rem; text-align: center; font-weight: 800;">{{ @$brnches_name->name ?? 'All Branches' }}</p>
        </div> --}}
            <div class="d-flex justify-content-between">
                <p><b>From Date: </b>{{ $fromDate }}</p>
                <p>{{ @$bank_accounts->holder_name ?? 'All Banks' }}</p>
                <p style=" padding-left:100px;"><b>To Date: </b>{{ $toDate }}</p>
            </div>
            <div class="table-responsive" style="position: relative;">
                <table class="table datatable">
                    <thead style="position: sticky; top: 0; background-color: white; z-index: 1;">
                        <tr class="table_heads thead2" style="font-size:0.8rem;">
                            <th>{{ __('Date') }}</th>
                            @foreach ($branches as $key => $branch)
                                @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                                    <th colspan="2" class="text-center">{{ $branch }}</th>
                                @endif
                            @endforeach
                            <th colspan="2" class="text-center">{{ __('Total') }}</th>
                        </tr>
                        <tr class="table_heads" style="font-size:0.8rem;">
                            <th></th>
                            @foreach ($branches as $key => $branch)
                                @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                                    <th>{{ __('Receipts') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                @endif
                            @endforeach
                            <th>{{ __('Receipts') }}</th>
                            <th>{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $startDate = \Carbon\Carbon::parse($fromDate);
                            $endDate = \Carbon\Carbon::parse($toDate);
                        @endphp

                        @for ($date = $startDate; $date <= $endDate; $date->addDay())
                            @php
                                $hasData = false;
                                $dateTotalReceipts = 0;
                                $dateTotalAmount = 0;
                            @endphp

                            @foreach ($branches as $key => $branch)
                                @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                                    @php
                                        $branchReceipts = $recipts->filter(function ($receipt) use ($date, $key) {
                                            return \Carbon\Carbon::parse($receipt->recipt_date)->isSameDay($date) &&
                                                $receipt->owned_by == $key;
                                        });
                                    @endphp
                                    @if ($branchReceipts->isNotEmpty())
                                        @php
                                            $hasData = true;
                                        @endphp
                                    @endif
                                @endif
                            @endforeach

                            @if ($hasData)
                                <tr style="font-size:0.7rem;">
                                    <td>{{ $date->format('d-M-Y') }}</td>
                                    @foreach ($branches as $key => $branch)
                                        @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                                            @php
                                                $branchReceipts = $recipts->filter(function ($receipt) use (
                                                    $date,
                                                    $key,
                                                ) {
                                                    return \Carbon\Carbon::parse($receipt->recipt_date)->isSameDay(
                                                        $date,
                                                    ) && $receipt->owned_by == $key;
                                                });
                                                $receiptCount = $branchReceipts->count();
                                                $totalAmount = $branchReceipts->sum(function ($receipt) {
                                                    return $receipt->voucher->sum('credit');
                                                });
                                                $dateTotalReceipts += $receiptCount;
                                                $dateTotalAmount += $totalAmount;
                                            @endphp
                                            <td>{{ $receiptCount }}</td>
                                            <td>{{ $totalAmount }}</td>
                                        @endif
                                    @endforeach
                                    <td>{{ $dateTotalReceipts }}</td>
                                    <td>{{ $dateTotalAmount }}</td>
                                </tr>
                            @endif
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
