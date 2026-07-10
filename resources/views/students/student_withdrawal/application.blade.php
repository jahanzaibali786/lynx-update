@extends('layouts.admin')
@section('page-title')
    {{ __('Withdrawl Application') }}
@endsection
@php
    $isHO = \Auth::user()->type == 'company' || \Auth::user()->type == 'super admin';
@endphp
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>

    <script>
        $(document).ready(function() {

            $('#calculateBalance').on('click', function(event) {
                event.preventDefault();
                var studentId = {{ @$studentwithdrawal->student_id }};

                $.ajax({
                    url: '{{ route('calculate.balance') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        student_id: studentId
                    },
                    success: function(response) {
                        if (response.admission_error) {
                            $('#error_ch')
                                .text(response.admission_error)
                                .css('color', 'red')
                                .show();
                        } else {
                            $('#error_ch').text('').hide();
                        }

                        $('#actual_fee').val(response.actual_fee);
                        $('#security_deposit').val(Number(response.security_deposit).toFixed(
                            2));
                        $('#security_payable').val(response.security_payable);
                        $('#other_fee').val(response.other_fee);
                        $('#refund').val(response.refund);
                        $('#notice_fee').val(response.notice_fee);
                        $('#other_deduction').val(response.other_deduction);
                        $('#total_payables').val(response.total_payables);
                        $('#total_receivables').val(response.total_receivables);
                        $('#net_balance').val(response.net_balance);
                    },
                    error: function(xhr, status, error) {
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            alert(xhr.responseJSON.error);
                        } else {
                            alert('An error occurred while calculating the balance.');
                        }
                    }
                });
            });

            $('.adj_put').on('input', function() {
                var max = parseFloat($(this).data('max')) || 0;
                var val = parseFloat($(this).val());
                if (isNaN(val) || val < 0) {
                    $(this).val(0);
                    return;
                }
                if (val > max) {
                    $(this).val(max);
                }
            });



        });

        $(document).on('input', '.oldramount', function() {
            let py = parseFloat($('#pya').val()) || 0; // Total available
            let originalMax = parseFloat($(this).attr('data-original-max')) || parseFloat($(this).attr('max')) || 0;
            var val = parseFloat($(this).val());
            // Adjust max based on py
            let currentMax = (py < originalMax) ? py : originalMax;
            $(this).attr('max', currentMax); // Set max attribute to the new max

            if (isNaN(val) || val < 0) {
                $(this).val(0);
                val = 0;
            }

            if (val > currentMax) {
                $(this).val(currentMax);
                val = currentMax;
            }

            // Calculate sum of all .oldramount fields
            var sum = 0;
            $('.oldramount').each(function() {
                var v = parseFloat($(this).val());
                if (!isNaN(v)) sum += v;
            });

            // If sum exceeds py, revert this input to its previous value
            if (sum > py) {
                $(this).val(oldValues[this.name] || 0);
            } else {
                oldValues[this.name] = $(this).val();
            }
        });




        function submitAdjustment(challanNo) {

            var modal = $('#challanDetailModal' + challanNo);
            var headIds = [];
            var amounts = [];
            var valid = true;

            // const adjAmount = document.getElementById(challanNo).value;

            // Ensure the value is valid before proceeding
            // if (adjAmount === "" || adjAmount < 0) {
            //     show_toastr('error', 'Please enter a valid adjustment amount.', 'error');
            //     return;
            // }
            var py = parseFloat($('#pya').val()) || 0;
            // AJAX request to submit the data
            var form = $('#adjustmentForm' + challanNo);
            var formData = form.serialize(); // All form data
            $.ajax({
                url: '{{ route('submit_adjustment') }}',
                type: 'POST',
                data: {
                    challanNo: challanNo,
                    headIds: headIds,
                    amounts: amounts,
                    sec_amount: py,
                    form: formData,

                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        show_toastr('success', 'Adjustment submitted successfully!', 'success');
                        location.reload();
                    } else {
                        show_toastr('error', 'Failed to submit adjustment', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    alert("An error occurred. Please try again.");
                }
            });
        }



        function deleteAdjustment(id) {

            Swal.fire({
                title: 'Are you sure?',
                text: "This adjustment will be permanently deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff3a6e', //red color for delete action
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Rollback!',
                cancelButtonText: 'Cancel'
            }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: '{{ route('delete_adjustment') }}',
                    type: 'POST',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {

                        if (response.success) {
                            show_toastr('success', 'Adjustment deleted successfully!', 'success');
                            location.reload();
                        } else {
                            show_toastr('error', 'Failed to delete adjustment', 'error');
                        }

                    },
                    error: function() {
                        show_toastr('error', 'Something went wrong', 'error');
                    }
                });

            });
        }
        $(document).on('click', '.challan-detail-link', function() {
            var payable = parseFloat($(this).data('payable')) || 0; // Current clicked amount
            var challanNo = $(this).data('challan_id');

            var py = parseFloat($('#pya').val()) || 0; // Previously stored amount
            // console.log(py,payable,challanNo);
            console.log(py);

            if (py > 0) { // ✅ If previously stored amount is greater than zero

                $('#challan-detail-body').html(
                    '<div class="text-center"><span class="spinner-border"></span></div>'
                );

                // Show modal
                $('#challanDetailModal').modal('show');
                $.ajax({
                    url: '{{ route('student-challan-detail') }}',
                    type: 'GET',
                    data: {
                        id: challanNo,
                    },
                    success: function(response) {
                        $('#challan-detail-body').html(response.html);
                    },
                    error: function() {
                        $('#challan-detail-body').html(
                            '<div class="alert alert-danger">Failed to load challan details.</div>');

                    }
                })
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No payable amount available.', // Proper error message
                    confirmButtonText: 'OK',
                });
            }
        });
        $(document).on('click', '.challan-ajustment-link', function() {
            var challanNo = $(this).data('challan_id');
            var adjustment_id = $(this).data('adjustment_id');

            $('#challan-detail-body').html(
                '<div class="text-center"><span class="spinner-border"></span></div>'
            );

            // Show modal
            $('#challanDetailModal').modal('show');
            $.ajax({
                url: '{{ route('student-adjust-detail') }}',
                type: 'GET',
                data: {
                    id: adjustment_id,
                },
                success: function(response) {
                    $('#challan-detail-body').html(response.html);
                },
                error: function() {
                    $('#challan-detail-body').html(
                        '<div class="alert alert-danger">Failed to load challan details.</div>');

                }
            })

        });
        $(document).ready(function() {
            // Automatically trigger the click event
            $('#calculateBalance').trigger('click');
        });

        var branchSnapshot = {!! json_encode($studentwithdrawal->branch_snapshot ?? []) !!};
        var hoSnapshot = {!! json_encode($studentwithdrawal->ho_snapshot ?? []) !!};

        function applySnapshot(snapshot) {
            if (!snapshot) return;
            if (snapshot.actual_fee !== undefined) $('#actual_fee').val(snapshot.actual_fee);
            if (snapshot.security_deposit !== undefined) $('#security_deposit').val(Number(snapshot.security_deposit)
                .toFixed(2));
            if (snapshot.security_payable !== undefined) $('#security_payable').val(snapshot.security_payable);
            if (snapshot.other_fee !== undefined) $('#other_fee').val(snapshot.other_fee);
            if (snapshot.other_account !== undefined) $('[name="other_account"]').val(snapshot.other_account);
            if (snapshot.refund !== undefined) $('#refund').val(snapshot.refund);
            if (snapshot.notice_fee !== undefined) $('#notice_fee').val(snapshot.notice_fee);
            if (snapshot.other_deduction !== undefined) $('#other_deduction').val(snapshot.other_deduction);
            if (snapshot.total_payables !== undefined) $('#total_payables').val(snapshot.total_payables);
            if (snapshot.total_receivables !== undefined) $('#total_receivables').val(snapshot.total_receivables);
            if (snapshot.net_balance !== undefined) $('#net_balance').val(snapshot.net_balance);
        }

        @if (!$isHO)
            $(document).on('click', '#sendToHoBtn', function() {
                var formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('actual_fee', $('#actual_fee').val());
                formData.append('security_deposit', $('#security_deposit').val());
                formData.append('security_payable', $('#security_payable').val());
                formData.append('other_fee', $('#other_fee').val());
                formData.append('other_account', $('[name="other_account"]').val());
                formData.append('refund', $('#refund').val());
                formData.append('notice_fee', $('#notice_fee').val());
                formData.append('other_deduction', $('#other_deduction').val());
                formData.append('total_payables', $('#total_payables').val());
                formData.append('total_receivables', $('#total_receivables').val());
                formData.append('net_balance', $('#net_balance').val());
                formData.append('remarks', $('#remarks').val());

                $.ajax({
                    url: '{{ route('fwdtoho', $studentwithdrawal->id) }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        show_toastr('success', 'Forwarded to HO successfully.');
                        setTimeout(function() {
                            window.location.href = '{{ route('withdrawlstudent.index') }}';
                        }, 1000);
                    },
                    error: function(xhr) {
                        show_toastr('error', xhr.responseJSON ? xhr.responseJSON.error :
                            'Error forwarding to HO');
                    }
                });
            });
        @endif
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Withdrawl Application') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        @if (!$isHO && @$studentwithdrawal->fwd_to_ho == 0)
            <button type="button" id="sendToHoBtn" class="btn btn-sm btn-outline-warning">Send to HO</button>
        @endif
        @if ($isHO)
            @if (@$studentwithdrawal->branch_snapshot)
                <button type="button" class="btn btn-sm btn-outline-info" onclick="applySnapshot(branchSnapshot)"
                    data-bs-title="{{ __('Branch Calculation') }}">
                    Branch Calc
                </button>
            @endif
            @if (@$studentwithdrawal->ho_snapshot)
                <button type="button" class="btn btn-sm btn-outline-warning" onclick="applySnapshot(hoSnapshot)"
                    data-bs-title="{{ __('Company Calculation') }}">
                    Company Calc
                </button>
            @endif
            <a href="{{ route('student_withdrawal.settlement_certificate', $studentwithdrawal->id) }}" target="_blank"
                class="btn btn-sm btn-outline-success" data-bs-title="{{ __('Clearance Certificate') }}">
                Clearance Certificate </a>
            @if ($studentwithdrawal->status == 'approved')
                <a href="{{ route('student_withdrawal.certificate_print', $studentwithdrawal->id) }}" target="_blank"
                    class="btn btn-sm btn-outline-secondary" data-bs-title="{{ __('School Leaving Certificate') }}">
                    SLC
                </a>
            @endif
        @endif
        @if (@$withdrawal_challan)
            <button type="button" class="btn btn-sm btn-primary"
                onclick="window.open('{{ route('challan.show', $withdrawal_challan->id) }}', '_blank')"
                data-bs-title="{{ __('Challan') }}">
                Challan
            </button>
        @endif
    </div>
@endsection
@section('content')
    <div class="card mt-4 p-4">
        {!! Form::open(['route' => ['withdrawlapplicationstore', $studentwithdrawal->id], 'method' => 'POST']) !!}
        <div class="row d-flex justify-content-end ">
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('document_number', __('Document No'), ['class' => 'form-label']) }}
                    {{ Form::text('document_number', @$studentwithdrawal->document_no, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('application_date', __('Application Date'), ['class' => 'form-label']) }}
                    {{ Form::date('application_date', @$studentwithdrawal->apply_date, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('withdrawl_date', __('Withdrawl Date'), ['class' => 'form-label']) }}
                    {{ Form::date('withdrawl_date', @$studentwithdrawal->withdraw_date, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('expected_readmission_date', __('Expected Re-admission Date'), ['class' => 'form-label']) }}
                    {{ Form::date('expected_readmission_date', old('expected_readmission_date', optional($studentwithdrawal->expected_readmission_date)->format('Y-m-d') ?? date('Y-m-d', strtotime('+3 months'))), ['class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>

        </div>
        <div class="row d-flex justify-content-end mt-1 ">
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('roll_no', __('Roll No'), ['class' => 'form-label']) }}
                    {{ Form::text('roll_no', @$studentwithdrawal->student->roll_no, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('student_name', __('Student Name'), ['class' => 'form-label']) }}
                    {{ Form::text('student_name', @$studentwithdrawal->student->stdname, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('father_name', __('Fahter Name'), ['class' => 'form-label']) }}
                    {{ Form::text('father_name', @$studentwithdrawal->student->fathername, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                    {{ Form::text('class', @$studentwithdrawal->student->class->name, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>

        </div>
        <div class="row d-flex justify-content-start mt-1 ">
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                    {{ Form::text('branch', @$studentwithdrawal->branch->name, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('withdraw_reason', __('Withdraw Reason'), ['class' => 'form-label']) }}
                    {{ Form::text('withdraw_reason', @$studentwithdrawal->reason, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mr-2">
                {{ Form::label('remarks', __('Branch Remarks'), ['class' => 'form-label']) }}
                {{ Form::text('remarks', @$studentwithdrawal->remark, $isHO || @$studentwithdrawal->fwd_to_ho == 1 ? ['class' => 'form-control', 'readonly' => 'readonly'] : ['class' => 'form-control']) }}
            </div>

        </div>
        @if ($isHO)
            <div class="row d-flex justify-content-start mt-1 ">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mr-2">
                    {{ Form::label('ho_remarks', __('HO Remarks'), ['class' => 'form-label']) }}
                    {{ Form::textarea('ho_remarks', @$studentwithdrawal->ho_remarks, ['class' => 'form-control', 'rows' => 3]) }}
                </div>
                <div class="mt-2">
                    <button type="button" id="saveBasicsBtn" class="btn btn-success">{{ __('Save Basics') }}</button>
                </div>
                <script>
                    $(document).on('click', '#saveBasicsBtn', function() {
                        var formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');
                        formData.append('remarks', $('#remarks').val());
                        formData.append('ho_remarks', $('#ho_remarks').val());

                        $.ajax({
                            url: '{{ route('withdrawlapplication.savebasics', $studentwithdrawal->id) }}',
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                show_toastr('success', response.success);
                            },
                            error: function(xhr) {
                                show_toastr('error', xhr.responseJSON ? xhr.responseJSON.error :
                                    'Error saving basics');
                            }
                        });
                    });
                </script>
            </div>
        @endif
        <hr>
        <div class="row d-flex justify-content-start mt-1 ">
            @if ($PrevChallan->isNotEmpty())
                <h4>Unpaid Challans</h4>
                <table class="">
                    <thead class="table_heads">
                        <tr>
                            <th>#</th>
                            <th>Preview</th>
                            <th>Challan No</th>
                            <th>Challan Details</th>
                            <th>Billing Month</th>
                            <th>Payable Fee</th>
                            <th>Total Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $tot = 0;
                            $rec = 0;
                        @endphp
                        @foreach ($PrevChallan as $prev)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{-- <button id="submit_{{ $prev->challanNo }}" class="btn btn-sm btn-primary"
                                        onclick="submitAdjustment('{{ $prev->challanNo }}')">Submit</button>
                                    <button id="submit_{{ $prev->challanNo }}" class="btn btn-sm btn-danger"
                                        onclick="deleteAdjustment('{{ $prev->challanNo }}')">Rollback</button> --}}
                                    <button type="button" class="btn btn-sm btn-primary"
                                        id="submit_{{ $prev->challanNo }}"
                                        onclick="window.open('{{ route('challan.show', $prev->id) }}', '_blank')"
                                        data-bs-toggle="Print Challan" data-bs-title="Send to Head Office"
                                        data-bs-title="{{ __('Preview') }}">
                                        Preview
                                    </button>
                                </td>
                                <!-- <td>
                                                                <input type="number" name="form-control adj_amount{{ $prev->challanNo }}"
                                                                    id="{{ $prev->challanNo }}" data-ids="{{ $prev->challanNo }}"
                                                                    data-max="{{ $payable }}" class="adj_put" min="0"
                                                                    max='{{ $payable }}' />
                                                            </td> -->

                                <td>
                                    <a href="javascript:void(0);" class="challan-detail-link btn-sm btn-primary"
                                        data-challan_id="{{ $prev->id }}" data-challanno="{{ $prev->challanNo }}"
                                        data-duedate="{{ $prev->due_date }}"
                                        data-feemonth="{{ date('d-M-Y', strtotime($prev->fee_month)) }}"
                                        data-payable="{{ $prev->total_amount - ($prev->paid_amount + $prev->concession_amount) }}"
                                        data-total="{{ $prev->total_amount - $prev->concession_amount }}">
                                        Challan No: {{ $prev->challanNo }}
                                    </a>
                                </td>
                                <td>
                                    Challan Due Date: {{ $prev->due_date }}
                                </td>
                                <td>{{ date('M-Y', strtotime($prev->fee_month)) }}</td>
                                <td id="pay_{{ $prev->challanNo }}">
                                    {{ $prev->total_amount - ($prev->paid_amount + $prev->concession_amount) }}</td>
                                <td id="tot_{{ $prev->challanNo }}">
                                    {{ $prev->total_amount - $prev->concession_amount }}</td>
                                @php
                                    $tot += $prev->total_amount - ($prev->paid_amount + $prev->concession_amount);
                                    $rec += $prev->total_amount - $prev->concession_amount;
                                @endphp
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="5" style="text-align:right;"><strong> Total </strong></td>
                            <td><strong>{{ $tot }} </strong></td>
                            <td><strong>{{ $rec }} </strong></td>
                        </tr>
                    </tbody>
                </table>
            @else
                <p>No pending challan All challans are paid.</p>
            @endif
        </div>
        <hr>
        <div class="row d-flex justify-content-start mt-1 ">
            @if ($adj_entry->isNotEmpty())
                <h4>Previous Adjustments</h4>
                <table class="">
                    <thead class="table_heads">
                        <tr>
                            <th>#</th>
                            <th>Action</th>
                            <th>Challan No</th>
                            <th>Billing Month</th>
                            <th>Adjusted Date</th>
                            <th>Adjusted Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rel = 0;
                        @endphp
                        @foreach ($adj_entry as $pre)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td> <button id="rollback_{{ $pre->id }}" class="btn btn-sm btn-danger"
                                        onclick="deleteAdjustment('{{ $pre->id }}')">Rollback</button>
                                </td>

                                <td>
                                    <a href="javascript:void(0);" class="challan-ajustment-link btn-sm btn-primary"
                                        data-challan_id="{{ $pre->challan_id }}" data-adjustment_id="{{ $pre->id }}">
                                        Challan No: {{ $pre->challan->challanNo }}
                                    </a>
                                </td>
                                <td>{{ date('M-Y', strtotime($pre->challan->fee_month)) }}</td>
                                <td>{{ date('d-M-Y', strtotime($pre->date)) }}</td>
                                <td>
                                    {{ $pre->amount }}
                                </td>


                                {{-- <td id="pay_{{ $pre->challan_id }}"> {{ $pre }}</td> --}}
                                @php
                                    $rel += $pre->amount;
                                @endphp
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="5" style="text-align: right;"><strong> Total Adjusted Amount </strong></td>
                            <td><strong>{{ $rel }} </strong></td>
                        </tr>
                    </tbody>
                </table>
            @else
            @endif
        </div>
        <hr>
        <input type="hidden" name="py" id="pya" value="{{ $payable }}">
        <div class="row d-flex justify-content-end mt-1 ">
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('actual_fee', __('Outstanding Dues'), ['class' => 'form-label']) }}
                    {{ Form::text('actual_fee', '', ['id' => 'actual_fee', 'class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('security_deposit', __('Security Deposit'), ['class' => 'form-label']) }} <span
                        id="error_ch" style="font-size:12px;"></span>
                    {{ Form::text('security_deposit', '', ['id' => 'security_deposit', 'class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('security_payable', __('Security Payable'), ['class' => 'form-label']) }}
                    {{ Form::text('security_payable', '', ['id' => 'security_payable', 'class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('other_fee', __('Other Fee'), ['class' => 'form-label']) }}
                    {{ Form::text('other_fee', '0', array_merge(['id' => 'other_fee', 'class' => 'form-control'], !$isHO ? ['readonly' => 'readonly'] : [])) }}
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('other_account', __('Other Account'), ['class' => 'form-label']) }}
                    <select name="other_account" class="form-control selectbox" required="required"
                        {{ !$isHO ? 'disabled' : '' }}>
                        @foreach ($all_accounts as $chartAccount)
                            <option value="{{ $chartAccount['id'] }}" class="subAccount">
                                {{ $chartAccount['code'] . ' - ' . $chartAccount['name'] }}
                            </option>
                        @endforeach
                    </select>

                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('refund', __('Refund'), ['class' => 'form-label']) }}
                    {{ Form::text('refund', '0', ['id' => 'refund', 'class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>

        </div>
        <div class="row d-flex justify-content-end mt-1 ">
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('notice_fee', __('Notice Fee'), ['class' => 'form-label']) }}
                    {{ Form::text('notice_fee', '0', ['id' => 'notice_fee', 'class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('other_deduction', __('Other Deduction'), ['class' => 'form-label']) }}
                    {{ Form::text('other_deduction', '0', ['id' => 'other_deduction', 'class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('total_payables', __('Total Payable'), ['class' => 'form-label']) }}
                    {{ Form::text('total_payables', '0', ['id' => 'total_payables', 'class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('total_receivables', __('Total Receivable'), ['class' => 'form-label']) }}
                    {{ Form::text('total_receivables', '0', ['id' => 'total_receivables', 'class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('net_balance', __('Net Balance'), ['class' => 'form-label']) }}
                    {{ Form::text('net_balance', '0', ['id' => 'net_balance', 'class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 d-flex justify-content-end mt-4">
                <div class="btn-box">
                    <button id="calculateBalance" class="btn mx-1 btn-sm btn-outline-success">Calculate Balance</button>
                </div>
            </div>
        </div>
        <div class="modal fade" id="challanDetailModal" tabindex="-1" aria-labelledby="challanDetailModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="challanDetailModalLabel">Challan Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="challan-detail-body">

                    </div>

                </div>
            </div>
        </div>
        <hr>
        <div class="row d-flex justify-content-end ">
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('challan_date', __('Challan Date'), ['class' => 'form-label']) }}
                    {{ Form::date('challan_date', '', array_merge(['class' => 'form-control', 'readonly' => 'readonly'], !$isHO ? ['readonly' => 'readonly'] : [])) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}
                    {{ Form::date('due_date', '', array_merge(['class' => 'form-control', 'readonly' => 'readonly'], !$isHO ? ['readonly' => 'readonly'] : [])) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('invoice_no', __('Invoice No'), ['class' => 'form-label']) }}
                    {{ Form::text('invoice_no', '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('invoice_date', __('Invoice Date'), ['class' => 'form-label']) }}
                    {{ Form::date('invoice_date', '', array_merge(['class' => 'form-control', 'readonly' => 'readonly'], !$isHO ? ['readonly' => 'readonly'] : [])) }}
                </div>
            </div>
        </div>
        <div class="row d-flex justify-content-end mt-1 ">
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('beneficiary_name', __('Beneficiary Name'), ['class' => 'form-label']) }}
                    {{ Form::text('beneficiary_name', '', array_merge(['class' => 'form-control'], !$isHO ? ['readonly' => 'readonly'] : [])) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('bank_name', __('Bank Name'), ['class' => 'form-label']) }}
                    {{ Form::text('bank_name', '', array_merge(['class' => 'form-control'], !$isHO ? ['readonly' => 'readonly'] : [])) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('cheque_no', __('Cheque No'), ['class' => 'form-label']) }}
                    {{ Form::text('cheque_no', '', array_merge(['class' => 'form-control'], !$isHO ? ['readonly' => 'readonly'] : [])) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('cheque_date', __('Cheque Date'), ['class' => 'form-label']) }}
                    {{ Form::date('cheque_date', '', array_merge(['class' => 'form-control'], !$isHO ? ['readonly' => 'readonly'] : [])) }}
                </div>
            </div>
            @if ($isHO)
                <div class="row mt-4">
                    <div class="col text-end">

                        {{ Form::submit(__('Submit Withdrawal'), ['class' => 'btn btn-primary']) }}
                    </div>
                </div>
            @endif
            {!! Form::close() !!}
        </div>
    @endsection
