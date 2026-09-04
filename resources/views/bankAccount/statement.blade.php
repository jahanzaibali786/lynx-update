@extends('layouts.admin')
@section('page-title')
    {{ __('Bank Statement: ') . $bankAccount->holder_name }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('bank-account.index') }}">{{ __('Bank Account') }}</a></li>
    <li class="breadcrumb-item">{{ __('Statement') }}</li>
@endsection
@push('script-page')
    <script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('public/acron/searchselect.css') }}" />
    <script src="{{ asset('public/acron/searchselect.js') }}"></script>
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

        $(document).ready(function() {
            $('.selectbox').select2();
        });

    </script>
@endpush

@section('action-btn')
    <div class="float-end" style='display:flex; gap:5px;'>
       
    </div>

@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['bank-account.statement', $bankAccount->id], 'method' => 'GET', 'id' => 'report_ledger']) }}

                        <div class="row align-items-center justify-content-end">
                            <div class="col-xl-10">
                                <div class="row">
                                    <input type="hidden" name="export" id="is_export" value="0">
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

                                    <div class="col-auto mt-4">
                                        <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                            onclick="document.getElementById('report_ledger').submit(); return false;"
                                             data-bs-title="{{ __('Apply') }}"
                                            data-bs-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>
                                        <a href="{{ route('bank-account.statement', $bankAccount->id) }}" class="btn mx-1 btn-sm btn-outline-danger "
                                             data-bs-title="{{ __('Reset') }}"
                                            data-bs-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon">Clear</span>
                                        </a>
                                        <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                            onclick="document.getElementById('is_export').value='1'; document.getElementById('report_ledger').submit(); setTimeout(() => { document.getElementById('is_export').value='0'; }, 500); return false;"
                                            data-bs-toggle="tooltip" title="{{ __('Export') }}">
                                            <span class="btn-inner--icon">Export</span>
                                        </a>
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
                            <table class="table datatable">
                                <thead>
                                    <tr class="table_heads">
                                        <th>#</th>
                                        <th> {{ __('Date') }}</th>
                                        <th> {{ __('Voucher') }}</th>
                                        <th> {{ __('Account Name') }}</th>
                                        <th> {{ __('Memo') }}</th>
                                        <th> {{ __('Reference') }}</th>
                                        <th class="text-end"> {{ __('Debit') }}</th>
                                        <th class="text-end"> {{ __('Credit') }}</th>
                                        <th class="text-end"> {{ __('Balance') }}</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($rows->lazy() as $row)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ date('d-M-Y', strtotime($row['date'])) }}</td>
                                             <td>
                                                {!! isset($row['route']) 
                                                    ? '<a href="' . route($row['route'], $row['journal']) . '">' . e($row['voucher']) . '</a>' 
                                                    : e($row['voucher']) !!}
                                            </td>
                                            <td>{{ $row['account'] }}</td>
                                            <td>{{ $row['memo'] }}</td>
                                            <td>{{ $row['detail'] }}</td>
                                            <td class="text-end">{{ $row['debit'] }}</td>
                                            <td class="text-end">{{ $row['credit'] }}</td>
                                            <td class="text-end">{{ $row['balance'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">
                                                {{ __('No transactions for the selected period.') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
