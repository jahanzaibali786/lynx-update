@extends('layouts.admin')
@push('script-page')
@endpush
@section('page-title')
    {{ __('manage performance type') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block font-weight-400 mb-0 ">{{ __('Performance Type') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Performance Type') }}</li>
@endsection




@section('action-btn')
    <div class="float-end">
        <a href="#" data-url="{{ route('performanceType.create') }}" data-ajax-popup="true"
             data-bs-title="{{ __('Create') }}"
            class="btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">Create</span>
        </a>
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
                                {{ Form::open(['route' => ['performanceType.index'], 'method' => 'GET', 'id' => 'performanceType_submit']) }}
                                <div class="row d-flex justify-content-end ">
                
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                            {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
                                        </div>                               
                                    </div>
        
                                    <div class="col-auto float-end ms-2 mt-4">
                                        <a href="#" class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('performanceType_submit').submit(); return false;"
                                             data-bs-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>
                                        <a href="{{ route('performanceType.index') }}" class="btn btn-sm btn-danger" 
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
            @endif --}}
            <div class="">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="">
                            <thead>
                                <tr class="table_heads">
                                    <th scope="col">{{ __('Name') }}</th>
                                    <th scope="col" class="">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @foreach ($types as $type)
                                    <tr class="font-style">
                                        <td>{{ $type->name }}</td>

                                        <td class="">

                                            <div class="action-btn ms-2">
                                                <a href="#" data-url="{{ route('performanceType.edit', $type->id) }}"
                                                    data-ajax-popup="true"
                                                    class="mx-1 btn-outline-primary btn btn-sm align-items-center"
                                                     data-bs-title="{{ __('Edit') }}">
                                                    <span class="btn-inner--icon"><i class="ti ti-pencil "></i></span>
                                                </a>
                                                {!! Form::open([
                                                    'method' => 'DELETE',
                                                    'route' => ['performanceType.destroy', $type->id],
                                                    'id' => 'delete-form-' . $type->id,
                                                ]) !!}
                                                <a href="#!"
                                                    class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"
                                                     data-bs-title="{{ __('Delete') }}"
                                                    data-bs-title="{{ __('Delete') }}"
                                                    data-confirm="Are You Sure?|This action can not be undone. Do you want to continue?"
                                                    data-confirm-yes="document.getElementById('delete-form-{{ $type->id }}').submit();">
                                                    <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                                </a>
                                                {!! Form::close() !!}
                                            </div>

                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
        @if ($types->hasPages())
        <div class="pagination">
            <ul>
                @if ($types->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $types->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($types->currentPage() > 1)
                    <li><a href="{{ $types->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $types->currentPage();
                    $lastPage = $types->lastPage();
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
                    <li class="{{ $page == $types->currentPage() ? 'active' : '' }}">
                        <a href="{{ $types->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($types->hasMorePages())
                    <li><a href="{{ $types->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($types->currentPage() < $types->lastPage())
                    <li><a
                            href="{{ $types->appends(request()->query())->url($types->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif
    </div>
@endsection
