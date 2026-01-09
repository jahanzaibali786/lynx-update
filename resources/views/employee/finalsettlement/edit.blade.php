@extends('layouts.admin')
@section('page-title')
    {{ __('Edit Employee Final Settlement') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Final Settlement') }}</li>
@endsection
@push('script-page')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // $(document).ready(function () {
        $(document).on('keyup', '.adj', function() {
            var totalSum = 0;
            $('.adj').each(function() {
                var value = parseFloat($(this).val());
                if (!isNaN(value)) {
                    totalSum += value
                }
            });
            $('#adj_total').val(totalSum);
            var a = $('#gros').val();
            var b = $('#tot-dec').val();
            var p = $('#paid_amount').val();
            var c = (parseFloat(a) + parseFloat(totalSum)) - parseFloat(b) - parseFloat(p);
            var d = c.toFixed(2);
            $('#f_val').empty().append(d);
            $('#credit').val(d);
        });

        $(document).on('keyup', '.dec', function() {
            var totalSum = 0;
            $('.dec').each(function() {
                var value = parseFloat($(this).val());
                // console.log(value);
                if (!isNaN(value)) {
                    totalSum += parseFloat(value)
                }
            });

            $('#tot-dec').val(totalSum);
            var a = $('#gros').val();
            var b = $('#adj_total').val();
            var p = $('#paid_amount').val();
            var c = (parseFloat(a) + parseFloat(b)) - parseFloat(totalSum) - parseFloat(p);
            var d = c.toFixed(2);
            $('#f_val').empty().append(d);
            $('#credit').val(d);
        });
        // });
        // Add field in addition (as table row)
        $(document).on('click', '#add_fields', function() {
            var fieldHTML = `
                <tr class="addition-row">
                    <td>
                        <input type="text" class="form-control mb-1" name="adjustment_labels[]" placeholder="Addition Label" required>
                    </td>
                    <td>
                        <b>RS.</b>    
                    </td>
                    <td>
                        <input type="number" class="form-control mb-1 adj" name="adjustment[]" placeholder="Addition Value" required step="0.01">
                    </td>
                    <td>
                        <a href="#" class="btn btn-danger remove_field_addition mb-1" data-bs-title="Remove Fields">
                            <i class="ti ti-trash"></i> 
                        </a>
                    </td>
                </tr>`;
            // Prepend after the add button row
            var $tbody = $(this).closest('table').find('tbody');
            var $addBtnRow = $tbody.find('tr').first();
            if ($addBtnRow.length) {
                $(fieldHTML).insertAfter($addBtnRow);
            } else {
                $tbody.prepend(fieldHTML);
            }
        });

        // Remove addition row
        $(document).on('click', '.remove_field_addition', function(e) {
            e.preventDefault();
            $(this).closest('tr').remove();
            var totalSum = 0;
            $('.adj').each(function() {
                var value = parseFloat($(this).val());
                if (!isNaN(value)) {
                    totalSum += value
                }
            });
            $('#adj_total').val(totalSum);
            var a = $('#gros').val();
            var b = $('#tot-dec').val();
            var c = (parseFloat(a) + parseFloat(totalSum)) - parseFloat(b);
            var d = c.toFixed(2);
            $('#f_val').empty().append(d);
            $('#credit').val(d);
        });

        // Add field in deduction (as table row)
        $(document).on('click', '#add_fields_deduction', function() {
            var fieldHTML = `
                <tr class="deduction-row">
                    <td>
                        <input type="text" class="form-control mb-1" name="deduction_labels[]" placeholder="Deduction Label" required>
                    </td>
                    <td>
                        <b>RS.</b>    
                    </td>
                    <td>
                        <input type="number" class="form-control mb-1 dec" name="deduction[]" placeholder="Deduction Value" required step="0.01">
                    </td>
                    <td>
                        <a href="#" class="btn btn-danger remove_field_deduction mb-1" data-bs-title="Remove Fields">
                            <i class="ti ti-trash"></i>
                        </a>
                    </td>
                </tr>`;
            // Prepend after the add button row
            var $tbody = $(this).closest('table').find('tbody');
            var $addBtnRow = $tbody.find('tr').first();
            if ($addBtnRow.length) {
                $(fieldHTML).insertAfter($addBtnRow);
            } else {
                $tbody.prepend(fieldHTML);
            }
        });

        // Remove deduction row
        $(document).on('click', '.remove_field_deduction', function(e) {
            e.preventDefault();
            $(this).closest('tr').remove();
            var totalSum = 0;
            $('.dec').each(function() {
                var value = parseFloat($(this).val());
                if (!isNaN(value)) {
                    totalSum += value
                }
            });
            $('#tot-dec').val(totalSum);
            var a = $('#gros').val();
            var b = $('#adj_total').val();
            var c = (parseFloat(a) + parseFloat(b)) - parseFloat(totalSum);
            var d = c.toFixed(2);
            $('#f_val').empty().append(d);
            $('#credit').val(d);
        });
        //chart add
        $(document).on('click', '#add_fields_chart', function() {
            var fieldHTML = `
                <tr class="chart-row">
                    <td>
                        <select name="chart_of_accounts[]" id="chart_of_accounts" class="form-control" required>
                            @foreach ($allchartOfAccounts as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control mb-1" name="memo[]" placeholder="Memo" required>
                    </td>
                    <td>
                        <input type="number" class="form-control debit mb-1" name="debit[]" placeholder="Debit" required value="0.0">
                    </td>
                    <td>
                        <input type="number" class="form-control credit mb-1" name="credit[]" placeholder="Credit" value="0.0" >
                    </td>
                    <td>
                        <a href="#" class="btn btn-sm btn-danger remove_field_chart mb-1" data-bs-title="Remove Fields">
                            <i class="ti ti-trash"></i>
                        </a>
                    </td>
                </tr>`;
            // Prepend after the add button row
            var $tbody = $(this).closest('table').find('tbody');
            $tbody.append(fieldHTML);
        });

        // Remove chart row
        $(document).on('click', '.remove_field_chart', function(e) {
            e.preventDefault();
            $(this).closest('tr').remove();
            recalculatePayable();
            validateSettlementButton();
        });

        //challan detail
        $(document).on('click', '.challan-detail-link', function() {
            var securityAmount = $(this).data('sececurity-amount');
            var notpaidsalaries = $(this).data('unpaid-salaries');

            if (securityAmount == 0 && notpaidsalaries == 0) {
                swal.fire({
                    title: 'Warning!',
                    text: 'No security amount and no unpaid salaries to adjust.',
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
                return;
            }
            var challanId = $(this).data('challan-id');
            var empId = $(this).data('emp-id');
            $('#challan-detail-body').html('<div class="text-center"><span class="spinner-border"></span></div>');
            $('#challanDetailModal').modal('show');
            $.ajax({
                url: '{{ route('challan.detail.ajax') }}', // You need to define this route in web.php
                type: 'GET',
                data: {
                    id: challanId,
                    empId: empId,
                    secAmount: securityAmount,
                    notpaidsalaries: notpaidsalaries
                },
                success: function(response) {
                    if (response.error) {
                        $('#challan-detail-body').html(
                            '<div class="alert alert-danger">' + response.error + '</div>');
                    } else {
                        $('#challan-detail-body').html(response);
                    }
                },
                error: function() {
                    $('#challan-detail-body').html(
                        '<div class="alert alert-danger">Failed to load challan details.</div>');
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            const initialEmpSec = {{ $remaining_security ?? 0 }};
            const initialUnpaid = {{ $remaining_unpaid_salaries ?? 0 }};

            function toggleChallanLinks() {
                let isEligible = $('input[name="is_eligible_security"]').is(':checked');

                if (!isEligible) {
                    // Disable all challan links
                    $('.challan-detail-link').addClass('disabled-challan-link').css({
                        'pointer-events': 'none',
                        'opacity': '0.6',
                        'cursor': 'not-allowed'
                    });

                    // Set Emp Sec to 0 and update adj_total
                    $('#emp_sec').val(0);
                    let unpaid = parseFloat($('#unpaid_sal').val()) || 0;
                    //trigger adj key inputs values
                    var adjSum1 = 0;
                    $('.adj').each(function() {
                        var value = parseFloat($(this).val());
                        if (!isNaN(value)) {
                            adjSum1 += value
                        }
                    });
                    $('#adj_total').val((unpaid + adjSum1).toFixed(1));
                } else {
                    // Enable links
                    $('.challan-detail-link').removeClass('disabled-challan-link').css({
                        'pointer-events': 'auto',
                        'opacity': '1',
                        'cursor': 'pointer'
                    });

                    // Set Emp Sec to initial and update adj_total
                    $('#emp_sec').val(initialEmpSec);
                    console.log($('#emp_sec').val(), 'sec');

                    let unpaid = parseFloat($('#unpaid_sal').val()) || 0;
                    //trigger adj key inputs values
                    var adjSum = 0;
                    $('.adj').each(function() {
                        var value = parseFloat($(this).val());
                        if (!isNaN(value)) {
                            adjSum += value
                        }
                    });
                    $('#adj_total').val((adjSum).toFixed(1));
                }

                recalculatePayable(); // Optional: if you want real-time net payable update
            }

            // Initial values into DOM (if not already)
            if (!$('#emp_sec').length) {
                $('<input>', {
                    type: 'hidden',
                    id: 'emp_sec',
                    value: initialEmpSec
                }).appendTo('form');
            }

            if (!$('#unpaid_sal').length) {
                $('<input>', {
                    type: 'hidden',
                    id: 'unpaid_sal',
                    value: initialUnpaid
                }).appendTo('form');
            }

            toggleChallanLinks();

            $('input[name="is_eligible_security"]').on('change', function() {
                toggleChallanLinks();
            });
        });

        function recalculatePayable() {
            let gros = parseFloat($('#gros').val()) || 0;
            let adj = parseFloat($('#adj_total').val()) || 0;
            let dec = parseFloat($('#tot-dec').val()) || 0;
            let paid = parseFloat($('#paid_amount').val()) || 0;
            let final = ((gros + adj) - dec - paid).toFixed(1);

            // console.log(gros, adj, dec, paid, final);
            $('#f_val').text(final);
            $('#credit').val(final);
        }
        $(document).ready(function() {

            const maxPayable = parseFloat($('#credit').val()) || 0;

            function calculateTotals() {
                let totalDebit = 0,
                    totalCredit = 0;

                $('.debit').each(function() {
                    const val = parseFloat($(this).val());
                    if (!isNaN(val)) totalDebit += val;
                });

                $('.credit').each(function() {
                    const val = parseFloat($(this).val());
                    if (!isNaN(val)) totalCredit += val;
                });

                $('.totalDebit').html(totalDebit.toFixed(2));
                $('.totalCredit').html(totalCredit.toFixed(2));

                return {
                    totalDebit,
                    totalCredit
                };
            }

            function limitRowInput($input, totalUsed) {
                let value = parseFloat($input.val());
                if (isNaN(value)) return;

                let remaining = maxPayable - totalUsed;
                if (value > remaining) {
                    $input.val(remaining.toFixed(2));
                }
            }

            $(document).on('keyup change', '.debit', function() {
                const $row = $(this).closest('tr');
                const $credit = $row.find('.credit');
                const $amount = $row.find('.amount');

                let debitVal = parseFloat($(this).val()) || 0;

                // Temporarily zero out current row to calculate accurate "other row" totals
                $(this).data('temp', debitVal);
                $(this).val(0); // temporarily clear to get total without this value
                const totals = calculateTotals();
                $(this).val($(this).data('temp'));

                limitRowInput($(this), totals.totalDebit);
                debitVal = parseFloat($(this).val()) || 0;

                $credit.val('').prop('readonly', true);
                $amount.text(debitVal.toFixed(2));

                if ($(this).val().trim() == '') {
                    $credit.prop('readonly', false);
                    $amount.val('0.00');
                }

                calculateTotals();
                validateSettlementButton();
            });

            $(document).on('keyup change', '.credit', function() {
                const $row = $(this).closest('tr');
                const $debit = $row.find('.debit');
                const $amount = $row.find('.amount');

                let creditVal = parseFloat($(this).val()) || 0;

                $(this).data('temp', creditVal);
                $(this).val(0); // temporarily clear to get total without this value
                const totals = calculateTotals();
                $(this).val($(this).data('temp'));

                limitRowInput($(this), totals.totalCredit);
                creditVal = parseFloat($(this).val()) || 0;

                $debit.val('').prop('readonly', true);
                $amount.text(creditVal.toFixed(2));

                if ($(this).val().trim() == '') {
                    $debit.prop('readonly', false);
                    $amount.val('0.00');
                }

                calculateTotals();
                validateSettlementButton();
            });

            // Lock fixed row if needed
            $('.fixed-row .debit, .fixed-row .credit').prop('readonly', true);
            // Call it on page load
            validateSettlementButton();
        });

        function validateSettlementButton() {
            const maxPayable = parseFloat($('#credit').val()) || 0;
            let totalDebit = 0;
            $('.debit').each(function() {
                const val = parseFloat($(this).val());
                if (!isNaN(val)) totalDebit += val;
            });

            let totalCredit = 0;
            $('.credit').each(function() {
                const val = parseFloat($(this).val());
                if (!isNaN(val)) totalCredit += val;
            });

            let isValid = totalDebit == totalCredit;
            $('#submit-settlement').prop('disabled', !isValid);
        }
    </script>
    <script>
        function rollbackChallanAdjustment(id) {
            //fire sawl
            swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('rollback.challan.adjustment') }}',
                        type: 'POST',
                        data: {
                            id: id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                swal.fire({
                                    icon: 'success',
                                    title: 'Challan Adjustment rollback successfully!',
                                    text: response.success,
                                    confirmButtonText: 'OK',
                                }).then((result) => {
                                    location.reload();
                                })
                            } else {
                                swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.error,
                                    confirmButtonText: 'OK',
                                })
                            }
                        },
                        error: function(xhr, status, error) {
                            swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error,
                                confirmButtonText: 'OK',
                            })
                        }
                    });
                }
            });
        }
        function rollbackPaymentVoucher(id) {
            //fire sawl
            swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('rollback.payment.voucher') }}',
                        type: 'POST',
                        data: {
                            id: id,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                swal.fire({
                                    icon: 'success',
                                    title: 'Payment Voucher rollback successfully!',
                                    text: response.success,
                                    confirmButtonText: 'OK',
                                }).then((result) => {
                                    location.reload();
                                })
                            } else {
                                swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.error,
                                    confirmButtonText: 'OK',
                                })
                            }
                        },
                        error: function(xhr, status, error) {
                            swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error,
                                confirmButtonText: 'OK',
                            })
                        }
                    });
                }
            });
        }

    </script>
@endpush

@section('content')
    <div class="card mt-4 p-4">
        <form action="{{ route('emp-final-settlement.update', $emplsetlement->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-4">
                    <p style="font-size:1rem;"><b>Branch
                            :</b>{!! \Auth::user()->getBranch(@$emplsetlement->employee->branch_id)->name !!}
                    </p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:center;"><b>Name :</b>{{ @$emplsetlement->employee->name }}</p>
                    <input type="hidden" name="employee_id" value="{{ @$emplsetlement->employee->id }}">
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:right;"><b>Department
                            :</b>{!! @$emplsetlement->employee->department->name !!}
                    </p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:left;"><b>Designation
                            :</b>{!! @$emplsetlement->employee->designation->name !!}
                    </p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:center;"><b>Company D.O.j
                            :</b>{!! @$emplsetlement->employee->company_doj !!}
                    </p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:right;"><b>Resignation
                            :</b>{!! @$emplsetlement->employee->resignation->resignation_date !!}</p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:left;"><b>Last Date Of attendance
                            :</b>{!! @$emplsetlement->employee->resignation->last_attendance_date !!}</p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:center;"><b>Notice Period
                            :</b>{{ !empty(@$emplsetlement->employee->resignation->notice_date) ? 'Yes' : 'No' }}</p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:right;"><b>Clearnce Certificate Date
                            :</b>{{ \Carbon\Carbon::parse(now())->format('d-F-Y') }}</p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:left;"><b>Status:</b>{!! @$emplsetlement->employee->category == 'Regular' ? 'Permanent' : 'Adhoc' !!}</p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:center;"><b>Reason of Leaving
                            :</b>{{ !empty(@$emplsetlement->employee->resignation) ? 'Resign' : 'Terminate' }}</p>
                </div>
                @php
                    $gross = 0;
                    $earnedgross = 0;
                    $company_doj = \Carbon\Carbon::parse($emplsetlement->employee->company_doj);
                    $resign_date = \Carbon\Carbon::parse($emplsetlement->employee->resignation->last_attendance_date);
                    $years = $resign_date->diffInYears($company_doj);
                    $months = $resign_date->diffInMonths($company_doj) % 12;
                    if ($years > 0) {
                        $service_tenure = $years . 'years -' . $months . 'months';
                    } else {
                        $service_tenure = $months . ' Months';
                    }
                    $payscale = @$emplsetlement->employee->employee_payscale_details->last();
                    $today = $resign_date->copy();
                    $previous_month_25th = $resign_date->copy()->subMonth()->day(25);
                    if ($today->day > 25) {
                        $start_date = $today->copy()->day(25);
                        $nextMonth = $resign_date->copy()->addMonth();
                        $existingslaryforthismonth = \App\Models\EmployeeMonthlySalary::where(
                            'employee_id',
                            $emplsetlement->employee->id,
                        )
                            ->whereMonth('salary_date', $nextMonth->month)
                            ->whereYear('salary_date', $nextMonth->year)
                            ->first();
                    } else {
                        $start_date = $today->copy()->subMonth()->day(25);
                        $existingslaryforthismonth = \App\Models\EmployeeMonthlySalary::where(
                            'employee_id',
                            $emplsetlement->employee->id,
                        )
                            ->whereMonth('salary_date', $resign_date->month)
                            ->whereYear('salary_date', $resign_date->year)
                            ->first();
                    }
                    if ($existingslaryforthismonth) {
                        $total_days = 0;
                    } else {
                        $total_days = $today->diffInDays($start_date);
                    }
                    $total_days_in_month = $resign_date->daysInMonth;
                @endphp
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:right;"><b>Service Tenure
                            :</b>{{ !empty($service_tenure) ? $service_tenure : '' }}</p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:left;"><b>Pay Scale
                            :</b>{{ !empty(@$payscale->scale->scale_no) ? $payscale->scale->scale_no : '' }}</p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:center;"><b>Basic Salary
                            :</b>{{ @$emplsetlement->basice_sal ? $emplsetlement->basice_sal : $inititaSalary }}</p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:right;"><b>Working Days
                            :</b>{{ @$emplsetlement->working_days ? $emplsetlement->working_days : $total_days_in_month }}
                    </p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:left;"><b>Security Amount
                            :</b>{{ $total_sec ? $total_sec : '0' }}</p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:center;"><b>Remaining Security
                            :</b>{{ $remaining_security ? $remaining_security : '0' }}</p>
                </div>
                <div class="col-md-4">
                    <p style="font-size:1rem; text-align:right;">
                        <input type="hidden" name="is_eligible_security" value="0">
                        <input type="checkbox" name="is_eligible_security"
                            id="" {{ $emplsetlement->sec_eligibilty == 1 ? 'checked' : '' }}><b>&nbsp;&nbsp;Eligible for Security</b></p>
                </div>
                <div class="col-md-6">
                    <p style="font-size:1rem; text-align:left;"><b>Unpaid Salaries
                            :</b>{{ $notpaidsalaries ? $notpaidsalaries : '0' }}</p>
                </div>
                <div class="col-md-6">
                    <p style="font-size:1rem; text-align:right;"><b>Remaining Unpaid Salaries
                            :</b>{{ $remaining_unpaid_salaries ? $remaining_unpaid_salaries : '0' }}</p>
                </div>
                <div class="col-md-12">
                    <p style="font-size:1rem; text-align:right;"><b>Available Amount for adjustment
                            :</b>{{ $totalAvailable ? $totalAvailable : '0' }}</p>
                </div>
            </div>
            <hr>
            <table class="">
                <thead class="table_heads">
                    <tr>
                        <th style="width:150px;"></th>
                        <th style="width:20px;"></th>
                        <th style="width:150px;">As Per Anexture 'R'</th>
                        <th style="width:20px;"><b>{{ @$emplsetlement->working_days }}</b></th>
                        <th style="width:150px;">Salary for the month
                            {{ \Carbon\Carbon::parse($resign_date)->format('F-Y') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (@$emplsetlement->finalsettlementHeads as $salhead)
                        @php
                            $gross += $salhead->head_value;
                            $earnedgross += $salhead->earned_value;
                            $salheads = \App\Models\EmpFinalSettlementHeads::with('salaryHead')
                                ->where('final_settlement_id', $emplsetlement->id)
                                ->where('head_id', $salhead->head_id)
                                ->first();
                        @endphp
                        <tr>
                            <td>{!! $salheads->salaryHead->head !!}</td>
                            <td><b>Rs.</b></td>
                            <td>{!! $salheads->head_value !!}</td>
                            <td><b>Rs.</b></td>
                            <td>{{ number_format($salhead->earned_value, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td>Gross (A)</td>
                        <td><b>RS.</b></td>
                        <td><b>{{ $gross }}</b></td>
                        <td><b>RS.</b></td>
                        <td><b>{{ number_format($earnedgross, 2) }}</b></td>
                    </tr>
                    <tr>
                        <td>Less 8% GPE + EOBI</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>0</td>
                    </tr>
                    <tr style="background-color:gray;">
                        <td><b>Net Payable</b></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td><b>{{ number_format($earnedgross, 2) }}</b></td>
                    </tr>
                    <tr>
                        <td>Less PESSI</td>
                        <td></td>
                        <td>0</td>
                        <td></td>
                        <td>{{ number_format($earnedgross, 2) }}</td>
                    </tr>
                </tbody>
            </table>
            <hr>
            {{-- //emp childs --}}
            @php
                $employee_childs = \App\Models\EmpChildrens::where('emp_id', $emplsetlement->emp_id)->get();
            @endphp
            @if (count($employee_childs) > 0)
                <div class="row">
                    <div class="col-md-12">
                        <h6><b>Employee Children</b></h6>
                    </div>
                    <div class="col-md-12" style="display: grid; grid-template-columns: 33% 33% 33%; gap: 10px;">
                        @foreach ($employee_childs as $child)
                            @php
                                $unpaidchallans = \App\Models\Challans::where('student_id', $child->student_id)
                                    ->where('status', '!=', 'paid')
                                    ->get();
                                $totalAmount = 0;
                                $challan_adj = \App\Models\EmployeeChildAdjustment::where(
                                    'employee_id',
                                    $emplsetlement->emp_id,
                                )
                                    ->where('student_id', $child->student_id)
                                    ->where('adj_type', 'challan')
                                    ->get();
                            @endphp

                            <div class=" mb-3">
                                <div class="card border rounded p-2 shadow-sm h-100">
                                    <div class="card-body p-2">
                                        <h6 class="card-title text-primary">{{ @$child->student->stdname }}</h6>
                                        <p class="mb-1"><strong>Roll No:</strong> {{ @$child->student->roll_no }}</p>
                                        <p class="mb-1"><strong>Class:</strong> {{ @$child->student->class->name }}</p>
                                        <p class="mb-1"><strong>Branch:</strong> {{ @$child->student->branches->name }}
                                        </p>
                                        @if ($unpaidchallans->count() > 0)
                                            <div class="mb-1">
                                                <strong>Unpaid Challans:</strong>
                                                <ul class="list-unstyled row mb-0 "
                                                    style="display: flex; flex-wrap: wrap; justify-content: space-around;">
                                                    @foreach ($unpaidchallans as $challan)
                                                        <li class="col-md-5">
                                                            <a href="javascript:void(0);"
                                                                class="badge bg-light text-dark border mb-1 challan-detail-link"
                                                                data-challan-id="{{ $challan->id }}"
                                                                data-emp-id="{{ $emplsetlement->emp_id }}"
                                                                data-sececurity-amount="{{ $remaining_security }}"
                                                                data-unpaid-salaries="{{ $remaining_unpaid_salaries }}">
                                                                Ch#{{ $challan->challanNo }} -
                                                                {{ date('d M Y', strtotime($challan->challan_date)) }}
                                                                -
                                                                Rs.{{ number_format($challan->total_amount - $challan->paid_amount) }}
                                                            </a>
                                                        </li>
                                                        @php
                                                            $totalAmount +=
                                                                $challan->total_amount - $challan->paid_amount;
                                                        @endphp
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @else
                                            <p class="text-muted small">No unpaid challans</p>
                                        @endif
                                        <br>
                                        <p class="mb-1" style="float: right; "><strong>Amount:</strong> <span
                                                style="border-bottom: 1px solid black; border-top: 1px solid black; padding:1px 0px 0px 50px;">Rs.
                                                {{ number_format($totalAmount) }}</span>
                                        </p>
                                        <br>
                                        @if (count($challan_adj) > 0)
                                            <table>
                                                <thead class="table_heads">
                                                    <tr>
                                                        <th></th>
                                                        <th>Voucher</th>
                                                        <th>Adjustment</th>
                                                        <th>Reference</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($challan_adj as $adj)
                                                        <tr>
                                                            <td>
                                                                {{-- rollback --}}
                                                                <button type="button" class="btn btn-sm btn-danger"
                                                                    onclick="rollbackChallanAdjustment('{{ $adj->id }}')">
                                                                    Rollback
                                                                </button>
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('journal-entry.show', ['journal_entry' => $adj->voucher_id]) }}"
                                                                    class="btn btn-sm btn-primary"
                                                                    style="color: var(--primary-darker) !important;">
                                                                    {{ Auth::user()->journalNumberFormat($adj->voucher_id) }}
                                                                </a>
                                                            </td>
                                                            <td>Rs.{{ number_format($adj->adjust_amount) }}</td>
                                                            <td>{{ $adj->ref }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <hr>
            @endif

            <input type="hidden" name="gross" id="gros" value="{{ $earnedgross }}">
            <table class="">
                <thead clas="table_heads">
                    <tr>
                        <th style="width:150px;">ADD</th>
                        <th style="width:20px;"></th>
                        <th style="width:150px;"></th>
                        <th style="width:150px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4">
                            <button type="button" class="btn btn-sm float-end btn-primary" id="add_fields">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                    @php
                        $total_adj = 0;
                    @endphp
                    @foreach ($emplsetlement->final_set_adj_ded as $adj)
                        @if ($adj->type == 'adjustment')
                            @php
                                $total_adj += $adj->value;
                            @endphp
                            
                            {{-- //security  --}}
                            @if ($adj->key == 'Emp. Sec. Refunded to emp as per MD Instruction')
                                <tr>
                                    <td>Emp. Sec. Refunded to emp as per MD Instruction</td>
                                    <input type="hidden" name="adjustment_labels[]"
                                        value="Emp. Sec. Refunded to emp as per MD Instruction">
                                    <td><b>RS.</b></td>
                                    <td><input type="text" id="emp_sec" class="form-control adj"
                                            style="width: 100px;" required name="adjustment[]" id=""
                                            value="{{ $remaining_security }}"></td>
                                    <td></td>
                                </tr>
                            @else
                                <tr>
                                    <td>{{ $adj->key }}</td>
                                    <input type="hidden" name="adjustment_labels[]" value="{{ $adj->key }}">
                                    <td><b>RS.</b></td>
                                    <td><input type="text" class="form-control adj" style="width: 100px;" required
                                            name="adjustment[]" id="" value="{{ $adj->value }}"></td>
                                    <td></td>
                                </tr>
                            @endif
                        @endif
                    @endforeach
                    <tr>
                        <td><b>Gross Amount Payable</b></td>
                        <input type="hidden" name="" value="Gross Amount Payable">
                        <td><b>RS.</b></td>
                        <td><input type="text" class="form-control" style="width: 100px;" name=""
                                id="adj_total" value="0.00" readonly>
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <input type="hidden" name="total_payable" id="credit" value="">
            <table class="">
                <thead class="table_heads">
                    <tr>
                        <th style="width:150px;">Deduction</th>
                        <th style="width:20px;"></th>
                        <th style="width:150px;"></th>
                        <th style="width:150px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="4">
                            <button type="button" class="btn btn-sm float-end btn-primary" id="add_fields_deduction">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                    @php
                        $total_ded = 0;
                    @endphp
                    @foreach ($emplsetlement->final_set_adj_ded as $ded)
                        @if ($ded->type == 'deduction')
                            @if (preg_match_all('/(\d+)\s+(casual|Days Leaves)/i', $ded->key, $matches))
                                @php
                                    $casual = (int) ($matches[1][0] ?? 0); // First match: casual
                                    $annual = (int) ($matches[1][1] ?? 0); // Second match: Days Leaves (annual)
                                    $totalLeaves = $casual + $annual;

                                    $perdaysal = (float) ($gross / $total_days_in_month);
                                    $lvsded = $perdaysal * $totalLeaves;
                                    $percentageaddlvs = ($lvsded * 10) / 100;
                                    $total_ded += $lvsded + $percentageaddlvs;
                                @endphp
                                <tr>
                                    <td>{{ $ded->key }}</td>
                                    <input type="hidden" name="deduction_labels[]" value="{{ $ded->key }}">
                                    <td><b>RS.</b></td>
                                    <td><input type="text" class="form-control dec" style="width: 100px;" required
                                            name="deduction[]" id="" value="{{ $lvsded }}" readonly></td>
                                    <td>{{ number_format($percentageaddlvs, 2) }}</td>
                                    <input type="hidden" class="dec" value="{{ $percentageaddlvs }}">
                                </tr>
                            @else
                                <tr>
                                    <td>{{ $ded->key }}</td>
                                    <input type="hidden" name="deduction_labels[]" value="{{ $ded->key }}">
                                    <td><b>RS.</b></td>
                                    <td><input type="text" class="form-control dec" style="width: 100px;" required
                                            name="deduction[]" id="" value="{{ $ded->value }}"></td>
                                    <td></td>
                                    @php
                                        $total_ded += $ded->value;
                                    @endphp
                                </tr>
                            @endif

                            {{-- @endif --}}
                        @endif
                    @endforeach
                    <tr>
                        <td><b>Total Deduction</b></td>
                        <input type="hidden" name="" value="Net Payable">
                        <td><b>RS.</b></td>
                        <td><input type="text" id="tot-dec" class="form-control" style="width: 100px; color:white;"
                                name="" id="" value="{{ $total_ded }}" readonly>
                        </td>
                        <td style="display:flex"><b>Payable : </b>&nbsp;
                            <h5 id="f_val"></h5>
                        </td>
                    </tr>
                </tbody>
            </table>
            <hr>
            @php
                        $total_paid = 0;
                    @endphp
            @if($paymentVouchers->count() > 0)
            <h4>Payment Vouchers</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th></th>
                        <th>Voucher</th>
                        <th>Reference</th>
                        <th>Paid Amount</th>
                    </tr>
                </thead>
                <tbody>
                    
                    @foreach ($paymentVouchers as $voucher)
                        <tr>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger" onclick="rollbackPaymentVoucher('{{ $voucher->id }}')">
                                    Rollback
                                </button>
                            </td>
                            <td>
                                <a href="{{ route('bank-payment-voucher.show', ['bank_payment_voucher' => $voucher->id]) }}" class="btn btn-sm btn-primary" style="color: var(--primary-darker) !important;">{{ Auth::user()->BPVNumberFormat($voucher->id) }}</a>
                            </td>
                            <td>{{ $voucher->description }}</td>
                            <td>{{ $voucher->accounts->sum('debit') }}</td>
                        </tr>
                        @php
                            $total_paid += $voucher->accounts->sum('debit');
                        @endphp
                    @endforeach
                </tbody>
            </table>
            @endif
            <input type="hidden" name="paid_amount" id="paid_amount" value="{{ $total_paid }}">
            <input type="hidden" name="total_payable" id="credit" value="">
            <table class="">
                <thead class="table_heads">
                    <tr>
                        <th>Chart of Accounts</th>
                        <th>Memo</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="chart_of_accounts[]" id="chart_of_accounts" class="form-control">
                                @foreach ($allchartOfAccounts as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="text" class="form-control" name="memo[]" id=""
                                placeholder="Memo"></td>
                        <td><input type="text" class="form-control debit" name="debit[]" id=""
                                value="0"></td>
                        <td><input type="text" class="form-control credit" name="credit[]" value="0">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm  btn-primary" id="add_fields_chart">
                                <i class="fas fa-plus"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <hr>
            <div class="d-flex justify-content-end">
                <input type="submit" class="btn btn-outline-primary" value="Update">
            </div>
        </form>
    </div>
     <!-- Challan Detail Modal -->
    <div class="modal fade" id="challanDetailModal" tabindex="-1" aria-labelledby="challanDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="challanDetailModalLabel">Challan Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="challan-detail-body">
                    <!-- Details will be loaded here -->
                    <div class="text-center"><span class="spinner-border"></span></div>
                </div>
            </div>
        </div>
    </div>
@endsection
