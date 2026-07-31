@extends('layouts.admin')
@section('page-title')
    {{ __('Create Demand Order') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('demand-order.index') }}">{{ __('Demand Order') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create Demand Order') }}</li>
@endsection
@section('content')
    <style>
#items-table-wrap { overflow-x: auto; }
#items-table { min-width: 800px; }
.col-quantity, .col-price, .col-discount { width: 110px; }
.col-amount { width: 140px; }
.col-actions { width: 80px; }
.inline-edit-row { background: #f5f8ff !important; }
.inline-edit-row:hover { background: #edf2ff !important; }
.inline-edit-row td { vertical-align: top; padding: 8px 6px; }
.confirmed-row { background: #fff; }
.confirmed-row:hover { background: #fafafa; }
.confirmed-row td { vertical-align: middle; padding: 8px 6px; }
.inline-edit-row .form-control-sm { height: 31px; font-size: 12px; padding: 3px 7px; }
.unit-label { font-size: 11px; color: #6c757d; margin-left: 4px; }
.tax-badge { font-size: 10px; padding: 2px 6px; }
#items-tbody .custom-select-wrapper { width: 100% !important; }
</style>
<script>
var productServices = @json($product_services);
var purchaseItems = [];
var rowCounter = 0;
var MAX_OPEN_ROWS = 10;
var csrfToken = '{{ csrf_token() }}';
var productUrl = '{{ route("purchase.product") }}';
var currencySymbol = '{{ \Auth::user()->currencySymbol() }}';

var PRODUCT_OPTS = '<option value="">— Select Item —</option>';
@if(isset($product_services) && count($product_services) > 0)
@foreach ($product_services as $val => $label)
PRODUCT_OPTS += '<option value="{{ $val }}">{{ addslashes($label) }}</option>';
@endforeach
@endif

function formatAmount(value) {
    return (parseFloat(value) || 0).toFixed(2);
}

function appendHidden(wrapper, name, value) {
    var v = (value === 0 || value === '0') ? '0' : (value || '');
    wrapper.append($('<input>', { type: 'hidden', name: name, value: v }));
}

$(document).off('click', '#addItemBtn').on('click', '#addItemBtn', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    var openRows = $('#items-tbody tr[data-row-id]').length;
    if (openRows >= MAX_OPEN_ROWS) {
        show_toastr('warning', 'Please confirm the existing rows before adding more (max ' + MAX_OPEN_ROWS + ' open).', 'warning');
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
        PRODUCT_OPTS +
        '</select>' +
        '</td>' +
        '<td class="col-quantity">' +
        '<input type="number" class="form-control form-control-sm row-quantity" data-rid="' + rid + '" placeholder="Qty" min="1" step="1" value="1">' +
        '<span class="unit-label" id="unit-' + rid + '"></span>' +
        '</td>' +
        '<td class="col-price">' +
        '<input type="number" class="form-control form-control-sm row-price" data-rid="' + rid + '" placeholder="0.00" min="0" step="0.01" readonly>' +
        '</td>' +
        '<td class="col-discount">' +
        '<input type="number" class="form-control form-control-sm row-discount" data-rid="' + rid + '" placeholder="0.00" min="0" step="0.01" value="0" readonly>' +
        '</td>' +
        '<td class="col-amount text-end">' +
        '<span class="amount-display" id="amount-' + rid + '">0.00</span>' +
        '</td>' +
        '<td class="col-actions" style="white-space:nowrap;">' +
        '<button type="button" class="btn btn-sm btn-primary confirm-row-btn" data-rid="' + rid + '" title="Confirm"><i class="ti ti-check"></i></button> ' +
        '<button type="button" class="btn btn-sm btn-outline-danger discard-row-btn" data-rid="' + rid + '" title="Discard"><i class="ti ti-x"></i></button>' +
        '</td>' +
        '</tr>';

    $('#items-tbody').append(row);
    $('#empty-row').hide();

    setTimeout(function() {
        var selectElement = $('.row-item[data-rid="' + rid + '"]');
        if (selectElement.length) {
            selectElement.focus();
            selectElement[0].click();
            selectElement.trigger('mousedown');
        }
    }, 150);
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
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: { product_id: itemId },
        success: function(response) {
            var data = JSON.parse(response);
            var product = data.product;

            $('.row-price[data-rid="' + rid + '"]').val(parseFloat(product.sale_price || 0).toFixed(2));

            $('#unit-' + rid).text(data.unit || '');
            calculateRowAmount(rid);
        },
        error: function() {
            show_toastr('error', 'Failed to load product details', 'error');
        }
    });
});

function clearItemData(rid) {
    $('.row-price[data-rid="' + rid + '"]').val('');
    $('#unit-' + rid).text('');
    $('#amount-' + rid).text('0.00');
}

$(document).on('keyup change', '.row-quantity, .row-price, .row-discount', function() {
    var rid = $(this).data('rid');
    calculateRowAmount(rid);
});

function calculateRowAmount(rid) {
    var quantity = parseFloat($('.row-quantity[data-rid="' + rid + '"]').val()) || 0;
    var price = parseFloat($('.row-price[data-rid="' + rid + '"]').val()) || 0;
    var discount = parseFloat($('.row-discount[data-rid="' + rid + '"]').val()) || 0;
    var totalItemPrice = (quantity * price) - discount;
    $('#amount-' + rid).text(totalItemPrice.toFixed(2));
}

$(document).on('click', '.confirm-row-btn', function() {
    var rid = $(this).data('rid');
    var tr = $('tr[data-row-id="' + rid + '"]');

    var itemId = $('.row-item[data-rid="' + rid + '"]').val();
    var itemName = $('.row-item[data-rid="' + rid + '"] option:selected').text();
    var quantity = parseFloat($('.row-quantity[data-rid="' + rid + '"]').val()) || 0;
    var price = parseFloat($('.row-price[data-rid="' + rid + '"]').val()) || 0;
    var discount = parseFloat($('.row-discount[data-rid="' + rid + '"]').val()) || 0;
    var unit = $('#unit-' + rid).text();
    var amount = parseFloat($('#amount-' + rid).text()) || 0;
    if (!itemId) { show_toastr('error', 'Please select an item.', 'error'); return; }
    if (quantity <= 0) { show_toastr('error', 'Please enter a valid quantity.', 'error'); return; }
    if (price <= 0) { show_toastr('error', 'Please enter a valid price.', 'error'); return; }

    var entry = {
        id: Date.now(),
        item_id: itemId,
        item_name: itemName,
        quantity: quantity,
        price: price,
        discount: discount,
        unit: unit,
        amount: amount,
        description: ''
    };

    purchaseItems.push(entry);
    tr.replaceWith(buildLockedRow(entry));
    renderHiddenInputs();
    updateTotals();
    checkEmptyState();
});

function buildLockedRow(e) {
    return '<tr data-entry-id="' + e.id + '" class="confirmed-row">' +
        '<td>' + e.item_name + '</td>' +
        '<td>' + e.quantity + (e.unit ? ' <small class="text-muted">' + e.unit + '</small>' : '') + '</td>' +
        '<td class="text-end">' + formatAmount(e.price) + '</td>' +
        '<td class="text-end">' + formatAmount(e.discount) + '</td>' +
        '<td class="text-end fw-semibold">' + formatAmount(e.amount) + '</td>' +
        '<td class="text-center">' +
        '<a href="#" class="edit-entry-btn text-primary me-1" data-id="' + e.id + '" title="Edit"><i class="ti ti-pencil"></i></a>' +
        '<a href="#" class="remove-entry-btn text-danger" data-id="' + e.id + '" title="Remove"><i class="ti ti-trash"></i></a>' +
        '</td>' +
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
    purchaseItems = purchaseItems.filter(function(item) { return item.id !== id; });
    $('tr[data-entry-id="' + id + '"]').remove();
    renderHiddenInputs();
    updateTotals();
    checkEmptyState();
});

$(document).on('click', '.edit-entry-btn', function(e) {
    e.preventDefault();
    var id = parseInt($(this).data('id'));
    var entry = purchaseItems.find(function(item) { return item.id === id; });
    if (!entry) return;
    var tr = $('tr[data-entry-id="' + id + '"]');
    tr.find('td').eq(1).html('<input type="number" class="form-control form-control-sm edit-quantity" value="' + entry.quantity + '" min="1" step="1" data-id="' + id + '" style="width:80px;">' + (entry.unit ? ' <small class="text-muted">' + entry.unit + '</small>' : ''));
    tr.find('td').eq(2).html('<strong class="text-muted">' + formatAmount(entry.price) + '</strong>');
    tr.find('td').eq(3).html('<strong class="text-muted">' + formatAmount(entry.discount) + '</strong>');
    tr.find('td').eq(4).html('<span class="edit-amount text-end fw-semibold">' + formatAmount(entry.amount) + '</span>');
    tr.find('td').eq(5).html('<a href="#" class="save-edit-btn text-success me-1" data-id="' + id + '" title="Save"><i class="ti ti-check"></i></a><a href="#" class="cancel-edit-btn text-muted" data-id="' + id + '" title="Cancel"><i class="ti ti-x"></i></a>');
});

$(document).on('input', '.edit-quantity, .edit-discount', function() {
    var id = parseInt($(this).data('id'));
    var tr = $('tr[data-entry-id="' + id + '"]');
    var entry = purchaseItems.find(function(item) { return item.id === id; });
    if (!entry) return;
    var quantity = parseFloat(tr.find('.edit-quantity').val()) || 0;
    var discount = parseFloat(tr.find('.edit-discount').val()) || 0;
    tr.find('.edit-amount').text(formatAmount((quantity * entry.price) - discount));
});

$(document).on('click', '.save-edit-btn', function(e) {
    e.preventDefault();
    var id = parseInt($(this).data('id'));
    var tr = $('tr[data-entry-id="' + id + '"]');
    var entry = purchaseItems.find(function(item) { return item.id === id; });
    var quantity = parseFloat(tr.find('.edit-quantity').val()) || 0;
    var discount = parseFloat(tr.find('.edit-discount').val()) || 0;
    if (quantity <= 0) { show_toastr('error', 'Please enter a valid quantity.', 'error'); return; }
    entry.quantity = quantity; entry.discount = discount; entry.amount = (quantity * entry.price) - discount;
    tr.replaceWith(buildLockedRow(entry));
    renderHiddenInputs(); updateTotals();
});

$(document).on('click', '.cancel-edit-btn', function(e) {
    e.preventDefault();
    var id = parseInt($(this).data('id'));
    var entry = purchaseItems.find(function(item) { return item.id === id; });
    $('tr[data-entry-id="' + id + '"]').replaceWith(buildLockedRow(entry));
});

function renderHiddenInputs() {
    var wrapper = $('#hidden-inputs');
    wrapper.empty();
    $.each(purchaseItems, function(i, item) {
        var prefix = 'items[' + i + ']';
        appendHidden(wrapper, prefix + '[id]', item.id && item.id.toString().length < 15 ? item.id : '0');
        appendHidden(wrapper, prefix + '[item]', item.item_id);
        appendHidden(wrapper, prefix + '[quantity]', item.quantity);
        appendHidden(wrapper, prefix + '[price]', item.price);
        appendHidden(wrapper, prefix + '[discount]', item.discount);
        appendHidden(wrapper, prefix + '[tax]', '');
        appendHidden(wrapper, prefix + '[description]', item.description || '');
    });
}

function updateTotals() {
    var subTotal = 0, totalDiscount = 0;
    $.each(purchaseItems, function(i, item) {
        subTotal += (item.quantity * item.price);
        totalDiscount += item.discount;
    });
    $('.subTotal').text(formatAmount(subTotal));
    $('.totalDiscount').text(formatAmount(totalDiscount));
    $('.totalAmount').text(formatAmount(subTotal - totalDiscount));
}

function checkEmptyState() {
    var hasAny = $('#items-tbody tr[data-row-id], #items-tbody tr[data-entry-id]').length > 0;
    $('#empty-row').toggle(!hasAny);
}

$(document).on('keydown', '.row-item, .row-quantity, .row-price, .row-discount', function(e) {
    var rid = $(this).data('rid');
    if (!rid) return;
    if (e.key === 'Tab') { e.preventDefault(); return false; }
    if (e.key === 'Enter' && e.shiftKey) { e.preventDefault(); $('.confirm-row-btn[data-rid="' + rid + '"]').trigger('click'); setTimeout(function() { $('#addItemBtn').trigger('click'); }, 100); }
    else if (e.key === 'Enter') { e.preventDefault(); $('.confirm-row-btn[data-rid="' + rid + '"]').trigger('click'); }
    else if (e.key === 'Escape') { e.preventDefault(); $('.discard-row-btn[data-rid="' + rid + '"]').trigger('click'); }
});

$(document).on('keydown', '.edit-quantity', function(e) {
    var id = $(this).data('id');
    if (!id) return;
    if (e.key === 'Tab') { e.preventDefault(); return false; }
    if (e.key === 'Enter' && e.shiftKey) { e.preventDefault(); $('.save-edit-btn[data-id="' + id + '"]').trigger('click'); setTimeout(function() { $('#addItemBtn').trigger('click'); }, 100); }
    else if (e.key === 'Enter') { e.preventDefault(); $('.save-edit-btn[data-id="' + id + '"]').trigger('click'); }
    else if (e.key === 'Escape') { e.preventDefault(); $('.cancel-edit-btn[data-id="' + id + '"]').trigger('click'); }
});

$(document).on('submit', '#demand-order-form', function(e) {
    if (purchaseItems.length === 0) { e.preventDefault(); show_toastr('error', 'Please add at least one item.', 'error'); return false; }
});

$(document).on('keydown', function(e) {
    if (e.key === 'Enter' && e.shiftKey) { var target = $(e.target); if (target.is('textarea')) return; e.preventDefault(); $('#addItemBtn').trigger('click'); }
});

$(document).on('change', '#branch_id', function() {
    $('#branch_detail').removeClass('d-none').addClass('d-block');
    $('#branch-box').removeClass('d-block').addClass('d-none');
    var id = $(this).val();
    var url = $(this).data('url');
    $.ajax({
        url: url, type: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: { 'id': id }, cache: false,
        success: function(data) {
            if (data != '') { $('#branch_detail').html(data); }
            else { $('#branch-box').removeClass('d-none').addClass('d-block'); $('#branch_detail').removeClass('d-block').addClass('d-none'); }
        }
    });
});

$(document).on('click', '#remove', function() {
    $('#branch-box').removeClass('d-none').addClass('d-block');
    $('#branch_detail').removeClass('d-block').addClass('d-none');
});

$(document).ready(function() {
    if (typeof ajaxModalForm !== 'undefined') {
        ajaxModalForm({ formSelector: '.demand-order-ajax-form', submitText: '{{ __("Creating...") }}', onSuccess: function (r) { $.ajax({ url: window.location.href, cache: false, dataType: 'html', success: function(html) { var el = new DOMParser().parseFromString(html, 'text/html').getElementById('content-area'); if (el) { document.getElementById('content-area').innerHTML = el.innerHTML; try { common_bind(); commonLoader(); } catch(e){} } else { location.reload(); } }, error: function() { location.reload(); } }); } });
    }
});
</script>

    <div class="row">
        {{ Form::open(['url' => 'demand-order', 'class' => 'w-100 demand-order-ajax-form', 'id' => 'demand-order-form', 'novalidate' => true]) }}
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <div id="hidden-inputs"></div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" id="branch-box">
                                {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                                {{ Form::select('branch_id', $branches, $branchId, ['class' => 'form-control select', 'id' => 'branch_id', 'data-url' => route('demand-order.vender'), 'required' => 'required']) }}
                            </div>
                            <div id="branch_detail" class="d-none"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('warehouse_id', __('Warehouse'), ['class' => 'form-label']) }}
                                        {{ Form::select('warehouse_id', $warehouse, null, ['class' => 'form-control select', 'required' => 'required']) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('purchase_number', __('Demand Order Number'), ['class' => 'form-label']) }}
                                        <input type="text" class="form-control" value="{{ $purchase_number }}" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('purchase_date', __('Demand Order Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('purchase_date', null, ['class' => 'form-control', 'required' => 'required']) }}
                                    </div>
                                </div>
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
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                        <i class="ti ti-plus"></i> {{ __('Add Item') }}
                    </button>
                </div>
                <div id="items-table-wrap" class="card-body table-border-style pt-0">
                    <table class="table table-sm mb-0" id="items-table">
                        <thead>
                            <tr>
                                <th class="col-item">{{ __('Items') }}</th>
                                <th class="col-quantity">{{ __('Quantity') }}</th>
                                <th class="col-price text-end">{{ __('Price') }}</th>
                                <th class="col-discount text-end">{{ __('Discount') }}</th>
                                <th class="col-amount text-end">{{ __('Amount') }}</th>
                                <th class="col-actions"></th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            <tr id="empty-row">
                                <td colspan="6" class="text-center text-muted py-4">
                                    {{ __('No items added yet. Click "Add Item" to begin. Shortcut "SHIFT + ENTER"') }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>{{ __('Sub Total') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end subTotal fw-bold">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>{{ __('Discount') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end totalDiscount">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>{{ __('Total Amount') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                <td class="text-end totalAmount fw-bold">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" onclick="location.href = '{{ route('demand-order.index') }}';" class="btn btn-outline-light">
            <input type="submit" value="{{ __('Create') }}" class="btn btn-outline-primary">
        </div>
        {{ Form::close() }}
    </div>
@endsection
