@extends('layouts.admin')
@section('page-title')
    {{ __('Convert Stock Transfer Requisition to Stock Transfer Note') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock-transfer-order.index') }}">{{ __('Stock Transfer Requisition') }}</a></li>
    <li class="breadcrumb-item">{{ __('Convert to Stock Transfer Note') }}</li>
@endsection

@section('content')
    <style>
        #items-table-wrap {
            overflow-x: auto;
        }

        #items-table {
            min-width: 1180px;
        }

        .col-item {
            width: 42%;
        }

        .col-quantity {
            width: 110px;
        }

        .col-description {
            width: 220px;
        }

        .col-price {
            width: 120px;
        }

        .col-type {
            width: 110px;
        }

        .col-amount {
            width: 140px;
        }

        .col-actions {
            width: 74px;
        }

        .inline-edit-row {
            background: #f5f8ff !important;
        }

        .inline-edit-row:hover {
            background: #edf2ff !important;
        }

        .inline-edit-row td {
            vertical-align: top;
            padding: 8px 6px;
        }

        .confirmed-row {
            background: #fff;
        }

        .confirmed-row:hover {
            background: #fafafa;
        }

        .confirmed-row td {
            vertical-align: top;
            padding: 8px 6px;
        }

        .inline-edit-row .form-control-sm {
            height: 31px;
            font-size: 12px;
            padding: 3px 7px;
        }

        .unit-label {
            font-size: 11px;
            color: #6c757d;
            margin-left: 4px;
        }

        .convert-description {
            min-height: 38px;
            resize: vertical;
        }

        .price-input-group {
            display: flex;
            align-items: stretch;
        }

        .price-input-group .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .price-input-group .input-group-text {
            min-width: 36px;
            justify-content: center;
            border: 1px solid #2a1f8f;
            border-left: 0;
            border-radius: 0 6px 6px 0;
            background: #fff;
            color: #2a1f8f;
            font-size: 12px;
        }

        .convert-group-row {
            background: #f7f9ff;
        }

        .convert-group-row td {
            padding: 10px 8px;
            border-top: 1px solid #e7ebff;
            border-bottom: 1px solid #e7ebff;
        }

        .convert-group-layout {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: nowrap;
        }

        .convert-group-head {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 280px;
            flex: 1 1 auto;
        }

        .convert-group-toggle {
            border: 0;
            background: transparent;
            color: #2a1f8f;
            padding: 0;
            font-size: 16px;
            line-height: 1;
        }

        .convert-group-title {
            font-weight: 600;
            color: #111827;
        }

        .convert-group-meta {
            font-size: 12px;
            color: #6b7280;
        }

        .convert-group-tools {
            display: grid;
            grid-template-columns: 86px 96px minmax(160px, 1fr) 120px 122px;
            gap: 6px;
            align-items: center;
            flex: 0 0 auto;
        }

        .convert-group-tools .btn {
            white-space: nowrap;
            width: 100%;
        }
    </style>

    <script>
        var invoiceItems = @json($conversionItems);
        var studyPacks = @json($studyPackPayload ?? []);
        var storeUsers = @json($storeUsers ?? []);
        var rowCounter = 0;
        var MAX_OPEN_ROWS = 10;
        var csrfToken = '{{ csrf_token() }}';
        var productUrl = '{{ route('invoice.product') }}';
        var collapsedGroups = {};

        var PRODUCT_OPTS = '<option value="">-- Select Item --</option>';
        @if (isset($product_services) && count($product_services) > 0)
            @foreach ($product_services as $val => $label)
                PRODUCT_OPTS += '<option value="{{ $val }}">{{ addslashes($label) }}</option>';
            @endforeach
        @endif

        function formatAmount(value) {
            return (parseFloat(value) || 0).toFixed(2);
        }

        function appendHidden(wrapper, name, value) {
            var v = (value === 0 || value === '0') ? '0' : (value || '');
            wrapper.append($('<input>', {
                type: 'hidden',
                name: name,
                value: v
            }));
        }

        function notify(type, message) {
            if (typeof show_toastr === 'function') {
                show_toastr(type, message, type);
            }
        }

        function getProductName(productId) {
            var name = '';
            @if (isset($product_services) && count($product_services) > 0)
                @foreach ($product_services as $val => $label)
                    if ('{{ $val }}' == productId) {
                        name = '{{ addslashes($label) }}';
                    }
                @endforeach
            @endif
            return name;
        }

        function escapeHtml(value) {
            return $('<div>').text(value || '').html();
        }

        function groupItemsByStudyPack(items) {
            var groups = [];
            var lookup = {};

            $.each(items, function(_, item) {
                var hasStudyPack = !!item.study_pack_id;
                var key = hasStudyPack ? 'group_' + item.study_pack_id : 'item_' + item.id;

                if (!lookup[key]) {
                    lookup[key] = {
                        key: key,
                        isStudyPack: hasStudyPack,
                        title: hasStudyPack ? (item.study_pack_title || 'Study Pack') : '',
                        items: []
                    };
                    groups.push(lookup[key]);
                }

                lookup[key].items.push(item);
            });

            return groups;
        }

        function getGroupEntries(groupId) {
            return invoiceItems.filter(function(item) {
                return String(item.study_pack_id || '') === String(groupId || '');
            });
        }

        function getStudyPackById(studyPackId) {
            return studyPacks.find(function(pack) {
                return String(pack.id || '') === String(studyPackId || '');
            }) || null;
        }

        function getStudyPackBaseQuantity(studyPackId, productId, fallbackQuantity) {
            var pack = getStudyPackById(studyPackId);
            if (!pack || !pack.items) {
                return parseFloat(fallbackQuantity || 1) || 1;
            }

            var packItem = pack.items.find(function(item) {
                return String(item.product_id || '') === String(productId || '');
            });

            return parseFloat((packItem || {}).quantity || fallbackQuantity || 1) || 1;
        }

        function getGroupMultiplier(item) {
            var baseQuantity = parseFloat(item.base_quantity || 0);
            var quantity = parseFloat(item.quantity || 0);

            if (baseQuantity <= 0) {
                return quantity;
            }

            return quantity / baseQuantity;
        }

        function buildGroupHeader(group) {
            var first = group.items[0] || {};
            var studyPackId = first.study_pack_id;
            var firstMultiplier = getGroupMultiplier(first);
            var sameQty = group.items.every(function(item) {
                return Math.abs(getGroupMultiplier(item) - firstMultiplier) < 0.0001;
            });
            var sameDescription = group.items.every(function(item) {
                return String(item.description || '') === String(first.description || '');
            });
            var totalAmount = group.items.reduce(function(total, item) {
                return total + (parseFloat(item.amount) || 0);
            }, 0);
            var collapsed = !!collapsedGroups[String(studyPackId)];
            var groupQtyValue = '';

            if (sameQty) {
                groupQtyValue = firstMultiplier;
            }

            return '<tr class="group-header-row convert-group-row" data-group-key="' + escapeHtml(group.key) + '">' +
                '<td colspan="7">' +
                '<div class="convert-group-layout">' +
                '<div class="convert-group-head">' +
                '<button type="button" class="convert-group-toggle" data-convert-group-toggle="' + escapeHtml(studyPackId) + '">' +
                (collapsed ? '<i class="ti ti-chevron-right"></i>' : '<i class="ti ti-chevron-down"></i>') +
                '</button>' +
                '<div>' +
                '<div class="convert-group-title">' + escapeHtml(group.title) + '</div>' +
                '<div class="convert-group-meta">' + escapeHtml(group.items.length + ' item(s) | Rs ' + formatAmount(totalAmount)) + '</div>' +
                '</div>' +
                '</div>' +
                '<div class="convert-group-tools">' +
                '<input type="number" class="form-control form-control-sm convert-group-qty-value" min="1" step="1" value="' + escapeHtml(groupQtyValue) + '" data-group-id="' + escapeHtml(studyPackId) + '" placeholder="' + escapeHtml(@json(__('Study Pack Qty'))) + '">' +
                '<button type="button" class="btn btn-sm btn-primary text-white convert-apply-group-qty" data-group-id="' + escapeHtml(studyPackId) + '">' + escapeHtml(@json(__('Apply Qty'))) + '</button>' +
                '<input type="text" class="form-control form-control-sm convert-group-desc-value" data-group-id="' + escapeHtml(studyPackId) + '" value="' + escapeHtml(sameDescription ? (first.description || '') : '') + '" placeholder="' + escapeHtml(@json(__('Description for all items in this group'))) + '">' +
                '<button type="button" class="btn btn-sm btn-primary text-white convert-apply-group-desc" data-group-id="' + escapeHtml(studyPackId) + '">' + escapeHtml(@json(__('Apply Description'))) + '</button>' +
                '<button type="button" class="btn btn-sm btn-danger text-white convert-remove-group-btn" data-group-id="' + escapeHtml(studyPackId) + '">' + escapeHtml(@json(__('Delete Group'))) + '</button>' +
                '</div>' +
                '</div>' +
                '</td>' +
                '</tr>';
        }

        function syncGroupStates() {
            Object.keys(collapsedGroups).forEach(function(groupId) {
                if (!getGroupEntries(groupId).length) {
                    delete collapsedGroups[groupId];
                }
            });
        }

        function buildGroupFooter(group) {
            var totalQty = 0;
            var totalAmount = 0;

            $.each(group.items, function(_, item) {
                totalQty += parseFloat(item.quantity) || 0;
                totalAmount += parseFloat(item.amount) || 0;
            });

            return '<tr class="group-footer-row" data-group-key="' + escapeHtml(group.key) + '">' +
                '<td class="text-end" colspan="1"><strong>Group Total</strong></td>' +
                '<td><strong>' + formatAmount(totalQty) + '</strong></td>' +
                '<td></td>' +
                '<td></td>' +
                '<td></td>' +
                '<td class="text-end"><strong>' + formatAmount(totalAmount) + '</strong></td>' +
                '<td></td>' +
                '</tr>';
        }

        function buildLockedRow(e, hidden) {
            var hiddenStyle = hidden ? ' style="display:none;"' : '';
            var sourceBadge = e.source ? '<div class="mb-1"><span class="badge bg-info" style="font-size:10px;">' + e
                .source + '</span></div>' : '';
            var remainingBadge = (typeof e.remaining_quantity !== 'undefined') ?
                '<div class="mb-1"><span class="badge bg-light text-dark border" style="font-size:10px;">Remaining: ' +
                formatAmount(e.remaining_quantity) + '</span></div>' : '';

            return '<tr data-entry-id="' + e.id + '" class="confirmed-row"' + hiddenStyle + '>' +
                '<td>' + sourceBadge + remainingBadge +
                '<div class="fw-medium">' + escapeHtml(e.item_name || '') + '</div>' +
                '<div class="mt-1"><small class="text-muted entry-unit" data-id="' + e.id + '">' + escapeHtml(e.unit || '') + '</small></div>' +
                '</td>' +
                '<td><input type="number" class="form-control form-control-sm entry-quantity" value="' + escapeHtml(formatAmount(e.quantity)) + '" min="1" step="1" data-id="' + e.id + '"></td>' +
                '<td><textarea class="form-control form-control-sm convert-description entry-description" data-id="' + e.id + '" placeholder="Description">' + escapeHtml(e.description || '') + '</textarea></td>' +
                '<td><div class="price-input-group"><input type="number" class="form-control form-control-sm entry-price" value="' + escapeHtml(formatAmount(e.price)) + '" min="0" step="0.01" data-id="' + e.id + '"><span class="input-group-text">Rs</span></div></td>' +
                '<td><select class="form-control form-control-sm custom-select entry-type" data-id="' + e.id + '">' +
                '<option value="new"' + (e.type === 'new' ? ' selected' : '') + '>New</option>' +
                '<option value="use"' + (e.type === 'use' ? ' selected' : '') + '>Used</option>' +
                '<option value="damage"' + (e.type === 'damage' ? ' selected' : '') + '>Damage</option>' +
                '</select></td>' +
                '<td class="text-end fw-semibold"><span class="entry-amount" data-id="' + e.id + '">' + formatAmount(e.amount) + '</span></td>' +
                '<td class="text-center"><a href="#" class="remove-entry-btn text-danger" data-id="' + e.id + '" title="Remove"><i class="ti ti-trash"></i></a></td>' +
                '</tr>';
        }

        function syncStoreUsers() {
            var fromUser = storeUsers[$('select[name="store_from"]').val()] || {};
            var toUser = storeUsers[$('select[name="store_to"]').val()] || {};

            $('#issue_by').val(fromUser.id || '');
            $('#issue_by_name').val(fromUser.name || '');
            $('#recived_by').val(toUser.id || '');
            $('#recived_by_name').val(toUser.name || '');
        }

        function rebuildStudyPackOptions() {
            var sessionId = $('#convert_session_id').val() || '';
            var html = '<option value="">' + escapeHtml(@json(__('Select Study Pack'))) + '</option>';

            $.each(studyPacks, function(_, pack) {
                if (String(pack.session_id || '') !== String(sessionId)) {
                    return;
                }

                var alreadyAdded = invoiceItems.some(function(item) {
                    return String(item.study_pack_id || '') === String(pack.id);
                });

                html += '<option value="' + escapeHtml(pack.id) + '">' + escapeHtml(pack.title || '') + '</option>';

                if (alreadyAdded) {
                    html = html.replace(
                        'value="' + escapeHtml(pack.id) + '">' + escapeHtml(pack.title || '') + '</option>',
                        'value="' + escapeHtml(pack.id) + '" disabled>' + escapeHtml(pack.title || '') + '</option>'
                    );
                }
            });

            $('#study_pack_picker').html(html).val('');
        }

        function addStudyPack(packId) {
            var selectedPack = studyPacks.find(function(pack) {
                return String(pack.id) === String(packId);
            });

            if (!selectedPack || !selectedPack.items || !selectedPack.items.length) {
                notify('warning', @json(__('No items found in selected Study Pack.')));
                $('#study_pack_picker').val('');
                return;
            }

            var alreadyAdded = invoiceItems.some(function(item) {
                return String(item.study_pack_id || '') === String(selectedPack.id);
            });

            if (alreadyAdded) {
                notify('warning', @json(__('This Study Pack has already been added.')));
                $('#study_pack_picker').val('');
                return;
            }

            $.each(selectedPack.items, function(_, item) {
                var quantity = parseFloat(item.quantity) || 1;
                var price = parseFloat(item.price) || 0;

                invoiceItems.push({
                    id: Date.now() + Math.random(),
                    source_item_id: 0,
                    item_id: item.product_id,
                    item_name: item.name || getProductName(item.product_id),
                    quantity: quantity,
                    base_quantity: quantity,
                    ordered_quantity: quantity,
                    shipped_quantity: 0,
                    remaining_quantity: quantity,
                    price: price,
                    discount: 0,
                    tax: '',
                    type: item.type || 'new',
                    unit: '',
                    amount: quantity * price,
                    description: item.description || '',
                    study_pack_id: selectedPack.id,
                    study_pack_title: selectedPack.title || '',
                    study_pack_class: selectedPack.class || '',
                    source: @json(__('Study Pack'))
                });
            });

            $('#study_pack_picker').val('');
            renderLockedItems();
            renderHiddenInputs();
            updateTotals();
            rebuildStudyPackOptions();
            checkEmptyState();
        }

        $(function() {
            if (invoiceItems && invoiceItems.length > 0) {
                $('#empty-row').hide();
                $.each(invoiceItems, function(_, item) {
                    item.id = item.id || Date.now() + Math.random();
                    item.item_id = item.item_id || item.item;
                    item.item_name = item.item_name || getProductName(item.item_id);
                    item.quantity = parseFloat(item.quantity) || 0;
                    item.base_quantity = item.study_pack_id
                        ? getStudyPackBaseQuantity(item.study_pack_id, item.item_id, item.quantity)
                        : (parseFloat(item.base_quantity || item.quantity || 1) || 1);
                    item.price = parseFloat(item.price) || 0;
                    item.discount = parseFloat(item.discount) || 0;
                    item.type = item.type || 'new';
                    item.amount = (item.quantity * item.price) - item.discount;
                });
                renderLockedItems();
                renderHiddenInputs();
                updateTotals();
            }

            rebuildStudyPackOptions();
            syncStoreUsers();

            if (typeof ajaxModalForm !== 'undefined') {
                ajaxModalForm({
                    formSelector: '.stock-transfer-order-convert-form',
                    submitText: '{{ __('Converting...') }}',
                    closeOnSuccess: false,
                    showToast: true,
                    onSuccess: function(response) {
                        if (response && response.redirect_url) {
                            window.location.href = response.redirect_url;
                            return;
                        }
                        closeActiveBootstrapModal();
                        window.location.reload();
                    }
                });
            }
        });

        $(document).on('change', 'select[name="store_from"], select[name="store_to"]', function() {
            syncStoreUsers();
        });

        $(document).on('change', '#convert_session_id', function() {
            rebuildStudyPackOptions();
        });

        $(document).on('change', '#study_pack_picker', function() {
            var packId = $(this).val();
            if (packId) {
                addStudyPack(packId);
            }
        });

        $(document).on('click', '#addItemBtn', function() {
            var openRows = $('#items-tbody tr[data-row-id]').length;
            if (openRows >= MAX_OPEN_ROWS) {
                notify('warning', 'Please confirm the existing rows before adding more.');
                return;
            }
            appendInlineRow();
        });

        function appendInlineRow() {
            rowCounter++;
            var rid = rowCounter;
            var row = '<tr data-row-id="' + rid + '" class="inline-edit-row">' +
                '<td class="col-item">' +
                '<select class="form-control form-control-sm custom-select row-item" data-rid="' + rid + '">' +
                PRODUCT_OPTS + '</select>' +
                '<input type="hidden" class="row-stock-new" data-rid="' + rid + '" value="0">' +
                '<input type="hidden" class="row-stock-used" data-rid="' + rid + '" value="0">' +
                '<input type="hidden" class="row-stock-damaged" data-rid="' + rid + '" value="0">' +
                '</td>' +
                '<td class="col-quantity"><input type="number" class="form-control form-control-sm row-quantity" data-rid="' +
                rid + '" min="1" step="1" value="1"><span class="unit-label" id="unit-' + rid + '"></span></td>' +
                '<td class="col-description"><textarea class="form-control form-control-sm convert-description row-description" data-rid="' +
                rid + '" placeholder="Description"></textarea></td>' +
                '<td class="col-price"><div class="price-input-group"><input type="number" class="form-control form-control-sm row-price" data-rid="' +
                rid + '" min="0" step="0.01"><span class="input-group-text">Rs</span></div></td>' +
                '<td class="col-type"><select class="form-control form-control-sm custom-select row-type" data-rid="' +
                rid +
                '"><option value="new">New</option><option value="use">Used</option><option value="damage">Damage</option></select></td>' +
                '<td class="col-amount text-end"><span class="amount-display" id="amount-' + rid + '">0.00</span></td>' +
                '<td class="col-actions text-center"><button type="button" class="btn btn-sm btn-primary confirm-row-btn" data-rid="' +
                rid +
                '" title="Confirm"><i class="ti ti-check"></i></button> <button type="button" class="btn btn-sm btn-outline-danger discard-row-btn" data-rid="' +
                rid + '" title="Discard"><i class="ti ti-x"></i></button></td>' +
                '</tr>';

            $('#items-tbody').append(row);
            $('#empty-row').hide();
            setTimeout(function() {
                $('.row-item[data-rid="' + rid + '"]').focus();
            }, 100);
        }

        $(document).on('change', '.row-item', function() {
            var rid = $(this).data('rid');
            var itemId = $(this).val();
            if (!itemId) {
                clearItemData(rid);
                return;
            }

            $.ajax({
                url: productUrl,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: {
                    product_id: itemId
                },
                success: function(response) {
                    var item = JSON.parse(response);
                    $('.row-price[data-rid="' + rid + '"]').val(parseFloat(item.product.sale_price || 0)
                        .toFixed(2));
                    $('#unit-' + rid).text(item.unit || '');
                    $('.row-stock-new[data-rid="' + rid + '"]').val(item.stock_new || 0);
                    $('.row-stock-used[data-rid="' + rid + '"]').val(item.stock_used || 0);
                    $('.row-stock-damaged[data-rid="' + rid + '"]').val(item.stock_damaged || 0);
                    validateStock(rid);
                    calculateRowAmount(rid);
                },
                error: function() {
                    notify('error', 'Failed to load product details.');
                }
            });
        });

        function clearItemData(rid) {
            $('.row-price[data-rid="' + rid + '"]').val('');
            $('#unit-' + rid).text('');
            $('#amount-' + rid).text('0.00');
        }

        function validateStock(rid) {
            var quantityInput = $('.row-quantity[data-rid="' + rid + '"]');
            var quantity = parseFloat(quantityInput.val()) || 0;
            var type = $('.row-type[data-rid="' + rid + '"]').val();
            var productId = $('.row-item[data-rid="' + rid + '"]').val();
            if (!productId) return;

            var stockAvailable = type === 'use' ?
                (parseFloat($('.row-stock-used[data-rid="' + rid + '"]').val()) || 0) :
                (type === 'damage' ?
                    (parseFloat($('.row-stock-damaged[data-rid="' + rid + '"]').val()) || 0) :
                    (parseFloat($('.row-stock-new[data-rid="' + rid + '"]').val()) || 0));
            var usedInOtherRows = 0;
            $.each(invoiceItems, function(_, item) {
                if (item.item_id == productId && item.type == type) {
                    usedInOtherRows += parseFloat(item.quantity) || 0;
                }
            });
            var remainingStock = Math.max(stockAvailable - usedInOtherRows, 0);
            if (quantity > remainingStock && remainingStock > 0) {
                notify('warning', 'Requested quantity exceeds remaining stock (' + remainingStock + ').');
                quantityInput.val(remainingStock);
            } else if (quantity > 0 && remainingStock === 0) {
                notify('warning', 'No remaining stock available for selected type.');
                quantityInput.val(0);
            }
        }

        $(document).on('keyup change', '.row-quantity, .row-price, .row-type', function() {
            var rid = $(this).data('rid');
            if ($(this).hasClass('row-quantity') || $(this).hasClass('row-type')) {
                validateStock(rid);
            }
            calculateRowAmount(rid);
        });

        function calculateRowAmount(rid) {
            var quantity = parseFloat($('.row-quantity[data-rid="' + rid + '"]').val()) || 0;
            var price = parseFloat($('.row-price[data-rid="' + rid + '"]').val()) || 0;
            $('#amount-' + rid).text(formatAmount(quantity * price));
        }

        $(document).on('click', '.confirm-row-btn', function() {
            var rid = $(this).data('rid');
            var tr = $('tr[data-row-id="' + rid + '"]');
            var itemId = $('.row-item[data-rid="' + rid + '"]').val();
            var itemName = $('.row-item[data-rid="' + rid + '"] option:selected').text();
            var quantity = parseFloat($('.row-quantity[data-rid="' + rid + '"]').val()) || 0;
            var price = parseFloat($('.row-price[data-rid="' + rid + '"]').val()) || 0;
            var type = $('.row-type[data-rid="' + rid + '"]').val() || 'new';
            var unit = $('#unit-' + rid).text();
            var description = $('.row-description[data-rid="' + rid + '"]').val() || '';

            if (!itemId) {
                notify('error', 'Please select an item.');
                return;
            }
            if (quantity <= 0) {
                notify('error', 'Please enter a valid quantity.');
                return;
            }
            if (price <= 0) {
                notify('error', 'Please enter a valid price.');
                return;
            }

            var entry = {
                id: Date.now(),
                item_id: itemId,
                item_name: itemName,
                quantity: quantity,
                price: price,
                discount: 0,
                tax: '',
                type: type,
                unit: unit,
                amount: quantity * price,
                description: description,
                source: ''
            };
            invoiceItems.push(entry);
            tr.remove();
            renderLockedItems();
            renderHiddenInputs();
            updateTotals();
            checkEmptyState();
        });

        function renderLockedItems() {
            syncGroupStates();
            $('#items-tbody tr[data-entry-id], #items-tbody tr.group-header-row, #items-tbody tr.group-footer-row').remove();

            var groups = groupItemsByStudyPack(invoiceItems);

            $.each(groups, function(_, group) {
                if (group.isStudyPack) {
                    $('#items-tbody').append(buildGroupHeader(group));
                }

                $.each(group.items, function(__, item) {
                    $('#items-tbody').append(buildLockedRow(item, group.isStudyPack && collapsedGroups[String(item.study_pack_id)]));
                });

                if (group.isStudyPack) {
                    $('#items-tbody').append(buildGroupFooter(group));
                }
            });

            if (typeof initializeCustomSelects === 'function') {
                initializeCustomSelects($('#stock-transfer-order-convert-form'));
            }
        }

        $(document).on('click', '.discard-row-btn', function() {
            $('tr[data-row-id="' + $(this).data('rid') + '"]').remove();
            checkEmptyState();
        });

        $(document).on('click', '.remove-entry-btn', function(e) {
            e.preventDefault();
            var id = parseInt($(this).data('id'));
            if (!confirm('Remove this item?')) return;
            invoiceItems = invoiceItems.filter(function(item) {
                return parseInt(item.id) !== id;
            });
            renderLockedItems();
            renderHiddenInputs();
            updateTotals();
            checkEmptyState();
            rebuildStudyPackOptions();
        });

        $(document).on('click', '[data-convert-group-toggle]', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var groupId = String($(this).data('convert-group-toggle'));
            collapsedGroups[groupId] = !collapsedGroups[groupId];
            renderLockedItems();
        });

        $(document).on('click', '.convert-apply-group-qty', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var groupId = String($(this).data('group-id'));
            var value = parseFloat($('.convert-group-qty-value[data-group-id="' + groupId + '"]').val());

            if (!value || value <= 0) {
                notify('error', @json(__('Please enter a valid quantity.')));
                return;
            }

            invoiceItems.forEach(function(item) {
                if (String(item.study_pack_id || '') === groupId) {
                    var baseQuantity = parseFloat(item.base_quantity || item.quantity || 1) || 1;
                    item.quantity = baseQuantity * value;
                    syncEntryAmount(item);
                }
            });

            renderLockedItems();
            renderHiddenInputs();
            updateTotals();
        });

        $(document).on('click', '.convert-apply-group-desc', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var groupId = String($(this).data('group-id'));
            var value = $('.convert-group-desc-value[data-group-id="' + groupId + '"]').val() || '';

            invoiceItems.forEach(function(item) {
                if (String(item.study_pack_id || '') === groupId) {
                    item.description = value;
                }
            });

            renderLockedItems();
            renderHiddenInputs();
        });

        $(document).on('click', '.convert-remove-group-btn', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            var groupId = String($(this).data('group-id'));
            invoiceItems = invoiceItems.filter(function(item) {
                return String(item.study_pack_id || '') !== groupId;
            });
            delete collapsedGroups[groupId];
            renderLockedItems();
            renderHiddenInputs();
            updateTotals();
            checkEmptyState();
            rebuildStudyPackOptions();
        });

        function getEntryById(id) {
            return invoiceItems.find(function(item) {
                return parseInt(item.id) === parseInt(id);
            });
        }

        function syncEntryAmount(entry) {
            entry.amount = (parseFloat(entry.quantity) || 0) * (parseFloat(entry.price) || 0);
            entry.discount = 0;
        }

        function refreshEntryRow(id) {
            var entry = getEntryById(id);
            if (!entry) {
                return;
            }

            syncEntryAmount(entry);
            $('tr[data-entry-id="' + id + '"] .entry-amount[data-id="' + id + '"]').text(formatAmount(entry.amount));
            $('tr[data-entry-id="' + id + '"] .entry-unit[data-id="' + id + '"]').text(entry.unit || '');
            renderHiddenInputs();
            updateTotals();
            renderLockedItems();
        }

        $(document).on('input change', '.entry-quantity, .entry-price, .entry-description, .entry-type', function() {
            var id = parseInt($(this).data('id'));
            var entry = getEntryById(id);
            var $row = $('tr[data-entry-id="' + id + '"]');

            if (!entry) {
                return;
            }

            entry.quantity = parseFloat($row.find('.entry-quantity').val()) || 0;
            entry.price = parseFloat($row.find('.entry-price').val()) || 0;
            entry.description = $row.find('.entry-description').val() || '';
            entry.type = $row.find('.entry-type').val() || 'new';
            syncEntryAmount(entry);
            $row.find('.entry-amount[data-id="' + id + '"]').text(formatAmount(entry.amount));
            renderHiddenInputs();
            updateTotals();
        });

        function renderHiddenInputs() {
            var wrapper = $('#hidden-inputs');
            wrapper.empty();
            $.each(invoiceItems, function(i, item) {
                var prefix = 'items[' + i + ']';
                appendHidden(wrapper, prefix + '[id]', '0');
                appendHidden(wrapper, prefix + '[source_item_id]', item.source_item_id || 0);
                appendHidden(wrapper, prefix + '[item]', item.item_id);
                appendHidden(wrapper, prefix + '[quantity]', item.quantity);
                appendHidden(wrapper, prefix + '[price]', item.price);
                appendHidden(wrapper, prefix + '[discount]', item.discount || '0');
                appendHidden(wrapper, prefix + '[type]', item.type || 'new');
                appendHidden(wrapper, prefix + '[tax]', item.tax || '');
                appendHidden(wrapper, prefix + '[itemTaxPrice]', '0');
                appendHidden(wrapper, prefix + '[itemTaxRate]', '0');
                appendHidden(wrapper, prefix + '[description]', item.description || '');
                appendHidden(wrapper, prefix + '[study_pack_id]', item.study_pack_id || '');
                appendHidden(wrapper, prefix + '[study_pack_title]', item.study_pack_title || '');
                appendHidden(wrapper, prefix + '[study_pack_class]', item.study_pack_class || '');
            });
        }

        function updateTotals() {
            var subTotal = 0;
            $.each(invoiceItems, function(_, item) {
                subTotal += (parseFloat(item.quantity) || 0) * (parseFloat(item.price) || 0);
            });
            $('.subTotal').text(formatAmount(subTotal));
            $('.totalAmount').text(formatAmount(subTotal));
        }

        function checkEmptyState() {
            var hasAny = $('#items-tbody tr[data-row-id], #items-tbody tr[data-entry-id]').length > 0;
            $('#empty-row').toggle(!hasAny);
        }

        $(document).on('keydown', '.row-item, .row-quantity, .row-description, .row-price, .row-type', function(e) {
            var rid = $(this).data('rid');
            if (!rid) return;
            if (e.key === 'Tab') {
                e.preventDefault();
                return false;
            }
            if (e.key === 'Enter' && e.shiftKey) {
                e.preventDefault();
                $('.confirm-row-btn[data-rid="' + rid + '"]').trigger('click');
                setTimeout(function() {
                    $('#addItemBtn').trigger('click');
                }, 100);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                $('.confirm-row-btn[data-rid="' + rid + '"]').trigger('click');
            } else if (e.key === 'Escape') {
                e.preventDefault();
                $('.discard-row-btn[data-rid="' + rid + '"]').trigger('click');
            }
        });

        $(document).on('submit', '#stock-transfer-order-convert-form', function(e) {
            if ($('#items-tbody tr[data-row-id]').length > 0) {
                e.preventDefault();
                notify('error', 'Please confirm or discard open item rows before converting.');
                return false;
            }
            if (invoiceItems.length === 0) {
                e.preventDefault();
                notify('error', 'Please add at least one item.');
                return false;
            }
            renderHiddenInputs();
        });
    </script>

    <div class="row">
        {{ Form::open(['route' => ['stock-transfer-order.convert_to_invoice.store', $StockTransferOrder->id], 'method' => 'POST', 'class' => 'w-100 stock-transfer-order-convert-form', 'id' => 'stock-transfer-order-convert-form', 'novalidate' => true]) }}
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="issue_by" id="issue_by" value="">
        <input type="hidden" name="recived_by" id="recived_by" value="">
        <div id="hidden-inputs"></div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('store_from', __('Store From'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                <select name="store_from" class="form-control" required>
                                    <option value="{{ $mainStore->id ?? '' }}">{{ $mainStore->name ?? '' }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('store_to', __('Store To'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::select('store_to', $storeTo, $selectedStoreTo, ['class' => 'form-control', 'id' => 'convert_store_to', 'required' => 'required']) }}
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
                                {{ Form::select('session_id', $sessions, old('session_id', $defaultSessionId), ['class' => 'form-control', 'id' => 'convert_session_id', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-5 col-lg-5 col-md-6 col-sm-12">
                            <div class="form-group">
                                <label for="study_pack_picker" class="form-label">{{ __('Study Pack') }}</label>
                                <select id="study_pack_picker" class="form-control">
                                    <option value="">{{ __('Select Study Pack') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::date('issue_date', $issueDate, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
                                {{ Form::date('due_date', $dueDate, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('shipping_via', __('Shipping Via'), ['class' => 'form-label']) }}
                                {{ Form::text('shipping_via', '', ['class' => 'form-control', 'placeholder' => __('Shipping Via')]) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('stn_type', __('STN Type'), ['class' => 'form-label']) }}
                                {{ Form::text('stn_type', __('From Requisition'), ['class' => 'form-control', 'placeholder' => __('STN Type')]) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('ref_number', __('Ref Number'), ['class' => 'form-label']) }}
                                {{ Form::text('ref_number', 'STO-' . \Auth::user()->purchaseNumberFormat($StockTransferOrder->branch_purchase_no), ['class' => 'form-control']) }}
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
            <div class="card" style="padding-bottom:100px!important;">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('Product / Items') }}</h6>
                </div>
                <div id="items-table-wrap" class="card-body table-border-style pt-0">
                    <table class="table table-sm mb-0" id="items-table">
                        <thead>
                            <tr>
                                <th class="col-item">{{ __('Items') }}</th>
                                <th class="col-quantity">{{ __('Quantity') }}</th>
                                <th class="col-description">{{ __('Description') }}</th>
                                <th class="col-price text-end">{{ __('Price') }}</th>
                                <th class="col-type text-center">{{ __('Type') }}</th>
                                <th class="col-amount text-end">{{ __('Amount') }}</th>
                                <th class="col-actions"></th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            <tr id="empty-row">
                                <td colspan="7" class="text-center text-muted py-4">
                                    {{ __('No items added yet. Click "Add Item" to begin.') }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end"><strong>{{ __('Sub Total') }}
                                        ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end subTotal fw-bold">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="5" class="text-end"><strong>{{ __('Total Amount') }}
                                        ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end totalAmount fw-bold">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-light"
                onclick="closeActiveBootstrapModal();">{{ __('Cancel') }}</button>
            <button type="submit" class="btn btn-outline-primary">{{ __('Convert') }}</button>
        </div>
        {{ Form::close() }}
    </div>
@endsection
