@extends('layouts.admin')
@section('page-title')
    {{ __('Bank Balance Transfer') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Bank Balance Transfer') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        {{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1"  data-bs-title="{{__('Filter')}}"> --}}
        {{--            Filters --}}
        {{--        </a> --}}
        @can('create bank transfer')
            <a href="#" data-url="{{ route('bank-transfer.create') }}" data-ajax-popup="true"
                data-title="{{ __('Create Bank-Transfer') }}" class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class=" mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['bank-transfer.index'], 'method' => 'GET', 'id' => 'transfer_form']) }}
                        <input type="hidden" name="export" id="is_export" value="0">
                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">

                                    <div class="col-3">
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 month">
                                        <div class="btn-box">
                                            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
                                            {{ Form::text('date', isset($_GET['date']) ? $_GET['date'] : null, ['class' => 'form-control month-btn', 'id' => 'pc-daterangepicker-1', 'readonly']) }}

                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 date">
                                        <div class="btn-box">
                                            {{ Form::label('f_account', __('From Account'), ['class' => 'form-label']) }}
                                            {{ Form::select('f_account', $account, isset($_GET['f_account']) ? $_GET['f_account'] : '', ['class' => 'form-control select custom-select']) }}
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('t_account', __('To Account'), ['class' => 'form-label']) }}
                                            {{ Form::select('t_account', $account, isset($_GET['t_account']) ? $_GET['t_account'] : '', ['class' => 'form-control select custom-select']) }}
                                        </div>
                                    </div>


                                </div>
                            </div>
                            <div class="col-auto mt-4">
                                <div class="row">
                                    <div class="col-auto">

                                        <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                            onclick="document.getElementById('transfer_form').submit(); return false;"
                                            data-bs-title="{{ __('Apply') }}" data-bs-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>

                                        <a href="{{ route('bank-transfer.index') }}"
                                            class="btn mx-1 btn-sm btn-outline-danger " title="{{ __('Reset') }}"
                                            data-bs-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon">Clear</span>
                                        </a>
                                        <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                            onclick="document.getElementById('is_export').value='1'; document.getElementById('transfer_form').submit(); setTimeout(() => { document.getElementById('is_export').value='0'; }, 500); return false;"
                                            data-bs-toggle="tooltip" title="{{ __('Export') }}">
                                            <span class="btn-inner--icon">Export</span>
                                        </a>


                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <table class="datatable">
        <thead>
            <tr class="table_heads">
                <th>#</th>
                <th> {{ __('Date') }}</th>
                <th> {{ __('From Account') }}</th>
                <th> {{ __('To Account') }}</th>
                <th> {{ __('Amount') }}</th>
                <th> {{ __('Reference') }}</th>
                <th> {{ __('Description') }}</th>
                @if (Gate::check('edit transfer') || Gate::check('delete transfer'))
                    <th width="10%"> {{ __('Action') }}</th>
                @endif
            </tr>
        </thead>

        <tbody>
            @foreach ($transfers as $transfer)
                <tr class="font-style">
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Auth::user()->dateFormat($transfer->date) }}</td>
                    <td>{{ !empty($transfer->fromBankAccount()) ? $transfer->fromBankAccount()->bank_name . ' (' . $transfer->fromBankAccount()->holder_name . ')' : '' }}
                    </td>
                    <td>{{ !empty($transfer->toBankAccount()) ? $transfer->toBankAccount()->bank_name . ' (' . $transfer->toBankAccount()->holder_name . ')' : '' }}
                    </td>
                    <td>{{ \Auth::user()->priceFormat($transfer->amount) }}</td>
                    <td>{{ $transfer->reference }}</td>
                    <td>{{ $transfer->description }}</td>
                    @if (Gate::check('edit transfer') || Gate::check('delete transfer'))
                        <td class="Action">
                            <span>

                                <div class="action-btn">
                                    {{-- @can('view transfer') --}}
                                    <a href="{{ route('bank-transfer.show', $transfer->id) }}" target="_blank" class="mx-1 btn btn-sm align-items-center bg-info"
                                        data-bs-title="{{ __('View') }}" data-bs-toggle="{{ __('View Transfer') }}"
                                        data-bs-title="{{ __('View') }}">
                                        <i class="ti ti-eye text-white"></i>
                                    </a>
                                    {{-- @endcan --}}
                                    @can('edit transfer')
                                        <a href="#" class="mx-1 btn btn-sm align-items-center bg-primary"
                                            data-url="{{ route('bank-transfer.edit', $transfer->id) }}" data-ajax-popup="true"
                                            data-bs-title="{{ __('Edit') }}" data-bs-toggle="{{ __('Edit Transfer') }}"
                                            data-bs-title="{{ __('Edit') }}">
                                            <i class="ti ti-pencil text-white"></i>
                                        </a>
                                    @endcan
                                    @can('delete transfer')
                                        {!! Form::open([
                                            'method' => 'DELETE',
                                            'route' => ['bank-transfer.destroy', $transfer->id],
                                            'id' => 'delete-form-' . $transfer->id,
                                        ]) !!}

                                        <a href="#" class=" btn btn-sm align-items-center bs-pass-para btn-danger"
                                            data-bs-title="{{ __('Delete') }}" data-bs-title="{{ __('Delete') }}"
                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                            data-confirm-yes="document.getElementById('delete-form-{{ $transfer->id }}').submit();">
                                            <i class="ti ti-trash text-white text-white text-white"></i>
                                        </a>
                                        {!! Form::close() !!}
                                    @endcan
                                </div>
                            </span>
                        </td>
                    @endif
                </tr>
            @endforeach

        </tbody>
    </table>
@endsection
