@extends('layouts.admin')
@section('page-title')
    {{ $isDailyEntry ?? false ? __('Daily StudyPack Payments') : __('Studypack Receipts') }}
@endsection
@push('script-page')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
@endpush
@section('breadcrumb')
    <style>
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
    </style>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ $isDailyEntry ?? false ? __('Daily StudyPack Payments') : __('Studypack Receipts') }}
    </li>
@endsection
@section('action-btn')
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding:12px;">
                        {{ Form::open(['route' => ['studypackreceipts'], 'method' => 'GET', 'id' => 'studypack_receipt_submit']) }}
                        <div class="row d-flex justify-content-end">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                {{ Form::label('default_date', __('Default Date'), ['class' => 'form-label']) }}
                                {{ Form::date('date', isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'), ['class' => 'form-control', 'id' => 'default_date']) }}
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                {{ Form::label('default_bank', __('Default Bank'), ['class' => 'form-label']) }}
                                {{ Form::select('default_bank', $accounts, $defaultBankId ?? null, ['class' => 'form-control select js-searchBox', 'id' => 'default_bank', 'required' => 'required']) }}
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('studypack_receipt_submit').submit(); return false;">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('studypackreceipts') }}" class="btn mx-1 btn-sm btn-outline-danger">
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
            <span>New StudyPack Receipt Entry</span>
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
                            </td>
                            <td>
                                <div style="display: flex; gap: 2px; align-items: center;">
                                    <input type="number" id="remp_amt" value="" placeholder="0.00" step="any"
                                        min="0" style="width: 65%;">
                                    <button type="button" id="applyAmountBtn" class="btn btn-xs btn-primary"
                                        style="padding: 2px 6px; font-size: 10px; height: 22px;" disabled>Apply</button>
                                </div>
                            </td>
                            <td><input type="text" id="challan_amt" value="" disabled></td>
                            <td><input type="text" id="late_amt" value="0" disabled></td>
                            <td><input type="text" id="arrears" value="" disabled></td>
                            <td><input type="text" id="total_fee" value="" disabled></td>
                            <td><input type="text" id="rem_fee" value="" disabled></td>
                            <td>
                                {{ Form::select('default_bank_display', $accounts, $defaultBankId ?? null, ['disabled' => 'disabled', 'id' => 'default_bank_display', 'style' => 'width:100%;']) }}
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

            <div id="detailcard" style="display:none; margin-top:10px;">
                <div id="siblingContainer" style="display:none; margin-bottom:6px;"></div>
                <hr>
                <div class="row" style="padding:0 8px;">
                    <div id="headfee" class="col-md-6 pb-4"></div>
                    <div id="arrearsdetails" class="col-md-6 pb-4"></div>
                </div>
            </div>
        </div>
    </div>

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
                </tr>
            </thead>
            <tbody id="new_data">
                @foreach ($recipts as $recipt)
@php
                        $totalFee = (@$recipt->challan_amount + @$recipt->late_amount + @$recipt->arrears);
                        $remainingFee = @$recipt->remaining_fee ?? ($totalFee - (@$recipt->challan->paid_amount ?? 0));
                    @endphp
                    <tr>
                        <td><span style="font-size:11px;">{{ @$recipt->id }}</span></td>
                        <td><span class="font_less">{{ date('d/m/Y', strtotime($recipt->recipt_date)) }}</span></td>
                        <td><span style="font-size:12px;">{{ @$recipt->challan->challanNo }}</span></td>
                        <td><span style="font-size:13px;">{{ @$recipt->recipt_amount }}</span></td>
                        <td><span style="font-size:13px;">{{ @$recipt->challan_amount }}</span></td>
                        <td><span style="font-size:13px;">{{ @$recipt->late_amount }}</span></td>
                        <td><span style="font-size:13px;">{{ @$recipt->arrears }}</span></td>
                        <td><span style="font-size:12px;">{{ $totalFee }}</span></td>
                        <td><span style="font-size:12px;">{{ $remainingFee }}</span></td>
                        <td><span style="font-size:12px;">{{ $accounts[@$recipt->bank_id] ?? '-' }}</span></td>
                        <td><span style="font-size:12px;">{{ @$recipt->receive_type }}</span></td>
                        <td><span style="font-size:11px;">{{ @$recipt->referance }}</span></td>
                        <td><span style="font-size:11px;">{{ @$recipt->received->name }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <script>
        $(function() {
            const receiptState = {
                searchTimer: null,
                activeRequest: null,
                accountsData: {},
                accountAllData: {},
                serverDefaultBankId: null,
                pageDefaultBankId: @json($defaultBankId ?? null)
            };

            const challanSearchUrl = @json(route('challandata_for_studypackreceipt'));
            const receiptSaveUrl = @json(route('studypackpaid'));
            const isBranchUser = @json(Auth::user()->type != 'company');

            function toNumber(value) {
                const number = parseFloat(value);
                return Number.isFinite(number) ? number : 0;
            }

            function escapeHtml(value) {
                return $('<div>').text(value ?? '').html();
            }

            function showToast(type, message) {
                if (typeof show_toastr === 'function') {
                    show_toastr(type, message, type);
                    return;
                }

                alert(message);
            }

            function getReceiveTypeOptions(chartAccount) {
                const name = String(chartAccount || '').toUpperCase();

                if (name.includes('CSH') || name.includes('CASH')) {
                    return '<option value="CD">CD</option>';
                }

                return [
                    '<option value="DD">DD</option>',
                    '<option value="OL">OL</option>',
                    '<option value="CHQ">CHQ</option>'
                ].join('');
            }

            function getAccountMeta(bankId) {
                const id = String(bankId || '');

                return receiptState.accountAllData[id] ||
                    receiptState.accountAllData[bankId] ||
                    receiptState.accountsData[id] ||
                    receiptState.accountsData[bankId] ||
                    {};
            }

            function updateReceiveType(bankId, selectElement) {
                const accountMeta = getAccountMeta(bankId);
                const chartAccount = accountMeta.chart_account ||
                    accountMeta.chartAccount ||
                    accountMeta.account_name ||
                    accountMeta.name ||
                    '';

                $(selectElement).html(getReceiveTypeOptions(chartAccount));
            }

            function normalizeAccountEntries(accounts) {
                if (!accounts) {
                    return [];
                }

                if (Array.isArray(accounts)) {
                    return accounts.map(function(account, index) {
                        if (account && typeof account === 'object') {
                            const value = account.id ?? account.value ?? account.bank_id ?? index;
                            const text = account.text ??
                                account.name ??
                                account.account_name ??
                                account.label ??
                                value;

                            return [String(value), String(text)];
                        }

                        return [String(index), String(account)];
                    });
                }

                if (typeof accounts === 'object') {
                    return Object.entries(accounts).map(function(entry) {
                        return [String(entry[0]), String(entry[1])];
                    });
                }

                return [];
            }

            function getTopBankEntries() {
                const entries = [];

                $('#default_bank option').each(function() {
                    const value = String($(this).val() || '');

                    if (value !== '') {
                        entries.push([value, $(this).text()]);
                    }
                });

                return entries;
            }

            function buildBankOptions(accounts) {
                let entries = normalizeAccountEntries(accounts);

                if (entries.length === 0) {
                    entries = getTopBankEntries();
                }

                return entries.map(function(entry) {
                    return '<option value="' + escapeHtml(entry[0]) + '">' +
                        escapeHtml(entry[1]) +
                        '</option>';
                }).join('');
            }

            function getSelectedDefaultBankId() {
                return String(
                    $('#default_bank').val() ||
                    receiptState.serverDefaultBankId ||
                    receiptState.pageDefaultBankId ||
                    ''
                );
            }

            function syncDefaultBankToReceiptBank() {
                const selectedBankId = getSelectedDefaultBankId();

                if (!selectedBankId) {
                    return;
                }

                $('#default_bank_display').val(selectedBankId);

                const $receiptBank = $('#bank');

                if (!$receiptBank.length) {
                    return;
                }

                const bankExists = $receiptBank.find('option').filter(function() {
                    return String($(this).val()) === selectedBankId;
                }).length > 0;

                if (!bankExists) {
                    let selectedBankText = $('#default_bank option:selected').text();

                    if (!selectedBankText) {
                        selectedBankText = selectedBankId;
                    }

                    $receiptBank.append(new Option(selectedBankText, selectedBankId));
                }

                $receiptBank.val(selectedBankId);
                updateReceiveType(selectedBankId, '#rec_type');
            }

            function removeMessages() {
                $('#detailcard .challan-message').remove();
            }

            function showMessage(type, message, loading = false) {
                removeMessages();

                const icon = loading ?
                    '<i class="fa fa-spinner fa-spin me-1"></i>' :
                    '';

                $('#detailcard')
                    .show()
                    .prepend(
                        '<div class="alert alert-' + type +
                        ' challan-message" style="margin:15px;">' +
                        icon +
                        escapeHtml(message) +
                        '</div>'
                    );
            }

            function resetChallanDetails(hideCard = true) {
                removeMessages();

                $('#siblingContainer').empty().hide();
                $('#headfee').empty().hide();
                $('#arrearsdetails').empty().hide();

                $('#challan_amt').val('');
                $('#late_amt').val('0.00');
                $('#arrears').val('0.00');
                $('#total_fee').val('');
                $('#rem_fee').val('');
                $('#remp_amt').val('');
                $('#applyAmountBtn').prop('disabled', true);

                receiptState.accountsData = {};
                receiptState.accountAllData = {};
                receiptState.serverDefaultBankId = null;

                if (hideCard) {
                    $('#detailcard').hide();
                }
            }

            function normalizeAjaxResponse(response) {
                if (typeof response !== 'string') {
                    return response || {};
                }

                try {
                    return JSON.parse(response);
                } catch (error) {
                    console.error('Invalid JSON response:', response);
                    return {};
                }
            }

            function getResponseError(xhr) {
                if (xhr.responseJSON) {
                    return xhr.responseJSON.error ||
                        xhr.responseJSON.message ||
                        'An error occurred while searching for the challan.';
                }

                if (xhr.status === 404) {
                    return 'Challan not found.';
                }

                if (xhr.status === 500) {
                    return 'Server error while loading challan data.';
                }

                if (xhr.status === 0) {
                    return 'Network error. Please check your connection.';
                }

                return 'An error occurred while searching for the challan.';
            }

            function calculateRemainingAmount() {
                const totalFee = toNumber($('#total_fee').val());
                let receiptAmount = toNumber($('#remp_amt').val());

                if (receiptAmount > totalFee && totalFee > 0) {
                    receiptAmount = totalFee;
                    $('#remp_amt').val(totalFee.toFixed(2));
                }

                const remainingAmount = Math.max(totalFee - receiptAmount, 0);
                $('#rem_fee').val(remainingAmount.toFixed(2));

                const hasHeads = $('.ramount').length > 0;
                $('#applyAmountBtn').prop(
                    'disabled',
                    !(receiptAmount > 0 && totalFee > 0 && hasHeads)
                );
            }

            function populateSiblingTable(challanDetail) {
                const student = challanDetail.student || {};

                const row = [
                    '<div class="row d-flex justify-content-center" style="padding:0.5rem 1.5rem !important;">',
                    '<div class="col-md-2"><b>Roll No:</b> ' +
                        escapeHtml(student.roll_no || '-') +
                        '</div>',
                    '<div class="col-md-3"><b>Student Name:</b> ' +
                        escapeHtml(student.stdname || student.name || '-') +
                        '</div>',
                    '<div class="col-md-3"><b>Father Name:</b> ' +
                        escapeHtml(student.fathername || student.father_name || '-') +
                        '</div>',
                    '<div class="col-md-2"><b>Issue Date:</b> ' +
                        escapeHtml(challanDetail.issue_date || '-') +
                        '</div>',
                    '<div class="col-md-2"><b>Due Date:</b> ' +
                        escapeHtml(challanDetail.due_date || '-') +
                        '</div>',
                    '</div>'
                ].join('');

                $('#siblingContainer').html(row).show();
            }

            function normalizeHeads(headsData) {
                if (Array.isArray(headsData)) {
                    return headsData;
                }

                if (headsData && typeof headsData === 'object') {
                    return Object.values(headsData);
                }

                return [];
            }

            function populateHeadFee(headsData, accountSource) {
                const heads = normalizeHeads(headsData);
                const $headFeeContainer = $('#headfee').empty();

                if (heads.length === 0) {
                    $headFeeContainer
                        .html(
                            '<div class="alert alert-warning mb-0">' +
                            'No payable fee heads were returned for this challan.' +
                            '</div>'
                        )
                        .show();
                    return;
                }

                heads.forEach(function(head) {
                    const headId = head.head_id ?? head.id ?? '';
                    const headName = head.head_name ?? head.name ?? 'Fee Head';
                    const amount = toNumber(
                        head.amount ??
                        head.remaining_amount ??
                        head.payable_amount
                    );

                    const row = [
                        '<div class="row" style="padding:0.5rem 1.5rem !important;">',
                        '<div class="col-md-4 mb-1">',
                        escapeHtml(headName),
                        '<input name="head_id[]" type="hidden" value="',
                        escapeHtml(headId),
                        '">',
                        '</div>',
                        '<div class="col-md-3 mb-1">',
                        '<input name="tamount[]" class="form-control tamount" ',
                        'type="text" value="',
                        amount.toFixed(2),
                        '" disabled>',
                        '</div>',
                        '<div class="col-md-3 mb-1">',
                        '<input name="ramount[]" class="form-control ramount" ',
                        'style="font-size:13px;" type="number" value="" ',
                        'min="0" max="',
                        amount,
                        '" step="any">',
                        '</div>',
                        '</div>'
                    ].join('');

                    $headFeeContainer.append(row);
                });

                const bankOptions = buildBankOptions(accountSource);

                $headFeeContainer.append([
                    '<div class="row align-items-end g-2" ',
                    'style="padding:0.5rem 1.5rem !important;">',

                    '<div class="col-md-4">',
                    '<label for="bank" class="form-label mb-1">',
                    '<strong>Bank Account</strong>',
                    '</label>',
                    '<select id="bank" class="form-control" ',
                    'style="width:100%; font-size:12px;">',
                    bankOptions,
                    '</select>',
                    '</div>',

                    '<div class="col-md-2">',
                    '<label for="rec_type" class="form-label mb-1">',
                    '<strong>D Status</strong>',
                    '</label>',
                    '<select class="form-control" id="rec_type" ',
                    'name="receive_type">',
                    '<option value="DD">DD</option>',
                    '<option value="OL">OL</option>',
                    '<option value="CHQ">CHQ</option>',
                    '<option value="CD">CD</option>',
                    '</select>',
                    '</div>',

                    '<div class="col-md-4">',
                    '<label for="ref" class="form-label mb-1">',
                    '<strong>Reference</strong>',
                    '</label>',
                    '<input type="text" name="reference" id="ref" ',
                    'class="form-control" required>',
                    '</div>',

                    '<div class="col-md-2">',
                    '<button type="button" id="saveReceiptButton" ',
                    'class="btn btn-success w-100">Save</button>',
                    '</div>',

                    '</div>'
                ].join(''));

                $('#bank')
                    .off('change.receiptBank')
                    .on('change.receiptBank', function() {
                        updateReceiveType($(this).val(), '#rec_type');
                    });

                syncDefaultBankToReceiptBank();
                $headFeeContainer.show();
            }

            function renderChallan(response) {
                const challanDetail = response.challandetail;

                if (!challanDetail) {
                    resetChallanDetails(false);
                    showMessage(
                        'danger',
                        response.message ||
                        response.error ||
                        'Challan not found. Please check the challan number.'
                    );
                    return;
                }

                removeMessages();
                $('#siblingContainer').empty().hide();
                $('#headfee').empty().hide();
                $('#arrearsdetails').empty().hide();
                $('#detailcard').show();

                const totalAmount = toNumber(challanDetail.total_amount);
                const concessionAmount = toNumber(challanDetail.concession_amount);
                const paidAmount = toNumber(challanDetail.paid_amount);

                const challanAmount = Math.max(
                    totalAmount - concessionAmount - paidAmount,
                    0
                );

                const lateAmount = toNumber(
                    response.late_amount ??
                    response.late_fee ??
                    response.latefee ??
                    challanDetail.late_amount
                );

                const arrearsAmount = toNumber(
                    response.arrears_amount ??
                    response.arrears ??
                    challanDetail.arrears
                );

                const totalFee = challanAmount + lateAmount + arrearsAmount;

                $('#challan_amt').val(challanAmount.toFixed(2));
                $('#late_amt').val(lateAmount.toFixed(2));
                $('#arrears').val(arrearsAmount.toFixed(2));
                $('#total_fee').val(totalFee.toFixed(2));
                $('#rem_fee').val(totalFee.toFixed(2));
                $('#remp_amt').val('');
                $('#applyAmountBtn').prop('disabled', true);

                receiptState.accountsData =
                    response.accounts_data &&
                    typeof response.accounts_data === 'object' ?
                    response.accounts_data :
                    {};

                receiptState.accountAllData =
                    response.account_all_data &&
                    typeof response.account_all_data === 'object' ?
                    response.account_all_data :
                    {};

                receiptState.serverDefaultBankId =
                    response.default_bank_id || null;

                const accountSource =
                    response.account_all ||
                    response.accounts ||
                    null;

                populateSiblingTable(challanDetail);
                populateHeadFee(
                    response.headsData ||
                    response.heads_data ||
                    response.heads ||
                    [],
                    accountSource
                );

                showMessage('success', 'Challan found successfully.');

                setTimeout(function() {
                    $('#detailcard .alert-success.challan-message').fadeOut(
                        function() {
                            $(this).remove();
                        }
                    );
                }, 1500);
            }

            function searchChallan() {
                const challanNo = String($('#challan_id').val() || '').trim();

                clearTimeout(receiptState.searchTimer);

                if (!challanNo) {
                    if (receiptState.activeRequest) {
                        receiptState.activeRequest.abort();
                        receiptState.activeRequest = null;
                    }

                    resetChallanDetails(true);
                    return;
                }

                if (receiptState.activeRequest) {
                    receiptState.activeRequest.abort();
                }

                showMessage('info', 'Searching challan...', true);

                receiptState.activeRequest = $.ajax({
                    url: challanSearchUrl,
                    method: 'GET',
                    dataType: 'json',
                    cache: false,
                    data: {
                        challan_no: challanNo
                    },
                    success: function(response) {
                        response = normalizeAjaxResponse(response);
                        renderChallan(response);
                    },
                    error: function(xhr, status) {
                        if (status === 'abort') {
                            return;
                        }

                        resetChallanDetails(false);
                        showMessage('danger', getResponseError(xhr));

                        console.error(
                            'Challan search failed:',
                            xhr.status,
                            xhr.responseText
                        );
                    },
                    complete: function() {
                        receiptState.activeRequest = null;
                    }
                });
            }

            function calculateHeadReceiptAmount($changedInput) {
                const maxAmount = toNumber($changedInput.attr('max'));
                let enteredAmount = toNumber($changedInput.val());

                if (enteredAmount > maxAmount) {
                    enteredAmount = maxAmount;
                    $changedInput.val(maxAmount.toFixed(2));
                }

                if (enteredAmount < 0) {
                    enteredAmount = 0;
                    $changedInput.val('0.00');
                }

                let total = 0;

                $('.ramount').each(function() {
                    total += toNumber($(this).val());
                });

                $('#remp_amt').val(total.toFixed(2));
                calculateRemainingAmount();
            }

            function resetAfterSave() {
                $('#challan_id').val('');
                resetChallanDetails(true);
                $('#challan_id').focus();
            }

            $(document).on('input', '#challan_id', function() {
                clearTimeout(receiptState.searchTimer);

                const challanNo = String($(this).val() || '').trim();

                if (!challanNo) {
                    searchChallan();
                    return;
                }

                receiptState.searchTimer = setTimeout(searchChallan, 600);
            });

            $(document).on('keydown', '#challan_id', function(event) {
                if (event.key === 'Enter' || event.keyCode === 13) {
                    event.preventDefault();
                    clearTimeout(receiptState.searchTimer);
                    searchChallan();
                }
            });

            $(document).on('input', '#remp_amt', function() {
                calculateRemainingAmount();
            });

            $(document).on('input', '.ramount', function() {
                calculateHeadReceiptAmount($(this));
            });

            $(document).on('input', '.oldramount', function() {
                const $input = $(this);
                const $row = $input.closest('.row');
                const maxAmount = toNumber($row.find('.oldtamount').val());
                let amount = toNumber($input.val());

                if (amount > maxAmount) {
                    amount = maxAmount;
                    $input.val(maxAmount.toFixed(2));
                }
            });

            $(document).on('click', '#applyAmountBtn', function() {
                const totalReceiptAmount = toNumber($('#remp_amt').val());

                if (totalReceiptAmount <= 0) {
                    showToast('error', 'Please enter a valid receipt amount.');
                    return;
                }

                const headInputs = $('.ramount');

                if (!headInputs.length) {
                    showToast('error', 'No payment heads are available.');
                    return;
                }

                let totalPayable = 0;

                headInputs.each(function() {
                    totalPayable += toNumber($(this).attr('max'));
                });

                if (totalReceiptAmount > totalPayable) {
                    showToast(
                        'error',
                        'Receipt amount cannot exceed the total payable head amount.'
                    );
                    return;
                }

                let remainingAmount = totalReceiptAmount;

                headInputs.each(function() {
                    const $input = $(this);
                    const maxAmount = toNumber($input.attr('max'));
                    const assignedAmount = Math.min(
                        remainingAmount,
                        maxAmount
                    );

                    $input.val(assignedAmount.toFixed(2));
                    remainingAmount = Math.max(
                        remainingAmount - assignedAmount,
                        0
                    );
                });

                calculateRemainingAmount();

                showToast(
                    'success',
                    'Amount distributed to payment heads successfully.'
                );
            });

            $(document).on('change', '#default_bank', function() {
                syncDefaultBankToReceiptBank();
            });

            $(document).on('change', '#default_date', function() {
                $('#recipt_date').val($(this).val());
            });

            $(document).on('click', '#saveReceiptButton', function(event) {
                event.preventDefault();

                const $button = $(this);
                const formData = {
                    head_id: [],
                    tamount: [],
                    ramount: [],
                    challan_id: String($('#challan_id').val() || '').trim(),
                    challan_amt: $('#challan_amt').val(),
                    recipt_amt: $('#remp_amt').val(),
                    recipt_date: $('#recipt_date').val(),
                    late_amt: $('#late_amt').val(),
                    arrears: $('#arrears').val(),
                    bank: $('#bank').val(),
                    ref: String($('#ref').val() || '').trim(),
                    receive_type: $('#rec_type').val()
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

                if (!formData.challan_id) {
                    showToast('error', 'Please search a challan first.');
                    return;
                }

                if (!formData.recipt_date) {
                    showToast('error', 'Please select the receipt date.');
                    return;
                }

                if (isBranchUser) {
                    const minimumDate = new Date();
                    minimumDate.setHours(0, 0, 0, 0);
                    minimumDate.setDate(minimumDate.getDate() - 3);

                    const receiptDate = new Date(
                        formData.recipt_date + 'T00:00:00'
                    );

                    if (receiptDate < minimumDate) {
                        showToast(
                            'error',
                            'Receipt Date must be within the last 3 days.'
                        );
                        return;
                    }
                }

                const receiptAmount = toNumber(formData.recipt_amt);

                if (receiptAmount <= 0) {
                    showToast(
                        'error',
                        'Receipt Amount must be greater than 0.'
                    );
                    return;
                }

                let totalItemPayment = 0;
                let invalidItemFound = false;

                $('.ramount').each(function() {
                    const $input = $(this);
                    const value = toNumber($input.val());
                    const maxValue = toNumber($input.attr('max'));

                    totalItemPayment += value;

                    if (value > maxValue) {
                        invalidItemFound = true;
                        $input.focus();
                        return false;
                    }
                });

                if (invalidItemFound) {
                    showToast(
                        'error',
                        'Each item payment cannot exceed its payable amount.'
                    );
                    return;
                }

                if (
                    totalItemPayment <= 0 ||
                    Math.abs(totalItemPayment - receiptAmount) > 0.01
                ) {
                    showToast(
                        'error',
                        'The item-wise payments must equal the receipt amount.'
                    );
                    return;
                }

                if (!formData.bank) {
                    showToast('error', 'Please select a valid bank.');
                    return;
                }

                if (!formData.ref) {
                    showToast('error', 'Please enter a reference.');
                    $('#ref').focus();
                    return;
                }

                $button.prop('disabled', true).text('Saving...');

                $.ajax({
                    url: receiptSaveUrl,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: formData,
                    success: function(response) {
                        if (response && response.data) {
                            $('#new_data').append(response.data);
                        }

                        showToast(
                            'success',
                            response.message || 'Receipt saved successfully.'
                        );

                        resetAfterSave();
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON ?
                            (
                                xhr.responseJSON.error ||
                                xhr.responseJSON.message
                            ) :
                            null;

                        showToast(
                            'error',
                            message || 'Something went wrong while saving the receipt.'
                        );

                        console.error(
                            'Receipt save failed:',
                            xhr.status,
                            xhr.responseText
                        );
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('Save');
                    }
                });
            });

            $('#recipt_date').val($('#default_date').val());
            syncDefaultBankToReceiptBank();

            setTimeout(function() {
                $('#challan_id').focus();
            }, 300);
        });
    </script>
@endsection