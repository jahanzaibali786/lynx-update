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
        @if ($grn->status == 0)
            <a href="{{ route('grn.edit', $grn->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="ti ti-pencil text-light"></i> {{ __('Edit') }}
            </a>
            {{ Form::open(['route' => ['grn.finalize', $grn->id], 'method' => 'POST', 'class' => 'd-inline']) }}
                <button type="submit" class="btn btn-sm btn-outline-success"
                    onclick="return confirm('{{ __('Finalize this GRN? Stock and vendor balance will be updated.') }}')">
                    <i class="ti ti-check text-light"></i> {{ __('Finalize') }}
                </button>
            {{ Form::close() }}
        @endif
        <a href="{{ route('grn.index') }}" class="btn btn-sm btn-outline-secondary">
            {{ __('Back') }}
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">GRN-{{ sprintf('%05d', $grn->grn_no) }}</h5>
                    <span class="badge {{ $grn->status == 1 ? 'bg-primary' : 'bg-secondary' }} p-2 px-3">
                        {{ __(App\Models\Grn::$statues[$grn->status] ?? 'Received') }}
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
