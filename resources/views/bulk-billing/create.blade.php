@extends('layouts.admin')
@section('page-title')
    {{ __('Bulk Billing') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Bulk Billing') }}</li>
@endsection
@section('content')
    <div class="card mt-6 p-4">
        <div class="card-body">
            {!! Form::open(['route' => 'bulk-billing.store', 'method' => 'POST', 'id' => 'bulkBillingForm', 'novalidate' => 'novalidate']) !!}
            @csrf
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                        {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select custom-select', 'id' => 'branch_id', 'required' => 'required', 'placeholder' => __('Select Branch')]) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {{ Form::label('session_id', __('Session'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                        {{ Form::select('session_id', $sessions, 2, ['class' => 'form-control select custom-select', 'id' => 'session_id', 'required' => 'required', 'placeholder' => __('Select Session')]) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {{ Form::label('student_id', __('Student'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                        {{ Form::select('student_id', [], null, ['class' => 'form-control select custom-select', 'id' => 'student_id', 'required' => 'required', 'placeholder' => __('Select Student')]) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {{ Form::label('billing_month', __('Billing Month'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                        {{ Form::month('billing_month', date('Y-m'), ['class' => 'form-control', 'id' => 'billing_month', 'required' => 'required']) }}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {{ Form::label('challan_date', __('Challan Date'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                        {{ Form::date('challan_date', date('Y-m-d'), ['class' => 'form-control', 'id' => 'challan_date', 'required' => 'required']) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                        {{ Form::date('issue_date', date('Y-m-d'), ['class' => 'form-control', 'id' => 'issue_date', 'required' => 'required']) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                        {{ Form::date('due_date', date('Y-m-d'), ['class' => 'form-control', 'id' => 'due_date', 'required' => 'required']) }}
                    </div>
                </div>
            </div>

            <hr>
            <h5>{{ __('Fee Heads') }}</h5>
            <br>
            <div class="table-responsive">
                <table class="table table-bordered" id="feeHeadsTable">
                    <thead class="table_heads">
                        <tr>
                            <th style="width:40px;">{{ __('Select') }}</th>
                            <th>{{ __('Head Name') }}</th>
                            <th>{{ __('Base Amount') }}</th>
                            <th>{{ __('Concession') }}</th>
                            <th>{{ __('Payable') }}</th>
                        </tr>
                    </thead>
                    <tbody id="feeHeadsBody">
                        <tr>
                            <td colspan="5" class="text-center">{{ __('Select a student to load fee heads') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <hr>
            <h5>{{ __('Payment Details') }}</h5>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        {{ Form::label('payment_date', __('Payment Date'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                        {{ Form::date('payment_date', date('Y-m-d'), ['class' => 'form-control', 'id' => 'payment_date', 'required' => 'required']) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {{ Form::label('bank_id', __('Bank'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                        {{ Form::select('bank_id', $accounts, null, ['class' => 'form-control select custom-select', 'id' => 'bank_id', 'required' => 'required', 'placeholder' => __('Select Bank')]) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {{ Form::label('payment_type', __('Payment Type'), ['class' => 'form-label']) }}<span style="color:red"> *</span>
                        {{ Form::select('payment_type', ['DD' => 'Demand Draft', 'OL' => 'Online', 'CHQ' => 'Cheque', 'CD' => 'Cash Deposit'], null, ['class' => 'form-control custom-select', 'id' => 'payment_type', 'required' => 'required']) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {{ Form::label('reference', __('Reference'), ['class' => 'form-label']) }}
                        {{ Form::text('reference', null, ['class' => 'form-control', 'id' => 'reference', 'placeholder' => __('Cheque/Transaction No')]) }}
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-12 text-right">
                    <button type="submit" id="submitBtn" class="btn btn-primary">{{ __('Create Challan & Receipt') }}</button>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
@push('script-page')
<script>
    var accountsData = {!! json_encode($accountsData) !!};
    var bulkBillingStudentRequest = null;
    var bulkBillingStudentRequestToken = 0;

    function rebuildBulkBillingCustomSelect($select) {
        if (!$select.length) {
            return;
        }

        var select = $select[0];
        $select.addClass('custom-select');

        if (select.customSelectInstance) {
            var $activeWrapper = select.customSelectInstance.wrapper ? $(select.customSelectInstance.wrapper) : $();
            $select.siblings('.custom-select-wrapper').not($activeWrapper).remove();

            if (typeof select.customSelectInstance.updateOptions === 'function') {
                select.customSelectInstance.updateOptions();
            }

            if (!select.value && select.customSelectInstance.displayText) {
                select.customSelectInstance.selectedValue = '';
                select.customSelectInstance.selectedText = '';
                select.customSelectInstance.displayText.textContent = select.getAttribute('placeholder') || '{{ __("Select Student") }}';
            }
            return;
        }

        $select.siblings('.custom-select-wrapper').remove();
        $select.show();

        if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
            window.CustomSelect.create(select);
        }
    }

    $(document).ready(function() {
        $('#branch_id').off('change.bulkBillingStudents').on('change.bulkBillingStudents', function() {
            var branchId = $(this).val();
            var $studentSelect = $('#student_id');

            $studentSelect.val('').empty().append('<option value="">{{ __("Select Student") }}</option>');
            rebuildBulkBillingCustomSelect($studentSelect);
            $('#feeHeadsBody').html('<tr><td colspan="5" class="text-center">{{ __("Select a student to load fee heads") }}</td></tr>');

            if (bulkBillingStudentRequest && bulkBillingStudentRequest.readyState !== 4) {
                bulkBillingStudentRequest.abort();
            }

            if (branchId) {
                var requestToken = ++bulkBillingStudentRequestToken;
                bulkBillingStudentRequest = $.ajax({
                    url: '{{ route("bulk-billing.students", ":branchId") }}'.replace(':branchId', branchId),
                    type: 'GET',
                    success: function(data) {
                        if (requestToken !== bulkBillingStudentRequestToken) {
                            return;
                        }

                        var addedStudentIds = {};
                        $studentSelect.val('').empty().append('<option value="">{{ __("Select Student") }}</option>');
                        $.each(data, function(i, s) {
                            if (!s.id || addedStudentIds[s.id]) {
                                return;
                            }
                            addedStudentIds[s.id] = true;
                            $studentSelect.append('<option value="' + s.id + '">' + s.text + '</option>');
                        });
                        rebuildBulkBillingCustomSelect($studentSelect);
                    },
                    error: function(xhr, status) {
                        if (status !== 'abort') {
                            rebuildBulkBillingCustomSelect($studentSelect);
                        }
                    }
                });
            } else {
                bulkBillingStudentRequestToken++;
            }
        });

        $('#billing_month').off('change.bulkBillingDates').on('change.bulkBillingDates', function() {
            var month = $(this).val();
            if (month) {
                $('#challan_date').val(month + '-01');
                $('#issue_date').val(month + '-01');
                var due = new Date(month + '-01');
                due.setDate(due.getDate() + 14);
                $('#due_date').val(due.toISOString().split('T')[0]);
            }
        });

        $('#student_id, #billing_month').off('change.bulkBillingHeads').on('change.bulkBillingHeads', function() {
            var studentId = $('#student_id').val();
            var month = $('#billing_month').val();
            if (studentId && month) {
                $.ajax({
                    url: '{{ route("bulk-billing.fee-structure", [":studentId", ":month"]) }}'
                        .replace(':studentId', studentId)
                        .replace(':month', month),
                    type: 'GET',
                    success: function(data) {
                        var html = '';
                        $.each(data, function(i, h) {
                            var checked = h.checked ? 'checked' : '';
                            html += '<tr>';
                            html += '<td class="text-center"><input type="checkbox" name="heads[' + i + '][checked]" value="1" class="head-checkbox" ' + checked + '>';
                            html += '<input type="hidden" name="heads[' + i + '][head_id]" value="' + h.head_id + '"></td>';
                            html += '<td>' + h.head_name + '</td>';
                            html += '<td><input type="number" name="heads[' + i + '][base_amount]" value="' + h.base_amount + '" class="form-control base-amount" step="0.01" min="0"></td>';
                            html += '<td><input type="number" name="heads[' + i + '][concession_amount]" value="' + h.concession_amount + '" class="form-control concession-amount" step="0.01" min="0"></td>';
                            html += '<td><input type="number" name="heads[' + i + '][payable_amount]" value="' + h.payable_amount + '" class="form-control payable-amount" step="0.01" min="0" readonly></td>';
                            html += '</tr>';
                        });
                        $('#feeHeadsBody').html(html || '<tr><td colspan="5" class="text-center">{{ __("No fee heads found") }}</td></tr>');
                    },
                    error: function(xhr) {
                        var msg = '{{ __("Failed to load fee heads") }}';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            msg = xhr.responseJSON.error;
                        }
                        show_toastr('error', msg, 'error');
                        $('#feeHeadsBody').html('<tr><td colspan="5" class="text-center text-danger">' + msg + '</td></tr>');
                    }
                });
            }
        });

        $(document).on('input', '.base-amount, .concession-amount', function() {
            var row = $(this).closest('tr');
            var base = parseFloat(row.find('.base-amount').val()) || 0;
            var concession = parseFloat(row.find('.concession-amount').val()) || 0;
            var payable = Math.max(0, base - concession);
            row.find('.payable-amount').val(payable.toFixed(2));
            row.find('.head-checkbox').prop('checked', true);
        });

        $('#bank_id').off('change.bulkBillingBank').on('change.bulkBillingBank', function() {
            var bankId = $(this).val();
            var d = accountsData[bankId] || {};
            var chartAccount = d.chart_account || '';
            var $type = $('#payment_type');
            if (chartAccount.toUpperCase().includes('CSH') || chartAccount.toUpperCase().includes('CASH')) {
                $type.html('<option value="CD">CD</option>');
            } else {
                $type.html('<option value="DD">DD</option><option value="OL">OL</option><option value="CHQ">CHQ</option>');
            }
            rebuildBulkBillingCustomSelect($type);
        });

        $('#bulkBillingForm').on('submit', function(e) {
            var valid = true;
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).css('border-color', 'red');
                    valid = false;
                } else {
                    $(this).css('border-color', '');
                }
            });
            var checked = $('#feeHeadsBody .head-checkbox:checked').length;
            if (checked === 0) {
                show_toastr('error', '{{ __("Select at least one fee head") }}', 'error');
                valid = false;
            }
            if (!valid) {
                e.preventDefault();
                show_toastr('error', '{{ __("Please fill all required fields") }}', 'error');
            }
        });
    });
</script>
@endpush
