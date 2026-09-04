@extends('layouts.admin')
@section('page-title')
    {{ __('Bulk Generate Advance Salary') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employee-advance.index') }}">{{ __('Employee Advance') }}</a></li>
    <li class="breadcrumb-item">{{ __('Bulk Generate') }}</li>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            function numberValue(value) {
                var parsed = parseFloat(value);
                return isNaN(parsed) ? 0 : parsed;
            }

            function formatMoney(value) {
                return Math.round(value).toLocaleString();
            }

            function recalculateRow($row) {
                var checked = $row.find('.advance-check').is(':checked');
                var net = numberValue($row.find('.advance-percent').data('net'));
                var percent = numberValue($row.find('.advance-percent').val());
                var amount = checked ? Math.round((net * percent) / 100) : 0;

                $row.find('.advance-amount-label').text(formatMoney(amount));
                $row.find('.advance-amount-input').val(amount);

                return amount;
            }

            function recalculateTotal() {
                var total = 0;
                $('.advance-row').each(function() {
                    total += recalculateRow($(this));
                });

                $('#bulk_total_advance').text(formatMoney(total));
            }

            $('#global_percent').on('input', function() {
                var percent = $(this).val();
                $('.advance-percent:not(:disabled)').val(percent);
                recalculateTotal();
            });

            $('#global_reason').on('input', function() {
                $('#bulk_global_reason_hidden').val($(this).val());
                $('.advance-reason:not(:disabled)').each(function() {
                    if (!$(this).data('edited')) {
                        $(this).val($('#global_reason').val());
                    }
                });
            });

            $(document).on('input', '.advance-reason', function() {
                $(this).data('edited', true);
            });

            $(document).on('input change', '.advance-percent, .advance-check', function() {
                recalculateTotal();
            });

            $('#select_all_advances').on('change', function() {
                $('.advance-check:not(:disabled)').prop('checked', $(this).is(':checked'));
                recalculateTotal();
            });

            recalculateTotal();
        });
    </script>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['employee-advance.bulk-create'], 'method' => 'GET', 'id' => 'bulk_advance_filter']) }}
                    <div class="row align-items-end">
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                            {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
                            {{ Form::select('branches', $branches, $selectedBranch, ['class' => 'form-control select custom-select', 'required' => 'required']) }}
                        </div>
                        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-12">
                            {{ Form::label('advance_month', __('Advance Month'), ['class' => 'form-label']) }}
                            {{ Form::month('advance_month', $monthValue, ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        <div class="col-xl-2 col-lg-4 col-md-6 col-sm-12">
                            {{ Form::label('global_percent', __('Global Advance Salary %'), ['class' => 'form-label']) }}
                            {{ Form::number('global_percent', $globalPercent, ['class' => 'form-control', 'id' => 'global_percent', 'step' => '0.01', 'min' => 0, 'max' => 100]) }}
                        </div>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                            {{ Form::label('global_reason', __('Global Reason'), ['class' => 'form-label']) }}
                            {{ Form::text('global_reason', $globalReason, ['class' => 'form-control', 'id' => 'global_reason', 'maxlength' => 191]) }}
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('Search') }}</button>
                            <a href="{{ route('employee-advance.bulk-create') }}" class="btn btn-sm btn-outline-danger">{{ __('Clear') }}</a>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    @if(!empty($selectedBranch))
        {{ Form::open(['route' => 'employee-advance.bulk-store', 'method' => 'POST']) }}
        {{ Form::hidden('branches', $selectedBranch) }}
        {{ Form::hidden('advance_month', $monthValue) }}
        {{ Form::hidden('global_percent', $globalPercent) }}
        {{ Form::hidden('global_reason', $globalReason, ['id' => 'bulk_global_reason_hidden']) }}
        <div class="card-body full-card">
            <div class="table-responsive">
                @if($rows->isNotEmpty())
                    <table class="">
                        <thead>
                            <tr class="table_heads">
                                <th style="width: 45px;">
                                    <input type="checkbox" id="select_all_advances">
                                </th>
                                <th>{{ __('Employee') }}</th>
                                <th>{{ __('Gross Pay') }}</th>
                                <th>{{ __('Loan') }}</th>
                                <th>{{ __('Net') }}</th>
                                <th>{{ __('Advance Salary %') }}</th>
                                <th>{{ __('Advance Salary') }}</th>
                                <th>{{ __('Reason') }}</th>
                                <th>{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $row)
                                @php
                                    $percentAttributes = [
                                        'class' => 'form-control advance-percent',
                                        'step' => '0.01',
                                        'min' => 0,
                                        'max' => 100,
                                        'data-net' => $row['net'],
                                    ];
                                    if ($row['disabled']) {
                                        $percentAttributes['disabled'] = 'disabled';
                                    }
                                    $reasonAttributes = [
                                        'class' => 'form-control advance-reason',
                                        'maxlength' => 191,
                                        'placeholder' => __('Reason'),
                                    ];
                                    if ($row['disabled']) {
                                        $reasonAttributes['disabled'] = 'disabled';
                                    }
                                @endphp
                                <tr class="advance-row {{ $row['disabled'] ? 'opacity-50' : '' }}">
                                    <td>
                                        <input type="checkbox"
                                            class="advance-check"
                                            name="employee_ids[]"
                                            value="{{ $row['employee']->id }}"
                                            {{ $row['disabled'] ? 'disabled' : 'checked' }}>
                                    </td>
                                    <td>
                                        <strong>{{ $row['employee']->name }}</strong><br>
                                        <small>{{ \Auth::user()->employeeIdFormat($row['employee']->employee_id) }}</small>
                                    </td>
                                    <td>{{ number_format($row['gross']) }}</td>
                                    <td>{{ number_format($row['loan']) }}</td>
                                    <td>{{ number_format($row['net']) }}</td>
                                    <td style="min-width: 120px;">
                                        {{ Form::number('percentages[' . $row['employee']->id . ']', $row['percent'], $percentAttributes) }}
                                    </td>
                                    <td>
                                        <span class="advance-amount-label">{{ number_format($row['advance_amount']) }}</span>
                                        {{ Form::hidden('advance_amounts[' . $row['employee']->id . ']', $row['advance_amount'], ['class' => 'advance-amount-input']) }}
                                    </td>
                                    <td style="min-width: 220px;">
                                        {{ Form::text('reasons[' . $row['employee']->id . ']', $globalReason, $reasonAttributes) }}
                                    </td>
                                    <td>
                                        @if($row['disabled'])
                                            <span class="badge bg-danger">{{ $row['disabled_reason'] }}</span>
                                        @else
                                            <span class="badge bg-success">{{ __('Ready') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end"><strong>{{ __('Total Advance Salary') }}</strong></th>
                                <th id="bulk_total_advance">0</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                @else
                    <div class="text-center">{{ __('No employees found for selected branch.') }}</div>
                @endif
            </div>
            @if($rows->where('disabled', false)->isNotEmpty())
                <div class="text-end mt-3">
                    <a href="{{ route('employee-advance.index') }}" class="btn btn-outline-light">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-outline-primary">{{ __('Generate Advance') }}</button>
                </div>
            @endif
        </div>
        {{ Form::close() }}
    @endif
@endsection
