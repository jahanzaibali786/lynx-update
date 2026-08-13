@extends('layouts.admin')
@section('page-title')
    {{ __('Demand Order Detail') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('demand-order.index') }}">{{ __('Demand Order') }}</a></li>
    <li class="breadcrumb-item">{{ Auth::user()->purchaseNumberFormat($demandOrder->branch_purchase_no) }}</li>
@endsection

@push('script-page')
<script>
$(document).on('click', '#finalizeBtn', function(e) {
    e.preventDefault();
    $('#finalizeModal').modal('show');
});

$('#finalizeForm').on('submit', function(e) {
    var valid = true;
    $(this).find('input[name^="shipped_quantities"]').each(function() {
        var val = parseFloat($(this).val());
        if (isNaN(val) || val < 0) {
            valid = false;
            show_toastr('error', 'Please enter valid shipped quantities.', 'error');
            return false;
        }
    });
    if (!valid) {
        e.preventDefault();
    }
});
</script>
@endpush

@section('content')
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
                                    <h6 class="text-primary my-3">{{ __('Create Demand Order') }}</h6>
                                    <p class="text-muted text-sm mb-3"><i class="ti ti-clock mr-2"></i>{{ __('Created on ') }}{{ \Auth::user()->dateFormat($demandOrder->purchase_date) }}</p>
                                    <div class="timeline-action">
                                        @if(Gate::check('edit demand order') && ((\Auth::user()->type == 'company' && in_array($demandOrder->status, [0, 5])) || (\Auth::user()->type == 'branch' && $demandOrder->branch_id == \Auth::user()->id && $demandOrder->status == 0)))
                                                <a href="{{ route('demand-order.edit', Crypt::encrypt($demandOrder->id)) }}" class="btn mx-1 btn-sm btn-outline-primary" data-bs-title="{{ __('Edit') }}">
                                                    <span class="btn-inner--icon"><i class="ti ti-pencil mr-2"></i></span>{{ __('Edit') }}
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
                                        <i class="ti ti-mail-forward text-warning"></i>
                                    </div>
                                    <h6 class="text-warning my-3">{{ __('Fw to Ho') }}</h6>
                                    <p class="text-muted text-sm mb-3">
                                        @if($demandOrder->status >= 5)
                                            <i class="ti ti-clock mr-2"></i>{{ __('Forwarded on') }} {{ \Auth::user()->dateFormat($demandOrder->updated_at) }}
                                        @elseif($demandOrder->status == 0)
                                            <small>{{ __('Status') }} : {{ __('Draft') }}</small>
                                        @else
                                            <small>{{ __('Status') }} : {{ __('Not Forwarded') }}</small>
                                        @endif
                                    </p>
                                    <div class="timeline-action">
                                        @if($demandOrder->status == 0 && \Auth::user()->type != 'company' && Gate::check('forward demand order'))
                                            <a href="{{ route('demand-order.fw_to_ho', $demandOrder->id) }}" class="btn mx-1 btn-sm btn-outline-warning">
                                                <span class="btn-inner--icon"><i class="ti ti-mail-forward mr-2"></i></span>{{ __('Fw to Ho') }}
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
                                        @if($demandOrder->status == 6)
                                            <i class="ti ti-checks text-success"></i>
                                        @elseif($demandOrder->status == 5 && \Auth::user()->type == 'company')
                                            <i class="ti ti-hourglass-empty text-info"></i>
                                        @else
                                            <i class="ti ti-clock text-muted"></i>
                                        @endif
                                    </div>
                                    @if(\Auth::user()->type == 'company')
                                        <h6 class="my-3 @if($demandOrder->status == 6) text-success @elseif($demandOrder->status == 5) text-info @else text-muted @endif">
                                            {{ __('Approval') }}
                                        </h6>
                                    @else
                                        <h6 class="my-3 @if($demandOrder->status == 6) text-success @else text-muted @endif">
                                            @if($demandOrder->status == 5) {{ __('Under Approval') }} @else {{ __('Approval') }} @endif
                                        </h6>
                                    @endif
                                    <p class="text-muted text-sm mb-3">
                                        @if($demandOrder->status == 6)
                                            <i class="ti ti-clock mr-2"></i>{{ __('Approved') }}
                                        @elseif($demandOrder->status == 5 && \Auth::user()->type == 'company')
                                            <small>{{ __('Pending your decision') }}</small>
                                        @elseif($demandOrder->status == 5)
                                            <small>{{ __('Under review at Head Office') }}</small>
                                        @else
                                            <small>{{ __('Awaiting forwarding') }}</small>
                                        @endif
                                    </p>
                                    <div class="timeline-action">
                                        @if($demandOrder->status == 5 && \Auth::user()->type == 'company' && Gate::check('approve demand order'))
                                            <a href="#" id="finalizeBtn" class="btn mx-1 btn-sm btn-outline-success">
                                                <span class="btn-inner--icon"><i class="ti ti-check mr-2"></i></span>{{ __('Approve') }}
                                            </a>
                                        @endif
                                        @if($demandOrder->status == 5 && \Auth::user()->type == 'company' && Gate::check('reject demand order'))
                                            <a href="{{ route('demand-order.reject', $demandOrder->id) }}" class="btn mx-1 btn-sm btn-outline-danger">
                                                <span class="btn-inner--icon"><i class="ti ti-x mr-2"></i></span>{{ __('Reject') }}
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
                                    <h6 class="text-primary my-3">{{ __('Convert to Invoice') }}</h6>
                                    <p class="text-muted text-sm mb-3">
                                        @if($demandOrder->status == 6 && $demandOrder->invoice_converted)
                                            <small>{{ __('Converted') }}</small>
                                        @elseif($demandOrder->status == 6)
                                            <small>{{ __('Ready to convert') }}</small>
                                        @else
                                            <small>{{ __('Approve first') }}</small>
                                        @endif
                                    </p>
                                    <div class="timeline-action">
                                        @if($demandOrder->status == 6 && \Auth::user()->type == 'company' && !$demandOrder->invoice_converted && Gate::check('convert demand order to invoice') && Gate::check('create invoice'))
                                            <a href="#"
                                                data-url="{{ route('demand-order.convert_to_invoice', $demandOrder->id) }}"
                                                data-size="modal-fullscreen"
                                                data-ajax-popup="true"
                                                data-bs-title="{{ __('Convert to Invoice') }}"
                                                class="btn mx-1 btn-sm btn-outline-primary">
                                                <span class="btn-inner--icon"><i class="ti ti-file-import mr-2"></i></span>{{ __('Convert to Invoice') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="invoice">
                        <div class="invoice-print">
                            <div class="row invoice-title mt-2">
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12">
                                    <h4>{{ __('Demand Order') }}</h4>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-nd-6 col-lg-6 col-12 text-end">
                                    <h4 class="invoice-number">{{ Auth::user()->purchaseNumberFormat($demandOrder->branch_purchase_no) }}</h4>
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
                                                <strong>{{ __('Issue Date') }} :</strong><br>
                                                {{ \Auth::user()->dateFormat($demandOrder->purchase_date) }}<br><br>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col">
                                    <small class="font-style">
                                        <strong>{{ __('Branch') }} :</strong><br>
                                        @if(!empty($branch))
                                            {{ $branch->name }}<br>
                                            {{ !empty($branch->email) ? $branch->email : '' }}
                                        @else
                                            -
                                        @endif
                                    </small>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col">
                                    <small>
                                        <strong>{{ __('Status') }} :</strong><br>
                                        @if($demandOrder->status == 0)
                                            <span class="badge bg-secondary p-2 px-3 rounded">{{ __(\App\Models\DemandOrder::$statues[$demandOrder->status]) }}</span>
                                        @elseif($demandOrder->status == 5)
                                            <span class="badge bg-info p-2 px-3 rounded">{{ __(\App\Models\DemandOrder::$statues[$demandOrder->status]) }}</span>
                                        @elseif($demandOrder->status == 6)
                                            <span class="badge bg-success p-2 px-3 rounded">{{ __(\App\Models\DemandOrder::$statues[$demandOrder->status]) }}</span>
                                        @endif
                                    </small>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="font-bold mb-2">{{ __('Product Summary') }}</div>
                                    <div class="table-responsive mt-3">
                                        <table class="table">
                                            <tr>
                                                <th class="text-dark" data-width="40">#</th>
                                                <th class="text-dark">{{ __('Product') }}</th>
                                                <th class="text-dark">{{ __('Quantity') }}</th>
                                                <th class="text-dark">{{ __('Shipped') }}</th>
                                                <th class="text-dark">{{ __('Rate') }}</th>
                                                <th class="text-dark">{{ __('Discount') }}</th>
                                                <th class="text-dark">{{ __('Description') }}</th>
                                                <th class="text-end text-dark">{{ __('Amount') }}</th>
                                                <th class="text-end text-dark">{{ __('Shipped Amount') }}</th>
                                                <th class="text-end text-dark">{{ __('Remaining') }}</th>
                                            </tr>
                                            @php
                                                $totalQuantity = 0;
                                                $totalShipped = 0;
                                                $totalDiscount = 0;
                                                $totalAmount = 0;
                                                $totalShippedAmount = 0;
                                                $totalRemaining = 0;
                                            @endphp
                                            @foreach($iteams as $key => $iteam)
                                                @php
                                                    $qty = $iteam->quantity;
                                                    $shipped = $iteam->shipped_quantity ?? 0;
                                                    $price = $iteam->price;
                                                    $discount = $iteam->discount;
                                                    $rowAmount = ($qty * $price) - $discount;
                                                    $shippedAmount = ($shipped * $price) - $discount;
                                                    $remaining = $qty - $shipped;
                                                    $remainingAmount = $remaining * $price;
                                                    $totalQuantity += $qty;
                                                    $totalShipped += $shipped;
                                                    $totalDiscount += $discount;
                                                    $totalAmount += $rowAmount;
                                                    $totalShippedAmount += $shippedAmount;
                                                    $totalRemaining += $remainingAmount;
                                                @endphp
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ optional($iteam->product)->name }}</td>
                                                    <td>{{ $qty }}</td>
                                                    <td>{{ $shipped > 0 ? $shipped : '-' }}</td>
                                                    <td>{{ \Auth::user()->priceFormat($price) }}</td>
                                                    <td>{{ \Auth::user()->priceFormat($discount) }}</td>
                                                    <td>{{ !empty($iteam->description) ? $iteam->description : '-' }}</td>
                                                    <td class="text-end">{{ \Auth::user()->priceFormat($rowAmount) }}</td>
                                                    <td class="text-end">{{ \Auth::user()->priceFormat($shippedAmount) }}</td>
                                                    <td class="text-end">{{ $remaining > 0 ? $remaining . ' (' . \Auth::user()->priceFormat($remainingAmount) . ')' : '-' }}</td>
                                                </tr>
                                            @endforeach
                                            <tfoot>
                                                <tr>
                                                    <td></td>
                                                    <td><b>{{ __('Total') }}</b></td>
                                                    <td><b>{{ $totalQuantity }}</b></td>
                                                    <td><b>{{ $totalShipped }}</b></td>
                                                    <td></td>
                                                    <td><b>{{ \Auth::user()->priceFormat($totalDiscount) }}</b></td>
                                                    <td></td>
                                                    <td class="text-end"><b>{{ \Auth::user()->priceFormat($totalAmount) }}</b></td>
                                                    <td class="text-end"><b>{{ \Auth::user()->priceFormat($totalShippedAmount) }}</b></td>
                                                    <td class="text-end"><b>{{ \Auth::user()->priceFormat($totalRemaining) }}</b></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7"></td>
                                                    <td class="text-end"><b>{{ __('Sub Total') }}</b></td>
                                                    <td class="text-end">{{ \Auth::user()->priceFormat($demandOrder->getSubTotal()) }}</td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7"></td>
                                                    <td class="text-end"><b>{{ __('Discount') }}</b></td>
                                                    <td class="text-end">{{ \Auth::user()->priceFormat($demandOrder->getTotalDiscount()) }}</td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7"></td>
                                                    <td class="text-end"><b>{{ __('Total Shipped') }}</b></td>
                                                    <td class="text-end">{{ \Auth::user()->priceFormat($totalShippedAmount) }}</td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7"></td>
                                                    <td class="text-end"><b>{{ __('Total Remaining') }}</b></td>
                                                    <td class="text-end">{{ \Auth::user()->priceFormat($totalRemaining) }}</td>
                                                    <td></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7"></td>
                                                    <td class="blue-text text-end"><b>{{ __('Total') }}</b></td>
                                                    <td class="blue-text text-end">{{ \Auth::user()->priceFormat($demandOrder->getTotal()) }}</td>
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

    @if($demandOrder->status == 5 && \Auth::user()->type == 'company' && Gate::check('approve demand order'))
        <div class="modal fade" id="finalizeModal" tabindex="-1" role="dialog" aria-labelledby="finalizeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    {{ Form::open(['route' => ['demand-order.finalize', $demandOrder->id], 'method' => 'POST', 'id' => 'finalizeForm']) }}
                    <div class="modal-header">
                        <h5 class="modal-title" id="finalizeModalLabel">{{ __('Approve Demand Order - Enter Shipped Quantities') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Requested Qty') }}</th>
                                        <th>{{ __('Shipped Qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($iteams as $item)
                                        <tr>
                                            <td>{{ optional($item->product)->name }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>
                                                <input type="number" name="shipped_quantities[{{ $item->id }}]" 
                                                    class="form-control" value="{{ $item->quantity }}" 
                                                    min="0" step="0.01" required>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-outline-success">{{ __('Approve') }}</button>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    @endif
@endsection
