@extends('layouts.admin')
@section('page-title')
    {{ __('Revise Employee Taxes') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('tax-slab.index') }}">{{ __('Tax Slabs') }}</a></li>
    <li class="breadcrumb-item">{{ __('Revise') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    @if(!$taxSlabExists)
                        <div class="alert alert-warning text-center" role="alert">
                            <i class="ti ti-alert-triangle me-2"></i>
                            {{ __('Tax slab for current year is not created yet. Please create a tax slab first.') }}
                        </div>
                    @elseif(date('m') != 7)
                        <div class="alert alert-info text-center" role="alert">
                            <i class="ti ti-info-circle me-2"></i>
                            {{ __('Tax revision is only allowed during the month of July.') }}
                        </div>
                    @else
                        {{ Form::open(['route' => ['tax-slab.reviseEmployeeTaxes'], 'method' => 'POST', 'id' => 'tax_revise_form']) }}
                        <div class="row align-items-end justify-content-start">
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                                <div class="btn-box">
                                    {{ Form::label('branch_id', __('Select Branch to Revise Tax'), ['class' => 'form-label']) }}
                                    {{ Form::select('branch_id', $branches, $branchId, ['class' => 'form-control select', 'id' => 'branch_filter_select', 'required' => true]) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary btn-sm me-2" onclick="return confirm('{{ __('Are you sure you want to run tax revision and update scale histories for all active employees in the selected branch?') }}')" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{ __('Run tax revision and update scale histories for the selected branch') }}">
                                    <i class="ti ti-refresh me-1"></i>{{ __('Update Tax & Show Details') }}
                                </button>
                                <a href="{{ route('tax-slab.showRevisePage', ['clear_session' => 1]) }}" class="btn btn-outline-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{ __('Clear selection') }}">
                                    {{ __('Reset') }}
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    @endif
                </div>
            </div>
        </div>

        @if(count($recentlyUpdated) > 0)
            <div class="col-xl-12 mt-3">
                <div class="card border border-success">
                    <div class="card-header bg-light-success text-success d-flex justify-content-between align-items-center">
                        <h5>
                            <i class="ti ti-checkbox me-2"></i>{{ __('Recently Revised Employees (Updates Saved)') }}
                        </h5>
                        <a href="{{ route('tax-slab.exportRevised') }}" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{ __('Export revised employees to Excel') }}">
                             <i class="ti ti-download me-1"></i>{{ __('Export Excel') }}
                        </a>
                    </div>
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table table-bordered align-items-center">
                                <thead class="table_heads">
                                    <tr style="background-color: #d1e7dd; color: #0f5132;">
                                        <th>#</th>
                                        <th>{{ __('Employee ID') }}</th>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Scale No') }}</th>
                                        @foreach ($taxableHeads as $headName)
                                            <th>{{ $headName }}</th>
                                        @endforeach
                                        <th>{{ __('Other Income') }}</th>
                                        <th>{{ __('Gross') }}</th>
                                        <th class="text-end">{{ __('Old Tax') }}</th>
                                        <th class="text-end">{{ __('New Tax') }}</th>
                                        <th class="text-end">{{ __('Tax Change') }}</th>
                                        <th class="text-end">{{ __('Old Net') }}</th>
                                        <th class="text-end">{{ __('New Net') }}</th>
                                        <th class="text-end">{{ __('Net Change') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentlyUpdated as $index => $row)
                                        @php
                                            $rowHeads = (array) ($row->heads ?? []);
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $row->employee_id }}</td>
                                            <td><strong>{{ $row->name }}</strong></td>
                                            <td>{{ $row->scale_no ?? '-' }}</td>
                                            @foreach ($taxableHeads as $headName)
                                                <td>{{ \Auth::user()->priceFormat($rowHeads[$headName] ?? 0) }}</td>
                                            @endforeach
                                            <td>{{ \Auth::user()->priceFormat($row->other_income ?? 0) }}</td>
                                            <td>{{ \Auth::user()->priceFormat($row->gross ?? 0) }}</td>
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(isset($_GET['branch_id']) || !empty($branchId))
            <div class="col-xl-12 mt-3">
                <div class="alert alert-light text-center p-4" role="alert" style="background-color: #f8fafc; border: 1px dashed #cbd5e1;">
                    <i class="ti ti-circle-check text-success fs-3 d-block mb-2"></i>
                    <h5>{{ __('No Changes Found!') }}</h5>
                    <p class="text-muted m-0">
                        {{ __('No employees in the selected branch had any pending tax revisions under the current tax slab rules, so no updates were required.') }}
                    </p>
                </div>
            </div>
        @else
            <div class="col-xl-12 mt-3">
                <div class="alert alert-info text-center p-4" role="alert">
                    <i class="ti ti-info-circle fs-3 d-block mb-2"></i>
                    <h5>{{ __('Ready for Tax Revision') }}</h5>
                    <p class="text-muted m-0">
                        {{ __('Please select a branch from the dropdown above and click "Update Tax & Show Details" to revise employee taxes for that branch.') }}
                    </p>
                </div>
            </div>
        @endif
    </div>
@endsection
