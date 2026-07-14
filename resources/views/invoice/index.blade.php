@extends('layouts.admin')
@section('page-title')
{{__('Manage Invoices')}}
@endsection
@push('script-page')
<script>
function copyToClipboard(element) {

    var copyText = element.id;
    navigator.clipboard.writeText(copyText);
    // document.addEventListener('copy', function (e) {
    //     e.clipboardData.setData('text/plain', copyText);
    //     e.preventDefault();
    // }, true);
    //
    // document.execCommand('copy');
    show_toastr('success', 'Url copied to clipboard', 'success');
}
</script>
<script>

    function branchstore(id) {
        var branch = id;
        $.ajax({
            url: '{{route('branch.store')}}',
            type: 'POST',
            data: {
                "branch_id": branch, "_token": "{{ csrf_token() }}",
            },
            success: function (data) {

                $('#store').empty();
                // $('#store').append('<option value="">{{__('Select Store')}}</option>');

                for (let index = 0; index < data.length; index++) {
                    $('#store').append('<option value="' + data[index]['id'] + '">' + data[index]['name'] +'</option>');
                }
            }
        });
    }

    document.getElementById('branchstore').addEventListener('change', function() {
        var id = this.value;
        branchstore(id);
    });



</script>
@endpush


@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Invoice')}}</li>
@endsection

@section('action-btn')
<div class="float-end">
           {{-- <a class="btn btn-sm btn-primary" data-bs-toggle="collapse" href="#multiCollapseExample1" role="button" aria-expanded="false" aria-controls="multiCollapseExample1"  data-bs-title="{{__('Filter')}}">
               Filters
           </a> --}}

    {{-- <a href="{{ route('invoice.export') }}" class="btn mx-1 btn-sm btn-outline-primary" 
        data-bs-title="{{__('Export')}}">
        <span class="btn-inner--icon">Export</span>
    </a> --}}

    @can('create invoice')
    <a href="#" data-url="{{ route('invoice.create', 0) }}" data-size="modal-fullscreen" data-ajax-popup="true" data-bs-title="{{ __('Create Invoice') }}" class="btn mx-1 btn-sm btn-outline-primary">
        <span class="btn-inner--icon">Create</span>
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
                    {{ Form::open(['route' => ['invoice.index'], 'method' => 'GET', 'id' => 'customer_submit']) }}
                    <div class="row d-flex align-items-center justify-content-end">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('issue_date', __('Issue Date'),['class'=>'form-label'])}}
                                {{ Form::date('issue_date', isset($_GET['issue_date'])?$_GET['issue_date']:'', array('class' => 'form-control month-btn','id'=>'pc-daterangepicker-1')) }}
                            </div>
                        </div>
                        @if(\Auth::user()->type == 'company')
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'onchange' => 'branchstore(this.value)']) }}
                                </div>
                            </div>
                        @endif
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('store', __('Store'),['class'=>'form-label'])}}
                                {{ Form::select('store', $class ?? [], isset($_GET['store']) ? $_GET['store'] : '', ['class' => 'form-control select', 'id' => 'store']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="btn-box">
                                {{ Form::label('status', __('Status'),['class'=>'form-label'])}}
                                {{ Form::select('status', [''=>'Select Status'] + $status,isset($_GET['status'])?$_GET['status']:'', array('class' => 'form-control select')) }}
                            </div>
                        </div>
                        <div class="col-auto float-end ms-2 mt-4">
                            <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                onclick="document.getElementById('customer_submit').submit(); return false;"
                                 data-bs-title="{{ __('apply') }}">
                                <span class="btn-inner--icon">Search</span>
                            </a>
                            <a href="{{ route('invoice.index') }}" class="btn mx-1 btn-sm btn-outline-danger" 
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

<table class="">
    <thead>
        <tr class="table_heads">
            <th>{{ __('S.No') }}</th>
            <th> {{ __('Invoice') }}</th>
            <th> {{ __('Store From') }}</th>
            <th> {{ __('Store To') }}</th>
            {{--                                @if (!\Auth::guard('customer')->check())--}}
            {{--                                    <th>{{ __('Customer') }}</th>--}}
            {{--                                @endif--}}
            <th>{{ __('Issue Date') }}</th>
            <th>{{ __('Due Date') }}</th>
            <th>{{ __('Due Amount') }}</th>
            <th>{{ __('Status') }}</th>
            @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
            <th>{{ __('Action') }}</th>
            @endif
            {{-- <th>
                                <td class="barcode">
                                    {!! DNS1D::getBarcodeHTML($invoice->sku, "C128",1.4,22) !!}
                                    <p class="pid">{{$invoice->sku}}</p>
            </td>
            </th> --}}
        </tr>
    </thead>

    <tbody>
        @foreach ($invoices as $invoice)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td class="Id">
                <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                    class="btn btn-outline-primary btnpurchase1">{{ AUth::user()->invoiceNumberFormat($invoice->invoice_id) }}</a>
            </td>
            <td>{{ @$invoice->store_from->name}}</td>
            <td>{{ @$invoice->store_to->name}}</td>
            <td>{{ Auth::user()->dateFormat($invoice->issue_date) }}</td>
            <td>
                @if ($invoice->due_date < date('Y-m-d')) <p class="text-danger mt-3">
                    {{ \Auth::user()->dateFormat($invoice->due_date) }}</p>
                    @else
                    {{ \Auth::user()->dateFormat($invoice->due_date) }}
                    @endif
            </td>
            <td>{{ \Auth::user()->priceFormat($invoice->getDue()) }}</td>
            <td>
                @if ($invoice->status == 0)
                <span
                    class="status_badge badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                @elseif($invoice->status == 1)
                <span
                    class="status_badge badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                @elseif($invoice->status == 2)
                <span
                    class="status_badge badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                @elseif($invoice->status == 3)
                <span
                    class="status_badge badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                @elseif($invoice->status == 4)
                <span
                    class="status_badge badge bg-primary p-2 px-3 rounded">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                @endif
            </td>
            @if (Gate::check('edit invoice') || Gate::check('delete invoice') || Gate::check('show invoice'))
           <td class="Action">

            <div class="action-btn ms-2">
                    @php $invoiceID= Crypt::encrypt($invoice->id); @endphp
                        <a href="#"
                            data-url="{{ route('invoice.edit', \Crypt::encrypt($invoice->id)) }}"
                            data-size="modal-fullscreen" data-ajax-popup="true"
                            class="mx-3 btn btn-sm btn-outline-primary align-items-center"
                            data-bs-title="{{ __('Edit') }}">
                            <i class="ti ti-pencil text-white"></i>
                        </a>
                    @can('copy invoice')
                        <a href="#" id="{{ route('invoice.link.copy',[$invoiceID]) }}"
                            class="mx-3 btn btn-sm align-items-center" onclick="copyToClipboard(this)"
                             data-bs-title="{{__('Copy Invoice')}}">
                            <i class="ti ti-link text-white"></i></a>
                    @endcan
                    {{-- @can('duplicate invoice')
                    <div class="action-btn bg-primary ms-2">
                        {!! Form::open(['method' => 'get', 'route' => ['invoice.duplicate', $invoice->id], 'id' =>
                        'duplicate-form-' . $invoice->id]) !!}

                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" 
                            data-bs-title="{{ __('Duplicate') }}" 
                            title="Duplicate Invoice" data-bs-title="{{ __('Delete') }}"
                            data-confirm="You want to confirm this action. Press Yes to continue or Cancel to go back"
                            data-confirm-yes="document.getElementById('duplicate-form-{{ $invoice->id }}').submit();">
                            <i class="ti ti-copy text-white"></i>
                            {!! Form::open(['method' => 'get', 'route' => ['invoice.duplicate', $invoice->id], 'id' =>
                            'duplicate-form-' . $invoice->id]) !!}
                            {!! Form::close() !!}
                        </a>
                    </div>
                    @endcan --}}
                    @can('show invoice')
                        {{-- @if (\Auth::guard('customer')->check())
                            <div class="action-btn bg-info ms-2">
                                    <a href="{{ route('customer.invoice.show', \Crypt::encrypt($invoice->id)) }}"
                                        class="mx-3 btn btn-sm align-items-center"  title="Show "
                                        data-bs-title="{{ __('Detail') }}">
                                        <i class="ti ti-eye text-white"></i>
                                    </a>
                                </div>
                        @else --}}
                        {{-- <a href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}"
                            class="mx-1 btn mx-1 btn-sm btn-outline-infi align-items-center"  title="Show "
                            data-bs-title="{{ __('Detail') }}">
                            <i class="ti ti-eye text-white"></i>
                        </a> --}}
                        <a  data-bs-title="{{ __('Show') }}" href="{{ route('invoice.show', \Crypt::encrypt($invoice->id)) }}" class="btn mx-1 btn-sm btn-outline-info"><span class="btn-inner--icon"><i
                            class="fas fa-eye"></i></span></a>

                    @endcan
                    {{-- @can('edit invoice')
                    
                    @endcan
                    @can('delete invoice')
                    <div class="action-btn bg-danger ms-2">
                        {!! Form::open(['method' => 'DELETE', 'route' => ['invoice.destroy', $invoice->id], 'id' =>
                        'delete-form-' . $invoice->id]) !!}
                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para " 
                            data-bs-title="{{__('Delete')}}" data-bs-title="{{ __('Delete') }}"
                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                            data-confirm-yes="document.getElementById('delete-form-{{ $invoice->id }}').submit();">
                            <i class="ti ti-trash text-white"></i>
                        </a>
                        {!! Form::close() !!}
                    </div> 
                    @endcan --}}
                </div>
            </td> 
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
