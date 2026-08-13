@php
    $isEdit = ($mode ?? 'create') === 'edit';
    $pageTitle = $isEdit ? __('Edit Stock Transfer Requisition') : __('Create Stock Transfer Requisition');
    $submitLabel = $isEdit ? __('Update') : __('Create Stock Transfer Requisition');
    $selectedBranch = old('branch_id', $selectedBranch ?? '');
    $selectedWarehouse = old('warehouse_id', $selectedWarehouse ?? '');
    $selectedSessionId = old('session_id', $selectedSessionId ?? ($defaultSessionId ?? ''));
    $selectedDate = old('purchase_date', $selectedDate ?? date('Y-m-d'));
    $itemPayload = old('items', $initialItems ?? []);
    $productOptions = $product_services->toArray();
@endphp

@section('page-title')
    {{ $pageTitle }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock-transfer-order.index') }}">{{ __('Stock Transfer Requisition') }}</a></li>
    <li class="breadcrumb-item">{{ $pageTitle }}</li>
@endsection

@section('content')
    <style>
        #stor-items-wrap {
            overflow-x: auto;
        }

        #stor-items-table {
            min-width: 1220px;
        }

        .stor-group-row {
            background: #f7f9ff;
        }

        .stor-group-row td {
            padding: 12px 10px;
            border-top: 1px solid #e7ebff;
            border-bottom: 1px solid #e7ebff;
        }

        .stor-group-row .d-flex {
            flex-wrap: nowrap !important;
            align-items: center !important;
        }

        .stor-group-head {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 280px;
            flex: 1 1 auto;
        }

        .stor-group-toggle {
            border: 0;
            background: transparent;
            color: #2a1f8f;
            padding: 0;
            font-size: 16px;
            line-height: 1;
        }

        .stor-group-title {
            font-weight: 600;
            color: #111827;
        }

        .stor-group-meta {
            font-size: 12px;
            color: #6b7280;
        }

        .stor-group-tools {
            display: grid;
            grid-template-columns: 120px 90px minmax(180px, 1fr) 130px 130px;
            gap: 8px;
            align-items: center;
            flex: 0 0 auto;
        }

        .stor-group-tools .btn {
            white-space: nowrap;
            width: 100%;
        }

        .stor-item-description {
            min-height: 38px;
            resize: vertical;
        }

        .stor-row-actions {
            width: 74px;
            text-align: center;
        }

        .stor-row-actions .btn {
            padding: 4px 8px;
        }

        .stor-amount-text {
            font-weight: 600;
            white-space: nowrap;
        }

        .stor-empty-row td {
            padding: 28px 12px;
        }

        .stor-unit-hint {
            font-size: 11px;
            color: #6b7280;
            margin-top: 4px;
            min-height: 16px;
            display: block;
        }

        #stor-items-body .custom-select-wrapper {
            width: 100% !important;
        }
    </style>

    <div class="row">
        {{ Form::open([
            'url' => $formAction,
            'method' => $formMethod,
            'class' => 'w-100 stock-transfer-order-ajax-form',
            'id' => 'stock-transfer-order-form',
            'novalidate' => true,
        ]) }}
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group" id="branch-box">
                                {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::select('branch_id', $branches, $selectedBranch, ['class' => 'form-control select custom-select', 'id' => 'branch_id', 'data-url' => route('stock-transfer-order.vender'), 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('warehouse_id', __('Warehouse'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::select('warehouse_id', $warehouse, $selectedWarehouse, ['class' => 'form-control select custom-select', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('purchase_number', __('Stock Transfer Requisition Number'), ['class' => 'form-label']) }}
                                <input type="text" class="form-control" value="{{ $purchase_number }}" readonly>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('session_id', __('Session'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::select('session_id', $sessions, $selectedSessionId, ['class' => 'form-control select custom-select', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-5 col-lg-5 col-md-6 col-sm-12">
                            <div class="form-group">
                                <label for="study_pack_picker" class="form-label">{{ __('Study Pack') }}</label>
                                <select id="study_pack_picker" class="form-control select custom-select">
                                    <option value="">{{ __('Select Study Pack') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('purchase_date', __('Stock Transfer Requisition Date'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::date('purchase_date', $selectedDate, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('Product / Items') }}</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                        <i class="ti ti-plus" style="color:white"></i> {{ __('Add Item') }}
                    </button>
                </div>

                <div id="stor-items-wrap" class="card-body table-border-style pt-0">
                    <table class="table table-sm mb-0" id="stor-items-table">
                        <thead>
                            <tr>
                                <th>{{ __('Items') }}</th>
                                <th style="width: 120px;">{{ __('Quantity') }}</th>
                                <th style="width: 270px;">{{ __('Description') }}</th>
                                <th style="width: 130px;" class="text-end">{{ __('Price') }}</th>
                                <th style="width: 130px;" class="text-end">{{ __('Discount') }}</th>
                                <th style="width: 130px;" class="text-end">{{ __('Amount') }}</th>
                                <th style="width: 74px;"></th>
                            </tr>
                        </thead>
                        <tbody id="stor-items-body"></tbody>
                        <tfoot>
                            <tr>
                                <td class="text-end"><strong>{{ __('Total Items') }}</strong> <span class="stor-total-items">0</span></td>
                                <td class="text-end"><strong>{{ __('Total Qty') }}</strong> <span class="stor-total-qty">0.00</span></td>
                                <td></td>
                                <td class="text-end"><strong>{{ __('Total Price') }}</strong> <span class="stor-total-price">0.00</span></td>
                                <td class="text-end"><strong>{{ __('Discount') }}</strong> <span class="stor-total-discount">0.00</span></td>
                                <td class="text-end"><strong>{{ __('Amount') }}</strong> <span class="stor-total-amount">0.00</span></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end"><strong>{{ __('Sub Total') }} ({{ Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end stor-subtotal fw-bold">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end"><strong>{{ __('Total Amount') }} ({{ Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end stor-total fw-bold">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        </div>
        {{ Form::close() }}
    </div>

    <script>
        (function() {
            var formSelector = '#stock-transfer-order-form';
            var currencySymbol = @json(Auth::user()->currencySymbol());
            var productOptions = @json($productOptions);
            var studyPacks = @json($studyPackPayload ?? []);
            var initialItems = @json($itemPayload);
            var productUrl = @json(route('stock-transfer-order.product'));
            var csrfToken = @json(csrf_token());
            var productMetaCache = {};
            var state = {
                entries: [],
                collapsedGroups: {},
                nextKey: 1
            };

            function formatAmount(value) {
                return (parseFloat(value) || 0).toFixed(2);
            }

            function escapeHtml(value) {
                return $('<div>').text(value == null ? '' : value).html();
            }

            function initializeCustomSelects($container) {
                if (!window.CustomSelect || typeof window.CustomSelect.create !== 'function') {
                    return;
                }

                $container.find('select.custom-select').each(function() {
                    $(this).siblings('.custom-select-wrapper').remove();
                    this.customSelectInstance = null;
                    $(this).show();
                    window.CustomSelect.create(this);
                });
            }

            function parseProductPayload(response) {
                var data = response;

                if (typeof data === 'string') {
                    data = JSON.parse(data);
                }

                if (typeof data === 'string') {
                    data = JSON.parse(data);
                }

                return data || {};
            }

            function getProductName(productId) {
                return productOptions[String(productId)] || '';
            }

            function createEntry(data) {
                return $.extend({
                    key: 'stor-entry-' + (state.nextKey++),
                    id: '',
                    item_id: '',
                    item_name: '',
                    quantity: 1,
                    base_quantity: 1,
                    price: 0,
                    discount: 0,
                    description: '',
                    unit: '',
                    study_pack_id: null,
                    study_pack_title: '',
                    study_pack_class: '',
                    study_pack_session_id: ''
                }, data || {});
            }

            function getGroupEntries(studyPackId) {
                return state.entries.filter(function(entry) {
                    return String(entry.study_pack_id || '') === String(studyPackId || '');
                });
            }

            function usedStudyPackClassKeys() {
                var keys = {};
                state.entries.forEach(function(entry) {
                    if (entry.study_pack_id && entry.study_pack_class) {
                        keys[(entry.study_pack_session_id || '') + '|' + entry.study_pack_class] = true;
                    }
                });
                return keys;
            }

            function rebuildStudyPackOptions() {
                var sessionId = $('select[name="session_id"]').val() || '';
                var usedKeys = usedStudyPackClassKeys();
                var html = '<option value="">' + escapeHtml(@json(__('Select Study Pack'))) + '</option>';

                studyPacks.forEach(function(pack) {
                    if (String(pack.session_id) !== String(sessionId)) {
                        return;
                    }

                    var classKey = String(pack.session_id) + '|' + String(pack.class || '');
                    var disabled = usedKeys[classKey] ? ' disabled' : '';
                    html += '<option value="' + pack.id + '"' + disabled + '>' + escapeHtml(pack.title || '') + '</option>';
                });

                $('#study_pack_picker').html(html).val('');
                initializeCustomSelects($(formSelector));
            }

            function loadProductMeta(productId, callback) {
                if (!productId) {
                    if (typeof callback === 'function') {
                        callback(null);
                    }
                    return;
                }

                if (productMetaCache[String(productId)]) {
                    if (typeof callback === 'function') {
                        callback(productMetaCache[String(productId)]);
                    }
                    return;
                }

                $.ajax({
                    url: productUrl,
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    data: {
                        product_id: productId
                    },
                    success: function(response) {
                        productMetaCache[String(productId)] = parseProductPayload(response);
                        if (typeof callback === 'function') {
                            callback(productMetaCache[String(productId)]);
                        }
                    },
                    error: function() {
                        if (typeof show_toastr === 'function') {
                            show_toastr('error', @json(__('Failed to load product details.')), 'error');
                        }
                    }
                });
            }

            function addManualEntry() {
                state.entries.push(createEntry());
                renderItems();
            }

            function addStudyPack(packId) {
                var selectedPack = studyPacks.find(function(pack) {
                    return String(pack.id) === String(packId);
                });

                if (!selectedPack || !selectedPack.items || !selectedPack.items.length) {
                    if (typeof show_toastr === 'function') {
                        show_toastr('warning', @json(__('No items found in selected Study Pack.')), 'warning');
                    }
                    $('#study_pack_picker').val('');
                    return;
                }

                var classKey = String(selectedPack.session_id) + '|' + String(selectedPack.class || '');
                if (usedStudyPackClassKeys()[classKey]) {
                    if (typeof show_toastr === 'function') {
                        show_toastr('warning', @json(__('This class has already been applied once.')), 'warning');
                    }
                    $('#study_pack_picker').val('');
                    return;
                }

                selectedPack.items.forEach(function(item) {
                    state.entries.push(createEntry({
                        item_id: item.product_id,
                        item_name: item.name || getProductName(item.product_id),
                        quantity: parseFloat(item.quantity) || 1,
                        base_quantity: parseFloat(item.quantity) || 1,
                        price: parseFloat(item.price) || 0,
                        discount: 0,
                        description: item.description || '',
                        study_pack_id: selectedPack.id,
                        study_pack_title: selectedPack.title || '',
                        study_pack_class: selectedPack.class || '',
                        study_pack_session_id: selectedPack.session_id || ''
                    }));
                });

                state.collapsedGroups[String(selectedPack.id)] = false;
                $('#study_pack_picker').val('');
                renderItems();
                rebuildStudyPackOptions();
            }

            function removeEntryByIndex(index) {
                if (index < 0 || index >= state.entries.length) {
                    return;
                }

                var entry = state.entries[index];
                var groupId = entry.study_pack_id;
                state.entries.splice(index, 1);

                if (groupId && getGroupEntries(groupId).length === 0) {
                    delete state.collapsedGroups[String(groupId)];
                }

                renderItems();
                rebuildStudyPackOptions();
            }

            function renderGroupRow(studyPackId, entries) {
                var first = entries[0];
                var sameQty = entries.every(function(entry) {
                    return String(entry.quantity) === String(first.quantity);
                });
                var sameDescription = entries.every(function(entry) {
                    return (entry.description || '') === (first.description || '');
                });
                var totalAmount = entries.reduce(function(total, entry) {
                    var quantity = parseFloat(entry.quantity) || 0;
                    var price = parseFloat(entry.price) || 0;
                    var discount = parseFloat(entry.discount) || 0;
                    return total + ((quantity * price) - discount);
                }, 0);
                var collapsed = !!state.collapsedGroups[String(studyPackId)];

                return '<tr class="stor-group-row" data-group-id="' + studyPackId + '">' +
                    '<td colspan="7">' +
                    '<div class="d-flex justify-content-between gap-3 flex-wrap">' +
                    '<div class="stor-group-head">' +
                    '<button type="button" class="stor-group-toggle" data-group-toggle="' + studyPackId + '">' +
                    (collapsed ? '<i class="ti ti-chevron-right"></i>' : '<i class="ti ti-chevron-down"></i>') +
                    '</button>' +
                    '<div>' +
                    '<div class="stor-group-title">' + escapeHtml(first.study_pack_title || @json(__('Study Pack'))) + '</div>' +
                    '<div class="stor-group-meta">' + escapeHtml(entries.length + ' ' + @json(__('items'))) + ' | ' + escapeHtml(currencySymbol + ' ' + formatAmount(totalAmount)) + '</div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="stor-group-tools">' +
                    '<input type="number" class="form-control group-qty-value" min="1" step="1" value="' + escapeHtml(sameQty ? (first.base_quantity ? (parseFloat(first.quantity) / parseFloat(first.base_quantity)) : first.quantity) : '') + '" data-group-id="' + studyPackId + '" placeholder="' + escapeHtml(@json(__('Study Pack Qty'))) + '">' +
                    '<button type="button" class="btn btn-sm btn-primary text-white apply-group-qty" data-group-id="' + studyPackId + '">' + escapeHtml(@json(__('Apply Qty'))) + '</button>' +
                    '<input type="text" class="form-control group-desc-value" data-group-id="' + studyPackId + '" value="' + escapeHtml(sameDescription ? first.description : '') + '" placeholder="' + escapeHtml(@json(__('Description for all items in this group'))) + '">' +
                    '<button type="button" class="btn btn-sm btn-primary text-white apply-group-desc" data-group-id="' + studyPackId + '">' + escapeHtml(@json(__('Apply Description'))) + '</button>' +
                    '<button type="button" class="btn btn-sm btn-danger text-white remove-group-btn" data-group-id="' + studyPackId + '">' + escapeHtml(@json(__('Delete Group'))) + '</button>' +
                    '</div>' +
                    '</div>' +
                    '</td>' +
                    '</tr>';
            }

            function renderItemRow(entry, index, hidden) {
                var amount = ((parseFloat(entry.quantity) || 0) * (parseFloat(entry.price) || 0)) - (parseFloat(entry.discount) || 0);
                var selectedItem = entry.item_id ? String(entry.item_id) : '';
                var productOptionsHtml = '<option value="">' + escapeHtml(@json(__('Select item'))) + '</option>';

                Object.keys(productOptions).forEach(function(productId) {
                    productOptionsHtml += '<option value="' + productId + '"' + (selectedItem === String(productId) ? ' selected' : '') + '>' + escapeHtml(productOptions[productId]) + '</option>';
                });

                return '<tr class="stor-item-row"' + (hidden ? ' style="display:none;"' : '') + ' data-entry-index="' + index + '">' +
                    '<td>' +
                    '<input type="hidden" name="items[' + index + '][id]" value="' + escapeHtml(entry.id || '') + '">' +
                    '<input type="hidden" name="items[' + index + '][tax]" value="0">' +
                    '<input type="hidden" name="items[' + index + '][study_pack_id]" value="' + escapeHtml(entry.study_pack_id || '') + '">' +
                    '<input type="hidden" name="items[' + index + '][study_pack_title]" value="' + escapeHtml(entry.study_pack_title || '') + '">' +
                    '<input type="hidden" name="items[' + index + '][study_pack_class]" value="' + escapeHtml(entry.study_pack_class || '') + '">' +
                    '<select name="items[' + index + '][item]" class="form-control form-control-sm custom-select stor-item-select" data-index="' + index + '" required>' + productOptionsHtml + '</select>' +
                    '<span class="stor-unit-hint">' + escapeHtml(entry.unit || '') + '</span>' +
                    '</td>' +
                    '<td><input type="number" name="items[' + index + '][quantity]" class="form-control form-control-sm stor-quantity" data-index="' + index + '" min="0.01" step="0.01" value="' + escapeHtml(entry.quantity) + '" required></td>' +
                    '<td><textarea name="items[' + index + '][description]" class="form-control form-control-sm stor-item-description" data-index="' + index + '" placeholder="' + escapeHtml(@json(__('Description'))) + '">' + escapeHtml(entry.description || '') + '</textarea></td>' +
                    '<td><div class="input-group input-group-sm"><input type="number" name="items[' + index + '][price]" class="form-control stor-price" data-index="' + index + '" min="0" step="0.01" value="' + escapeHtml(formatAmount(entry.price)) + '" required><span class="input-group-text">' + escapeHtml(currencySymbol) + '</span></div></td>' +
                    '<td><input type="number" name="items[' + index + '][discount]" class="form-control form-control-sm stor-discount" data-index="' + index + '" min="0" step="0.01" value="' + escapeHtml(formatAmount(entry.discount)) + '"></td>' +
                    '<td class="text-end"><span class="stor-amount-text">' + formatAmount(amount) + '</span></td>' +
                    '<td class="stor-row-actions"><button type="button" class="btn btn-sm btn-outline-danger stor-remove-item" data-index="' + index + '"><i class="ti ti-trash"></i></button></td>' +
                    '</tr>';
            }

            function renderItems() {
                var html = '';
                var seenGroups = {};

                if (!state.entries.length) {
                    html = '<tr class="stor-empty-row"><td colspan="7" class="text-center text-muted">' + escapeHtml(@json(__('No items added yet. Click "Add Item" or choose a Study Pack.'))) + '</td></tr>';
                } else {
                    state.entries.forEach(function(entry, index) {
                        if (entry.study_pack_id) {
                            var groupId = String(entry.study_pack_id);
                            if (!seenGroups[groupId]) {
                                seenGroups[groupId] = true;
                                var entries = getGroupEntries(groupId);
                                html += renderGroupRow(groupId, entries);
                                entries.forEach(function(groupEntry) {
                                    html += renderItemRow(groupEntry, state.entries.indexOf(groupEntry), state.collapsedGroups[groupId]);
                                });
                            }
                        } else {
                            html += renderItemRow(entry, index, false);
                        }
                    });
                }

                $('#stor-items-body').html(html);
                initializeCustomSelects($(formSelector));
                updateTotals();
            }

            function updateTotals() {
                var totalItems = 0;
                var totalQty = 0;
                var totalPrice = 0;
                var totalDiscount = 0;
                var totalAmount = 0;

                state.entries.forEach(function(entry) {
                    var quantity = parseFloat(entry.quantity) || 0;
                    var price = parseFloat(entry.price) || 0;
                    var discount = parseFloat(entry.discount) || 0;
                    var amount = (quantity * price) - discount;

                    if (entry.item_id) {
                        totalItems++;
                    }

                    totalQty += quantity;
                    totalPrice += price;
                    totalDiscount += discount;
                    totalAmount += amount;
                });

                $('.stor-total-items').text(totalItems);
                $('.stor-total-qty').text(formatAmount(totalQty));
                $('.stor-total-price').text(formatAmount(totalPrice));
                $('.stor-total-discount').text(formatAmount(totalDiscount));
                $('.stor-total-amount, .stor-subtotal, .stor-total').text(formatAmount(totalAmount));
            }

            function refreshAmountOnly(index) {
                var entry = state.entries[index];
                var $row = $('#stor-items-body').find('tr[data-entry-index="' + index + '"]');

                if (!entry || !$row.length) {
                    return;
                }

                var amount = ((parseFloat(entry.quantity) || 0) * (parseFloat(entry.price) || 0)) - (parseFloat(entry.discount) || 0);
                $row.find('.stor-amount-text').text(formatAmount(amount));
            }

            function submitFormAjax(formElement) {
                var $form = $(formElement);
                var method = ($form.find('input[name="_method"]').val() || $form.attr('method') || 'POST').toUpperCase();

                $.ajax({
                    url: $form.attr('action'),
                    type: method,
                    data: $form.serialize(),
                    success: function(response) {
                        if (response && response.success) {
                            if (typeof show_toastr === 'function') {
                                show_toastr('success', response.message || @json(__('Saved successfully.')), 'success');
                            }

                            setTimeout(function() {
                                window.location.reload();
                            }, 500);
                            return;
                        }

                        if (typeof show_toastr === 'function') {
                            show_toastr('error', (response && response.message) ? response.message : @json(__('Something went wrong.')), 'error');
                        }
                    },
                    error: function(xhr) {
                        var message = @json(__('Something went wrong.'));

                        if (xhr.responseJSON) {
                            message = xhr.responseJSON.message || xhr.responseJSON.error || message;
                        }

                        if (typeof show_toastr === 'function') {
                            show_toastr('error', message, 'error');
                        }
                    }
                });
            }

            $(document).on('click', '#addItemBtn', function(e) {
                e.preventDefault();
                addManualEntry();
            });

            $(document).on('change', '#study_pack_picker', function() {
                if ($(this).val()) {
                    addStudyPack($(this).val());
                }
            });

            $(document).on('click', '[data-group-toggle]', function() {
                var groupId = String($(this).data('group-toggle'));
                state.collapsedGroups[groupId] = !state.collapsedGroups[groupId];
                renderItems();
            });

            $(document).on('click', '.apply-group-qty', function() {
                var groupId = String($(this).data('group-id'));
                var value = parseFloat($('.group-qty-value[data-group-id="' + groupId + '"]').val());

                if (!value || value <= 0) {
                    if (typeof show_toastr === 'function') {
                        show_toastr('error', @json(__('Please enter a valid quantity.')), 'error');
                    }
                    return;
                }

                state.entries.forEach(function(entry) {
                    if (String(entry.study_pack_id || '') === groupId) {
                        var baseQuantity = parseFloat(entry.base_quantity) || 1;
                        entry.quantity = baseQuantity * value;
                    }
                });

                renderItems();
            });

            $(document).on('click', '.apply-group-desc', function() {
                var groupId = String($(this).data('group-id'));
                var value = $('.group-desc-value[data-group-id="' + groupId + '"]').val() || '';

                state.entries.forEach(function(entry) {
                    if (String(entry.study_pack_id || '') === groupId) {
                        entry.description = value;
                    }
                });

                renderItems();
            });

            $(document).on('click', '.remove-group-btn', function() {
                var groupId = String($(this).data('group-id'));

                state.entries = state.entries.filter(function(entry) {
                    return String(entry.study_pack_id || '') !== groupId;
                });

                delete state.collapsedGroups[groupId];
                renderItems();
                rebuildStudyPackOptions();
            });

            $(document).on('click', '.stor-remove-item', function() {
                removeEntryByIndex(parseInt($(this).data('index'), 10));
            });

            $(document).on('input change', '.stor-quantity, .stor-price, .stor-discount, .stor-item-description', function() {
                var index = parseInt($(this).data('index'), 10);
                var entry = state.entries[index];

                if (!entry) {
                    return;
                }

                entry.quantity = parseFloat($('input[name="items[' + index + '][quantity]"]').val()) || 0;
                entry.price = parseFloat($('input[name="items[' + index + '][price]"]').val()) || 0;
                entry.discount = parseFloat($('input[name="items[' + index + '][discount]"]').val()) || 0;
                entry.description = $('textarea[name="items[' + index + '][description]"]').val() || '';

                refreshAmountOnly(index);
                updateTotals();
            });

            $(document).on('change', '.stor-item-select', function() {
                var index = parseInt($(this).data('index'), 10);
                var entry = state.entries[index];
                var productId = $(this).val();

                if (!entry) {
                    return;
                }

                entry.item_id = productId || '';
                entry.item_name = getProductName(productId);

                if (!productId) {
                    entry.price = 0;
                    entry.unit = '';
                    renderItems();
                    return;
                }

                loadProductMeta(productId, function(data) {
                    entry.price = parseFloat((data.product || {}).sale_price || 0);
                    entry.unit = data.unit || '';
                    renderItems();
                });
            });

            $(document).on('change', 'select[name="session_id"]', function() {
                rebuildStudyPackOptions();
            });

            $(document).on('submit', formSelector, function(e) {
                e.preventDefault();

                if (!state.entries.length) {
                    if (typeof show_toastr === 'function') {
                        show_toastr('error', @json(__('Please add at least one item.')), 'error');
                    }
                    return false;
                }

                var invalid = state.entries.some(function(entry) {
                    return !entry.item_id || (parseFloat(entry.quantity) || 0) <= 0 || (parseFloat(entry.price) || 0) < 0;
                });

                if (invalid) {
                    if (typeof show_toastr === 'function') {
                        show_toastr('error', @json(__('Please complete all item rows before saving.')), 'error');
                    }
                    return false;
                }

                submitFormAjax(this);
                return false;
            });

            initializeCustomSelects($(formSelector));

            if (Array.isArray(initialItems) && initialItems.length) {
                initialItems.forEach(function(item) {
                    if (item.study_pack_id) {
                        state.collapsedGroups[String(item.study_pack_id)] = false;
                    }

                    state.entries.push(createEntry({
                        id: item.id || '',
                        item_id: item.item_id || item.product_id || '',
                        item_name: item.item_name || getProductName(item.item_id || item.product_id),
                        quantity: parseFloat(item.quantity) || 1,
                        base_quantity: parseFloat(item.base_quantity || item.quantity) || 1,
                        price: parseFloat(item.price) || 0,
                        discount: parseFloat(item.discount) || 0,
                        description: item.description || '',
                        study_pack_id: item.study_pack_id || null,
                        study_pack_title: item.study_pack_title || '',
                        study_pack_class: item.study_pack_class || '',
                        study_pack_session_id: item.study_pack_session_id || $('select[name="session_id"]').val() || ''
                    }));
                });
            }

            renderItems();
            rebuildStudyPackOptions();
        })();
    </script>
@endsection
