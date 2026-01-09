@extends('layouts.admin')
@section('page-title')
    {{__('Manage Cash Payment Voucher Entry')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Vouchers')}}</li>
    <li class="breadcrumb-item">{{__('Cash Payment Voucher Entry')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        @can('create journal entry')
            <a href="{{ route('cash-payment-voucher.create') }}" data-bs-toggle="{{__('Create New Cash Payment Voucher')}}"   data-bs-title="{{__('Create')}}" class="btn mx-1 btn-sm btn-outline-primary">
               <span class="btn-inner--icon"> Create </span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    @if(\Auth::user()->type == 'company')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['cash-payment-voucher.index'], 'method' => 'GET', 'id' => 'cash-payment-voucher_submit']) }}
                        <div class="row d-flex justify-content-end ">
        
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' ]) }}
                                </div>                               
                            </div>

                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('cash-payment-voucher_submit').submit(); return false;"
                                     data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('cash-payment-voucher.index') }}" class="btn mx-1 btn-sm btn-outline-danger" 
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
    @endif

                        <table class="">
                            <thead>
                            <tr class="table_heads">
                                <th> {{__('Voucher ID')}}</th>
                                <th> {{__('Date')}}</th>
                                <th> {{__('Amount')}}</th>
                                <th> {{__('Description')}}</th>
                                <th width="10%"> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($journalEntries as $journalEntry)
                                <tr>
                                    <td class="Id">
                                        <a href="{{ route('cash-payment-voucher.show',$journalEntry->id) }}" class="btn btn-outline-primary">{{ AUth::user()->CPVNumberFormat($journalEntry->journal_id) }}</a>
                                    </td>
                                    <td>{{ Auth::user()->dateFormat($journalEntry->date) }}</td>
                                    <td>
                                        {{ \Auth::user()->priceFormat($journalEntry->totalCredit())}}
                                    </td>
                                    <td>{{!empty($journalEntry->description)?$journalEntry->description:'-'}}</td>
                                    
                                    <td>
                                    <div class="action-btn ms-2">
                                        @can('edit journal entry')
                                            
                                                <a data-bs-toggle="{{__('Edit Cash Payment Voucher')}}" href="{{ route('cash-payment-voucher.edit',[$journalEntry->id]) }}" class="mx-1 btn btn-outline-primary btn-sm align-items-center"  data-bs-title="{{__('Edit')}}" data-bs-title="{{__('Edit')}}">
                                                  <span class="btn-inner--icon">  <i class="ti ti-pencil"></i> </span>
                                                </a>
                                            
                                        @endcan
                                        @can('delete journal entry')
                                                
                                                    {!! Form::open(['method' => 'DELETE', 'route' => array('cash-payment-voucher.destroy', $journalEntry->id),'id'=>'delete-form-'.$journalEntry->id]) !!}

                                                    <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$journalEntry->id}}').submit();">
                                                            <span class="btn-inner--icon"> <i class="ti ti-trash"></i> </span>
                                                        </a>
                                                    {!! Form::close() !!}
                                                
                                        @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if($journalEntries != "")
<div class="pagination">
    <ul>
        @if ($journalEntries->onFirstPage())
        <li class="disabled">&laquo;</li>
        @else
        <li><a href="{{ $journalEntries->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif
        @for ($page = 1; $page <= $journalEntries->lastPage(); $page++)
            <li class="{{ $page == $journalEntries->currentPage() ? 'active' : '' }}">
                <a href="{{ $journalEntries->appends(request()->query())->url($page) }}">{{ $page }}</a>
            </li>
            @endfor
            @if ($journalEntries->hasMorePages())
            <li><a href="{{ $journalEntries->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a></li>
            @else
            <li class="disabled">&raquo;</li>
            @endif
    </ul>
</div>
@endif
@endsection
