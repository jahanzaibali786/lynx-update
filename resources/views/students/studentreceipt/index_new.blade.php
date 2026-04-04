@extends('layouts.admin')
@section('page-title')
    {{ __('Daily CMR Statement') }}
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
    </style>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Daily CMR Statement') }}</li>
@endsection
@section('action-btn')
@endsection
@section('content')
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
                                {{ Form::select('default_bank', $accounts, null, ['class' => 'form-control select js-searchBox', 'id' => 'default_bank', 'required' => 'required']) }}
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

    {{-- ══════════════════════════════════════════════════════════
         NEW RECEIPT ENTRY CARD  (fully separate from records table)
    ══════════════════════════════════════════════════════════ --}}
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
                            <td><input type="text" id="late_amt" value="0" disabled></td>
                            <td><input type="text" id="arrears" value="" disabled></td>
                            <td><input type="text" id="total_fee" value="" disabled></td>
                            <td><input type="text" id="rem_fee" value="" disabled></td>
                            <td>
                                {{ Form::select('default_bank', $accounts, null, [
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
                    <tr style="border-radius:10px !important;">
                        <td><input type="text" value="{{ @$recipt->id }}" disabled
                                style="width:50px;  font-size:11px;"></td>
                        <td><input type="text" value="{{ date('d/m/Y', strtotime($recipt->recipt_date)) }}" disabled
                                class="font_less" style="width:63px; font-size:11px;"></td>
                        <td><input type="text" value="{{ @$recipt->challan->challanNo }}" disabled
                                style="width:60px;"></td>
                        <td><input type="text" value="{{ @$recipt->recipt_amount }}" disabled
                                style="width:60px;  font-size:13px;"></td>
                        <td><input type="text" value="{{ @$recipt->challan_amount }}" disabled
                                style="width:65px;  font-size:13px;"></td>
                        <td><input type="text" value="{{ @$recipt->late_amount }}" disabled
                                style="width:50px;  font-size:13px;"></td>
                        <td><input type="text" value="{{ @$recipt->arrears }}" disabled
                                style="width:50px;  font-size:13px;"></td>
                        <td><input type="text"
                                value="{{ @$recipt->challan_amount + @$recipt->late_amount + @$recipt->arrears }}"
                                disabled style="width:60px; font-size:12px;"></td>
                        <td><input type="text"
                                value="{{ @$recipt->challan_amount + @$recipt->late_amount + @$recipt->arrears - @$recipt->recipt_amount }}"
                                disabled style="width:65px; font-size:12px;"></td>
                        <td>{{ Form::select('default_bank', $accounts, @$recipt->bank_id, ['style' => 'width:100px; font-size:12px;', 'disabled' => 'disabled']) }}
                        </td>
                        <td>
                            <select class="input" disabled>
                                @foreach ($options as $option)
                                    <option value="{{ $option }}"
                                        {{ $option == @$recipt->receive_type ? 'selected' : '' }}>{{ $option }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="text" value="{{ @$recipt->referance }}" disabled
                                style="width:80px;  font-size:11px;"></td>
                        <td><input type="text" value="{{ @$recipt->received->name }}" disabled
                                style="width:100px; font-size:11px;"></td>
                        @if (Auth::user()->type == 'company')
                            <td>
                                <a href="#!" data-size="lg"
                                    data-url="{{ route('student_receipt.edit', $recipt->id) }}" data-ajax-popup="true"
                                    class="btn btn-sm btn-outline-primary" data-bs-title="{{ __('Edit') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                </a>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
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

        // ── On ready ─────────────────────────────────────────────────────
        $(document).ready(function() {
            $('#default_date').on('change', function() {
                $('#recipt_date').val($(this).val());
            });
            setTimeout(function() {
                $('#challan_id').focus();
            }, 300);
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
                        var latefee = parseFloat($('#late_amt').val()) || 0;
                        var arrearsTotal = 0;

                        response.previousUnpaidChallans.forEach(function(a) {
                            arrearsTotal += (a.total_amount - a.concession_amount - a
                                .paid_amount);
                        });

                        var challanNet = detail.total_amount - detail.concession_amount - detail
                            .paid_amount;

                        $('#challan_amt').val(challanNet);
                        $('#arrears').val(arrearsTotal);
                        $('#total_fee').val(parseFloat(arrearsTotal) + latefee + parseFloat(
                        challanNet));
                        $('#remp_amt').val('0.00');
                        $('#rem_fee').val($('#total_fee').val());

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

                    // Clear all summary fields in the entry row
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
            var challanAmt = parseFloat($('#challan_amt').val()) || 0;
            var rempAmt = parseFloat($('#remp_amt').val()) || 0;
            if (rempAmt > challanAmt) {
                rempAmt = challanAmt;
                $('#remp_amt').val(challanAmt.toFixed(2));
            }
            $('#rem_fee').val(((parseFloat($('#total_fee').val()) || 0) - rempAmt).toFixed(2));
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
            // Hide & clear all detail sections
            $('#detailcard').hide();
            $('#siblingContainer').empty().hide();
            $('#headfee').empty().hide();
            $('#arrearsdetails').empty();

            // Clear all summary fields in the entry row
            $('#challan_amt').val('');
            $('#remp_amt').val('');
            $('#late_amt').val('0');
            $('#arrears').val('');
            $('#total_fee').val('');
            $('#rem_fee').val('');

            // Clear the challan input itself
            $('#challan_id').val('');

            $('#challan-searching').hide();
        }

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
                    '<div class="col-md-3 mb-1"><input name="ramount[]" class="form-control ramount" style="font-size:13px;" type="number" value="" min="0" step="any"></div>'
                    );
                c.append(row);
            });

            var banks_id = defaultBankId || $('#default_bank').val();

            c.append(`
                <div style="display:flex;" class="gap-2 mt-2">
                    <label style="display:block;margin-bottom:5px;"><strong>Bank Account</strong>
                        <select id="bank" class="form-control js-searchBox" style="width:150px;font-size:12px;">
                            ${all_accountOptions}
                        </select>
                    </label>
                    <label style="display:block;margin-bottom:5px;"><strong>D Status</strong>
                        <select class="input form-control" id="rec_type" style="width:100px;" name="receive_type">
                            <option value="DD">DD</option>
                            <option value="OL">OL</option>
                            <option value="CHQ">CHQ</option>
                            <option value="CD">CD</option>
                        </select>
                    </label>
                    <label style="display:block;margin-bottom:5px;"><strong>Reference <span style="color:red;">*</span></strong>
                        <input type="text" value="" name="ref" id="ref" class="form-control ref-input" style="width:190px;font-size:11px;">
                    </label>
                </div>
                <div class="d-flex justify-content-end gap-4" style="padding-top:10px;">
                    <button class="btn btn-success saveButton">Save</button>
                </div>
            `);

            $('#bank').val(banks_id);
            updateReceiveType(banks_id, '#rec_type');
            $('#bank').on('change', function() {
                updateReceiveType($(this).val(), '#rec_type');
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
                <div class="col-md-8 mb-1"><input type="text" value="" id="oldref" class="old_ref form-control old-ref-input" style="font-size:13px;"></div>
                <div style="display:flex;" class="gap-4">
                    <label style="display:block;margin-bottom:5px;">Bank Account
                        <select id="old_bank" name="default_bank" class="form-control old_banks js-searchBox" style="width:280px;font-size:12px;">
                            ${bankOpts}
                        </select>
                    </label>
                    <label style="display:block;margin-bottom:5px;">D Status
                        <select class="input form-control old_rec_types" id="old_rec_type" style="width:150px;" name="receive_type">
                            <option value="DD">DD</option><option value="OL">OL</option>
                            <option value="CHQ">CHQ</option><option value="CD">CD</option>
                        </select>
                    </label>
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
                        '<div class="col-md-4"><input name="oldramount[]" class="form-control oldramount" type="number" value="" min="0" step="any"></div>'
                        );
                    mhc.append(headRow);
                }
            });

            var banks_id = $('#bank').val() || defaultBankId || $('#default_bank').val();
            $('#old_bank').val(banks_id);
            const d = accountAllData[banks_id] || accountsData[banks_id] || {};
            $('#old_rec_type').html(getReceiveTypeOptions(d.chart_account || ''));
            $('#old_bank').off('change').on('change', function() {
                updateReceiveType($(this).val(), '#old_rec_type');
            });
        }

        $(document).on('change', '#default_bank', function() {
            var sel = $(this).val();
            if ($('#bank').length) {
                $('#bank').val(sel);
                updateReceiveType(sel, '#rec_type');
            }
            if ($('#old_bank').length) {
                $('#old_bank').val(sel);
                updateReceiveType(sel, '#old_rec_type');
            }
        });

        // ── Main save ─────────────────────────────────────────────────────
        $(document).on('click', '.saveButton', function(event) {
            event.preventDefault();
            var $btn = $(this).prop('disabled', true);

            if (!validateAllAmounts()) {
                $btn.prop('disabled', false);
                return;
            }

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
                    // ── Full reset of entire entry card ──
                    resetEntryDetail();

                    // Prepend saved row to the TOP of the records table
                    $('#new_data').prepend(response.data);

                    // Re-focus for next entry immediately
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

            var oldformData = {
                head_id: [],
                tamount: [],
                ramount: [],
                challan_id: mhd.find('.old_ch_id').val(),
                challan_amt: 0,
                recipt_date: $('#recipt_date').val(),
                recipt_amt: 0,
                late_amt: 0,
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
