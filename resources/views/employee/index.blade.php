@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Employee') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee') }}</li>
@endsection
@push('script-page')
    <script>
    function submitWithPrintFlag() {
        const form = document.getElementById('employee_submit');
        const input = document.getElementById('is_print');
        input.value = 1;

        form.target = '_blank';
        form.submit();
        setTimeout(() => {
             input.value = 0;
             form.target = '';
        }, 5000); // 1 second delay is enough to ensure the value is used correctly
    }
</script>

@endpush
@section('action-btn')
    <div class="float-end">
        {{-- <a href="#" data-size="md"  data-bs-title="{{__('Import')}}"
        data-url="{{ route('employee.file.import') }}" data-ajax-popup="true"
        data-bs-toggle="{{__('Import employee CSV file')}}" class="btn mx-1 btn-sm btn-outline-primary">
        <span class="btn-inner--icon"><i class="ti ti-file-import"></i></span>
    </a>
    <a href="{{route('employee.export')}}"  data-bs-title="{{__('Export')}}"
        class="btn mx-1 btn-sm btn-outline-primary">
        <span class="btn-inner--icon">Export</span>
    </a> --}}
        @can('create employee')
            <a href="{{ route('employee.create') }}" data-bs-title="{{ __('Create Employee') }}"
                class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['employee.index'], 'method' => 'GET', 'id' => 'employee_submit']) }}
                        <div class="row d-flex justify-content-start ">
                            {{-- @if (\Auth::user()->type == 'company') --}}
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'onchange' => 'branchtype(this.value)']) }}
                                </div>
                            </div>
                            {{-- @endif --}}
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                    {{ Form::select('department_id', $departments, request()->get('department_id'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <input type="hidden" name="is_print" id="is_print" value="0">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                    {{ Form::select('designation_id', $designations, request()->get('designation_id'), ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('ter_status', __('Status'), ['class' => 'form-label']) }}
                                    {{ Form::select('ter_status', ['0' => 'Active', '1' => 'Resigned', '2' => 'Terminate'], isset($_GET['ter_status']) ? $_GET['ter_status'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('sort', __('Sort'), ['class' => 'form-label']) }}
                                    {{ Form::select('sort', ['' => 'Select Sort', 'asc' => 'A - Z', 'desc' => 'Z - A'], isset($_GET['sort']) ? $_GET['sort'] : '', ['class' => 'form-control select']) }}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                {{-- //print  --}}

                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('employee_submit').submit(); return false;"
                                    data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('employee.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <!-- Actions Dropdown -->
                                <div class="dropdown d-inline-block mx-1">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                        <li>
                                            <button class="dropdown-item" type="submit" name="export" value="excel">
                                                <i class="ti ti-file me-2"></i>Excel
                                            </button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" type="submit" name="export" value="pdf">
                                                <i class="ti ti-download me-2"></i>Pdf
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table datatable">
            <thead>
                <tr class="table_heads">
                    <th>Sr.</th>
                    <th>{{ __('Emp.No') }}</th>
                    <th>{{__('Image')}}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Number') }}</th>
                    <th>{{ __('Branch') }}</th>
                    <th>{{ __('Department') }}</th>
                    <th>{{ __('Designation') }}</th>
                    <th>{{ __('Date Of Joining') }}</th>
                    <th>{{ __('Pay Scale') }}</th>
                    <th>{{ __('Gross Salary') }}</th>
                    <th>{{ __('Net Salary') }}</th>
                    <!-- {{-- <th> {{__('Last Login')}}</th> --}} -->
                    <th>{{ __('Action') }}</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $employee)
                    @php
                        $lastscale = $employee->employee_payscale_details->last();
                        $monthlysalary = $employee->employee_monthly_salaries->last();
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="Id">
                            @can('show employee profile')
                                <a href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                    class="btn id-btn mx-1 btn-sm btn-outline-primary" data-bs-toggle="tooltip" data-bs-title="{{ __('Edit') }}"><span
                                        class="btn-inner--icon">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</span></a>
                            @else
                                <a href="#" class="btn id-btn mx-1 btn-sm btn-outline-primary"><span
                                        class="btn-inner--icon">{{ \Auth::user()->employeeIdFormat($employee->employee_id) }}</span></a>
                            @endcan
                        </td>
                        <td>
                            @if ($employee->profile_img)
                                <img id="profileImage" src="{{ Storage::url('emp_profile_images/' . $employee->profile_img) }}"
                                    alt="" style="border:1px solid var(--primary); width:100%; height: 50px; border-radius:50%; object-fit: contain !important;">
                            @else
                                <img id="profileImage" src="{{ Storage::url('emp_profile_images/avatar_1751294680.png') }}" alt=""
                                    style="border:1px solid var(--primary); width:100%; height: 50px; border-radius:50%; object-fit: contain !important;">
                            @endif
                        </td>
                        <td class="font-style">{{ $employee->name }}</td>
                        <td>{{ $employee->phone }}</td>
                        @if ($employee->branch_id)
                            <td class="font-style">
                                {{ !empty(\Auth::user()->getBranch($employee->owned_by)) ? \Auth::user()->getBranch($employee->owned_by)->name : '' }}
                            </td>
                        @else
                            <td>-</td>
                        @endif
                        @if ($employee->department_id)
                            <td class="font-style">
                                {{ !empty(\Auth::user()->getDepartment($employee->department_id)) ? \Auth::user()->getDepartment($employee->department_id)->name : '' }}
                            </td>
                        @else
                            <td>-</td>
                        @endif
                        @if ($employee->designation_id)
                            <td class="font-style">
                                {{ !empty(\Auth::user()->getDesignation($employee->designation_id)) ? \Auth::user()->getDesignation($employee->designation_id)->name : '' }}
                            </td>
                        @else
                            <td>-</td>
                        @endif
                        @if ($employee->company_doj)
                            <td class="font-style">{{ \Auth::user()->dateFormat($employee->company_doj) }}</td>
                        @else
                            <td>-</td>
                        @endif
                        <td class="font-style">
                            {{ !empty($lastscale->scale) ? $lastscale->scale->scale_no : '' }}
                        </td>
                        <td class="font-style">
                            {{ !empty($monthlysalary) ? $monthlysalary->gross : '' }}
                        </td>
                        <td class="font-style">
                            {{ !empty($lastscale) ? $lastscale->net : '' }}
                        </td>

                        <!-- {{--
            <td>
                {{ (!empty($employee->user->last_login_at)) ? $employee->user->last_login_at : '-' }}
            </td> --}} -->
                        @if (Gate::check('edit employee') || Gate::check('delete employee'))
                            <td>
                                <div class="action-btn ms-2">
                                    @if ($employee->is_active == 1)
                                        @can('edit employee')
                                            <a href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}"
                                                class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center"
                                                data-bs-toggle="tooltip" data-bs-title="{{ __('Edit') }}"><span
                                                    class="btn-inner--icon"><i class="ti ti-pencil"></i></span></a>
                                        @endcan
                                        @if (isset($_GET['ter_status']) != 1 || $_GET['ter_status'] == '')
                                            {{-- @can('edit employee')
                    <a href="{{route('AssignLeaveAfterProbation',$employee->id)}}"
                        class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center" 
                        data-bs-title="{{__('Assign Leave')}}" data-bs-title="{{__('Assign Leave')}}"><span class="btn-inner--icon"><i class="ti ti-eye"></i></span></a>
                @endcan --}}
                                        @endif
                                        @if (isset($_GET['ter_status']) && $_GET['ter_status'] == 1)
                                            <a href="#" data-url="{{ route('emp-rejoin', $employee->id) }}"
                                                data-size="lg" data-ajax-popup="true" data-bs-toggle="tooltip"
                                                data-bs-title="{{ __('Rejoin') }}"
                                                class="btn btn-sm  btn-outline-primary align-items-center">Rejoin</a>
                                        @endif
                                        {{-- @can('delete employee')
                    {!! Form::open(['method' => 'DELETE', 'route' => ['employee.destroy',
                    $employee->id],'id'=>'delete-form-'.$employee->id]) !!}

                    <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para" 
                        data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}"
                        data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}"
                        data-confirm-yes="document.getElementById('delete-form-{{$employee->id}}').submit();">
                        <i class="ti ti-trash ""></i></span></a>
                    {!! Form::close() !!}
                @endcan --}}
                                </div>
                            @else
                                <i class="ti ti-lock"></i>
                        @endif
                        </td>
                @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        {{-- 
        @if ($employees->hasPages())
            <div class="pagination">
                <ul>
                    @if ($employees->onFirstPage())
                        <li class="disabled">&laquo; Previous</li>
                    @else
                        <li><a href="{{ $employees->appends(request()->query())->previousPageUrl() }}"
                                rel="prev">&laquo; Previous</a></li>
                    @endif
                    @if ($employees->currentPage() > 1)
                        <li><a href="{{ $employees->appends(request()->query())->url(1) }}">First</a></li>
                    @endif
                    @php
                        $currentPage = $employees->currentPage();
                        $lastPage = $employees->lastPage();
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
                        <li class="{{ $page == $employees->currentPage() ? 'active' : '' }}">
                            <a href="{{ $employees->appends(request()->query())->url($page) }}">{{ $page }}</a>
                        </li>
                    @endfor
                    @if ($employees->hasMorePages())
                        <li><a href="{{ $employees->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                                &raquo;</a></li>
                    @else
                        <li class="disabled">Next &raquo;</li>
                    @endif
                    @if ($employees->currentPage() < $employees->lastPage())
                        <li><a href="{{ $employees->appends(request()->query())->url($employees->lastPage()) }}">Last</a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif --}}
    </div>

@endsection
