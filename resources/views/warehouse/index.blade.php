@extends('layouts.admin')
@section('page-title')
    {{ __('Store') }}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Store') }}</li>
@endsection
@section('action-btn')
    @can('create warehouse')
    <div class="float-end">
        <a href="#" data-size="lg" data-url="{{ route('store.create') }}" data-ajax-popup="true"
            data-bs-title="{{ __('Create') }}" data-bs-toggle="{{ __('Create Store') }}"
            class="btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">Create</span>
        </a>

    </div>
    @endcan
@endsection

@section('content')
    <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => ['store.index'], 'method' => 'GET', 'id' => 'store_submit']) }}
            <div class="row d-flex justify-content-start">

                {{-- Branches Dropdown --}}
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branch', __('Branches'), ['class' => 'form-label']) }}
                        {{ Form::select('branch', $branches, request('branch'), ['class' => 'form-control select custom-select', 'id' => 'branch']) }}
                    </div>
                </div>
                {{-- Action Buttons --}}
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <!-- Search Button -->
                    <a href="#" class="btn btn-sm btn-outline-primary"
                        onclick="document.getElementById('store_submit').submit(); return false;" data-bs-title="Search">
                        <span class="btn-inner--icon">Search</span>
                    </a>

                    <!-- Export Dropdown -->
                    {{-- <div class="dropdown">
                        <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                            id="reportActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Export
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="reportActionsDropdown">
                            <li>
                                <button class="dropdown-item" type="submit" name="export" value="excel">
                                    <i class="ti ti-file me-2"></i>Excel
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item" type="button" onclick="startPdfExport()">
                                    <i class="ti ti-download me-2"></i>Pdf
                                </button>
                            </li>
                        </ul>
                    </div> --}}
                </div>

            </div>
            {{ Form::close() }}
        </div>
    </div>


    <div class="row">
        <div class="col-xl-12">
            <div class="table-responsive">
                <table class="datatable">
                    <thead class="table_heads">
                        <tr>
                            <th>{{ __('S.No.') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Address') }}</th>
                            <th>{{ __('Branch') }}</th>
                            <th>{{ __('City') }}</th>
                            <th>{{ __('Zip Code') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($warehouses as $warehouse)
                            <tr class="font-style">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $warehouse->name }}</td>
                                <td>{{ $warehouse->address }}</td>
                                <td>{{ $warehouse->branch->name ?? '' }}</td>
                                <td>{{ $warehouse->city }}</td>
                                <td>{{ $warehouse->city_zip }}</td>

                                @if (Gate::check('show warehouse') || Gate::check('edit warehouse') || Gate::check('delete warehouse'))
                                    <td class="Action">
                                        <div class="action-btn ms-2">
                                            @can('show warehouse')
                                                <a href="{{ route('store.show', $warehouse->id) }}"
                                                    class="mx-1 btn btn-outline-info btn-sm  align-items-center"><span
                                                        class="btn-inner--icon"><i class="ti ti-eye"></i></span></a>
                                            @endcan
                                            @can('edit warehouse')
                                                <a data-url="{{ route('store.edit', $warehouse->id) }}" data-ajax-popup="true"
                                                    class="mx-1 btn btn-outline-info btn-sm  align-items-center" data-size="lg "
                                                    data-bs-title="{{ __('Edit') }}"
                                                    data-bs-toggle="{{ __('Edit Store') }}">
                                                    <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                                </a>
                                            @endcan
                                            {{-- @can('delete warehouse')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['store.destroy', $warehouse->id],'id'=>'delete-form-'.$warehouse->id]) !!}
                                                    <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" ><i class="ti ti-trash text-white"></i></a>
                                                    {!! Form::close() !!}
                                                @endcan --}}
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
@endsection
