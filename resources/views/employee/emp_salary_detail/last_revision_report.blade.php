@extends('layouts.admin')
@section('page-title')
    {{ __('Last Salary Revision Report') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Last Salary Revision Report') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body" style="padding: 12px;">
                    {{ Form::open(['route' => ['last_salary_revision_report'], 'method' => 'GET', 'id' => 'last_revision_form']) }}
                    <div class="row d-flex justify-content-start align-items-end">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                {{ Form::select('department_id', $departments, request()->get('department_id'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                {{ Form::select('designation_id', $designations, request()->get('designation_id'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('employee_id', __('Employees'), ['class' => 'form-label']) }}
                                {{ Form::select('employee_id', $employees, request()->get('employee_id'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <button type="submit" class="btn btn-sm btn-primary me-2">
                                <i class="ti ti-search me-1"></i>{{ __('Search') }}
                            </button>
                            <a href="{{ route('last_salary_revision_report') }}" class="btn btn-sm btn-outline-danger me-2">
                                {{ __('Reset') }}
                            </a>
                            @if(count($revisedData) > 0)
                                <a href="{{ route('last_salary_revision_report.export', request()->all()) }}" class="btn btn-sm btn-outline-success">
                                    <i class="ti ti-file me-1"></i>{{ __('Export Excel') }}
                                </a>
                            @endif
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

        <div class="col-xl-12 mt-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>{{ __('Last Salary Revision Details') }}</h5>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-items-center mb-0" style="white-space: nowrap;">
                            <thead class="table_heads">
                                <tr style="background-color: #1f385c; color: #ffffff;">
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">#</th>
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ __('Branch Name') }}</th>
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ __('Revision Date') }}</th>
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ __('Emp No') }}</th>
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ __('Emp Name') }}</th>
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ __('Department') }}</th>
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ __('Payscale No') }}</th>
                                    @foreach ($taxableHeads as $headName)
                                        <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ $headName }}</th>
                                    @endforeach
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ __('Other Income') }}</th>
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ __('Gross Salary') }}</th>
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ __('Old Tax') }}</th>
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ __('New Tax') }}</th>
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ __('Tax Difference') }}</th>
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ __('Old Net') }}</th>
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ __('New Net') }}</th>
                                    <th style="background-color: #1f385c; color: #ffffff; text-align: center;">{{ __('Net Difference') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totals = [
                                        'heads' => array_fill_keys($taxableHeads, 0),
                                        'other_income' => 0,
                                        'gross' => 0,
                                        'oldTax' => 0,
                                        'oldNet' => 0,
                                        'newTax' => 0,
                                        'newNet' => 0,
                                        'taxChange' => 0,
                                        'netChange' => 0,
                                    ];
                                @endphp
                                @forelse ($revisedData as $index => $row)
                                    @php
                                        $rowHeads = (array) ($row->heads ?? []);
                                        $totals['other_income'] += ($row->other_income ?? 0);
                                        $totals['gross'] += ($row->gross ?? 0);
                                        $totals['oldTax'] += ($row->oldTax ?? 0);
                                        $totals['oldNet'] += ($row->oldNet ?? 0);
                                        $totals['newTax'] += ($row->newTax ?? 0);
                                        $totals['newNet'] += ($row->newNet ?? 0);
                                        $totals['taxChange'] += ($row->taxChange ?? 0);
                                        $totals['netChange'] += ($row->netChange ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $row->branch_name }}</td>
                                        <td class="text-center">{{ $row->date }}</td>
                                        <td class="text-center">{{ $row->employee_id }}</td>
                                        <td><strong>{{ $row->name }}</strong></td>
                                        <td>{{ $row->department }}</td>
                                        <td class="text-center">{{ $row->scale_no }}</td>
                                        @foreach ($taxableHeads as $headName)
                                            @php
                                                $val = (float) ($rowHeads[$headName] ?? 0);
                                                $totals['heads'][$headName] += $val;
                                            @endphp
                                            <td class="text-end">{{ \Auth::user()->priceFormat($val) }}</td>
                                        @endforeach
                                        <td class="text-end">{{ \Auth::user()->priceFormat($row->other_income ?? 0) }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($row->gross ?? 0) }}</td>
                                        <td class="text-end text-muted">{{ \Auth::user()->priceFormat($row->oldTax) }}</td>
                                        <td class="text-end font-weight-bold">{{ \Auth::user()->priceFormat($row->newTax) }}</td>
                                        <td class="text-end font-weight-bold {{ $row->taxChange > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ $row->taxChange > 0 ? '+' : '' }}{{ \Auth::user()->priceFormat($row->taxChange) }}
                                        </td>
                                        <td class="text-end text-muted">{{ \Auth::user()->priceFormat($row->oldNet) }}</td>
                                        <td class="text-end font-weight-bold">{{ \Auth::user()->priceFormat($row->newNet) }}</td>
                                        <td class="text-end font-weight-bold {{ $row->netChange < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ $row->netChange > 0 ? '+' : '' }}{{ \Auth::user()->priceFormat($row->netChange) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ count($taxableHeads) + 16 }}" class="text-center text-muted py-4">
                                            {{ __('No salary revision records found.') }}
                                        </td>
                                    </tr>
                                @endforelse

                                @if(count($revisedData) > 0)
                                    {{-- Totals Row --}}
                                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                                        <td colspan="7" class="text-center font-weight-bold" style="background-color: #e2e8f0;">Total</td>
                                        @foreach ($taxableHeads as $headName)
                                            <td class="text-end font-weight-bold" style="background-color: #e2e8f0;">{{ \Auth::user()->priceFormat($totals['heads'][$headName]) }}</td>
                                        @endforeach
                                        <td class="text-end font-weight-bold" style="background-color: #e2e8f0;">{{ \Auth::user()->priceFormat($totals['other_income']) }}</td>
                                        <td class="text-end font-weight-bold" style="background-color: #e2e8f0;">{{ \Auth::user()->priceFormat($totals['gross']) }}</td>
                                        <td class="text-end font-weight-bold" style="background-color: #e2e8f0;">{{ \Auth::user()->priceFormat($totals['oldTax']) }}</td>
                                        <td class="text-end font-weight-bold" style="background-color: #e2e8f0;">{{ \Auth::user()->priceFormat($totals['newTax']) }}</td>
                                        <td class="text-end font-weight-bold {{ $totals['taxChange'] > 0 ? 'text-danger' : 'text-success' }}" style="background-color: #e2e8f0;">
                                            {{ $totals['taxChange'] > 0 ? '+' : '' }}{{ \Auth::user()->priceFormat($totals['taxChange']) }}
                                        </td>
                                        <td class="text-end font-weight-bold" style="background-color: #e2e8f0;">{{ \Auth::user()->priceFormat($totals['oldNet']) }}</td>
                                        <td class="text-end font-weight-bold" style="background-color: #e2e8f0;">{{ \Auth::user()->priceFormat($totals['newNet']) }}</td>
                                        <td class="text-end font-weight-bold {{ $totals['netChange'] < 0 ? 'text-danger' : 'text-success' }}" style="background-color: #e2e8f0;">
                                            {{ $totals['netChange'] > 0 ? '+' : '' }}{{ \Auth::user()->priceFormat($totals['netChange']) }}
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
