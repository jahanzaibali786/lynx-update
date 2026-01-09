@extends('layouts.admin')
@section('page-title')
{{__('Employee Salary History')}}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Employee Salary History')}}</li>
@endsection
@push('script-page')
    <script>
        function printappointmentletter(employeeId) {
            // alert('hhh');
            $.ajax({
                url: "{{ route('generate_appointment_letter', ['id' => '__employeeId__']) }}".replace('__employeeId__', employeeId),
                method: 'GET',
                data: {
                    title: 'Appointment Letter',
                    content: 'This is a sample content for the PDF.'
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    const base64Pdf = response.base64Pdf;
                    const byteCharacters = atob(base64Pdf);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);
                    const blob = new Blob([byteArray], { type: 'application/pdf' });
                    const blobUrl = URL.createObjectURL(blob);
                    window.open(blobUrl, '_blank');
                },
                error: function (xhr) {
                    //json 
                    alert(xhr.responseJSON.error);
                    console.log(xhr.responseText);
                }
            });
        }
        function printanexture(employeeId) {
            // alert('sss');
            $.ajax({
                url: "{{ route('generate_anexture', ['id' => '__employeeId__']) }}".replace('__employeeId__', employeeId),
                method: 'GET',
                data: {
                    title: 'Anexture',
                    content: 'This is a sample content for the PDF.'
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    const base64Pdf = response.base64Pdf;
                    const byteCharacters = atob(base64Pdf);
                    const byteNumbers = new Array(byteCharacters.length);
                    for (let i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    const byteArray = new Uint8Array(byteNumbers);
                    const blob = new Blob([byteArray], { type: 'application/pdf' });
                    const blobUrl = URL.createObjectURL(blob);
                    window.open(blobUrl, '_blank');
                },
                error: function (xhr) {
                    alert(xhr.responseJSON.error);
                    console.log(xhr.responseText);
                }
            });
        }
    </script>


@endpush
@section('content')
{{-- @if(\Auth::user()->type == 'company') --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['salary_history'], 'method' => 'GET', 'id' => 'employee_submit']) }}
                        <div class="row d-flex justify-content-end ">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label'])}}
                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'onchange' => 'branchtype(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('employee_submit').submit(); return false;"
                                     data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('salary_history') }}" class="btn mx-1 btn-sm btn-outline-danger" 
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                {{-- <button type="submit" name="export"  title="excel" value="excel" class="btn mx-1 btn-sm btn-outline-warning"><span class="btn-inner--icon"><i
                                    class="fas fa-print"></i></span></button> --}}
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
{{-- @endif --}}

<table class="datatable">
    <thead>
        <tr class="table_heads">
            <th>Sr.</th>
            <th>{{__('Branch') }}</th>
            <th>{{__('Name')}}</th>
            <th>{{__('FatherName')}}</th>
            <th>{{__('Designation') }}</th>
            <th>{{__('Scale') }}</th>
            <th>{{__('Effect From') }}</th>
            <th> {{__('Gross')}}</th>
            <th> {{__('Net')}}</th>
            <th>{{__('Action')}}</th>
        </tr>
    </thead>
    <tbody>
        {{-- @dd($employeesscale->toArray()) --}}
        @foreach ($employeesscale as $scale)
                {{-- @dd($employeesscale); --}}
                {{-- @php
                    $lastPayscaleDetail = $employee->employee_payscale_details->last();
                @endphp --}}
                {{-- @foreach ($lastPayscaleDetail as $payscaleemp) --}}
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    @if(@$scale->employee->branch_id)
                        <td class="font-style">
                            {{!empty(\Auth::user()->getBranch(@$scale->employee->branch_id)) ? \Auth::user()->getBranch(@$scale->employee->branch_id)->name : ''}}
                        </td>
                    @else
                        <td>-</td>
                    @endif

                    <td class="font-style">{{ @$scale->employee->name }}</td>
                    <td class="font-style">{{ @$scale->employee->f_name }}</td>
                    @if(@$scale->employee->designation_id)
                        <td class="font-style">
                            {{!empty(\Auth::user()->getDesignation(@$scale->employee->designation_id)) ? \Auth::user()->getDesignation(@$scale->employee->designation_id)->name : ''}}
                        </td>
                    @else
                        <td>-</td>
                    @endif

                    <td class="font-style">{{!empty($scale->scale) ? $scale->scale->scale_no : '' }}
                    </td>
                    <td class="font-style">
                        {{!empty($scale->scale) ? $scale->scale->effect_from : '' }}
                    </td>
                    <td class="font-style">
                        {{!empty($scale) ? $scale->net + $scale->emp_sec : '0' }}
                    </td>
                    <td class="font-style">{{!empty($scale) ? $scale->net : '' }}</td>
                    <td>
                        <div class="action-btn">
                            <a href="#!" data-size="lg" data-url="{{route('salary_history_detail', @$scale->employee->id)}}"
                                data-ajax-popup="true" class="mx-1 btn mx-1 btn-sm btn-outline-primary"
                                data-bs-toggle="tooltip" 
                                data-bs-title="{{__('Employee salary Scale History')}}"><span class="btn-inner--icon"><i
                                        class="ti ti-eye "></i></span></a>
                            <a class="btn mx-1 btn-sm btn-outline-success"
                                onclick="printappointmentletter('{{$scale->id}}')" data-bs-toggle="tooltip" data-bs-title="appointment letter"><span class="btn-inner--icon"><i
                                        class="fas fa-print"></i></span></a>
                            <a class="btn mx-1 btn-sm btn-outline-warning"
                                data-bs-toggle="tooltip"
                                 data-bs-title="print" onclick="printanexture('{{@$scale->employee->id}}')"><span class="btn-inner--icon"><i
                                        class="fas fa-print"></i></span></a>
                            <!-- <a class="btn mx-1 btn-sm btn-outline-success"
                                                onclick="printappointmentletter('{{@$scale->employee->id}}')"><span class="btn-inner--icon"><i
                                                        class="fas fa-print"></i></span></a> -->
                        </div>
                    </td>
                </tr>
                {{-- @endforeach --}}
        @endforeach
    </tbody>
</table>

  {{-- @if ($employeesscale->hasPages())
    <div class="pagination">
        <ul>
            @if ($employeesscale->onFirstPage())
                <li class="disabled">&laquo; Previous</li>
            @else
                <li><a href="{{ $employeesscale->appends(request()->query())->previousPageUrl() }}"
                        rel="prev">&laquo; Previous</a></li>
            @endif
            @if ($employeesscale->currentPage() > 1)
                <li><a href="{{ $employeesscale->appends(request()->query())->url(1) }}">First</a></li>
            @endif
            @php
                $currentPage = $employeesscale->currentPage();
                $lastPage = $employeesscale->lastPage();
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
                <li class="{{ $page == $employeesscale->currentPage() ? 'active' : '' }}">
                    <a href="{{ $employeesscale->appends(request()->query())->url($page) }}">{{ $page }}</a>
                </li>
            @endfor
            @if ($employeesscale->hasMorePages())
                <li><a href="{{ $employeesscale->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                        &raquo;</a></li>
            @else
                <li class="disabled">Next &raquo;</li>
            @endif
            @if ($employeesscale->currentPage() < $employeesscale->lastPage())
                <li><a
                        href="{{ $employeesscale->appends(request()->query())->url($employeesscale->lastPage()) }}">Last</a>
                </li>
            @endif
        </ul>
    </div>
@endif --}}

@endsection
