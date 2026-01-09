@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Classes') }}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('All Classes') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        {{-- @can('create session') --}}
        <a href="#" data-size="md" data-url="{{ route('classes.create') }}" data-ajax-popup="true" 
            data-bs-title="{{ __('Create') }}" class="btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">Create</span>
        </a>
        {{-- @endcan --}}
    </div>
@endsection
@section('content')
    {{-- @if (\Auth::user()->type == 'company') --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2 " id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body filter_change">
                            {{ Form::open(['route' => ['classes.index'], 'method' => 'GET', 'id' => 'classes_submit']) }}
                            <div class="row d-flex justify-content-end ">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>

                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                        onclick="document.getElementById('classes_submit').submit(); return false;"
                                         data-bs-title="{{ __('Apply') }}"
                                        data-bs-title="{{ __('Search') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('classes.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                         data-bs-title="{{ __('Clear') }}"
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
    {{-- @endif --}}
    <div class="table-responsive">
        <table class="datatable">
            <thead class="table_heads">
                <tr>
                    <th>#</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Branch') }}</th>
                    <th>{{ __('Section') }}</th>
                    <th width="200px" style="text-align: center;">{{ __('Action') }}</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($classes as $class)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            {{ !empty($class->name) ? $class->name : '-' }}
                        </td>
                        <td>
                            {{ !empty($class->branch) ? $class->branch->name : '-' }}
                        </td>
                        <td>
                            @foreach (@$class->classSectionAll as $section)
                                {{ $section->name }} ,
                            @endforeach
                        </td>

                        {{-- @if (Gate::check('edit session') || Gate::check('delete session')) --}}
                        <td>
                            <div class="action-btn ms-5">
                                {{-- @can('edit session') --}}

                                <a href="{{ route('class_fee', $class->id) }}"
                                    class="mx-1 btn mx-1 btn-sm btn-outline-success"
                                    data-bs-whatever="{{ __('View Class Wise Fee') }}" 
                                    data-bs-title="{{ __('View Class Wise Fee') }}"> <span class="btn-inner--icon"><i
                                            class="ti ti-eye"></i></span></a>

                                <a href="#!"data-url="{{ route('classes.edit', $class->id) }}" data-ajax-popup="true"
                                    class="mx-1 btn mx-1 btn-sm btn-outline-primary" 
                                    data-bs-title="{{ __('Edit') }}" data-bs-title="{{ __('Edit') }}"><span
                                        class="btn-inner--icon"><i class="ti ti-pencil"></i></span></a>

                                {{-- @endcan
                                @can('delete section') --}}
                                {!! Form::open([
                                    'method' => 'DELETE',
                                    'route' => ['classes.destroy', $class->id],
                                    'id' => 'delete-form-' . $class->id,
                                ]) !!}
                                <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger bs-pass-para"
                                     data-bs-title="{{ __('Delete') }}"
                                    data-bs-title="{{ __('Delete') }}"
                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                    data-confirm-yes="document.getElementById('delete-form-{{ $class->id }}').submit();"><span
                                        class="btn-inner--icon"><i class="ti ti-trash "></i></span></a>
                                {!! Form::close() !!}
                                {{-- @endcan --}}
                            </div>
                        </td>
                        {{-- @endif --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
