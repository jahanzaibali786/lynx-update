@extends('layouts.admin')
@section('page-title')
    {{__('Manage Tax Rate')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Taxes')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        @can('create constant tax')
            <a href="#" data-url="{{ route('taxes.create') }}" data-ajax-popup="true"  data-bs-title="{{__('Create')}}"  class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-3">
            @include('layouts.account_setup')
        </div>
        <div class="col-9">

            <div class="table-responsive">
                <table class="">
                            <thead>
                            <tr class="table_heads">
                                <th> {{__('Tax Name')}}</th>
                                <th> {{__('Rate %')}}</th>
                                <th style="width:150px;"> {{__('Account Income')}}</th>
                                <th style="width:150px;"> {{__('Account Expence')}}</th>
                                <th width="10%"> {{__('Action')}}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($taxes as $taxe)
                                <tr class="font-style">
                                    <td>{{ $taxe->name }}</td>
                                    <td>{{ $taxe->rate }}</td>
                                    <td style="width:150px;">{{ @$taxe->account_incomes->name }}</td>
                                    <td style="width:150px;">{{ @$taxe->account_expances->name }}</td>
                                    <td class="Action">
                                        <span>
                                            <div class="action-btn  ms-2">
                                                @can('edit constant tax')
                                                    <a href="#" class="mx-1 btn-outline-primary btn btn-sm align-items-center" data-url="{{ route('taxes.edit',$taxe->id) }}" data-ajax-popup="true"  data-bs-title="{{__('Edit')}}">
                                                        <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                                    </a>
                                                @endcan

                                                {{-- @can('delete constant tax')
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['taxes.destroy', $taxe->id],'id'=>'delete-form-'.$taxe->id]) !!}
                                                        <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"  data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}" data-confirm-yes="document.getElementById('delete-form-{{$taxe->id}}').submit();">
                                                           <span class="btn-inner--icon"> <i class="ti ti-trash"></i></span>
                                                        </a>
                                                    {!! Form::close() !!}
                                                @endcan --}}
                                            </div>
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                        @if($taxes != "")
<div class="pagination">
    <ul>
        @if ($taxes->onFirstPage())
        <li class="disabled">&laquo;</li>
        @else
        <li><a href="{{ $taxes->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif
        @for ($page = 1; $page <= $taxes->lastPage(); $page++)
            <li class="{{ $page == $taxes->currentPage() ? 'active' : '' }}">
                <a href="{{ $taxes->appends(request()->query())->url($page) }}">{{ $page }}</a>
            </li>
            @endfor
            @if ($taxes->hasMorePages())
            <li><a href="{{ $taxes->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a></li>
            @else
            <li class="disabled">&raquo;</li>
            @endif
    </ul>
</div>
@endif
        </div>
    </div>
@endsection
