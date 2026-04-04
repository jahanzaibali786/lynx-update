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

        /* Optional: Style spinner arrows */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            opacity: 1;
        }

        .error-message {
            color: #dc3545;
            font-size: 12px;
            margin-top: 2px;
        }
    </style>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Daily CMR Statement') }}</li>
@endsection
@section('action-btn')
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding: 12px;">
                        {{ Form::open(['route' => ['student_receipt.index'], 'method' => 'GET', 'id' => 'student_receipt_submit']) }}
                        <div class="row d-flex justify-content-end ">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('default_date', __('Default Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('date', isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'), ['class' => 'form-control', 'id' => 'default_date']) }}
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('default_bank', __('Default Bank'), ['class' => 'form-label']) }}
                                    {{ Form::select('default_bank', $accounts, null, ['class' => 'form-control select js-searchBox', 'id' => 'default_bank', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('student_receipt_submit').submit(); return false;"
                                    data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('student_receipt.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <!-- Actions Dropdown -->
                                <div class="dropdown d-inline-block mx-1">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                        <li>
                                            <button class="dropdown-item" type="submit" name="export" value="excel">
                                                <i class="ti ti-file me-2"></i>Excel
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" type="submit" name="export" value="pdf">
                                                <i class="ti ti-download me-2"></i>Pdf
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
    <div class="col-12 ">
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
            @php
                $options = ['DD', 'OL', 'CHQ', 'CD'];
            @endphp
            <tbody id="new_data">
                @foreach ($recipts as $recipt)
                    <tr style="border-radius: 10px !important;">
                        <td>
                            <input type="text" value="{{ @$recipt->id }}" disabled
                                style="width:50px; font-size: 11px;">
                        </td>
                        <td>
                            <input type="text" value="{{ date('d/m/Y', strtotime($recipt->recipt_date)) }}" disabled
                                class="font_less" style="width:63px; font-size: 11px;">
                        </td>
                        <td>
                            <input type="text" value="{{ @$recipt->challan->challanNo }}" disabled style="width:60px;">
                        </td>
                        <td>
                            <input type="text" value="{{ @$recipt->recipt_amount }}" disabled
                                style="width:60px; font-size: 13px;">
                        </td>
                        <td>
                            <input type="text" value="{{ @$recipt->challan_amount }}" disabled
                                style="width:65px; font-size: 13px;">
                        </td>
                        <td>
                            <input type="text" value="{{ @$recipt->late_amount }}" disabled
                                style="width:50px; font-size: 13px;">
                        </td>
                        <td>
                            <input type="text" value="{{ @$recipt->arrears }}" disabled
                                style="width:50px; font-size: 13px;">
                        </td>
                        <td>
                            <input type="text"
                                value="{{ @$recipt->challan_amount + @$recipt->late_amount + @$recipt->arrears }}"
                                disabled style="width:60px; font-size: 12px;">
                        </td>
                        <td>
                            <input type="text"
                                value="{{ @$recipt->challan_amount + @$recipt->late_amount + @$recipt->arrears - @$recipt->recipt_amount }}"
                                disabled style="width:65px; font-size: 12px;">
                        </td>
                        <td>
                            {{ Form::select('default_bank', $accounts, @$recipt->bank_id, ['style' => 'width:100px; font-size: 12px;', 'disabled' => 'disabled']) }}
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
                        <td>
                            <input type="text" value="{{ @$recipt->referance }}" disabled
                                style="width:80px; font-size: 11px;">
                        </td>
                        <td>
                            <input type="text" value="{{ @$recipt->received->name }}" disabled
                                style="width:100px; font-size: 11px;">
                        </td>
                        @if (Auth::user()->type == 'company')
                            <td>
                                <a href="#!" data-size="lg"
                                    data-url="{{ route('student_receipt.edit', $recipt->id) }}" data-ajax-popup="true"
                                    title="Edit" class="btn btn-sm btn-outline-primary"
                                    data-bs-title="{{ __('Edit') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span></a>
                            </td>
                        @endif
                    </tr>
                @endforeach
                <tr id="focus_row" style="border-radius: 10px !important;">
                    <td>
                        <input type="text" value="" disabled style="width:50px; font-size: 11px;">
                    </td>
                    <td>
                        <input type="date" value="{{ isset($_GET['date']) ? $_GET['date'] : date('Y-m-d') }}"
                            id="recipt_date" class="font_less" style="width:70px; font-size: 11px;">
                    </td>
                    <td>
                        <input type="text" id="challan_id" value="" style="width:60px;">
                    </td>
                    <td>
                        <input type="text" value="" id="remp_amt" disabled style="width:60px; font-size: 13px;">
                    </td>
                    <td>
                        <input type="text" id="challan_amt" value="" disabled style="width:65px; font-size: 13px;">
                    </td>
                    <td>
                        <input type="text" id="late_amt" value="0" disabled style="width:50px; font-size: 13px;">
                    </td>
                    <td>
                        <input type="text" id="arrears" value="" disabled style="width:50px; font-size: 13px;">
                    </td>
                    <td>
                        <input type="text" id="total_fee" value="" disabled style="width:60px; font-size: 12px;">
                    </td>
                    <td>
                        <input type="text" id="rem_fee" value="" disabled style="width:65px; font-size: 12px;">
                    </td>
                    <td>
                        {{ Form::select('default_bank', $accounts, null, ['style' => 'width:100px; font-size: 12px;', 'disabled' => 'disabled']) }}
                    </td>
                    <td>
                        <select class="input" name="receive_type" disabled>
                            <option value="DD">DD</option>
                            <option value="OL">OL</option>
                            <option value="CHQ">CHQ</option>
                            <option value="CD">CD</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" value="" style="width:80px; font-size: 11px;" disabled>
                    </td>
                    <td>
                        <input type="text" value="{{ Auth::user()->name }}" style="width:100px; font-size: 11px;"
                            disabled>
                    </td>
                </tr>
            </tbody>
        </table>

        <div id="detailcard" class="card" style="display:none;">
            <div class="" id="siblingContainer" style="display:none;">

            </div>
            <hr style="margin-top: 5px;">
            <div class="row" style="padding: 0px 30px;">
                <div id="headfee" class="col-md-6 pb-4">
                </div>
                <div id="arrearsdetails" class="col-md-6 pb-4">
                </div>
            </div>
        </div>
    </div>

    <!-- Arrears Modal -->
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
        let account = [];
        let accountOptions = '';
        let all_accountOptions = '';
        let accountsData = {}; // Store account data with chart_account info
        let accountAllData = {}; // Store all account data with chart_account info
        let defaultBankId = null; // Store default bank for branch users

        // Sync date filter with receipt date input
        $(document).ready(function() {
            $('#default_date').on('change', function() {
                var selectedDate = $(this).val();
                $('#recipt_date').val(selectedDate);
            });
        });

        // Function to get receive type options based on chart account
        function getReceiveTypeOptions(chartAccount) {
            if (!chartAccount) return '';

            const accountName = chartAccount.toUpperCase();

            // If contains CSH or CASH → show only CD
            if (accountName.includes('CSH') || accountName.includes('CASH')) {
                return '<option value="CD">CD</option>';
            } else {
                // All other accounts
                return `
                    <option value="DD">DD</option>
                    <option value="OL">OL</option>
                    <option value="CHQ">CHQ</option>
                `;
            }
        }

        // Function to update receive type dropdown based on selected bank
        function updateReceiveType(bankId, selectElement) {
            let chartAccount = '';
            
            // Check if bank exists in accountAllData first, then accountsData
            if (accountAllData[bankId]) {
                chartAccount = accountAllData[bankId].chart_account;
            } else if (accountsData[bankId]) {
                chartAccount = accountsData[bankId].chart_account;
            }
            const options = getReceiveTypeOptions(chartAccount);            
            $(selectElement).html(options);
        }

        // Focus on challan No field and scroll to that section
        document.addEventListener('DOMContentLoaded', function() {
            var targetSection = document.getElementById('focus_row');
            var textbox = document.getElementById('challan_id');
            setTimeout(function() {
                targetSection.scrollIntoView({
                    behavior: 'smooth'
                });
                textbox.focus();
            }, 600);
        });

        // Validation function to check all amounts
        function validateAllAmounts() {
            let isValid = true;
            let errorMessages = [];

            // Check main fee heads
            $('.ramount').each(function() {
                let inputAmount = parseFloat($(this).val()) || 0;
                let maxAmount = parseFloat($(this).closest('.row').find('.tamount').val()) || 0;

                if (inputAmount > maxAmount) {
                    isValid = false;
                    let headName = $(this).closest('.row').find('div:first').text().trim();
                    errorMessages.push(`${headName}: Amount (${inputAmount}) exceeds maximum (${maxAmount})`);
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            // Check arrears amounts in modal
            $('.oldramount').each(function() {
                let inputAmount = parseFloat($(this).val()) || 0;
                let maxAmount = parseFloat($(this).closest('.row').find('.oldtamount').val()) || 0;

                if (inputAmount > maxAmount) {
                    isValid = false;
                    let headName = $(this).closest('.row').find('div:first').text().trim();
                    errorMessages.push(`${headName}: Amount (${inputAmount}) exceeds maximum (${maxAmount})`);
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            if (!isValid) {
                show_toastr('error', 'Please correct the following amounts:\n' + errorMessages.join('\n'), 'error');
            }

            return isValid;
        }

        // Real-time validation for main amounts
        $(document).on('input change keyup', '.ramount', function() {
            let inputAmount = parseFloat($(this).val()) || 0;
            let maxAmount = parseFloat($(this).closest('.row').find('.tamount').val()) || 0;

            if (inputAmount > maxAmount) {
                $(this).val(maxAmount);
                show_toastr('warning', 'Amount cannot exceed maximum allowed', 'warning');
            }

            var row = $(this).closest('.row');
            calculateRAmount(row);
        });

        // Real-time validation for arrears amounts
        $(document).on('input change', '.oldramount', function() {
            let inputAmount = parseFloat($(this).val()) || 0;
            let maxAmount = parseFloat($(this).closest('.row').find('.oldtamount').val()) || 0;

            if (inputAmount > maxAmount) {
                $(this).val(maxAmount);
                show_toastr('warning', 'Amount cannot exceed maximum allowed', 'warning');
            }

            var row = $(this).closest('.row');
            calculateOldRAmount(row);
        });

        // Search challan data for challan no
        $(document).on('keyup', '#challan_id', function() {
            var challan_id = $(this).val();
            if (challan_id.length >= 1) {
                $.ajax({
                    url: '{{ route('challandata_for_receipt') }}',
                    type: 'GET',
                    data: {
                        challan_id: challan_id
                    },
                    success: function(response) {
                        $('#detailcard').show();
                        var $arrearsTotal = 0;
                        if (response.challandetail) {
                            document.getElementById('challan_amt').value = response.challandetail
                                .total_amount - response.challandetail.concession_amount - response
                                .challandetail.paid_amount;
                            var latefee = document.getElementById('late_amt').value;
                            response.previousUnpaidChallans.forEach(function(arear) {
                                $arrearsTotal += (arear.total_amount - arear.concession_amount -
                                    arear.paid_amount);
                                document.getElementById('arrears').value = $arrearsTotal;
                            });
                            document.getElementById('total_fee').value = parseFloat($arrearsTotal) +
                                parseFloat(latefee) + parseFloat(response.challandetail.total_amount -
                                    response.challandetail.concession_amount - response.challandetail
                                    .paid_amount);
                            
                            // Initialize remp_amt to 0
                            document.getElementById('remp_amt').value = '0.00';
                            document.getElementById('rem_fee').value = document.getElementById(
                                'total_fee').value;

                            // Store account data with chart_account information
                            bankAccounts = response.accounts;
                            accountsData = response.accounts_data || {};
                            accountOptions = '';
                            Object.entries(bankAccounts).forEach(([value, text]) => {
                                accountOptions += `<option value="${value}">${text}</option>`;
                            });

                            allAccounts = response.account_all;
                            accountAllData = response.account_all_data || {};
                            all_accountOptions = '';
                            Object.entries(allAccounts).forEach(([value, text]) => {
                                all_accountOptions += `<option value="${value}">${text}</option>`;
                            });
                            
                            // Store default bank ID for branch users
                            defaultBankId = response.default_bank_id;

                            populateSiblingTable(response.challandetail);
                            populateHeadFee(response.headsData);
                            populateArrears(response.previousUnpaidChallans);
                            $('#siblingtable').show();
                            $('#headfee').css('display', 'block');
                        } else {
                            $('#siblingtable').hide();
                            $('#headfee').css('display', 'none');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            } else {
                $('#detailcard').hide();
                var siblingContainer = $('#siblingContainer');
                siblingContainer.empty();
                siblingContainer.hide();
                $('#headfee').empty();
                $('#arrearsdetails').empty();

                $('#total_fee').val(0);
                $('#rem_fee').val(0);
                $('#challan_amt').val(0);
                $('#remp_amt').val(0);
                $('#late_amt').val(0);
                $('#arrears').val(0);
            }
        });

        document.getElementById('remp_amt').addEventListener('keyup', calculateRemainingAmount);

        function calculateRemainingAmount() {
            var challanAmt = parseFloat(document.getElementById('challan_amt').value) || 0;
            var rempAmt = parseFloat(document.getElementById('remp_amt').value) || 0;
            if (rempAmt > challanAmt) {
                rempAmt = challanAmt;
                document.getElementById('remp_amt').value = challanAmt.toFixed(2);
            }
            var totalFee = parseFloat(document.getElementById('total_fee').value) || 0;
            var remainingAmount = totalFee - rempAmt;
            document.getElementById('rem_fee').value = remainingAmount.toFixed(2);
        }
        calculateRemainingAmount();

        // Function to calculate remaining amount based on row elements
        function calculateRAmount(row) {
            var challanAmt = parseFloat(row.find('.tamount').val()) || 0;
            var rempAmt = parseFloat(row.find('.ramount').val()) || 0;

            if (rempAmt > challanAmt) {
                rempAmt = challanAmt;
                row.find('.ramount').val(challanAmt.toFixed(1));
            }
            
            // Calculate total from all ramount inputs
            let total = 0;
            document.querySelectorAll('.ramount').forEach(element => {
                let val = parseFloat(element.value);
                if (!isNaN(val)) {
                    total += val;
                }
            });
            
            console.log('Total calculated:', total); // Debug log
            document.getElementById('remp_amt').value = total.toFixed(2);
            calculateRemainingAmount();
        }

        $(document).on('keyup', '.oldramount', function() {
            var row = $(this).closest('.row');
            calculateOldRAmount(row);
        });

        $(document).on('keyup', '.ramount', function() {
            var row = $(this).closest('.row');
            calculateRAmount(row);
        });

        function calculateOldRAmount(row) {
            var challanAmt = parseFloat(row.find('.oldtamount').val()) || 0;
            var rempAmt = parseFloat(row.find('.oldramount').val()) || 0;

            if (rempAmt > challanAmt) {
                rempAmt = challanAmt;
                row.find('.oldramount').val(challanAmt.toFixed(1));
            }
        }

        function populateSiblingTable(challandetail) {
            var siblingContainer = $('#siblingContainer');
            siblingContainer.empty();

            var row = $('<div class="row d-flex justify-content-center" style="padding: 0.5rem 1.5rem !important;">');
            row.append('<div class="col-md-3"><b>Student Name:</b> ' + challandetail.student.stdname + '</div>');
            row.append('<div class="col-md-3"><b>Father Name:</b> ' + challandetail.student.fathername + '</div>');
            row.append('<div class="col-md-3"><b>Issue Date:</b> ' + challandetail.issue_date + '</div>');
            row.append('<div class="col-md-3"><b>Due Date:</b> ' + challandetail.due_date + '</div>');
            siblingContainer.append(row);
            siblingContainer.show();
        }

        function populateHeadFee(headsData) {
            var headFeeContainer = $('#headfee');
            headFeeContainer.empty();

            // Check if headsData is empty or has no valid entries
            if (!headsData || headsData.length === 0) {
                headFeeContainer.hide();
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
                    '<div class="col-md-3 mb-1"><input name="ramount[]" class="form-control ramount" style="font-size: 13px;" type="number" value="" min="0" step="any"></div>'
                );
                headFeeContainer.append(row);
            });

            // Determine which bank to select (default bank for branch users or default_bank from filter)
            var banks_id = defaultBankId || $('#default_bank').val();

            headFeeContainer.append(`<div style="display:flex;" class="gap-2">
                <label for="bank" style="display:block; margin-bottom:5px;"><strong>Bank Account</strong>
                    <select id="bank" class="form-control js-searchBox main-bank-select" style="width:150px; font-size: 12px;">
                        ${all_accountOptions}
                    </select>
                </label>
                <label for="rec_type" style="display:block; margin-bottom:5px;"><strong>D Status</strong>
                    <select class="input form-control" id="rec_type" style="width:100px" name="receive_type">
                        <option value="DD">DD</option>
                        <option value="OL">OL</option>
                        <option value="CHQ">CHQ</option>
                        <option value="CD">CD</option>
                    </select>
                </label>
                <label for="ref" style="display:block; margin-bottom:5px;"><strong>Reference <span style="color:red;">*</span></strong>
                    <input type="text" value="" name="ref" id="ref" class="form-control ref-input" style="width:190px; font-size: 11px;">
                </label>
            </div>
            <div class="d-flex justify-content-end gap-4" style="position:relative; right:80px; padding-top: 10px;">
                <button class="btn btn-success saveButton">Save</button>
            </div>
            `);

            $('#bank').val(banks_id);
            
            // Update receive type based on selected bank
            updateReceiveType(banks_id, '#rec_type');
            
            // Add event listener for bank change
            $('#bank').on('change', function() {
                var selectedBank = $(this).val();
                updateReceiveType(selectedBank, '#rec_type');
            });
            
            headFeeContainer.show();
        }

        function populateArrears(previousUnpaidChallans) {
            var arrearsContainer = $('#arrearsdetails');
            arrearsContainer.empty();
            arrearsContainer.append('<h5><strong>Arrears :-</strong></h5><br>');
            previousUnpaidChallans.forEach(function(arrear) {
                var row = $(
                    '<div class="mb-3 arrear-row" style="cursor: pointer; display: flex; flex-direction: row; gap:20px;">'
                );
                row.append(
                    '<div class=""><b>Challan No:</b> <span style="background-color: #100773; color: white; font-weight: bold; padding: 2px 5px; border-radius: 3px; animation: blink-effect 1s infinite;">' +
                    arrear.challanNo + '</span></div>');

                $('head').append(
                    '<style>@keyframes blink-effect { 0% { background-color: #100773; color: white; } 50% { background-color: white; color: #100773; } 100% { background-color: #100773; color: white; } }</style>'
                );

                row.append('<div class=""><b>Amount:</b> ' + (arrear.total_amount - arrear.concession_amount -
                    arrear.paid_amount) + '</div>');
                row.append('<div class=""><b>Due Date: </b>' + arrear.due_date + '</div>');
                row.data('arrear', arrear);
                row.on('click', function() {
                    var arrearData = $(this).data('arrear');
                    populateModalHeads(arrearData);
                    $('#arrearModal').modal('show');
                });

                arrearsContainer.append(row);
            });
        }

        function populateModalHeads(arrearData) {
            var modalHeadsContainer = $('#modalHeadsContainer');
            modalHeadsContainer.empty();
            var modalHeadsData = $('.modalHeadsData');
            modalHeadsData.empty();
            var row = $('<div class="row">');
            row.append(
                '<div class="col-md-4 mb-1">Reference <span style="color:red;">*</span></div><input name="old_challan_id" class="old_ch_id" type="hidden" value="' +
                arrearData.challanNo + '">');
            if (arrearData.owned_by == "{{ Auth::user()->ownedId() }}") {
                row.append(
                    `<div class="col-md-8 mb-1"><input type="text" value="" id="oldref" class="old_ref form-control old-ref-input" style="font-size: 13px;"></div><div style="display:flex" class="gap-4"><label for="old_bank" style="display:block; margin-bottom:5px;">Bank Account
                        <select id="old_bank" name="default_bank" class="form-control old_banks js-searchBox modal-bank-select" style="width:280px; font-size: 12px;">
                            ${all_accountOptions}
                        </select>
                    </label>
                    <label for="old_rec_type" style="display:block; margin-bottom:5px;">D Status
                        <select class="input form-control old_rec_types" id="old_rec_type" style="width:150px" name="receive_type">
                            <option value="DD">DD</option>
                            <option value="OL">OL</option>
                            <option value="CHQ">CHQ</option>
                            <option value="CD">CD</option>
                        </select>
                    </label>
                </div>`
                );
            } else {
                row.append(
                    `<div class="col-md-8 mb-1"><input type="text" value="" id="oldref" class="old_ref form-control old-ref-input" style="font-size: 13px;"></div><div style="display:flex" class="gap-4"><label for="old_bank" style="display:block; margin-bottom:5px;">Bank Account
                        <select id="old_bank" name="default_bank" class="form-control old_banks js-searchBox modal-bank-select" style="width:280px; font-size: 12px;">
                            ${accountOptions}
                        </select>
                    </label>
                    <label for="old_rec_type" style="display:block; margin-bottom:5px;">D Status
                        <select class="input form-control old_rec_types" id="old_rec_type" style="width:150px" name="receive_type">
                            <option value="DD">DD</option>
                            <option value="OL">OL</option>
                            <option value="CHQ">CHQ</option>
                            <option value="CD">CD</option>
                        </select>
                    </label>
                </div>`
                );
            }
            
            modalHeadsData.append(row);

            // Add fee heads first
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
                    modalHeadsContainer.append(headRow);
                }
            });

            // Now set bank and update receive type after DOM is ready
            // Determine which bank to select - prioritize: 1) Main form bank, 2) Default bank for branch, 3) Filter bank
            var banks_id = $('#bank').val() || defaultBankId || $('#default_bank').val();
            
            console.log('Modal - Setting bank:', banks_id); // Debug log
            console.log('Modal - Account data:', accountAllData); // Debug log
            
            // Set the bank value
            $('#old_bank').val(banks_id);
            
            // Get chart account for the selected bank
            let chartAccount = '';
            if (accountAllData[banks_id]) {
                chartAccount = accountAllData[banks_id].chart_account;
            } else if (accountsData[banks_id]) {
                chartAccount = accountsData[banks_id].chart_account;
            }
            
            console.log('Modal - Chart account:', chartAccount); // Debug log
            
            // Update receive type immediately
            const receiveTypeOptions = getReceiveTypeOptions(chartAccount);
            $('#old_rec_type').html(receiveTypeOptions);
            
            console.log('Modal - Receive type options set:', receiveTypeOptions); // Debug log
            
            // Add event listener for modal bank change
            $('#old_bank').off('change').on('change', function() {
                var selectedBank = $(this).val();
                console.log('Modal - Bank changed to:', selectedBank); // Debug log
                updateReceiveType(selectedBank, '#old_rec_type');
            });
        }

        $(document).on('change', '#default_bank', function() {
            var selectedBank = $(this).val();

            // Update main form bank and receive type
            if ($('#bank').length) {
                $('#bank').val(selectedBank);
                updateReceiveType(selectedBank, '#rec_type');
            }

            // Update modal bank and receive type
            if ($('#old_bank').length) {
                $('#old_bank').val(selectedBank);
                updateReceiveType(selectedBank, '#old_rec_type');
            }
        });

        // Main save button handler
        $(document).on('click', '.saveButton', function(event) {
            event.preventDefault();

            var $saveBtn = $(this);
            $saveBtn.prop('disabled', true);

            // Validate all amounts first
            if (!validateAllAmounts()) {
                $saveBtn.prop('disabled', false);
                return;
            }

            var refValue = $.trim($('#headfee').find('.ref-input').val());

            var formData = {
                head_id: [],
                tamount: [],
                ramount: [],
                challan_id: '',
                challan_amt: '',
                recipt_amt: '',
                recipt_date: '',
                late_amt: '',
                arrears: '',
                bank: '',
                ref: '',
                receive_type: '',
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

            formData.challan_id = $('#challan_id').val();
            formData.challan_amt = $('#challan_amt').val();
            formData.recipt_date = $('#recipt_date').val();
            formData.recipt_amt = $('#remp_amt').val();
            formData.late_amt = $('#late_amt').val();
            formData.arrears = $('#arrears').val();
            formData.bank = $('#bank').val();
            formData.ref = refValue;
            formData.receive_type = $('#rec_type').val();

            if ({{ Auth::user()->type != 'company' ? 'true' : 'false' }}) {
                const minDate = new Date();
                minDate.setDate(minDate.getDate() - 3);
                const reciptDate = new Date(formData.recipt_date);
            }

            if (formData.recipt_amt <= 0) {
                show_toastr('error', 'Receipt Amount must be greater than 0', 'error');
                $saveBtn.prop('disabled', false);
                return;
            }

            if (!formData.bank) {
                show_toastr('error', 'Please select a valid bank', 'error');
                $saveBtn.prop('disabled', false);
                return;
            }

            if (!formData.ref || formData.ref === '') {
                show_toastr('error', 'Reference field is required', 'error');
                $('#headfee').find('.ref-input').focus();
                $saveBtn.prop('disabled', false);
                return;
            }

            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: 'paidchallan',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: formData,
                success: function(response) {
                    console.log('Response:', response.data);
                    var row = document.getElementById('focus_row');
                    if (row) {
                        row.remove();
                    }
                    $('#detailcard').hide();
                    var siblingContainer = $('#siblingContainer');
                    siblingContainer.empty();
                    siblingContainer.hide();
                    $('#headfee').empty();
                    $('#arrearsdetails').empty();
                    $('#new_data').append(response.data);
                    var banks_id = $('#default_bank').val();
                    if ($('#bank').length) {
                        $('#bank').val(banks_id);
                    }

                    $saveBtn.prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    alert('Something Went Wrong...');
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        console.error('Error:', xhr.responseJSON.error);
                    } else {
                        console.error('An unknown error occurred.');
                    }

                    $saveBtn.prop('disabled', false);
                }
            });
        });

        // Old save button handler
        $(document).on('click', '#oldsaveButton', function(event) {
            event.preventDefault();

            var $oldSaveBtn = $(this);
            $oldSaveBtn.prop('disabled', true);

            // Validate all amounts first
            if (!validateAllAmounts()) {
                $oldSaveBtn.prop('disabled', false);
                return;
            }

            var activeModal = $oldSaveBtn.closest('#arrearModal');

            if (!activeModal.length) {
                console.error('No active modal found.');
                $oldSaveBtn.prop('disabled', false);
                return;
            }

            var modalHeadsDataDiv = activeModal.find('.modalHeadsData');

            if (!modalHeadsDataDiv.length) {
                console.error('No modalHeadsData div found in the active modal.');
                $oldSaveBtn.prop('disabled', false);
                return;
            }

            var old_ref = $.trim(modalHeadsDataDiv.find('.old-ref-input').val());

            var oldformData = {
                head_id: [],
                tamount: [],
                ramount: [],
                challan_id: '',
                challan_amt: '',
                recipt_amt: '',
                recipt_date: '',
                late_amt: '',
                arrears: '',
                bank: '',
                ref: '',
                receive_type: '',
            };
            var tot = 0;

            $('input[name="oldhead_id[]"]').each(function() {
                oldformData.head_id.push($(this).val());
            });

            $('input[name="oldtamount[]"]').each(function() {
                oldformData.tamount.push($(this).val());
                tot += parseFloat($(this).val());
            });

            var rev = 0;
            $('input[name="oldramount[]"]').each(function() {
                oldformData.ramount.push($(this).val());
                rev += parseFloat($(this).val()) || 0;
            });

            var chal_id = modalHeadsDataDiv.find('.old_ch_id').val();
            var banks = modalHeadsDataDiv.find('.old_banks').val();
            var old_rec_type = modalHeadsDataDiv.find('.old_rec_types').val();

            oldformData.challan_id = chal_id;
            oldformData.challan_amt = tot;
            oldformData.recipt_date = $('#recipt_date').val();
            oldformData.recipt_amt = rev;
            oldformData.late_amt = 0;
            oldformData.arrears = 0;
            oldformData.bank = banks;
            oldformData.ref = old_ref;
            oldformData.receive_type = old_rec_type;

            if ({{ Auth::user()->type != 'company' ? 'true' : 'false' }}) {
                const minDate = new Date();
                minDate.setDate(minDate.getDate() - 3);
                const reciptDate = new Date(oldformData.recipt_date);
            }

            if (oldformData.recipt_amt <= 0) {
                show_toastr('error', 'Receipt Amount must be greater than 0', 'error');
                $oldSaveBtn.prop('disabled', false);
                return;
            }

            if (!oldformData.bank) {
                show_toastr('error', 'Please select a valid bank', 'error');
                $oldSaveBtn.prop('disabled', false);
                return;
            }

            if (!oldformData.ref || oldformData.ref === '') {
                show_toastr('error', 'Reference field is required', 'error');
                modalHeadsDataDiv.find('.old-ref-input').focus();
                $oldSaveBtn.prop('disabled', false);
                return;
            }

            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: 'paidchallan',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: oldformData,
                success: function(response) {
                    console.log('Response:', response);
                    window.location.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Something went wrong!');

                    $oldSaveBtn.prop('disabled', false);
                }
            });
        });
    </script>
@endsection