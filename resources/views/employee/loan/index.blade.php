@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Loan') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Loan') }}</li>
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('public/acron/searchselect.css') }}" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
@endpush

@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
     <script src="{{ asset('public/acron/searchselect.js') }}"></script>
    <script>
        function loanFilterBranchEmployees(id) {
            var $employeeSelect = $('#loan_filter_employee_id');

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
                        $employeeSelect.empty();
                        $employeeSelect.append($('<option>', {
                            value: '',
                            text: 'Select Employee'
                        }));

                        for (var j = 0; j < result.employee.length; j++) {
                            var cls = result.employee[j];
                            $employeeSelect.append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                        if ($employeeSelect.data('selected')) {
                            $employeeSelect.val($employeeSelect.data('selected'));
                        }
                        $employeeSelect.trigger('change');
                    }
                    if (result.status == 'error') {}
                }
            });
        }

        $(document).ready(function() {
            $('#loan_filter_employee_id').data('selected', "{{ request('employee_id') }}");
        });
    </script>
@endpush
@section('action-btn')
    @can('create loan')
        <div class="col text-end">
            <a href="#" data-url="{{ route('loan.create') }}" data-size="lg" data-ajax-popup="true"
                 data-title="{{ __('Create Loan') }}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ __('Create Loan') }}"
                style="align-content: space-evenly;" class="apply-btn btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        </div>
    @endcan
@endsection
@section('content')
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2 " id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body filter_change">
                            {{ Form::open(['route' => ['loan.index'], 'method' => 'GET', 'id' => 'loan_submit']) }}
                            <div class="row d-flex justify-content-end ">
                                <div class="col-xl-11">
                                    <div class="row">
                                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                            <div class="btn-box">
                                                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                                {{ Form::select('branches', $branches, request('branches'), ['class' => 'form-control select', 'onchange' => 'loanFilterBranchEmployees(this.value)']) }}
                                            </div>
                                        </div>
                                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                            <div class="btn-box">
                                                {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                                {{ Form::select('department_id', $departments, request('department_id'), ['class' => 'form-control  ']) }}
                                            </div>
                                        </div>
                                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                            <div class="btn-box">
                                                {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                                {{ Form::select('designation_id', $designations, request('designation_id'), ['class' => 'form-control  ']) }}
                                            </div>
                                        </div>
                                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-2">
                                            <div class="btn-box">
                                                {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}
                                                {{ Form::select('employee_id', $employees, request('employee_id'), ['class' => 'form-control select custom-select', 'id' => 'loan_filter_employee_id']) }}
                                            </div>
                                        </div>

                                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2 mt-2">
                                            <div class="btn-box">
                                                {{ Form::label('fiscal_year', __('Fiscal Year'), ['class' => 'form-label']) }}
                                                {{ Form::select('fiscal_year', $fiscalYears, request('fiscal_year'), ['class' => 'form-control select']) }}
                                            </div>
                                        </div>
                                       <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                            <div class="btn-box">
                                                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                                {{ Form::select('status', ['' => 'Select Status', '0' => 'Pending', '1' => 'Approved', '2' => 'Rejected'], request('status'), ['class' => 'form-control select']) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-auto mt-4 ">
                                    <div class="row">
                                        <div class="col-auto mt-1">
                                            <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                                onclick="document.getElementById('loan_submit').submit(); return false;"
                                                 data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ __('apply') }}"
                                                style="align-content: space-evenly;">
                                                <span class="btn-inner--icon">Search</span>
                                            </a>
                                            <a href="{{ route('loan.index') }}"
                                                class="btn mx-1 btn-sm btn-outline-danger" 
                                                data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="{{ __('Reset') }}" style="align-content: space-evenly;">
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
            @if (!$loans->isEmpty())
                <table class="datatable">
                    <thead class="">
                        <tr class="table_heads">
                            <th>#</th>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Loan Amount') }}</th>
                            <th>{{ __('Received Amount') }}</th>
                            <th>{{ __('Deduction Start Month') }}</th>
                            <th>{{ __('End Month') }}</th>
                            <th>{{ __('Stopped Months') }}</th>
                            <th>{{ __('charge amnt/mon') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Approved By') }}</th>
                            @if (\Auth::user()->type != 'Employee')
                                <th>{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loans as $loan)
                            
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td style='width:100px;'>
                                    @if(!empty($loan->employee))
                                    @can('show employee profile')
                                        <a style="width: 100%;" href="#" data-url="{{ route('loan.show', $loan->id) }}"
                                            data-size="lg" data-ajax-popup="true" data-title="{{ __('Loan Details') }}"
                                            data-bs-toggle="tooltip" data-bs-title="{{ __('Loan Details') }}"
                                            class="apply-btn btn mx-1 btn-sm btn-outline-primary">{{ !empty($loan->employee->name) ? $loan->employee->name : '' }}</a>
                                    @else
                                        <a href="#"
                                            class="btn btn-outline-primary">{{ !empty($loan->employee->name) ? $loan->employee->name : '' }}</a>
                                    @endcan
                                    @else
                                        {{ '-' }}
                                    @endif
                                </td>
                                <td>{{ $loan->title }}</td>
                                <td>{{ @$loan->amount }}</td>
                                <td>{{ @$loan->received_amount }}</td>
                                <td>{{ !empty($loan->from_pay_month) ? \Carbon\Carbon::parse($loan->from_pay_month)->format('M Y') : '-' }}</td>
                                <td>{{ !empty($loan->loan_ended) ? \Carbon\Carbon::parse($loan->loan_ended)->format('M Y') : '-' }}</td>
                                <td>{{ $loan->stopHistories->sum('months') }}</td>
                                <td>{{ @$loan->per_month_amount }}</td>
                                <td>
                                    @if ($loan->status == 0)
                                        <a style="width: 100%;" href="#"
                                            data-url="{{ route('loan.status', $loan->id) }}" data-size="lg"
                                            data-ajax-popup="true" data-title="{{ __('Loan Details') }}"
                                            data-bs-toggle="tooltip" data-bs-title="{{ __('Loan Details') }}"
                                            class="btn btn-sm {{ @$loan->status == 0 ? 'btn-outline-warning' : ($loan->status == 2 ? 'btn-outline-danger' : 'btn-outline-success') }}">{{ $loan->status == 0 ? 'Pending' : ($loan->status == 2 ? 'Rejected' : 'Approved') }}
                                        </a>
                                    @else
                                         <button style="width: 100%; cursor:auto; color: #fff !important;"
                                            class="btn btn-sm {{ @$loan->status == 0 ? 'btn-warning' : ($loan->status == 2 ? 'btn-danger' : 'btn-success') }}">
                                            {{ $loan->status == 1 ? 'Approved' : ($loan->status == 2 ? 'Rejected' : 'Approved') }}
                                        </button>
                                    @endif
                                </td>
                                <td>{{ !empty($loan->approvedBy->name) ? $loan->approvedBy->name : '-' }}</td>
                                @if (\Auth::user()->type != 'Employee')
                                    <td>
                                        <div class="action-btn ms-2 d-flex align-items-center gap-1">
                                            <a class="mx-1 btn mx-1 btn-sm btn-outline-warning"
                                                href="{{ route('printloan', $loan->id) }}"
                                                data-bs-toggle="tooltip"
                                                data-bs-title="{{ __('Print') }}">
                                                <span class="btn-inner--icon"><i class="fas fa-print"></i></span>
                                            </a>
                                            @can('edit loan')
                                                 @if(
                                                        (in_array($loan->status, [0, 1, 2]) && Auth::user()->type == 'company') ||
                                                        in_array($loan->status, [0, 2])
                                                    )
                                                        <a href="#" data-url="{{ URL::to('loan/' . $loan->id . '/edit') }}"
                                                            data-size="lg" data-ajax-popup="true"
                                                            data-title="{{ __('Edit Loan') }}"
                                                            class="mx-1 btn btn-sm btn-outline-primary"
                                                            data-bs-toggle="tooltip"
                                                            data-bs-title="{{ __('Edit Loan') }}">

                                                            <span class="btn-inner--icon">
                                                                <i class="ti ti-pencil"></i>
                                                            </span>
                                                        </a>
                                                    @endif
                                                @if ($loan->status == 1)
                                                    <a href="#" data-url="{{ route('loan.stop', $loan->id) }}"
                                                        data-size="lg" data-ajax-popup="true"
                                                        data-title="{{ __('Stop Loan') }}"
                                                        class="mx-1 btn btn-sm btn-outline-info"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-title="{{ __('Stop Loan') }}">
                                                        <span class="btn-inner--icon">
                                                            <i class="ti ti-player-pause"></i>
                                                        </span>
                                                    </a>
                                                @endif
                                            @endcan
                                            @if ($loan->status != 1)
                                                @can('delete loan')
                                                    {!! Form::open([
                                                        'method' => 'DELETE',
                                                        'route' => ['loan.destroy', $loan->id],
                                                        'id' => 'loan-delete-form-' . $loan->id,
                                                    ]) !!}
                                                    <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger bs-pass-para"
                                                        data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="document.getElementById('loan-delete-form-{{ $loan->id }}').submit();"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-title="{{ __('Delete') }}">
                                                        <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                                    </a>
                                                    {!! Form::close() !!}
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="mt-2 text-center">
                    No Loan Data Found!
                </div>
            @endif
        </div>
    </div>

@endsection
