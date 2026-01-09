@extends('layouts.admin')
@section('page-title')
{{__('Manage Credit Notes')}}
@endsection
@push('script-page')
<script>
$(document).on('change', '#invoice', function() {

    var id = $(this).val();
    var url = "{{route('invoice.get')}}";

    $.ajax({
        url: url,
        type: 'get',
        cache: false,
        data: {
            'id': id,

        },
        success: function(data) {
            $('#amount').val(data)
        },

    });

})
</script>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Credit Note')}}</li>
@endsection

@section('action-btn')
<div class="float-end">
    @can('create credit note')
    <a href="#" data-url="{{ route('invoice.custom.credit.note') }}"  data-bs-title="{{__('Create')}}"
        data-ajax-popup="true" data-bs-toggle="{{__('Create New Credit Note')}}" class="btn btn-sm btn-primary">
        Create
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
                    {{ Form::open(['route' => ['credit.note'], 'method' => 'GET', 'id' => 'credit-note_submit']) }}
                    <div class="row d-flex justify-content-end ">

                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
                            </div>
                        </div>

                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="document.getElementById('credit-note_submit').submit(); return false;"
                                 data-bs-title="{{ __('apply') }}">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <a href="{{ route('credit.note') }}" class="btn btn-sm btn-danger" 
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
            <th> {{__('Invoice')}}</th>
            <th> {{__('Customer')}}</th>
            <th> {{__('Date')}}</th>
            <th> {{__('Amount')}}</th>
            <th> {{__('Description')}}</th>
            <th width="10%"> {{__('Action')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($invoices as $invoice)
        @if(!empty($invoice->creditNote))
        @foreach ($invoice->creditNote as $creditNote)
        <tr>
            <td class="Id">
                <a href="{{ route('invoice.show',\Crypt::encrypt($creditNote->invoice)) }}"
                    class="btn btn-outline-primary">{{ AUth::user()->invoiceNumberFormat($invoice->invoice_id) }}</a>
            </td>
            <td>{{ (!empty($invoice->customer)?$invoice->customer->name:'-') }}</td>
            <td>{{ Auth::user()->dateFormat($creditNote->date) }}</td>
            <td>{{ Auth::user()->priceFormat($creditNote->amount) }}</td>
            <td>{{!empty($creditNote->description)?$creditNote->description:'-'}}</td>
            <td>
                @can('edit credit note')
                <div class="action-btn bg-primary ms-2">
                    <a data-url="{{ route('invoice.edit.credit.note',[$creditNote->invoice,$creditNote->id]) }}"
                        data-ajax-popup="true" data-bs-toggle="{{__('Edit Credit Note')}}" href="#"
                        class="mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('Edit')}}"
                        data-bs-title="{{__('Edit')}}">
                        <i class="ti ti-pencil text-white"></i>
                    </a>
                </div>
                @endcan
                @can('edit credit note')
                <div class="action-btn bg-danger ms-2">
                    {!! Form::open(['method' => 'DELETE', 'route' => array('invoice.delete.credit.note',
                    $creditNote->invoice,$creditNote->id),'class'=>'delete-form-btn','id'=>'delete-form-'.$creditNote->id])
                    !!}
                    <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" 
                        data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}"
                        data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}"
                        data-confirm-yes="document.getElementById('delete-form-{{$creditNote->id}}').submit();">
                        <i class="ti ti-trash text-white"></i>
                    </a>
                    {!! Form::close() !!}
                </div>
                @endcan
            </td>
        </tr>
        @endforeach
        @endif
        @endforeach
    </tbody>
</table>
@if($invoices != "")
<div class="pagination">
    <ul>
        @if ($invoices->onFirstPage())
        <li class="disabled">&laquo;</li>
        @else
        <li><a href="{{ $invoices->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif
        @for ($page = 1; $page <= $invoices->lastPage(); $page++)
            <li class="{{ $page == $invoices->currentPage() ? 'active' : '' }}">
                <a href="{{ $invoices->appends(request()->query())->url($page) }}">{{ $page }}</a>
            </li>
            @endfor
            @if ($invoices->hasMorePages())
            <li><a href="{{ $invoices->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a></li>
            @else
            <li class="disabled">&raquo;</li>
            @endif
    </ul>
</div>
@endif
@endsection