@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Competencies') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Competencies') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        {{-- @can('Create Competencies') --}}
        <a href="#" data-url="{{ route('competencies.create') }}" data-ajax-popup="true"
            data-title="{{ __('Create New Competencies') }}"  title="{{ __('Create') }}"
            class="btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">Create</span>
        </a>

        {{-- @endcan --}}
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
                                {{ Form::open(['route' => ['competencies.index'], 'method' => 'GET', 'id' => 'competencies_submit']) }}
                                <div class="row d-flex justify-content-end ">
                
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                            {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
                                        </div>                               
                                    </div>
        
                                    <div class="col-auto float-end ms-2 mt-4">
                                        <a href="#" class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('competencies_submit').submit(); return false;"
                                             data-bs-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>
                                        <a href="{{ route('competencies.index') }}" class="btn btn-sm btn-danger" 
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
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th width="200px">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="font-style">
                    @foreach ($competencies as $competency)
                        <tr>
                            <td>{{ $competency->name }}</td>
                            <td>{{ !empty($competency->performance) ? $competency->performance->name : '' }}</td>



                            <td class="Action">
                                <span>
                                    {{-- @can('edit document type') --}}
                                    <div class="action-btn ms-2">
                                        <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center"
                                            data-url="{{ URL::to('competencies/' . $competency->id . '/edit') }}"
                                            data-ajax-popup="true" title="{{ __('Edit Competencies') }}"
                                             data-bs-title="{{ __('Edit') }}"
                                            >
                                            <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                            {{-- <i class="ti ti-pencil text-white"></i> --}}
                                        </a>
                                        {{-- </div> --}}
                                        {{-- @endcan --}}

                                        {{-- @can('Delete Competencies') --}}
                                        {{-- <div class="action-btn bg-danger ms-2"> --}}
                                        {!! Form::open([
                                            'method' => 'DELETE',
                                            'route' => ['competencies.destroy', $competency->id],
                                            'id' => 'delete-form-' . $competency->id,
                                        ]) !!}
                                        <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger  align-items-center bs-pass-para"
                                             data-bs-title="{{ __('Delete') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                            {{-- <i class="ti ti-trash text-white"></i> --}}
                                            </a>
                                        {!! Form::close() !!}
                                    </div>
                                    {{-- @endcan --}}

                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
        @if ($competencies->hasPages())
        <div class="pagination">
            <ul>
                @if ($competencies->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $competencies->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($competencies->currentPage() > 1)
                    <li><a href="{{ $competencies->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $competencies->currentPage();
                    $lastPage = $competencies->lastPage();
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
                    <li class="{{ $page == $competencies->currentPage() ? 'active' : '' }}">
                        <a href="{{ $competencies->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($competencies->hasMorePages())
                    <li><a href="{{ $competencies->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($competencies->currentPage() < $competencies->lastPage())
                    <li><a
                            href="{{ $competencies->appends(request()->query())->url($competencies->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif
    </div>
@endsection
