@extends('layouts.admin')
@section('page-title')
    {{ __('Create Expense Voucher') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('journal-entry.index') }}">{{ __('Journal Entry') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Expense Voucher') }}</li>
@endsection

@section('content')
    <div class="row mt-4">
        <div class="col-xl-12">
            {{ Form::open(['route' => 'expense-voucher.store', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'class' => 'ajax-modal-form']) }}
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
                                {{ Form::select('branches', $branches, null, ['class' => 'form-control', 'id' => 'branches']) }}
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                {{ Form::label('date', __('Transaction Date'), ['class' => 'form-label']) }}
                                {{ Form::date('date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                {{ Form::label('bank_id', __('Bank Account (Head Imprest Only)'), ['class' => 'form-label']) }}
                                <span class="text-danger">*</span>
                                {{ Form::select('bank_id', $bankAccounts, null, ['class' => 'form-control select2', 'required' => 'required', 'placeholder' => __('Select Bank Account')]) }}
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="form-group">
                                {{ Form::label('expense_account_id', __('Expense Account (COA Expense Head Only)'), ['class' => 'form-label']) }}
                                <span class="text-danger">*</span>
                                {{ Form::select('expense_account_id', $chartAccounts, null, ['class' => 'form-control select2', 'required' => 'required']) }}
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-lg-4 col-md-4">
                            <div class="form-group">
                                {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
                                <span class="text-danger">*</span>
                                {{ Form::number('amount', '', ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0.01', 'placeholder' => __('Enter Amount')]) }}
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <div class="form-group">
                                {{ Form::label('payment_mode', __('Payment Mode'), ['class' => 'form-label']) }}
                                <span class="text-danger">*</span>
                                {{ Form::select('payment_mode', ['' => 'Select Payment Mode', 'dd' => 'DD', 'cd' => 'CD', 'bank-transfer' => 'Bank Transfer', 'chq' => 'Cheque', 'others' => 'Others'], '', ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <div class="form-group">
                                {{ Form::label('reference', __('Invoice No'), ['class' => 'form-label']) }}
                                {{ Form::text('reference', '', ['class' => 'form-control', 'placeholder' => __('Enter Invoice No')]) }}
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-lg-12 col-md-12">
                            <div class="form-group">
                                {{ Form::label('attachment', __('Attachment'), ['class' => 'form-label']) }}
                                <span class="text-danger">*</span>
                                {{ Form::file('attachment', ['class' => 'form-control', 'required' => 'required']) }}
                                <small class="text-muted">{{ __('Supported formats: PDF, Images, Document (Max: 5MB)') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-lg-12 col-md-12">
                            <div class="form-group">
                                {{ Form::label('description', __('Description / Narration'), ['class' => 'form-label']) }}
                                {{ Form::textarea('description', '', ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Narration')]) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-end">
                    <a href="{{ route('journal-entry.index') }}" class="btn btn-outline-light">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-outline-primary ms-2">{{ __('Create Expense Voucher') }}</button>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>
@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            if (typeof ajaxModalForm === 'function') {
                ajaxModalForm({
                    formSelector: '.ajax-modal-form',
                    onSuccess: function(response) {
                        if (response.redirect) {
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 1000);
                        }
                    }
                });
            }
        });
    </script>
@endpush
