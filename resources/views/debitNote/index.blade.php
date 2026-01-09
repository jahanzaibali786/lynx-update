@extends('layouts.admin')
@section('page-title')
{{__('Manage Debit Notes')}}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Debit Note')}}</li>
@endsection
@push('script-page')
<script>
$(document).on('change', '#bill', function() {

    var id = $(this).val();
    var url = "{{route('bill.get')}}";

    $.ajax({
        url: url,
        type: 'get',
        cache: false,
        data: {
            'bill_id': id,

        },
        success: function(data) {
            $('#amount').val(data)
        },

    });

})
</script>
@endpush

@section('action-btn')
<div class="float-end">
    @can('create debit note')
    <a href="#" data-url="{{ route('bill.custom.debit.note') }}" data-ajax-popup="true"
        data-bs-toggle="{{__('Create New Debit Note')}}"  data-bs-title="{{__('Create')}}"
        class="btn btn-sm btn-primary">
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
                    {{ Form::open(['route' => ['debit.note'], 'method' => 'GET', 'id' => 'debit-note_submit']) }}
                    <div class="row d-flex justify-content-end ">

                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'), ['class' => 'form-label'])}}
                                {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'onchange' => 'branchtype(this.value)']) }}
                            </div>
                        </div>

                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn btn-sm btn-primary"
                                onclick="document.getElementById('debit-note_submit').submit(); return false;"
                                 data-bs-title="{{ __('apply') }}">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <a href="{{ route('debit.note') }}" class="btn btn-sm btn-danger" 
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
            <th> {{__('Bill')}}</th>
            <th> {{__('Vendor')}}</th>
            <th> {{__('Date')}}</th>
            <th> {{__('Amount')}}</th>
            <th> {{__('Description')}}</th>
            <th width="10%"> {{__('Action')}}</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($bills as $bill)
        @if(!empty($bill->debitNote))
        @foreach ($bill->debitNote as $debitNote)

        <tr class="font-style">
            <td class="Id">
                <a href="{{ route('bill.show', \Crypt::encrypt($debitNote->bill)) }}"
                    class="btn btn-outline-primary">{{ AUth::user()->billNumberFormat($bill->bill_id) }}

                </a>
            </td>
            <td>{{ (!empty($bill->vender) ? $bill->vender->name : '-') }}</td>
            <td>{{ Auth::user()->dateFormat($debitNote->date) }}</td>
            <td>{{ Auth::user()->priceFormat($debitNote->amount) }}</td>
            <td>{{!empty($debitNote->description) ? $debitNote->description : '-'}}</td>
            <td class="Action">
                <span>
                    @can('edit debit note')
                    <div class="action-btn bg-primary ms-2">
                        <a data-url="{{ route('bill.edit.debit.note', [$debitNote->bill, $debitNote->id]) }}"
                            data-ajax-popup="true" data-bs-toggle="{{__('Edit Debit Note')}}" href="#"
                            class="mx-3 btn btn-sm align-items-center"  data-bs-title="{{__('Edit')}}"
                            data-bs-title="{{__('Edit')}}">
                            <i class="ti ti-pencil text-white"></i>
                        </a>
                    </div>
                    @endcan
                    @can('edit debit note')
                    <div class="action-btn bg-danger ms-2">
                        {!! Form::open(['method' => 'DELETE', 'route' => array('bill.delete.debit.note',
                        $debitNote->bill, $debitNote->id), 'id' => 'delete-form-' . $debitNote->id]) !!}

                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" 
                            data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}"
                            data-confirm="{{__('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?')}}"
                            data-confirm-yes="document.getElementById('delete-form-{{$debitNote->id}}').submit();">
                            <i class="ti ti-trash text-white"></i>
                        </a>
                        {!! Form::close() !!}
                    </div>
                    @endcan
                </span>
            </td>
        </tr>
        @endforeach
        @endif
        @endforeach
    </tbody>
</table>
@if($bills != "")
<div class="pagination">
    <ul>
        @if ($bills->onFirstPage())
        <li class="disabled">&laquo;</li>
        @else
        <li><a href="{{ $bills->appends(request()->query())->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif
        @for ($page = 1; $page <= $bills->lastPage(); $page++)
            <li class="{{ $page == $bills->currentPage() ? 'active' : '' }}">
                <a href="{{ $bills->appends(request()->query())->url($page) }}">{{ $page }}</a>
            </li>
            @endfor
            @if ($bills->hasMorePages())
            <li><a href="{{ $bills->appends(request()->query())->nextPageUrl() }}" rel="next">&raquo;</a></li>
            @else
            <li class="disabled">&raquo;</li>
            @endif
    </ul>
</div>
@endif
@endsection