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
            //     callback();
            //     function callback() {
            //         var start_date = $(".startDate").val();
            //         var end_date = $(".endDate").val();
            //         var branch = $(".account").val();

            //         $('.start_date').val(start_date);
            //         $('.end_date').val(end_date);
            //         $('.branch1').val(branch);
            //     }
        });
    </script>
@endpush

@section('action-btn')
    <div class="float-end" style='display:flex; gap:5px;'>
        {{--        <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1"  data-bs-title="{{__('Filter')}}"> --}}
        {{--            Filters --}}
        {{--        </a> --}}
        {{-- {{ Form::open(['route' => ['ledger.export']]) }}
            <input type="hidden" name="start_date" class="start_date">
            <input type="hidden" name="end_date" class="end_date">
            <input type="hidden" name="account" class="branch1">
            <button type="submit" class="btn btn-sm btn-outline-primary"  data-bs-title="{{ __('Export') }}"
            data-bs-title="{{ __('Export') }}"><span class="btn-inner--icon">Export</span></button>
        {{ Form::close() }} --}}
        <a href="#" class="btn btn-sm btn-outline-primary" onclick="saveAsPDF()"
            data-bs-title="{{ __('Download') }}" data-bs-title="{{ __('Download') }}">
            <span class="btn-inner--icon">Pdf / Print</span>
        </a>

    </div>

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
                                            {{-- {{ Form::select('account', $accounts, isset($_GET['account']) ? $_GET['account'] : '', ['class' => 'account form-control select']) }} --}}
                                            <select name="account" class="form-control selectbox" required="required">
                                                @foreach ($accounts as $chartAccount)
                                                    <option value="{{ $chartAccount['id'] }}" class="subAccount"
                                                        {{ isset($_GET['account']) && $chartAccount['id'] == $_GET['account'] ? 'selected' : '' }}>
                                                        {{ $chartAccount['code'] . ' - ' . $chartAccount['name'] }}</option>
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
                                        <a href="#" class="btn btn-sm btn-outline-primary"
                                            onclick="document.getElementById('report_ledger').submit(); return false;"
                                             data-bs-title="{{ __('Apply') }}"
                                            data-bs-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>
                                        <a href="{{ route('report.ledger') }}" class="btn btn-sm btn-outline-danger "
                                             data-bs-title="{{ __('Reset') }}"
                                            data-bs-title="{{ __('Reset') }}">
                                            <span class="btn-inner--icon"><i
                                                    class="ti ti-trash-off text-white-off "></i></span>
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
       
        {{-- @if (!empty($account))
            <div class="row mt-2">
                <div class="col">
                    <div class="card p-4 mb-4">
                        <h6 class="mb-0">{{ __('Account Name') }} :</h6>
                        <h7 class="text-sm mb-0">{{ $account->name }}</h7>
                    </div>
                </div>

                <div class="col">
                    <div class="card p-4 mb-4">
                        <h6 class="mb-0">{{ __('Account Code') }} :</h6>
                        <h7 class="text-sm mb-0">{{ $account->code }}</h7>
                    </div>
                </div>
                <div class="col">
                    <div class="card p-4 mb-4">
                        <h6 class="mb-0">{{ __('Total Debit') }} :</h6>
                        <h7 class="text-sm mb-0">{{ $authUser->newpriceFormat($filter['debit']) }}</h7>
                    </div>
                </div>
                <div class="col">
                    <div class="card p-4 mb-4">
                        <h6 class="mb-0">{{ __('Total Credit') }} :</h6>
                        <h7 class="text-sm mb-0">{{ $authUser->newpriceFormat($filter['credit']) }}</h7>
                    </div>
                </div>

                <div class="col">
                    <div class="card p-4 mb-4">
                        <h6 class="mb-0">{{ __('Balance') }} :</h6>
                        <h7 class="text-sm mb-0">
                            {{ $filter['balance'] > 0 ? __('Cr') . '. ' . $authUser->newpriceFormat(abs($filter['balance'])) : __('Dr') . '. ' . $authUser->newpriceFormat(abs($filter['balance'])) }}
                        </h7>
                    </div>
                </div>
            </div>
        @endif --}}
        <div class="row mb-4">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body table-border-style">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th> {{ __('Date') }}</th>
                                        <th> {{ __('Account Name') }}</th>
                                        <th> {{ __('Memo') }}</th>
                                        <th> {{ __('Transaction Type') }}</th>
                                        <th> {{ __('Debit') }}</th>
                                        <th> {{ __('Credit') }}</th>
                                        <th> {{ __('Balance') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!--{{-- @php
                                        $balance = 0;
                                        $totalDebit = 0;
                                        $totalCredit = 0;

                                        $accountArrays = [];
                                        foreach ($accountss as $key => $account) {

                                            $chartDatas = App\Models\Utility::getAccountData($account->id, $filter['startDateRange'], $filter['endDateRange']);

                                            $a = [0 => ['account' => $account->id]];
                                            $chartDatas = array_merge($chartDatas, $a);

                                            $accountArrays[] = $chartDatas;
                                        }
                                    @endphp --}} -->
                                    @php
                                        $balance = 0;
                                        $totalDebit = 0;
                                        $totalCredit = 0;
                                        $authUser = Auth::user();
                                        $accountArrays = [];
                                        foreach ($chart_accounts as $key => $account) {
                                            $chartDatas = App\Models\Utility::getAccountData(
                                                $account['id'],
                                                $filter['startDateRange'],
                                                $filter['endDateRange'],
                                            );
                                            // $chartDatas = $chartDatas->toArray();
                                            $a = [0 => ['account' => $account->id]];
                                            $chartDatas = array_merge($chartDatas, $a);
                                            $accountArrays[] = $chartDatas;
                                        }
                                    @endphp
                                    @foreach ($accountArrays as $account)
                                        @foreach ($account[0] as $a)
                                            
                                            @php $accountName = \App\Models\ChartOfAccount::find($a); @endphp         

                                            @php
                                                $debit = 0;
                                                $credit = 0;
                                                $i = 0;
                                            @endphp
                                            @if ($type == 'other')
                                                @foreach ($account['journalItem'] as $journalItemData)
                                                    @if ($i == 0)
                                                        @if ($journalItemData->type_name == 'Assets' || $journalItemData->type_name == 'Liabilities')
                                                            @php
                                                                $old_balance = App\Models\Utility::old_trialBalance(
                                                                    $journalItemData->account,
                                                                    $filter['startDateRange'],
                                                                );
                                                                $balance =
                                                                    @$old_balance->totalDebit -
                                                                    @$old_balance->totalCredit;
                                                                $i++;
                                                            @endphp
                                                            @if ($balance)
                                                                <tr>
                                                                    <td colspan='6' style="text-align: center;">Previous
                                                                        Balance</td>
                                                                    <td>{{ $authUser->newpriceFormat($balance) }}</td>
                                                                </tr>
                                                            @endif
                                                        @endif
                                                    @endif
                                                    <tr>
                                                        <td>{{ $journalItemData->created_at->format('d-m-Y') }}</td>
                                                        <td>{{ $accountName->name }}</td>
                                                        <td>{{ $journalItemData->description }}</td>
                                                        <td>
                                                            {{ $authUser->formatVoucherNumber($journalItemData->journal_id, $journalItemData->voucher_type) }}
                                                        </td>
                                                        <td>{{ $authUser->newpriceFormat($journalItemData->debit) }}</td>
                                                        <td>{{ $authUser->newpriceFormat($journalItemData->credit) }}</td>
                                                        <td>
                                                            @if ($journalItemData->debit != 0)
                                                                @php $balance+= $journalItemData->debit @endphp
                                                            @else
                                                                @php $balance-= $journalItemData->credit @endphp
                                                            @endif
                                                            {{ $authUser->newpriceFormat($balance) }}
                                                        </td>
                                                    </tr>
                                                @endforeach

                                                @php
                                                    $subAccounts = \App\Models\ChartOfAccount::select(
                                                        'chart_of_accounts.id',
                                                        'chart_of_accounts.code',
                                                        'chart_of_accounts.name',
                                                        'chart_of_account_parents.account',
                                                    );
                                                    $subAccounts->leftjoin(
                                                        'chart_of_account_parents',
                                                        'chart_of_accounts.parent',
                                                        'chart_of_account_parents.id',
                                                    );
                                                    $subAccounts->where('chart_of_accounts.parent', '!=', 0);
                                                    $subAccounts->where(
                                                        'chart_of_account_parents.account',
                                                        $account[0],
                                                    );
                                                    $subAccounts->where(
                                                        'chart_of_accounts.created_by',
                                                        $authUser->creatorId(),
                                                    );
                                                    $subAccounts = $subAccounts->get();
                                                @endphp
                                                {{-- for child account data show  --}}
                                                @php
                                                    $accountArrays_child = [];
                                                    foreach ($subAccounts as $key => $account_child) {
                                                        $chartDatas_child = App\Models\Utility::getAccountData(
                                                            $account_child['id'],
                                                            $filter['startDateRange'],
                                                            $filter['endDateRange'],
                                                        );
                                                        // $chartDatas = $chartDatas->toArray();
                                                        $ab = [0 => ['account' => $account_child->id]];
                                                        $chartDatas_child = array_merge($chartDatas_child, $ab);
                                                        $accountArrays_child[] = $chartDatas_child;
                                                    }
                                                @endphp
                                                @foreach ($accountArrays_child as $account_ch)
                                                    @foreach ($account_ch[0] as $ab)
                                                        @php $accountName_ch = \App\Models\ChartOfAccount::find($ab); @endphp
                                                        @php
                                                            $ic = 0;
                                                        @endphp
                                                        @foreach ($account_ch['journalItem'] as $journalItemData_ch)
                                                            @if ($ic == 0)
                                                                @if ($journalItemData_ch->type_name == 'Assets' || $journalItemData_ch->type_name == 'Liabilities')
                                                                    @php
                                                                        $old_balance = App\Models\Utility::old_trialBalance(
                                                                            $journalItemData_ch->account,
                                                                            $filter['startDateRange'],
                                                                        );
                                                                        $balance =
                                                                            @$old_balance->totalDebit -
                                                                            @$old_balance->totalCredit;
                                                                        $ic++;
                                                                    @endphp
                                                                    @if ($balance)
                                                                        <tr>
                                                                            <td colspan='7' style="text-align: center;">
                                                                                Previous Balance</td>
                                                                            <td>{{ $authUser->newpriceFormat($balance) }}
                                                                            </td>
                                                                        </tr>
                                                                    @endif
                                                                @endif
                                                            @endif
                                                            <tr>
                                                                <td>{{ $journalItemData_ch->created_at->format('d-m-Y') }}
                                                                <td>{{ $accountName_ch->name }}</td>
                                                                <td>{{ $journalItemData_ch->description }}</td>
                                                                <td>
                                                                    {{ $authUser->formatVoucherNumber($journalItemData_ch->journal_id, $journalItemData_ch->voucher_type) }}
                                                                </td>
                                                                </td>
                                                                <td>{{ $authUser->newpriceFormat($journalItemData_ch->debit) }}
                                                                </td>
                                                                <td>{{ $authUser->newpriceFormat($journalItemData_ch->credit) }}
                                                                </td>
                                                                <td>
                                                                    @if ($journalItemData_ch->debit != 0)
                                                                        @php $balance+= $journalItemData_ch->debit @endphp
                                                                    @else
                                                                        @php $balance-= $journalItemData_ch->credit @endphp
                                                                    @endif
                                                                    {{ $authUser->newpriceFormat($balance) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                @endforeach
                                                {{-- End child row data  --}}
                                            @else
                                                @if ($account['type'] == 'group')
                                                    @foreach ($account['journalItem'] as $journalItem)
                                                        @foreach ($journalItem as $journalItemData)
                                                            @php $accountName = \App\Models\ChartOfAccount::find($journalItemData->account,); @endphp

                                                            <tr>
                                                                <td>{{ $journalItemData->created_at->format('d-m-Y') }}
                                                                <td>{{ @$accountName->name }}</td>
                                                                <td>{{ $journalItemData->description }}</td>
                                                                <td>
                                                                    {{ $authUser->formatVoucherNumber($journalItemData->journal_id, $journalItemData->voucher_type) }}
                                                                </td>
                                                                </td>
                                                                <td>{{ $authUser->newpriceFormat($journalItemData->debit) }}
                                                                </td>
                                                                <td>{{ $authUser->newpriceFormat($journalItemData->credit) }}
                                                                </td>
                                                                <td>
                                                                    @if ($journalItemData->debit != 0)
                                                                        @php $balance-= $journalItemData->debit @endphp
                                                                    @else
                                                                        @php $balance+= $journalItemData->credit @endphp
                                                                    @endif
                                                                    {{ $authUser->newpriceFormat($balance) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach
                                                @endif
                                                @break;
                                            @endif
                                        @endforeach
                                        @if ($type == 'group')
                                            @break;
                                        @endif
                                    @endforeach
                                </tbody>
                            </table> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
