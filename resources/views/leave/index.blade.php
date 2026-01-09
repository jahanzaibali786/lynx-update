@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Leave') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Manage Leave') }}</li>
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
    <div class="float-end">
        @can('create leave')
            <a href="#" data-size="lg" data-url="{{ route('leave.create') }}" data-ajax-popup="true"
                data-bs-title="{{ __('Create Leave') }}" class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    @if (\Auth::user()->type == 'company')
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2 " id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body">
                            {{ Form::open(['route' => ['leave.index'], 'method' => 'GET', 'id' => 'leave_submit']) }}
                            <div class="row d-flex justify-content-end ">

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'onchange' => 'branchtype(this.value)']) }}
                                    </div>
                                </div>

                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                        onclick="document.getElementById('leave_submit').submit(); return false;"
                                        data-bs-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('leave.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                        data-bs-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon">Clear</span>
                                    </a>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <table class="">
        <thead>
            <tr class="table_heads">
                <th>#</th>
                @if (\Auth::user()->type != 'Employee')
                    <th>{{ __('Employee') }}</th>
                @endif
                <th>{{ __('Leave Type') }}</th>
                <th>{{ __('Applied On') }}</th>
                <th>{{ __('Start Date') }}</th>
                <th>{{ __('End Date') }}</th>
                <th>{{ __('Total Days') }}</th>
                <th>{{ __('Leave Reason') }}</th>
                <th>{{ __('status') }}</th>
                @can('edit leave')
                    <th width="200px">{{ __('Action') }}</th>
                @endcan
            </tr>
        </thead>
        <tbody>
            @foreach ($leaves as $leave)
                <tr>
                    <td>{{ ($leaves->currentPage() - 1) * $leaves->perPage() + $loop->iteration }}</td>
                    @if (\Auth::user()->type != 'Employee')
                        <td>{{ !empty(\Auth::user()->getEmployee($leave->employee_id)) ? \Auth::user()->getEmployee(@$leave->employee_id)->name : '-' }}
                        </td>
                    @endif
                    <td>{{ !empty(\Auth::user()->getLeaveType($leave->leave_type_id)) ? \Auth::user()->getLeaveType($leave->leave_type_id)->title : '' }}
                    </td>
                    <td>{{ \Auth::user()->dateFormat($leave->applied_on) }}</td>
                    <td>{{ \Auth::user()->dateFormat($leave->start_date) }}</td>
                    <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td>
                    <td>{{ $leave->total_leave_days }}</td>
                    @php
                        $leavesreasons = [
                            'sick_leave' => __('Sick Leave'),
                            'domestic_problem' => __('Domestic Problem'),
                            'maternity' => __('Maternity'),
                        ];
                    @endphp
                    <td>{{ $leavesreasons[$leave->leave_reason] }}</td>
                    <td>
                        @if ($leave->status == 'Pending')
                            <div class="status_badge badge bg-warning p-2 px-3 rounded">
                                {{ $leave->status }}</div>
                        @elseif($leave->status == 'Approved')
                            <div class="status_badge badge bg-success p-2 px-3 rounded">{{ $leave->status }}</div>
                            @else($leave->status=="Reject")
                            <div class="status_badge badge bg-danger p-2 px-3 rounded">{{ $leave->status }}</div>
                        @endif
                    </td>
                    <td style="display: flex
;
    gap: 10px;
}">
                        @if (\Auth::user()->type == 'Employee')
                            @if ($leave->status == 'Pending')
                                @can('edit leave')
                                    <div class="action-btn ms-2">
                                        <a href="#" data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}"
                                            data-size="lg" data-ajax-popup="true" data-bs-title="{{ __('Edit Leave') }}"
                                            class="btn mx-1 btn-sm btn-outline-primary align-items-center">
                                            <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span></a>
                                        </a>
                                    @endcan
                            @endif
                        @else
                            <a href="#" data-url="{{ URL::to('leave/' . $leave->id . '/action') }}" data-size="lg"
                                data-ajax-popup="true" class="btn mx-1 btn-sm btn-outline-warning  align-items-center"
                                data-bs-title="{{ __('Leave Action') }}">
                                <span class="btn-inner--icon"><i class="ti ti-caret-right"></i></span> </a>
                            @can('edit leave')
                                <a href="#" data-url="{{ URL::to('leave/' . $leave->id . '/edit') }}" data-size="lg"
                                    data-ajax-popup="true" class="btn mx-1 btn-sm btn-outline-primary align-items-center"
                                    data-bs-toggle="{{ __('Edit Leave') }}" data-bs-title="{{ __('Edit') }}">
                                    <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span></a>
                            @endcan
                        @endif
                        @can('delete leave')
                            {!! Form::open([
                                'method' => 'DELETE',
                                'route' => ['leave.destroy', $leave->id],
                                'id' => 'delete-form-' . $leave->id,
                            ]) !!}
                            <a href="#" class="btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"
                                data-bs-toggle="{{ __('Delete') }}" data-bs-title="{{ __('Delete') }}"
                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                data-confirm-yes="document.getElementById('delete-form-{{ $leave->id }}').submit();">
                                <span class="btn-inner--icon"><i class="ti ti-trash"></i></span></a>
                            {!! Form::close() !!}
                @endif
                </div>
                </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if ($leaves->hasPages())
            <div class="pagination">
                <ul>
                    @if ($leaves->onFirstPage())
                        <li class="disabled">&laquo; Previous</li>
                    @else
                        <li><a href="{{ $leaves->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;
                                Previous</a></li>
                    @endif
                    @if ($leaves->currentPage() > 1)
                        <li><a href="{{ $leaves->appends(request()->query())->url(1) }}">First</a></li>
                    @endif
                    @php
                        $currentPage = $leaves->currentPage();
                        $lastPage = $leaves->lastPage();
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
                        <li class="{{ $page == $leaves->currentPage() ? 'active' : '' }}">
                            <a href="{{ $leaves->appends(request()->query())->url($page) }}">{{ $page }}</a>
                        </li>
                    @endfor
                    @if ($leaves->hasMorePages())
                        <li><a href="{{ $leaves->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                                &raquo;</a></li>
                    @else
                        <li class="disabled">Next &raquo;</li>
                    @endif
                    @if ($leaves->currentPage() < $leaves->lastPage())
                        <li><a href="{{ $leaves->appends(request()->query())->url($leaves->lastPage()) }}">Last</a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif
    @endsection

    @push('script-page')
        <script>
            $(document).on('change', '#employee_id', function() {
                var employee_id = $(this).val();
                $.ajax({
                    url: '{{ route('leave.jsoncount') }}',
                    type: 'POST',
                    data: {
                        "employee_id": employee_id,
                        "_token": "{{ csrf_token() }}",
                    },
                    success: function(data) {
                        $('#leave_type_id').empty();
                        $('#leave_type_id').append(
                            '<option value="">{{ __('Select Leave Type ') }}</option>');
                        var annualTotal = 0;
                        var casualTotal = 0;

                        $.each(data, function(key, value) {
                            var disabled = value.isOnProbation && (value.title.toLowerCase() ==
                                'annual leave');
                            if (value.title.toLowerCase() == 'annual leave') {
                                console.log(value.days, value.title, disabled);
                                annualTotal = disabled ? 0 : (value.days - value.total_leave);
                                disabled = annualTotal == 0 ? true : false;
                            } else if (value.title.toLowerCase() == 'casual') {
                                casualTotal = disabled ? 0 : (value.days - value.total_leave);
                                disabled = casualTotal == 0 ? true : false;
                            }
                            var optionText = value.title + '&nbsp(' + value.total_leave + '/' +
                                value.days + ')';
                            var option = '<option value="' + value.id + '"' + (disabled ?
                                ' disabled' : '') + '>' + optionText + '</option>';

                            // if (value.title.toLowerCase() == 'annual leave') {
                            //     console.log(value.days, value.title, disabled);
                            //     annualTotal = disabled ? 0 : (value.days - value.total_leave);
                            //     console.log(annualTotal);
                            //     disabled = annualTotal == 0 ? true : false;
                            // } else if (value.title.toLowerCase() == 'casual') {
                            //     casualTotal = disabled ? 0 : (value.days - value.total_leave);
                            //     disabled = annualTotal == 0 ? true : false;
                            // }
                            $('#leave_type_id').append(option);
                        });

                        $('input[name="annual_total"]').val(annualTotal);
                        $('input[name="casual_total"]').val(casualTotal);
                    }
                });
            });
        </script>
        <script>
            $(document).on('change', '#start_date, #end_date', function() {
                var startDate = $('#start_date').val();
                var endDate = $('#end_date').val();

                if (startDate && endDate) {
                    var start = new Date(startDate);
                    var end = new Date(endDate);
                    var timeDifference = end.getTime() - start.getTime();
                    var dayDifference = timeDifference / (1000 * 3600 * 24) + 1;

                    if (dayDifference > 0) {
                        $('#total_days').val(dayDifference);
                    } else {
                        $('#total_days').val(0);
                    }
                }
            });
        </script>
    @endpush
