@extends('layouts.admin')

@section('page-title')
    {{ __('GRN Detail') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('grn.index') }}">{{ __('GRN') }}</a></li>
    <li class="breadcrumb-item">GRN-{{ sprintf('%05d', $grn->grn_no) }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('grn.index') }}" class="btn btn-sm btn-outline-secondary">
            {{ __('Back') }}
        </a>
    </div>
@endsection

@section('content')
    @if($grn->status != 6)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row timeline-wrapper">
                            <div class="col-md-4" style="height: 150px">
                                <div class="timeline-step h-100">
                                    <div class="timeline-content">
                                        <div class="timeline-icons"><span class="timeline-dots"></span>
                                            <i class="ti ti-plus text-primary"></i>
                                        </div>
                                        <h6 class="text-primary my-3">{{__('Create GRN')}}</h6>
                                        <p class="text-muted text-sm mb-3"><i class="ti ti-clock mr-2"></i>{{__('Created on ')}}{{\Auth::user()->dateFormat($grn->grn_date)}}</p>
                                        <div class="timeline-action">
                                            @if($grn->status == 0)
                                                <a href="{{ route('grn.edit', $grn->id) }}" class="btn mx-1 btn-sm btn-outline-primary" data-bs-title="{{__('Edit')}}"><span class="btn-inner--icon"><i class="ti ti-pencil mr-2"></i></span>{{__('Edit')}}</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4" style="height: 150px">
                                <div class="timeline-step h-100">
                                    <div class="timeline-content">
                                        <div class="timeline-icons"><span class="timeline-dots"></span>
                                            <i class="ti ti-mail-forward text-warning"></i>
                                        </div>
                                        <h6 class="text-warning my-3">{{__('Fw to Ho')}}</h6>
                                        <p class="text-muted text-sm mb-3">
                                            @if($grn->status >= 5)
                                                <i class="ti ti-clock mr-2"></i>{{__('Forwarded on')}} {{\Auth::user()->dateFormat($grn->updated_at)}}
                                            @elseif($grn->status == 0)
                                                <small>{{__('Status')}} : {{__('Draft')}}</small>
                                            @else
                                                <small>{{__('Status')}} : {{__('Not Forwarded')}}</small>
                                            @endif
                                        </p>
                                        <div class="timeline-action">
                                            @if($grn->status == 0)
                                                <a href="{{ route('grn.fw_to_ho', $grn->id) }}" class="btn mx-1 btn-sm btn-outline-warning">
                                                    <span class="btn-inner--icon"><i class="ti ti-mail-forward mr-2"></i></span>{{__('Fw to Ho')}}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4" style="height: 150px">
                                <div class="timeline-step h-100">
                                    <div class="timeline-content">
                                        <div class="timeline-icons"><span class="timeline-dots"></span>
                                            @if($grn->status == 6)
                                                <i class="ti ti-checks text-success"></i>
                                            @elseif($grn->status == 5 && \Auth::user()->type == 'company')
                                                <i class="ti ti-hourglass-empty text-info"></i>
                                            @else
                                                <i class="ti ti-clock text-muted"></i>
                                            @endif
                                        </div>
                                        @if(\Auth::user()->type == 'company')
                                            <h6 class="my-3 @if($grn->status == 6) text-success @elseif($grn->status == 5) text-info @else text-muted @endif">
                                                {{__('Approval')}}
                                            </h6>
                                        @else
                                            <h6 class="my-3 @if($grn->status == 6) text-success @else text-muted @endif">
                                                @if($grn->status == 5) {{__('Under Approval')}} @else {{__('Approval')}} @endif
                                            </h6>
                                        @endif
                                        <p class="text-muted text-sm mb-3">
                                            @if($grn->status == 6)
                                                <i class="ti ti-clock mr-2"></i>{{__('Finalized')}}
                                            @elseif($grn->status == 5 && \Auth::user()->type == 'company')
                                                <small>{{__('Pending your decision')}}</small>
                                            @elseif($grn->status == 5)
                                                <small>{{__('Under review at Head Office')}}</small>
                                            @else
                                                <small>{{__('Awaiting forwarding')}}</small>
                                            @endif
                                        </p>
                                        <div class="timeline-action">
                                            @if($grn->status == 5 && \Auth::user()->type == 'company')
                                                <a href="{{ route('grn.finalize', $grn->id) }}" class="btn mx-1 btn-sm btn-outline-success" onclick="return confirm('{{ __('Finalize this GRN? Stock and vendor balance will be updated.') }}')">
                                                    <span class="btn-inner--icon"><i class="ti ti-check mr-2"></i></span>{{__('Finalize')}}
                                                </a>
                                                <a href="{{ route('grn.reject', $grn->id) }}" class="btn mx-1 btn-sm btn-outline-danger">
                                                    <span class="btn-inner--icon"><i class="ti ti-x mr-2"></i></span>{{__('Reject')}}
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
    @endif

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">GRN-{{ sprintf('%05d', $grn->grn_no) }}</h5>
                    <span class="badge
                        @if($grn->status == 0) bg-secondary
                        @elseif($grn->status == 5) bg-info
                        @elseif($grn->status == 6) bg-success
                        @else bg-secondary
                        @endif p-2 px-3">
                        {{ __(App\Models\Grn::$statues[$grn->status] ?? 'Draft') }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <small class="text-muted d-block">{{ __('GRN Date') }}</small>
                            <strong>{{ \Auth::user()->dateFormat($grn->grn_date) }}</strong>
                        </div>
                        <div class="col-md-3 mb-3">
                            <small class="text-muted d-block">{{ __('Vendor') }}</small>
                            <strong>{{ optional($grn->vendor)->name ?? '-' }}</strong>
                        </div>
                        <div class="col-md-3 mb-3">
                            <small class="text-muted d-block">{{ __('Store') }}</small>
                            <strong>{{ optional($grn->warehouse)->name ?? '-' }}</strong>
                        </div>
                        <div class="col-md-3 mb-3">
                            <small class="text-muted d-block">{{ __('Reference No') }}</small>
                            <strong>{{ $grn->reference_no ?? '-' }}</strong>
                        </div>
                        @if($grn->purchase_order_id)
                            <div class="col-md-3 mb-3">
                                <small class="text-muted d-block">{{ __('Purchase Order') }}</small>
                                <strong>{{ $grn->purchase_order_id }}</strong>
                            </div>
                        @endif
                        <div class="col-md-12">
                            <small class="text-muted d-block">{{ __('Remarks') }}</small>
                            <span>{{ $grn->remarks ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Product / Items') }}</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ __('Product Code') }}</th>
                                <th>{{ __('Product') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th class="text-end">{{ __('Quantity') }}</th>
                                <th class="text-end">{{ __('Cost') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th class="text-end">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($grn->items as $item)
                                <tr>
                                    <td>{{ optional($item->product)->sku ?? '-' }}</td>
                                    <td>{{ optional($item->product)->name ?? '-' }}</td>
                                    <td>{{ ucfirst($item->condition) }}</td>
                                    <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                                    <td class="text-end">{{ \Auth::user()->priceFormat($item->price) }}</td>
                                    <td>{{ $item->description ?? '-' }}</td>
                                    <td class="text-end">{{ \Auth::user()->priceFormat($item->quantity * $item->price) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end">{{ __('Total') }}</th>
                                <th class="text-end">{{ \Auth::user()->priceFormat($grn->getSubTotal()) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection