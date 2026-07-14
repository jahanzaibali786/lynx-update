@php
    $isEdit = !empty($grn);
    $formDefaults = $formDefaults ?? [];
    $submitLabel = $submitLabel ?? ($isEdit ? __('Update') : __('Create'));
    $cancelUrl = $cancelUrl ?? route('grn.index');
    $showPurchaseLink = $showPurchaseLink ?? true;
    $showAddVendorLink = $showAddVendorLink ?? true;
    $providedInitialItems = $initialItems ?? null;
    $initialItems = !is_null($providedInitialItems)
        ? collect($providedInitialItems)
        : ($isEdit
            ? $grn->items
                ->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'condition' => $item->condition,
                        'quantity' => (float) $item->quantity,
                        'price' => (float) $item->price,
                        'description' => $item->description,
                    ];
                })
                ->values()
            : collect([
                [
                    'product_id' => '',
                    'condition' => 'new',
                    'quantity' => 1,
                    'price' => 0,
                    'description' => '',
                ],
            ]));
@endphp

    <style>
        .grn-row-locked select,
        .grn-row-locked input,
        .grn-row-locked textarea {
            background: #f8f9fa;
            pointer-events: none;
        }

        .grn-action {
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
    <script>
        var grnProducts = @json($productMeta);
        var grnProductOptions = @json($productOptions);
        var initialGrnItems = @json($initialItems);

        function rebuildCustomSelect($select) {
            const element = $select[0];
            if (!element) {
                return;
            }
            if (element.customSelectInstance) {
                element.customSelectInstance.destroy();
                element.customSelectInstance = null;
            }
            $select.next('.custom-select-wrapper').remove();
            $select.show();
            setTimeout(function() {
                if (window.CustomSelect) {
                    window.CustomSelect.initContainer($select.parent()[0]);
                }
            }, 0);
        }

        function productOptionsHtml(selected) {
            return Object.entries(grnProductOptions).map(([id, name]) => {
                const value = id === '' ? '' : id;
                return `<option value="${value}" ${String(selected) === String(value) ? 'selected' : ''}>${name}</option>`;
            }).join('');
        }

        function grnRowHtml(item = {}, index = 0, locked = false) {
            const condition = item.condition || 'new';
            const sourceBadge = item.source ? `<span class="badge bg-info me-1 text-xs">${item.source}</span>` : '';
            return `
                <tr class="${locked ? 'grn-row-locked' : ''}">
                    <td>
                        ${sourceBadge}
                        <select name="items[${index}][product_id]" class="form-control custom-select item-select" required placeholder="Select Product">
                            ${productOptionsHtml(item.product_id || '')}
                        </select>
                    </td>
                    <td>
                        <select name="items[${index}][condition]" class="form-control condition-select" required>
                            <option value="new" ${condition === 'new' ? 'selected' : ''}>New</option>
                            <option value="used" ${condition === 'used' ? 'selected' : ''}>Used</option>
                            <option value="damaged" ${condition === 'damaged' ? 'selected' : ''}>Damaged</option>
                        </select>
                    </td>
                    <td><input type="number" name="items[${index}][quantity]" class="form-control qty-input" min="0.01" step="0.01" value="${item.quantity || 1}" required></td>
                    <td><input type="number" name="items[${index}][price]" class="form-control price-input" min="0" step="0.01" value="${item.price || 0}"></td>
                    <td><input type="text" name="items[${index}][description]" class="form-control desc-input" value="${(item.description || '').replace(/"/g, '&quot;')}"></td>
                    <td class="text-end amount-cell">0.00</td>
                    <td class="text-center row-actions">
                        ${locked ? lockedActionsHtml() : editActionsHtml()}
                    </td>
                </tr>`;
        }

        function editActionsHtml() {
            return `
                <button type="button" class="btn btn-sm border-0 bg-transparent text-success grn-action confirm-row" title="Confirm"><i class="ti ti-check"></i></button>
                <button type="button" class="btn btn-sm border-0 bg-transparent text-danger grn-action discard-row" title="Discard"><i class="ti ti-x"></i></button>`;
        }

        function lockedActionsHtml() {
            return `
                <button type="button" class="btn btn-sm border-0 bg-transparent text-primary grn-action edit-row" title="Edit"><i class="ti ti-pencil"></i></button>
                <button type="button" class="btn btn-sm border-0 bg-transparent text-danger grn-action delete-row" title="Delete"><i class="ti ti-trash"></i></button>`;
        }

        function setGrnSelectLocked($select, locked) {
            const mirrorClass = 'locked-select-hidden';
            const selectName = $select.attr('name');

            $select.siblings('input.' + mirrorClass + '[data-for="' + selectName + '"]').remove();

            if (locked) {
                $('<input>', {
                    type: 'hidden',
                    class: mirrorClass,
                    'data-for': selectName,
                    name: selectName,
                    value: $select.val()
                }).insertAfter($select);
                $select.prop('disabled', true);
            } else {
                $select.prop('disabled', false);
            }
        }

        function reindexRows() {
            $('#grn-items-table tbody tr').each(function(index) {
                $(this).find('[name]').each(function() {
                    this.name = this.name.replace(/items\[\d+\]/, `items[${index}]`);
                });
            });
        }

        function recalcRow($row) {
            const qty = parseFloat($row.find('.qty-input').val()) || 0;
            const price = parseFloat($row.find('.price-input').val()) || 0;
            $row.find('.amount-cell').text((qty * price).toFixed(2));
            recalcTotal();
        }

        function recalcTotal() {
            let total = 0;
            $('.amount-cell').each(function() {
                total += parseFloat($(this).text()) || 0;
            });
            $('#grn-total').text(total.toFixed(2));
        }

        function isGrnRowBlank($row) {
            return !($row.find('.item-select').val()) &&
                !(parseFloat($row.find('.qty-input').val()) || 0) &&
                !(parseFloat($row.find('.price-input').val()) || 0) &&
                !($row.find('.desc-input').val());
        }

        function focusFirstIncompleteField($row) {
            if (!$row.find('.item-select').val()) {
                $row.find('.item-select').focus();
            } else if ((parseFloat($row.find('.qty-input').val()) || 0) <= 0) {
                $row.find('.qty-input').focus();
            } else {
                $row.find('.condition-select').focus();
            }
        }

        function lockRow($row) {
            const product = $row.find('.item-select').val();
            const qty = parseFloat($row.find('.qty-input').val()) || 0;
            if (!product || qty <= 0) {
                show_toastr('error', 'Please select item and quantity.', 'error');
                return false;
            }
            $row.addClass('grn-row-locked');
            $row.find('.item-select').each(function() {
                setGrnSelectLocked($(this), true);
            });
            $row.find('.row-actions').html(lockedActionsHtml());
            recalcRow($row);
            show_toastr('success', 'Item confirmed successfully', 'success');
            return true;
        }

        function addRow(item = {}, locked = false, focus = false) {
            const $emptyRows = $('#grn-items-table tbody tr').filter(function() {
                return !$(this).find('.item-select').val();
            });
            if ($emptyRows.length >= 3) {
                show_toastr('error', 'Please fill existing items before adding more.', 'error');
                $emptyRows.first().find('.item-select').focus();
                return;
            }

            const index = $('#grn-items-table tbody tr').length;
            $('#grn-items-table tbody').append(grnRowHtml(item, index, locked));
            const $row = $('#grn-items-table tbody tr:last');
            recalcRow($row);
            if (focus) {
                setTimeout(() => $row.find('.item-select').focus(), 50);
            }
        }

        $(document).on('change', '.item-select', function() {
            const $row = $(this).closest('tr');
            const productId = $(this).val();
            if (!productId) return;
            const meta = grnProducts[productId] || {};
            $row.find('.price-input').val(meta.price || 0);
            if (!$row.find('.desc-input').val()) {
                $row.find('.desc-input').val(meta.description || '');
            }
            recalcRow($row);
            if (!$row.hasClass('grn-row-locked') && parseFloat($row.find('.qty-input').val()) > 0) {
                lockRow($row);
            }
        });

        $(document).on('input', '.qty-input, .price-input', function() {
            recalcRow($(this).closest('tr'));
        });

        $(document).on('keydown', '.item-select, .qty-input, .price-input, .condition-select', function(e) {
            const $row = $(this).closest('tr');

            if (e.key === 'Enter' && e.shiftKey) {
                e.preventDefault();
                if (lockRow($(this).closest('tr'))) {
                    addRow({
                        condition: 'new',
                        quantity: 1,
                        price: 0,
                        description: ''
                    }, false, true);
                }
                return false;
            }

            if (e.key === 'Enter') {
                e.preventDefault();
                if ($(this).hasClass('item-select')) {
                    $row.find('.qty-input').focus();
                } else if ($(this).hasClass('qty-input')) {
                    $row.find('.price-input').focus();
                } else if ($(this).hasClass('price-input')) {
                    $row.find('.condition-select').focus();
                } else if ($(this).hasClass('condition-select')) {
                    if (lockRow($row)) {
                        addRow({
                            condition: 'new',
                            quantity: 1,
                            price: 0,
                            description: ''
                        }, false, true);
                    }
                }
                return false;
            }

            if (e.key === 'Escape') {
                e.preventDefault();
                $row.find('.discard-row').trigger('click');
                return false;
            }
        });

        $(document).on('click', '.confirm-row', function() {
            lockRow($(this).closest('tr'));
        });

        $(document).on('click', '.edit-row', function() {
            const $row = $(this).closest('tr');
            $row.removeClass('grn-row-locked');
            $row.find('.item-select').each(function() {
                setGrnSelectLocked($(this), false);
            });
            $row.find('.row-actions').html(editActionsHtml());
            $row.find('.item-select').focus();
        });

        $(document).on('click', '.discard-row, .delete-row', function() {
            if ($(this).hasClass('delete-row') && !confirm('Are you sure you want to delete this element?')) {
                return;
            }
            if ($('#grn-items-table tbody tr').length <= 1) {
                const $row = $(this).closest('tr');
                $row.find('.item-select').val('');
                $row.find('.condition-select').val('new');
                $row.find('.qty-input').val(1);
                $row.find('.price-input').val(0);
                $row.find('.desc-input').val('');
                $row.removeClass('grn-row-locked');
                $row.find('.row-actions').html(editActionsHtml());
                recalcRow($row);
                return;
            }
            $(this).closest('tr').remove();
            reindexRows();
            recalcTotal();
        });

        $(document).on('click', '#add-grn-row', function() {
            addRow({
                condition: 'new',
                quantity: 1,
                price: 0,
                description: ''
            }, false, true);
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Enter' && e.shiftKey) {
                const target = $(e.target);
                if (target.is('.item-select, .qty-input, .price-input, .condition-select, textarea, input')) {
                    return;
                }
                e.preventDefault();
                addRow({
                    condition: 'new',
                    quantity: 1,
                    price: 0,
                    description: ''
                }, false, true);
            }
        });
        $('#grn-form').on('submit', function(e) {
            $('#grn-items-table tbody tr').not('.grn-row-locked').each(function() {
                const $row = $(this);
                if ($row.find('.item-select').val() && parseFloat($row.find('.qty-input').val()) > 0) {
                    lockRow($row);
                } else if (isGrnRowBlank($row)) {
                    $row.remove();
                }
            });
            reindexRows();
            recalcTotal();

            if ($('#grn-items-table tbody tr.grn-row-locked').length === 0) {
                e.preventDefault();
                show_toastr('error', 'Please add at least one item.', 'error');
                return false;
            }

            if ($('#grn-items-table tbody tr').not('.grn-row-locked').length > 0) {
                e.preventDefault();
                show_toastr('error', 'Please complete or remove open item rows.', 'error');
                return false;
            }
        });

        $(function() {
            $('#grn-items-table tbody').empty();
            initialGrnItems.forEach(item => addRow(item, Boolean(item.product_id)));
            $('#grn-items-table tbody tr.grn-row-locked .item-select').each(function() {
                setGrnSelectLocked($(this), true);
            });
        });
    </script>

<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        {{ Form::label('grn_no', __('GRN No'), ['class' => 'form-label']) }}
                        {{ Form::text('grn_no', 'GRN-' . sprintf('%05d', $isEdit ? $grn->grn_no : $nextGrnNumber), ['class' => 'form-control', 'disabled' => true]) }}
                    </div>
                    <div class="col-md-3">
                        {{ Form::label('grn_date', __('GRN Date'), ['class' => 'form-label']) }}
                        {{ Form::date('grn_date', old('grn_date', $isEdit ? $grn->grn_date : ($formDefaults['grn_date'] ?? date('Y-m-d'))), ['class' => 'form-control', 'required' => 'required']) }}
                    </div>
                    <div class="col-md-3">
                        {{ Form::label('vendor_id', __('Vendor'), ['class' => 'form-label']) }}
                        {{ Form::select('vendor_id', $vendors, old('vendor_id', $isEdit ? $grn->vendor_id : ($formDefaults['vendor_id'] ?? '')), ['class' => 'form-control select custom-select', 'id' => 'grn_vendor_id', 'required' => 'required']) }}
                        @if($showAddVendorLink)
                            <div class="text-xs mt-1">
                                <a href="#" data-ajax-popup-over="true" data-url="{{ route('grn.add_vendor_form') }}" data-title="{{ __('Add Vendor') }}">{{ __('Add Vendor') }}</a>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-3">
                        {{ Form::label('reference_no', __('Reference No'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                        {{ Form::text('reference_no', old('reference_no', $isEdit ? $grn->reference_no : ($formDefaults['reference_no'] ?? '')), ['class' => 'form-control', 'required' => 'required']) }}
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-3">
                        {{ Form::label('purchase_order_id', __('Purchase Order'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                        {{ Form::text('purchase_order_id', old('purchase_order_id', $isEdit ? $grn->purchase_order_id : ($formDefaults['purchase_order_id'] ?? '')), ['class' => 'form-control', 'required' => 'required']) }}
                        @if($showPurchaseLink)
                            <div class="text-xs mt-1">
                                <a href="#" data-ajax-popup-over="true" data-url="{{ route('grn.draft_purchases') }}" data-title="{{ __('Link Purchase') }}">{{ __('Link Purchase') }}</a>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-3">
                        {{ Form::label('warehouse_id', __('Store'), ['class' => 'form-label']) }}
                        <select name="warehouse_id" id="warehouse_id" class="form-control select" required>
                            <option value="">{{ __('Select Store') }}</option>
                            @foreach ($warehouseRecords as $warehouse)
                                <option value="{{ $warehouse->id }}" data-branch="{{ $warehouse->owned_by }}"
                                    {{ (string) old('warehouse_id', $isEdit ? $grn->warehouse_id : ($formDefaults['warehouse_id'] ?? '')) === (string) $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        {{ Form::label('remarks', __('Remarks'), ['class' => 'form-label']) }}
                        {{ Form::text('remarks', old('remarks', $isEdit ? $grn->remarks : ($formDefaults['remarks'] ?? '')), ['class' => 'form-control']) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Product / Items') }}</h5>
                <button type="button" id="add-grn-row" class="btn btn-sm btn-outline-primary">
                    <i class="ti ti-plus text-light"></i> {{ __('Add') }}
                </button>
            </div>
            <div class="px-3 pb-0 text-muted small">
                {{ __('Click "Add" to add a product, or use shortcut Shift+Enter') }}
            </div>
            <div class="card-body table-responsive">
                <table class="table" id="grn-items-table">
                    <thead>
                        <tr>
                            <th style="width: 28%;">{{ __('Item') }}</th>
                            <th style="width: 12%;">{{ __('Type') }}</th>
                            <th style="width: 12%;">{{ __('Quantity') }}</th>
                            <th style="width: 12%;">{{ __('Cost') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th style="width: 12%;" class="text-end">{{ __('Amount') }}</th>
                            <th style="width: 95px;" class="text-center">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="5" class="text-end">{{ __('Total') }}</th>
                            <th class="text-end" id="grn-total">0.00</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="text-end">
            <a href="{{ $cancelUrl }}" class="btn btn-light me-2">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-outline-primary">{{ $submitLabel }}</button>
        </div>
    </div>
</div>
