@extends('layouts.admin')
@section('page-title')
    {{ __('Convert Stock Transfer Order to Stock Transfer') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock-transfer-order.index') }}">{{ __('Stock Transfer Order') }}</a></li>
    <li class="breadcrumb-item">{{ __('Convert to Stock Transfer Note') }}</li>
@endsection

@section('content')
    <style>
        #items-table-wrap {
            overflow-x: auto;
        }

        #items-table {
            min-width: 980px;
        }

        .col-item {
            width: 24%;
        }

        .col-quantity,
        .col-price,
        .col-discount,
        .col-type {
            width: 110px;
        }

        .col-amount {
            width: 140px;
        }

        .col-actions {
            width: 80px;
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
            vertical-align: middle;
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
    </style>

    <script>
        var invoiceItems = @json($conversionItems);
        var rowCounter = 0;
        var MAX_OPEN_ROWS = 10;
        var csrfToken = '{{ csrf_token() }}';
        var productUrl = '{{ route('invoice.product') }}';

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

        $(function() {
            if (invoiceItems && invoiceItems.length > 0) {
                $('#empty-row').hide();
                $.each(invoiceItems, function(_, item) {
                    item.id = item.id || Date.now() + Math.random();
                    item.item_id = item.item_id || item.item;
                    item.item_name = item.item_name || getProductName(item.item_id);
                    item.quantity = parseFloat(item.quantity) || 0;
                    item.price = parseFloat(item.price) || 0;
                    item.discount = parseFloat(item.discount) || 0;
                    item.type = item.type || 'new';
                    item.amount = (item.quantity * item.price) - item.discount;
                    $('#items-tbody').append(buildLockedRow(item));
                });
                renderHiddenInputs();
                updateTotals();
            }

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
                '<td class="col-price"><input type="number" class="form-control form-control-sm row-price" data-rid="' +
                rid + '" min="0" step="0.01"></td>' +
                '<td class="col-discount"><input type="number" class="form-control form-control-sm row-discount" data-rid="' +
                rid + '" min="0" step="0.01" value="0"></td>' +
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

        $(document).on('keyup change', '.row-quantity, .row-price, .row-discount, .row-type', function() {
            var rid = $(this).data('rid');
            if ($(this).hasClass('row-quantity') || $(this).hasClass('row-type')) {
                validateStock(rid);
            }
            calculateRowAmount(rid);
        });

        function calculateRowAmount(rid) {
            var quantity = parseFloat($('.row-quantity[data-rid="' + rid + '"]').val()) || 0;
            var price = parseFloat($('.row-price[data-rid="' + rid + '"]').val()) || 0;
            var discount = parseFloat($('.row-discount[data-rid="' + rid + '"]').val()) || 0;
            $('#amount-' + rid).text(formatAmount((quantity * price) - discount));
        }

        $(document).on('click', '.confirm-row-btn', function() {
            var rid = $(this).data('rid');
            var tr = $('tr[data-row-id="' + rid + '"]');
            var itemId = $('.row-item[data-rid="' + rid + '"]').val();
            var itemName = $('.row-item[data-rid="' + rid + '"] option:selected').text();
            var quantity = parseFloat($('.row-quantity[data-rid="' + rid + '"]').val()) || 0;
            var price = parseFloat($('.row-price[data-rid="' + rid + '"]').val()) || 0;
            var discount = parseFloat($('.row-discount[data-rid="' + rid + '"]').val()) || 0;
            var type = $('.row-type[data-rid="' + rid + '"]').val() || 'new';
            var unit = $('#unit-' + rid).text();

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
                discount: discount,
                tax: '',
                type: type,
                unit: unit,
                amount: (quantity * price) - discount,
                description: '',
                source: ''
            };
            invoiceItems.push(entry);
            tr.replaceWith(buildLockedRow(entry));
            renderHiddenInputs();
            updateTotals();
            checkEmptyState();
        });

        function buildLockedRow(e) {
            var sourceBadge = e.source ? '<div class="mb-1"><span class="badge bg-info" style="font-size:10px;">' + e
                .source + '</span></div>' : '';
            var remainingBadge = (typeof e.remaining_quantity !== 'undefined') ?
                '<div class="mb-1"><span class="badge bg-light text-dark border" style="font-size:10px;">Remaining: ' +
                formatAmount(e.remaining_quantity) + '</span></div>' : '';
            return '<tr data-entry-id="' + e.id + '" class="confirmed-row">' +
                '<td>' + sourceBadge + remainingBadge + e.item_name + '</td>' +
                '<td>' + e.quantity + (e.unit ? ' <small class="text-muted">' + e.unit + '</small>' : '') + '</td>' +
                '<td class="text-end">' + formatAmount(e.price) + '</td>' +
                '<td class="text-end">' + formatAmount(e.discount) + '</td>' +
                '<td class="text-center">' + (e.type ? e.type.charAt(0).toUpperCase() + e.type.slice(1) : '') + '</td>' +
                '<td class="text-end fw-semibold">' + formatAmount(e.amount) + '</td>' +
                '<td class="text-center"><a href="#" class="edit-entry-btn text-primary me-1" data-id="' + e.id +
                '" title="Edit"><i class="ti ti-pencil"></i></a><a href="#" class="remove-entry-btn text-danger" data-id="' +
                e.id + '" title="Remove"><i class="ti ti-trash"></i></a></td>' +
                '</tr>';
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
            $('tr[data-entry-id="' + id + '"]').remove();
            renderHiddenInputs();
            updateTotals();
            checkEmptyState();
        });

        $(document).on('click', '.edit-entry-btn', function(e) {
            e.preventDefault();
            var id = parseInt($(this).data('id'));
            var entry = invoiceItems.find(function(item) {
                return parseInt(item.id) === id;
            });
            if (!entry) return;
            var tr = $('tr[data-entry-id="' + id + '"]');
            tr.find('td').eq(1).html(
                '<input type="number" class="form-control form-control-sm edit-quantity" value="' + entry
                .quantity + '" min="1" step="1" data-id="' + id + '" style="width:80px;">' + (entry.unit ?
                    ' <small class="text-muted">' + entry.unit + '</small>' : ''));
            tr.find('td').eq(2).html(
                '<input type="number" class="form-control form-control-sm edit-price" value="' + entry.price +
                '" min="0" step="0.01" data-id="' + id + '" style="width:100px;">');
            tr.find('td').eq(3).html(
                '<input type="number" class="form-control form-control-sm edit-discount" value="' + entry
                .discount + '" min="0" step="0.01" data-id="' + id + '" style="width:100px;">');
            tr.find('td').eq(4).html('<select class="form-control form-control-sm edit-type" data-id="' + id +
                '"><option value="new" ' + (entry.type === 'new' ? 'selected' : '') +
                '>New</option><option value="use" ' + (entry.type === 'use' ? 'selected' : '') +
                '>Used</option><option value="damage" ' + (entry.type === 'damage' ? 'selected' : '') +
                '>Damage</option></select>');
            tr.find('td').eq(5).html('<span class="edit-amount text-end fw-semibold">' + formatAmount(entry
                .amount) + '</span>');
            tr.find('td').eq(6).html('<a href="#" class="save-edit-btn text-success me-1" data-id="' + id +
                '" title="Save"><i class="ti ti-check"></i></a><a href="#" class="cancel-edit-btn text-muted" data-id="' +
                id + '" title="Cancel"><i class="ti ti-x"></i></a>');
        });

        $(document).on('input change', '.edit-quantity, .edit-price, .edit-discount, .edit-type', function() {
            var id = parseInt($(this).data('id'));
            var tr = $('tr[data-entry-id="' + id + '"]');
            var quantity = parseFloat(tr.find('.edit-quantity').val()) || 0;
            var price = parseFloat(tr.find('.edit-price').val()) || 0;
            var discount = parseFloat(tr.find('.edit-discount').val()) || 0;
            tr.find('.edit-amount').text(formatAmount((quantity * price) - discount));
        });

        $(document).on('click', '.save-edit-btn', function(e) {
            e.preventDefault();
            var id = parseInt($(this).data('id'));
            var tr = $('tr[data-entry-id="' + id + '"]');
            var entry = invoiceItems.find(function(item) {
                return parseInt(item.id) === id;
            });
            if (!entry) return;
            var quantity = parseFloat(tr.find('.edit-quantity').val()) || 0;
            var price = parseFloat(tr.find('.edit-price').val()) || 0;
            var discount = parseFloat(tr.find('.edit-discount').val()) || 0;
            if (quantity <= 0) {
                notify('error', 'Please enter a valid quantity.');
                return;
            }
            if (price <= 0) {
                notify('error', 'Please enter a valid price.');
                return;
            }
            entry.quantity = quantity;
            entry.price = price;
            entry.discount = discount;
            entry.type = tr.find('.edit-type').val() || 'new';
            entry.amount = (quantity * price) - discount;
            tr.replaceWith(buildLockedRow(entry));
            renderHiddenInputs();
            updateTotals();
        });

        $(document).on('click', '.cancel-edit-btn', function(e) {
            e.preventDefault();
            var id = parseInt($(this).data('id'));
            var entry = invoiceItems.find(function(item) {
                return parseInt(item.id) === id;
            });
            $('tr[data-entry-id="' + id + '"]').replaceWith(buildLockedRow(entry));
        });

        function renderHiddenInputs() {
            var wrapper = $('#hidden-inputs');
            wrapper.empty();
            $.each(invoiceItems, function(i, item) {
                var prefix = 'items[' + i + ']';
                appendHidden(wrapper, prefix + '[id]', '0');
                appendHidden(wrapper, prefix + '[source_item_id]', item.source_item_id || item.id || '0');
                appendHidden(wrapper, prefix + '[item]', item.item_id);
                appendHidden(wrapper, prefix + '[quantity]', item.quantity);
                appendHidden(wrapper, prefix + '[price]', item.price);
                appendHidden(wrapper, prefix + '[discount]', item.discount || '0');
                appendHidden(wrapper, prefix + '[type]', item.type || 'new');
                appendHidden(wrapper, prefix + '[tax]', item.tax || '');
                appendHidden(wrapper, prefix + '[itemTaxPrice]', '0');
                appendHidden(wrapper, prefix + '[itemTaxRate]', '0');
                appendHidden(wrapper, prefix + '[description]', item.description || '');
            });
        }

        function updateTotals() {
            var subTotal = 0,
                totalDiscount = 0;
            $.each(invoiceItems, function(_, item) {
                subTotal += (parseFloat(item.quantity) || 0) * (parseFloat(item.price) || 0);
                totalDiscount += parseFloat(item.discount) || 0;
            });
            $('.subTotal').text(formatAmount(subTotal));
            $('.totalDiscount').text(formatAmount(totalDiscount));
            $('.totalAmount').text(formatAmount(subTotal - totalDiscount));
        }

        function checkEmptyState() {
            var hasAny = $('#items-tbody tr[data-row-id], #items-tbody tr[data-entry-id]').length > 0;
            $('#empty-row').toggle(!hasAny);
        }

        $(document).on('keydown', '.row-item, .row-quantity, .row-price, .row-discount, .row-type', function(e) {
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

        $(document).on('keydown', '.edit-quantity, .edit-price, .edit-discount, .edit-type', function(e) {
            var id = $(this).data('id');
            if (!id) return;
            if (e.key === 'Tab') {
                e.preventDefault();
                return false;
            }
            if (e.key === 'Enter' && e.shiftKey) {
                e.preventDefault();
                $('.save-edit-btn[data-id="' + id + '"]').trigger('click');
                setTimeout(function() {
                    $('#addItemBtn').trigger('click');
                }, 100);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                $('.save-edit-btn[data-id="' + id + '"]').trigger('click');
            } else if (e.key === 'Escape') {
                e.preventDefault();
                $('.cancel-edit-btn[data-id="' + id + '"]').trigger('click');
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
        <input type="hidden" name="store_from" value="{{ $mainStore->id ?? '' }}">
        <div id="hidden-inputs"></div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('store_from_label', __('Store From'), ['class' => 'form-label']) }}
                                <input type="text" class="form-control" value="{{ $mainStore->name ?? '' }}" readonly>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('store_to', __('Store To'), ['class' => 'form-label']) }}<span
                                    style="color:red"> *</span>
                                {{ Form::select('store_to', $storeTo, $selectedStoreTo, ['class' => 'form-control custom-select', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('invoice_number', __('Invoice Number'), ['class' => 'form-label']) }}
                                <input type="text" class="form-control" value="{{ $invoice_number }}" readonly>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}<span
                                    style="color:red"> *</span>
                                {{ Form::date('issue_date', $issueDate, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}<span
                                    style="color:red"> *</span>
                                {{ Form::date('due_date', $dueDate, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="form-group">
                                {{ Form::label('ref_number', __('Ref Number'), ['class' => 'form-label']) }}
                                {{ Form::text('ref_number', 'STO-' . \Auth::user()->purchaseNumberFormat($StockTransferOrder->branch_purchase_no), ['class' => 'form-control']) }}
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
                                <th class="col-price text-end">{{ __('Price') }}</th>
                                <th class="col-discount text-end">{{ __('Discount') }}</th>
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
                                <td colspan="5" class="text-end"><strong>{{ __('Discount') }}
                                        ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end totalDiscount">0.00</td>
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
