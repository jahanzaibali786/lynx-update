@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Head Imprest Vouchers') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Head Imprest Vouchers')}}</li>
@endsection

@section('action-btn')
    <div class="float-end d-flex align-items-center gap-2">
        <div class="badge bg-primary p-2 px-3 rounded text-white" style="font-size: 0.9rem; font-weight: 500; letter-spacing: 0.5px;">
            <i class="ti ti-building-bank me-1"></i> {{ __('Total Balance: ') }} <strong>{{ \Auth::user()->priceFormat($totalBankBalance) }}</strong>
        </div>
        <a href="{{ route('report.head-imprest.cashflow') }}" class="btn btn-sm btn-info text-white m-0">
            <span class="btn-inner--icon"><i class="ti ti-report me-1"></i>{{__('Cash Flow Report')}}</span>
        </a>
        @can('create journal entry')
            <a href="#" data-url="{{ route('expense-voucher.create') }}" data-ajax-popup="true" data-title="{{__('Create Expense Voucher')}}" data-size="lg" class="btn btn-sm btn-outline-primary m-0">
                <span class="btn-inner--icon"><i class="ti ti-plus me-1"></i>{{__('Create Expense Voucher')}}</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12 mb-4">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['head-imprest-vouchers.index'], 'method' => 'GET', 'id' => 'head_imprest_voucher_filter']) }}
                    <div class="row align-items-center justify-content-end">
                        <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12">
                            <div class="row">
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('from_date', __('From Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('from_date', isset($_GET['from_date']) ? $_GET['from_date'] : '', ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('to_date', __('To Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('to_date', isset($_GET['to_date']) ? $_GET['to_date'] : '', ['class' => 'form-control']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                                        {{ Form::select('branch_id', $branches, isset($_GET['branch_id']) ? $_GET['branch_id'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                                    <div class="btn-box">
                                        {{ Form::label('expense_account_id', __('Expense Head'), ['class' => 'form-label']) }}
                                        {{ Form::select('expense_account_id', $chartAccounts, isset($_GET['expense_account_id']) ? $_GET['expense_account_id'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto mt-4">
                            <div class="row">
                                <div class="col-auto d-flex gap-1">
                                    <a href="#" class="btn btn-sm btn-outline-primary"
                                        onclick="document.getElementById('head_imprest_voucher_filter').submit(); return false;">
                                        <span class="btn-inner--icon">{{ __('Search') }}</span>
                                    </a>
                                    <a href="{{ route('head-imprest-vouchers.index') }}" class="btn btn-sm btn-outline-danger">
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
                        <table class="datatable">
                            <thead class="table_heads">
                            <tr>
                                <th>#</th>
                                <th> {{__('Journal ID')}}</th>
                                <th> {{__('Date')}}</th>
                                <th> {{__('Payee')}}</th>
                                <th> {{__('Receiver')}}</th>
                                <th> {{__('Payment Mode')}}</th>
                                <th> {{__('Amount')}}</th>
                                <th> {{__('Status')}}</th>
                                <th> {{__('Approved By')}}</th>
                                <th class="wrap-td"> {{__('Description')}}</th>
                                <th width="15%"> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($journalEntries as $journalEntry)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="Id">
                                        <a href="{{ route('journal-entry.show',$journalEntry->id) }}" class="btn btnpurchase1 btn-outline-primary">{{ $journalEntry->getVoucherNumber() }}</a>
                                    </td>
                                    <td>{{ Auth::user()->dateFormat($journalEntry->date) }}</td>
                                    <td>
                                        <div><strong>{{ $journalEntry->payee_account_title ?? '-' }}</strong></div>
                                        <small class="text-muted">{{ $journalEntry->payee_account_no ?? '' }}</small>
                                    </td>
                                    <td>{{ $journalEntry->receiver_name ?? '-' }}</td>
                                    <td>
                                        <div>{{ strtoupper($journalEntry->payment_mode ?? ($journalEntry->mode ?? '-')) }}</div>
                                        @if(!empty($journalEntry->payment_date))
                                            <small class="text-muted">{{ \Auth::user()->dateFormat($journalEntry->payment_date) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ \Auth::user()->priceFormat($journalEntry->totalCredit())}}
                                    </td>
                                    <td>
                                        @if($journalEntry->status == 'Draft')
                                            <span class="badge bg-warning p-2 px-3 rounded">{{ __($journalEntry->status) }}</span>
                                        @elseif($journalEntry->status == 'Approved')
                                            <span class="badge bg-success p-2 px-3 rounded">{{ __($journalEntry->status) }}</span>
                                        @elseif($journalEntry->status == 'Posted')
                                            <span class="badge bg-info p-2 px-3 rounded">{{ __($journalEntry->status) }}</span>
                                        @else
                                            <span class="badge bg-danger p-2 px-3 rounded">{{ __($journalEntry->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($journalEntry->approved_by)
                                            {{ optional(\App\Models\User::find($journalEntry->approved_by))->name ?? '-' }}
                                        @else
                                            <span class="text-muted">{{ __('Pending') }}</span>
                                        @endif
                                    </td>
                                    <td class="wrap-td">{{!empty($journalEntry->description)?$journalEntry->description:'-'}}</td>
                                    <td>
                                        <div class="action-btn ms-2">
                                            <a title="{{__('View Details')}}" href="{{ route('journal-entry.show',[$journalEntry->id]) }}" class="mx-1 btn mx-1 btn-sm btn-outline-info align-items-center">
                                                <span class="btn-inner--icon"><i class="ti ti-eye"></i></span>
                                            </a>
                                            @can('show journal entry')
                                                <a title="{{ __('Voucher Print') }}" href="{{ route('journal-entry.voucher-print', $journalEntry->id) }}" target="_blank" class="mx-1 btn mx-1 btn-sm btn-outline-secondary align-items-center" data-bs-title="{{ __('Print') }}">
                                                    <span class="btn-inner--icon"> <i class="ti ti-printer"></i> </span>
                                                </a>
                                            @endcan
                                            @can('edit journal entry')
                                                @if(!in_array($journalEntry->status, ['Approved', 'Posted']) && \Auth::user()->type == 'company')
                                                    {!! Form::open(['method' => 'POST', 'route' => array('head-imprest-vouchers.approve', $journalEntry->id), 'style'=>'display:inline-block;', 'id'=>'approve-head-form-'.$journalEntry->id]) !!}
                                                    <a href="#" class="mx-1 btn btn-sm btn-outline-success align-items-center bs-pass-para" data-bs-title="{{__('Approve')}}" data-confirm="{{__('Approve Voucher?').'|'.__('This will approve the voucher and update bank balances. Do you want to continue?')}}" data-confirm-yes="document.getElementById('approve-head-form-{{$journalEntry->id}}').submit();">
                                                        <span class="btn-inner--icon"><i class="ti ti-check"></i></span>
                                                    </a>
                                                    {!! Form::close() !!}
                                                @endif
                                                @if($journalEntry->status == 'Draft' && \Auth::user()->type == 'branch')
                                                    {!! Form::open(['method' => 'POST', 'route' => array('head-imprest-vouchers.send-to-ho', $journalEntry->id), 'style'=>'display:inline-block;', 'id'=>'send-to-ho-form-'.$journalEntry->id]) !!}
                                                    <a href="#" class="mx-1 btn btn-sm btn-outline-info align-items-center bs-pass-para" data-bs-title="{{__('Send to HO')}}" data-confirm="{{__('Send to HO?').'|'.__('Are you sure you want to send this voucher to HO for approval?')}}" data-confirm-yes="document.getElementById('send-to-ho-form-{{$journalEntry->id}}').submit();">
                                                        <span class="btn-inner--icon"><i class="ti ti-send"></i></span>
                                                    </a>
                                                    {!! Form::close() !!}
                                                @endif
                                                @if(!in_array($journalEntry->status, ['Approved', 'Posted']))
                                                    <a title="{{__('Edit')}}" href="{{ route('journal-entry.edit',[$journalEntry->id]) }}" class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center" data-bs-title="{{__('Edit')}}">
                                                        <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                                    </a>
                                                @endif
                                            @endcan
                                            @can('delete journal entry')
                                                @if(!($journalEntry->status == 'Approved' && \Auth::user()->type == 'branch'))
                                                    {!! Form::open(['method' => 'DELETE', 'route' => array('journal-entry.destroy', $journalEntry->id),'id'=>'delete-form-'.$journalEntry->id, 'style'=>'display:inline-block;']) !!}
                                                    <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$journalEntry->id}}').submit();">
                                                        <span class="btn-inner--icon"> <i class="ti ti-trash"></i> </span>
                                                    </a>
                                                    {!! Form::close() !!}
                                                @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
