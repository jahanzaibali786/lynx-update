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
            <a href="{{ route('expense-voucher.create') }}" class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">{{__('Create Expense Voucher')}}</span>
            </a>
            <a href="{{ route('createVoucher') }}" class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">{{__('Create Voucher')}}</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
            <div class="col-sm-12">
                <div class="mt-2 " id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body">
                            {{ Form::open(['route' => ['journal-entry.index'], 'method' => 'GET', 'id' => 'journal-entry_submit']) }}
                            <div class="row d-flex justify-content-end ">

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>

                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                        onclick="document.getElementById('journal-entry_submit').submit(); return false;"
                                         data-bs-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('journal-entry.index') }}"
                                        class="btn mx-1 btn-sm btn-outline-danger" 
                                        data-bs-title="{{ __('Reset') }}">
                                        <span class="btn-inner--icon">Clear</span>
                                    </a>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="">
                            <thead class="table_heads">
                            <tr>
                                <th>#</th>
                                <th> {{__('Journal ID')}}</th>
                                <th> {{__('Category Type')}}</th>
                                <th> {{__('Date')}}</th>
                                <th> {{__('Payee')}}</th>
                                <th> {{__('Receiver')}}</th>
                                <th> {{__('Payment Mode')}}</th>
                                <th> {{__('Amount')}}</th>
                                <th class="wrap-td"> {{__('Description')}}</th>
                                <th> {{__('Status')}}</th>
                                <th width="10%"> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($journalEntries as $journalEntry)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="Id">
                                        <a href="{{ route('journal-entry.show',$journalEntry->id) }}" class="btn btnpurchase1 btn-outline-primary">{{ $journalEntry->getVoucherNumber() }}</a>
                                    </td>
                                    <td>{{ !empty($journalEntry->categoryType) ? $journalEntry->categoryType->name : '-' }}</td>
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
                                    <td class="wrap-td">{{!empty($journalEntry->description)?$journalEntry->description:'-'}}</td>
                                    <td>
                                        @if($journalEntry->status == 'Approved')
                                            <span class="badge bg-success p-2 px-3 rounded">{{__('Approved')}}</span>
                                        @elseif($journalEntry->status == 'Draft')
                                            <span class="badge bg-warning p-2 px-3 rounded">{{__('Draft')}}</span>
                                        @else
                                            <span class="badge bg-danger p-2 px-3 rounded">{{ $journalEntry->status }}</span>
                                        @endif
                                    </td>
                                     <td>
                                                <div class="action-btn ms-2" style="display: flex; gap: 5px;">  
                                                    @can('show journal entry')
                                                <a title="{{ __('Voucher Print') }}" href="{{ route('journal-entry.voucher-print', $journalEntry->id) }}" target="_blank" class="mx-1 btn mx-1 btn-sm btn-outline-secondary align-items-center" data-bs-title="{{ __('Print') }}">
                                                   <span class="btn-inner--icon"> <i class="ti ti-printer"></i> </span>
                                                </a>
                                        @endcan
                                         @can('edit journal entry')
                                             @if($journalEntry->status != 'Approved' && \Auth::user()->type == 'company')
                                                 {!! Form::open(['method' => 'POST', 'route' => array('journal-entry.approve', $journalEntry->id), 'id'=>'approve-form-'.$journalEntry->id, 'style'=>'display:inline;']) !!}
                                                 <a href="#" class="mx-1 btn btn-sm btn-outline-success align-items-center bs-pass-para" data-bs-title="{{__('Approve / Send to HO')}}" data-confirm="{{__('Approve Voucher?').'|'.__('This will approve the voucher and update bank balances. Do you want to continue?')}}" data-confirm-yes="document.getElementById('approve-form-{{$journalEntry->id}}').submit();">
                                                     <span class="btn-inner--icon"> <i class="ti ti-check"></i> </span>
                                                 </a>
                                                 {!! Form::close() !!}
                                             @endif
                                             @if($journalEntry->status == 'Draft' && \Auth::user()->type == 'branch')
                                                 {!! Form::open(['method' => 'POST', 'route' => array('journal-entry.send-to-ho', $journalEntry->id), 'id'=>'send-to-ho-form-'.$journalEntry->id, 'style'=>'display:inline;']) !!}
                                                 <a href="#" class="mx-1 btn btn-sm btn-outline-info align-items-center bs-pass-para" data-bs-title="{{__('Send to HO')}}" data-confirm="{{__('Send to HO?').'|'.__('Are you sure you want to send this voucher to HO for approval?')}}" data-confirm-yes="document.getElementById('send-to-ho-form-{{$journalEntry->id}}').submit();">
                                                     <span class="btn-inner--icon"> <i class="ti ti-send"></i> </span>
                                                 </a>
                                                 {!! Form::close() !!}
                                             @endif
                                             @if($journalEntry->is_system_generated == 0)
                                                 <a title="{{__('Edit Journal')}}" href="{{ route('journal-entry.edit',[$journalEntry->id]) }}" class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center"  data-bs-title="{{__('Edit')}}" data-bs-title="{{__('Edit')}}">
                                                    <span class="btn-inner--icon"> <i class="ti ti-pencil"></i> </span>
                                                 </a>
                                             @endif
                                         @endcan
                                         @can('delete journal entry')
                                             @if(!($journalEntry->status == 'Approved' && \Auth::user()->type == 'branch'))
                                                 {!! Form::open(['method' => 'DELETE', 'route' => array('journal-entry.destroy', $journalEntry->id),'id'=>'delete-form-'.$journalEntry->id, 'style'=>'display:inline;']) !!}
                                                 <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$journalEntry->id}}').submit();">
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
                    @if ($journalEntries->hasPages())
                        <div class="pagination">
                            <ul>
                                @if ($journalEntries->onFirstPage())
                                    <li class="disabled">&laquo;</li>
                                @else
                                    <li><a href="{{ $journalEntries->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                                @endif
                                @if ($journalEntries->currentPage() > 1)
                                    <li><a href="{{ $journalEntries->appends(request()->query())->url(1) }}">First</a></li>
                                @endif
                                @php
                                    $currentPage = $journalEntries->currentPage();
                                    $lastPage = $journalEntries->lastPage();
                                    $startPage = max(1, $currentPage - 4);
                                    $endPage = min($lastPage, $currentPage + 5);
                                    if ($endPage - $startPage < 9) {
                                        if ($currentPage < $lastPage - 9) {
                                            $endPage = $startPage + 9;
                                        } else {
                                            $startPage = max(1, $lastPage - 9);
                                        }
                                    }
                                @endphp
                                @for ($page = $startPage; $page <= $endPage; $page++)
                                    <li class="{{ $page == $journalEntries->currentPage() ? 'active' : '' }}">
                                        <a href="{{ $journalEntries->appends(request()->query())->url($page) }}">{{ $page }}</a>
                                    </li>
                                @endfor
                                @if ($journalEntries->currentPage() < $journalEntries->lastPage())
                                    <li><a href="{{ $journalEntries->appends(request()->query())->url($journalEntries->lastPage()) }}">Last</a></li>
                                @endif
                                @if ($journalEntries->hasMorePages())
                                    <li><a href="{{ $journalEntries->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a></li>
                                @else
                                    <li class="disabled">&raquo;</li>
                                @endif
                            </ul>
                        </div>
                    @endif





                </div>
            </div>
        </div>
    </div>

@endsection
