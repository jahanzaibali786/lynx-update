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
                            <tbody id="daily-closing-table-body">
                                @foreach ($closings as $index => $closing)
                                    @include('dailyClosing.partials.row', ['closing' => $closing, 'index' => $index + 1])
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
        window.reindexDailyClosingRows = function() {
            $('#daily-closing-table-body tr').each(function(index) {
                $(this).find('.daily-closing-row-index').text(index + 1);
            });
        };

        window.refreshDailyClosingTooltips = function() {
            if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
                return;
            }

            $('#daily-closing-table-body [data-bs-toggle="tooltip"]').each(function() {
                var existingTooltip = bootstrap.Tooltip.getInstance(this);
                if (existingTooltip) {
                    existingTooltip.dispose();
                }
                new bootstrap.Tooltip(this);
            });
        };

        window.upsertDailyClosingRow = function(rowHtml, id, mode) {
            if (!rowHtml || !$('#daily-closing-table-body').length) {
                return;
            }

            var existingRow = $('[data-closing-row="' + id + '"]');
            if (existingRow.length) {
                existingRow.replaceWith(rowHtml);
            } else if (mode === 'prepend') {
                $('#daily-closing-table-body').prepend(rowHtml);
            }

            window.reindexDailyClosingRows();
            window.refreshDailyClosingTooltips();
        };

        $(document).off('click.dailyClosingApproval', '.toggle-approval');
        $(document).on('click.dailyClosingApproval', '.toggle-approval', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var btn = $(this);
            if (btn.data('request-running')) {
                return false;
            }

            var id = btn.data('id');
            var url = btn.data('url');
            var currentStatus = $.trim($('#status-badge-' + id).text()).toLowerCase();
            var desiredStatus = currentStatus === 'approved' ? 'pending' : 'approved';

            btn.data('request-running', true);
            btn.addClass('disabled').css('pointer-events', 'none');
            
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: desiredStatus
                },
                success: function(response) {
                    if (response.success) {
                        if (response.row && typeof window.upsertDailyClosingRow === 'function') {
                            window.upsertDailyClosingRow(response.row, response.id || id, 'replace');
                        } else {
                            var badge = $('#status-badge-' + id);
                            badge.text(response.status.charAt(0).toUpperCase() + response.status.slice(1));
                            if (response.status === 'approved') {
                                badge.removeClass('bg-warning').addClass('bg-success');
                                btn.closest('td').find('[data-ajax-popup="true"], .daily-closing-delete').remove();
                            } else {
                                badge.removeClass('bg-success').addClass('bg-warning');
                            }
                            btn.removeClass('disabled').css('pointer-events', '');
                        }
                        show_toastr('Success', response.message, 'success');
                    } else {
                        show_toastr('Error', response.message || 'Something went wrong', 'error');
                        btn.data('request-running', false);
                        btn.removeClass('disabled').css('pointer-events', '');
                    }
                },
                error: function(xhr) {
                    var message = (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)) || 'Unable to toggle status';
                    show_toastr('Error', message, 'error');
                    btn.data('request-running', false);
                    btn.removeClass('disabled').css('pointer-events', '');
                }
            });

            return false;
        });

        $(document).on('click', '.daily-closing-delete', function(e) {
            e.preventDefault();

            if (!confirm('{{ __('This action cannot be undone. Do you want to continue?') }}')) {
                return;
            }

            var btn = $(this);
            btn.addClass('disabled').css('pointer-events', 'none');

            $.ajax({
                url: btn.data('url'),
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function(response) {
                    if (response.success) {
                        $('[data-closing-row="' + btn.data('id') + '"]').remove();
                        window.reindexDailyClosingRows();
                        show_toastr('Success', response.message, 'success');
                    } else {
                        show_toastr('Error', response.message || response.error || 'Something went wrong', 'error');
                        btn.removeClass('disabled').css('pointer-events', '');
                    }
                },
                error: function(xhr) {
                    var message = (xhr.responseJSON && xhr.responseJSON.error) || 'Unable to delete daily closing';
                    show_toastr('Error', message, 'error');
                    btn.removeClass('disabled').css('pointer-events', '');
                }
            });
        });
    </script>
@endpush
