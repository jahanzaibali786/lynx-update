@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Employee Scale Heads') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Employee Scale Heads') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create trainer')
            <a href="#" data-size="md" data-url="{{ route('employee-scale-heads.create') }}" data-ajax-popup="true"
                 data-bs-title="{{ __('Create') }}" data-bs-toggle="{{ __('Create Scale Heads') }}"
                class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="table-responsive">
        <table class="">
            <thead>
                <tr class="table_heads">
                    <th>{{ __('#') }}</th>
                    <th>{{ __('Salary Head') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th style="width: 75px">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody class="font-style">
                @foreach ($employee_scales_heads as $scale)
                    <tr>
                        {{-- @dd($scale->initial_basic) --}}
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ !empty(@$scale->head) ? $scale->head : '' }}</td>
                        <td>{{ !empty(@$scale->status == '1') ? 'Active' : 'In-Active' }}</td>

                        @if (Gate::check('edit trainer') || Gate::check('delete trainer') || Gate::check('show trainer'))
                            <td style="width: 75px">
                                <div class="action-btn ms-2">
                                    @can('edit trainer')
                                        <a href="#" data-url="{{ route('employee-scale-heads.edit', $scale->id) }}"
                                            data-size="md" data-ajax-popup="true"
                                            class="mx-1 btn mx-1 btn-sm btn-outline-primary"
                                            data-bs-toggle="tooltip" 
                                            data-bs-title="{{ __('Edit') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                        </a>
                                    @endcan

                                    @can('delete trainer')
                                        @if (\Auth::user()->type == 'company')
                                            {!! Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['employee-scale-heads.destroy', $scale->id],
                                                'id' => 'delete-form-' . $scale->id,
                                            ]) !!}

                                            <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger bs-pass-para"
                                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                data-confirm-yes="document.getElementById('delete-form-{{ $scale->id }}').submit();"
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
    </div>
    @if ($employee_scales_heads->hasPages())
    <div class="pagination">
        <ul>
            @if ($employee_scales_heads->onFirstPage())
                <li class="disabled">&laquo; Previous</li>
            @else
                <li><a href="{{ $employee_scales_heads->appends(request()->query())->previousPageUrl() }}"
                        rel="prev">&laquo; Previous</a></li>
            @endif
            @if ($employee_scales_heads->currentPage() > 1)
                <li><a href="{{ $employee_scales_heads->appends(request()->query())->url(1) }}">First</a></li>
            @endif
            @php
                $currentPage = $employee_scales_heads->currentPage();
                $lastPage = $employee_scales_heads->lastPage();
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
                <li class="{{ $page == $employee_scales_heads->currentPage() ? 'active' : '' }}">
                    <a href="{{ $employee_scales_heads->appends(request()->query())->url($page) }}">{{ $page }}</a>
                </li>
            @endfor
            @if ($employee_scales_heads->hasMorePages())
                <li><a href="{{ $employee_scales_heads->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                        &raquo;</a></li>
            @else
                <li class="disabled">Next &raquo;</li>
            @endif
            @if ($employee_scales_heads->currentPage() < $employee_scales_heads->lastPage())
                <li><a
                        href="{{ $employee_scales_heads->appends(request()->query())->url($employee_scales_heads->lastPage()) }}">Last</a>
                </li>
            @endif
        </ul>
    </div>
@endif
@endsection
