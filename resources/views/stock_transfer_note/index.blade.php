@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Stock Transfer Notes') }}
@endsection

@push('script-page')
    <script>
        function copyToClipboard(element) {
            const copyText = element.id;

            navigator.clipboard.writeText(copyText);
            show_toastr('success', 'Url copied to clipboard', 'success');
        }
    </script>

    <script>
        function branchstore(id) {
            $.ajax({
                url: '{{ route('branch.store') }}',
                type: 'POST',
                data: {
                    branch_id: id,
                    _token: '{{ csrf_token() }}',
                },
                success: function (data) {
                    const storeSelect = $('#store');
                    storeSelect.empty();

                    for (let index = 0; index < data.length; index++) {
                        storeSelect.append(
                            '<option value="' + data[index].id + '">' +
                            data[index].name +
                            '</option>'
                        );
                    }
                },
            });
        }

        const branchStoreElement = document.getElementById('branchstore');

        if (branchStoreElement) {
            branchStoreElement.addEventListener('change', function () {
                branchstore(this.value);
            });
        }

        $(document)
            .off('click.stockTransferNoteWorkflow', '.stn-workflow-action')
            .on('click.stockTransferNoteWorkflow', '.stn-workflow-action', function (event) {
                event.preventDefault();

                const button = $(this);
                const message = button.data('confirm-message');

                if (message && !window.confirm(message)) {
                    return;
                }

                button.prop('disabled', true);

                $.ajax({
                    url: button.data('url'),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    headers: {
                        Accept: 'application/json',
                    },
                    success: function (response) {
                        show_toastr('success', response.message || @json(__('Stock Transfer Note updated successfully.')), 'success');
                        triggerContentAreaRefresh(window.location.href);
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON || {};
                        show_toastr('error', response.message || @json(__('Unable to update Stock Transfer Note.')), 'error');
                        button.prop('disabled', false);
                    },
                });
            });
    </script>
@endpush

@push('css-page')
    <style>
        .stock-transfer-note-page .card {
            border-radius: 10px;
        }

        .stock-transfer-note-page .form-label {
            margin-bottom: 4px;
            font-size: 12px;
        }

        .stock-transfer-note-page .table {
            width: 100%;
            margin-bottom: 0;
        }

        .stock-transfer-note-page .table thead tr.table_heads th {
            background: #11007f;
            color: #fff;
            border-color: #11007f;
            font-size: 12px;
            white-space: nowrap;
        }

        .stock-transfer-note-page .table tbody td {
            vertical-align: middle;
        }

        .stock-transfer-note-page .action-btn,
        .stock-transfer-note-page .stock-transfer-note-delete-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            padding: 0;
        }

        .stock-transfer-note-page .stock-transfer-note-delete-btn {
            border-color: #ff3d66;
            color: #ff3d66;
        }

        .stock-transfer-note-page .stock-transfer-note-delete-btn:hover {
            background: #ff3d66;
            color: #fff;
        }

        .stock-transfer-note-page .btn-sm {
            padding: 0.35rem 0.65rem;
        }

        .stock-transfer-note-page .select,
        .stock-transfer-note-page .form-control {
            min-height: 36px;
        }

        .stock-transfer-note-page .btn-box {
            margin-bottom: 0;
        }
    </style>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a>
    </li>
    <li class="breadcrumb-item">{{ __('Stock Transfer Note') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        {{--
        <a
            class="btn btn-sm btn-primary"
            data-bs-toggle="collapse"
            href="#multiCollapseExample1"
            role="button"
            aria-expanded="false"
            aria-controls="multiCollapseExample1"
            data-bs-title="{{ __('Filter') }}"
        >
            Filters
        </a>
        --}}

        {{--
        <a
            href="{{ route('stock-transfer-note.export') }}"
            class="btn mx-1 btn-sm btn-outline-primary"
            data-bs-title="{{ __('Export') }}"
        >
            <span class="btn-inner--icon">Export</span>
        </a>
        --}}

        @can('create stock transfer note')
            <a
                href="#"
                data-url="{{ route('stock-transfer-note.create', 0) }}"
                data-size="modal-fullscreen"
                data-ajax-popup="true"
                data-bs-title="{{ __('Create Stock Transfer Note') }}"
                class="btn mx-1 btn-sm btn-outline-primary"
            >
                <span class="btn-inner--icon">{{ __('Create Stock Transfer Note') }}</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="stock-transfer-note-page">
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2" id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body">
                            {{
                                Form::open([
                                    'route' => ['stock-transfer-note.index'],
                                    'method' => 'GET',
                                    'id' => 'customer_submit',
                                ])
                            }}

                            <div class="row d-flex align-items-center justify-content-end">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{
                                            Form::label(
                                                'issue_date',
                                                __('Issue Date'),
                                                ['class' => 'form-label']
                                            )
                                        }}

                                        {{
                                            Form::date(
                                                'issue_date',
                                                $_GET['issue_date'] ?? '',
                                                [
                                                    'class' => 'form-control month-btn',
                                                    'id' => 'pc-daterangepicker-1',
                                                ]
                                            )
                                        }}
                                    </div>
                                </div>

                                @if (Auth::user()->type === 'company')
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{
                                                Form::label(
                                                    'branches',
                                                    __('Branches'),
                                                    ['class' => 'form-label']
                                                )
                                            }}

                                            {{
                                                Form::select(
                                                    'branches',
                                                    $branches,
                                                    $_GET['branches'] ?? '',
                                                    [
                                                        'class' => 'form-control select',
                                                        'id' => 'branchstore',
                                                    ]
                                                )
                                            }}
                                        </div>
                                    </div>
                                @endif

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{
                                            Form::label(
                                                'store',
                                                __('Store'),
                                                ['class' => 'form-label']
                                            )
                                        }}

                                        {{
                                            Form::select(
                                                'store',
                                                $store ?? [],
                                                $_GET['store'] ?? '',
                                                [
                                                    'class' => 'form-control select',
                                                    'id' => 'store',
                                                ]
                                            )
                                        }}
                                    </div>
                                </div>

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{
                                            Form::label(
                                                'status',
                                                __('Status'),
                                                ['class' => 'form-label']
                                            )
                                        }}

                                        {{
                                            Form::select(
                                                'status',
                                                ['' => __('Select Status')] + $status,
                                                $_GET['status'] ?? '',
                                                ['class' => 'form-control select']
                                            )
                                        }}
                                    </div>
                                </div>

                                <div class="col-auto float-end ms-2 mt-4">
                                    <a
                                        href="#"
                                        class="btn mx-1 btn-sm btn-outline-primary"
                                        onclick="document.getElementById('customer_submit').submit(); return false;"
                                        data-bs-title="{{ __('Apply') }}"
                                    >
                                        <span class="btn-inner--icon">{{ __('Search') }}</span>
                                    </a>

                                    <a
                                        href="{{ route('stock-transfer-note.index') }}"
                                        class="btn mx-1 btn-sm btn-outline-danger"
                                        data-bs-title="{{ __('Reset') }}"
                                    >
                                        <span class="btn-inner--icon">{{ __('Clear') }}</span>
                                    </a>
                                </div>
                            </div>

                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm stock-transfer-note-table">
                <thead>
                    <tr class="table_heads">
                        <th>{{ __('S.No') }}</th>
                        <th>{{ __('Stock Transfer Note') }}</th>
                        <th>{{ __('Store From') }}</th>
                        <th>{{ __('Store To') }}</th>
                        <th>{{ __('Issue Date') }}</th>
                        <th>{{ __('Due Date') }}</th>
                        <th>{{ __('Due Amount') }}</th>
                        <th>{{ __('Status') }}</th>

                        @if (Gate::any(['edit stock transfer note', 'delete stock transfer note', 'show stock transfer note', 'forward stock transfer note', 'approve stock transfer note', 'reject stock transfer note', 'issue stock transfer note']))
                            <th>{{ __('Action') }}</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @foreach ($invoices as $invoice)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td class="Id">
                                <a
                                    href="{{ route('stock-transfer-note.show', Crypt::encrypt($invoice->id)) }}"
                                    class="btn btn-outline-primary btnpurchase1"
                                >
                                    {{ Auth::user()->invoiceNumberFormat($invoice->invoice_id) }}
                                </a>
                            </td>

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
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-warning stn-workflow-action"
                                                    data-url="{{ route('stock-transfer-note.forward-to-ho', $invoiceID) }}"
                                                    data-confirm-message="{{ __('Send this Stock Transfer Note for approval?') }}"
                                                    data-bs-title="{{ __('Send for Approval') }}"
                                                ><span class="btn-inner--icon">
                                                    <i class="ti ti-send text-white"></i></span>
                                                </button>
                                            @endif
                                        @endcan

                                        @can('approve stock transfer note')
                                            @if ($invoice->status === App\Models\StockTransferNote::STATUS_SENT_FOR_APPROVAL)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-success stn-workflow-action"
                                                    data-url="{{ route('stock-transfer-note.approve-by-ho', $invoiceID) }}"
                                                    data-confirm-message="{{ __('Approve this note and transfer its stock?') }}"
                                                    data-bs-title="{{ __('HO Approve') }}"
                                                ><span class="btn-inner--icon">
                                                    <i class="ti ti-check"></i></span>
                                                </button>
                                            @endif
                                        @endcan

                                        @can('reject stock transfer note')
                                            @if ($invoice->status === App\Models\StockTransferNote::STATUS_SENT_FOR_APPROVAL)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger stn-workflow-action"
                                                    data-url="{{ route('stock-transfer-note.reject-by-ho', $invoiceID) }}"
                                                    data-confirm-message="{{ __('Reject this Stock Transfer Note?') }}"
                                                    data-bs-title="{{ __('HO Reject') }}"
                                                ><span class="btn-inner--icon">
                                                    <i class="ti ti-x"></i></span>
                                                </button>
                                            @endif
                                        @endcan

                                        @can('issue stock transfer note')
                                            @if ($invoice->status === App\Models\StockTransferNote::STATUS_APPROVED)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary stn-workflow-action"
                                                    data-url="{{ route('stock-transfer-note.issue', $invoiceID) }}"
                                                    data-confirm-message="{{ __('Issue this Stock Transfer Note?') }}"
                                                    data-bs-title="{{ __('Issue') }}"
                                                ><span class="btn-inner--icon">
                                                    <i class="ti ti-package-export"></i></span>
                                                </button>
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
                                            >
                                                <span class="btn-inner--icon">
                                                    <i class="ti ti-printer"></i>
                                                </span>
                                            </a>

                                            <a
                                                href="{{ route('stock-transfer-note.show', $invoiceID) }}"
                                                class="btn btn-sm btn-outline-info"
                                                data-bs-title="{{ __('Show') }}"
                                            >
                                                <span class="btn-inner--icon">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </a>
                                        @endcan

                                        @can('delete stock transfer note')
                                            @if (in_array($invoice->status, [App\Models\StockTransferNote::STATUS_DRAFT, App\Models\StockTransferNote::STATUS_REJECTED], true))
                                            <div class="action-btn bg-outline-danger">
                                                {!!
                                                    Form::open([
                                                        'method' => 'DELETE',
                                                        'route' => ['stock-transfer-note.destroy', $invoice->id],
                                                        'id' => 'delete-form-' . $invoice->id,
                                                    ])
                                                !!}

                                                <a
                                                    href="#"
                                                    class="mx-3 btn btn-sm align-items-center bs-pass-para"
                                                    data-bs-title="{{ __('Delete') }}"
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
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
