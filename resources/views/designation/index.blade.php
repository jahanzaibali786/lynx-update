@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Designation') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Designation') }}</li>
@endsection


@section('action-btn')
    <div class="float-end">
        @can('create designation')
            <a href="#" data-size="lg" data-url="{{ route('designation.create') }}" data-ajax-popup="true"
                title="{{ __('Create New Designation') }}"  data-bs-title="{{ __('Create') }}"
                class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
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
                                {{ Form::open(['route' => ['designation.index'], 'method' => 'GET', 'id' => 'designation_submit']) }}
                                <div class="row d-flex justify-content-end ">

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                            {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
                                        </div>
                                    </div>

                                    <div class="col-auto float-end ms-2 mt-4">
                                        <a href="#" class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('designation_submit').submit(); return false;"
                                             data-bs-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>
                                        <a href="{{ route('designation.index') }}" class="btn btn-sm btn-danger" 
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

            <table class="">
                <thead>
                    <tr class="table_heads">
                        <th>#</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Designation') }}</th>
                        <th>{{ __('Ranks') }}</th>
                        <th>{{ __('Code') }}</th>
                        <th width="200px">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="font-style">
                    @foreach ($designations as $designation)
                        <tr>
                            <td>{{ ($designations->currentPage() - 1) * $designations->perPage() + $loop->iteration }}</td>
                            <td>{{ !empty($designation->department) ? @$designation->department->name : '' }}</td>
                            <td>{{ $designation->name }}</td>
                            <td>{{ $designation->rank }}</td>
                            <td>{{ $designation->code }}</td>

                            <td class="Action">
                                <span>
                                    <div class="action-btn ms-2">
                                        @can('edit designation')
                                        <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-primary" data-url="{{route('designation.edit',$designation->id) }}" data-ajax-popup="true" title="{{__('Edit Designation')}}"  data-bs-title="{{__('Edit')}}">
                                            <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                        </a>
                                        @endcan

                                        @can('delete designation')
                                            {!! Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['designation.destroy', $designation->id],
                                                'id' => 'delete-form-' . $designation->id,
                                            ]) !!}
                                            <a href="#" 
                                            class="mx-3 btn mx-1 btn-sm btn-outline-danger bs-pass-para" 
                                             
                                            data-bs-title="{{ __('Delete') }}" 
                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}" 
                                            data-confirm-yes="document.getElementById('delete-form-{{ $designation->id }}').submit();">
                                             <span class="btn-inner--icon">
                                                 <i class="ti ti-trash"></i>
                                             </span>
                                         </a>
                                         
                                            {!! Form::close() !!}
                                        @endcan

                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($designations->hasPages())
                <div class="pagination">
                    <ul>
                        @if ($designations->onFirstPage())
                            <li class="disabled">&laquo; Previous</li>
                        @else
                            <li><a href="{{ $designations->appends(request()->query())->previousPageUrl() }}"
                                    rel="prev">&laquo; Previous</a></li>
                        @endif
                        @if ($designations->currentPage() > 1)
                            <li><a href="{{ $designations->appends(request()->query())->url(1) }}">First</a></li>
                        @endif
                        @php
                            $currentPage = $designations->currentPage();
                            $lastPage = $designations->lastPage();
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
                            <li class="{{ $page == $designations->currentPage() ? 'active' : '' }}">
                                <a href="{{ $designations->appends(request()->query())->url($page) }}">{{ $page }}</a>
                            </li>
                        @endfor
                        @if ($designations->hasMorePages())
                            <li><a href="{{ $designations->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                                    &raquo;</a></li>
                        @else
                            <li class="disabled">Next &raquo;</li>
                        @endif
                        @if ($designations->currentPage() < $designations->lastPage())
                            <li><a
                                    href="{{ $designations->appends(request()->query())->url($designations->lastPage()) }}">Last</a>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif
        </div>

    </div>
@endsection
