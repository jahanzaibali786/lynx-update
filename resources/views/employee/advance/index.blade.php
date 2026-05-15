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
                data-bs-title="{{ __('Create Advance') }}" class="apply-btn btn mx-1 btn-sm btn-outline-primary" data-bs-toggle="tooltip" >
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
                                                data-bs-title="{{ __('apply') }}" data-bs-toggle="tooltip" data-bs-title="{{ __('Search') }}">
                                                <span class="btn-inner--icon">Search</span>
                                            </a>
                                            <a href="{{ route('employee-advance.index') }}"
                                                class="btn mx-1 btn-sm btn-outline-danger"
                                                data-bs-title="{{ __('Reset') }}" data-bs-toggle="tooltip"
                                                data-bs-title="{{ __('Clear') }}">
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
                            <th>{{ __('Advance Month') }}</th>
                            <th>{{ __('Approval Date') }}</th>
                            <th>{{ __('Advance Amount') }}</th>
                            <th>{{ __('Advance Reason') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Deduction') }}</th>
                            <th>{{ __('Approved By') }}</th>
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
                                <td>{{ !empty($adv->advance_date) ? \Carbon\Carbon::parse($adv->advance_date)->format('M Y') : '-' }}</td>
                                <td>{{ !empty($adv->approval_date) ? \Carbon\Carbon::parse($adv->approval_date)->format('d-M-Y') : '-' }}</td>
                                <td>{{ @$adv->advance_amount }}</td>
                                <td style="max-width: 280px;">
                                    @php
                                        $reason = $adv->advance_reason ?? '';
                                        $reasonPreview = \Illuminate\Support\Str::words($reason, 50, '...');
                                    @endphp
                                    @if(!empty($reason))
                                        <a href="#"
                                            data-bs-toggle="modal"
                                            data-bs-target="#advance-reason-modal-{{ $adv->id }}"
                                            class="text-primary d-block text-truncate"
                                            style="max-width: 260px;"
                                            title="{{ $reason }}">
                                            {{ $reasonPreview }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($adv->status == 0)
                                        <a style="width: 100%;" href="#"
                                            data-url="{{ route('employee-advance.status', @$adv->id) }}" data-size="lg"
                                            data-ajax-popup="true" data-title="{{ __('Advance Details') }}"
                                            data-bs-toggle="tooltip" data-bs-title="{{ __('Advance Details') }}"
                                            class="btn btn-sm {{ @$adv->status == 0 ? 'btn-outline-warning' : ($adv->status == 2 ? 'btn-outline-danger' : 'btn-outline-success') }}">{{ $adv->status == 0 ? 'Pending' : ($adv->status == 2 ? 'Rejected' : 'Approved') }}
                                        </a>
                                    @else
                                         <button style="width: 100%; cursor:auto; color: #fff !important;"
                                            class="btn btn-sm {{ @$adv->status == 0 ? 'btn-warning' : ($adv->status == 2 ? 'btn-danger' : 'btn-success') }}">
                                            {{ $adv->status == 1 ? 'Approved' : ($adv->status == 2 ? 'Rejected' : 'Approved') }}
                                        </button>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($adv->deducted_salary_id))
                                        <span class="badge bg-success">{{ __('Deducted') }}</span>
                                    @else
                                        <span class="badge bg-warning">{{ __('Pending') }}</span>
                                    @endif
                                </td>
                                <td><small>{{ !empty($adv->approvedBy->name) ? $adv->approvedBy->name : '-' }}</small></td>

                                <td>
                                    <div class="action-btn d-flex align-items-center gap-1">
                                        @if($adv->status == 0)
                                            <a href="#" data-url="{{ route('employee-advance.status', @$adv->id) }}"
                                                data-size="lg"
                                                data-ajax-popup="true"
                                                data-bs-toggle="tooltip"
                                                data-bs-title="{{ __('Approve / Reject') }}"
                                                class="btn btn-sm btn-outline-success">
                                                <span class="btn-inner--icon"><i class="ti ti-check"></i></span>
                                            </a>
                                        @endif
                                        @if($adv->status != 1 || \Auth::user()->type == 'company')
                                            <a href="#" data-url="{{ route('employee-advance.edit', @$adv->id) }}"
                                                data-size="lg"
                                                data-ajax-popup="true"
                                                data-bs-toggle="tooltip"
                                                data-bs-title="{{ __('Edit') }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                            </a>
                                        @endif
                                        @if (\Auth::user()->type == 'company')
                                            @if($adv->status != 1)
                                                {!! Form::open([
                                                    'method' => 'DELETE',
                                                    'route' => ['employee-advance.destroy', $adv->id],
                                                    'id' => 'delete-form-' . $adv->id,
                                                    'class' => 'd-inline',
                                                ]) !!}

                                                <a type="button"
                                                    class="btn btn-sm btn-outline-danger bs-pass-para"
                                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                    data-confirm-yes="document.getElementById('delete-form-{{ $adv->id }}').submit();"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-title="{{ __('Delete') }}">
                                                     <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                                </a>
                                                {!! Form::close() !!}
                                            @endif
                                        @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @foreach ($advance as $adv)
                <div class="modal fade" id="advance-reason-modal-{{ $adv->id }}" tabindex="-1" aria-labelledby="advance-reason-modal-label-{{ $adv->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="advance-reason-modal-label-{{ $adv->id }}">{{ __('Advance Reason') }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                            </div>
                            <div class="modal-body" style="white-space: pre-line;">
                                {!! nl2br(e($adv->advance_reason)) !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
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
