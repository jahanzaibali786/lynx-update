@extends('layouts.admin')
@section('page-title')
    {{ __('Daily Closings') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Daily Closing') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @if (Gate::check('manage bank account') || Gate::check('manage bank transfer'))
            <a href="#" data-url="{{ route('daily-closing.create') }}" data-ajax-popup="true" data-title="{{ __('Create Daily Closing') }}" data-size="xl" class="btn mx-1 btn-sm btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{ __('Create Daily Closing') }}">
                <span class="btn-inner--icon">{{ __('Create Daily Closing') }}</span>
            </a>
        @endif
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12 mb-4">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['daily-closing.index'], 'method' => 'GET', 'id' => 'daily_closing_submit']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-8 col-lg-8 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-md-6 col-sm-12">
                                    <div class="btn-box">
                                        {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('from_date', isset($_GET['from_date']) ? $_GET['from_date'] : '', ['class' => 'form-control', 'placeholder' => 'Select From Date']) }}
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-12">
                                    <div class="btn-box">
                                        {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('to_date', isset($_GET['to_date']) ? $_GET['to_date'] : '', ['class' => 'form-control', 'placeholder' => 'Select To Date']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto d-flex gap-1">
                                    <a href="#" class="btn btn-sm btn-outline-primary"
                                        onclick="document.getElementById('daily_closing_submit').submit(); return false;" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{ __('Search') }}">
                                        <span class="btn-inner--icon">{{ __('Search') }}</span>
                                    </a>
                                    <a href="{{ route('daily-closing.index') }}" class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="{{ __('Clear Search Filters') }}">
                                        <span class="btn-inner--icon">{{ __('Clear') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>

        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table align-items-center table-bordered">
                            <thead class="table_heads">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('From Date') }}</th>
                                    <th>{{ __('To Date') }}</th>
                                    <th>{{ __('Deposit Date') }}</th>
                                    <th>{{ __('Total Received') }}</th>
                                    <th>{{ __('Total Deposited') }}</th>
                                    <th>{{ __('Difference') }}</th>
                                    <th>{{ __('Issued By') }}</th>
                                    <th>{{ __('Received By') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th width="15%" class="text-end">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($closings as $index => $closing)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $closing->from_date->format('d-M-Y') }}</td>
                                        <td>{{ $closing->to_date->format('d-M-Y') }}</td>
                                        <td>{{ $closing->deposit_date ? $closing->deposit_date->format('d-M-Y') : '-' }}</td>
                                        <td>{{ \Auth::user()->priceFormat($closing->total_income_received) }}</td>
                                        <td>{{ \Auth::user()->priceFormat($closing->total_income_deposited) }}</td>
                                        <td>
                                            @if($closing->difference > 0)
                                                <span class="text-danger font-weight-bold">({{ \Auth::user()->priceFormat(abs($closing->difference)) }})</span>
                                            @elseif($closing->difference < 0)
                                                <span class="text-success font-weight-bold">{{ \Auth::user()->priceFormat(abs($closing->difference)) }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ $closing->issued_by ?? '-' }}</td>
                                        <td>{{ $closing->received_by ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-status p-2 px-3 rounded-pill bg-{{ $closing->status === 'approved' ? 'success' : 'warning' }}" id="status-badge-{{ $closing->id }}">
                                                {{ ucfirst($closing->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end font-style">
                                            <a href="{{ route('daily-closing.show', $closing->id) }}" target="_blank"
                                               class="btn btn-sm btn-outline-info align-items-center"
                                               data-bs-toggle="tooltip" data-bs-title="{{ __('View/Print') }}">
                                                <span class="btn-inner--icon"><i class="ti ti-eye"></i></span>
                                            </a>
                                            
                                            @if(\Auth::user()->type == 'company')
                                                <a href="#" class="btn btn-sm btn-outline-success align-items-center toggle-approval"
                                                   data-id="{{ $closing->id }}" data-url="{{ route('daily-closing.approve', $closing->id) }}"
                                                   data-bs-toggle="tooltip" data-bs-title="{{ __('Toggle Approval') }}">
                                                    <span class="btn-inner--icon"><i class="ti ti-circle-check"></i></span>
                                                    
                                                </a>
                                            @endif

                                            @if($closing->status !== 'approved')
                                                <a href="#" class="btn btn-sm btn-outline-primary align-items-center"
                                                   data-url="{{ route('daily-closing.edit', $closing->id) }}" data-ajax-popup="true"
                                                   data-title="{{ __('Edit Daily Closing') }}" data-size="xl"
                                                   data-bs-toggle="tooltip" data-bs-title="{{ __('Edit') }}">
                                                    <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                                    
                                                </a>

                                                {!! Form::open(['method' => 'DELETE', 'route' => ['daily-closing.destroy', $closing->id], 'id' => 'delete-form-' . $closing->id, 'class' => 'd-inline']) !!}
                                                    <a href="#" class="btn btn-sm btn-outline-danger align-items-center bs-pass-para"
                                                       data-confirm="{{ __('Are You Sure?') }}" data-text="{{ __('This action cannot be undone. Do you want to continue?') }}"
                                                       data-confirm-yes="delete-form-{{ $closing->id }}"
                                                       data-bs-toggle="tooltip" data-bs-title="{{ __('Delete') }}">
                                                        <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                                    </a>
                                                {!! Form::close() !!}
                                            @endif
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
@endsection

@push('script-page')
    <script>
        $(document).on('click', '.toggle-approval', function(e) {
            e.preventDefault();
            var btn = $(this);
            var id = btn.data('id');
            var url = btn.data('url');
            
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        var badge = $('#status-badge-' + id);
                        badge.text(response.status.charAt(0).toUpperCase() + response.status.slice(1));
                        if (response.status === 'approved') {
                            badge.removeClass('bg-warning').addClass('bg-success');
                            show_toastr('Success', response.message, 'success');
                        } else {
                            badge.removeClass('bg-success').addClass('bg-warning');
                            show_toastr('Success', response.message, 'success');
                        }
                    } else {
                        show_toastr('Error', response.message || 'Something went wrong', 'error');
                    }
                },
                error: function(xhr) {
                    show_toastr('Error', 'Unable to toggle status', 'error');
                }
            });
        });
    </script>
@endpush
