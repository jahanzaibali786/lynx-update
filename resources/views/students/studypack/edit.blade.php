@extends('layouts.admin')
@section('page-title')
    {{ __('StudyPack Edit') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('studypack.index') }}">{{ __('Study Pack') }}</a></li>
    <li class="breadcrumb-item">{{ __('StudyPack Edit') }}</li>
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script>
        // ─── State ────────────────────────────────────────────────────────────────────
        var studyPackItems = @json($invoice->items ?? []);
        var rowCounter = 0;
        var MAX_OPEN_ROWS = 4;

        // Build product options HTML once
        var PRODUCT_OPTS = '<option value="">— Select Item —</option>';
        @foreach ($product_services as $product)
            PRODUCT_OPTS += '<option value="{{ $product['id'] }}">' +
                '{{ addslashes($product['name']) }}</option>';
        @endforeach

        function formatAmount(value) {
            return (parseFloat(value) || 0).toFixed(2);
        }

        function appendHidden(wrapper, name, value) {
            wrapper.append($('<input>', {
                type: 'hidden',
                name: name,
                value: value || ''
            }));
        }

        $(function() {
            var $itemsTbody = $('#items-tbody');

            if (studyPackItems.length && !$itemsTbody.data('existing-loaded')) {
                $itemsTbody.data('existing-loaded', true);
                $itemsTbody.find('tr[data-entry-id]').remove();
                $('#empty-row').hide();
                $.each(studyPackItems, function(_, item) {
                    var entry = {
                        id: item.id || Date.now() + Math.random(),
                        item_id: item.product_id || item.item,
                        item_name: getProductName(item.product_id || item.item),
                        quantity: parseFloat(item.quantity) || 0,
                        price: parseFloat(item.price) || 0,
                        unit: '',
                        amount: (parseFloat(item.quantity) || 0) * (parseFloat(item.price) || 0),
                        discount: parseFloat(item.discount) || 0
                    };
                    $itemsTbody.append(buildLockedRow(entry));
                    studyPackItems[_] = entry;
                });
                renderHiddenInputs();
                updateTotals();
            }
        });

        function getProductName(productId) {
            var name = '';
            @foreach ($product_services as $product)
                if ('{{ $product['id'] }}' == productId) {
                    name = '{{ addslashes($product['name']) }}';
                }
            @endforeach
            return name;
        }

        // ─── Add line button ──────────────────────────────────────────────────────────
        $(document).off('click', '#addItemBtn').on('click', '#addItemBtn', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var openRows = $('#items-tbody tr[data-row-id]').length;
            if (openRows >= MAX_OPEN_ROWS) {
                show_toastr('warning', 'Please confirm the existing rows before adding more (max ' + MAX_OPEN_ROWS +
                    ' open).', 'warning');
                return;
            }
            appendInlineRow();
        });

        function appendInlineRow() {
            var tbody = document.getElementById('items-tbody');
            var now = Date.now();
            var lastAppendAt = parseInt(tbody.getAttribute('data-last-append-at') || '0', 10);

            if (now - lastAppendAt < 300) {
                return;
            }

            tbody.setAttribute('data-last-append-at', now);
            rowCounter++;
            var rid = rowCounter;

            var row = '<tr data-row-id="' + rid + '" class="inline-edit-row">' +
                // ① Item
                '<td class="col-item">' +
                '<select class="form-control form-control-sm custom-select row-item" data-rid="' + rid + '">' +
                PRODUCT_OPTS +
                '</select>' +
                '</td>' +
                // ② Quantity
                '<td class="col-quantity">' +
                '<input type="number" class="form-control form-control-sm row-quantity" data-rid="' + rid +
                '" placeholder="Qty" min="1" step="1" value="1">' +
                '<span class="unit-label" id="unit-' + rid + '"></span>' +
                '</td>' +
                // ③ Price
                '<td class="col-price">' +
                '<input type="number" class="form-control form-control-sm row-price" data-rid="' + rid +
                '" placeholder="0.00" min="0" step="0.01">' +
                '</td>' +
                // ④ Amount
                '<td class="col-amount text-end">' +
                '<span class="amount-display" id="amount-' + rid + '">0.00</span>' +
                '</td>' +
                // ⑤ Actions
                '<td class="col-actions" style="white-space:nowrap;">' +
                '<button type="button" class="btn btn-sm btn-primary confirm-row-btn" data-rid="' + rid +
                '" title="Confirm"><i class="ti ti-check"></i></button> ' +
                '<button type="button" class="btn btn-sm btn-danger discard-row-btn" data-rid="' + rid +
                '" title="Discard"><i class="ti ti-x"></i></button>' +
                '</td>' +
                '</tr>';

            $('#items-tbody').append(row);
            $('#empty-row').hide();

            // Auto-focus and open the dropdown for the new row
            setTimeout(function() {
                var selectElement = $('.row-item[data-rid="' + rid + '"]');
                if (selectElement.length) {
                    selectElement.focus();

                    // Try multiple methods to open the dropdown
                    // Method 1: Native click
                    selectElement[0].click();

                    // Method 2: Simulate mousedown event
                    selectElement.trigger('mousedown');

                    // Method 3: If using custom-select plugin
                    if (typeof selectElement.customSelect === 'function') {
                        selectElement.customSelect('open');
                    }

                    // Method 4: For Choices.js or Select2
                    if (selectElement.next('.choices').length) {
                        selectElement.next('.choices').find('input').focus().click();
                    }
                }
            }, 150);
        }

        // ─── Item change → load product details ──────────────────────────────────────
        $(document).off('change', '.row-item').on('change', '.row-item', function() {
            var rid = $(this).data('rid');
            var itemId = $(this).val();

            if (!itemId) {
                clearItemData(rid);
                return;
            }

            $.ajax({
                url: '{{ route('studypack.product') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    'product_id': itemId
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    var product = data.product;

                    // Set price
                    $('.row-price[data-rid="' + rid + '"]').val(parseFloat(product.sale_price || 0)
                        .toFixed(2));

                    // Set unit
                    $('#unit-' + rid).text(data.unit || '');

                    // Calculate amount
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

        // ─── Quantity / Price change → recalculate amount ────────────────────────────
        $(document).off('keyup change', '.row-quantity, .row-price').on('keyup change', '.row-quantity, .row-price',
            function() {
                var rid = $(this).data('rid');
                calculateRowAmount(rid);
            });

        function calculateRowAmount(rid) {
            var quantity = parseFloat($('.row-quantity[data-rid="' + rid + '"]').val()) || 0;
            var price = parseFloat($('.row-price[data-rid="' + rid + '"]').val()) || 0;
            var totalAmount = quantity * price;

            $('#amount-' + rid).text(totalAmount.toFixed(2));
        }

        // ─── Confirm row ──────────────────────────────────────────────────────────────
        $(document).off('click', '.confirm-row-btn').on('click', '.confirm-row-btn', function() {
            var rid = $(this).data('rid');
            var tr = $('tr[data-row-id="' + rid + '"]');

            var itemId = $('.row-item[data-rid="' + rid + '"]').val();
            var itemName = $('.row-item[data-rid="' + rid + '"] option:selected').text();
            var quantity = parseFloat($('.row-quantity[data-rid="' + rid + '"]').val()) || 0;
            var price = parseFloat($('.row-price[data-rid="' + rid + '"]').val()) || 0;
            var unit = $('#unit-' + rid).text();
            var amount = parseFloat($('#amount-' + rid).text()) || 0;

            if (!itemId) {
                show_toastr('error', 'Please select an item.', 'error');
                return;
            }
            if (quantity <= 0) {
                show_toastr('error', 'Please enter a valid quantity.', 'error');
                return;
            }
            if (price <= 0) {
                show_toastr('error', 'Please enter a valid price.', 'error');
                return;
            }

            var entry = {
                id: Date.now(),
                item_id: itemId,
                item_name: itemName,
                quantity: quantity,
                price: price,
                unit: unit,
                amount: amount
            };

            studyPackItems.push(entry);
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
                '<td class="text-end fw-semibold">' + formatAmount(e.amount) + '</td>' +
                '<td class="text-center">' +
                '<a href="#" class="edit-entry-btn text-primary me-1" data-id="' + e.id +
                '" title="Edit"><i class="ti ti-pencil"></i></a>' +
                '<a href="#" class="remove-entry-btn text-danger" data-id="' + e.id +
                '" title="Remove"><i class="ti ti-trash"></i></a>' +
                '</td>' +
                '</tr>';
        }

        // ─── Discard open row ─────────────────────────────────────────────────────────
        $(document).off('click', '.discard-row-btn').on('click', '.discard-row-btn', function() {
            $('tr[data-row-id="' + $(this).data('rid') + '"]').remove();
            checkEmptyState();
        });

        // ─── Remove confirmed entry ───────────────────────────────────────────────────
        $(document).off('click', '.remove-entry-btn').on('click', '.remove-entry-btn', function(e) {
            e.preventDefault();
            var id = parseInt($(this).data('id'));
            if (!confirm('Remove this item?')) return;

            studyPackItems = studyPackItems.filter(function(item) {
                return item.id !== id;
            });
            $('tr[data-entry-id="' + id + '"]').remove();
            renderHiddenInputs();
            updateTotals();
            checkEmptyState();
        });

        // ─── Edit confirmed entry ─────────────────────────────────────────────────────
        $(document).off('click', '.edit-entry-btn').on('click', '.edit-entry-btn', function(e) {
            e.preventDefault();
            var id = parseInt($(this).data('id'));
            var entry = studyPackItems.find(function(item) {
                return item.id === id;
            });
            if (!entry) return;

            var tr = $('tr[data-entry-id="' + id + '"]');

            // Replace quantity, price with editable inputs
            tr.find('td').eq(1).html(
                '<input type="number" class="form-control form-control-sm edit-quantity" value="' + entry
                .quantity +
                '" min="1" step="1" data-id="' + id + '" style="width:80px;">'
            );
            tr.find('td').eq(2).html(
                '<input type="number" class="form-control form-control-sm edit-price" value="' + entry.price +
                '" min="0" step="0.01" data-id="' + id + '" style="width:100px;">'
            );
            tr.find('td').eq(3).html(
                '<span class="edit-amount text-end fw-semibold">' + formatAmount(entry.amount) + '</span>'
            );
            tr.find('td').eq(4).html(
                '<a href="#" class="save-edit-btn text-success me-1" data-id="' + id +
                '" title="Save"><i class="ti ti-check"></i></a>' +
                '<a href="#" class="cancel-edit-btn text-muted" data-id="' + id +
                '" title="Cancel"><i class="ti ti-x"></i></a>'
            );
        });

        // Recalculate amount during edit
        $(document).off('input', '.edit-quantity, .edit-price').on('input', '.edit-quantity, .edit-price', function() {
            var id = parseInt($(this).data('id'));
            var tr = $('tr[data-entry-id="' + id + '"]');

            var quantity = parseFloat(tr.find('.edit-quantity').val()) || 0;
            var price = parseFloat(tr.find('.edit-price').val()) || 0;
            var totalAmount = quantity * price;

            tr.find('.edit-amount').text(formatAmount(totalAmount));
        });

        // Save edit
        $(document).off('click', '.save-edit-btn').on('click', '.save-edit-btn', function(e) {
            e.preventDefault();
            var id = parseInt($(this).data('id'));
            var tr = $('tr[data-entry-id="' + id + '"]');
            var entry = studyPackItems.find(function(item) {
                return item.id === id;
            });

            var quantity = parseFloat(tr.find('.edit-quantity').val()) || 0;
            var price = parseFloat(tr.find('.edit-price').val()) || 0;

            if (quantity <= 0) {
                show_toastr('error', 'Please enter a valid quantity.', 'error');
                return;
            }
            if (price <= 0) {
                show_toastr('error', 'Please enter a valid price.', 'error');
                return;
            }

            // Update entry
            var totalAmount = quantity * price;
            entry.quantity = quantity;
            entry.price = price;
            entry.amount = totalAmount;

            tr.replaceWith(buildLockedRow(entry));
            renderHiddenInputs();
            updateTotals();
        });

        // Cancel edit
        $(document).off('click', '.cancel-edit-btn').on('click', '.cancel-edit-btn', function(e) {
            e.preventDefault();
            var id = parseInt($(this).data('id'));
            var entry = studyPackItems.find(function(item) {
                return item.id === id;
            });
            $('tr[data-entry-id="' + id + '"]').replaceWith(buildLockedRow(entry));
        });

        // ─── Helper functions ─────────────────────────────────────────────────────────
        function renderHiddenInputs() {
            var wrapper = $('#hidden-inputs');
            wrapper.empty();

            $.each(studyPackItems, function(i, item) {
                var prefix = 'items[' + i + ']';
                appendHidden(wrapper, prefix + '[id]', item.id || '');
                appendHidden(wrapper, prefix + '[item]', item.item_id);
                appendHidden(wrapper, prefix + '[quantity]', item.quantity);
                appendHidden(wrapper, prefix + '[price]', item.price);
                appendHidden(wrapper, prefix + '[tax]', '');
                appendHidden(wrapper, prefix + '[itemTaxPrice]', '0');
                appendHidden(wrapper, prefix + '[itemTaxRate]', '0');
                appendHidden(wrapper, prefix + '[discount]', '0');
            });
        }

        function updateTotals() {
            var totalAmount = 0;

            $.each(studyPackItems, function(i, item) {
                totalAmount += item.amount;
            });

            $('.subTotal').text(formatAmount(totalAmount));
            $('.totalAmount').text(formatAmount(totalAmount));
            $('.subtotal').val(formatAmount(totalAmount));
        }

        function checkEmptyState() {
            var hasAny = $('#items-tbody tr[data-row-id], #items-tbody tr[data-entry-id]').length > 0;
            $('#empty-row').toggle(!hasAny);
        }

        // ─── Keyboard shortcuts ──────────────────────────────────────────────────────
        $(document).off('keydown', '.row-item, .row-quantity, .row-price').on('keydown',
            '.row-item, .row-quantity, .row-price',
            function(e) {
                var rid = $(this).data('rid');
                if (!rid) return;

                // Disable Tab key navigation
                if (e.key === 'Tab') {
                    e.preventDefault();
                    return false;
                }

                if (e.key === 'Enter' && e.shiftKey) {
                    // Shift+Enter: Add new line
                    e.preventDefault();
                    $('.confirm-row-btn[data-rid="' + rid + '"]').trigger('click');
                    // Small delay to let the confirm complete, then add new row
                    setTimeout(function() {
                        $('#addItemBtn').trigger('click');
                    }, 100);
                } else if (e.key === 'Enter') {
                    // Enter: Confirm current row
                    e.preventDefault();
                    $('.confirm-row-btn[data-rid="' + rid + '"]').trigger('click');
                } else if (e.key === 'Escape') {
                    // Escape: Discard current row
                    e.preventDefault();
                    $('.discard-row-btn[data-rid="' + rid + '"]').trigger('click');
                }
            });

        $(document).off('keydown', '.edit-quantity, .edit-price').on('keydown', '.edit-quantity, .edit-price', function(e) {
            var id = $(this).data('id');
            if (!id) return;

            // Disable Tab key navigation
            if (e.key === 'Tab') {
                e.preventDefault();
                return false;
            }

            if (e.key === 'Enter' && e.shiftKey) {
                // Shift+Enter: Save and add new line
                e.preventDefault();
                $('.save-edit-btn[data-id="' + id + '"]').trigger('click');
                // Small delay to let the save complete, then add new row
                setTimeout(function() {
                    $('#addItemBtn').trigger('click');
                }, 100);
            } else if (e.key === 'Enter') {
                // Enter: Save edit
                e.preventDefault();
                $('.save-edit-btn[data-id="' + id + '"]').trigger('click');
            } else if (e.key === 'Escape') {
                // Escape: Cancel edit
                e.preventDefault();
                $('.cancel-edit-btn[data-id="' + id + '"]').trigger('click');
            }
        });

        // ─── Form validation before submit ────────────────────────────────────────────
        $(document).off('submit', '#studypack-form').on('submit', '#studypack-form', function(e) {
            if (studyPackItems.length === 0) {
                e.preventDefault();
                show_toastr('error', 'Please add at least one item.', 'error');
                return false;
            }
        });

        // ─── Global Shift+Enter to add new row ────────────────────────────────────────
        $(document).off('keydown.studypackAddRow').on('keydown.studypackAddRow', function(e) {
            // Check if Shift+Enter is pressed anywhere on the page
            if (e.key === 'Enter' && e.shiftKey) {
                // Don't trigger if we're in a textarea or other multi-line input
                var target = $(e.target);
                if (target.is('textarea')) {
                    return; // Allow normal behavior in textareas
                }

                e.preventDefault();
                $('#addItemBtn').trigger('click');
            }
        });
    </script>

    <style>
        /* ══ Items table ═════════════════════════════════════════════════════════════ */
        #items-table-wrap {
            overflow-x: auto;
        }

        #items-table {
            min-width: 800px;
        }

        .col-quantity,
        .col-price {
            width: 120px;
        }

        .col-amount {
            width: 150px;
        }

        .col-actions {
            width: 80px;
        }

        /* Editable row highlight */
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

        /* Confirmed row */
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

        /* Small inputs */
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

        .taxes-display .badge {
            font-size: 10px;
            padding: 2px 6px;
        }

        #items-tbody .custom-select-wrapper {
            width: 100% !important;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        {{ Form::model($invoice, ['route' => ['studypack.update', $invoice->id], 'method' => 'PUT', 'class' => 'w-100', 'id' => 'studypack-form']) }}
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <div id="hidden-inputs"></div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                            <div class="form-group">
                                {{ Form::label('session', __('Session'), ['class' => 'form-label']) }}<span
                                    class="text-danger"> *</span>
                                {{ Form::select('session', $session, $invoice->session_id, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                            <div class="form-group">
                                {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}<span
                                    class="text-danger"> *</span>
                                {{ Form::select('class', $class ?? [], $selectedClassId, [
                                    'class' => 'form-control',
                                    'id' => 'class',
                                    'required' => 'required',
                                ]) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                            <div class="form-group">
                                {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}<span class="text-danger">
                                    *</span>
                                {{ Form::date('date', date('Y-m-d', strtotime($invoice->date)), ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                            <div class="form-group">
                                {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}<span
                                    class="text-danger"> *</span>
                                {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter StudyPack Title'), 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12">
                            <div class="form-group">
                                {{ Form::label('study_pack_cost', __('Study Pack Cost'), ['class' => 'form-label']) }}<span
                                    class="text-danger"> *</span>
                                {{ Form::text('study_pack_cost', null, ['class' => 'form-control subtotal', 'placeholder' => __('Enter StudyPack Cost'), 'required' => 'required', 'readonly' => 'readonly']) }}
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
                        <i class="ti ti-plus"></i> {{ __('Add Item') }}
                    </button>
                </div>
                <div id="items-table-wrap" class="card-body table-border-style pt-0">
                    <table class="table table-sm mb-0" id="items-table">
                        <thead>
                            <tr>
                                <th class="col-item">{{ __('Products') }}</th>
                                <th class="col-quantity">{{ __('Quantity') }}</th>
                                <th class="col-price text-end">{{ __('Price') }}</th>
                                <th class="col-amount text-end">{{ __('Amount') }}</th>
                                <th class="col-actions"></th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            <tr id="empty-row">
                                <td colspan="5" class="text-center text-muted py-4">
                                    {{ __('No items added yet. Click "Add Item" to begin.') }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>{{ __('Total Amount') }}
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
            <input type="button" value="{{ __('Cancel') }}" onclick="location.href = '{{ route('studypack.index') }}';"
                class="btn btn-light me-3">
            <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
        </div>
        {{ Form::close() }}
    </div>
@endsection
