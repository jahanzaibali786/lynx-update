@extends('layouts.admin')
@section('page-title')
    {{ __('Stock Transfer Note Detail') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock-transfer-note.index') }}">{{ __('Stock Transfer Note') }}</a></li>
    <li class="breadcrumb-item">{{ AUth::user()->invoiceNumberFormat($invoice->invoice_id) }}</li>
@endsection

@section('content')
    @php
        $groupedItems = collect($iteams)->groupBy(function ($item) {
            return !empty($item->study_pack_id) ? 'group_' . $item->study_pack_id : 'item_' . $item->id;
        });
    @endphp
    <style>
        .stn-show-card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 10px 28px rgba(30, 64, 175, 0.10);
        }

        .stn-show-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .stn-show-summary-item {
            border: 1px solid #d9e8ff;
            border-radius: 12px;
            padding: 14px 16px;
            background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
        }

        .stn-show-summary-label {
            font-size: 12px;
            font-weight: 700;
            color: #4b6aa6;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 6px;
        }

        .stn-show-summary-value {
            font-size: 15px;
            font-weight: 700;
            color: #12336f;
        }

        .stn-show-actions {
            margin-bottom: 20px;
        }

        .stn-show-table {
            margin-bottom: 0;
        }

        .stn-show-table thead th {
            background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%);
            color: #fff;
            border-color: #1d4ed8;
            font-size: 12px;
            white-space: nowrap;
            padding-top: 12px;
            padding-bottom: 12px;
        }

        .stn-show-table tbody td {
            vertical-align: middle;
            padding-top: 11px;
            padding-bottom: 11px;
        }

        .stn-group-header-row td {
            background: #eaf3ff;
            border-color: #d7e6ff;
            padding: 0;
        }

        .stn-group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            color: #12336f;
            background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
        }

        .stn-group-header-main {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stn-group-toggle {
            width: 32px;
            height: 32px;
            border: 1px solid #2563eb;
            border-radius: 8px;
            background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 6px 14px rgba(37, 99, 235, 0.22);
        }

        .stn-group-toggle i {
            color: #fff;
            font-size: 16px;
        }

        .stn-group-title {
            font-size: 15px;
            font-weight: 700;
            color: #12336f;
        }

        .stn-group-subtitle {
            font-size: 12px;
            font-weight: 600;
            color: #5f7fb8;
        }

        .stn-group-total {
            text-align: right;
        }

        .stn-group-total-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #5f7fb8;
        }

        .stn-group-total-value {
            font-size: 15px;
            font-weight: 800;
            color: #1d4ed8;
        }

        .stn-group-item-row td {
            background: #ffffff;
            border-bottom-color: #e7f0ff;
        }

        .stn-group-item-row.stn-group-collapsed {
            display: none;
        }

        .stn-product-name {
            font-weight: 600;
            color: #111827;
        }

        .stn-product-sku {
            font-size: 12px;
            color: #6a89bf;
        }

        .stn-show-table tfoot td {
            background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%);
            border-top: 2px solid #93c5fd;
            color: #12336f;
            font-size: 14px;
            padding-top: 14px;
            padding-bottom: 14px;
        }

        .stn-show-grand-total-label {
            font-weight: 800;
            letter-spacing: 0.02em;
            color: #1d4ed8;
        }

        .stn-show-grand-total-value {
            font-weight: 900;
            font-size: 16px;
            color: #0f2f6a;
        }

        .stn-show-actions .btn-outline-secondary,
        .stn-show-actions .btn-outline-warning,
        .stn-show-actions .btn-outline-success,
        .stn-show-actions .btn-outline-danger,
        .stn-show-actions .btn-outline-primary {
            border-width: 0;
            color: #fff;
        }

        .stn-show-actions .btn-outline-secondary {
            background: #475569;
        }

        .stn-show-actions .btn-outline-warning {
            background: #f59e0b;
        }

        .stn-show-actions .btn-outline-success {
            background: #10b981;
        }

        .stn-show-actions .btn-outline-danger {
            background: #ef4444;
        }

        .stn-show-actions .btn-outline-primary {
            background: #2563eb;
        }

        .stn-show-actions .btn-outline-secondary:hover,
        .stn-show-actions .btn-outline-warning:hover,
        .stn-show-actions .btn-outline-success:hover,
        .stn-show-actions .btn-outline-danger:hover,
        .stn-show-actions .btn-outline-primary:hover {
            color: #fff;
            filter: brightness(0.96);
        }
    </style>
    <div class="row">
        <div class="col-12">
            <div class="card stn-show-card">
                <div class="card-body">
                    <div class="stn-show-summary">
                        <div class="stn-show-summary-item">
                            <div class="stn-show-summary-label">{{ __('Stock Transfer Note No') }}</div>
                            <div class="stn-show-summary-value">{{ AUth::user()->invoiceNumberFormat($invoice->invoice_id) }}</div>
                        </div>
                        <div class="stn-show-summary-item">
                            <div class="stn-show-summary-label">{{ __('Store From') }}</div>
                            <div class="stn-show-summary-value">{{ $branch->name ?? '-' }}</div>
                        </div>
                        <div class="stn-show-summary-item">
                            <div class="stn-show-summary-label">{{ __('Store To') }}</div>
                            <div class="stn-show-summary-value">{{ $store->name ?? '-' }}</div>
                        </div>
                        <div class="stn-show-summary-item">
                            <div class="stn-show-summary-label">{{ __('Issue Date') }}</div>
                            <div class="stn-show-summary-value">{{ \Auth::user()->dateFormat($invoice->issue_date) }}</div>
                        </div>
                        <div class="stn-show-summary-item">
                            <div class="stn-show-summary-label">{{ __('Due Date') }}</div>
                            <div class="stn-show-summary-value">{{ \Auth::user()->dateFormat($invoice->due_date) }}</div>
                        </div>
                        <div class="stn-show-summary-item">
                            <div class="stn-show-summary-label">{{ __('Status') }}</div>
                            <div class="stn-show-summary-value">{{ __(\App\Models\StockTransferNote::$statues[$invoice->status] ?? '-') }}</div>
                        </div>
                        <div class="stn-show-summary-item">
                            <div class="stn-show-summary-label">{{ __('Session') }}</div>
                            <div class="stn-show-summary-value">{{ $invoice->academicSession->year ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 flex-wrap stn-show-actions">
                        @can('show stock transfer note')
                            <a href="{{ route('stock-transfer-note.print', Crypt::encrypt($invoice->id)) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank"
                                data-bs-title="{{ __('Print') }}"
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                title="{{ __('Print') }}">
                                <i class="ti ti-printer text-white" ></i> {{ __('Print') }}
                            </a>
                        @endcan

                        @can('forward stock transfer note')
                            @if (in_array($invoice->status, [\App\Models\StockTransferNote::STATUS_DRAFT, \App\Models\StockTransferNote::STATUS_REJECTED], true))
                                <a href="#"
                                    class="btn btn-sm btn-outline-warning stn-workflow-action"
                                    data-url="{{ route('stock-transfer-note.forward-to-ho', Crypt::encrypt($invoice->id)) }}"
                                    data-confirm="{{ __('Send this Stock Transfer Note for approval?') }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ __('Send for Approval') }}">
                                    <i class="ti ti-send  text-white"></i> {{ __('Send for Approval') }}
                                </a>
                            @endif
                        @endcan

                        @can('approve stock transfer note')
                            @if ($invoice->status === \App\Models\StockTransferNote::STATUS_SENT_FOR_APPROVAL)
                                <a href="#"
                                    class="btn btn-sm btn-outline-success stn-workflow-action"
                                    data-url="{{ route('stock-transfer-note.approve-by-ho', Crypt::encrypt($invoice->id)) }}"
                                    data-confirm="{{ __('Approve this note and transfer its stock now?') }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ __('HO Approve') }}">
                                    <i class="ti ti-check  text-white"></i> {{ __('HO Approve') }}
                                </a>
                            @endif
                        @endcan

                        @can('reject stock transfer note')
                            @if ($invoice->status === \App\Models\StockTransferNote::STATUS_SENT_FOR_APPROVAL)
                                <a href="#"
                                    class="btn btn-sm btn-outline-danger stn-workflow-action"
                                    data-url="{{ route('stock-transfer-note.reject-by-ho', Crypt::encrypt($invoice->id)) }}"
                                    data-confirm="{{ __('Reject this Stock Transfer Note?') }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ __('HO Reject') }}">
                                    <i class="ti ti-x"></i> {{ __('HO Reject') }}
                                </a>
                            @endif
                        @endcan

                        @can('issue stock transfer note')
                            @if ($invoice->status === \App\Models\StockTransferNote::STATUS_APPROVED)
                                <a href="#"
                                    class="btn btn-sm btn-outline-primary stn-workflow-action"
                                    data-url="{{ route('stock-transfer-note.issue', Crypt::encrypt($invoice->id)) }}"
                                    data-confirm="{{ __('Issue this approved Stock Transfer Note?') }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ __('Issue') }}">
                                    <i class="ti ti-package-export"></i> {{ __('Issue') }}
                                </a>
                            @endif
                        @endcan

                        @can('delete stock transfer note')
                            @if (in_array($invoice->status, [\App\Models\StockTransferNote::STATUS_DRAFT, \App\Models\StockTransferNote::STATUS_REJECTED], true))
                                {!! Form::open([
                                    'method' => 'DELETE',
                                    'route' => ['stock-transfer-note.destroy', $invoice->id],
                                    'id' => 'show-delete-form-' . $invoice->id,
                                    'class' => 'd-inline',
                                ]) !!}
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger bs-pass-para"
                                    data-bs-title="{{ __('Delete') }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ __('Delete') }}"
                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                    data-confirm-yes="document.getElementById('show-delete-form-{{ $invoice->id }}').submit();">
                                    <i class="ti ti-trash text-white"></i> {{ __('Delete') }}
                                </button>
                                {!! Form::close() !!}
                            @endif
                        @endcan
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm stn-show-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th class="text-end">{{ __('Price') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groupedItems as $groupKey => $items)
                                    @php
                                        $firstItem = $items->first();
                                        $isStudyPackGroup = !empty($firstItem->study_pack_id);
                                        $groupTotal = $items->sum(function ($groupItem) {
                                            return (float) $groupItem->price * (float) $groupItem->quantity;
                                        });
                                        $groupDomId = 'stn-group-' . ($firstItem->study_pack_id ?: $firstItem->id);
                                    @endphp

                                    @if ($isStudyPackGroup)
                                        <tr class="stn-group-header-row">
                                            <td colspan="6">
                                                <div class="stn-group-header">
                                                    <div class="stn-group-header-main">
                                                        <button type="button" class="stn-group-toggle" data-group-toggle="{{ $groupDomId }}" aria-expanded="false">
                                                            <i class="ti ti-chevron-right"></i>
                                                        </button>
                                                        <div>
                                                            <div class="stn-group-title">{{ $firstItem->study_pack_title ?: __('Study Pack') }}</div>
                                                            <div class="stn-group-subtitle">{{ $items->count() }} {{ __('items') }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="stn-group-total">
                                                        <div class="stn-group-total-label">{{ __('Group Total') }}</div>
                                                        <div class="stn-group-total-value">{{ \Auth::user()->priceFormat($groupTotal) }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif

                                    @foreach ($items as $item)
                                        <tr class="{{ $isStudyPackGroup ? 'stn-group-item-row stn-group-collapsed' : '' }}" @if($isStudyPackGroup) data-group-item="{{ $groupDomId }}" @endif>
                                            <td>
                                                <div class="stn-product-name">{{ $item->product->name ?? '-' }}</div>
                                                @if (!empty($item->product->sku))
                                                    <div class="stn-product-sku">{{ $item->product->sku }}</div>
                                                @endif
                                            </td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ $item->description ?: '-' }}</td>
                                            <td class="text-end">{{ \Auth::user()->priceFormat($item->price) }}</td>
                                            <td>{{ ucfirst($item->type ?? 'new') }}</td>
                                            <td class="text-end">{{ \Auth::user()->priceFormat($item->price * $item->quantity) }}</td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end">
                                        <span class="stn-show-grand-total-label">{{ __('Grand Total') }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="stn-show-grand-total-value">{{ \Auth::user()->priceFormat($invoice->getTotal()) }}</span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function initStockTransferNoteShowTooltips(scope) {
            if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
                return;
            }

            var elements = (scope || document).querySelectorAll('[data-bs-toggle="tooltip"]');
            elements.forEach(function (element) {
                var instance = bootstrap.Tooltip.getInstance(element);
                if (instance) {
                    instance.dispose();
                }
                new bootstrap.Tooltip(element);
            });
        }

        $(document).ready(function () {
            initStockTransferNoteShowTooltips(document);
        });

        $(document)
            .off('click.stockTransferNoteGroupToggle', '.stn-group-toggle')
            .on('click.stockTransferNoteGroupToggle', '.stn-group-toggle', function (event) {
                event.preventDefault();

                var $button = $(this);
                var groupId = $button.data('group-toggle');
                var isExpanded = $button.attr('aria-expanded') === 'true';

                $('[data-group-item="' + groupId + '"]').toggleClass('stn-group-collapsed', isExpanded);
                $button.attr('aria-expanded', isExpanded ? 'false' : 'true');
                $button.find('i')
                    .toggleClass('ti-chevron-right', isExpanded)
                    .toggleClass('ti-chevron-down', !isExpanded);
            });

        $(document)
            .off('click.stockTransferNoteWorkflow', '.stn-workflow-action')
            .on('click.stockTransferNoteWorkflow', '.stn-workflow-action', function (event) {
                event.preventDefault();

                var $button = $(this);
                if (!confirm($button.data('confirm'))) {
                    return;
                }

                $button.prop('disabled', true);

                $.ajax({
                    url: $button.data('url'),
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                        if (response.success) {
                            if (typeof show_toastr === 'function') {
                                show_toastr('success', response.message, 'success');
                            }
                            if (typeof triggerContentAreaRefresh === 'function') {
                                triggerContentAreaRefresh(window.location.href);
                            }
                            return;
                        }

                        if (typeof show_toastr === 'function') {
                            show_toastr('error', response.message || @json(__('Unable to update Stock Transfer Note.')), 'error');
                        }
                    },
                    error: function (xhr) {
                        var response = xhr.responseJSON || {};
                        if (typeof show_toastr === 'function') {
                            show_toastr('error', response.message || @json(__('Unable to update Stock Transfer Note.')), 'error');
                        }
                    },
                    complete: function () {
                        $button.prop('disabled', false);
                    }
                });
            });
    </script>
@endsection
