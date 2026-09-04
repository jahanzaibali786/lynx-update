@extends('layouts.admin')
@section('page-title')
    {{ __('Fee Receipt Summary') }}
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script>
        function branchcustomer(id) {
            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: "{{ route('branch.session_class') }}",
                type: "POST",
                data: { id: id },
                dataType: 'json',
                success: function(result) {
                    if (result.status == 'success') {
                        var $classSelect = $('#class_select');
                        if ($classSelect[0] && $classSelect[0].customSelectInstance) {
                            $classSelect[0].customSelectInstance.destroy();
                            delete $classSelect[0].customSelectInstance;
                        }
                        if ($classSelect.next('.custom-select-wrapper').length) {
                            $classSelect.next('.custom-select-wrapper').remove();
                        }
                        $classSelect.removeClass('custom-select');
                        $classSelect.empty();
                        $classSelect.append($('<option>', { value: 'all', text: 'All Class' }));
                        for (var j = 0; j < result.class.length; j++) {
                            $classSelect.append($('<option>', { value: result.class[j].id, text: result.class[j].name }));
                        }
                        $classSelect.addClass('custom-select').show();
                        if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                            window.CustomSelect.create($classSelect[0]);
                        }
                        $('#sessionselect').empty();
                        $('#sessionselect').append($('<option>', { value: 'all', text: 'All Session' }));
                        for (var i = 0; i < result.session.length; i++) {
                            $('#sessionselect').append($('<option>', { value: result.session[i].id, text: result.session[i].title }));
                        }
                    }
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
    <div class="float-end"></div>
@endsection

@section('content')
    <style>
        .table_heads { line-height: 0.1rem !important; }
        .trNew { line-height: 0.2rem !important; }
        .maximumHeightNew { max-height: 100vh; overflow-y: auto; }
        .sticky-headerNew { position: sticky; top: 0; background-color: white; z-index: 1; }
    </style>

    @php
        $fromDate       = request()->get('start_date') ?? date('Y-m-d', strtotime('-1 month'));
        $toDate         = request()->get('end_date')   ?? date('Y-m-d');
        $selectedBranch = request()->get('branches');

        $dateRange = new \DatePeriod(
            \Carbon\Carbon::parse($fromDate),
            new \DateInterval('P1D'),
            \Carbon\Carbon::parse($toDate)->addDay()
        );

        // -------------------------------------------------------
        // DEDUPLICATION: one voucher_id per branch per date
        // Multiple StudentReceipt rows can share the same
        // voucher_id (one row per journal line). Grouping by
        // voucher_id ensures each payment is counted only once,
        // mirroring the detail view's groupBy('voucher_id') logic.
        // -------------------------------------------------------
        $deduplicatedReceipts = $recipts
            ->groupBy('owned_by')
            ->flatMap(function ($branchReceipts) {
                return $branchReceipts
                    ->groupBy('voucher_id')
                    ->map(function ($voucherGroup) {
                        // Keep the first row as the representative record;
                        // sum credit across all voucher lines for the amount.
                        $first  = $voucherGroup->first();
                        $amount = $first->voucher->sum('credit');

                        // Clone-like stdClass so downstream code stays identical
                        return (object) [
                            'owned_by'    => $first->owned_by,
                            'recipt_date' => $first->recipt_date,
                            'voucher'     => $first->voucher,
                            '_amount'     => $amount,   // pre-calculated, avoids re-summing
                        ];
                    });
            });

        $branchTotals       = [];
        $grandTotalReceipts = 0;
        $grandTotalAmount   = 0;
        $processedReceipts  = [];

        foreach ($deduplicatedReceipts as $receipt) {
            $dateKey   = \Carbon\Carbon::parse($receipt->recipt_date)->format('Y-m-d');
            $branchKey = $receipt->owned_by;
            $amount    = $receipt->_amount;

            // Build date → branch matrix
            if (!isset($processedReceipts[$dateKey][$branchKey])) {
                $processedReceipts[$dateKey][$branchKey] = ['count' => 0, 'amount' => 0];
            }
            $processedReceipts[$dateKey][$branchKey]['count']++;
            $processedReceipts[$dateKey][$branchKey]['amount'] += $amount;

            // Branch totals
            if (!isset($branchTotals[$branchKey])) {
                $branchTotals[$branchKey] = ['receipts' => 0, 'amount' => 0];
            }
            $branchTotals[$branchKey]['receipts']++;
            $branchTotals[$branchKey]['amount'] += $amount;

            // Grand totals
            $grandTotalReceipts++;
            $grandTotalAmount += $amount;
        }
    @endphp

    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding: 12px;">
                        {{ Form::open(['route' => ['fee_receipt_summary'], 'method' => 'GET', 'id' => 'student_receipt_list']) }}
                        <div class="row d-flex justify-content-end" style="width: 100%">
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : '', ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, $selectedBranch, ['class' => 'form-control select custom-select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('default_bank', __('Bank'), ['class' => 'form-label']) }}
                                    {{ Form::select('default_bank', $accounts, request()->get('default_bank'), ['class' => 'form-control select custom-select', 'id' => 'default_bank']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex gap-2 align-items-center">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('student_receipt_list').submit(); return false;"
                                    data-bs-title="Search">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                        <li>
                                            <button class="dropdown-item" type="button"
                                                onclick="window.location.href='{{ route('fee_receipt_summary.report', array_merge(request()->all(), ['export' => 'excel'])) }}'">
                                                <i class="ti ti-file me-2"></i>Excel
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" type="button"
                                                onclick="window.location.href='{{ route('fee_receipt_summary.report', array_merge(request()->all(), ['export' => 'pdf'])) }}'">
                                                <i class="ti ti-download me-2"></i>PDF
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-4 pt-0" id="studentfeereceipt">
        <div style="width: 100%; text-align: center;">
            <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center; margin-bottom: 0;">
                <b>The Lynx School</b>
            </p>
            <p style="font-size:1rem; text-align: center; font-weight: 800; margin-top: -10px;">
                Fee Receipt Summary
            </p>
            <p>{{ @$bank_accounts->holder_name ?? 'All Banks' }}</p>
        </div>
        <div class="d-flex justify-content-between">
            <p><b>From Date: </b>{{ $fromDate }}</p>
            <p style="padding-left:100px;"><b>To Date: </b>{{ $toDate }}</p>
        </div>
        <div class="table-responsive maximumHeightNew">
            <table class="table datatable">
                <thead class="sticky-headerNew">
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
                    @foreach ($dateRange as $date)
                        @php
                            $dateKey           = $date->format('Y-m-d');
                            $hasData           = isset($processedReceipts[$dateKey]);
                            $dateTotalReceipts = 0;
                            $dateTotalAmount   = 0;
                        @endphp

                        @if ($hasData)
                            <tr class="trNew" style="font-size:0.7rem;">
                                <td>{{ $date->format('d-M-Y') }}</td>
                                @foreach ($branches as $key => $branch)
                                    @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                                        @php
                                            $branchData         = $processedReceipts[$dateKey][$key] ?? ['count' => 0, 'amount' => 0];
                                            $dateTotalReceipts += $branchData['count'];
                                            $dateTotalAmount   += $branchData['amount'];
                                        @endphp
                                        <td>{{ $branchData['count'] }}</td>
                                        <td>{{ number_format($branchData['amount'], 0) }}</td>
                                    @endif
                                @endforeach
                                <td>{{ $dateTotalReceipts }}</td>
                                <td>{{ number_format($dateTotalAmount, 0) }}</td>
                            </tr>
                        @endif
                    @endforeach

                    <tr class="trNew" style="font-weight: bold; background-color: #f0f0f0; font-size: 0.8rem; border-top: 2px solid black;">
                        <td>{{ __('TOTAL') }}</td>
                        @foreach ($branches as $key => $branch)
                            @if ($key !== '' && ($selectedBranch === null || $selectedBranch == $key))
                                <td>{{ $branchTotals[$key]['receipts'] ?? 0 }}</td>
                                <td>{{ number_format($branchTotals[$key]['amount'] ?? 0, 0) }}</td>
                            @endif
                        @endforeach
                        <td>{{ $grandTotalReceipts }}</td>
                        <td>{{ number_format($grandTotalAmount, 0) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection