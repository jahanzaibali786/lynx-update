@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Employee Advance') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Advance') }}</li>
@endsection
@push('script-page')
    <script>
        function branchemployees(id) {
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
                    console.log(result);
                    if (result.status == 'success') {
                        $('#employee_id').empty();
                        $('#employee_id').append($('<option>', {
                            value: '',
                            text: 'Select Employee'
                        }));
                        for (var j = 0; j < result.employee.length; j++) {
                            var cls = result.employee[j];
                            $('#employee_id').append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                    }
                    if (result.status == 'error') {}
                }
            });
        }
    </script>
@endpush
@section('action-btn')
    @can('create loan')
        <div class="col text-end">
            <a href="#" data-url="{{ route('employee-advance.create') }}" data-size="lg" data-ajax-popup="true"
                data-bs-title="{{ __('Create Advance') }}" class="apply-btn btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        </div>
    @endcan
@endsection
@section('content')
    @if (\Auth::user()->type == 'company')
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2 " id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body">
                            {{ Form::open(['route' => ['employee-advance.index'], 'method' => 'GET', 'id' => 'loan_submit']) }}
                            <div class="row d-flex justify-content-end ">
                                <div class="col-xl-10">
                                    <div class="row">
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                            <div class="btn-box">
                                                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                                {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'onchange' => 'branchtype(this.value)']) }}
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                            <div class="btn-box">
                                                {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                                {{ Form::select('department_id', $departments, isset($_GET['department_id']) ? $_GET['department_id'] : '', ['class' => 'form-control  ']) }}
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                            <div class="btn-box">
                                                {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                                {{ Form::select('designation_id', $designations, isset($_GET['designation_id']) ? $_GET['designation_id'] : '', ['class' => 'form-control  ']) }}
                                            </div>
                                        </div>
                                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                            <div class="btn-box">
                                                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                                {{ Form::select('status', ['' => 'Select Status', '0' => 'Pending', '1' => 'Approved', '2' => 'Rejected'], isset($_GET['status']) ? $_GET['status'] : '', ['class' => 'form-control select']) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto mt-4 ">
                                    <div class="row">
                                        <div class="col-auto mt-1">
                                            <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                                onclick="document.getElementById('loan_submit').submit(); return false;"
                                                data-bs-title="{{ __('apply') }}">
                                                <span class="btn-inner--icon">Search</span>
                                            </a>
                                            <a href="{{ route('employee-advance.index') }}"
                                                class="btn mx-1 btn-sm btn-outline-danger"
                                                data-bs-title="{{ __('Reset') }}">
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
    @endif
    <div class="card-body full-card">
        <div class="table-responsive">
            @if (!$advance->isEmpty())
                <table class="">
                    <thead class="">
                        <tr class="table_heads">
                            <th>#</th>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Advnace Date') }}</th>
                            <th>{{ __('Advnace Amount') }}</th>
                            <th>{{ __('Advnace Reason') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($advance as $adv)
                            <tr>
                                <td>{{ ($advance->currentPage() - 1) * $advance->perPage() + $loop->iteration }}</td>
                                <td>
                                    {{ !empty($adv->employee->name) ? $adv->employee->name : '' }}
                                </td>
                                <td>{{ @$adv->advance_date }}</td>
                                <td>{{ @$adv->advance_amount }}</td>
                                <td>{{ @$adv->advance_reason }}</td>

                                <td>
                                    <div class="action-btn">
                                        <a href="#" data-url="{{ route('employee-advance.edit', @$adv->id) }}"
                                            data-size="lg" data-ajax-popup="true" data-bs-title="{{ __('Edit Advance') }}"
                                            class="apply-btn btn mx-1 btn-sm btn-outline-primary">
                                            <i class="ti ti-pencil"></i>
                                        </a>
                                        @if (\Auth::user()->type == 'company')
                                            {!! Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['employee-advance.destroy', $adv->id],
                                                'id' => 'delete-form-' . $adv->id,
                                            ]) !!}

                                            <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger bs-pass-para"
                                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                data-confirm-yes="document.getElementById('delete-form-{{ $adv->id }}').submit();"
                                                data-bs-title="{{ __('Delete') }}">
                                                <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                            </a>
                                            {!! Form::close() !!}
                                        @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="mt-2 text-center">
                No Advnace Data Found!
            </div>
        @endif
    </div>
</div>
</div>
@if ($advance->hasPages())
    <div class="pagination">
        <ul>
            @if ($advance->onFirstPage())
                <li class="disabled">&laquo; Previous</li>
            @else
                <li><a href="{{ $advance->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;
                        Previous</a></li>
            @endif
            @if ($advance->currentPage() > 1)
                <li><a href="{{ $advance->appends(request()->query())->url(1) }}">First</a></li>
            @endif
            @php
                $currentPage = $advance->currentPage();
                $lastPage = $advance->lastPage();
                $startPage = max(1, $currentPage - 4);
                $endPage = min($lastPage, $currentPage + 5);
                if ($endPage - $startPage < 9) {
                    if ($currentPage < $lastPage - 9) {
                        $endPage = $startPage + 9;
                    } else {
                        $startPage = max(1, $lastPage - 9);
                    }
                }
            @endphp
            @for ($page = $startPage; $page <= $endPage; $page++)
                <li class="{{ $page == $advance->currentPage() ? 'active' : '' }}">
                    <a href="{{ $advance->appends(request()->query())->url($page) }}">{{ $page }}</a>
                </li>
            @endfor
            @if ($advance->hasMorePages())
                <li><a href="{{ $advance->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                        &raquo;</a></li>
            @else
                <li class="disabled">Next &raquo;</li>
            @endif
            @if ($advance->currentPage() < $advance->lastPage())
                <li><a href="{{ $advance->appends(request()->query())->url($advance->lastPage()) }}">Last</a>
                </li>
            @endif
        </ul>
    </div>
@endif
</div>
@endsection
