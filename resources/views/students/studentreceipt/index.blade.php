@extends('layouts.admin')
@section('page-title')
    {{ __('Daily CMR Statement') }}
@endsection
@push('script-page')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="{{ asset('js/jquery.min.js') }}"></script>l
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
@endpush
@section('breadcrumb')
    <style>
        /* variables */
        :root {
            --primary: #100773;
        }

        th,
        td {
            padding: 8px 4px !important;
        }

        .font_less {
            font-size: 11px;
        }

        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        .searchBoxElement{
            z-index : 1000 !important;
        }
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            opacity: 1;
        }

        /* ── Entry card ── */
        #entry-card {
            border: 2px solid var(--primary);
            border-radius: 8px;
            margin-bottom: 16px;
        }

        #entry-card .entry-card-header {
            background: linear-gradient(90deg, var(--primary), var(--primary));
            color: #fff;
            font-weight: 600;
            padding: 7px 14px;
            border-radius: 6px 6px 0 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        #challan-searching {
            display: none;
            font-size: 12px;
            color: #ffffffcc;
        }

        /* ── Make entry inputs look identical to the table inputs below ── */
        .entry-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 3px 0;
        }

        .entry-table th {
            font-size: 11px;
            color: #444;
            font-weight: 600;
            padding: 4px 3px !important;
            white-space: nowrap;
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .entry-table td {
            padding: 4px 3px !important;
            vertical-align: middle;
        }

        /* match the plain borderless input style used in the records table */
        .entry-table input[type="text"],
        .entry-table input[type="date"],
        .entry-table input[type="number"],
        .entry-table select {
            border: 1px solid #ced4da;
            border-radius: 3px;
            padding: 2px 4px;
            font-size: 11px;
            width: 100%;
            background: #fff;
            color: #212529;
        }

        .entry-table input[disabled],
        .entry-table select[disabled] {
            background: #f4f4f4;
            color: #888;
        }

        .entry-table input#challan_id {
            font-size: 12px;
            font-weight: 600;
            border-color: var(--primary);
            outline: none;
        }

        .entry-table input#challan_id:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(78, 115, 223, .2);
        }

        /* detail section inside entry card */
        #detailcard {
            margin-top: 10px;
        }

        #detailcard hr {
            margin: 6px 0;
        }

        /* late fee exempt notice */
        #late-fee-exempt-notice {
            display: none;
            font-size: 11px;
            color: #856404;
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 3px;
            padding: 2px 6px;
            margin-top: 2px;
        }

        /* Tab styles */
        .nav-tabs .nav-link {
            color: #100773;
            font-weight: 600;
            border: 2px solid transparent;
            border-bottom: none;
            cursor: pointer;
        }
        .nav-tabs .nav-link.active {
            color: #100773;
            background-color: #fff;
            border-color: #100773 #100773 #fff;
            border-bottom: 2px solid #fff;
        }
        .nav-tabs .nav-link:hover {
            color: #100773;
            border-color: #100773 #100773 #fff;
        }
        .tab-content {
            padding-top: 10px;
        }

        /* Searching animation */
        #reference-searching {
            display: none;
            font-size: 12px;
            color: #100773;
            margin-left: 10px;
        }
        #reference-searching i {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Expandable student row styles */
        .student-row {
            cursor: pointer;
            background-color: #aaaaaa;
            font-weight: 600;
        }
        .student-row:hover {
            background-color: #a3adb7;
        }
        .student-row .expand-icon {
            transition: transform 0.2s;
        }
        .student-row.expanded .expand-icon {
            transform: rotate(90deg);
        }
        .receipt-details-row {
            display: none;
            background-color: #fff;
        }
        .receipt-details-row.show {
            display: table-row;
        }
        .receipt-details-table {
            width: 100%;
            margin: 0;
            font-size: 11px;
        }
        .receipt-details-table th {
            background-color: #dddddd;
            padding: 6px 8px;
            font-weight: 600;
            border-bottom: 1px solid #dee2e6;
        }
        .receipt-details-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Daily CMR Statement') }}</li>
@endsection
@section('action-btn')
@endsection
@section('content')
    {{-- ══════════════════════════════════════════════════════════
         TABS
    ══════════════════════════════════════════════════════════ --}}
    <ul class="nav nav-tabs" id="studentReceiptTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="daily-cmr-tab" data-bs-toggle="tab" data-bs-target="#daily-cmr" type="button" role="tab" aria-controls="daily-cmr" aria-selected="true">Daily CMR Statement</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="search-reference-tab" data-bs-toggle="tab" data-bs-target="#search-reference" type="button" role="tab" aria-controls="search-reference" aria-selected="false">Search by Reference</button>
        </li>
    </ul>

    <div class="tab-content" id="studentReceiptTabsContent">
        {{-- ══════════════════════════════════════════════════════════
             TAB 1: DAILY CMR STATEMENT
        ══════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade show active" id="daily-cmr" role="tabpanel" aria-labelledby="daily-cmr-tab">
    {{-- ══════════════════════════════════════════════════════════
         FILTER BAR
    ══════════════════════════════════════════════════════════ --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding:12px;">
                        {{ Form::open(['route' => ['student_receipt.index'], 'method' => 'GET', 'id' => 'student_receipt_submit']) }}
                        <div class="row d-flex justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                {{ Form::label('default_date', __('Default Date'), ['class' => 'form-label']) }}
                                {{ Form::date('date', isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'), ['class' => 'form-control', 'id' => 'default_date']) }}
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                {{ Form::label('default_bank', __('Default Bank'), ['class' => 'form-label']) }}
                                                                 {{ Form::select('default_bank', $accounts, request('default_bank'), ['class' => 'form-control custom-select', 'id' => 'default_bank', 'required' => 'required']) }}
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('student_receipt_submit').submit(); return false;">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('student_receipt.index') }}" class="btn mx-1 btn-sm btn-outline-danger">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <div class="dropdown d-inline-block mx-1">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">Export</button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                        <li><button class="dropdown-item" type="submit" name="export" value="excel"><i
                                                            class="ti ti-file me-2"></i>Excel</button></li>
                                        <li><button class="dropdown-item" type="submit" name="export" value="pdf"><i
                                                            class="ti ti-download me-2"></i>Pdf</button></li>
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
    <div class="col-12" id="entry-card">
        <div class="entry-card-header">
            <i class="fa fa-plus-circle"></i>
            <span>New Receipt Entry</span>
            <span id="challan-searching"><i class="fa fa-spinner fa-spin"></i> Searching…</span>
        </div>
        <div style="padding:10px 12px;">

            <div class="table-responsive">
                <table class="entry-table">
                    <thead>
                        <tr>
                            <th style="width:80px;">Rpt Date</th>
                            <th style="width:68px;">Challan No.</th>
                            <th style="width:62px;">Rpt Amt</th>
                            <th style="width:68px;">Challan Amt</th>
                            <th style="width:56px;">Late Amt</th>
                            <th style="width:56px;">Arrears</th>
                            <th style="width:65px;">Total Fee</th>
                            <th style="width:68px;">Rem. Fee</th>
                            <th style="width:108px;">Bank Account</th>
                            <th style="width:72px;">D Status</th>
                            <th style="width:88px;">Reference</th>
                            <th style="width:110px;">Received By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <input type="date" id="recipt_date"
                                    value="{{ isset($_GET['date']) ? $_GET['date'] : date('Y-m-d') }}">
                            </td>
                            <td>
                                <input type="text" id="challan_id" value="" placeholder="Enter…"
                                    autocomplete="off">
                                <span id="challan-searching"><i class="fa fa-spinner fa-spin"></i> Searching…</span>
                                <span id="challan-not-found" style="display:none; color:#dc3545;">Not found</span>
                            </td>
                            <td><input type="text" id="remp_amt" value="" disabled></td>
                            <td><input type="text" id="challan_amt" value="" disabled></td>
                            <td>
                                <input type="text" id="late_amt" value="0" disabled>
                                <div id="late-fee-exempt-notice">Late fee exempt</div>
                            </td>
                            <td><input type="text" id="arrears" value="" disabled></td>
                            <td><input type="text" id="total_fee" value="" disabled></td>
                            <td><input type="text" id="rem_fee" value="" disabled></td>
                            <td>
                                {{ Form::select('default_bank', $accounts, null, [
                                    'id' => 'static_bank',
                                    'disabled' => 'disabled',
                                    'style' => 'width:100%;',
                                ]) }}
                            </td>
                            <td>
                                <select name="receive_type" disabled>
                                    <option value="DD">DD</option>
                                    <option value="OL">OL</option>
                                    <option value="CHQ">CHQ</option>
                                    <option value="CD">CD</option>
                                </select>
                            </td>
                            <td><input type="text" value="" disabled></td>
                            <td><input type="text" value="{{ Auth::user()->name }}" disabled></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Challan detail — hidden until search returns a result --}}
            <div id="detailcard" style="display:none;">
                <div id="siblingContainer" style="display:none; margin-bottom:6px;"></div>
                <hr>
                <div class="row" style="padding:0 8px;">
                    <div id="headfee" class="col-md-6 pb-4"></div>
                    <div id="arrearsdetails" class="col-md-6 pb-4"></div>
                </div>
            </div>

        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         RECORDS TABLE
    ══════════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <table class="datatable">
            <thead class="table_heads">
                <tr>
                    <th>Rpt No.</th>
                    <th>Rpt Date</th>
                    <th>Challan No.</th>
                    <th>Rpt Amt</th>
                    <th>Challan Amt</th>
                    <th>Late Amt</th>
                    <th>Arrears</th>
                    <th>Total Fee</th>
                    <th>Rem. Fee</th>
                    <th>Bank Account</th>
                    <th>D Status</th>
                    <th>Reference</th>
                    <th>Received</th>
                    @if (Auth::user()->type == 'company')
                        <th>Action</th>
                    @endif
                </tr>
            </thead>
            @php $options = ['DD', 'OL', 'CHQ', 'CD']; @endphp
            <tbody id="new_data">
                @foreach ($recipts as $recipt)
                    @php
                        $totalFee = @$recipt->challan_amount + @$recipt->late_amount + @$recipt->arrears;
                        $remainingFee = $totalFee - @$recipt->recipt_amount;
                    @endphp

                    <tr style="border-radius:10px !important;">

                        <td>
                            <span style="font-size:11px;">
                                {{ @$recipt->id }}
                            </span>
                        </td>

                        <td>
                            <span class="font_less" style="font-size:11px;">
                                {{ date('d/m/Y', strtotime($recipt->recipt_date)) }}
                            </span>
                        </td>

                        <td>
                            <span style="font-size:12px;">
                                {{ @$recipt->challan->challanNo }}
                            </span>
                        </td>

                        <td>
                            <span style="font-size:13px;">
                                {{ @$recipt->recipt_amount }}
                            </span>
                        </td>

                        <td>
                            <span style="font-size:13px;">
                                {{ @$recipt->challan_amount }}
                            </span>
                        </td>

                        <td>
                            <span style="font-size:13px;">
                                {{ @$recipt->late_amount }}
                            </span>
                        </td>

                        <td>
                            <span style="font-size:13px;">
                                {{ @$recipt->arrears }}
                            </span>
                        </td>

                        <td>
                            <span style="font-size:12px;">
                                {{ $totalFee }}
                            </span>
                        </td>

                        <td>
                            <span style="font-size:12px;">
                                {{ $remainingFee }}
                            </span>
                        </td>

                        <td>
                            <span style="font-size:12px;">
                                {{ $accounts[@$recipt->bank_id] ?? '-' }}
                            </span>
                        </td>

                        <td>
                            <span style="font-size:12px;">
                                {{ @$recipt->receive_type }}
                            </span>
                        </td>

                        <td>
                            <span style="font-size:11px;">
                                {{ @$recipt->referance }}
                            </span>
                        </td>

                        <td>
                            <span style="font-size:11px;">
                                {{ @$recipt->received->name }}
                            </span>
                        </td>

                        @if (Auth::user()->type == 'company')
                            <td>
                                <a href="#!" data-size="lg"
                                    data-url="{{ route('student_receipt.edit', $recipt->id) }}" data-ajax-popup="true"
                                    class="btn btn-sm btn-outline-primary" data-bs-title="{{ __('Edit') }}">

                                    <span class="btn-inner--icon">
                                        <i class="ti ti-pencil"></i>
                                    </span>

                                </a>
                            </td>
                        @endif

                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($recipts->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $recipts->appends(['date' => request('date'), 'default_bank' => request('default_bank')])->links() }}
            </div>
        @endif
    </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════
             TAB 2: SEARCH BY REFERENCE
        ══════════════════════════════════════════════════════════ --}}
        <div class="tab-pane fade" id="search-reference" role="tabpanel" aria-labelledby="search-reference-tab">
            <div class="row">
                <div class="col-sm-12">
                    <div class="mt-2">
                        <div class="card">
                            <div class="card-body" style="padding:12px;">
                                <div class="row d-flex justify-content-end">
                                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                        {{ Form::label('reference', __('Reference'), ['class' => 'form-label']) }}
                                        <div class="input-group">
                                            {{ Form::text('reference', old('reference', $reference ?? ''), ['class' => 'form-control', 'placeholder' => 'Enter reference number', 'id' => 'search_reference_input']) }}
                                            <span id="reference-searching"><i class="fa fa-spinner fa-spin"></i> Searching…</span>
                                        </div>
                                    </div>
                                    <div class="col-auto float-end ms-2 mt-4">
                                        <button type="button" id="btn_search_reference" class="btn mx-1 btn-sm btn-outline-primary">
                                            <span class="btn-inner--icon">Search</span>
                                        </button>
                                        <button type="button" id="btn_clear_reference" class="btn mx-1 btn-sm btn-outline-danger">
                                            <span class="btn-inner--icon">Clear</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="search_results_container" style="display:none;">
                <div class="row mt-3">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <h5>Search Results</h5>
                                <p><strong>Reference:</strong> <span id="result_reference"></span></p>
                                <p><strong>Total Students Found:</strong> <span id="result_total"></span></p>
                                <hr>
                                <div id="results_table_container"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         ARREARS MODAL
    ══════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="arrearModal" tabindex="-1" aria-labelledby="arrearModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="arrearModalLabel">Arrear Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalHeadsContainer"></div>
                    <div class="pt-2 modalHeadsData"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="oldsaveButton" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let accountOptions = '';
        let all_accountOptions = '';
        let accountsData = {};
        let accountAllData = {};
        let defaultBankId = null;
        let challanXHR = null; // tracks in-flight XHR so we can abort it

        // ── Challan meta stored after search ─────────────────────────────
        let currentChallanMeta = {
            challanType: '', // 'regular' | other
            dueDate: '', // 'YYYY-MM-DD'
            totalAmount: 0,
            concession: 0,
            lateFeeAmount: 0,
            paidAmount: 0,
        };

        // ── PHP-side auth flag passed to JS ───────────────────────────────
        const IS_COMPANY_USER = {{ \Auth::user()->type == 'company' ? 'true' : 'false' }};

        // ── On ready ─────────────────────────────────────────────────────
        $(document).ready(function() {
            $('#default_date').on('change', function() {
                $('#recipt_date').val($(this).val());
                // Recalculate late fee when date changes and a challan is loaded
                if ($('#challan_id').val()) {
                    recalculateLateFee();
                }
                var bankId = $('#bank').length ? $('#bank').val() : $('#default_bank').val();
                validateCashAccountDate(bankId, $(this).val());
            });
            setTimeout(function() {
                $('#challan_id').focus();
            }, 300);

            // Switch to search by reference tab if reference is present
            @if(isset($reference) && !empty($reference))
                $('#search-reference-tab').tab('show');
            @endif
        });

        // ── Tab switching helper ───────────────────────────────────────────
        function switchToDailyTab() {
            $('#daily-cmr-tab').tab('show');
        }

        // ── Reference search AJAX ───────────────────────────────────────────
        let referenceSearchXHR = null;

        $(document).on('click', '#btn_search_reference', function() {
            var reference = $('#search_reference_input').val().trim();

            if (!reference) {
                show_toastr('error', 'Please enter a reference number', 'error');
                return;
            }

            // Abort previous request if still running
            if (referenceSearchXHR) {
                referenceSearchXHR.abort();
                referenceSearchXHR = null;
            }

            // Show searching animation
            $('#reference-searching').show();
            $('#btn_search_reference').prop('disabled', true);

            // Hide previous results
            $('#search_results_container').hide();

            referenceSearchXHR = $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('student_receipt.search_by_reference') }}",
                type: "GET",
                data: {
                    reference: reference
                },
                dataType: 'json',
                success: function(response) {
                    referenceSearchXHR = null;
                    $('#reference-searching').hide();
                    $('#btn_search_reference').prop('disabled', false);

                    if (response.success) {
                        $('#result_reference').text(response.reference);
                        $('#result_total').text(response.total_students);

                        if (response.total_students > 0) {
                            var tableHtml = '<table class="datatable">' +
                                '<thead class="table_heads">' +
                                '<tr>' +
                                '<th></th>' +
                                '<th>Branch</th>' +
                                '<th>Student Name</th>' +
                                '<th>Roll No</th>' +
                                '<th>Father Name</th>' +
                                '<th>Class</th>' +
                                '</tr>' +
                                '</thead>' +
                                '<tbody>';

                            response.results.forEach(function(student, index) {
                                var studentRowId = 'student_row_' + index;
                                var receiptRowId = 'receipt_row_' + index;
                                
                                tableHtml += '<tr class="student-row" id="' + studentRowId + '" data-receipt-row="' + receiptRowId + '">' +
                                    '<td style="width: 30px;"><i class="fa fa-chevron-right expand-icon"></i></td>' +
                                    '<td>' + student.branch + '</td>' +
                                    '<td>' + student.name + '</td>' +
                                    '<td>' + student.roll_no + '</td>' +
                                    '<td>' + student.fathername + '</td>' +
                                    '<td>' + student.class + '</td>' +
                                    '</tr>';
                                
                                // Receipt details row (hidden by default)
                                tableHtml += '<tr class="receipt-details-row" id="' + receiptRowId + '">' +
                                    '<td colspan="6">' +
                                    '<table class="receipt-details-table">' +
                                    '<thead>' +
                                    '<tr>' +
                                    '<th>Date</th>' +
                                    '<th>Ch. Type</th>' +
                                    '<th>Challan No</th>' +
                                    '<th>Billing Month</th>' +
                                    '<th>Bank/Cash</th>' +
                                    '<th>Mode</th>' +
                                    '<th>T.Head</th>' +
                                    '<th>Ref.</th>' +
                                    '<th>Rs.</th>' +
                                    '</tr>' +
                                    '</thead>' +
                                    '<tbody>';
                                
                                student.receipts.forEach(function(receipt) {
                                    tableHtml += '<tr>' +
                                        '<td>' + receipt.date + '</td>' +
                                        '<td>' + receipt.challan_type + '</td>' +
                                        '<td>' + receipt.challan_no + '</td>' +
                                        '<td>' + receipt.billing_month + '</td>' +
                                        '<td>' + receipt.bank_name + '</td>' +
                                        '<td>' + receipt.receive_type + '</td>' +
                                        '<td>' + receipt.fee_head + '</td>' +
                                        '<td>' + receipt.reference + '</td>' +
                                        '<td>' + receipt.amount + '</td>' +
                                        '</tr>';
                                });
                                
                                tableHtml += '</tbody></table></td></tr>';
                            });

                            tableHtml += '</tbody></table>';
                            $('#results_table_container').html(tableHtml);
                        } else {
                            $('#results_table_container').html('<p>No student receipts found for this reference.</p>');
                        }

                        $('#search_results_container').show();
                    }
                },
                error: function(xhr, status, error) {
                    referenceSearchXHR = null;
                    $('#reference-searching').hide();
                    $('#btn_search_reference').prop('disabled', false);

                    if (status !== 'abort') {
                        show_toastr('error', 'An error occurred while searching. Please try again.', 'error');
                    }
                }
            });
        });

        // Clear reference search
        $(document).on('click', '#btn_clear_reference', function() {
            $('#search_reference_input').val('');
            $('#search_results_container').hide();
            $('#results_table_container').html('');
            $('#search_reference_input').focus();
        });

        // Allow Enter key to trigger search
        $(document).on('keypress', '#search_reference_input', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#btn_search_reference').click();
            }
        });

        // Expand/collapse student row to show receipt details
        $(document).on('click', '.student-row', function() {
            var $row = $(this);
            var receiptRowId = $row.data('receipt-row');
            var $receiptRow = $('#' + receiptRowId);
            
            $row.toggleClass('expanded');
            $receiptRow.toggleClass('show');
        });

        // ── Receive-type helpers ──────────────────────────────────────────
        function getReceiveTypeOptions(chartAccount) {
            if (!chartAccount) return '';
            const name = chartAccount.toUpperCase();
            if (name.includes('CSH') || name.includes('CASH')) {
                return '<option value="CD">CD</option>';
            }
            return '<option value="DD">DD</option><option value="OL">OL</option><option value="CHQ">CHQ</option>';
        }

        function updateReceiveType(bankId, selectElement) {
            const d = accountAllData[bankId] || accountsData[bankId] || {};
            $(selectElement).html(getReceiveTypeOptions(d.chart_account || ''));
        }

        // ── Validation ────────────────────────────────────────────────────
        function validateAllAmounts() {
            let isValid = true,
                msgs = [];
            $('.ramount').each(function() {
                let inp = parseFloat($(this).val()) || 0;
                let max = parseFloat($(this).closest('.row').find('.tamount').val()) || 0;
                if (inp > max) {
                    isValid = false;
                    msgs.push($(this).closest('.row').find('div:first').text().trim() + ': exceeds max (' + max +
                        ')');
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            $('.oldramount').each(function() {
                let inp = parseFloat($(this).val()) || 0;
                let max = parseFloat($(this).closest('.row').find('.oldtamount').val()) || 0;
                if (inp > max) {
                    isValid = false;
                    msgs.push($(this).closest('.row').find('div:first').text().trim() + ': exceeds max (' + max +
                        ')');
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            if (!isValid) show_toastr('error', 'Please correct:\n' + msgs.join('\n'), 'error');
            return isValid;
        }

        $(document).on('input change keyup', '.ramount', function() {
            let max = parseFloat($(this).closest('.row').find('.tamount').val()) || 0;
            if ((parseFloat($(this).val()) || 0) > max) {
                $(this).val(max);
                show_toastr('warning', 'Amount cannot exceed maximum allowed', 'warning');
            }
            calculateRAmount($(this).closest('.row'));
        });
        $(document).on('input change', '.oldramount', function() {
            let max = parseFloat($(this).closest('.row').find('.oldtamount').val()) || 0;
            if ((parseFloat($(this).val()) || 0) > max) {
                $(this).val(max);
                show_toastr('warning', 'Amount cannot exceed maximum allowed', 'warning');
            }
            calculateOldRAmount($(this).closest('.row'));
        });

        // ══════════════════════════════════════════════════════════════════
        //  LATE FEE CALCULATION  (client-side mirror of server-side logic)
        // ══════════════════════════════════════════════════════════════════

        /**
         * Returns the computed late fee (float) based on current state.
         *
         * Rules:
         *  1. Only 'regular' challan type gets late fee.
         *  2. Company user posting on/before due date → 0.
         *  3. Paid amount (already paid) >= 50% of net payable → 0.
         *     Also if THIS receipt amount will push total >= 50% → 0.
         *  4. OL (Online) → 1 day grace after due date.
         *  5. Otherwise → dailyLateFee × daysOverdue.
         */
        /**
         * Parse a 'YYYY-MM-DD' string into { y, m, d } using LOCAL parts only.
         * Avoids the UTC-midnight timezone shift that new Date('YYYY-MM-DD') causes.
         */
        function parseDateStr(str) {
            if (!str) return null;
            const parts = String(str).split('-');
            if (parts.length !== 3) return null;
            return {
                y: parseInt(parts[0], 10),
                m: parseInt(parts[1], 10),
                d: parseInt(parts[2], 10)
            };
        }

        /**
         * Compare two date strings (YYYY-MM-DD) as plain strings.
         * Returns negative / 0 / positive like a comparator.
         */
        function cmpDateStr(a, b) {
            // String comparison works correctly for ISO date strings
            if (a < b) return -1;
            if (a > b) return 1;
            return 0;
        }

        /**
         * Count calendar days between two 'YYYY-MM-DD' strings (b - a).
         * Uses local Date constructor with explicit y/m/d to avoid UTC shift.
         */
        function daysBetween(fromStr, toStr) {
            const f = parseDateStr(fromStr);
            const t = parseDateStr(toStr);
            if (!f || !t) return 0;
            // Use noon local time to avoid DST edge cases
            const fromMs = new Date(f.y, f.m - 1, f.d, 12, 0, 0).getTime();
            const toMs = new Date(t.y, t.m - 1, t.d, 12, 0, 0).getTime();
            return Math.round((toMs - fromMs) / (1000 * 60 * 60 * 24));
        }

        function computeLateFee(receiveType) {
            // Rule 1 — only 'regular' challan type gets late fee
            const challanType = (currentChallanMeta.challanType || '').toLowerCase().trim();
            if (challanType !== 'regular') {
                return 0;
            }

            const dueDateStr = (currentChallanMeta.dueDate || '').substring(0, 10); // 'YYYY-MM-DD'
            const postDateStr = ($('#recipt_date').val() || '').substring(0, 10);

            if (!dueDateStr || !postDateStr) return 0;

            // Posting on or before due date → no late fee
            if (cmpDateStr(postDateStr, dueDateStr) <= 0) return 0;

            // Days past due: due=8, post=9 → 1 day, post=10 → 2 days, etc.
            let daysLate = daysBetween(dueDateStr, postDateStr);

            // OL (Online) payment type gets a 1-day grace WINDOW (not a deduction):
            // - If posting EXACTLY 1 day late (day 9) → exempt, 0 fee
            // - If posting 2+ days late (day 10+) → charge ALL overdue days (no deduction)
            //   The grace window has closed — you missed it.
            //
            // Examples for OL:
            //   due=8, post=9 (1 day late) → 0 (grace window)
            //   due=8, post=10 (2 days late) → 240 (grace window missed, charge full 2 days)
            //   due=8, post=11 (3 days late) → 360 (charge full 3 days)
            //
            // Non-OL (DD, CHQ, CD) — charge from first day overdue:
            //   due=8, post=9 (1 day late) → 120
            //   due=8, post=10 (2 days late) → 240
            if ((receiveType || '').toUpperCase() === 'OL') {
                if (daysLate === 1) return 0; // ONLY if exactly 1 day late → exempt
                // If 2+ days late → charge full daysLate (no deduction)
            }

            if (daysLate <= 0) return 0;

            // 50% rule — late fee stops accumulating once student pays ≥ 50% of total payable
            // Check if THIS receipt will bring them to the 50% threshold
            // (NOT whether they're already at 50% — late fee keeps growing until they cross 50%)
            const baseTotal = currentChallanMeta.totalAmount - (currentChallanMeta.lateFeeAmount || 0);
            const totalPayable = baseTotal - currentChallanMeta.concession;
                        if (totalPayable <= 0) return 0;

            const alreadyPaid = currentChallanMeta.paidAmount;

            // Calculate what THIS receipt will pay (sum of ramount inputs)
            let thisReceiptAmt = 0;
            document.querySelectorAll('.ramount').forEach(function(el) {
                thisReceiptAmt += parseFloat(el.value) || 0;
            });

            // If THIS payment brings total to ≥ 50% → exempt late fee
            const projectedPaid = alreadyPaid + thisReceiptAmt;
            if (totalPayable > 0 && projectedPaid >= totalPayable * 0.5) return 0;

            // Daily rate — server-provided or fallback 120
            const dailyRate = currentChallanMeta.dailyLateFee || 120;
            const lateFee = Math.round(dailyRate * daysLate * 100) / 100;

            // Max late fee cap = 1200
            return Math.min(lateFee, 1200);
        }

        /** Recalculate & apply late fee to #late_amt and refresh totals. */
        function recalculateLateFee() {
            const receiveType = $('#rec_type').val() || $('#headfee').find('#rec_type').val() || 'DD';
            const lateFee = computeLateFee(receiveType);
            const challanAmt = parseFloat($('#challan_amt').val()) || 0;
            const arrears = parseFloat($('#arrears').val()) || 0;

            $('#late_amt').val(lateFee.toFixed(2));

            // Only show 'exempt' badge when posting is PAST due date but fee is waived
            const dueDateStr = (currentChallanMeta.dueDate || '').substring(0, 10);
            const postDateStr = ($('#recipt_date').val() || '').substring(0, 10);
            const isOverdue = dueDateStr && postDateStr && cmpDateStr(postDateStr, dueDateStr) > 0;
            if (lateFee == 0 && isOverdue) {
                $('#late-fee-exempt-notice').show();
            } else {
                $('#late-fee-exempt-notice').hide();
            }

            // Update total
            $('#total_fee').val((challanAmt + arrears + lateFee).toFixed(2));
            calculateRemainingAmount();
        }

        // Recalculate when receive type changes (OL grace rule)
        $(document).on('change', '#rec_type, #old_rec_type', function() {
            if ($('#challan_id').val()) {
                recalculateLateFee();
            }
        });

        // Recalculate when receipt date changes
        $('#recipt_date').on('change', function() {
            if ($('#challan_id').val()) {
                recalculateLateFee();
            }
            var bankId = $('#bank').length ? $('#bank').val() : $('#default_bank').val();
            validateCashAccountDate(bankId, $(this).val());
        });

        // Recalculate when ramount inputs change (50% threshold check)
        $(document).on('input change keyup', '.ramount', function() {
            recalculateLateFee();
        });

        // ── Challan search — abort previous XHR on every keystroke ───────
        $(document).on('keyup', '#challan_id', function() {
            var challan_id = $.trim($(this).val());

            if (challanXHR) {
                challanXHR.abort();
                challanXHR = null;
            }

            if (challan_id.length < 1) {
                resetEntryDetail();
                return;
            }

            $('#challan-searching').show();
            $('#challan-not-found').hide();

            challanXHR = $.ajax({
                url: '{{ route('challandata_for_receipt') }}',
                type: 'GET',
                data: {
                    challan_id: challan_id
                },
                success: function(response) {
                    challanXHR = null;
                    $('#challan-searching').hide();

                    if (response.challandetail) {
                        var detail = response.challandetail;
                        var arrearsTotal = 0;

                        response.previousUnpaidChallans.forEach(function(a) {
                            arrearsTotal += (a.total_amount - a.concession_amount - a
                                .paid_amount);
                        });

                        var challanNet = detail.total_amount - detail.concession_amount - detail
                            .paid_amount;

                        // Store challan meta for late fee calculations
                        currentChallanMeta = {
                            // Try both possible field names your API might return
                            challanType: detail.challan_type || detail.challanType || detail.type ||
                                '',
                            dueDate: detail.due_date || detail.dueDate || '',
                            lateFeeAmount: parseFloat(response.challan_late_fee) || 0,
                            totalAmount: parseFloat(detail.total_amount) || 0,
                            concession: parseFloat(detail.concession_amount) || 0,
                            paidAmount: parseFloat(detail.paid_amount) || 0,
                            dailyLateFee: parseFloat(response.daily_late_fee) || 120,
                        };
                        $('#challan_amt').val(challanNet);
                        $('#arrears').val(arrearsTotal);
                        $('#remp_amt').val('0.00');

                        accountsData = response.accounts_data || {};
                        accountAllData = response.account_all_data || {};
                        defaultBankId = response.default_bank_id;
                        accountOptions = '';
                        all_accountOptions = '';

                        Object.entries(response.accounts).forEach(([v, t]) => {
                            accountOptions += `<option value="${v}">${t}</option>`;
                        });
                        Object.entries(response.account_all).forEach(([v, t]) => {
                            all_accountOptions += `<option value="${v}">${t}</option>`;
                        });

                        populateSiblingTable(detail);
                        populateHeadFee(response.headsData);
                        populateArrears(response.previousUnpaidChallans);

                        // Calculate late fee AFTER populating heads (receive type now known)
                        // Small delay to let #rec_type render first
                        setTimeout(function() {
                            recalculateLateFee();
                        }, 50);

                        $('#detailcard').show();
                        setTimeout(function() {
                            document.getElementById('detailcard').scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }, 100);

                    } else {
                        resetEntryDetail();
                        $('#challan-not-found').show();
                    }
                },
                error: function(xhr) {
                    if (xhr.statusText == 'abort') return;
                    $('#challan-searching').hide();
                    $('#detailcard').hide();
                    $('#siblingContainer').empty().hide();
                    $('#headfee').empty().hide();
                    $('#arrearsdetails').empty();

                    $('#challan_amt').val('');
                    $('#remp_amt').val('');
                    $('#late_amt').val('0');
                    $('#arrears').val('');
                    $('#total_fee').val('');
                    $('#rem_fee').val('');
                    $('#challan-not-found').show();
                    console.error('Challan search error');
                }
            });
        });

        // ── Remaining amount ──────────────────────────────────────────────
        document.getElementById('remp_amt').addEventListener('keyup', calculateRemainingAmount);

        function calculateRemainingAmount() {
            var totalFee = parseFloat($('#total_fee').val()) || 0;
            var rempAmt = parseFloat($('#remp_amt').val()) || 0;
            if (rempAmt > totalFee) {
                rempAmt = totalFee;
                $('#remp_amt').val(totalFee.toFixed(2));
            }
            $('#rem_fee').val((totalFee - rempAmt).toFixed(2));
        }
        calculateRemainingAmount();

        function calculateRAmount(row) {
            var max = parseFloat(row.find('.tamount').val()) || 0;
            if ((parseFloat(row.find('.ramount').val()) || 0) > max) {
                row.find('.ramount').val(max.toFixed(1));
            }
            let total = 0;
            document.querySelectorAll('.ramount').forEach(el => {
                let v = parseFloat(el.value);
                if (!isNaN(v)) total += v;
            });
            $('#remp_amt').val(total.toFixed(2));
            calculateRemainingAmount();
        }

        $(document).on('keyup', '.ramount', function() {
            calculateRAmount($(this).closest('.row'));
        });
        $(document).on('keyup', '.oldramount', function() {
            calculateOldRAmount($(this).closest('.row'));
        });

        function calculateOldRAmount(row) {
            var max = parseFloat(row.find('.oldtamount').val()) || 0;
            if ((parseFloat(row.find('.oldramount').val()) || 0) > max) {
                row.find('.oldramount').val(max.toFixed(1));
            }
        }

        // ── Full entry card reset (called after save & on empty input) ────
        function resetEntryDetail() {
            $('#detailcard').hide();
            $('#siblingContainer').empty().hide();
            $('#headfee').empty().hide();
            $('#arrearsdetails').empty();

            $('#challan_amt').val('');
            $('#remp_amt').val('');
            $('#late_amt').val('0');
            $('#arrears').val('');
            $('#total_fee').val('');
            $('#rem_fee').val('');
            $('#challan_id').val('');
            $('#challan-searching').hide();
            $('#late-fee-exempt-notice').hide();

            // Reset challan meta
            currentChallanMeta = {
                challanType: '',
                dueDate: '',
                totalAmount: 0,
                concession: 0,
                paidAmount: 0,
                lateFeeAmount: 0,
            };
        }

                // Add user type variable
        const userType = "{{ Auth::user()->type }}";

        function validateCashAccountDate(bankId, dateStr) {
            console.log('[CashDateValidation] Validating... Bank ID:', bankId, 'Date:', dateStr);
            // Skip validation for company users
            if (userType === 'company') {
                console.log('[CashDateValidation] User type is company. Skipping cash account date validation.');
                return true;
            }
            if (!bankId || !dateStr) {
                console.log('[CashDateValidation] Missing bankId or dateStr. Skipping.');
                return true;
            }
            
            var bankData = accountAllData[bankId] || accountsData[bankId] || {};
            var bankChart = (bankData.chart_account || '').toUpperCase();
            var isCashAccount = bankChart.includes('CSH') || bankChart.includes('CASH');
            console.log('[CashDateValidation] Chart Account:', bankChart, '| isCashAccount:', isCashAccount);
            
            if (isCashAccount) {
                var today = new Date();
                var yyyy = today.getFullYear();
                var mm = String(today.getMonth() + 1).padStart(2, '0');
                var dd = String(today.getDate()).padStart(2, '0');
                var todayStr = yyyy + '-' + mm + '-' + dd;

                if (dateStr !== todayStr) {
                    show_toastr('error', 'For cash accounts, receipt date must be today.', 'error');
                    console.log('[CashDateValidation] Validation FAILED. Date not today');
                    $('.saveButton').prop('disabled', true);
                    $('#oldsaveButton').prop('disabled', true);
                    return false;
                }
            }
            console.log('[CashDateValidation] Validation PASSED.');
            $('.saveButton').prop('disabled', false);
            $('#oldsaveButton').prop('disabled', false);
            return true;
        }
            // Duplicate validateCashAccountDate removed to avoid override




        // ── Populate helpers ──────────────────────────────────────────────
        function populateSiblingTable(challandetail) {
            var c = $('#siblingContainer').empty();
            var row = $('<div class="row d-flex justify-content-center" style="padding:0.5rem 1.5rem;">');
            row.append('<div class="col-md-3"><b>Student Name:</b> ' + challandetail.student.stdname + '</div>');
            row.append('<div class="col-md-3"><b>Father Name:</b> ' + challandetail.student.fathername + '</div>');
            row.append('<div class="col-md-3"><b>Issue Date:</b> ' + challandetail.issue_date + '</div>');
            row.append('<div class="col-md-3"><b>Due Date:</b> ' + challandetail.due_date + '</div>');
            c.append(row).show();
        }

        function populateHeadFee(headsData) {
            var c = $('#headfee').empty();
            if (!headsData || headsData.length === 0) {
                c.hide();
                return;
            }

            headsData.forEach(function(head) {
                var row = $('<div class="row">');
                row.append('<div class="col-md-4 mb-1">' + head.head_name +
                    '<input name="head_id[]" type="hidden" value="' + head.head_id + '"></div>');
                row.append(
                    '<div class="col-md-3 mb-1"><input name="tamount[]" class="form-control tamount" type="text" value="' +
                    head.amount + '" disabled></div>');
                row.append(
                    '<div class="col-md-3 mb-1"><input name="ramount[]" class="form-control ramount" style="font-size:13px;" type="number" value="" min="0" step="0.01"></div>'
                    );
                c.append(row);
            });

            var banks_id = defaultBankId || $('#default_bank').val();

            c.append(`
                <div style="display:flex;" class="gap-2 mt-2">
                    <div style="margin-bottom:5px; width:150px;">
                        <strong>Bank Account</strong>
                        <select id="bank" class="form-control" placeholder="Bank Account" style="font-size:12px;">
                            ${all_accountOptions}
                        </select>
                    </div>
                    <div style="margin-bottom:5px;">
                        <strong>D Status</strong>
                        <select class="input form-control" id="rec_type" style="width:100px;" name="receive_type">
                            <option value="DD">DD</option>
                            <option value="OL">OL</option>
                            <option value="CHQ">CHQ</option>
                            <option value="CD">CD</option>
                        </select>
                    </div>
                    <div style="margin-bottom:5px;"><strong>Reference <span style="color:red;">*</span></strong>
                        <input type="text" value="" name="ref" id="ref" class="form-control ref-input" style="width:190px;font-size:11px;">
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-4" style="padding-top:10px;">
                    <button class="btn btn-success saveButton">Save</button>
                </div>
            `);

            $('#bank').val(banks_id);
            updateReceiveType(banks_id, '#rec_type');
            if (window.CustomSelect && typeof window.CustomSelect.initContainer === 'function') {
                window.CustomSelect.initContainer(c[0]);
            }

            $('#bank').on('change', function() {
                updateReceiveType($(this).val(), '#rec_type');
                validateCashAccountDate($(this).val(), $('#recipt_date').val());
            });

            // Recalculate late fee when receive type inside headfee changes
            $('#headfee').on('change', '#rec_type', function() {
                recalculateLateFee();
            });

            c.show();
        }

        function populateArrears(previousUnpaidChallans) {
            var c = $('#arrearsdetails').empty();
            c.append('<h5><strong>Arrears :-</strong></h5><br>');
            $('head').append(
                '<style>@keyframes blink-effect{0%{background-color:#100773;color:#fff;}50%{background-color:#fff;color:#100773;}100%{background-color:#100773;color:#fff;}}</style>'
                );
            previousUnpaidChallans.forEach(function(arrear) {
                var row = $(
                    '<div class="mb-3 arrear-row" style="cursor:pointer;display:flex;flex-direction:row;gap:20px;">'
                    );
                row.append(
                    '<div><b>Challan No:</b> <span style="background-color:#100773;color:#fff;font-weight:bold;padding:2px 5px;border-radius:3px;animation:blink-effect 1s infinite;">' +
                    arrear.challanNo + '</span></div>');
                row.append('<div><b>Amount:</b> ' + (arrear.total_amount - arrear.concession_amount - arrear
                    .paid_amount) + '</div>');
                row.append('<div><b>Due Date:</b> ' + arrear.due_date + '</div>');
                row.data('arrear', arrear);
                row.on('click', function() {
                    populateModalHeads($(this).data('arrear'));
                    $('#arrearModal').modal('show');
                });
                c.append(row);
            });
        }

        function populateModalHeads(arrearData) {
            var mhc = $('#modalHeadsContainer').empty();
            var mhd = $('.modalHeadsData').empty();
            var row = $('<div class="row">');
            row.append(
                '<div class="col-md-4 mb-1">Reference <span style="color:red;">*</span></div><input name="old_challan_id" class="old_ch_id" type="hidden" value="' +
                arrearData.challanNo + '">');
            var bankOpts = (arrearData.owned_by == "{{ Auth::user()->ownedId() }}") ? all_accountOptions : accountOptions;
            row.append(`
                <div class="col-md-8 mb-1"><input type="text" value="" id="oldref" class="old_ref form-control old-ref-input" style="font-size:13px;" /></div>
                <div style="display:flex;" class="gap-4">
                    <div style="margin-bottom:5px; width:280px;">
                        <strong>Bank Account</strong>
                        <select id="old_bank" name="default_bank" class="form-control old_banks" placeholder="Bank Account" style="font-size:12px;">
                            ${bankOpts}
                        </select>
                    </div>
                    </div>
                    <div style="margin-bottom:5px;"><strong>D Status</strong>
                        <select class="input form-control old_rec_types" id="old_rec_type" style="width:150px;" name="receive_type">
                            <option value="DD">DD</option><option value="OL">OL</option>
                            <option value="CHQ">CHQ</option><option value="CD">CD</option>
                        </select>
                    </div>
                </div>
            `);
            mhd.append(row);

            arrearData.heads.forEach(function(head) {
                if (head.price - head.concession - head.paid != 0) {
                    var headRow = $('<div class="row mb-3">');
                    headRow.append('<div class="col-md-4">' + head.fee_head.fee_head +
                        '</div><input name="oldhead_id[]" type="hidden" value="' + head.head_id + '">');
                    headRow.append(
                        '<div class="col-md-4"><input name="oldtamount[]" class="form-control oldtamount" type="text" value="' +
                        (head.price - head.concession - head.paid) + '" disabled></div>');
                    headRow.append(
                        '<div class="col-md-4"><input name="oldramount[]" class="form-control oldramount" type="number" value="" min="0" step="0.01"></div>'
                        );
                    mhc.append(headRow);
                }
            });

            var banks_id = $('#bank').val() || defaultBankId || $('#default_bank').val();
            $('#old_bank').val(banks_id);
            // Auto-sync default bank on page load if a value is already selected
            if ($('#default_bank').val()) {
                $('#default_bank').trigger('change');
            }
            const d = accountAllData[banks_id] || accountsData[banks_id] || {};
            $('#old_rec_type').html(getReceiveTypeOptions(d.chart_account || ''));
            if (window.CustomSelect && typeof window.CustomSelect.initContainer === 'function') {
                window.CustomSelect.initContainer(mhd[0]);
            }
            $('#old_bank').off('change').on('change', function() {
                updateReceiveType($(this).val(), '#old_rec_type');
                validateCashAccountDate($(this).val(), $('#recipt_date').val());
            });
        }
        
        function handleDefaultBankChange() {
            var sel = $(this).val();
            console.log('[BankSync] #default_bank changed. Selected value:', sel);
            
            if ($('#static_bank').length) {
                console.log('[BankSync] Updating #static_bank with value:', sel);
                if ($('#static_bank')[0].customSelectInstance) {
                    $('#static_bank')[0].customSelectInstance.setValue(sel);
                } else {
                    $('#static_bank').val(sel).trigger('change');
                }
            } else {
                console.log('[BankSync] #static_bank not found.');
            }
            
            if ($('#bank').length) {
                console.log('[BankSync] Updating #bank with value:', sel);
                if ($('#bank')[0].customSelectInstance) {
                    $('#bank')[0].customSelectInstance.setValue(sel);
                } else {
                    $('#bank').val(sel).trigger('change');
                }
            } else {
                console.log('[BankSync] #bank not found.');
            }
            
            if ($('#old_bank').length) {
                console.log('[BankSync] Updating #old_bank with value:', sel);
                if ($('#old_bank')[0].customSelectInstance) {
                    $('#old_bank')[0].customSelectInstance.setValue(sel);
                } else {
                    $('#old_bank').val(sel).trigger('change');
                }
            } else {
                console.log('[BankSync] #old_bank not found.');
            }
            
            validateCashAccountDate(sel, $('#recipt_date').val());
        }

        $('#default_bank').on('change', handleDefaultBankChange);
        $(document).on('change', '#default_bank', handleDefaultBankChange);

                // Auto‑select default bank on page load if query parameter is present
        if ($('#default_bank').val()) {
            $('#default_bank').trigger('change');
        }
        $(document).on('click', '.saveButton', function(event) {
            event.preventDefault();
            var $btn = $(this).prop('disabled', true);

            if (!validateAllAmounts()) {
                $btn.prop('disabled', false);
                return;
            }

            if (!validateCashAccountDate($('#bank').val(), $('#recipt_date').val())) {
                $btn.prop('disabled', false);
                return;
            }

            // Final late fee recalculation before save
            recalculateLateFee();

            var formData = {
                head_id: [],
                tamount: [],
                ramount: [],
                challan_id: $('#challan_id').val(),
                challan_amt: $('#challan_amt').val(),
                recipt_date: $('#recipt_date').val(),
                recipt_amt: $('#remp_amt').val(),
                late_amt: $('#late_amt').val(),
                arrears: $('#arrears').val(),
                bank: $('#bank').val(),
                ref: $.trim($('#headfee').find('.ref-input').val()),
                receive_type: $('#rec_type').val(),
            };

            $('input[name="head_id[]"]').each(function() {
                formData.head_id.push($(this).val());
            });
            $('input[name="tamount[]"]').each(function() {
                formData.tamount.push($(this).val());
            });
            $('input[name="ramount[]"]').each(function() {
                formData.ramount.push($(this).val());
            });

            if (formData.recipt_amt <= 0) {
                show_toastr('error', 'Receipt Amount must be greater than 0', 'error');
                $btn.prop('disabled', false);
                return;
            }
            if (!formData.bank) {
                show_toastr('error', 'Please select a valid bank', 'error');
                $btn.prop('disabled', false);
                return;
            }
            if (!formData.ref) {
                show_toastr('error', 'Reference field is required', 'error');
                $('#headfee').find('.ref-input').focus();
                $btn.prop('disabled', false);
                return;
            }

            $.ajax({
                url: 'paidchallan',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: formData,
                success: function(response) {
                    resetEntryDetail();
                    $('#new_data').prepend(response.data);
                    $('#challan_id').focus();
                    $btn.prop('disabled', false);
                },
                error: function(xhr) {
                    alert('Something Went Wrong...');
                    console.error(xhr.responseJSON ? xhr.responseJSON.error : 'Unknown error');
                    $btn.prop('disabled', false);
                }
            });
        });

        // ── Arrears save ──────────────────────────────────────────────────
        $(document).on('click', '#oldsaveButton', function(event) {
            event.preventDefault();
            var $btn = $(this).prop('disabled', true);

            if (!validateAllAmounts()) {
                $btn.prop('disabled', false);
                return;
            }

            var mhd = $('#arrearModal').find('.modalHeadsData');
            if (!mhd.length) {
                console.error('No modalHeadsData found.');
                $btn.prop('disabled', false);
                return;
            }

            if (!validateCashAccountDate(mhd.find('.old_banks').val(), $('#recipt_date').val())) {
                $btn.prop('disabled', false);
                return;
            }

            var oldformData = {
                head_id: [],
                tamount: [],
                ramount: [],
                challan_id: mhd.find('.old_ch_id').val(),
                challan_amt: 0,
                recipt_date: $('#recipt_date').val(),
                recipt_amt: 0,
                late_amt: 0, // Arrears late fee always 0 (separate challan logic)
                arrears: 0,
                bank: mhd.find('.old_banks').val(),
                ref: $.trim(mhd.find('.old-ref-input').val()),
                receive_type: mhd.find('.old_rec_types').val(),
            };

            $('input[name="oldhead_id[]"]').each(function() {
                oldformData.head_id.push($(this).val());
            });
            $('input[name="oldtamount[]"]').each(function() {
                oldformData.tamount.push($(this).val());
                oldformData.challan_amt += parseFloat($(this).val()) || 0;
            });
            $('input[name="oldramount[]"]').each(function() {
                oldformData.ramount.push($(this).val());
                oldformData.recipt_amt += parseFloat($(this).val()) || 0;
            });

            if (oldformData.recipt_amt <= 0) {
                show_toastr('error', 'Receipt Amount must be greater than 0', 'error');
                $btn.prop('disabled', false);
                return;
            }
            if (!oldformData.bank) {
                show_toastr('error', 'Please select a valid bank', 'error');
                $btn.prop('disabled', false);
                return;
            }
            if (!oldformData.ref) {
                show_toastr('error', 'Reference field is required', 'error');
                mhd.find('.old-ref-input').focus();
                $btn.prop('disabled', false);
                return;
            }

            $.ajax({
                url: 'paidchallan',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: oldformData,
                success: function() {
                    window.location.reload();
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    alert('Something went wrong!');
                    $btn.prop('disabled', false);
                }
            });
        });
    </script>
@endsection
