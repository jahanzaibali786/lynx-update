@extends('layouts.admin')

@section('page-title')
    {{__('Stock Transfer Note Detail')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{route('dashboard')}}">
            {{__('Dashboard')}}
        </a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{route('stock-transfer-note.index')}}">
            {{__('Stock Transfer Note')}}
        </a>
    </li>
    <li class="breadcrumb-item">
        {{ AUth::user()->stockTransferNoteNumberFormat($invoice->invoice_id) }}
    </li>
@endsection
@php
    $settings = Utility::settings();
@endphp

@push('css-page')
    <style>
        #card-element {
            border: 1px solid #a3afbb !important;
            border-radius: 10px !important;
            padding: 10px !important;
        }
    </style>
@endpush

@push('script-page')

    <script type="text/javascript">
        $('.cp_link').on('click', function () {
            var value = $(this).attr('data-link');
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(value).select();
            document.execCommand("copy");
            $temp.remove();
            show_toastr('success', '{{__('Link Copy on Clipboard')}}', 'success')
        });
  
        $(document).on('click', '#shipping', function () {
            var url = $(this).data('url');
            var is_display = $("#shipping").is(":checked");
            $.ajax( {
                url: url,
                type: 'get',
                data: {
                    'is_display': is_display,
                },
                success: function (data) {
                    // console.log(data);
                }
            });
        })
    </script>
@endpush

@section('content')
    @can('send invoice')
        @if($invoice->status!=4)
            <div class="row">
                <div class="col-1 mb-4 d-flex justify-content-center align-items-center" style="background-color: white; border-radius: 10px">
                    <a href="{{ route('invoice_back',\Crypt::encrypt($invoice->id)) }}" class="text-primary"  data-bs-title="{{__('Back')}}">
                        <svg
                            class="text-primary"
                            fill="#000000"
                            width="40px"
                            height="200px"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path d="m19.2 2.43-2.422-2.43-11.978 12 11.978 12 2.422-2.43-9.547-9.57z"  fill="#100773"/>
                        </svg>
                    </a>
                </div>
                <div class="col-10">
                    <div class="card ">
                        <div class="card-body">
                            <div class="row timeline-wrapper">
                                <div class="col-md-6 col-lg-4 col-xl-4" style="height: 150px">
                                    <div class="timeline-icons">
                                        <span class="timeline-dots">
                                        </span>
                                        <i class="ti ti-plus text-primary">
                                        </i>
                                    </div>
                                    <h6 class="text-primary my-3">
                                        {{__('Create Stock Transfer Note')}}
                                    </h6>
                                    <p class="text-muted text-sm mb-3">
                                        <i class="ti ti-clock mr-2">
                                        </i>
                                        {{__('Created on ')}}{{\Auth::user()->dateFormat($invoice->issue_date)}}
                                    </p>
                                    {{--
                                        @can('edit invoice')
                                            <a
                                                href="{{ route('stock-transfer-note.edit',\Crypt::encrypt($invoice->id)) }}"
                                                class="btn btn-sm btn-primary"
                                                data-bs-title="{{__('Edit')}}"
                                            >
                                                <i class="ti ti-pencil mr-2">
                                                </i>
                                                {{__('Edit')}}
                                            </a>
                                        @endcan
                                    --}}
                                </div>
                                <div class="col-md-6 col-lg-4 col-xl-4" style="height: 150px">
                                    <div class="timeline-icons">
                                        <span class="timeline-dots">
                                        </span>
                                        <i class="ti ti-mail text-warning">
                                        </i>
                                    </div>
                                    <h6 class="text-warning my-3">
                                        {{__('Verify & ')}}{{__('Send Stock Transfer Note')}}
                                    </h6>
                                    <p class="text-muted text-sm mb-3">
                                        @if($invoice->status!=0)
                                            <i class="ti ti-clock mr-2">
                                            </i>
                                            {{__('Sent on')}} {{\Auth::user()->dateFormat($invoice->send_date)}}
                                        @else
                                            @can('send invoice')
                                                <small>
                                                    {{__('Status')}} : {{__('Not Sent')}}
                                                </small>
                                            @endcan
                                        @endif
                                    </p>
                                    @if($invoice->status==0)
                                        @can('send bill')
                                            <a href="{{ route('stock-transfer-note.sent',$invoice->id) }}" class="btn btn-sm btn-warning"  data-bs-title="{{__('Mark Sent')}}">
                                                <i class="ti ti-send mr-2">
                                                </i>
                                                {{__('Send')}}
                                            </a>
                                        @endcan
                                    @endif
                                </div>
                                <div class="col-md-6 col-lg-4 col-xl-4" style="height: 150px">
                                    <div class="timeline-icons">
                                        <span class="timeline-dots">
                                        </span>
                                        <i class="ti ti-report-money text-info">
                                        </i>
                                    </div>
                                    <h6 class="text-info my-3">
                                        {{__('Get Paid')}}
                                    </h6>
                                    <p class="text-muted text-sm mb-3">
                                        {{__('Status')}} : {{__('Awaiting payment')}}
                                    </p>
                                    @if($invoice->status!=0)
                                        @can('create payment invoice')
                                            <a
                                                href="#"
                                                data-url="{{ route('stock-transfer-note.payment',$invoice->id) }}"
                                                data-ajax-popup="true"
                                                data-bs-toggle="{{__('Add Payment')}}"
                                                class="btn btn-sm btn-info"
                                                data-bs-title="{{__('Add Payment')}}"
                                            >
                                                <i class="ti ti-report-money mr-2">
                                                </i>
                                                {{__('Receive Payment')}}
                                            </a>
                                            <br>
                                        @endcan
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-1 mb-4 d-flex justify-content-center align-items-center" style="background-color: white; border-radius: 10px">
                    <a href="{{ route('invoice_next',\Crypt::encrypt($invoice->id)) }}"  data-bs-title="{{__('Next')}}">
                        <svg
                            fill="#000000"
                            width="40px"
                            height="200px"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path d="m4.8 21.57 2.422 2.43 11.978-12-11.978-12-2.422 2.43 9.547 9.57z"  fill="#100773"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endif
    @endcan
    @if ( Gate::check('show invoice'))
        @if($invoice->status!=0)
            <div class="row justify-content-between align-items-center mb-3">
                <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
       
                    <div class="all-button-box">
                        <a href="{{ route('stock-transfer-note.pdf', Crypt::encrypt($invoice->id))}}" target="_blank" class="btn btn-sm btn-primary">
                            {{__('Download')}}
                        </a>
                    </div>
                </div>
            </div>
        @endif
    @endif
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="invoice">
                        <div class="invoice-print">
                            <div class="row invoice-title mt-2">
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12">
                                    <h4>
                                        {{__('Stock Transfer Note')}}
                                    </h4>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 text-end">
                                    <h4 class="invoice-number">
                                        {{ AUth::user()->stockTransferNoteNumberFormat($invoice->invoice_id) }}
                                    </h4>
                                </div>
                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col text-end">
                                    <div class="d-flex align-items-center justify-content-end">
                                        <div class="me-4">
                                            <small>
                                                <strong>
                                                    {{__('Issue Date')}} :
                                                </strong>
                                                <br>
                                                {{\Auth::user()->dateFormat($invoice->issue_date)}}
                                                <br>
                                                <br>
                                            </small>
                                        </div>
                                        <div>
                                            <small>
                                                <strong>
                                                    {{__('Due Date')}} :
                                                </strong>
                                                <br>
                                                {{\Auth::user()->dateFormat($invoice->due_date)}}
                                                <br>
                                                <br>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                
                                <div class="col">
                                    <div class="float-end mt-3">
                                        {!! DNS2D::getBarcodeHTML(route('stock-transfer-note.link.copy',\Illuminate\Support\Facades\Crypt::encrypt($invoice->id)), "QRCODE",2,2) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <small>
                                        <strong>
                                            {{__('Status')}} :
                                        </strong>
                                        <br>
                                        @if($invoice->status == 0)
                                            <span class="badge bg-primary">
                                                {{ __(\App\\Models\\StockTransferNote::\$statues[$invoice->status]) }}
                                            </span>
                                        @elseif($invoice->status == 1)
                                            <span class="badge bg-warning">
                                                {{ __(\App\\Models\\StockTransferNote::\$statues[$invoice->status]) }}
                                            </span>
                                        @elseif($invoice->status == 2)
                                            <span class="badge bg-danger">
                                                {{ __(\App\\Models\\StockTransferNote::\$statues[$invoice->status]) }}
                                            </span>
                                        @elseif($invoice->status == 3)
                                            <span class="badge bg-info">
                                                {{ __(\App\\Models\\StockTransferNote::\$statues[$invoice->status]) }}
                                            </span>
                                        @elseif($invoice->status == 4)
                                            <span class="badge bg-primary">
                                                {{ __(\App\\Models\\StockTransferNote::\$statues[$invoice->status]) }}
                                            </span>
                                        @elseif($invoice->status == 5)
                                            <span class="badge bg-success">
                                                {{ __(\App\Models\StockTransferNote::$statues[$invoice->status]) }}
                                            </span>
                                        @endif
                                    </small>
                                </div>
                                @if(!empty($customFields) && count($invoice->customField)>0)
                                    @foreach($customFields as $field)
                                        <div class="col text-md-right">
                                            <small>
                                                <strong>
                                                    {{$field->name}} :
                                                </strong>
                                                <br>
                                                {{!empty($invoice->customField)?$invoice->customField[$field->id]:'-'}}
                                                <br>
                                                <br>
                                            </small>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="font-weight-bold">
                                        {{__('Product Summary')}}
                                    </div>
                                    <small>
                                        {{__('All items here cannot be deleted.')}}
                                    </small>
                                    <div class="table-responsive mt-2">
                                        <table class="table mb-0 table-striped">
                                            <tr>
                                                <th data-width="40" class="text-dark">
                                                    #
                                                </th>
                                                <th class="text-dark">
                                                    {{__('Product Code')}}
                                                </th>
                                                <th class="text-dark">
                                                    {{__('Product')}}
                                                </th>
                                                <th class="text-dark">
                                                    {{__('Quantity')}}
                                                </th>
                                                <th class="text-dark">
                                                    {{__('Rate')}}
                                                </th>
                                                {{--
                                                    <th class="text-dark">
                                                        {{__('Discount')}}
                                                    </th>
                                                --}}
                                                <th class="text-end text-dark" width="12%">
                                                    {{__('Price')}}
                                                </th>
                                            </tr>
                                            @php
                                                $totalQuantity=0;
                                                $totalRate=0;
                                                $totalTaxPrice=0;
                                                $totalDiscount=0;
                                                $taxesData=[];
                                            @endphp
                                            @foreach($iteams as $key =>$iteam)
                                                @php
                                                    $totalQuantity+=$iteam->quantity;
                                                    $totalRate+=$iteam->price;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        {{$key+1}}
                                                    </td>
                                                    <td>
                                                        {{!empty($iteam->product())?$iteam->product()->sku:''}}
                                                    </td>
                                                    <td>
                                                        {{!empty($iteam->product())?$iteam->product()->name:''}}
                                                    </td>
                                                    <td>
                                                        {{$iteam->quantity}}
                                                    </td>
                                                    <td>
                                                        {{\Auth::user()->priceFormat($iteam->price)}}
                                                    </td>
                                                    {{--
                                                        <td>
                                                            {{\Auth::user()->priceFormat($iteam->discount)}}
                                                        </td>
                                                    --}}
                                                    <td class="text-end">
                                                        {{\Auth::user()->priceFormat($iteam->price * $iteam->quantity)}}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tfoot>
                                                <tr>
                                                    <td>
                                                    </td>
                                                    <td>
                                                        <b>
                                                            {{__('Total')}}
                                                        </b>
                                                    </td>
                                                    <td>
                                                    </td>
                                                    <td>
                                                        <b>
                                                            {{$totalQuantity}}
                                                        </b>
                                                    </td>
                                                    <td>
                                                        <b>
                                                            {{\Auth::user()->priceFormat($totalRate)}}
                                                        </b>
                                                    </td>
                                                    {{--
                                                        <td>
                                                            <b>
                                                                {{\Auth::user()->priceFormat($totalDiscount)}}
                                                            </b>
                                                        </td>
                                                    --}}
                                                    <td>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">
                                                    </td>
                                                    <td class="text-end">
                                                        <b>
                                                            {{__('Sub Total')}}
                                                        </b>
                                                    </td>
                                                    <td class="text-end">
                                                        {{\Auth::user()->priceFormat($invoice->getSubTotal())}}
                                                    </td>
                                                </tr>
                                                {{--
                                                    <tr>
                                                        <td colspan="6">
                                                        </td>
                                                        <td class="text-end">
                                                            <b>
                                                                {{__('Discount')}}
                                                            </b>
                                                        </td>
                                                        <td class="text-end">
                                                            {{\Auth::user()->priceFormat($invoice->getTotalDiscount())}}
                                                        </td>
                                                    </tr>
                                                --}}
                                                <tr>
                                                    <td colspan="4">
                                                    </td>
                                                    <td class="blue-text text-end">
                                                        <b>
                                                            {{__('Total')}}
                                                        </b>
                                                    </td>
                                                    <td class="blue-text text-end">
                                                        {{\Auth::user()->priceFormat($invoice->getTotal())}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4">
                                                    </td>
                                                    <td class="text-end">
                                                        <b>
                                                            {{__('Paid')}}
                                                        </b>
                                                    </td>
                                                    <td class="text-end">
                                                        {{\Auth::user()->priceFormat(($invoice->getTotal()-$invoice->getDue())-($invoice->invoiceTotalCreditNote()))}}
                                                    </td>
                                                </tr>
                                                {{--
                                                    <tr>
                                                        <td colspan="6">
                                                        </td>
                                                        <td class="text-end">
                                                            <b>
                                                                {{__('Credit Note')}}
                                                            </b>
                                                        </td>
                                                        <td class="text-end">
                                                            {{\Auth::user()->priceFormat(($invoice->invoiceTotalCreditNote()))}}
                                                        </td>
                                                    </tr>
                                                --}}
                                                <tr>
                                                    <td colspan="4">
                                                    </td>
                                                    <td class="text-end">
                                                        <b>
                                                            {{__('Due')}}
                                                        </b>
                                                    </td>
                                                    <td class="text-end">
                                                        {{\Auth::user()->priceFormat($invoice->getDue())}}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
