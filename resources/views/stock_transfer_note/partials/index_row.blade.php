<tr data-stn-row-id="{{ $invoice->id }}">
    @php
        $branchName = optional(optional($invoice->toStore)->branch)->name
            ?? optional(optional($invoice->fromStore)->branch)->name
            ?? '-';
        $className = $invoice->class_names;
    @endphp
    <td class="stn-row-number">{{ $rowNumber ?? '-' }}</td>

    <td class="Id">
        <a
            href="{{ route('stock-transfer-note.show', Crypt::encrypt($invoice->id)) }}"
            class="btn btn-outline-primary btnpurchase1"
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="{{ __('View Stock Transfer Note') }}"
        >
            {{ Auth::user()->invoiceNumberFormat($invoice->invoice_id) }}
        </a>
    </td>

    <td>{{ $branchName }}</td>
    <td>{{ $className ?: '-' }}</td>
    <td>{{ optional($invoice->fromStore)->name }}</td>
    <td>{{ optional($invoice->toStore)->name }}</td>
    <td>{{ Auth::user()->dateFormat($invoice->issue_date) }}</td>

    <td>
        @if ($invoice->due_date < date('Y-m-d'))
            <p class="text-danger mb-0">
                {{ Auth::user()->dateFormat($invoice->due_date) }}
            </p>
        @else
            {{ Auth::user()->dateFormat($invoice->due_date) }}
        @endif
    </td>

    <td>{{ Auth::user()->priceFormat($invoice->getDue()) }}</td>

    <td>
        @if ($invoice->status == 0)
            <span class="status_badge badge bg-secondary p-2 px-3 rounded">
                {{ __(App\Models\StockTransferNote::$statues[$invoice->status]) }}
            </span>
        @elseif ($invoice->status == 1)
            <span class="status_badge badge bg-warning p-2 px-3 rounded">
                {{ __(App\Models\StockTransferNote::$statues[$invoice->status]) }}
            </span>
        @elseif ($invoice->status == 2)
            <span class="status_badge badge bg-danger p-2 px-3 rounded">
                {{ __(App\Models\StockTransferNote::$statues[$invoice->status]) }}
            </span>
        @elseif ($invoice->status == 3)
            <span class="status_badge badge bg-info p-2 px-3 rounded">
                {{ __(App\Models\StockTransferNote::$statues[$invoice->status]) }}
            </span>
        @elseif ($invoice->status == 4)
            <span class="status_badge badge bg-primary p-2 px-3 rounded">
                {{ __(App\Models\StockTransferNote::$statues[$invoice->status]) }}
            </span>
        @elseif ($invoice->status == 5)
            <span class="status_badge badge bg-success p-2 px-3 rounded">
                {{ __(App\Models\StockTransferNote::$statues[$invoice->status]) }}
            </span>
        @elseif ($invoice->status == 6)
            <span class="status_badge badge bg-danger p-2 px-3 rounded">
                {{ __(App\Models\StockTransferNote::$statues[$invoice->status]) }}
            </span>
        @endif
    </td>

    @if (Gate::any(['edit stock transfer note', 'delete stock transfer note', 'show stock transfer note', 'forward stock transfer note', 'approve stock transfer note', 'reject stock transfer note', 'issue stock transfer note']))
        <td class="Action">
            @php
                $invoiceID = Crypt::encrypt($invoice->id);
            @endphp

            <div class="d-inline-flex align-items-center gap-1">
                @can('forward stock transfer note')
                    @if (in_array($invoice->status, [App\Models\StockTransferNote::STATUS_DRAFT, App\Models\StockTransferNote::STATUS_REJECTED], true))
                        <a
                            href="#"
                            class="btn btn-sm btn-outline-warning stn-workflow-action"
                            data-url="{{ route('stock-transfer-note.forward-to-ho', $invoiceID) }}"
                            data-confirm-message="{{ __('Send this Stock Transfer Note for approval?') }}"
                            data-bs-title="{{ __('Send for Approval') }}"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="{{ __('Send for Approval') }}"
                        ><span class="btn-inner--icon">
                            <i class="ti ti-send text-white"></i></span>
                        </a>
                    @endif
                @endcan

                @can('approve stock transfer note')
                    @if ($invoice->status === App\Models\StockTransferNote::STATUS_SENT_FOR_APPROVAL)
                        <a
                            href="#"
                            class="btn btn-sm btn-outline-success stn-workflow-action"
                            data-url="{{ route('stock-transfer-note.approve-by-ho', $invoiceID) }}"
                            data-confirm-message="{{ __('Approve this note and transfer its stock?') }}"
                            data-bs-title="{{ __('HO Approve') }}"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="{{ __('HO Approve') }}"
                        ><span class="btn-inner--icon">
                            <i class="ti ti-check"></i></span>
                        </a>
                    @endif
                @endcan

                @can('reject stock transfer note')
                    @if ($invoice->status === App\Models\StockTransferNote::STATUS_SENT_FOR_APPROVAL)
                        <a
                            href="#"
                            class="btn btn-sm btn-outline-danger stn-workflow-action"
                            data-url="{{ route('stock-transfer-note.reject-by-ho', $invoiceID) }}"
                            data-confirm-message="{{ __('Reject this Stock Transfer Note?') }}"
                            data-bs-title="{{ __('HO Reject') }}"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="{{ __('HO Reject') }}"
                        ><span class="btn-inner--icon">
                            <i class="ti ti-x"></i></span>
                        </a>
                    @endif
                @endcan

                @can('issue stock transfer note')
                    @if ($invoice->status === App\Models\StockTransferNote::STATUS_APPROVED)
                        <a
                            href="#"
                            class="btn btn-sm btn-outline-primary stn-workflow-action"
                            data-url="{{ route('stock-transfer-note.issue', $invoiceID) }}"
                            data-confirm-message="{{ __('Issue this Stock Transfer Note?') }}"
                            data-bs-title="{{ __('Issue') }}"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="{{ __('Issue') }}"
                        ><span class="btn-inner--icon">
                            <i class="ti ti-truck-delivery"></i></span>
                        </a>
                    @endif
                @endcan

                @can('edit stock transfer note')
                    @if (in_array($invoice->status, [App\Models\StockTransferNote::STATUS_DRAFT, App\Models\StockTransferNote::STATUS_REJECTED], true))
                    <a
                        href="#"
                        data-url="{{ route('stock-transfer-note.edit', $invoiceID) }}"
                        data-size="modal-fullscreen"
                        data-ajax-popup="true"
                        class="btn btn-sm btn-outline-primary align-items-center"
                        data-bs-title="{{ __('Edit') }}"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ __('Edit') }}"
                    ><span class="btn-inner--icon">
                        <i class="ti ti-pencil"></i></span>
                    </a>
                    @endif
                @endcan

                @can('show stock transfer note')
                    <a
                        href="{{ route('stock-transfer-note.print', $invoiceID) }}"
                        class="btn btn-sm btn-outline-secondary"
                        target="_blank"
                        data-bs-title="{{ __('Print') }}"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ __('Print') }}"
                    >
                        <span class="btn-inner--icon">
                            <i class="ti ti-printer"></i>
                        </span>
                    </a>

                    <a
                        href="{{ route('stock-transfer-note.show', $invoiceID) }}"
                        class="btn btn-sm btn-outline-info"
                        data-bs-title="{{ __('Show') }}"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="{{ __('Show') }}"
                    >
                        <span class="btn-inner--icon">
                            <i class="fas fa-eye"></i>
                        </span>
                    </a>
                @endcan

                @can('delete stock transfer note')
                    @if (in_array($invoice->status, [App\Models\StockTransferNote::STATUS_DRAFT, App\Models\StockTransferNote::STATUS_REJECTED], true))
                    <div class="action-btn bg-outline-danger">
                        {!! Form::open([
                            'method' => 'DELETE',
                            'route' => ['stock-transfer-note.destroy', $invoice->id],
                            'id' => 'delete-form-' . $invoice->id,
                        ]) !!}

                        <a
                            href="#"
                            class="mx-3 btn btn-sm align-items-center bs-pass-para"
                            data-bs-title="{{ __('Delete') }}"
                            data-bs-toggle="tooltip"
                            data-bs-placement="top"
                            title="{{ __('Delete') }}"
                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                            data-confirm-yes="document.getElementById('delete-form-{{ $invoice->id }}').submit();"
                        >
                            <i class="ti ti-trash"></i>
                        </a>

                        {!! Form::close() !!}
                    </div>
                    @endif
                @endcan
            </div>
        </td>
    @endif
</tr>
