@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Stock Transfer Note') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock-transfer-note.index') }}">{{ __('Stock Transfer Note') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection

@section('content')
    <style>
        .stn-edit-items th {
            font-size: 11px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .stn-edit-items td {
            vertical-align: middle;
        }

        .stn-edit-items .col-item { width: 43%; }
        .stn-edit-items .col-quantity { width: 12%; }
        .stn-edit-items .col-price { width: 12%; }
        .stn-edit-items .col-type { width: 12%; }
        .stn-edit-items .col-amount { width: 12%; }
        .stn-edit-items .col-actions { width: 9%; }

        .stn-edit-items .item-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 54px;
        }
    </style>

    <div class="row">
        {{ Form::model($invoice, [
            'route' => ['stock-transfer-note.update', Crypt::encrypt($invoice->id)],
            'method' => 'PUT',
            'class' => 'w-100 invoice-ajax-form stock-transfer-note-edit-form',
            'id' => 'stock-transfer-note-edit-form',
            'novalidate' => true,
        ]) }}
        <input type="hidden" name="issue_by" id="issue_by" value="{{ $invoice->issue_by ?? '' }}">
        <input type="hidden" name="recived_by" id="recived_by" value="{{ $invoice->recived_by ?? '' }}">

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('store_from', __('Store From'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                <select name="store_from" class="form-control custom-select" required>
                                    <option value="{{ $store_from->id }}" @selected((int) $invoice->store_from === (int) $store_from->id)>
                                        {{ $store_from->name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('store_to', __('Store To'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::select('store_to', $store_to, $invoice->store_to, ['class' => 'form-control custom-select', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('invoice_number', __('Stock Transfer Note Number'), ['class' => 'form-label']) }}
                                <input type="text" class="form-control" value="{{ $invoice_number }}" readonly>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('session_id', __('Session'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::select('session_id', $sessions, old('session_id', $invoice->session_id), ['class' => 'form-control select custom-select', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::date('issue_date', old('issue_date', $invoice->issue_date), ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::date('due_date', old('due_date', $invoice->due_date), ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('shipping_via', __('Shipping Via'), ['class' => 'form-label']) }}
                                {{ Form::text('shipping_via', old('shipping_via', $invoice->shipping_via), ['class' => 'form-control', 'placeholder' => __('Shipping Via')]) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('stn_type', __('STN Type'), ['class' => 'form-label']) }}
                                {{ Form::text('stn_type', old('stn_type', $invoice->stn_type), ['class' => 'form-control', 'placeholder' => __('STN Type')]) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('ref_number', __('Ref Number'), ['class' => 'form-label']) }}
                                {{ Form::text('ref_number', old('ref_number', $invoice->ref_number), ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('issue_by_name', __('Issue By User'), ['class' => 'form-label']) }}
                                <input type="text" id="issue_by_name" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 col-12">
                            <div class="form-group">
                                {{ Form::label('recived_by_name', __('Received By User'), ['class' => 'form-label']) }}
                                <input type="text" id="recived_by_name" class="form-control" readonly>
                            </div>
                        </div>



                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between py-3">
                    <h6 class="mb-0">{{ __('Products') }}</h6>
                    <button type="button" class="btn btn-sm btn-primary" id="stn-add-item">
                        <i class="ti ti-plus"></i> {{ __('Add Item') }}
                    </button>
                </div>

                <div class="card-body table-border-style pt-0">
                    <div class="table-responsive">
                        <table class="table table-sm stn-edit-items mb-0">
                            <thead>
                                <tr>
                                    <th class="col-item">{{ __('Items') }}</th>
                                    <th class="col-quantity">{{ __('Quantity') }}</th>
                                    <th class="col-price">{{ __('Price') }}</th>
                                    <th class="col-type">{{ __('Type') }}</th>
                                    <th class="col-amount text-end">{{ __('Amount') }}</th>
                                    <th class="col-actions">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="stn-edit-item-rows">
                                @foreach ($invoice->items as $index => $item)
                                    <tr class="stn-item-row" data-row-index="{{ $index }}">
                                        <td>
                                            <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                            <select name="items[{{ $index }}][item]" class="form-control form-control-sm custom-select stn-item-select" required>
                                                <option value="">{{ __('Select item') }}</option>
                                                @foreach ($product_services as $productId => $productName)
                                                    <option value="{{ $productId }}" @selected((int) $item->product_id === (int) $productId)>{{ $productName }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm stn-quantity" value="{{ $item->quantity }}" min="1" step="1" required>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="number" name="items[{{ $index }}][price]" class="form-control stn-price" value="{{ $item->price }}" min="0" step="0.01" required>
                                                <span class="input-group-text">{{ Auth::user()->currencySymbol() }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <select name="items[{{ $index }}][type]" class="form-control form-control-sm stn-type">
                                                <option value="new" @selected(($item->type ?? 'new') === 'new')>{{ __('New') }}</option>
                                                <option value="use" @selected($item->type === 'use')>{{ __('Used') }}</option>
                                                <option value="damage" @selected($item->type === 'damage')>{{ __('Damage') }}</option>
                                            </select>
                                        </td>
                                        <td class="text-end stn-row-amount">{{ number_format($item->quantity * $item->price, 2, '.', '') }}</td>
                                        <td>
                                            <div class="item-actions">
                                                <button type="button" class="btn btn-sm btn-outline-success stn-confirm-item" title="{{ __('Confirm') }}"><i class="ti ti-check"></i></button>
                                                <button type="button" class="btn btn-sm btn-outline-danger stn-remove-item" title="{{ __('Delete') }}"><i class="ti ti-x"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="text-end"><strong>{{ __('Total Items') }}</strong> <span class="stn-total-items">0</span></td>
                                    <td class="text-end"><strong>{{ __('Total Qty') }}</strong> <span class="stn-total-qty">0.00</span></td>
                                    <td class="text-end"><strong>{{ __('Total Price') }}</strong> <span class="stn-total-price">0.00</span></td>
                                    <td></td>
                                    <td class="text-end"><strong>{{ __('Total Amount') }}</strong> <span class="stn-total-amount">0.00</span></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>{{ __('Sub Total') }} ({{ Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end stn-subtotal">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>{{ __('Total Amount') }} ({{ Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end stn-total fw-bold">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
        </div>
        {{ Form::close() }}
    </div>

    <template id="stn-edit-row-template">
        <tr class="stn-item-row" data-row-index="__INDEX__">
            <td>
                <input type="hidden" name="items[__INDEX__][id]" value="">
                <select name="items[__INDEX__][item]" class="form-control form-control-sm custom-select stn-item-select" required>
                    <option value="">{{ __('Select item') }}</option>
                    @foreach ($product_services as $productId => $productName)
                        <option value="{{ $productId }}">{{ $productName }}</option>
                    @endforeach
                </select>
            </td>
            <td><input type="number" name="items[__INDEX__][quantity]" class="form-control form-control-sm stn-quantity" value="1" min="1" step="1" required></td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" name="items[__INDEX__][price]" class="form-control stn-price" value="0.00" min="0" step="0.01" required>
                    <span class="input-group-text">{{ Auth::user()->currencySymbol() }}</span>
                </div>
            </td>
            <td>
                <select name="items[__INDEX__][type]" class="form-control form-control-sm stn-type">
                    <option value="new">{{ __('New') }}</option>
                    <option value="use">{{ __('Used') }}</option>
                    <option value="damage">{{ __('Damage') }}</option>
                </select>
            </td>
            <td class="text-end stn-row-amount">0.00</td>
            <td>
                <div class="item-actions">
                    <button type="button" class="btn btn-sm btn-outline-success stn-confirm-item" title="{{ __('Confirm') }}"><i class="ti ti-check"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger stn-remove-item" title="{{ __('Delete') }}"><i class="ti ti-x"></i></button>
                </div>
            </td>
        </tr>
    </template>

    <script>
        (function () {
            var namespace = '.stockTransferNoteEdit';
            var nextRowIndex = {{ $invoice->items->count() }};
            var productUrl = @json(route('stock-transfer-note.product'));
            var storeUsers = @json($storeUsers);

            $(document).off(namespace);

            function updateStoreUser(storeId, hiddenSelector, displaySelector) {
                var storeUser = storeUsers && storeUsers[storeId] ? storeUsers[storeId] : { id: '', name: '' };
                $(hiddenSelector).val(storeUser.id || '');
                $(displaySelector).val(storeUser.name || '');
            }

            function refreshStoreUsers() {
                updateStoreUser($('select[name="store_from"]').val(), '#issue_by', '#issue_by_name');
                updateStoreUser($('select[name="store_to"]').val(), '#recived_by', '#recived_by_name');
            }

            function calculateRow($row) {
                var quantity = parseFloat($row.find('.stn-quantity').val()) || 0;
                var price = parseFloat($row.find('.stn-price').val()) || 0;
                $row.find('.stn-row-amount').text((quantity * price).toFixed(2));
            }

            function calculateTotals() {
                var totalItems = 0;
                var totalQty = 0;
                var totalPrice = 0;
                var total = 0;
                $('#stn-edit-item-rows .stn-item-row').each(function () {
                    var $row = $(this);
                    var itemId = $row.find('.stn-item-select').val();
                    var quantity = parseFloat($row.find('.stn-quantity').val()) || 0;
                    var price = parseFloat($row.find('.stn-price').val()) || 0;
                    calculateRow($row);
                    if (itemId) {
                        totalItems++;
                    }
                    totalQty += quantity;
                    totalPrice += price;
                    total += quantity * price;
                });
                $('.stn-total-items').text(totalItems);
                $('.stn-total-qty').text(totalQty.toFixed(2));
                $('.stn-total-price').text(totalPrice.toFixed(2));
                $('.stn-total-amount').text(total.toFixed(2));
                $('.stn-subtotal, .stn-total').text(total.toFixed(2));
            }

            $(document).on('click' + namespace, '#stn-add-item', function (event) {
                event.preventDefault();
                event.stopPropagation();
                var template = $('#stn-edit-row-template').html().replace(/__INDEX__/g, nextRowIndex++);
                $('#stn-edit-item-rows').append(template);
                calculateTotals();
            });

            $(document).on('input' + namespace + ' change' + namespace, '.stn-quantity, .stn-price', function () {
                calculateTotals();
            });

            $(document).on('click' + namespace, '.stn-confirm-item', function (event) {
                event.preventDefault();
                calculateRow($(this).closest('.stn-item-row'));
                calculateTotals();
            });

            $(document).on('click' + namespace, '.stn-remove-item', function (event) {
                event.preventDefault();
                $(this).closest('.stn-item-row').remove();
                calculateTotals();
            });

            $(document).on('change' + namespace, '.stn-item-select', function () {
                var $select = $(this);
                var productId = $select.val();
                var $row = $select.closest('.stn-item-row');

                if (!productId) {
                    return;
                }

                $.ajax({
                    url: productUrl,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: { product_id: productId },
                    success: function (response) {
                        var item = typeof response === 'string' ? JSON.parse(response) : response;
                        if (item && item.product) {
                            $row.find('.stn-price').val(parseFloat(item.product.sale_price || 0).toFixed(2));
                            calculateTotals();
                        }
                    }
                });
            });

            $(document).on('change' + namespace, 'select[name="store_from"], select[name="store_to"]', refreshStoreUsers);

            $(document).on('submit' + namespace, '#stock-transfer-note-edit-form', function (event) {
                if ($('#stn-edit-item-rows .stn-item-row').length === 0) {
                    event.preventDefault();
                    if (typeof show_toastr === 'function') {
                        show_toastr('error', @json(__('Please add at least one item.')), 'error');
                    }
                    return false;
                }
            });

            refreshStoreUsers();
            calculateTotals();

            if (typeof ajaxModalForm !== 'undefined') {
                ajaxModalForm({
                    formSelector: '#stock-transfer-note-edit-form',
                    closeOnSuccess: true,
                    showToast: true,
                    onSuccess: function (response) {
                        if (response && response.success && typeof triggerContentAreaRefresh === 'function') {
                            triggerContentAreaRefresh(window.location.href);
                        }
                    }
                });
            }
        })();
    </script>
@endsection
