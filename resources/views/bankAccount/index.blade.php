@extends('layouts.admin')
@section('page-title')
{{__('Manage Bank Account')}}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Bank Account')}}</li>
@endsection


@section('action-btn')
<div class="float-end">
    @can('create bank account')
    <a href="#" data-url="{{ route('bank-account.create') }}" data-ajax-popup="true" data-size="lg"
         title="{{__('Create')}}" data-title="{{__('Create New Bank Account')}}"
        class="btn mx-1 btn-sm btn-outline-primary">
        <span class="btn-inner--icon">Create</span>
    </a>
    @endcan
</div>
@endsection

@section('content')
<div class="" style="margin-top: 25px;">
    {{-- <div class="card-body">
        <form action="" class="row float-end" style="align-items: baseline">
            <div class="form-group col-12 col-md-8">
                <input type="search" name="search" id="" class="form-control" placeholder="Search" value="{{$search}}">
            </div>
            <button class="btn mx-1 btn-sm btn-outline-primary col-12 col-md-3" style="height: 35px;">Search</button>
        </form>
    </div> --}}
</div>

<table class="datatable">
    <thead>
        <tr class="table_heads">
            <th>#</th>
            <th>{{__('Chart Of Account')}}</th>
            <th>{{__('Name')}}</th>
            <th>{{__('Type')}}</th>
            <th>{{__('Bank')}}</th>
            <th>{{__('Account Number')}}</th>
            <th>{{__('Current Balance')}}</th>
            <th>{{__('Contact Number')}}</th>
            <th>{{__('Bank Branch')}}</th>
            <th width="10%">{{__('Action')}}</th>
        </tr>
    </thead>
    <tbody id="tableBody">
        @foreach ($accounts as $account)
        <tr class="font-style">
            <td>{{ $loop->iteration }}</td>
            <td>{{ (!empty($account->chartAccount)?$account->chartAccount->name :'-') }}</td>
            <td>{{ $account->holder_name }}</td>
            <td>{{ $account->type == 'head_imprest' ? 'Head Imprest' : ucfirst($account->type ?? 'normal') }}</td>
            <td>{{ $account->bank_name }}</td>
            <td>{{ $account->account_number }}</td>
            <td>{{ \Auth::user()->priceFormat($account->opening_balance) }}</td>
            <td>{{ $account->contact_number }}</td>
            <td>{{ @$account->branch->name }}</td>
            @if(Gate::check('edit bank account') || Gate::check('delete bank account'))
            <td class="Action">
                {{-- <span> --}}
                    <div class="action-btn ms-2">
                    @if($account->holder_name!='Cash')
                        <a href="{{ route('bank-account.statement', $account->id) }}" class="mx-1 btn btn-sm btn-outline-info align-items-center"
                            data-bs-title="{{__('Statement')}}" title="{{__('Statement')}}">
                            <span class="btn-inner--icon"><i class="ti ti-file-invoice"></i></span>
                        </a>
                    @can('edit bank account')
                        <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center"
                            data-url="{{ route('bank-account.edit',$account->id) }}" data-ajax-popup="true"
                            data-bs-title="{{__('Edit')}}" title="{{__('Edit Bank Account')}}"
                             data-size="lg">
                            <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                        </a>
                    @endcan
                    @can('delete bank account')
                        {!! Form::open(['method' => 'DELETE', 'route' => ['bank-account.destroy',
                        $account->id],'id'=>'delete-form-'.$account->id]) !!}
                        <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"
                             data-bs-title="{{__('Delete')}}"
                            data-bs-title="{{__('Delete')}}"
                            data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}"
                            data-confirm-yes="document.getElementById('delete-form-{{$account->id}}').submit();">
                            <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                        </a>
                        {!! Form::close() !!}
                    @endcan
                    @else
                    -
                    @endif
                    </div>
                {{-- </span> --}}
            </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>

      {{-- @if ($accounts->hasPages())
    <div class="pagination">
        <ul>
            @if ($accounts->onFirstPage())
                <li class="disabled">&laquo; Previous</li>
            @else
                <li><a href="{{ $accounts->appends(request()->query())->previousPageUrl() }}"
                        rel="prev">&laquo; Previous</a></li>
            @endif
            @if ($accounts->currentPage() > 1)
                <li><a href="{{ $accounts->appends(request()->query())->url(1) }}">First</a></li>
            @endif
            @php
                $currentPage = $accounts->currentPage();
                $lastPage = $accounts->lastPage();
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
                <li class="{{ $page == $accounts->currentPage() ? 'active' : '' }}">
                    <a href="{{ $accounts->appends(request()->query())->url($page) }}">{{ $page }}</a>
                </li>
            @endfor
            @if ($accounts->hasMorePages())
                <li><a href="{{ $accounts->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                        &raquo;</a></li>
            @else
                <li class="disabled">Next &raquo;</li>
            @endif
            @if ($accounts->currentPage() < $accounts->lastPage())
                <li><a
                        href="{{ $accounts->appends(request()->query())->url($accounts->lastPage()) }}">Last</a>
                </li>
            @endif
        </ul>
    </div>
@endif --}}

@endsection

