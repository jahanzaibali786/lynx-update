@extends('layouts.admin')
@section('page-title')
    {{ __('Advance Tax Collection') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Advance Tax Collection') }}</li>
@endsection

@push('script-page')
    <script>
        function advanceTaxCollectionEmployees(id, target) {
            var employeeTarget = target || '#employee_id';
            var $employee = $(employeeTarget);
            var previousValue = $employee.val();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('branch.employees') }}",
                type: "POST",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(result) {
                    if (result.status == 'success') {
                        if ($employee[0] && $employee[0].customSelectInstance) {
                            try {
                                $employee[0].customSelectInstance.destroy();
                            } catch (e) {}
                            delete $employee[0].customSelectInstance;
                        }
                        if ($employee.next('.custom-select-wrapper').length) {
                            $employee.next('.custom-select-wrapper').remove();
                        }
                        $employee.removeClass('custom-select');

                        $employee.empty();
                        $employee.append($('<option>', {
                            value: '',
                            text: 'Select Employee'
                        }));
                        for (var j = 0; j < result.employee.length; j++) {
                            var emp = result.employee[j];
                            $employee.append($('<option>', {
                                value: emp.id,
                                text: emp.name
                            }));
                        }

                        $employee.addClass('custom-select').show();
                        if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
                            window.CustomSelect.create($employee[0]);
                        }

                        if (previousValue && $employee.find('option[value="' + previousValue + '"]').length) {
                            $employee.val(previousValue);
                            if ($employee[0] && $employee[0].customSelectInstance && typeof $employee[0].customSelectInstance.refresh === 'function') {
                                $employee[0].customSelectInstance.refresh();
                            }
                        }
                    }
                }
            });
        }

        function branchemployees(id) {
            advanceTaxCollectionEmployees(id, '#employee_id');
        }
    </script>
@endpush

@section('action-btn')
    <div class="col text-end">
        <a href="#" data-url="{{ route('advance-tax-collection.create') }}" data-size="lg" data-ajax-popup="true"
            data-bs-title="{{ __('Create Advance Tax Collection') }}" class="btn mx-1 btn-sm btn-outline-primary" data-bs-toggle="tooltip">
            <span class="btn-inner--icon">Create</span>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['advance-tax-collection.index'], 'method' => 'GET', 'id' => 'advance_tax_collection_submit']) }}
                        <div class="row d-flex justify-content-end">
                            <div class="col-xl-12">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                            {{ Form::select('branches', $branches, request('branches'), ['class' => 'form-control select', 'onchange' => 'advanceTaxCollectionEmployees(this.value, "#employee_id")']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                            {{ Form::select('department_id', $departments, request('department_id'), ['class' => 'form-control select']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                            {{ Form::select('designation_id', $designations, request('designation_id'), ['class' => 'form-control select']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                            {{ Form::select('employee_id', $employees, request('employee_id'), ['class' => 'form-control select custom-select', 'id' => 'employee_id']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('tax_month', __('Tax Month'), ['class' => 'form-label']) }}
                                            {{ Form::month('tax_month', request('tax_month'), ['class' => 'form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                            {{ Form::select('status', ['' => __('Select Status')] + $statuses, request('status'), ['class' => 'form-control select']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mt-4">
                                        <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                            onclick="document.getElementById('advance_tax_collection_submit').submit(); return false;"
                                            data-bs-toggle="tooltip" data-bs-title="{{ __('Search') }}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>
                                        <a href="{{ route('advance-tax-collection.index') }}"
                                            class="btn mx-1 btn-sm btn-outline-danger"
                                            data-bs-toggle="tooltip" data-bs-title="{{ __('Clear') }}">
                                            <span class="btn-inner--icon">Clear</span>
                                        </a>
                                    </div>
                            </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body full-card">
        <div class="table-responsive">
            @if (!$collections->isEmpty())
                <table class="">
                    <thead>
                        <tr class="table_heads">
                            <th>#</th>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Tax Month') }}</th>
                            <th>{{ __('Collection Date') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Payment Method') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Approval Date') }}</th>
                            <th>{{ __('Approved By') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($collections as $collection)
                            <tr>
                                <td>{{ ($collections->currentPage() - 1) * $collections->perPage() + $loop->iteration }}</td>
                                <td>{{ @$collection->employee->name ?: '-' }}</td>
                                <td>{{ \Carbon\Carbon::parse($collection->tax_month)->format('M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($collection->collection_date)->format('d-M-Y') }}</td>
                                <td>{{ number_format($collection->amount, 2) }}</td>
                                <td>{{ $collection->payment_method ? ucfirst($collection->payment_method) : '-' }}</td>
                                <td>{{ $collection->reference ?: '-' }}</td>
                                <td>
                                    @if($collection->status == 1)
                                        <span class="badge bg-success">{{ __('Approved') }}</span>
                                    @elseif($collection->status == 2)
                                        <span class="badge bg-danger">{{ __('Rejected') }}</span>
                                    @else
                                        @if(\Auth::user()->type == 'company')
                                            <a href="#" data-url="{{ route('advance-tax-collection.status', $collection->id) }}"
                                                data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"
                                                data-bs-title="{{ __('Approve / Reject') }}"
                                                class="btn btn-sm btn-outline-warning w-100">
                                                {{ __('Pending') }}
                                            </a>
                                        @else
                                            <span class="badge bg-warning">{{ __('Pending') }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ !empty($collection->approval_date) ? \Carbon\Carbon::parse($collection->approval_date)->format('d-M-Y') : '-' }}</td>
                                <td><small>{{ !empty($collection->approvedBy->name) ? $collection->approvedBy->name : '-' }}</small></td>
                                <td>
                                    <div class="action-btn d-flex align-items-center gap-1">
                                        @if($collection->status == 0 && \Auth::user()->type == 'company')
                                            <a href="#" data-url="{{ route('advance-tax-collection.status', $collection->id) }}"
                                                data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"
                                                data-bs-title="{{ __('Approve / Reject') }}"
                                                class="btn btn-sm btn-outline-success">
                                                <span class="btn-inner--icon"><i class="ti ti-check"></i></span>
                                            </a>
                                        @endif
                                        <a href="#" data-url="{{ route('advance-tax-collection.show', $collection->id) }}"
                                            data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"
                                            data-bs-title="{{ __('View') }}" class="btn btn-sm btn-outline-info">
                                            <span class="btn-inner--icon"><i class="ti ti-eye"></i></span>
                                        </a>
                                        @if($collection->status != 1 || \Auth::user()->type == 'company')
                                            <a href="#" data-url="{{ route('advance-tax-collection.edit', $collection->id) }}"
                                                data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"
                                                data-bs-title="{{ __('Edit') }}" class="btn btn-sm btn-outline-primary">
                                                <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                            </a>
                                        @endif
                                        @if($collection->status != 1)
                                            {!! Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['advance-tax-collection.destroy', $collection->id],
                                                'id' => 'delete-form-' . $collection->id,
                                                'class' => 'd-inline',
                                            ]) !!}
                                            <a type="button" class="btn btn-sm btn-outline-danger bs-pass-para"
                                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                data-confirm-yes="document.getElementById('delete-form-{{ $collection->id }}').submit();"
                                                data-bs-toggle="tooltip" data-bs-title="{{ __('Delete') }}">
                                                <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                            </a>
                                            {!! Form::close() !!}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if ($collections->hasPages())
                    <div class="pagination">
                        <ul>
                            @if ($collections->onFirstPage())
                                <li class="disabled">&laquo;</li>
                            @else
                                <li><a href="{{ $collections->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                            @endif
                            @for ($page = 1; $page <= $collections->lastPage(); $page++)
                                <li class="{{ $page == $collections->currentPage() ? 'active' : '' }}">
                                    <a href="{{ $collections->appends(request()->query())->url($page) }}">{{ $page }}</a>
                                </li>
                            @endfor
                            @if ($collections->hasMorePages())
                                <li><a href="{{ $collections->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a></li>
                            @else
                                <li class="disabled">&raquo;</li>
                            @endif
                        </ul>
                    </div>
                @endif
            @else
                <div class="text-center">
                    <h6>{{ __('No advance tax collection records found.') }}</h6>
                </div>
            @endif
        </div>
    </div>
@endsection
