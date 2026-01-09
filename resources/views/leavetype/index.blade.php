@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Leave Type') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Leave Type') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create leave type')
            <a href="#" data-url="{{ route('leavetype.create') }}" data-ajax-popup="true"
                 data-bs-title="{{ __('Create') }}"
                class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon"> Create</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            {{-- @if (\Auth::user()->type == 'company')
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2 " id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body">
                            {{ Form::open(['route' => ['leavetype.index'], 'method' => 'GET', 'id' =>
                            'leavetype_submit']) }}
                            <div class="row d-flex justify-content-end ">

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ?
                                        $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' =>
                                        'branchtype(this.value)']) }}
                                    </div>
                                </div>

                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn btn-sm btn-primary"
                                        onclick="document.getElementById('leavetype_submit').submit(); return false;"
                                         data-bs-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('leavetype.index') }}" class="btn btn-sm btn-danger"
                                         data-bs-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon"><i
                                                class="ti ti-trash-off text-white-off"></i></span>
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
        --}}
            <table class="">
                <thead>
                    <tr class="table_heads">
                        <th>#</th>
                        <th>{{ __('Leave Type') }}</th>
                        <th>{{ __('Days / Year') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th width="200px">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="font-style">
                    @foreach ($leavetypes as $leavetype)
                        <tr>
                            <td>{{ ($leavetypes->currentPage() - 1) * $leavetypes->perPage() + $loop->iteration }}</td>
                            <td>{{ $leavetype->title }}</td>
                            <td>{{ $leavetype->days }}</td>
                            <td>{{ @$leavetype->status }}</td>

                            <td class="Action">
                                <span>
                                    <div class="action-btn ms-2">
                                        @can('edit leave type')
                                            <a href="#" class="mx-3 btn mx-1 btn-sm btn-outline-primary align-items-center"
                                                data-url="{{ URL::to('leavetype/' . $leavetype->id . '/edit') }}"
                                                data-ajax-popup="true"
                                                
                                                data-bs-title="{{ __('Edit') }}">
                                                <span class="btn-inner--icon"> <i class="ti ti-pencil"></i></span>
                                            </a>
                                        @endcan

                                        @can('delete leave type')
                                            {!! Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['leavetype.destroy', $leavetype->id],
                                                'id' => 'delete-form-' . $leavetype->id,
                                            ]) !!}
                                            <a href="#"
                                                class="mx-3 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"
                                                 data-bs-title="{{ __('Delete') }}"
                                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                data-confirm-yes="document.getElementById('delete-form-{{ $leavetype->id }}').submit();">
                                                <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                            </a>
                                            {!! Form::close() !!}
                                        @endcan
                                    </div>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($leavetypes->hasPages())
            <div class="pagination">
                <ul>
                    @if ($leavetypes->onFirstPage())
                        <li class="disabled">&laquo; Previous</li>
                    @else
                        <li><a href="{{ $leavetypes->appends(request()->query())->previousPageUrl() }}"
                                rel="prev">&laquo; Previous</a></li>
                    @endif
                    @if ($leavetypes->currentPage() > 1)
                        <li><a href="{{ $leavetypes->appends(request()->query())->url(1) }}">First</a></li>
                    @endif
                    @php
                        $currentPage = $leavetypes->currentPage();
                        $lastPage = $leavetypes->lastPage();
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
                        <li class="{{ $page == $leavetypes->currentPage() ? 'active' : '' }}">
                            <a href="{{ $leavetypes->appends(request()->query())->url($page) }}">{{ $page }}</a>
                        </li>
                    @endfor
                    @if ($leavetypes->hasMorePages())
                        <li><a href="{{ $leavetypes->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                                &raquo;</a></li>
                    @else
                        <li class="disabled">Next &raquo;</li>
                    @endif
                    @if ($leavetypes->currentPage() < $leavetypes->lastPage())
                        <li><a
                                href="{{ $leavetypes->appends(request()->query())->url($leavetypes->lastPage()) }}">Last</a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif
    </div>

@endsection
