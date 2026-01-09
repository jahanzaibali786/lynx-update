@extends('layouts.admin')
@php
// $profile=asset(Storage::url('uploads/avatar/'));
$profile=\App\Models\Utility::get_file('uploads/avatar/');
@endphp
@push('script-page')
<script>
$(document).on('click', '#billing_data', function() {
    $("[name='shipping_name']").val($("[name='billing_name']").val());
    $("[name='shipping_country']").val($("[name='billing_country']").val());
    $("[name='shipping_state']").val($("[name='billing_state']").val());
    $("[name='shipping_city']").val($("[name='billing_city']").val());
    $("[name='shipping_phone']").val($("[name='billing_phone']").val());
    $("[name='shipping_zip']").val($("[name='billing_zip']").val());
    $("[name='shipping_address']").val($("[name='billing_address']").val());
})
</script>
@endpush
@section('page-title')
{{__('Manage Customers')}}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Customer')}}</li>
@endsection

@section('action-btn')
<div class="float-end">
    <a href="#" data-size="md"  data-bs-title="{{__('Import')}}"
        data-url="{{ route('customer.file.import') }}" data-ajax-popup="true"
        data-bs-toggle="{{__('Import customer CSV file')}}" class="btn btn-sm btn-primary">
        <i class="ti ti-file-import"></i>
    </a>
    <a href="{{route('customer.export')}}"  data-bs-title="{{__('Export')}}"
        class="btn btn-sm btn-primary">
        Export
    </a>

    <a href="#" data-size="lg" data-url="{{ route('customer.create') }}" data-ajax-popup="true" 
        data-bs-title="{{__('Create')}}" data-bs-toggle="{{__('Create Customer')}}" class="btn btn-sm btn-primary">
        Create
    </a>
</div>
@endsection

@section('content')
@if(\Auth::user()->type == 'company')
<div class="row">
    <div class="col-sm-12">
        <div class="mt-2 " id="multiCollapseExample1">
            <div class="card">
                <div class="card-body">
                    {{ Form::open(['route' => ['customer.index'], 'method' => 'GET', 'id' => 'customer_submit']) }}
                    <div class="row d-flex justify-content-end ">

                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
                            </div>
                        </div>

                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="document.getElementById('customer_submit').submit(); return false;"
                                 data-bs-title="{{ __('apply') }}">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <a href="{{ route('customer.index') }}" class="btn btn-sm btn-danger" 
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
            <th>#</th>
            <th> {{__('Name')}}</th>
            <th> {{__('Contact')}}</th>
            <th> {{__('Email')}}</th>
            <th> {{__('Balance')}}</th>
            <th>{{__('Action')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($customers as $k=>$customer)
        <tr class="cust_tr" id="cust_detail" data-url="{{route('customer.show',$customer['id'])}}"
            data-id="{{$customer['id']}}">
            <td class="Id">
                @can('show customer')
                <a href="{{ route('customer.show',\Crypt::encrypt($customer['id'])) }}" class="btn btn-outline-primary">
                    {{ AUth::user()->customerNumberFormat($customer['customer_id']) }}
                </a>
                @else
                <a href="#" class="btn btn-outline-primary">
                    {{ AUth::user()->customerNumberFormat($customer['customer_id']) }}
                </a>
                @endcan
            </td>
            <td class="font-style">{{$customer['name']}}</td>
            <td>{{$customer['contact']}}</td>
            <td>{{$customer['email']}}</td>
            <td>{{\Auth::user()->priceFormat($customer['balance'])}}</td>
            <td class="Action">
                <span>
                    @if($customer['is_active']==0)
                    <i class="ti ti-lock" title="Inactive"></i>
                    @else
                    @can('show customer')
                    <div class="action-btn bg-info ms-2">
                        <a href="{{ route('customer.show',\Crypt::encrypt($customer['id'])) }}"
                            class="mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('View')}}">
                            <i class="ti ti-eye text-white text-white"></i>
                        </a>
                    </div>
                    @endcan
                    @can('edit customer')
                    <div class="action-btn bg-primary ms-2">
                        <a href="#" class="mx-3 btn btn-sm  align-items-center"
                            data-url="{{ route('customer.edit',$customer['id']) }}" data-ajax-popup="true"
                            data-size="lg"  data-bs-title="{{__('Edit')}}"
                            data-bs-toggle="{{__('Edit Customer')}}">
                            <i class="ti ti-pencil text-white"></i>
                        </a>
                    </div>
                    @endcan
                    @can('delete customer')
                    <div class="action-btn bg-danger ms-2">
                        {!! Form::open(['method' => 'DELETE', 'route' => ['customer.destroy',
                        $customer['id']],'id'=>'delete-form-'.$customer['id']]) !!}
                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" 
                            data-bs-title="{{__('Delete')}}"><i class="ti ti-trash text-white text-white"></i></a>
                        {!! Form::close() !!}
                    </div>
                    @endcan
                    @endif
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@if($customers != "")
<div class="pagination">
    <ul>
        @if ($customers->onFirstPage())
        <li class="disabled">&laquo;</li>
        @else
        <li><a href="{{ $customers->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif
        @for ($page = 1; $page <= $customers->lastPage(); $page++)
            <li class="{{ $page == $customers->currentPage() ? 'active' : '' }}">
                <a href="{{ $customers->appends(request()->query())->url($page) }}">{{ $page }}</a>
            </li>
            @endfor
            @if ($customers->hasMorePages())
            <li><a href="{{ $customers->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a></li>
            @else
            <li class="disabled">&raquo;</li>
            @endif
    </ul>
</div>
@endif
@endsection