@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Stock Transfer Notes') }}
@endsection

@push('script-page')
    <script>
        function initStockTransferNoteTooltips(scope) {
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

        function renumberStockTransferNoteRows() {
            $('.stock-transfer-note-table tbody tr').each(function (index) {
                $(this).find('.stn-row-number').text(index + 1);
            });
        }

        $(document).ready(function () {
            initStockTransferNoteTooltips(document);
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

                <tbody id="stock-transfer-note-table-body">
                    @foreach ($invoices as $invoice)
                        @include('stock_transfer_note.partials.index_row', ['invoice' => $invoice, 'rowNumber' => $loop->iteration])
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
