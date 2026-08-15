@extends('layouts.admin')
@section('page-title')
    {{ __('Ledger Summary') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Ledger Summary') }}</li>
@endsection
@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('public/acron/searchselect.css') }}" />
    <script src="{{ asset('public/acron/searchselect.js') }}"></script>
    <style>
        .ledger-summary-toolbar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 12px;
        }

        .ledger-summary-search {
            max-width: 320px;
        }

        .ledger-summary-table {
            table-layout: fixed;
            width: 100%;
        }

        .ledger-summary-table th:nth-child(7),
        .ledger-summary-table td:nth-child(7) {
            width: 28%;
            min-width: 280px;
            white-space: normal;
            word-break: break-word;
        }

        .ledger-summary-table th:nth-child(1),
        .ledger-summary-table td:nth-child(1) {
            width: 44px;
        }

        .ledger-summary-table th:nth-child(2),
        .ledger-summary-table td:nth-child(2) {
            width: 110px;
        }

        .ledger-summary-table th:nth-child(6),
        .ledger-summary-table td:nth-child(6) {
            width: 180px;
            min-width: 180px;
            white-space: normal;
            word-break: break-word;
        }

        .ledger-summary-table tfoot td {
            font-weight: 700;
            background: #f8f9fa;
            border-top: 2px solid #dee2e6;
        }
    </style>
    <script>
        var filename = $('#filename').val();

        function saveAsPDF() {
            var element = document.getElementById('printableArea');
            var opt = {
                margin: 0.3,
                filename: filename,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 4,
                    dpi: 72,
                    letterRendering: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'A2'
                }
            };
            html2pdf().set(opt).from(element).save();
        }

        function filterLedgerSummaryRows() {
            var input = document.getElementById('ledgerSummarySearch');
            var table = document.querySelector('.ledger-summary-table');

            if (!input || !table) {
                return;
            }

            var query = (input.value || '').toLowerCase().trim();
            var rows = table.querySelectorAll('tbody tr');

            rows.forEach(function(row) {
                var emptyStateCell = row.querySelector('td[colspan="11"]');
                if (emptyStateCell) {
                    row.style.display = query === '' ? '' : 'none';
                    return;
                }

                var text = (row.textContent || '').toLowerCase();
                row.style.display = query === '' || text.indexOf(query) !== -1 ? '' : 'none';
            });
        }

        $(document).ready(function() {
            $('.selectbox').select2();
        });

        function exportToExcel() {
            $('#export_start_date').val($('.startDate').val());
            $('#export_end_date').val($('.endDate').val());
            $('#export_account').val($('select[name="account"]').val());
            $('#export_branch').val($('select[name="branch"]').val());
            $('#ledger_export_form').submit();
        }
    </script>
@endpush

@section('action-btn')
    <div class="float-end" style='display:flex; gap:5px;'>
       
        <a href="#" class="btn btn-sm btn-outline-success" onclick="exportToExcel()"
            data-bs-title="{{ __('Export') }}">
            <span class="btn-inner--icon">Export Excel</span>
        </a>

        <a href="#" class="btn btn-sm btn-outline-primary" onclick="saveAsPDF()"
            data-bs-title="{{ __('Download') }}" data-bs-title="{{ __('Download') }}">
            <span class="btn-inner--icon">Pdf / Print</span>
        </a>

    </div>

    <form method="POST" action="{{ route('ledger.export') }}" id="ledger_export_form" style="display:none;">
        @csrf
        <input type="hidden" name="start_date" id="export_start_date">
        <input type="hidden" name="end_date" id="export_end_date">
        <input type="hidden" name="account" id="export_account">
        <input type="hidden" name="branch" id="export_branch">
    </form>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['report.ledger'], 'method' => 'GET', 'id' => 'report_ledger']) }}

                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                                            {{ Form::select('branch', $branches, isset($_GET['branch']) ? $_GET['branch'] : '', ['class' => 'form-control selectbox']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('start_date', $filter['startDateRange'], ['class' => 'startDate month-btn form-control']) }}
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('end_date', $filter['endDateRange'], ['class' => 'endDate month-btn form-control']) }}
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                        <div class="btn-box">
                                            {{ Form::label('account', __('Account'), ['class' => 'form-label']) }}
                                            <select name="account" class="form-control selectbox" required="required">
                                                @foreach ($accounts as $chartAccount)
                                                    <option value="{{ $chartAccount['id'] }}" class="subAccount"
                                                        {{ isset($_GET['account']) && $chartAccount['id'] == $_GET['account'] ? 'selected' : '' }}>
                                                        {{ $chartAccount['code'] . ' - ' . $chartAccount['name'] }}
                                                    </option>
                                                    @foreach ($subAccounts as $subAccount)
                                                        @if ($chartAccount['id'] == $subAccount['account'])
                                                            <option value="{{ $subAccount['id'] }}" class="ms-5"
                                                                {{ isset($_GET['account']) && $_GET['account'] == $subAccount['id'] ? 'selected' : '' }}>
                                                                &nbsp; &nbsp;&nbsp;
                                                                {{ $subAccount['code'] . ' - ' . $subAccount['name'] }}
                                                            </option>
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <div class="row">
                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                            onclick="document.getElementById('report_ledger').submit(); return false;"
                                             data-bs-title="{{ __('Apply') }}"
                                            data-bs-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>
                                        <a href="{{ route('report.ledger') }}" class="btn mx-1 btn-sm btn-outline-danger "
                                             data-bs-title="{{ __('Reset') }}"
                                            data-bs-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon">Clear</span>
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

    <div id="printableArea">
        <div class="row mb-4">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <div class="ledger-summary-toolbar">
                                <input type="text" id="ledgerSummarySearch" class="form-control ledger-summary-search"
                                    oninput="filterLedgerSummaryRows()"
                                    placeholder="{{ __('Search table...') }}">
                            </div>
                            <table class="table ledger-summary-table">
                                <thead>
                                    <tr class="table_heads">
                                        <th>#</th>
                                        <th> {{ __('Date') }}</th>
                                        <th> {{ __('Account Name') }}</th>
                                        <th> {{ __('Category') }}</th>
                                        <th> {{ __('User Type') }}</th>
                                        <th> {{ __('User Name') }}</th>
                                        <th> {{ __('Memo') }}</th>
                                        <th> {{ __('Transaction Type') }}</th>
                                        <th class="text-end"> {{ __('Debit') }}</th>
                                        <th class="text-end"> {{ __('Credit') }}</th>
                                        <th class="text-end"> {{ __('Balance') }}</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($rows->lazy() as $row)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $row['date'] }}</td>
                                            <td>{{ $row['account'] }}</td>
                                            <td>{{ $row['category'] ?? '-' }}</td>
                                            <td>{{ $row['user_type'] ?? '-' }}</td>
                                            <td>{{ $row['user_name'] ?? '-' }}</td>
                                            <td>{{ $row['memo'] }}</td>
                                            <td>
                                                {!! isset($row['route']) 
                                                    ? '<a href="' . route($row['route'], $row['journal']) . '">' . e($row['voucher']) . '</a>' 
                                                    : e($row['voucher']) !!}
                                            </td>
                                            <td class="text-end">{{ $row['debit'] }}</td>
                                            <td class="text-end">{{ $row['credit'] }}</td>
                                            <td class="text-end">{{ $row['balance'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center text-muted">
                                                {{ __('No transactions for the selected period.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold">
                                        <td colspan="8" class="text-end">{{ __('Grand Total') }}</td>
                                        <td class="text-end">{{ number_format((float) ($grandTotals['debit'] ?? 0), 2) }}</td>
                                        <td class="text-end">{{ number_format((float) ($grandTotals['credit'] ?? 0), 2) }}</td>
                                        <td class="text-end">{{ number_format((float) ($grandTotals['balance'] ?? 0), 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
