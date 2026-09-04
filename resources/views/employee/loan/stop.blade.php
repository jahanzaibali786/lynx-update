<script>
    $(document).ready(function() {
        var latestGeneratedSalaryMonth = '{{ $latestGeneratedSalaryMonth }}';
        var latestGeneratedSalaryText = '{{ $latestGeneratedSalaryText }}';
        var loanStartMonth = '{{ \Carbon\Carbon::parse($loan->from_pay_month)->format('Y-m') }}';
        var loanEndMonth = '{{ \Carbon\Carbon::parse($loan->loan_ended)->format('Y-m') }}';

        function monthValueToDate(value) {
            return value ? new Date(value + '-01T00:00:00') : null;
        }

        function formatMonthValue(date) {
            var month = String(date.getMonth() + 1).padStart(2, '0');
            return date.getFullYear() + '-' + month;
        }

        function updateStopToMonth() {
            var stopFrom = monthValueToDate($('#stop_from_month').val());
            var months = parseInt($('#stop_months').val());

            $('#stop_to_month').val('');

            if (!stopFrom || isNaN(stopFrom.getTime()) || isNaN(months) || months <= 0) {
                return;
            }

            stopFrom.setMonth(stopFrom.getMonth() + (months - 1));
            $('#stop_to_month').val(formatMonthValue(stopFrom));
        }

        function getRemainingDueMonths() {
            var stopFrom = monthValueToDate($('#stop_from_month').val());
            var loanStart = monthValueToDate(loanStartMonth);
            var loanEnd = monthValueToDate(loanEndMonth);

            if (!stopFrom || !loanStart || !loanEnd || isNaN(stopFrom.getTime()) || isNaN(loanStart.getTime()) || isNaN(loanEnd.getTime()) || stopFrom < loanStart || stopFrom > loanEnd) {
                return 0;
            }

            return ((loanEnd.getFullYear() - stopFrom.getFullYear()) * 12) + (loanEnd.getMonth() - stopFrom.getMonth()) + 1;
        }

        function validateStopMonth(showMessage) {
            var stopFrom = $('#stop_from_month').val();
            var months = parseInt($('#stop_months').val());
            var remainingDueMonths = getRemainingDueMonths();
            var message = 'From paid month salary already generated. Please select next month.';

            if (latestGeneratedSalaryMonth && stopFrom && stopFrom <= latestGeneratedSalaryMonth) {
                $('#stop_month_error').text(latestGeneratedSalaryText ? message + ' Last salary: ' + latestGeneratedSalaryText + '.' : message);
                if (showMessage) {
                    show_toastr('error', message, 'error');
                }
                return false;
            }

            if (stopFrom && (stopFrom < loanStartMonth || stopFrom > loanEndMonth || remainingDueMonths <= 0)) {
                $('#stop_month_error').text('Stop month must be within the loan period.');
                if (showMessage) {
                    show_toastr('error', 'Stop month must be within the loan period.', 'error');
                }
                return false;
            }

            $('#stop_month_error').text('');
            if (remainingDueMonths > 0) {
                $('#remaining_due_months').val(remainingDueMonths);
            } else {
                $('#remaining_due_months').val('');
            }
            return true;
        }

        $('#stop_from_month, #stop_months').on('input change keyup', function() {
            updateStopToMonth();
            validateStopMonth(false);
        });

        $('#loan_stop_form').on('submit', function(event) {
            if (!validateStopMonth(true)) {
                event.preventDefault();
            }
        });

        updateStopToMonth();
    });
</script>
{{ Form::open(['route' => ['loan.stop.store', $loan->id], 'method' => 'post', 'id' => 'loan_stop_form']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-4">
            {{ Form::label('employee_name', __('Employee'), ['class' => 'form-label']) }}
            {{ Form::text('employee_name', !empty($loan->employee->name) ? $loan->employee->name : '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('from_pay_month', __('Current Paid From'), ['class' => 'form-label']) }}
            {{ Form::month('from_pay_month', \Carbon\Carbon::parse($loan->from_pay_month)->format('Y-m'), ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('loan_ended', __('Current To Month'), ['class' => 'form-label']) }}
            {{ Form::month('loan_ended', \Carbon\Carbon::parse($loan->loan_ended)->format('Y-m'), ['class' => 'form-control', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('stop_from_month', __('Stop From Month'), ['class' => 'form-label']) }}<span class="text-danger pl-1"> *</span>
            @php
                $loanStartMonth = \Carbon\Carbon::parse($loan->from_pay_month)->startOfMonth();
                $minimumStopMonth = $latestGeneratedSalaryMonth ? \Carbon\Carbon::createFromFormat('Y-m', $latestGeneratedSalaryMonth)->addMonth()->startOfMonth() : $loanStartMonth;
                if ($minimumStopMonth->lt($loanStartMonth)) {
                    $minimumStopMonth = $loanStartMonth;
                }
            @endphp
            {{ Form::month('stop_from_month', null, ['class' => 'form-control', 'id' => 'stop_from_month', 'required' => 'required', 'min' => $minimumStopMonth->format('Y-m'), 'max' => \Carbon\Carbon::parse($loan->loan_ended)->format('Y-m')]) }}
            <span class="text-danger" id="stop_month_error"></span>
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('months', __('Stop Months'), ['class' => 'form-label']) }}<span class="text-danger pl-1"> *</span>
            {{ Form::number('months', 1, ['class' => 'form-control', 'id' => 'stop_months', 'required' => 'required', 'min' => '1']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('stop_to_month', __('Stop To Month'), ['class' => 'form-label']) }}
            {{ Form::month('stop_to_month', null, ['class' => 'form-control', 'id' => 'stop_to_month', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('remaining_due_months', __('Remaining Due Months'), ['class' => 'form-label']) }}
            {{ Form::number('remaining_due_months', null, ['class' => 'form-control', 'id' => 'remaining_due_months', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-8">
            {{ Form::label('reason', __('Reason'), ['class' => 'form-label']) }}
            {{ Form::textarea('reason', null, ['class' => 'form-control', 'rows' => 3]) }}
        </div>
    </div>

    <div class="mt-4">
        <h6 class="mb-3">{{ __('Stop History') }}</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>{{ __('From') }}</th>
                        <th>{{ __('To') }}</th>
                        <th>{{ __('Months') }}</th>
                        <th>{{ __('Reason') }}</th>
                        @if(\Auth::user()->type == 'company')
                            <th class="text-center">{{ __('Action') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($loan->stopHistories as $history)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($history->stop_from_month)->format('M Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($history->stop_to_month)->format('M Y') }}</td>
                            <td>{{ $history->months }}</td>
                            <td>{{ !empty($history->reason) ? $history->reason : '-' }}</td>
                            @if(\Auth::user()->type == 'company')
                                <td class="text-center">
                                    <button type="submit"
                                        form="loan-stop-delete-form-{{ $history->id }}"
                                        class="mx-1 btn btn-sm btn-outline-danger"
                                        data-bs-toggle="tooltip"
                                        data-bs-title="{{ __('Delete') }}"
                                        onclick="return confirm('{{ __('Are You Sure?') }}')">
                                        <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ \Auth::user()->type == 'company' ? 5 : 4 }}" class="text-center">{{ __('No stop history found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Save') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}
@if(\Auth::user()->type == 'company')
    @foreach($loan->stopHistories as $history)
        {!! Form::open([
            'method' => 'DELETE',
            'route' => ['loan.stop.destroy', $history->id],
            'id' => 'loan-stop-delete-form-' . $history->id,
        ]) !!}
        {!! Form::close() !!}
    @endforeach
@endif
