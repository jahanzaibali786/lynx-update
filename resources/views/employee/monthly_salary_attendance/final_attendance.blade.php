@extends('layouts.admin')
@section('page-title')
{{__('Final Attendance')}}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Final Attendance')}}</li>
@endsection
@push('script-page')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
     document.getElementById('check-all').addEventListener('change', function (event) {
            var checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = event.target.checked;
            });
        });
        document.getElementById('adm-finalize-btn').addEventListener('click', function (event) {
            event.preventDefault();
            var checkedRows = [];
            var checkboxes = document.querySelectorAll('.row-checkbox:checked');
            checkboxes.forEach(function (checkbox) {
                checkedRows.push(checkbox.value);
            });

            if (checkedRows.length > 0) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ route('finalize_adm_attendance') }}",
                    type: "POST",
                    data: { rows: checkedRows },
                    success: function (result) {
                        if (result.success) {
                        // alert(result.message);
                        Swal.fire({
                            icon: 'success',
                            title: 'Attendance Finalized',
                            text: result.message,
                            confirmButtonText: 'OK',
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        // alert(result.message);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: result.message,
                            confirmButtonText: 'OK',
                        }).then(() => {
                            window.location.reload();
                        });
                        }
                    }
                });
            } else {
                // alert('No rows selected');
                Swal.fire({
                    icon: 'warning',
                    title: 'No Rows Selected',
                    text: 'Please select at least one row to finalize.',
                    confirmButtonText: 'OK',
                });
            }
        });
        document.getElementById('adm-unfinalize-btn').addEventListener('click', function (event) {
            event.preventDefault();
            var checkedRows = [];
            var checkboxes = document.querySelectorAll('.row-checkbox:checked');
            checkboxes.forEach(function (checkbox) {
                checkedRows.push(checkbox.value);
            });

            if (checkedRows.length > 0) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ route('unfinalize_adm_attendance') }}",
                    type: "POST",
                    data: { rows: checkedRows },
                    success: function (result) {
                        if (result.success) {
                            // alert(result.message);
                            Swal.fire({
                                icon: 'success',
                                title: 'Attendance UnFinalized',
                                text: result.message,
                                confirmButtonText: 'OK',
                            }).then(() => {
                                window.location.reload();
                            });
                            // window.location.reload();
                        } else {
                            // alert(result.error);
                            // window.location.reload();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: result.error,
                                confirmButtonText: 'OK',
                            }).then(() => {
                                window.location.reload();
                            });
                        }
                    }
                });
            } else {
                // alert('No rows selected');
                Swal.fire({
                    icon: 'warning',
                    title: 'No Rows Selected',
                    text: 'Please select at least one row to unfinalize.',
                    confirmButtonText: 'OK',
                });
            }
        });
</script>
@endpush
@section('content')
{{-- @if(\Auth::user()->type == 'company') --}}
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2" id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['final_attendance'], 'method' => 'GET', 'id' => 'employee_submit']) }}
                    <div class="row d-flex justify-content-end">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'), ['class' => 'form-label'])}}
                                {{ Form::select('branches', $branchesList, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('department_id', __('Department'), ['class' => 'form-label'])}}
                                {{ Form::select('department_id', $departments, isset($_GET['department_id']) ? $_GET['department_id'] : '', ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label'])}}
                                {{ Form::select('designation_id', $designations, isset($_GET['designation_id']) ? $_GET['designation_id'] : '', ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('date', __('Date'), ['class' => 'form-label'])}}
                                {{ Form::date('date', $date ?? now()->format('Y-m-d'), ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-auto float-end ms-2 mt-4">
                            <a id="adm-finalize-btn" href="#" class="btn mx-1 btn-sm btn-outline-warning"  data-bs-title="Finalize">
                                <span class="btn-inner--icon">Finalize</span>
                            </a>
                    <a id="adm-unfinalize-btn" href="#" class="btn mx-1 btn-sm btn-outline-warning"  data-bs-title="UnFinalize / RollBack">
                                <span class="btn-inner--icon">UnFinalize / RollBack</span>
                            </a>
                        <a href="#" class="btn mx-1 btn-sm btn-outline-primary" onclick="document.getElementById('employee_submit').submit(); return false;"  data-bs-title="{{ __('Apply') }}">
                            <span class="btn-inner--icon">Search</span>
                        </a>
                        <a href="{{ route('final_attendance') }}" class="btn mx-1 btn-sm btn-outline-danger"  data-bs-title="{{ __('Reset') }}">
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
{{-- @endif --}}
@if($datas->isNotEmpty())
<div class="table-responsive">
    <table class="">
        <thead>
            <tr class="table_heads">
                <th>#</th>
                <th>{{__('Branch')}}</th>
                <th>{{__('Name')}}</th>
                <th>{{__('Sal. Month')}}</th>
                <th>{{__('Sal. Days')}}</th>
                <th>{{__('Leave')}}</th>
                <th >{{__('Absents')}}</th>
                <th>{{__('GmFinal')}}</th>
                <th>{{__('SalFinal')}}</th>
                <th>{{__('Fwd to Admin')}}</th>
                <th><input type="checkbox" id="check-all"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datas as $data)
            <tr style="color:
            @if (isset($data->gm_final) && trim(strtolower($data->gm_final)) == 1)
                green
            @elseif (isset($data->sal_final) && trim(strtolower($data->sal_final)) == 1)
                red
            @elseif (isset($data->adm_final) && trim(strtolower($data->adm_final)) == 1)
                red
            @elseif (isset($data->accountant_finalize) && trim(strtolower($data->accountant_finalize)) == 1)
                blue
            @else
                red
            @endif">
                <td>{{ $loop->iteration }}</td>
                <td class="font-style">{{ !empty($data) ? $data->employee->user->name : '' }}</td>
                <td class="font-style">{{ !empty($data) ? $data->employee->name : '' }}</td>
                <td>{{ !empty($data) ? date('M-y', strtotime($data->for_month_of)) : '' }}</td>
                <td>{{!empty($data) ? $data->working_days : '' }}</td>
                <td>{{ !empty($data) ? $data->leave : '' }}</td>
                <td>{{!empty($data) ? $data->absents : '' }}</td>
                <td><input type="checkbox" name="gmfinal" {{ !empty($data) && $data->gm_final == 1 ? 'checked' : '' }} disabled></td>
                <td><input type="checkbox" name="salfinal" {{ !empty($data) && $data->sal_final == 1 ? 'checked' : '' }} disabled></td>
                <td><input type="checkbox" name="admfinal" value="{{ $data->id }}" class="adm-checkbox" {{ !empty($data) && $data->adm_final == 1 ? 'checked' : '' }} {{ !empty($data) && $data->adm_final== 1 || $data->adm_final== 0 ? 'disabled' : '' }} ></td>
                <td><input type="checkbox" class="row-checkbox" value="{{ $data->id }}"></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
  {{-- @if ($datas->hasPages())
    <div class="pagination">
        <ul>
            @if ($datas->onFirstPage())
                <li class="disabled">&laquo; Previous</li>
            @else
                <li><a href="{{ $datas->appends(request()->query())->previousPageUrl() }}"
                        rel="prev">&laquo; Previous</a></li>
            @endif
            @if ($datas->currentPage() > 1)
                <li><a href="{{ $datas->appends(request()->query())->url(1) }}">First</a></li>
            @endif
            @php
                $currentPage = $datas->currentPage();
                $lastPage = $datas->lastPage();
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
                <li class="{{ $page == $datas->currentPage() ? 'active' : '' }}">
                    <a href="{{ $datas->appends(request()->query())->url($page) }}">{{ $page }}</a>
                </li>
            @endfor
            @if ($datas->hasMorePages())
                <li><a href="{{ $datas->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                        &raquo;</a></li>
            @else
                <li class="disabled">Next &raquo;</li>
            @endif
            @if ($datas->currentPage() < $datas->lastPage())
                <li><a
                        href="{{ $datas->appends(request()->query())->url($datas->lastPage()) }}">Last</a>
                </li>
            @endif
        </ul>
    </div>
@endif --}}
@endif
@endsection
