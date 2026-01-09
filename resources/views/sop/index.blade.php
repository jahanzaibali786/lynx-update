@extends('layouts.admin')

@section('page-title')
    {{ __('Manage SOP') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('SOP') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create termination type')
            <a href="#" data-size="lg" data-url="{{ route('sops.create') }}" data-ajax-popup="true"
                  data-bs-title="{{ __('Create New Sops') }}"
                class="btn btn-sm btn-primary">
                Create
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
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="">
                                    <thead>
                                        <tr class="table_heads">
                                            <th>{{ __('Sr. No') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sops as $key => $sop)
                                            <tr>
                                                <td>{{ ++$key }}</td>
                                                <td>{{ $sop->sop_title }}</td>
                                                <td>{{ $sop->sop_type }}</td>
                                                <td>{{ Str::limit($sop->sop_description, 25, '....') }}</td>
                                                <td>
                                                    <div class="action-btn ms-2">

                                                    @can('edit termination type')
                                                        <a href="#" data-url="{{ route('sops.edit', $sop->id) }}"
                                                            data-ajax-popup="true" data-size="lg"
                                                            
                                                            data-bs-title="{{ __('Edit Sops') }}" class="btn mx-1 btn-sm btn-outline-primary">
                                                            <span class="btn-inner--icon">
                                                                <i class="ti ti-pencil"></i>
                                                            </span>
                                                        </a>
                                                    @endcan
                                                    {{-- // delete sop --}}
                                                    @can('delete termination type')
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['sops.destroy', $sop->id],
                                                            'id' => 'delete-form-' . $sop->id,
                                                        ]) !!}
                                                        <a href="#"
                                                            class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"
                                                             data-bs-title="{{ __('Delete') }}"
                                                            data-bs-title="{{ __('Delete') }}"
                                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{ $sop->id }}').submit();">
                                                            <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                                        </a>
                                                        {!! Form::close() !!}
                                                    @endcan
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
            </div>
        </div>
    </div>
@endsection
