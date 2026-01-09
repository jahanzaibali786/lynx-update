@extends('layouts.admin')
@section('page-title')
    {{ __('Withdrawl Application') }}
@endsection
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
                        if (response.error) {
                            alert(response.error);
                            return;
                        }
                        console.log(response);

                        $('#actual_fee').val(response.actual_fee.toFixed(2));
                        $('#security_deposit').val(Number(response.security_deposit).toFixed(
                        2));
                        $('#security_payable').val(response.security_payable.toFixed(2));
                        $('#other_fee').val(response.other_fee.toFixed(2));
                        $('#refund').val(response.refund.toFixed(2));
                        $('#notice_fee').val(response.notice_fee.toFixed(2));
                        $('#other_deduction').val(response.other_deduction.toFixed(2));
                        $('#total_payables').val(response.total_payables.toFixed(2));
                        $('#total_receivables').val(response.total_receivables.toFixed(2));
                        $('#net_balance').val(response.net_balance.toFixed(2));
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

        function submitAdjustment(challanNo) {
            const adjAmount = document.getElementById(challanNo).value;

            // Ensure the value is valid before proceeding
            if (adjAmount === "" || adjAmount < 0) {
                show_toastr('error', 'Please enter a valid adjustment amount.', 'error');
                return;
            }

            // AJAX request to submit the data
            $.ajax({
                url: '{{ route('submit_adjustment') }}',
                type: 'POST',
                data: {
                    challanNo: challanNo,
                    adjAmount: adjAmount,
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

        function deleteAdjustment(challanNo) {
            const adjAmount = document.getElementById(challanNo).value;

            // Ensure the value is valid before proceeding
            // if (adjAmount === "" || adjAmount < 0) {
            //     alert("Please enter a valid adjustment amount.");
            //     return;
            // }

            // AJAX request to submit the data
            $.ajax({
                url: '{{ route('delete_adjustment') }}',
                type: 'POST',
                data: {
                    challanNo: challanNo,
                    // adjAmount: adjAmount,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        show_toastr('success', 'Adjustment delete successfully!', 'success');
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
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Withdrawl Application') }}</li>
@endsection
@section('action-btn')
    <div class="float-end"></div>
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
                    {{ Form::label('document_date', __('Document Date'), ['class' => 'form-label']) }}
                    {{ Form::date('document_date', @$studentwithdrawal->document_date, ['class' => 'form-control', 'readonly' => 'readonly']) }}
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
        </div>
            <div class="row d-flex justify-content-end mt-1 ">
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('student_name', __('Student Name'), ['class' => 'form-label']) }}
                        {{ Form::text('student_name', @$studentwithdrawal->student->stdname, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('father_name', __('Fahter Name'), ['class' => 'form-label']) }}
                        {{ Form::text('father_name', @$studentwithdrawal->student->father_name, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                        {{ Form::text('class', @$studentwithdrawal->student->class->name, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                        {{ Form::text('branch', @$studentwithdrawal->branch->name, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                    </div>
                </div>
            </div>
        <div class="row d-flex justify-content-start mt-1 ">
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('remarks', __('Remarks'), ['class' => 'form-label']) }}
                    {{ Form::text('remarks', @$studentwithdrawal->remark, ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('withdraw_reason', __('Withdraw Reason'), ['class' => 'form-label']) }}
                    {{ Form::text('withdraw_reason', @$studentwithdrawal->reason, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('expected_readmission_date', __('Expected Re-admission Date'), ['class' => 'form-label']) }}
                    {{ Form::date('expected_readmission_date', date('Y-m-d', strtotime('+3 months')), ['class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
        </div>
        <hr>
        <div class="row d-flex justify-content-start mt-1 ">
            @if ($PrevChallan->isNotEmpty())
                <table class="datatable">
                    <tr>
                        <th>Adjustment</th>
                        <th>Adjust Amount</th>
                        <th>Challan Details</th>
                        <th>Billing Month</th>
                        <th>Payable Fee</th>
                        <th>Total Fee</th>
                    </tr>
                    @foreach ($PrevChallan as $prev)
                        <tr>
                            <td><button id="submit_{{ $prev->challanNo }}" class="btn btn-sm btn-primary"
                                    onclick="submitAdjustment('{{ $prev->challanNo }}')">Submit</button>
                                <button id="submit_{{ $prev->challanNo }}" class="btn btn-sm btn-danger"
                                    onclick="deleteAdjustment('{{ $prev->challanNo }}')">Rollback</button>
                            </td>
                            <td>
                                <input type="number" name="form-control adj_amount{{ $prev->challanNo }}"
                                    id="{{ $prev->challanNo }}" data-ids="{{ $prev->challanNo }}"
                                    data-max="{{ $payable }}"
                                    class="adj_put" min="0"
                                    max='{{ $payable }}' />
                            </td>
                            <td>Challan Date: {{ $prev->due_date }}</td>
                            <td>{{ $prev->fee_month }}</td>
                            <td id="pay_{{ $prev->challanNo }}">
                                {{ $prev->total_amount - ($prev->paid_amount + $prev->concession_amount) }}</td>
                            <td id="tot_{{ $prev->challanNo }}">
                                {{ $prev->total_amount - ($prev->paid_amount + $prev->concession_amount) }}</td>
                        </tr>
                    @endforeach
                </table>
            @else
                <p>No previous challans available.</p>
            @endif
        </div>
        <hr>
        <div class="row d-flex justify-content-end mt-1 ">
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('actual_fee', __('Actual Fee'), ['class' => 'form-label']) }}
                    {{ Form::text('actual_fee', '', ['id' => 'actual_fee', 'class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('security_deposit', __('Security Deposit'), ['class' => 'form-label']) }}
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
                    {{ Form::text('other_fee', '0', ['id' => 'other_fee', 'class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('refund', __('Refund'), ['class' => 'form-label']) }}
                    {{ Form::text('refund', '0', ['id' => 'refund', 'class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('notice_fee', __('Notice Fee'), ['class' => 'form-label']) }}
                    {{ Form::text('notice_fee', '0', ['id' => 'notice_fee', 'class' => 'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>
        </div>
        <div class="row d-flex justify-content-end mt-1 ">
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
            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 d-flex justify-content-end mt-4">
                <div class="btn-box">
                    <button id="calculateBalance" class="btn mx-1 btn-sm btn-outline-success">Calculate Balance</button>
                </div>
            </div>
        </div>

        <hr>
        <div class="row d-flex justify-content-end ">
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('challan_date', __('Challan Date'), ['class' => 'form-label']) }}
                    {{ Form::date('challan_date', '', ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}
                    {{ Form::date('due_date', '', ['class' => 'form-control']) }}
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
                    {{ Form::date('invoice_date', '', ['class' => 'form-control']) }}
                </div>
            </div>
        </div>
        <div class="row d-flex justify-content-end mt-1 ">
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('beneficiary_name', __('Beneficiary Name'), ['class' => 'form-label']) }}
                    {{ Form::text('beneficiary_name', '', ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('bank_name', __('Bank Name'), ['class' => 'form-label']) }}
                    {{ Form::text('bank_name', '', ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('cheque_no', __('Cheque No'), ['class' => 'form-label']) }}
                    {{ Form::text('cheque_no', '', ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('cheque_date', __('Cheque Date'), ['class' => 'form-label']) }}
                    {{ Form::date('cheque_date', '', ['class' => 'form-control']) }}
                </div>
            </div>
            <div class="row mt-4">
            <div class="col text-end">
                {{ Form::submit(__('Submit Withdrawal'), ['class' => 'btn btn-primary']) }}
            </div>
        </div>
        {!! Form::close() !!}
    </div>
@endsection
