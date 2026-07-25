@extends('layouts.admin')
@section('page-title')
    {{__('Purchase Detail')}}
@endsection

@php
    $settings = Utility::settings();
@endphp
@push('script-page')
    <script>
        $(document).on('click', '#shipping', function () {
            var url = $(this).data('url');
            var is_display = $("#shipping").is(":checked");
            $.ajax({
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
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item"><a href="{{route('purchase.index')}}">{{__('Purchase')}}</a></li>
    <li class="breadcrumb-item">{{ Auth::user()->purchaseNumberFormat($purchase->purchase_id) }}</li>
@endsection

@section('content')

    @can('send purchase')
        @if($purchase->status!=4)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row timeline-wrapper">
                                <div class="col-md-3" style="height: 150px">
                                    <div class="timeline-step h-100">
                                        <div class="timeline-content">
                                            <div class="timeline-icons"><span class="timeline-dots"></span>
                                                <i class="ti ti-plus text-primary"></i>
                                            </div>
                                            <h6 class="text-primary my-3">{{__('Create Purchase')}}</h6>
                                            <p class="text-muted text-sm mb-3"><i class="ti ti-clock mr-2"></i>{{__('Created on ')}}{{\Auth::user()->dateFormat($purchase->purchase_date)}}</p>
                                            <div class="timeline-action">
                                                @can('edit purchase' && $purchase->status != 7)
                                                    <a href="{{ route('purchase.edit',\Crypt::encrypt($purchase->id)) }}" class="btn mx-1 btn-sm btn-outline-primary" data-bs-title="{{__('Edit')}}"><span class="btn-inner--icon"><i class="ti ti-pencil mr-2"></i></span>{{__('Edit')}}</a>
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3" style="height: 150px">
                                    <div class="timeline-step h-100">
                                        <div class="timeline-content">
                                            <div class="timeline-icons"><span class="timeline-dots"></span>
                                                <i class="ti ti-mail-forward text-warning"></i>
                                            </div>
                                            <h6 class="text-warning my-3">{{__('Fw to Ho')}}</h6>
                                            <p class="text-muted text-sm mb-3">
                                                @if($purchase->status >= 5)
                                                    <i class="ti ti-clock mr-2"></i>{{__('Forwarded on')}} {{\Auth::user()->dateFormat($purchase->updated_at)}}
                                                @elseif($purchase->status == 0)
                                                    <small>{{__('Status')}} : {{__('Draft')}}</small>
                                                @else
                                                    <small>{{__('Status')}} : {{__('Not Forwarded')}}</small>
                                                @endif
                                            </p>
                                            <div class="timeline-action">
                                                @if($purchase->status == 0)
                                                    @can('send purchase')
                                                    <a href="{{ route('purchase.fw_to_ho', $purchase->id) }}" class="btn mx-1 btn-sm btn-outline-warning">
                                                        <span class="btn-inner--icon"><i class="ti ti-mail-forward mr-2"></i></span>{{__('Fw to Ho')}}
                                                    </a>
                                                    @endcan
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3" style="height: 150px">
                                    <div class="timeline-step h-100">
                                        <div class="timeline-content">
                                            <div class="timeline-icons"><span class="timeline-dots"></span>
                                                @if($purchase->status >= 6)
                                                    <i class="ti ti-checks text-success"></i>
                                                @elseif($purchase->status == 5 && \Auth::user()->type == 'company')
                                                    <i class="ti ti-hourglass-empty text-info"></i>
                                                @else
                                                    <i class="ti ti-clock text-muted"></i>
                                                @endif
                                            </div>
                                            @if(\Auth::user()->type == 'company')
                                                <h6 class="my-3 @if($purchase->status >= 6) text-success @elseif($purchase->status == 5) text-info @else text-muted @endif">
                                                    {{__('Approval')}}
                                                </h6>
                                            @else
                                                <h6 class="my-3 @if($purchase->status >= 6) text-success @else text-muted @endif">
                                                    @if($purchase->status == 5) {{__('Under Approval')}} @else {{__('Approval')}} @endif
                                                </h6>
                                            @endif
                                            <p class="text-muted text-sm mb-3">
                                                @if($purchase->status >= 6)
                                                    <i class="ti ti-clock mr-2"></i>{{__('Finalized')}}
                                                @elseif($purchase->status == 5 && \Auth::user()->type == 'company')
                                                    <small>{{__('Pending your decision')}}</small>
                                                @elseif($purchase->status == 5)
                                                    <small>{{__('Under review at Head Office')}}</small>
                                                @else
                                                    <small>{{__('Awaiting forwarding')}}</small>
                                                @endif
                                            </p>
                                            <div class="timeline-action">
                                                @if($purchase->status == 5 && \Auth::user()->type == 'company')
                                                    <a href="{{ route('purchase.finalize', $purchase->id) }}" class="btn mx-1 btn-sm btn-outline-success">
                                                        <span class="btn-inner--icon"><i class="ti ti-check mr-2"></i></span>{{__('Finalize')}}
                                                    </a>
                                                    <a href="{{ route('purchase.reject', $purchase->id) }}" class="btn mx-1 btn-sm btn-outline-danger">
                                                        <span class="btn-inner--icon"><i class="ti ti-x mr-2"></i></span>{{__('Reject')}}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3" style="height: 150px">
                                    <div class="timeline-step h-100">
                                        <div class="timeline-content">
                                            <div class="timeline-icons"><span class="timeline-dots"></span>
                                                <i class="ti ti-file-import text-primary"></i>
                                            </div>
                                            <h6 class="text-primary my-3">{{__('Convert to GRN')}}</h6>
                                            <p class="text-muted text-sm mb-3">
                                                @if($purchase->status == 7 && $purchase->grn_converted)
                                                    <small>{{__('Fully Received')}}</small>
                                                @elseif($purchase->status == 6 && $purchase->items->sum('received_quantity') > 0)
                                                    <small>{{__('Partially Converted')}}</small>
                                                @elseif($purchase->status == 6)
                                                    <small>{{__('Ready to convert')}}</small>
                                                @else
                                                    <small>{{__('Finalize first')}}</small>
                                                @endif
                                            </p>
                                            <div class="timeline-action">
                                                @can('convert purchase to grn')
                                                @if($purchase->status == 6 && \Auth::user()->type == 'company' && !$purchase->grn_converted)
                                                    <a href="#"
                                                        data-url="{{ route('purchase.convert_to_grn', $purchase->id) }}"
                                                        data-size="modal-fullscreen"
                                                        data-ajax-popup="true"
                                                        data-bs-title="{{ __('Convert to GRN') }}"
                                                        class="btn mx-1 btn-sm btn-outline-primary">
                                                        <span class="btn-inner--icon"><i class="ti ti-file-import mr-2"></i></span>{{__('Convert to GRN')}}
                                                    </a>
                                                @endif
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endcan

    @if(\Auth::user()->type=='company' && $purchase->status!=0 && $purchase->status!=5)
        <div class="row justify-content-between align-items-center mb-3">
            <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                <div class="all-button-box">
                    <a href="{{ route('purchase.pdf', Crypt::encrypt($purchase->id))}}" target="_blank" class="btn mx-1 btn-sm btn-outline-primary">
                        {{__('Download')}}
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="invoice">
                        <div class="invoice-print">
                            <div class="row invoice-title mt-2">
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12">
                                    <h4>{{__('Purchase')}}</h4>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 text-end">
                                    <h4 class="invoice-number">{{ Auth::user()->purchaseNumberFormat($purchase->purchase_id) }}</h4>
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
                                                <strong>{{__('Issue Date')}} :</strong><br>
                                                {{\Auth::user()->dateFormat($purchase->purchase_date)}}<br><br>
                                            </small>
                                        </div>

                                    </div>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col">
                                    <small class="font-style">
                                        <strong>{{__('Billed To')}} :</strong><br>
                                        @if(!empty($vendor->billing_name))
                                            {{!empty($vendor->billing_name)?$vendor->billing_name:''}}<br>
                                            {{!empty($vendor->billing_address)?$vendor->billing_address:''}}<br>
                                            {{!empty($vendor->billing_city)?$vendor->billing_city:'' .', '}} <br>
                                            {{!empty($vendor->billing_state)?$vendor->billing_state:'',', '}},
                                            {{!empty($vendor->billing_zip)?$vendor->billing_zip:''}}<br>
                                            {{!empty($vendor->billing_country)?$vendor->billing_country:''}}<br>
                                            {{!empty($vendor->billing_phone)?$vendor->billing_phone:''}}<br>
                                            @if($settings['vat_gst_number_switch'] == 'on')
                                                <strong>{{__('Tax Number ')}} : </strong>{{!empty($vendor->tax_number)?$vendor->tax_number:'-'}}
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </small>
                                </div>

                                @if(App\Models\Utility::getValByName('shipping_display')=='on')
                                    <div class="col">
                                        <small>
                                            <strong>{{__('Shipped To')}} :</strong><br>
                                            @if(!empty($vendor->shipping_name))
                                                {{!empty($vendor->shipping_name)?$vendor->shipping_name:''}}<br>
                                                {{!empty($vendor->shipping_address)?$vendor->shipping_address:''}}<br>
                                                {{!empty($vendor->shipping_city)?$vendor->shipping_city:'' .', '}}<br>
                                                {{!empty($vendor->shipping_state)?$vendor->shipping_state:'',', '}},
                                                {{!empty($vendor->shipping_zip)?$vendor->shipping_zip:''}}<br>
                                                {{!empty($vendor->shipping_country)?$vendor->shipping_country:''}}<br>
                                                {{!empty($vendor->shipping_phone)?$vendor->shipping_phone:''}}<br>
                                            @else
                                            -
                                            @endif
                                        </small>
                                    </div>
                                @endif

                                <div class="col">
                                    <div class="float-end mt-3">

                                        {!! DNS2D::getBarcodeHTML(route('purchase.link.copy',\Illuminate\Support\Facades\Crypt::encrypt($purchase->id)), "QRCODE",2,2) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <small>
                                        <strong>{{__('Status')}} :</strong><br>
                                        @if($purchase->status == 0)
                                            <span class="badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 1)
                                            <span class="badge bg-warning p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 2)
                                            <span class="badge bg-danger p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 3)
                                            <span class="badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 4)
                                            <span class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 5)
                                            <span class="badge bg-info p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 6)
                                            <span class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                        @elseif($purchase->status == 7)
                                            <span class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\Purchase::$statues[$purchase->status]) }}</span>
                                        @endif
                                    </small>
                                </div>


                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="font-bold mb-2">{{__('Product Summary')}}</div>
                                    <small class="mb-2">{{__('All items here cannot be deleted.')}}</small>
                                    <div class="table-responsive mt-3">
                                        <table class="table ">
                                            <tr>
                                                <th class="text-dark" data-width="40">#</th>
                                                <th class="text-dark">{{__('Product')}}</th>
                                                <th class="text-dark">{{__('Quantity')}}</th>
                                                <th class="text-dark">{{__('Rate')}}</th>
                                                <th class="text-dark">{{__('Discount')}}</th>
                                                <th class="text-dark">{{__('Tax')}}</th>
                                                <th class="text-dark">{{__('Description')}}</th>
                                                <th class="text-end text-dark" width="12%">{{__('Price')}}<br>
                                                    <small class="text-danger font-weight-bold">{{__('after tax & discount')}}</small>
                                                </th>
                                                <th></th>
                                            </tr>
                                            @php
                                                $totalQuantity=0;
                                                $totalRate=0;
                                                $totalTaxPrice=0;
                                                $totalDiscount=0;
                                                $taxesData=[];
                                            @endphp

                                            @foreach($iteams as $key =>$iteam)
                                                @if(!empty($iteam->tax))
                                                    @php
                                                        $taxes=App\Models\Utility::tax($iteam->tax);
                                                        $totalQuantity+=$iteam->quantity;
                                                        $totalRate+=$iteam->price;
                                                        $totalDiscount+=$iteam->discount;
                                                        foreach($taxes as $taxe){
                                                            $taxDataPrice=App\Models\Utility::taxRate($taxe->rate,$iteam->price,$iteam->quantity,$iteam->discount);
                                                            if (array_key_exists($taxe->name,$taxesData))
                                                            {
                                                                $taxesData[$taxe->name] = $taxesData[$taxe->name]+$taxDataPrice;
                                                            }
                                                            else
                                                            {
                                                                $taxesData[$taxe->name] = $taxDataPrice;
                                                            }
                                                        }
                                                    @endphp
                                                @endif
                                                <tr>
                                                    <td>{{$key+1}}</td>
                                                    <td>{{!empty($iteam->product())?$iteam->product()->name:''}}</td>
                                                    <td>{{$iteam->quantity}}</td>
                                                    <td>{{\Auth::user()->priceFormat($iteam->price)}}</td>
                                                    <td>{{\Auth::user()->priceFormat($iteam->discount)}}</td>
                                                    <td>
                                                        @if(!empty($iteam->tax))
                                                            <table class="">
                                                                @php
                                                                    $totalTaxRate = 0;
                                                                    $totalTaxPrice=0;
                                                                @endphp
                                                                @foreach($taxes as $tax)

                                                                    @php
                                                                        $taxPrice=App\Models\Utility::taxRate($tax->rate,$iteam->price,$iteam->quantity,$iteam->discount) ;
                                                                        $totalTaxPrice+=$taxPrice;
                                                                    @endphp
                                                                    <tr>
                                                                        <td>{{$tax->name .' ('.$tax->rate .'%)'}}</td>
                                                                        <td>{{\Auth::user()->priceFormat($taxPrice)}}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>

                                                    <td>{{!empty($iteam->description)?$iteam->description:'-'}}</td>
                                                    <td class="text-end">{{\Auth::user()->priceFormat(($iteam->price * $iteam->quantity - $iteam->discount) + $totalTaxPrice)}}</td>
                                                    <td></td>
                                                </tr>
                                            @endforeach
                                            <tfoot>
                                            <tr>
                                                <td></td>
                                                <td><b>{{__('Total')}}</b></td>
                                                <td><b>{{$totalQuantity}}</b></td>
                                                <td><b>{{\Auth::user()->priceFormat($totalRate)}}</b></td>
                                                <td><b>{{\Auth::user()->priceFormat($totalDiscount)}}</b></td>
                                                <td><b>{{\Auth::user()->priceFormat($totalTaxPrice)}}</b></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>


                                            </tr>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="text-end"><b>{{__('Sub Total')}}</b></td>
                                                <td class="text-end">{{\Auth::user()->priceFormat($purchase->getSubTotal())}}</td>
                                                <td></td>
                                            </tr>

                                                <tr>
                                                    <td colspan="6"></td>
                                                    <td class="text-end"><b>{{__('Discount')}}</b></td>
                                                    <td class="text-end">{{\Auth::user()->priceFormat($purchase->getTotalDiscount())}}</td>
                                                    <td></td>
                                                </tr>

                                            @if(!empty($taxesData))
                                                @foreach($taxesData as $taxName => $taxPrice)
                                                    <tr>
                                                        <td colspan="6"></td>
                                                        <td class="text-end"><b>{{$taxName}}</b></td>
                                                        <td class="text-end">{{ \Auth::user()->priceFormat($taxPrice) }}</td>
                                                        <td></td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="blue-text text-end"><b>{{__('Total')}}</b></td>
                                                <td class="blue-text text-end">{{\Auth::user()->priceFormat($purchase->getTotal())}}</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="text-end"><b>{{__('Paid')}}</b></td>
                                                <td class="text-end">{{\Auth::user()->priceFormat(($purchase->getTotal()-$purchase->getDue()))}}</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td colspan="6"></td>
                                                <td class="text-end"><b>{{__('Due')}}</b></td>
                                                <td class="text-end">{{\Auth::user()->priceFormat($purchase->getDue())}}</td>
                                                <td></td>
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
