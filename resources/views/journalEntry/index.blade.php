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
                                <th> {{__('Date')}}</th>
                                <th> {{__('Amount')}}</th>
                                <th class="wrap-td"> {{__('Description')}}</th>
                                <th width="10%"> {{__('Action')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($journalEntries as $journalEntry)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="Id">
                                        <a href="{{ route('journal-entry.show',$journalEntry->id) }}" class="btn btnpurchase1 btn-outline-primary">{{ AUth::user()->journalNumberFormat($journalEntry->journal_id) }}</a>
                                    </td>
                                    <td>{{ Auth::user()->dateFormat($journalEntry->date) }}</td>
                                    <td>
                                        {{ \Auth::user()->priceFormat($journalEntry->totalCredit())}}
                                    </td>
                                    <td class="wrap-td">{{!empty($journalEntry->description)?$journalEntry->description:'-'}}</td>
                                     <td>
                                                <div class="action-btn ms-2">
                                        @can('edit journal entry')
                                            
                                                <a title="{{__('Edit Journal')}}" href="{{ route('journal-entry.edit',[$journalEntry->id]) }}" class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center"  data-bs-title="{{__('Edit')}}" data-bs-title="{{__('Edit')}}">
                                                   <span class="btn-inner--icon"> <i class="ti ti-pencil"></i> </span>
                                                </a>
                                        @endcan
                                        @can('delete journal entry')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => array('journal-entry.destroy', $journalEntry->id),'id'=>'delete-form-'.$journalEntry->id]) !!}

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
