@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Employee EOBI') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee EOBI') }}</li>
@endsection
@section('content')
    <div class="card-body full-card">
        <div class="table-responsive">
            @if (!$emps->isEmpty())
                <table class="datatable">
                    <thead class="">
                        <tr class="table_heads">
                            <th>#</th>
                            <th>{{ __('Employee') }}</th>
                            <th>{{ __('Eobi') }}</th>
                            <th>{{ __('Eobi Employer') }}</th>
                            <th>{{ __('Pessi') }}</th>
                            <th>{{ __('Pessi Employer') }}</th>
                            @if (\Auth::user()->type != 'Employee')
                                <th>{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($emps as $emp)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $emp->name }}</td>
                                <td>{{ @$emp->eobi }}</td>
                                <td>{{ @$emp->eobi_employer }}</td>
                                <td>{{ @$emp->pessi }}</td>
                                <td>{{ @$emp->pessi_employer }}</td>
                                @if (\Auth::user()->type != 'Employee')
                                    <td class="row">
                                        <div class="action-btn  ms-3">
                                            @can('edit loan')
                                                <a href="#" data-url="{{ URL::to('emp-eobi-allocation/' . $emp->id . '/edit') }}"
                                                    data-size="md" data-ajax-popup="true"
                                                    data-bs-title="{{ __('Edit Employee Leave') }}"
                                                    class="mx-1 btn btn-sm  btn-outline-primary align-items-center"
                                                    
                                                    title="{{ __('Edit') }}"><span class="btn-inner--icon">
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

    {{-- @if ($emps->hasPages())
        <div class="pagination">
            <ul>
                @if ($emps->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $emps->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($emps->currentPage() > 1)
                    <li><a href="{{ $emps->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $emps->currentPage();
                    $lastPage = $emps->lastPage();
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
                    <li class="{{ $page == $emps->currentPage() ? 'active' : '' }}">
                        <a href="{{ $emps->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($emps->hasMorePages())
                    <li><a href="{{ $emps->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($emps->currentPage() < $emps->lastPage())
                    <li><a
                            href="{{ $emps->appends(request()->query())->url($emps->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif --}}

@endsection
