@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Termination') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Termination') }}</li>
@endsection
@push('script-page')
    <script src="{{ asset('js/ckeditor.js') }}"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/45.2.1/translations/en.umd.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5-premium-features/45.2.1/translations/en.umd.js"></script>@endpush
@section('action-btn')
    <div class="float-end">
        @can('create termination')
            <a href="#" data-url="{{ route('termination.create') }}" data-size="lg" data-ajax-popup="true"
                 data-bs-title="{{ __('Create New Termination') }}"
                class="btn mx-1 btn-sm btn-outline-primary">
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
                        <div class="card-body filter_change">
                            {{ Form::open(['route' => ['termination.index'], 'method' => 'GET', 'id' => 'termination_submit']) }}
                            <div class="row d-flex justify-content-end ">

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'onchange' => 'branchtype(this.value)']) }}
                                    </div>
                                </div>

                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                        onclick="document.getElementById('termination_submit').submit(); return false;"
                                         data-bs-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('termination.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
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

    <table class="datatable">
        <thead>
            <tr class="table_heads">
                <th>#</th>
                @role('company')
                    <th>{{ __('Employee Name') }}</th>
                @endrole
                <th>{{ __('Termination Type') }}</th>
                <th>{{ __('Notice Date') }}</th>
                <th>{{ __('Termination Date') }}</th>
                <th>{{ __('Description') }}</th>
                @if (Gate::check('edit termination') || Gate::check('delete termination'))
                    <th>{{ __('Action') }}</th>
                @endif
            </tr>
        </thead>
        <tbody class="font-style">
            @foreach ($terminations as $termination)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    @role('company')
                        <td>{{ !empty($termination->employee()) ? $termination->employee()->name : '' }}</td>
                    @endrole

                    <td>{{ !empty($termination->terminationType()) ? $termination->terminationType()->name : '' }}</td>
                    <td>{{ \Auth::user()->dateFormat($termination->notice_date) }}</td>
                    <td>{{ \Auth::user()->dateFormat($termination->termination_date) }}</td>
                    <td>
                        <a href="#" class="action-item"
                            data-url="{{ route('termination.description', $termination->id) }}" data-ajax-popup="true"
                             data-bs-title="{{ __('Desciption') }}" data-bs-toggle="{{ __('Desciption') }}"><i
                                class="fa fa-comment text-dark"></i></a>
                    </td>
                    @if (Gate::check('edit termination') || Gate::check('delete termination'))
                        <td>
                            <span>
                                @can('edit termination')
                                    <div class="action-btn ms-2">
                                        <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center"
                                            data-url="{{ URL::to('termination/' . $termination->id . '/edit') }}"
                                            data-size="lg" data-ajax-popup="true"
                                             data-bs-title="{{ __('Edit Termination') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                            {{-- <i class="ti ti-pencil text-white"></i>     --}}
                                        </a>
                                    @endcan

                                    @can('delete termination')
                                        {!! Form::open([
                                            'method' => 'DELETE',
                                            'route' => ['termination.destroy', $termination->id],
                                            'id' => 'delete-form-' . $termination->id,
                                        ]) !!}
                                        <a href="#"
                                            class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"
                                             data-bs-title="{{ __('Delete') }}"
                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                            data-confirm-yes="document.getElementById('delete-form-{{ $termination->id }}').submit();">
                                            <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                        </a>
                                        {!! Form::close() !!}
                                    </div>
                                @endcan
                            </span>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- @if ($terminations->hasPages())
        <div class="pagination">
            <ul>
                @if ($terminations->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $terminations->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($terminations->currentPage() > 1)
                    <li><a href="{{ $terminations->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $terminations->currentPage();
                    $lastPage = $terminations->lastPage();
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
                    <li class="{{ $page == $terminations->currentPage() ? 'active' : '' }}">
                        <a href="{{ $terminations->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($terminations->hasMorePages())
                    <li><a href="{{ $terminations->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($terminations->currentPage() < $terminations->lastPage())
                    <li><a
                            href="{{ $terminations->appends(request()->query())->url($terminations->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif --}}
@endsection
