@extends('layouts.admin')
@section('page-title')
    {{ __('Studypack Receipts') }}
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
    </style>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Studypack Receipts') }}</li>
@endsection
@section('action-btn')
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding: 12px;">
                        {{ Form::open(['route' => ['studypackreceipts'], 'method' => 'GET', 'id' => 'studypack_receipt_submit']) }}
                        <div class="row d-flex justify-content-end ">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('default_date', __('Default Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('date', isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'), ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('default_bank', __('Default Bank'), ['class' => 'form-label']) }}
                                    {{ Form::select('default_bank', $accounts, null, ['class' => 'form-control select custom-select', 'id' => 'default_bank', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('studypack_receipt_submit').submit(); return false;"
                                    data-bs-title="{{ __('Apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('studypackreceipts') }}" class="btn mx-1 btn-sm btn-outline-danger"
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
        <div class="col-12">
        <div class="card p-4 table-responsive maximumHeightNew">
            <div style="width: 100%; text-align: center;">
                <p style="font-family:Edwardian Script ITC; font-size:3rem;"><b>The Lynx School</b></p>
            </div>
            <div style="width: 100%; text-align: center;">
                <p style="text-align: center; font-weight: 900; font-size: 1rem;">Study Pack Receiving Statement</p>
            </div>
            <div class="d-flex justify-content-between">
                <p><b>Period From :</b> {{ @$request->date_from ?? '' }}</p>
                <p><b>Period To :</b> {{ @$request->date_to ?? '' }}</p>
            </div>
            <table style="font-size:0.8rem; width:100%;">
                <thead>
                    <tr class="table_heads report_table">
                        <th>Sr#</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Challan No</th>
                        <th>Billing Month</th>
                        <th>Receipt Mode</th>
                        <th>Receipt Ref.</th>
                        <th>Bank</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php $runningBalance = 0; @endphp
                    @foreach ($recipts as $i => $recipt)
                        @php
                            $debit = (float) ($recipt->recipt_amount ?? 0);
                            $credit = 0;
                            $runningBalance = $runningBalance + $credit - $debit;
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ !empty($recipt->recipt_date) ? date('Y-m-d', strtotime($recipt->recipt_date)) : '-' }}</td>
                            <td>StudyPack Receipt for Challan No {{ $recipt->challan->challanNo ?? '-' }}</td>
                            <td>
                                @if (!empty($recipt->challan_id))
                                    <a href="{{ route('studypackchallan.print', $recipt->challan_id) }}" target="_blank" rel="noopener">
                                        {{ $recipt->challan->challanNo ?? '-' }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ !empty($recipt->challan->fee_month) ? date('M-Y', strtotime($recipt->challan->fee_month)) : '-' }}</td>
                            <td>{{ $recipt->receive_type ?? '-' }}</td>
                            <td>{{ $recipt->referance ?? '-' }}</td>
                            <td>{{ $accounts[$recipt->bank_id] ?? '-' }}</td>
                            <td>{{ number_format($debit, 2) }}</td>
                            <td>{{ $credit ? number_format($credit, 2) : '-' }}</td>
                            <td>{{ number_format($runningBalance, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

{{-- </div>
    </div> --}}
    <script>
        let account = [];
        let accountOptions = '';
        let all_accountOptions = '';



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
                $('#detailcard .alert-danger').remove();
                var siblingContainer = $('#siblingContainer');
                siblingContainer.empty();
                siblingContainer.hide();
                $('#headfee').empty();
                $('#total_fee').val(0);
                $('#rem_fee').val(0);
                $('#challan_amt').val(0);
                $('#remp_amt').val(0);
                $('#late_amt').val(0);
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

                    // Show loading state (optional)
                    $('#detailcard').show();
                    $('#detailcard .alert-danger').remove();
                    // const loadingMessage =
                    //     '<div class="alert alert-info" style="margin: 15px;"><i class="fa fa-spinner fa-spin"></i> Searching for challan...</div>';
                    // $('#detailcard').html(loadingMessage);

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
                                var siblingContainer = $('#detailcard');
                                siblingContainer.empty();
                                // Populate with new data
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
                                $('#siblingtable').show();
                                $('#headfee').css('display', 'block');

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
            var siblingContainer = $('#detailcard');
            siblingContainer.empty();            
            var row = $('<div class="row d-flex justify-content-center" style = "padding: 0.5rem 1.5rem !important;">');
            row.append('<div class="col-md-2"><b>Roll No:</b> ' + challandetail.student.roll_no + '</div>');
            row.append('<div class="col-md-3"><b>Student Name:</b> ' + challandetail.student.stdname + '</div>');
            row.append('<div class="col-md-3"><b>Father Name:</b> ' + challandetail.student.fathername + '</div>');
            row.append('<div class="col-md-2"><b>Issue Date:</b> ' + challandetail.issue_date + '</div>');
            row.append('<div class="col-md-2"><b>Due Date:</b> ' + challandetail.due_date + '</div>');
            siblingContainer.append(row);
            siblingContainer.show();
        }

        function populateHeadFee(headsData) {
            var headFeeContainer = $('#detailcard');
            headsData.forEach(function(head) {
                var row = $('<div class="row" style="padding: 0.5rem 1.5rem !important;">');
                row.append('<div class="col-md-4 mb-1">' + head.head_name +
                    '<input name="head_id[]" type="hidden" value="' + head.head_id + '"></div>');
                row.append(
                    '<div class="col-md-3 mb-1"><input name="tamount[]" class="form-control tamount" type="text" value="' +
                    head.amount + '" disabled></div>');
                row.append(
                    '<div class="col-md-3 mb-1"><input name="ramount[]" class="form-control ramount" style = "font-size: 13px;" type="number" value="" min="0" step="any"></div>'
                );
                headFeeContainer.append(row);
            });

            headFeeContainer.append(`<div style="display:flex; justify-content:center; padding: 0.5rem 1.5rem !important;" class="gap-2">
                        <label for="bank" style="display:block; margin-bottom:5px; margin-left:270px !important;" class=" ps-5"><strong>Bank Account</strong>
                            <select id="bank" class="form-control js-searchBox" style="width:200px; font-size: 12px;">
                                ${accountOptions} </select>
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
                    </div>
            `);
            var banks_id = $('#default_bank').val();
            $('#bank').val(banks_id);

        }

        $(document).on('change', '#default_bank', function() {
            $('#bank').val($(this).val());
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
                error: function(error) {
                    alert('Something Went Wrong...')
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
