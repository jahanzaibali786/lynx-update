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
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>{{ __('Stock Transfer Note No') }}:</strong>
                            <div>{{ AUth::user()->invoiceNumberFormat($invoice->invoice_id) }}</div>
                        </div>
                        <div class="col-md-4">
                            <strong>{{ __('Store From') }}:</strong>
                            <div>{{ $branch->name ?? '-' }}</div>
                        </div>
                        <div class="col-md-4">
                            <strong>{{ __('Store To') }}:</strong>
                            <div>{{ $store->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong>{{ __('Issue Date') }}:</strong>
                            <div>{{ \Auth::user()->dateFormat($invoice->issue_date) }}</div>
                        </div>
                        <div class="col-md-4">
                            <strong>{{ __('Due Date') }}:</strong>
                            <div>{{ \Auth::user()->dateFormat($invoice->due_date) }}</div>
                        </div>
                        <div class="col-md-4">
                            <strong>{{ __('Status') }}:</strong>
                            <div>{{ __(\App\Models\StockTransferNote::$statues[$invoice->status] ?? '-') }}</div>
                        </div>
                        <div class="col-md-4 mt-3">
                            <strong>{{ __('Session') }}:</strong>
                            <div>{{ $invoice->academicSession->year ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mb-3">
                        @can('show stock transfer note')
                            <a href="{{ route('stock-transfer-note.print', Crypt::encrypt($invoice->id)) }}"
                                class="btn btn-sm btn-outline-secondary" target="_blank"
                                data-bs-title="{{ __('Print') }}">
                                <i class="ti ti-printer"></i> {{ __('Print') }}
                            </a>
                        @endcan

                        @can('forward stock transfer note')
                            @if (in_array($invoice->status, [\App\Models\StockTransferNote::STATUS_DRAFT, \App\Models\StockTransferNote::STATUS_REJECTED], true))
                                <button type="button"
                                    class="btn btn-sm btn-outline-warning stn-workflow-action"
                                    data-url="{{ route('stock-transfer-note.forward-to-ho', Crypt::encrypt($invoice->id)) }}"
                                    data-confirm="{{ __('Send this Stock Transfer Note for approval?') }}">
                                    <i class="ti ti-send"></i> {{ __('Send for Approval') }}
                                </button>
                            @endif
                        @endcan

                        @can('approve stock transfer note')
                            @if ($invoice->status === \App\Models\StockTransferNote::STATUS_SENT_FOR_APPROVAL)
                                <button type="button"
                                    class="btn btn-sm btn-outline-success stn-workflow-action"
                                    data-url="{{ route('stock-transfer-note.approve-by-ho', Crypt::encrypt($invoice->id)) }}"
                                    data-confirm="{{ __('Approve this note and transfer its stock now?') }}">
                                    <i class="ti ti-check"></i> {{ __('HO Approve') }}
                                </button>
                            @endif
                        @endcan

                        @can('reject stock transfer note')
                            @if ($invoice->status === \App\Models\StockTransferNote::STATUS_SENT_FOR_APPROVAL)
                                <button type="button"
                                    class="btn btn-sm btn-outline-danger stn-workflow-action"
                                    data-url="{{ route('stock-transfer-note.reject-by-ho', Crypt::encrypt($invoice->id)) }}"
                                    data-confirm="{{ __('Reject this Stock Transfer Note?') }}">
                                    <i class="ti ti-x"></i> {{ __('HO Reject') }}
                                </button>
                            @endif
                        @endcan

                        @can('issue stock transfer note')
                            @if ($invoice->status === \App\Models\StockTransferNote::STATUS_APPROVED)
                                <button type="button"
                                    class="btn btn-sm btn-outline-primary stn-workflow-action"
                                    data-url="{{ route('stock-transfer-note.issue', Crypt::encrypt($invoice->id)) }}"
                                    data-confirm="{{ __('Issue this approved Stock Transfer Note?') }}">
                                    <i class="ti ti-package-export"></i> {{ __('Issue') }}
                                </button>
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
                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                    data-confirm-yes="document.getElementById('show-delete-form-{{ $invoice->id }}').submit();">
                                    <i class="ti ti-trash"></i> {{ __('Delete') }}
                                </button>
                                {!! Form::close() !!}
                            @endif
                        @endcan
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th class="text-end">{{ __('Price') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th class="text-end">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($iteams as $item)
                                    <tr>
                                        <td>{{ $item->product->name ?? '-' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($item->price) }}</td>
                                        <td>{{ ucfirst($item->type ?? 'new') }}</td>
                                        <td class="text-end">{{ \Auth::user()->priceFormat($item->price * $item->quantity) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>{{ __('Total') }}</strong></td>
                                    <td class="text-end"><strong>{{ \Auth::user()->priceFormat($invoice->getTotal()) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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
