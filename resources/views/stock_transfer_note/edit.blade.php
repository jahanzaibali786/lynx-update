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
    @php
        $productOptions = $product_services->toArray();
    @endphp

    <style>
        #stn-items-wrap {
            overflow-x: auto;
        }

        #stn-items-table {
            min-width: 1180px;
        }

        .stn-group-row {
            background: #f7f9ff;
        }

        .stn-group-row td {
            padding: 12px 10px;
            border-top: 1px solid #e7ebff;
            border-bottom: 1px solid #e7ebff;
        }

        .stn-group-row .d-flex {
            flex-wrap: nowrap !important;
            align-items: center !important;
        }

        .stn-group-head {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 280px;
            flex: 1 1 auto;
        }

        .stn-group-toggle {
            border: 0;
            background: transparent;
            color: #2a1f8f;
            padding: 0;
            font-size: 16px;
            line-height: 1;
        }

        .stn-group-title {
            font-weight: 600;
            color: #111827;
        }

        .stn-group-meta {
            font-size: 12px;
            color: #6b7280;
        }

        .stn-group-tools {
            display: grid;
            grid-template-columns: 120px 90px minmax(160px, 1fr) 120px 130px;
            gap: 8px;
            align-items: center;
            flex: 0 0 auto;
        }

        .stn-group-tools .btn {
            white-space: nowrap;
            width: 100%;
        }

        .stn-item-description {
            min-height: 38px;
            resize: vertical;
        }

        .stn-row-actions {
            width: 74px;
            text-align: center;
        }

        .stn-row-actions .btn {
            padding: 4px 8px;
        }

        .stn-amount-text {
            font-weight: 600;
            white-space: nowrap;
        }

        .stn-empty-row td {
            padding: 28px 12px;
        }

        #stn-items-body .custom-select-wrapper {
            width: 100% !important;
        }
    </style>

    <div class="row">
        {{ Form::model($invoice, [
            'route' => ['stock-transfer-note.update', Crypt::encrypt($invoice->id)],
            'method' => 'PUT',
            'class' => 'w-100 invoice-ajax-form',
            'id' => 'stock-transfer-note-edit-form',
            'novalidate' => true,
        ]) }}

        <div class="col-12">
            <input type="hidden" name="issue_by" id="issue_by" value="{{ $invoice->issue_by ?? '' }}">
            <input type="hidden" name="recived_by" id="recived_by" value="{{ $invoice->recived_by ?? '' }}">

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('store_from', __('Store From'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                <select name="store_from" class="form-control custom-select" required>
                                    <option value="{{ $store_from->id }}" @selected((int) $invoice->store_from === (int) $store_from->id)>{{ $store_from->name }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('store_to', __('Store To'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::select('store_to', $store_to, old('store_to', $invoice->store_to), ['class' => 'form-control select custom-select', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('invoice_number', __('Stock Transfer Note Number'), ['class' => 'form-label']) }}
                                <input type="text" class="form-control" value="{{ $invoice_number }}" readonly>
                            </div>
                        </div>

                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('session_id', __('Session'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::select('session_id', $sessions, old('session_id', $invoice->session_id), ['class' => 'form-control select custom-select', 'required' => 'required']) }}
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

                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::date('issue_date', old('issue_date', $invoice->issue_date), ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::date('due_date', old('due_date', $invoice->due_date), ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('shipping_via', __('Shipping Via'), ['class' => 'form-label']) }}
                                {{ Form::text('shipping_via', old('shipping_via', $invoice->shipping_via), ['class' => 'form-control', 'placeholder' => __('Shipping Via')]) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('stn_type', __('STN Type'), ['class' => 'form-label']) }}
                                {{ Form::text('stn_type', old('stn_type', $invoice->stn_type), ['class' => 'form-control', 'placeholder' => __('STN Type')]) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('ref_number', __('Ref Number'), ['class' => 'form-label']) }}
                                {{ Form::text('ref_number', old('ref_number', $invoice->ref_number), ['class' => 'form-control']) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('issue_by_name', __('Issue By User'), ['class' => 'form-label']) }}
                                <input type="text" id="issue_by_name" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
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
                    <h6 class="mb-0">{{ __('Product / Items') }}</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                        <i class="ti ti-plus"></i> {{ __('Add Item') }}
                    </button>
                </div>

                <div id="stn-items-wrap" class="card-body table-border-style pt-0">
                    <table class="table table-sm mb-0" id="stn-items-table">
                        <thead>
                            <tr>
                                <th>{{ __('Items') }}</th>
                                <th style="width: 110px;">{{ __('Quantity') }}</th>
                                <th style="width: 260px;">{{ __('Description') }}</th>
                                <th style="width: 110px;" class="text-end">{{ __('Price') }}</th>
                                <th style="width: 110px;">{{ __('Type') }}</th>
                                <th style="width: 120px;" class="text-end">{{ __('Amount') }}</th>
                                <th style="width: 74px;"></th>
                            </tr>
                        </thead>
                        <tbody id="stn-items-body"></tbody>
                        <tfoot>
                            <tr>
                                <td class="text-end"><strong>{{ __('Total Items') }}</strong> <span class="stn-total-items">0</span></td>
                                <td class="text-end"><strong>{{ __('Total Qty') }}</strong> <span class="stn-total-qty">0.00</span></td>
                                <td></td>
                                <td class="text-end"><strong>{{ __('Total Price') }}</strong> <span class="stn-total-price">0.00</span></td>
                                <td></td>
                                <td class="text-end"><strong>{{ __('Total Amount') }}</strong> <span class="stn-total-amount">0.00</span></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end"><strong>{{ __('Sub Total') }} ({{ Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end stn-subtotal fw-bold">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end"><strong>{{ __('Total Amount') }} ({{ Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end stn-total fw-bold">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
        </div>
        {{ Form::close() }}
    </div>

    <script>
        (function() {
            var currencySymbol = @json(Auth::user()->currencySymbol());
            var productOptions = @json($productOptions);
            var studyPacks = @json($studyPackPayload);
            var initialItems = @json($existingItemsPayload);
            var storeUsers = @json($storeUsers);
            var productUrl = @json(route('stock-transfer-note.product'));
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

            function getProductName(productId) {
                return productOptions[String(productId)] || '';
            }

            function getStockAvailable(meta, type) {
                if (!meta) {
                    return 0;
                }

                if (type === 'use') {
                    return parseFloat(meta.stock_used) || 0;
                }

                if (type === 'damage') {
                    return parseFloat(meta.stock_damaged) || 0;
                }

                return parseFloat(meta.stock_new) || 0;
            }

            function getUsedQuantity(productId, type, excludedIndex) {
                var used = 0;

                state.entries.forEach(function(entry, index) {
                    if (index === excludedIndex) {
                        return;
                    }

                    if (String(entry.item_id || '') === String(productId || '') && String(entry.type || 'new') === String(type || 'new')) {
                        used += parseFloat(entry.quantity) || 0;
                    }
                });

                return used;
            }

            function validateEntryQuantity(index, options) {
                var entry = state.entries[index];
                options = options || {};

                if (!entry || !entry.item_id) {
                    return true;
                }

                var meta = productMetaCache[String(entry.item_id)];
                if (!meta) {
                    return true;
                }

                var available = getStockAvailable(meta, entry.type || 'new');
                var remaining = Math.max(available - getUsedQuantity(entry.item_id, entry.type || 'new', index), 0);
                var quantity = parseFloat(entry.quantity) || 0;

                var $input = $('input[name="items[' + index + '][quantity]"]');
                if ($input.length) {
                    $input.attr('max', remaining > 0 ? remaining : 0);
                }

                if (quantity > remaining) {
                    entry.quantity = remaining;

                    if ($input.length) {
                        $input.val(remaining);
                    }

                    if (!options.silent && typeof show_toastr === 'function') {
                        show_toastr('warning', @json(__('Quantity adjusted to available stock.')), 'warning');
                    }
                }

                return quantity <= remaining;
            }

            function validateAllEntries(options) {
                state.entries.forEach(function(entry, index) {
                    validateEntryQuantity(index, options);
                });
                updateTotals();
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
                        var data = typeof response === 'string' ? JSON.parse(response) : response;
                        productMetaCache[String(productId)] = data || {};
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

            function usedStudyPackClassKeys() {
                var keys = {};
                state.entries.forEach(function(entry) {
                    if (entry.study_pack_id && entry.study_pack_class) {
                        keys[(entry.study_pack_session_id || $('select[name="session_id"]').val() || '') + '|' + entry.study_pack_class] = true;
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
                    var label = pack.title || '';
                    html += '<option value="' + pack.id + '"' + disabled + '>' + escapeHtml(label) + '</option>';
                });

                $('#study_pack_picker').html(html).val('');
                initializeCustomSelects($('#stock-transfer-note-edit-form'));
            }

            function createEntry(data) {
                return $.extend({
                    key: 'stn-entry-' + (state.nextKey++),
                    item_id: '',
                    item_name: '',
                    quantity: 1,
                    base_quantity: 1,
                    price: 0,
                    description: '',
                    type: 'new',
                    study_pack_id: null,
                    study_pack_title: '',
                    study_pack_class: '',
                    study_pack_session_id: $('select[name="session_id"]').val() || '',
                    unit: ''
                }, data || {});
            }

            function getGroupEntries(studyPackId) {
                return state.entries.filter(function(entry) {
                    return String(entry.study_pack_id || '') === String(studyPackId || '');
                });
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
                validateAllEntries({ silent: true });
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
                        description: item.description || '',
                        type: item.type || 'new',
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
                selectedPack.items.forEach(function(item) {
                    loadProductMeta(item.product_id, function() {
                        validateAllEntries({ silent: true });
                    });
                });
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
                    return total + ((parseFloat(entry.quantity) || 0) * (parseFloat(entry.price) || 0));
                }, 0);
                var collapsed = !!state.collapsedGroups[String(studyPackId)];

                return '<tr class="stn-group-row" data-group-id="' + studyPackId + '">' +
                    '<td colspan="7">' +
                    '<div class="d-flex justify-content-between gap-3 flex-wrap">' +
                    '<div class="stn-group-head">' +
                    '<button type="button" class="stn-group-toggle" data-group-toggle="' + studyPackId + '">' +
                    (collapsed ? '<i class="ti ti-chevron-right"></i>' : '<i class="ti ti-chevron-down"></i>') +
                    '</button>' +
                    '<div>' +
                    '<div class="stn-group-title">' + escapeHtml(first.study_pack_title || @json(__('Study Pack'))) + '</div>' +
                    '<div class="stn-group-meta">' + escapeHtml(entries.length + ' ' + @json(__('items'))) + ' | ' + escapeHtml(currencySymbol + ' ' + formatAmount(totalAmount)) + '</div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="stn-group-tools">' +
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
                var amount = (parseFloat(entry.quantity) || 0) * (parseFloat(entry.price) || 0);
                var type = entry.type || 'new';
                var productValue = entry.item_id ? String(entry.item_id) : '';
                var productOptionsHtml = '<option value="">' + escapeHtml(@json(__('Select item'))) + '</option>';

                Object.keys(productOptions).forEach(function(productId) {
                    productOptionsHtml += '<option value="' + productId + '"' + (productValue === String(productId) ? ' selected' : '') + '>' + escapeHtml(productOptions[productId]) + '</option>';
                });

                return '<tr class="stn-item-row"' + (hidden ? ' style="display:none;"' : '') + ' data-entry-index="' + index + '">' +
                    '<td>' +
                    '<input type="hidden" name="items[' + index + '][id]" value="' + escapeHtml(entry.id || '') + '">' +
                    '<input type="hidden" name="items[' + index + '][study_pack_id]" value="' + escapeHtml(entry.study_pack_id || '') + '">' +
                    '<input type="hidden" name="items[' + index + '][study_pack_title]" value="' + escapeHtml(entry.study_pack_title || '') + '">' +
                    '<input type="hidden" name="items[' + index + '][study_pack_class]" value="' + escapeHtml(entry.study_pack_class || '') + '">' +
                    '<select name="items[' + index + '][item]" class="form-control form-control-sm custom-select stn-item-select" data-index="' + index + '" required>' + productOptionsHtml + '</select>' +
                    '</td>' +
                    '<td><input type="number" name="items[' + index + '][quantity]" class="form-control form-control-sm stn-quantity" data-index="' + index + '" min="1" step="1" value="' + escapeHtml(entry.quantity) + '" required></td>' +
                    '<td><textarea name="items[' + index + '][description]" class="form-control form-control-sm stn-item-description" data-index="' + index + '" placeholder="' + escapeHtml(@json(__('Description'))) + '">' + escapeHtml(entry.description || '') + '</textarea></td>' +
                    '<td>' +
                    '<div class="input-group input-group-sm">' +
                    '<input type="number" name="items[' + index + '][price]" class="form-control stn-price" data-index="' + index + '" min="0" step="0.01" value="' + escapeHtml(formatAmount(entry.price)) + '" required>' +
                    '<span class="input-group-text">' + escapeHtml(currencySymbol) + '</span>' +
                    '</div>' +
                    '</td>' +
                    '<td>' +
                    '<select name="items[' + index + '][type]" class="form-control form-control-sm custom-select stn-type" data-index="' + index + '">' +
                    '<option value="new"' + (type === 'new' ? ' selected' : '') + '>' + escapeHtml(@json(__('New'))) + '</option>' +
                    '<option value="use"' + (type === 'use' ? ' selected' : '') + '>' + escapeHtml(@json(__('Used'))) + '</option>' +
                    '<option value="damage"' + (type === 'damage' ? ' selected' : '') + '>' + escapeHtml(@json(__('Damage'))) + '</option>' +
                    '</select>' +
                    '</td>' +
                    '<td class="text-end"><span class="stn-amount-text">' + formatAmount(amount) + '</span></td>' +
                    '<td class="stn-row-actions"><button type="button" class="btn btn-sm btn-outline-danger stn-remove-item" data-index="' + index + '"><i class="ti ti-trash"></i></button></td>' +
                    '</tr>';
            }

            function renderItems() {
                var html = '';
                var seenGroups = {};

                if (!state.entries.length) {
                    html = '<tr class="stn-empty-row"><td colspan="7" class="text-center text-muted">' + escapeHtml(@json(__('No items added yet. Click "Add Item" or choose a Study Pack.'))) + '</td></tr>';
                } else {
                    state.entries.forEach(function(entry, index) {
                        if (entry.study_pack_id) {
                            var groupId = String(entry.study_pack_id);
                            if (!seenGroups[groupId]) {
                                var entries = getGroupEntries(groupId);
                                seenGroups[groupId] = true;
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

                $('#stn-items-body').html(html);
                initializeCustomSelects($('#stock-transfer-note-edit-form'));
                updateTotals();
                validateAllEntries({ silent: true });
            }

            function updateTotals() {
                var totalItems = 0;
                var totalQty = 0;
                var totalPrice = 0;
                var totalAmount = 0;

                state.entries.forEach(function(entry) {
                    if (entry.item_id) {
                        totalItems++;
                    }

                    totalQty += parseFloat(entry.quantity) || 0;
                    totalPrice += parseFloat(entry.price) || 0;
                    totalAmount += (parseFloat(entry.quantity) || 0) * (parseFloat(entry.price) || 0);
                });

                $('.stn-total-items').text(totalItems);
                $('.stn-total-qty').text(formatAmount(totalQty));
                $('.stn-total-price').text(formatAmount(totalPrice));
                $('.stn-total-amount, .stn-subtotal, .stn-total').text(formatAmount(totalAmount));
            }

            function syncStoreUsers() {
                var fromUser = storeUsers[$('select[name="store_from"]').val()] || {};
                var toUser = storeUsers[$('select[name="store_to"]').val()] || {};

                $('#issue_by').val(fromUser.id || '');
                $('#issue_by_name').val(fromUser.name || '');
                $('#recived_by').val(toUser.id || '');
                $('#recived_by_name').val(toUser.name || '');
            }

            $(document).on('click.stockTransferNoteEdit', '#addItemBtn', function(e) {
                e.preventDefault();
                addManualEntry();
            });

            $(document).on('change.stockTransferNoteEdit', '#study_pack_picker', function() {
                if ($(this).val()) {
                    addStudyPack($(this).val());
                }
            });

            $(document).on('click.stockTransferNoteEdit', '[data-group-toggle]', function() {
                var groupId = String($(this).data('group-toggle'));
                state.collapsedGroups[groupId] = !state.collapsedGroups[groupId];
                renderItems();
            });

            $(document).on('click.stockTransferNoteEdit', '.apply-group-qty', function() {
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
                validateAllEntries();
            });

            $(document).on('click.stockTransferNoteEdit', '.apply-group-desc', function() {
                var groupId = String($(this).data('group-id'));
                var value = $('.group-desc-value[data-group-id="' + groupId + '"]').val() || '';

                state.entries.forEach(function(entry) {
                    if (String(entry.study_pack_id || '') === groupId) {
                        entry.description = value;
                    }
                });

                renderItems();
            });

            $(document).on('click.stockTransferNoteEdit', '.remove-group-btn', function() {
                var groupId = String($(this).data('group-id'));

                state.entries = state.entries.filter(function(entry) {
                    return String(entry.study_pack_id || '') !== groupId;
                });

                delete state.collapsedGroups[groupId];
                renderItems();
                rebuildStudyPackOptions();
                validateAllEntries({ silent: true });
            });

            $(document).on('click.stockTransferNoteEdit', '.stn-remove-item', function() {
                removeEntryByIndex(parseInt($(this).data('index'), 10));
            });

            $(document).on('input.stockTransferNoteEdit change.stockTransferNoteEdit', '.stn-quantity, .stn-price, .stn-item-description, .stn-type', function() {
                var index = parseInt($(this).data('index'), 10);
                var entry = state.entries[index];

                if (!entry) {
                    return;
                }

                entry.quantity = parseFloat($('input[name="items[' + index + '][quantity]"]').val()) || 0;
                entry.price = parseFloat($('input[name="items[' + index + '][price]"]').val()) || 0;
                entry.description = $('textarea[name="items[' + index + '][description]"]').val() || '';
                entry.type = $('select[name="items[' + index + '][type]"]').val() || 'new';

                validateEntryQuantity(index);
                updateTotals();
                $('tr[data-entry-index="' + index + '"] .stn-amount-text').text(formatAmount(entry.quantity * entry.price));
            });

            $(document).on('change.stockTransferNoteEdit', '.stn-item-select', function() {
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
                    renderItems();
                    return;
                }

                loadProductMeta(productId, function(data) {
                    entry.price = parseFloat((data.product || {}).sale_price || 0);
                    entry.unit = data.unit || '';
                    renderItems();
                });
            });

            $(document).on('change.stockTransferNoteEdit', 'select[name="store_from"], select[name="store_to"]', syncStoreUsers);

            $(document).on('change.stockTransferNoteEdit', 'select[name="session_id"]', function() {
                rebuildStudyPackOptions();
            });

            $(document).on('submit.stockTransferNoteEdit', '#stock-transfer-note-edit-form', function(e) {
                if (!state.entries.length) {
                    e.preventDefault();
                    if (typeof show_toastr === 'function') {
                        show_toastr('error', @json(__('Please add at least one item.')), 'error');
                    }
                    return false;
                }

                var invalid = state.entries.some(function(entry) {
                    return !entry.item_id || (parseFloat(entry.quantity) || 0) <= 0 || (parseFloat(entry.price) || 0) < 0;
                });

                if (invalid) {
                    e.preventDefault();
                    if (typeof show_toastr === 'function') {
                        show_toastr('error', @json(__('Please complete all item rows before saving.')), 'error');
                    }
                    return false;
                }
            });

            $(document).ready(function() {
                state.entries = (initialItems || []).map(function(item) {
                    if (item.study_pack_id) {
                        state.collapsedGroups[String(item.study_pack_id)] = false;
                    }

                    return createEntry({
                        id: item.id || '',
                        item_id: item.item_id || '',
                        item_name: item.item_name || getProductName(item.item_id),
                        quantity: parseFloat(item.quantity) || 1,
                        base_quantity: (function() {
                            var pack = studyPacks.find(function(studyPack) {
                                return String(studyPack.id) === String(item.study_pack_id || '');
                            });

                            if (!pack || !pack.items) {
                                return parseFloat(item.quantity) || 1;
                            }

                            var packItem = pack.items.find(function(packRow) {
                                return String(packRow.product_id) === String(item.item_id || '');
                            });

                            return parseFloat((packItem || {}).quantity) || parseFloat(item.quantity) || 1;
                        })(),
                        price: parseFloat(item.price) || 0,
                        description: item.description || '',
                        type: item.type || 'new',
                        study_pack_id: item.study_pack_id || null,
                        study_pack_title: item.study_pack_title || '',
                        study_pack_class: item.study_pack_class || '',
                        study_pack_session_id: $('select[name="session_id"]').val() || ''
                    });
                });

                state.entries.forEach(function(entry) {
                    if (entry.item_id) {
                        loadProductMeta(entry.item_id, function() {
                            validateAllEntries({ silent: true });
                        });
                    }
                });

                syncStoreUsers();
                rebuildStudyPackOptions();
                renderItems();

                if (typeof ajaxModalForm !== 'undefined') {
                    ajaxModalForm({
                        formSelector: '#stock-transfer-note-edit-form',
                        closeOnSuccess: true,
                        showToast: true,
                        onSuccess: function(response) {
                            if (response && response.success && typeof triggerContentAreaRefresh === 'function') {
                                triggerContentAreaRefresh(window.location.href);
                            }
                        }
                    });
                }
            });
        })();
    </script>
@endsection
