@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Leaves Allocations') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leaves Allocations') }}</li>
@endsection
@section('content')
    <div class="card-body full-card">
        <div class="table-responsive">
            @if (!$emp_leave->isEmpty())
                <table class="table datatable">
                    <thead class="">
                        <tr class="table_heads">
                            <th>#</th>
                            <th>{{ __('emp no') }}</th>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Casual Total') }}</th>
                            <th>{{ __('Casual Consumed') }}</th>
                            <th>{{ __('Annual Total') }}</th>
                            <th>{{ __('Annual Consumed') }}</th>
                            @if (\Auth::user()->type != 'Employee')
                                <th>{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($emp_leave as $lv)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @can('show employee profile')
                                        <a href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt(@$lv->emp_id)) }}"
                                            class="btn id-btn mx-1 btn-sm btn-outline-primary" data-bs-toggle="tooltip" data-bs-title="{{ __('Edit') }}"><span
                                                class="btn-inner--icon">{{ \Auth::user()->employeeIdFormat(@$lv->emp_no) }}</span></a>
                                    @else
                                        <a href="#" class="btn id-btn mx-1 btn-sm btn-outline-primary"><span
                                                class="btn-inner--icon">{{ \Auth::user()->employeeIdFormat(@$lv->emp_no) }}</span></a>
                                    @endcan
                                </td>
                                
                                <td>{{ @$lv->emp_name }}</td>
                                <td>{{ @$lv->casual_total }}</td>
                                <td>{{ @$lv->casual_consumed }}</td>
                                <td>{{ @$lv->annual_total }}</td>
                                <td>{{ @$lv->annual_consumed }}</td>
                                @if (\Auth::user()->type != 'Employee')
                                    <td class="row">
                                        <div class="action-btn  ms-3">
                                            @can('edit loan')
                                                <a href="#" data-url="{{ URL::to('emp-leaves/' . $lv->id . '/edit') }}"
                                                    data-size="lg" data-ajax-popup="true"
                                                    data-bs-title="{{ __('Edit Employee Leave') }}"
                                                    class="mx-1 btn btn-sm  btn-outline-primary align-items-center"
                                                    title="{{ __('Edit') }}" 
                                                    ><span class="btn-inner--icon">
                                                        <i class="ti ti-pencil"></i></span></a>
                                            @endcan

                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="mt-2 text-center">
                    No Leaves Allocated Yet!
                </div>
            @endif
        </div>
    </div>

    {{-- @if ($emp_leave->hasPages())
        <div class="pagination">
            <ul>
                @if ($emp_leave->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $emp_leave->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($emp_leave->currentPage() > 1)
                    <li><a href="{{ $emp_leave->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $emp_leave->currentPage();
                    $lastPage = $emp_leave->lastPage();
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
                    <li class="{{ $page == $emp_leave->currentPage() ? 'active' : '' }}">
                        <a href="{{ $emp_leave->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($emp_leave->hasMorePages())
                    <li><a href="{{ $emp_leave->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($emp_leave->currentPage() < $emp_leave->lastPage())
                    <li><a href="{{ $emp_leave->appends(request()->query())->url($emp_leave->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif --}}

@endsection
