@extends('layouts.admin')
@section('page-title')
{{ __('Store Stock Details') }}
@endsection

@push('script-page')
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
<li class="breadcrumb-item">{{ __('Store Stock Details') }}</li>
@endsection
@section('action-btn')
<div class="float-end">
    <a href="{{ route('branch.print',@$warehouse_id) }}" data-bs-title="{{__('Print')}}"
        class="btn mx-1 btn-sm btn-outline-primary">
        <span class="btn-inner--icon">Print</span>
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-xl-12">
        <div class="table-responsive">
            <table class="datatable">
                <thead class="table_heads">
                    <tr>
                        <th>{{ __('Product Name') }}</th>
                        <th>{{ __('Product Code') }}</th>
                        <th>{{ __('Quantity') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($warehouse as $warehouses)
                    {{-- @dd($warehouse) --}}
                    <tr class="font-style">
                        @if (!empty($warehouses->product()))
                        <td>{{ !empty($warehouses->product()) ? $warehouses->product()->name : '' }}</td>
                        <td>{{ !empty($warehouses->product()) ? $warehouses->product()->sku : '' }}</td>
                        <td>{{ $warehouses->quantity }}</td>
                        @endif
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection