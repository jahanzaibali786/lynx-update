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
                                    {{ Form::date('date', isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'), ['class' => 'form-control']) }}
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
                                {{-- export --}}
                                {{-- <button class="btn mx-1 btn-sm btn-outline-success" type="submit" name="export"
                                    value="excel" data-bs-title="{{ __('Download Report') }}"><span
                                        class="btn-inner--icon">Export</span>
                                </button> --}}
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
                {{-- <th>Type</th> --}}
                <th>Bank Account</th>
                <th>D Status</th>
                <th>Referance</th>
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
                    <tr style="  border-radius: 10px !important;">
                        <td>
                            <input type="text" value="{{ @$recipt->id }}" disabled
                                style="width:50px; font-size: 11px;">
                        </td>
                        <td>
                            <input type="text" value="{{ date('d/m/Y', strtotime($recipt->recipt_date)) }}" disabled
                                class="font_less" style="width:63px; font-size: 11px;">
                        </td>
                        <td>
                            <input type="text" value="{{ @$recipt->challan->challanNo }}" disabled style="width:60px; ">
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
                                value="{{ @$recipt->challan_amount + @$recipt->late_amount + @$recipt->arrears }}" disabled
                                style="width:60px; font-size: 12px;">
                        </td>
                        <td>
                            <input type="text"
                                value="{{ @$recipt->challan_amount + @$recipt->late_amount + @$recipt->arrears - @$recipt->recipt_amount }}"
                                disabled style="width:65px; font-size: 12px;">
                        </td>
                        {{-- <td>
                            <input type="text" value="RV" disabled style="width:50px; font-size: 13px;">
                        </td> --}}
                        <td>
                            {{ Form::select('default_bank', $accounts, @$recipt->bank_id, ['style' => 'width:100px; font-size: 12px;', 'disabled' => 'disabled']) }}
                        </td>
                        <td>
                            <select class="input" disabled>
                                @foreach ($options as $option)
                                    <option value="{{ $option }}"
                                        {{ $option == @$recipt->receive_type ? 'selected' : '' }}> {{ $option }}
                                    </option>
                                @endforeach>
                            </select>

                        </td>
                        <td>
                            <input type="text" value="{{ @$recipt->referance }}" disabled
                                style="width:80px; font-size: 11px;">
                        </td>
                        <td>
                            <input type="text" value="{{@$recipt->received->name}}" disabled
                                style="width:100px; font-size: 11px;">
                        </td>
@if (Auth::user()->type == 'company')
                            <td>
                                <a href="#!" data-size="lg"
                                    data-url="{{ route('student_receipt.edit', $recipt->id) }}" data-ajax-popup="true" title="Edit"
                                    class=" btn btn-sm btn-outline-primary" data-bs-title="{{ __('Edit') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span></a>
                            </td>
                        @endif
                    </tr>
                @endforeach
                <tr id="focus_row" style="  border-radius: 10px !important;">
                    <td>
                        <input type="text" value="" disabled style="width:50px; font-size: 11px;">
                    </td>
                    <td>
                        {{-- {{ Form::date('date', date('Y-m-d'),['class' => 'form-control']) }} --}}
                        <input type="date" value="{{ date('Y-m-d') }}" id="recipt_date" class="font_less" min="{{Auth::user()->type == 'company' ? '': date('Y-m-d', strtotime('-3 days'))}}"
                            style="width:70px; font-size: 11px;">
                    </td>
                    <td>
                        <input type="text" id="challan_id" value="" style="width:60px; ">
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

                        <input type="text" id="arrears" value="" disabled
                            style="width:50px; font-size: 13px;">
                    </td>
                    <td>
                        <input type="text" id="total_fee" value="" disabled
                            style="width:60px; font-size: 12px;">
                    </td>
                    <td>
                        <input type="text" id="rem_fee" value="" disabled
                            style="width:65px; font-size: 12px;">
                    </td>
                    {{-- <td>
                        <input type="text" value="RV" disabled style="width:50px; font-size: 13px;">
                    </td> --}}
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
                        <input type="text" value="{{Auth::user()->name}}" style="width:100px; font-size: 11px;" disabled>
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

    {{-- </div>
    </div> --}}
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
        let account =[];
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
                            document.getElementById('rem_fee').value = document.getElementById(
                                'total_fee').value - document.getElementById('remp_amt').value;
                               
                                bankAccounts = response.accounts;
                                accountOptions = '';
                                // Loop through the bankAccounts array and create options
                                Object.entries(bankAccounts).forEach(([value, text]) => {
                                    accountOptions += `<option value="${value}">${text}</option>`;
                                });

                                allAccounts = response.account_all;
                                
                                all_accountOptions = '';
                                // Loop through the bankAccounts array and create options
                                Object.entries(allAccounts).forEach(([value, text]) => {
                                    all_accountOptions += `<option value="${value}">${text}</option>`;
                                });
                                
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
            var siblingContainer = $('#siblingContainer');
            siblingContainer.empty();

            var row = $('<div class="row d-flex justify-content-center" style = "padding: 0.5rem 1.5rem !important;">');
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
            headsData.forEach(function(head) {
                var row = $('<div class="row">');
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

            // Get the default bank value
            var banks_id = $('#default_bank').val();

            headFeeContainer.append(`<div style="display:flex;" class="gap-2">
                        <label for="bank" style="display:block; margin-bottom:5px;"><strong>Bank Account</strong>
                            <select id="bank" class="form-control js-searchBox" style="width:150px; font-size: 12px;">
                                ${all_accountOptions}
                            </select>
                         </label>
                        <label for="rec_type" style="display:block; margin-bottom:5px;"><strong>D Status</strong>
                        <select class="input form-control" id="rec_type" style="width:100px" name="receive_type">
                            <option value="DD">DD</option>
                            <option value="OL">OL</option>
                            <option value="CHQ">CHQ</option>
                            <option value="CD">CD</option>
                        </select></label>
                        <label for="ref" style="display:block; margin-bottom:5px;"><strong>Referance</strong>
                        <input type="text" value="" name="ref" id="ref" class="form-control" required style="width:190px; font-size: 11px;"></label>
                    </div>
                <div class="d-flex justify-content-end gap-4" id="saveButton" style="position:relative; right:80px; padding-top: 10px;">
                    <button class="btn btn-success">Save</button>
                </div>
            `);

            // Set the default bank value after the select is created
            $('#bank').val(banks_id);
        }

        function populateArrears(previousUnpaidChallans) {
            var arrearsContainer = $('#arrearsdetails');
            arrearsContainer.empty();
            arrearsContainer.append('<h5><strong>Arrears :-</strong></h5><br>');
            previousUnpaidChallans.forEach(function(arrear) {
                var row = $(
                    '<div class="mb-3 arrear-row" style="cursor: pointer; display: flex; flex-direction: row; gap:20px;">'
                );
                // row.append('<div class=""><b>Challan No:</b> ' + arrear.challanNo +'</div>');
                // row.append('<div class=""><b>Challan No:</b> <span style="background-color: #100773; color: white; font-weight: bold; padding: 2px 5px; border-radius: 3px;">' + arrear.challanNo + '</span></div>');
                row.append(
                    '<div class=""><b>Challan No:</b> <span style="background-color: #100773; color: white; font-weight: bold; padding: 2px 5px; border-radius: 3px; animation: blink-effect 1s infinite;">' +
                    arrear.challanNo + '</span></div>');

                // Add the animation definition to your <style> section dynamically
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
                '<div class="col-md-4 mb-1">Referance</div><input name="old_challan_id" class="old_ch_id" type="hidden" value="' +
                arrearData.challanNo + '">');
                if(arrearData.owned_by == "{{Auth::user()->ownedId()}}"){
                    row.append(
                        `<div class="col-md-8 mb-1"><input type="text" value="" id="oldref" class = "old_ref form-control" style="font-size: 13px;"></div><div style="display:flex" class="gap-4"><label for="old_bank" style="display:block; margin-bottom:5px;">Bank Account
                            <select id="old_bank" name=default_bank class="form-control old_banks js-searchBox" style="width:280px; font-size: 12px;">
                                        ${all_accountOptions} </select>
                                </label>
                                <label for="old_rec_type" style="display:block; margin-bottom:5px;">D Status
                                <select class="input form-control old_rec_types" id="old_rec_type" style="width:150px" name="receive_type">
                                    <option value="DD">DD</option> <option value="OL">OL</option> <option value="CHQ">CHQ</option> <option value="CD">CD</option>
                                </select></label></div>`
                    );
                }else{
                    row.append(
                        `<div class="col-md-8 mb-1"><input type="text" value="" id="oldref" class = "old_ref form-control" style="font-size: 13px;"></div><div style="display:flex" class="gap-4"><label for="old_bank" style="display:block; margin-bottom:5px;">Bank Account
                            <select id="old_bank" name=default_bank class="form-control old_banks js-searchBox" style="width:280px; font-size: 12px;">
                                        ${accountOptions} </select>
                                </label>
                                <label for="old_rec_type" style="display:block; margin-bottom:5px;">D Status
                                <select class="input form-control old_rec_types" id="old_rec_type" style="width:150px" name="receive_type">
                                    <option value="DD">DD</option> <option value="OL">OL</option> <option value="CHQ">CHQ</option> <option value="CD">CD</option>
                                </select></label></div>`
                    );
                }
            var banks_id = $('#default_bank').val();
            modalHeadsData.append(row);
            
            // Set the default bank value after the select is created in the modal
            $('#old_bank').val(banks_id);
            
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
        }

        $(document).on('change', '#default_bank', function() {
            var selectedBank = $(this).val();
            
            // Update the #bank select if it exists
            if ($('#bank').length) {
                $('#bank').val(selectedBank);
            }
            
            // Update the #old_bank select if it exists
            if ($('#old_bank').length) {
                $('#old_bank').val(selectedBank);
            }
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
                
                if(reciptDate < minDate){
                    show_toastr('error', 'Receipt Date must be within the last 3 days', 'error');
                    return;
                }
            }
            if(formData.recipt_amt <= 0){
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
                url: 'paidchallan', // Replace with your server endpoint URL
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken // Include the CSRF token in the headers
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

        // Add event listener for the save button
        $(document).on('click', '#oldsaveButton', function() {
            event.preventDefault();
            // Collect form data
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
                tot += parseFloat($(this).val())
            });
            var rev = 0;
            $('input[name="oldramount[]"]').each(function() {
                oldformData.ramount.push($(this).val());
                rev += parseFloat($(this).val()) || 0;
            });

            // Find the parent modal
            var activeModal = $(this).closest('#arrearModal');

            if (!activeModal.length) {
                console.error('No active modal found.');
                return;
            }

            // Get the modalHeadsData div within the active modal
            var modalHeadsDataDiv = activeModal.find('.modalHeadsData');

            if (!modalHeadsDataDiv.length) {
                console.error('No modalHeadsData div found in the active modal.');
                return;
            }

            var lgInputs = modalHeadsDataDiv.find('.old_ch_id');
            var chal_id = '';
            lgInputs.each(function() {
                chal_id = $(this).val();
            });
            var lgInputs = modalHeadsDataDiv.find('.old_banks');
            var banks = '';
            lgInputs.each(function() {
                banks = $(this).val();
            });
            var lgInputs = modalHeadsDataDiv.find('.old_rec_types');
            var old_rec_type = '';
            lgInputs.each(function() {
                old_rec_type = $(this).val();
            });
            var lgInputs = modalHeadsDataDiv.find('.old_ref');
            var old_ref = '';
            lgInputs.each(function() {
                old_ref = $(this).val();
            });

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
                
                if(reciptDate < minDate){
                    show_toastr('error', 'Receipt Date must be within the last 3 days', 'error');
                    return;
                }
            }
            if(oldformData.recipt_amt <= 0){
                show_toastr('error', 'Recipt Amount must be greater than 0', 'error');
                return;
            }
            if (!oldformData.bank) {
                show_toastr('error', 'Please select a valid bank', 'error');
                return;
            }
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            // You can use AJAX to send the data to the server
            $.ajax({
                url: 'paidchallan', // Replace with your server endpoint URL
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken // Include the CSRF token in the headers
                },
                data: oldformData,
                success: function(response) {
                    console.log('Response:', response);
                    window.location.reload();
                },
                error: function(error) {
                    console.error('Error:', error);
                    // Handle error response
                }
            });
        });
    </script>
@endsection