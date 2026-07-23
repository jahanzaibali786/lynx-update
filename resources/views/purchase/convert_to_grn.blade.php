@extends('layouts.admin')

@section('page-title')
    {{ __('Convert Purchase to GRN') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
    <li class="breadcrumb-item">{{ __('Convert to GRN') }}</li>
@endsection

@section('content')
    {{ Form::open(['route' => ['purchase.convert_to_grn.store', $purchase->id], 'method' => 'POST', 'id' => 'grn-form', 'class' => 'purchase-grn-convert-form', 'novalidate' => true]) }}
        @include('grn.form', [
            'grn' => null,
            'initialItems' => $initialItems,
            'formDefaults' => $formDefaults,
            'submitLabel' => $submitLabel,
            'cancelUrl' => $cancelUrl,
            'showPurchaseLink' => $showPurchaseLink,
            'showAddVendorLink' => $showAddVendorLink,
            'existingGrns' => $existingGrns ?? null
        ])
    {{ Form::close() }}

    <script>
        $(document).ready(function () {
            ajaxModalForm({
                formSelector: '.purchase-grn-convert-form',
                submitText: '{{ __('Converting...') }}',
                closeOnSuccess: false,
                showToast: true,
                onSuccess: function (response) {
                    if (response && response.redirect_url) {
                        window.location.href = response.redirect_url;
                        return;
                    }
                    closeActiveBootstrapModal();
                    window.location.reload();
                }
            });
        });
    </script>
@endsection
