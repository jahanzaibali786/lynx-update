@extends('layouts.admin')
@section('page-title')
    {{ __('Invoice Edit') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('invoice.index') }}">{{ __('Invoice') }}</a></li>
    <li class="breadcrumb-item">{{ __('Invoice Edit') }}</li>
@endsection
@section('content')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var selector = "body";
        if ($(selector + " .repeater").length) {
            var $dragAndDrop = $("body .repeater tbody").sortable({
                handle: '.sort-handler'
            });
            var $repeater = $(selector + ' .repeater').repeater({
                initEmpty: true,
                defaultValues: {
                    'status': 1
                },
                show: function() {
                    $(this).slideDown();
                    var file_uploads = $(this).find('input.multi');
                    if (file_uploads.length) {
                        $(this).find('input.multi').MultiFile({
                            max: 3,
                            accept: 'png|jpg|jpeg',
                            max_size: 2048
                        });
                    }
                },
                hide: function(deleteElement) {
                    $(this).slideUp(deleteElement);
                    $(this).remove();
                    var inputs = $(".amount");
                    var subTotal = 0;
                    for (var i = 0; i < inputs.length; i++) {
                        subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                    }
                    $('.subTotal').html(subTotal.toFixed(2));
                    $('.totalAmount').html(subTotal.toFixed(2));
                },
                ready: function(setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },
                isFirstItemUndeletable: false
            });
            var value = $(selector + " .repeater").attr('data-value');

            if (typeof value != 'undefined' && value.length != 0) {
                value = JSON.parse(value);
                // Map product_id to item so repeater sets the select correctly
                for (var k = 0; k < value.length; k++) {
                    value[k].item = value[k].product_id;
                }
                $repeater.setList(value);
                // Allow repeater rows to render before populating selects
                setTimeout(function() {
                    for (var i = 0; i < value.length; i++) {
                        var tr = $('#sortable-table .id[value="' + value[i].id + '"]').parent();
                        if (!tr.length) {
                            tr = $('#sortable-table [data-repeater-item]').eq(i).find('tr').first();
                        }
                        tr.find('.item').val(value[i].product_id);
                        changeItem(tr.find('.item'));
                    }
                    setTimeout(function() {
                        $('#sortable-table [data-repeater-item]').each(function() {
                            setInvoiceRowLocked($(this), true);
                        });
                        recalculateInvoiceTotals();
                    }, 800);
                }, 300);
            }
        }

        $(document).on('change', '#customer', function() {
            $('#customer_detail').removeClass('d-none');
            $('#customer_detail').addClass('d-block');
            $('#customer-box').removeClass('d-block');
            $('#customer-box').addClass('d-none');
            var id = $(this).val();
            var url = $(this).data('url');
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'id': id
                },
                cache: false,
                success: function(data) {
                    if (data != '') {
                        $('#customer_detail').html(data);
                    } else {
                        $('#customer-box').removeClass('d-none');
                        $('#customer-box').addClass('d-block');
                        $('#customer_detail').removeClass('d-block');
                        $('#customer_detail').addClass('d-none');
                    }
                },
            });
        });

        $(document).on('click', '#remove', function() {
            $('#customer-box').removeClass('d-none');
            $('#customer-box').addClass('d-block');
            $('#customer_detail').removeClass('d-block');
            $('#customer_detail').addClass('d-none');
        })

        $(document).on('change', '.item', function() {
            changeItem($(this));
        });

        var invoice_id = '{{ $invoice->id }}';

        function changeItem(element) {
            var iteams_id = element.val();
            var url = element.data('url');
            var el = element;
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'product_id': iteams_id
                },
                cache: false,
                success: function(data) {
                    var item = JSON.parse(data);

                    $.ajax({
                        url: '{{ route('invoice.items') }}',
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': jQuery('#token').val()
                        },
                        data: {
                            'invoice_id': invoice_id,
                            'product_id': iteams_id,
                        },
                        cache: false,
                        success: function(data) {
                            var invoiceItems = JSON.parse(data);

                            if (invoiceItems != null) {
                                var amount = (invoiceItems.price * invoiceItems.quantity);
                                $(el.parent().parent().find('.quantity')).val(invoiceItems.quantity);
                                $(el.parent().parent().find('.price')).val(invoiceItems.price);
                            } else {
                                $(el.parent().parent().find('.quantity')).val(1);
                                $(el.parent().parent().find('.price')).val(item.product.sale_price);
                            }

                            $(el.parent().parent().find('.unit')).html(item.unit);

                            // Store stock quantities as data attributes
                            var quantityInput = el.parent().parent().find('.quantity');
                            var typeSelect = el.parent().parent().find('.type');
                            quantityInput.attr('data-stock-new', item.stock_new || 0);
                            quantityInput.attr('data-stock-used', item.stock_used || 0);
                            quantityInput.attr('data-stock-damaged', item.stock_damaged || 0);
                            typeSelect.attr('data-stock-new', item.stock_new || 0);
                            typeSelect.attr('data-stock-used', item.stock_used || 0);
                            typeSelect.attr('data-stock-damaged', item.stock_damaged || 0);
                            if (invoiceItems != null) {
                                quantityInput.attr('data-original-product', iteams_id);
                                quantityInput.attr('data-original-type', invoiceItems.type || typeSelect.val());
                                quantityInput.attr('data-original-quantity', invoiceItems.quantity || 0);
                            } else {
                                validateStock(quantityInput);
                            }

                            if (invoiceItems != null) {
                                $(el.parent().parent().find('.amount')).html(parseFloat(amount).toFixed(2));
                            } else {
                                $(el.parent().parent().find('.amount')).html(parseFloat(item.totalAmount).toFixed(2));
                            }

                            recalculateInvoiceTotals();
                        }
                    });
                },
            });
        }

        $(document).on('keyup', '.quantity', function() {
            var el = $(this).parent().parent().parent().parent();
            var quantity = $(this).val();
            var price = $(el.find('.price')).val();
            var amount = (quantity * price);

            $(el.find('.amount')).html(parseFloat(amount).toFixed(2));
            recalculateInvoiceTotals();

            // Stock validation on quantity change
            validateStock($(this));
        })

        $(document).on('keyup change', '.price', function() {
            var el = $(this).parent().parent().parent().parent();
            var price = $(this).val();
            var quantity = $(el.find('.quantity')).val();
            var amount = (quantity * price);

            $(el.find('.amount')).html(parseFloat(amount).toFixed(2));
            recalculateInvoiceTotals();
        })

        // Stock validation function - resets quantity to available stock if exceeded
        function validateStock(element) {
            var row = element.closest('[data-repeater-item]');
            var quantityInput = row.find('.quantity');
            var quantity = parseFloat(quantityInput.val()) || 0;
            var type = row.find('.type').val();
            var productId = row.find('.item').val();
            var stockAvailable = 0;

            if (!productId) {
                return;
            }

            if (type === 'new') {
                stockAvailable = parseFloat(quantityInput.attr('data-stock-new')) || 0;
            } else if (type === 'use') {
                stockAvailable = parseFloat(quantityInput.attr('data-stock-used')) || 0;
            } else if (type === 'damage') {
                stockAvailable = parseFloat(quantityInput.attr('data-stock-damaged')) || 0;
            }

            if (
                productId == quantityInput.attr('data-original-product') &&
                type == quantityInput.attr('data-original-type')
            ) {
                stockAvailable += parseFloat(quantityInput.attr('data-original-quantity')) || 0;
            }

            var usedInOtherRows = 0;
            $('#sortable-table [data-repeater-item]').not(row).each(function() {
                if ($(this).find('.item').val() == productId && $(this).find('.type').val() == type) {
                    usedInOtherRows += parseFloat($(this).find('.quantity').val()) || 0;
                }
            });

            var remainingStock = Math.max(stockAvailable - usedInOtherRows, 0);
            var otherRowsMessage = usedInOtherRows > 0 ? ' Other invoice rows already use ' + usedInOtherRows + '.' : '';

            if (quantity > remainingStock && remainingStock > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock Warning',
                    text: 'Requested quantity (' + quantity + ') exceeds remaining stock (' + remainingStock + ') for selected type: ' + type + '.' + otherRowsMessage,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                quantityInput.val(remainingStock);
                quantityInput.trigger('keyup');
            } else if (quantity > 0 && remainingStock === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Out of Stock',
                    text: 'No remaining stock available for selected type: ' + type + '.' + otherRowsMessage,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                quantityInput.val(0);
                quantityInput.trigger('keyup');
            }
        }

        // Type change handler for stock validation
        $(document).on('change', '.type', function() {
            validateStock($(this));
        });

        function setSelectLocked($select, locked) {
            var mirrorClass = 'locked-select-hidden';

            if (locked) {
                $select.siblings('input.' + mirrorClass + '[data-for="' + $select.attr('name') + '"]').remove();
                $('<input>', {
                    type: 'hidden',
                    class: mirrorClass,
                    'data-for': $select.attr('name'),
                    name: $select.attr('name'),
                    value: $select.val()
                }).insertAfter($select);
                $select.prop('disabled', true);
            } else {
                $select.prop('disabled', false);
                $select.siblings('input.' + mirrorClass + '[data-for="' + $select.attr('name') + '"]').remove();
            }
        }

        // ─── Confirm row - switch to locked mode ─────────────────────────────────────
        function recalculateInvoiceTotals() {
            var subTotal = 0;

            $('#sortable-table [data-repeater-item]').each(function() {
                var quantity = parseFloat($(this).find('.quantity').val()) || 0;
                var price = parseFloat($(this).find('.price').val()) || 0;

                subTotal += quantity * price;
            });

            $('.subTotal').html(subTotal.toFixed(2));
            $('.totalAmount').html(subTotal.toFixed(2));
        }

        function setInvoiceRowLocked($row, locked) {
            if (!$row.length) {
                return;
            }

            $row.toggleClass('confirmed-row', locked);
            $row.find('tr').first().toggleClass('confirmed-row', locked);
            $row.find('.edit-actions').toggle(!locked);
            $row.find('.locked-actions').toggle(locked);
            $row.find('.quantity, .price').prop('readonly', locked);
            $row.find('.item, .type').each(function() {
                setSelectLocked($(this), locked);
            });
        }

        function isInvoiceRowBlank($row) {
            return !($row.find('.item').val()) &&
                !(parseFloat($row.find('.quantity').val()) || 0) &&
                !(parseFloat($row.find('.price').val()) || 0);
        }

        function deleteInvoiceRow($row) {
            if (!$row.length || !confirm('Are you sure you want to delete this element?')) {
                return;
            }

            var id = $row.find('.id').val();
            var amount = $row.find('.amount').html();

            if (id) {
                $.ajax({
                    url: '{{ route('invoice.product.destroy') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': jQuery('#token').val()
                    },
                    data: {
                        'id': id,
                        'amount': amount,
                    },
                    cache: false
                });
            }

            $row.remove();
            recalculateInvoiceTotals();
        }

        $(document).on('click', '.confirm-row-btn', function(e) {
            e.preventDefault();
            var $row = $(this).closest('[data-repeater-item]');
            if (!$row.length) return;

            var itemVal = $row.find('.item').val();
            if (!itemVal) {
                Swal.fire({ icon: 'warning', title: 'Please select an item', confirmButtonColor: '#3085d6', confirmButtonText: 'OK' });
                return;
            }
            var qty = parseFloat($row.find('.quantity').val()) || 0;
            if (qty <= 0) {
                Swal.fire({ icon: 'warning', title: 'Please enter a valid quantity', confirmButtonColor: '#3085d6', confirmButtonText: 'OK' });
                return;
            }
            var price = parseFloat($row.find('.price').val()) || 0;
            if (price <= 0) {
                Swal.fire({ icon: 'warning', title: 'Please enter a valid price', confirmButtonColor: '#3085d6', confirmButtonText: 'OK' });
                return;
            }

            setInvoiceRowLocked($row, true);
            recalculateInvoiceTotals();
            show_toastr('success', 'Item confirmed successfully');
        });

        // ─── Discard row - remove it ─────────────────────────────────────────────────
        $(document).on('click', '.discard-row-btn', function(e) {
            e.preventDefault();
            var $row = $(this).closest('[data-repeater-item]');
            if (!$row.length) return;
            deleteInvoiceRow($row);
        });

        // ─── Edit row - switch back to edit mode ─────────────────────────────────────
        $(document).on('click', '.edit-row-btn', function(e) {
            e.preventDefault();
            var $row = $(this).closest('[data-repeater-item]');
            if (!$row.length) return;
            setInvoiceRowLocked($row, false);
            $row.find('.item').first().focus();
        });

        // ─── Keyboard shortcuts (inspired by Study Pack) ──────────────────────────────
        $(document).on('keydown', '.item, .quantity, .price, .type', function(e) {
            var $row = $(this).closest('[data-repeater-item]');
            if (!$row.length) return;

            if (e.key === 'Enter' && e.shiftKey) {
                e.preventDefault();
                $row.find('.confirm-row-btn').trigger('click');
                setTimeout(function() {
                    $('[data-repeater-create]').first().trigger('click');
                }, 100);
                return false;
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if ($(this).hasClass('item')) {
                    $row.find('.quantity').focus();
                } else if ($(this).hasClass('quantity')) {
                    $row.find('.price').focus();
                } else if ($(this).hasClass('price')) {
                    $row.find('.type').focus();
                } else if ($(this).hasClass('type')) {
                    $row.find('.confirm-row-btn').trigger('click');
                }
                return false;
            } else if (e.key === 'Escape') {
                e.preventDefault();
                $row.find('.discard-row-btn').trigger('click');
                return false;
            }
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'Enter' && e.shiftKey) {
                var target = $(e.target);
                if (target.is('textarea') || target.hasClass('item') || target.hasClass('quantity') || target.hasClass('price') || target.hasClass('type')) {
                    return;
                }
                e.preventDefault();
                $('[data-repeater-create]').first().trigger('click');
            }
        });

        $(document).on('submit', 'form', function(e) {
            if ($(this).find('#sortable-table').length === 0) {
                return;
            }

            $('#sortable-table [data-repeater-item]').each(function() {
                if (!$(this).hasClass('confirmed-row') && isInvoiceRowBlank($(this))) {
                    $(this).remove();
                }
            });

            if ($('#sortable-table [data-repeater-item].confirmed-row').length === 0) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Please confirm at least one item', confirmButtonColor: '#3085d6', confirmButtonText: 'OK' });
                return false;
            }

            if ($('#sortable-table [data-repeater-item]').not('.confirmed-row').length > 0) {
                e.preventDefault();
                Swal.fire({ icon: 'warning', title: 'Please confirm or discard open item rows', confirmButtonColor: '#3085d6', confirmButtonText: 'OK' });
                return false;
            }
        });

        $(document).on('click', '[data-repeater-create]', function() {
            $('.item :selected').each(function() {
                var id = $(this).val();
                $(".item option[value=" + id + "]").prop("disabled", true);
            });
            setTimeout(function() {
                var $row = $('#sortable-table [data-repeater-item]').last();
                setInvoiceRowLocked($row, false);
                $row.find('.locked-select-hidden').remove();
                $row.find('.type').val('new');
                $row.find('.item').first().focus();
            }, 100);
        })

        $(document).on('click', '.delete-row-btn', function(e) {
            e.preventDefault();
            deleteInvoiceRow($(this).closest('[data-repeater-item]'));
        });

        $(document).ready(function() {
            if (typeof ajaxModalForm !== 'undefined') {
                ajaxModalForm('.invoice-ajax-form', {
                    closeOnSuccess: true,
                    showToast: true,
                    onSuccess: function(response, $form) {
                        if (typeof response === 'object' && response.success) {
                            closeActiveBootstrapModal();
                            if (response.message && typeof show_toastr === 'function') {
                                show_toastr('success', response.message, 'success');
                            }
                            setTimeout(function() { window.location.reload(); }, 500);
                        } else if (typeof response === 'string') {
                            closeActiveBootstrapModal();
                            window.location.reload();
                        }
                    }
                });
            }
        });
    </script>

    <style>
        .invoice-select-locked {
            pointer-events: none;
            background-color: #f8f9fa !important;
        }
        .confirm-row-btn i {
            color: #198754 !important;
            font-weight: 700;
        }
        .confirm-row-btn:hover i {
            color: #ffffff !important;
        }
    </style>

    <div class="row">
        {{ Form::model($invoice, ['route' => ['invoice.update', $invoice->id], 'method' => 'PUT', 'class' => 'w-100 invoice-ajax-form', 'novalidate' => true]) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div class="row">
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="form-group">
                                        {{ Form::label('store_from', __('Store From'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                        <select name="store_from" class="form-control custom-select"
                                            value="{{ isset($_GET['store_from']) ? $_GET['store_from'] : '' }}" required>
                                            <option value="{{ $store_from->id }} "> {{ $store_from->name }} </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="form-group">
                                        {{ Form::label('store_to', __('Store To'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                        {{ Form::select('store_to', $store_to, $invoice->store_to ?? '', ['class' => 'form-control select custom-select']) }}
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('invoice_number', __('Invoice Number'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                        <input type="text" class="form-control" value="{{ $invoice_number }}" readonly>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                        {{ Form::date('issue_date', old('issue_date', $invoice->issue_date ?: date('Y-m-d')), ['class' => 'form-control', 'required' => 'required']) }}
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                        {{ Form::date('due_date', old('due_date', $invoice->due_date ?: date('Y-m-d')), ['class' => 'form-control', 'required' => 'required']) }}
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('ref_number', __('Ref Number'), ['class' => 'form-label']) }}
                                        <div class="form-icon-user">
                                            <span><i class="ti ti-joint"></i></span>
                                            {{ Form::text('ref_number', '', ['class' => 'form-control']) }}
                                        </div>
                                    </div>
                                </div>
                                @if (!$customFields->isEmpty())
                                    <div class="col-md-6">
                                        <div class="tab-pane fade show" id="tab-2" role="tabpanel">
                                            @include('customFields.formBuilder')
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <h5 class=" d-inline-block mb-4">{{ __('Products') }}</h5>
            <div class="card repeater" data-value='{!! json_encode($invoice->items->map(function($item) { $item->item = $item->product_id; return $item; })) !!}'>
                <div class="item-section py-2">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                            <div class="all-button-box me-2">
                                <a href="#" data-repeater-create="" class="btn btn-primary" data-bs-toggle="modal" data-target="#add-bank">
                                    Create {{ __('Add item') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Items') }}</th>
                                    <th width="10%">{{ __('Quantity') }}</th>
                                    <th width="10%">{{ __('Price') }} </th>
                                    <th width="10%">{{ __('Type') }}</th>
                                    <th class="text-end">{{ __('Amount') }} </th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="ui-sortable" data-repeater-item>
                                <tr>
                                    {{ Form::hidden('id', null, ['class' => 'form-control id']) }}
                                    <td class="form-group pt-0">
                                        {{ Form::select('item', $product_services, null, ['class' => 'form-control item select custom-select', 'data-url' => route('invoice.product')]) }}
                                    </td>
                                    <td width="10%">
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('quantity', null, ['class' => 'form-control quantity', 'required' => 'required', 'placeholder' => __('Qty')]) }}
                                            <span class="unit input-group-text bg-transparent"></span>
                                        </div>
                                    </td>
                                    <td width="10%">
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('price', null, ['class' => 'form-control price', 'required' => 'required', 'placeholder' => __('Price')]) }}
                                            <span class="input-group-text bg-transparent">{{ \Auth::user()->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    <td width="10%">
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::select(
                                                'type',
                                                ['new' => 'New', 'use' => 'Used', 'damage' => 'Damage'],
                                                isset($_GET['type']) ? $_GET['type'] : 'new',
                                                ['class' => 'form-control select type'],
                                            ) }}
                                        </div>
                                    </td>

                                    <td class="text-end amount">0.00</td>
                                    <td style="white-space:nowrap;">
                                        <span class="edit-actions">
                                            <a class="btn btn-sm btn-outline-success confirm-row-btn me-1 pt-2"
                                                title="{{ __('Confirm') }}">
                                                <span class="btn-inner--icon"><i class="ti ti-check"></i></span>
                                            </a>
                                            <a class="btn btn-sm btn-outline-danger discard-row-btn pt-2"
                                                title="{{ __('Discard') }}">
                                                <span class="btn-inner--icon"><i class="ti ti-x"></i></span>
                                            </a>
                                        </span>
                                        <span class="locked-actions" style="display:none;">
                                            <a class="btn btn-sm btn-outline-primary edit-row-btn me-1 pt-2"
                                                title="{{ __('Edit') }}">
                                                <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                            </a>
                                            <a class="btn btn-sm btn-outline-danger delete-row-btn pt-2"
                                                title="{{ __('Delete') }}">
                                                <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                            </a>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td></td>
                                    <td><strong>{{ __('Sub Total') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end subTotal">0.00</td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td class="blue-text"><strong>{{ __('Total Amount') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalAmount blue-text">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" onclick="location.href = '{{ route('invoice.index') }}';" class="btn btn-light me-3">
            <input type="submit" value="{{ __('Update') }}" class="btn btn-primary">
        </div>
        {{ Form::close() }}
    </div>
@endsection
