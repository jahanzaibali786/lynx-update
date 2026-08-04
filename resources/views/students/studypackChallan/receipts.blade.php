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
    <li class="breadcrumb-item">{{ $isDailyEntry ?? false ? __('Daily StudyPack Payments') : __('Studypack Receipts') }}</li>
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
                                        <li><button class="dropdown-item" type="submit" name="export" value="excel"><i class="ti ti-file me-2"></i>Excel</button></li>
                                        <li><button class="dropdown-item" type="submit" name="export" value="pdf"><i class="ti ti-download me-2"></i>Pdf</button></li>
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
                                <input type="text" id="challan_id" value="" placeholder="Enter…" autocomplete="off">
                            </td>
                            <td><input type="text" id="remp_amt" value="" disabled></td>
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
                        $remainingFee = $totalFee - @$recipt->recipt_amount;
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
        let account = [];
        let accountOptions = '';
        let all_accountOptions = '';
        let accountsData = {};
        let accountAllData = {};
        let defaultBankId = null;

        function getReceiveTypeOptions(chartAccount) {
            if (!chartAccount) {
                return '<option value="DD">DD</option><option value="OL">OL</option><option value="CHQ">CHQ</option><option value="CD">CD</option>';
            }
            const name = chartAccount.toUpperCase();
            if (name.includes('CSH') || name.includes('CASH')) {
                return '<option value="CD">CD</option>';
            }
            return '<option value="DD">DD</option><option value="OL">OL</option><option value="CHQ">CHQ</option>';
        }

        function updateReceiveType(bankId, selectElement) {
            const data = accountAllData[bankId] || accountsData[bankId] || {};
            $(selectElement).html(getReceiveTypeOptions(data.chart_account || ''));
        }

        //  for focus on challan No field and scroll to that section start
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
        //  for focus on challan No field and scrool to that section end

        // Search challan data for challan no
        // document.getElementById('challan_id').addEventListener('keyup', function() {
        // Debounced search for challan data
        // Improved debounce and clear logic
        // Improved debounce and error handling for challan search
        (function() {
            let challanSearchTimeout;
            let currentChallanNo = ''; // Track current challan number to avoid duplicate requests

            function clearChallanDetailCard() {
                $('#detailcard').hide();
                $('#detailcard .alert-danger, #detailcard .alert-info, #detailcard .alert-success').remove();
                var siblingContainer = $('#siblingContainer');
                siblingContainer.empty();
                siblingContainer.hide();
                $('#headfee').empty();
                $('#headfee').hide();
                $('#total_fee').val(0);
                $('#rem_fee').val(0);
                $('#challan_amt').val(0);
                $('#remp_amt').val(0);
                $('#late_amt').val(0);
            }

            function showSearchingMessage() {
                $('#detailcard').show();
                $('#detailcard .alert-danger, #detailcard .alert-success').remove();
                $('#detailcard .alert-info').remove();
                $('#detailcard').prepend('<div class="alert alert-info" style="margin: 15px;"><i class="fa fa-spinner fa-spin"></i> Searching challan...</div>');
            }

            function showErrorMessage(message) {
                // Show the detail card first
                $('#detailcard').show();

                // Remove any existing error messages
                $('#detailcard .alert-danger').remove();

                // Add the error message to the card
                const errorMessageElement = '<div class="alert alert-danger" style="margin: 15px;">' + message +
                    '</div>';
                $('#detailcard').prepend(errorMessageElement);
            }

            $(document).on('keyup', '#challan_id', function() {
                var challan_no = $(this).val().trim();

                // Clear timeout for previous request
                clearTimeout(challanSearchTimeout);

                // If input is empty or too short, clear everything
                if (challan_no.length < 2) {
                    clearChallanDetailCard();
                    currentChallanNo = '';
                    return;
                }

                // If it's the same challan number, don't make another request
                if (challan_no === currentChallanNo) {
                    return;
                }

                // Set timeout for debouncing - increased to 800ms for better user experience
                challanSearchTimeout = setTimeout(function() {
                    // Update current challan number
                    currentChallanNo = challan_no;

                    showSearchingMessage();

                    $.ajax({
                        url: '{{ route('challandata_for_studypackreceipt') }}',
                        type: 'GET',
                        data: {
                            challan_no: challan_no
                        },
                        success: function(response) {
                            // Clear loading message
                            $('#detailcard .alert-info').remove();

                            if (response.challandetail) {
                                // Clear previous data but keep the card visible
                                $('#detailcard').find('.alert').remove();
                                $('#siblingContainer').empty().hide();
                                $('#headfee').empty().hide();
                                $('#arrearsdetails').empty();
                                $('#detailcard').show();

                                document.getElementById('challan_amt').value = response
                                    .challandetail
                                    .total_amount - response.challandetail
                                    .concession_amount - response
                                    .challandetail.paid_amount;

                                var latefee = parseFloat(document.getElementById('late_amt')
                                    .value) || 0;
                                var challanAmount = parseFloat(response.challandetail
                                    .total_amount -
                                    response.challandetail.concession_amount - response
                                    .challandetail
                                    .paid_amount) || 0;

                                document.getElementById('total_fee').value = (latefee +
                                    challanAmount).toFixed(2);
                                document.getElementById('rem_fee').value = (parseFloat(
                                    document.getElementById(
                                        'total_fee').value) - parseFloat(document
                                    .getElementById('remp_amt').value || 0)).toFixed(2);

                                // Handle bank accounts
                                accountsData = response.accounts_data || {};
                                accountAllData = response.account_all_data || {};
                                defaultBankId = response.default_bank_id || null;

                                bankAccounts = response.accounts;
                                accountOptions = '';
                                Object.entries(bankAccounts).forEach(([value, text]) => {
                                    accountOptions +=
                                        `<option value="${value}">${text}</option>`;
                                });

                                allAccounts = response.account_all;
                                all_accountOptions = '';
                                Object.entries(allAccounts).forEach(([value, text]) => {
                                    all_accountOptions +=
                                        `<option value="${value}">${text}</option>`;
                                });

                                // Populate tables and show success
                                populateSiblingTable(response.challandetail);
                                populateHeadFee(response.headsData);
                                $('#siblingContainer').show();
                                $('#headfee').show();

                                // Show success message briefly
                                const successMessage =
                                    '<div class="alert alert-success" style="margin: 15px;">Challan found successfully!</div>';
                                $('#detailcard').prepend(successMessage);
                                $('#detailcard').show();
                                // Remove success message after 2 seconds
                                setTimeout(function() {
                                    $('#detailcard .alert-success').fadeOut(
                                        function() {
                                            $(this).remove();
                                        });
                                }, 2000);

                            } else {
                                // Challan not found
                                showErrorMessage(
                                    'Challan not found. Please check the challan number and try again.'
                                    );
                            }
                        },
                        error: function(xhr, status, error) {
                            // Handle error response
                            let errorMsg =
                                'An error occurred while searching for the challan.';

                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMsg = xhr.responseJSON.error;
                            } else if (xhr.status === 404) {
                                errorMsg = 'Challan not found.';
                            } else if (xhr.status === 500) {
                                errorMsg = 'Server error. Please try again later.';
                            } else if (xhr.status === 0) {
                                errorMsg = 'Network error. Please check your connection.';
                            }

                            showErrorMessage(errorMsg);
                            setTimeout(function() {
                                $('#detailcard').empty();
                            }, 2000);
                            // console.error('Error:', error);
                        }
                    });
                }, 800); // Increased debounce time to 800ms
            });

            // Clear search when input is cleared
            $(document).on('blur', '#challan_id', function() {
                var challan_no = $(this).val().trim();
                if (challan_no.length === 0) {
                    clearChallanDetailCard();
                    currentChallanNo = '';
                }
            });

            // Optional: Add enter key support for immediate search
            $(document).on('keydown', '#challan_id', function(e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    clearTimeout(challanSearchTimeout);
                    $(this).trigger('keyup');
                }
            });
        })();
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
            // Retrieve and parse the total amount and remaining amount from the row
            var challanAmt = parseFloat(row.find('.tamount').val()) || 0;
            var rempAmt = parseFloat(row.find('.ramount').val()) || 0;

            // Ensure remaining amount does not exceed total amount
            if (rempAmt > challanAmt) {
                rempAmt = challanAmt;
                row.find('.ramount').val(challanAmt.toFixed(1));
            }
            let total = 0;
            document.querySelectorAll('.ramount').forEach(element => {
                total += parseFloat(element.value) || 0;
            });
            document.getElementById('remp_amt').value = total;
            calculateRemainingAmount();
        }
        $(document).on('keyup', '.oldramount', function() {
            var row = $(this).closest('.row');
            calculateOldRAmount(row);
        });

        // Event listener for keyup event in amount inputs of a single row
        $(document).on('keyup', '.ramount', function() {
            var row = $(this).closest('.row');
            calculateRAmount(row);
        });

        // Function to calculate remaining amount based on row elements
        function calculateOldRAmount(row) {
            // Retrieve and parse the total amount and remaining amount from the row
            var challanAmt = parseFloat(row.find('.oldtamount').val()) || 0;
            var rempAmt = parseFloat(row.find('.oldramount').val()) || 0;

            // Ensure remaining amount does not exceed total amount
            if (rempAmt > challanAmt) {
                rempAmt = challanAmt;
                row.find('.oldramount').val(challanAmt.toFixed(1));
            }
        }

        // Event listener for keyup event in amount inputs of a single row


        function populateSiblingTable(challandetail) {
            var siblingContainer = $('#siblingContainer').empty();
            var row = $('<div class="row d-flex justify-content-center" style="padding: 0.5rem 1.5rem !important;">');
            row.append('<div class="col-md-2"><b>Roll No:</b> ' + challandetail.student.roll_no + '</div>');
            row.append('<div class="col-md-3"><b>Student Name:</b> ' + challandetail.student.stdname + '</div>');
            row.append('<div class="col-md-3"><b>Father Name:</b> ' + challandetail.student.fathername + '</div>');
            row.append('<div class="col-md-2"><b>Issue Date:</b> ' + challandetail.issue_date + '</div>');
            row.append('<div class="col-md-2"><b>Due Date:</b> ' + challandetail.due_date + '</div>');
            siblingContainer.append(row);
            siblingContainer.show();
        }

        function populateHeadFee(headsData) {
            var headFeeContainer = $('#headfee').empty();
            if (!headsData || headsData.length === 0) {
                headFeeContainer.hide();
                return;
            }

            headsData.forEach(function(head) {
                var row = $('<div class="row" style="padding: 0.5rem 1.5rem !important;">');
                row.append('<div class="col-md-4 mb-1">' + head.head_name +
                    '<input name="head_id[]" type="hidden" value="' + head.head_id + '"></div>');
                row.append(
                    '<div class="col-md-3 mb-1"><input name="tamount[]" class="form-control tamount" type="text" value="' +
                    head.amount + '" disabled></div>');
                row.append(
                    '<div class="col-md-3 mb-1"><input name="ramount[]" class="form-control ramount" style="font-size: 13px;" type="number" value="" min="0" max="' + head.amount + '" step="any"></div>'
                );
                headFeeContainer.append(row);
            });

            headFeeContainer.append(`<div style="display:flex; justify-content:center; padding: 0.5rem 1.5rem !important;" class="gap-2">
                        <label for="bank" style="display:block; margin-bottom:5px; margin-left:270px !important;" class="ps-5"><strong>Bank Account</strong>
                            <select id="bank" class="form-control js-searchBox" style="width:200px; font-size: 12px;">
                                ${all_accountOptions}</select>
                         </label>
                        <label for="rec_type" style="display:block; margin-bottom:5px;"><strong>D Status</strong>
                        <select class="input form-control" id="rec_type" style="width:100px" name="receive_type">
                            <option value="DD">DD</option>
                            <option value="OL">OL</option>
                            <option value="CHQ">CHQ</option>
                            <option value="CD">CD</option>
                        </select></label>
                        <label for="ref" style="display:block; margin-bottom:5px;"><strong>Referance</strong>
                        <input type="text" value="" name="reference" id="ref" class="form-control" required style="width:320px; font-size: 11px;"></label>
                        <div class="d-flex justify-content-end gap-4" id="saveButton" style="position:relative; top:15px; height:40px;">
                            <button class="btn btn-success">Save</button>
                        </div>
                    </div>`);
            var banks_id = $('#default_bank').val() || defaultBankId;
            $('#bank').val(banks_id);
            updateReceiveType(banks_id, '#rec_type');
            $('#bank').on('change', function() {
                updateReceiveType($(this).val(), '#rec_type');
            });
            headFeeContainer.show();
        }

        $(document).on('change', '#default_bank', function() {
            var selectedBank = $(this).val();
            $('#bank').val(selectedBank);
            $('#default_bank_display').val(selectedBank);
        });

        if ($('#default_bank').length && $('#default_bank_display').length) {
            var selectedBank = $('#default_bank').val();
            $('#default_bank_display').val(selectedBank);
        }

        if ($('#default_date').length && $('#recipt_date').length) {
            $('#recipt_date').val($('#default_date').val());
        }

        $(document).on('change', '#default_date', function() {
            $('#recipt_date').val($(this).val());
        });

        // Add event listener for the save button
        $(document).on('click', '#saveButton', function() {
            event.preventDefault();
            // Collect form data
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
            formData.ref = $('#ref').val();
            formData.receive_type = $('#rec_type').val();
            if ({{ Auth::user()->type != 'company' ? 'true' : 'false' }}) {
                const minDate = new Date();
                minDate.setDate(minDate.getDate() - 3);
                const reciptDate = new Date(formData.recipt_date);

                if (reciptDate < minDate) {
                    show_toastr('error', 'Receipt Date must be within the last 3 days', 'error');
                    return;
                }
            }
            if (formData.recipt_amt <= 0) {
                show_toastr('error', 'Recipt Amount must be greater than 0', 'error');
                return;
            }

            let totalItemPayment = 0;
            $('.ramount').each(function() {
                const value = parseFloat($(this).val()) || 0;
                const maxValue = parseFloat($(this).attr('max')) || 0;
                totalItemPayment += value;
                if (value > maxValue) {
                    show_toastr('error', 'Each item payment cannot exceed its payable amount.', 'error');
                    $(this).focus();
                    return false;
                }
            });
            if (totalItemPayment > parseFloat($('#remp_amt').val()) || totalItemPayment <= 0) {
                show_toastr('error', 'The item-wise payments must match the receipt amount and stay within the payable amounts.', 'error');
                return;
            }
            if (!formData.bank) {
                show_toastr('error', 'Please select a valid bank', 'error');
                return;
            }
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            // You can use AJAX to send the data to the server
            $.ajax({
                url: '{{route('studypackpaid')}}', // Replace with your server endpoint URL
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken // Include the CSRF token in the headers
                },
                data: formData,
                success: function(response) {
                    var row = document.getElementById('focus_row');
                    if (row) {
                        row.remove();
                    }
                    $('#detailcard').hide();
                    var siblingContainer = $('#siblingContainer');
                    siblingContainer.empty();
                    siblingContainer.hide();
                    $('#headfee').empty();
                    $('#new_data').append(response.data)
                    var banks_id = $('#default_bank').val();
                    $('#bank').val(banks_id);
                },
                error: function(xhr) {
                    alert('Something Went Wrong...');
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        console.error('Error:', xhr.responseJSON.error);
                    } else {
                        console.error('An unknown error occurred.');
                    }
                }
            });
        });

        
    </script>
@endsection
