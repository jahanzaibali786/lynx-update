@extends('layouts.admin')
@section('page-title')
    {{__('Manage Journal Entry')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Journal Entry')}}</li>
@endsection

@section('action-btn')
<style>
    .wrap-td {
        max-width: 500px !important;
        text-wrap: auto !important;
    }
</style>
    <div class="float-end">
        @can('create journal entry')
            <a href="{{ route('journal-entry.create') }}" title="{{__('Create New Journal')}}"   data-bs-title="{{__('Create')}}" class="btn mx-1 btn-sm btn-outline-primary">
               <span class="btn-inner--icon"> Create </span>
            </a>
        @endcan
    </div>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    @endsection
    @push('script-page')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {

   
    var table = $('#tables').DataTable({
    processing: false,
    serverSide: false,

    ajax: {
        url: "{{ route('journal.test') }}",
dataSrc: function (json) {
    if (json.openingRow) {
        json.data.unshift(json.openingRow);
    }
    return json.data;
},
        data: function (d) {
            d.start_date = $('.startDate').val();
            d.end_date = $('.endDate').val();
            d.branch = $('select[name="branch"]').val();
            d.account = $('select[name="account"]').val();
        }
    },

    pageLength: 100,

    columns: [
        { data: 'date', name: 'je.date' },
        { data: 'accountname', name: 'ca.name' },
        { data: 'memo', name: 'ji.description' },
        { data: 'voucher_type', name: 'je.voucher_type' },
        { data: 'debit', name: 'ji.debit' },
        { data: 'credit', name: 'ji.credit' },
        { data: 'balance', name: 'balance' },
    ]
});
$('.ddd').click(function(e){
     e.preventDefault();
    table.ajax.reload();
});
});
</script>
@endpush

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['journal.test'], 'method' => 'GET', 'id' => 'report_ledger']) }}

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
                                        <a href="#" class="btn mx-1 btn-sm btn-outline-primary ddd"
                                            
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

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table id="tables" class="display">
    <thead>
        <tr>
            <th>{{ __('Date') }}</th>
            <th>{{ __('Account') }}</th>
            <th>{{ __('Description') }}</th>
            <th>{{ __('Voucher Type') }}</th>
            <th>{{ __('Debit') }}</th>
            <th>{{ __('Credit') }}</th>
            <th>{{ __('Balance') }}</th>
        </tr>
    </thead>
</table>
                    </div>
                





                </div>
            </div>
        </div>
    </div>

@endsection
