@extends('layouts.admin')
@section('page-title')
    {{ __('Invoice Create') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('invoice.index') }}">{{ __('Invoice') }}</a></li>
    <li class="breadcrumb-item">{{ __('Invoice Create') }}</li>
@endsection
<style>
    .select2-selection__arrow {
        display: none !important;
    }
    .select2-selection__rendered {
    border: 1px solid #100773 !important;
    border-radius: 10px !important;
    padding-bottom: 0px !important;
}
.m-header.main-logo img{
    display: flex !important;
    position: relative !important;
    align-items: center !important;
    left: 50% !important;
    top: -120px !important;
    transform: translateX(-65px) !important;
    }
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
@push('script-page')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        var selector = "body";

        function repeaterShowLogic(row) {
            $(row).slideDown();

            // MultiFile input init
            var file_uploads = $(row).find('input.multi');
            if (file_uploads.length) {
                file_uploads.MultiFile({
                    max: 3,
                    accept: 'png|jpg|jpeg',
                    max_size: 2048
                });
            }
            // $(row).find('.selectBox').select2();
        }

        if ($(selector + " .repeater").length) {
            var $dragAndDrop = $("body .repeater tbody").sortable({
                handle: '.sort-handler'
            });

            var $repeater = $(selector + ' .repeater').repeater({
                initEmpty: false,
                defaultValues: {
                    'status': 1
                },
                show: function() {
                    repeaterShowLogic(this); // When dynamically added
                },
                hide: function(deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {
                        $(this).slideUp(deleteElement);
                        $(this).remove();

                        var subTotal = 0;
                        $(".amount").each(function() {
                            subTotal += parseFloat($(this).html()) || 0;
                        });
                        $('.subTotal, .totalAmount').html(subTotal.toFixed(2));
                    }
                },
                ready: function(setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },
                isFirstItemUndeletable: false
            });

            var value = $(selector + " .repeater").attr('data-value');
            if (typeof value !== 'undefined' && value.length !== 0) {
                value = JSON.parse(value);
                $repeater.setList(value);
            }
        }

        $(selector + " .repeater tbody tr").each(function() {
            repeaterShowLogic($(this)); // Fire your logic manually
            $('.selectBox').select2({
                theme: 'bootstrap4'
            });

            function addRowAndInit() {
                const newRow = $('#sortable-table tbody:last');
                newRow.find('.selectBox').select2({
                    theme: 'bootstrap4'
                });
            }
            addRowAndInit();
        });
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

            var iteams_id = $(this).val();
            var url = $(this).data('url');
            var el = $(this);

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
                    console.log(el.parent().parent().find('.quantity'))
                    $(el.parent().parent().find('.quantity')).val(1);
                    $(el.parent().parent().find('.price')).val(item.product.sale_price);
                    $(el.parent().parent().parent().find('.pro_description')).val(item.product
                        .description);
                    // $('.pro_description').text(item.product.description);

                    var taxes = '';
                    var tax = [];

                    var totalItemTaxRate = 0;

                    if (item.taxes == 0) {
                        taxes += '-';
                    } else {
                        for (var i = 0; i < item.taxes.length; i++) {
                            taxes += '<span class="badge bg-primary mt-1 mr-2">' + item.taxes[i].name +
                                ' ' + '(' + item.taxes[i].rate + '%)' + '</span>';
                            tax.push(item.taxes[i].id);
                            totalItemTaxRate += parseFloat(item.taxes[i].rate);
                        }
                    }
                    var itemTaxPrice = parseFloat((totalItemTaxRate / 100)) * parseFloat((item.product
                        .sale_price * 1));
                    $(el.parent().parent().find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));
                    $(el.parent().parent().find('.itemTaxRate')).val(totalItemTaxRate.toFixed(2));
                    $(el.parent().parent().find('.taxes')).html(taxes);
                    $(el.parent().parent().find('.tax')).val(tax);
                    $(el.parent().parent().find('.unit')).html(item.unit);
                    $(el.parent().parent().find('.discount')).val(0);

                    // Store stock quantities as data attributes
                    var quantityInput = el.parent().parent().find('.quantity');
                    var typeSelect = el.parent().parent().find('.type');
                    quantityInput.attr('data-stock-new', item.stock_new || 0);
                    quantityInput.attr('data-stock-used', item.stock_used || 0);
                    quantityInput.attr('data-stock-damaged', item.stock_damaged || 0);
                    typeSelect.attr('data-stock-new', item.stock_new || 0);
                    typeSelect.attr('data-stock-used', item.stock_used || 0);
                    typeSelect.attr('data-stock-damaged', item.stock_damaged || 0);
                    validateStock(quantityInput);

                    var inputs = $(".amount");
                    var subTotal = 0;
                    for (var i = 0; i < inputs.length; i++) {
                        subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                    }

                    var totalItemPrice = 0;
                    var priceInput = $('.price');
                    for (var j = 0; j < priceInput.length; j++) {
                        totalItemPrice += parseFloat(priceInput[j].value);
                    }

                    var totalItemTaxPrice = 0;
                    var itemTaxPriceInput = $('.itemTaxPrice');
                    for (var j = 0; j < itemTaxPriceInput.length; j++) {
                        totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
                        $(el.parent().parent().find('.amount')).html(parseFloat(item.totalAmount) +
                            parseFloat(itemTaxPriceInput[j].value));
                    }

                    var totalItemDiscountPrice = 0;
                    var itemDiscountPriceInput = $('.discount');

                    for (var k = 0; k < itemDiscountPriceInput.length; k++) {

                        totalItemDiscountPrice += parseFloat(itemDiscountPriceInput[k].value);
                    }

                    $('.subTotal').html(totalItemPrice.toFixed(2));
                    $('.totalTax').html(totalItemTaxPrice.toFixed(2));
                    $('.totalAmount').html((parseFloat(totalItemPrice) - parseFloat(
                        totalItemDiscountPrice) + parseFloat(totalItemTaxPrice)).toFixed(2));


                },
            });
        });

        $(document).on('keyup', '.quantity', function() {
            var quntityTotalTaxPrice = 0;

            var el = $(this).parent().parent().parent().parent();

            var quantity = $(this).val();
            var price = $(el.find('.price')).val();
            var discount = $(el.find('.discount')).val();
            if (discount.length <= 0) {
                discount = 0;
            }

            var totalItemPrice = (quantity * price) - discount;

            var amount = (totalItemPrice);


            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

            $(el.find('.amount')).html(parseFloat(itemTaxPrice) + parseFloat(amount));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }


            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            $('.subTotal').html(totalItemPrice.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));

            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));

            // Stock validation on quantity change
            validateStock($(this));

        })

        $(document).on('keyup change', '.price', function() {
            var el = $(this).parent().parent().parent().parent();
            var price = $(this).val();
            var quantity = $(el.find('.quantity')).val();

            var discount = $(el.find('.discount')).val();
            if (discount.length <= 0) {
                discount = 0;
            }
            var totalItemPrice = (quantity * price) - discount;

            var amount = (totalItemPrice);


            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

            $(el.find('.amount')).html(parseFloat(itemTaxPrice) + parseFloat(amount));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }


            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            $('.subTotal').html(totalItemPrice.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));

            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));


        })

        $(document).on('keyup change', '.discount', function() {
            var el = $(this).parent().parent().parent();
            var discount = $(this).val();
            if (discount.length <= 0) {
                discount = 0;
            }

            var price = $(el.find('.price')).val();
            var quantity = $(el.find('.quantity')).val();
            var totalItemPrice = (quantity * price) - discount;


            var amount = (totalItemPrice);


            var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
            var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
            $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

            $(el.find('.amount')).html(parseFloat(itemTaxPrice) + parseFloat(amount));

            var totalItemTaxPrice = 0;
            var itemTaxPriceInput = $('.itemTaxPrice');
            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
            }


            var totalItemPrice = 0;
            var inputs_quantity = $(".quantity");

            var priceInput = $('.price');
            for (var j = 0; j < priceInput.length; j++) {
                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }


            var totalItemDiscountPrice = 0;
            var itemDiscountPriceInput = $('.discount');

            for (var k = 0; k < itemDiscountPriceInput.length; k++) {

                totalItemDiscountPrice += parseFloat(itemDiscountPriceInput[k].value);
            }


            $('.subTotal').html(totalItemPrice.toFixed(2));
            $('.totalTax').html(totalItemTaxPrice.toFixed(2));

            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));
            $('.totalDiscount').html(totalItemDiscountPrice.toFixed(2));




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
                // Reset quantity to available stock
                quantityInput.val(remainingStock);
                // Trigger keyup to recalculate totals
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
            var totalTax = 0;
            var totalDiscount = 0;
            var totalAmount = 0;

            $('#sortable-table [data-repeater-item]').each(function() {
                var quantity = parseFloat($(this).find('.quantity').val()) || 0;
                var price = parseFloat($(this).find('.price').val()) || 0;
                var discount = parseFloat($(this).find('.discount').val()) || 0;
                var itemTaxPrice = parseFloat($(this).find('.itemTaxPrice').val()) || 0;
                var amount = parseFloat($(this).find('.amount').html()) || 0;

                subTotal += quantity * price;
                totalDiscount += discount;
                totalTax += itemTaxPrice;
                totalAmount += amount;
            });

            $('.subTotal').html(subTotal.toFixed(2));
            $('.totalDiscount').html(totalDiscount.toFixed(2));
            $('.totalTax').html(totalTax.toFixed(2));
            $('.totalAmount').html(totalAmount.toFixed(2));
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

        function deleteInvoiceRow($row) {
            if (!$row.length) {
                return;
            }

            if (confirm('Are you sure you want to delete this element?')) {
                $row.remove();
                recalculateInvoiceTotals();
            }
        }

        function isInvoiceRowBlank($row) {
            return !($row.find('.item').val()) &&
                !(parseFloat($row.find('.quantity').val()) || 0) &&
                !(parseFloat($row.find('.price').val()) || 0);
        }

        $(document).on('click', '.confirm-row-btn', function(e) {
            e.preventDefault();
            var $row = $(this).closest('[data-repeater-item]');
            if (!$row.length) return;

            // Validate required fields
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

            // Lock the row
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
        // Shift+Enter on any field in a row → Add new item line
        // Enter on item select → Move to quantity
        // Enter on quantity → Move to price
        // Enter on price → Move to type
        // Enter on type → Confirm the row
        // Escape on any field → Discard the current row
        $(document).on('keydown', '.item, .quantity, .price, .type', function(e) {
            var $row = $(this).closest('[data-repeater-item]');
            if (!$row.length) return;

            if (e.key === 'Enter' && e.shiftKey) {
                // Shift+Enter: Add new item line
                e.preventDefault();
                $row.find('.confirm-row-btn').trigger('click');
                setTimeout(function() {
                    $('[data-repeater-create]').first().trigger('click');
                }, 100);
                return false;
            } else if (e.key === 'Enter') {
                e.preventDefault();
                // Navigate to next field in the row
                if ($(this).hasClass('item')) {
                    $row.find('.quantity').focus();
                } else if ($(this).hasClass('quantity')) {
                    $row.find('.price').focus();
                } else if ($(this).hasClass('price')) {
                    $row.find('.type').focus();
                } else if ($(this).hasClass('type')) {
                    // Enter on type → Confirm the row
                    $row.find('.confirm-row-btn').trigger('click');
                }
                return false;
            } else if (e.key === 'Escape') {
                // Escape: Discard the current row
                e.preventDefault();
                $row.find('.discard-row-btn').trigger('click');
                return false;
            }
        });

        // Global Shift+Enter to add new row from anywhere on the page
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
            setTimeout(function() {
                var $row = $('#sortable-table [data-repeater-item]').last();
                setInvoiceRowLocked($row, false);
                $row.find('.locked-select-hidden').remove();
                $row.find('.type').val('new');
                $row.find('.item').first().focus();
            }, 100);
        });
    </script>
    <script>
        $(document).on('click', '.del', function() {
            if (!confirm('Are you sure you want to delete this element?')) {
                return;
            }
            var el = $(this).closest('[data-repeater-item]');
            var id = $(el.find('.id')).val();
            el.remove();
            recalculateInvoiceTotals();
        });

        $('#company').on('change', function() {
            var comp_id = $('#company').val();
            $.ajax({
                url: "{{ route('company_contract') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'id': comp_id
                },
                cache: false,
                success: function(data) {
                    console.log(data);
                    if (data != '') {
                        // $('#sortable-table tbody:gt(0)').remove();
                        $('tbody').remove();
                        $('.btn-primary[data-repeater-create]').click();
                        // $('#company_detail').removeClass('d-block');
                        $('#contract').empty().append(
                            `<option selected disabled value="">Select a Contract</option>`);
                        for (var i = 0; i < data.data.length; i++) {
                            $('#contract').append(`<option value="` + data.data[i]['id'] + `">` + data
                                .data[i]['subject'] + `</option>`);
                        }
                    }

                },

            });

        });

        $('#store').on('change', function() {
            var comp_id = $('#store').val();
            $.ajax({
                url: "{{ route('company_contract_detail') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'id': comp_id
                },
                cache: false,
                success: function(data) {
                    $('#sortable-table tbody:gt(0)').remove();
                    $('.btn-primary[data-repeater-create]').click();

                    if (data.html !== undefined) {
                        $('#sortable-table tbody:gt(0)').remove();
                        $('.ui-sortable').empty().html(data.html);
                        $('.btn-primary[data-repeater-create]').click();

                    }
                    $('.quantity').trigger('keyup');
                    $('.item').trigger('change');
                },

            });

        });


        function branchcustomer(id) {
            var branch = id;
            $.ajax({
                url: '{{ route('branch.class') }}',
                type: 'POST',
                data: {
                    "branch_id": branch,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('#class').empty();
                    $('#class').append('<option value="">{{ __('Select Class') }}</option>');

                    for (let index = 0; index < data.length; index++) {
                        $('#class').append('<option value="' + data[index]['id'] + '">' + data[index]['name'] +
                            '</option>');
                    }
                }
            });
        }

        document.getElementById('branchcustomer').addEventListener('change', function() {
            var id = this.value;
            branchcustomer(id);
        });

        $(document).on('change', '#store', function() {
            var warehouse_id = $(this).val();
            getProduct(warehouse_id);
        });

        function getProduct(wid) {
            $.ajax({
                url: '{{ route('warehouse-transfer.getproduct') }}',
                type: 'POST',
                data: {
                    "warehouse_id": wid,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#product_id').empty();

                    $("#product_div").html('');
                    $('#product_div').append(
                        '<label for="product" class="form-label">{{ __('Product') }}</label>');
                    $('#product_div').append(
                        '<select class="form-control" id="product_id" name="product_id"></select>');
                    $('#product_id').append('<option value="">{{ __('Select Product') }}</option>');

                    $.each(data.ware_products, function(key, value) {
                        $('#product_id').append('<option value="' + key + '">' + value + '</option>');
                    });

                    $('select[name=to_warehouse]').empty();
                    $.each(data.to_warehouses, function(key, value) {
                        var option = '<option value="' + key + '">' + value + '</option>';
                        $('select[name=to_warehouse]').append(option);
                    });
                }

            });
        }
        // $(document).ready(function() {
        //     setTimeout(function() {
        //         $('.selectBox').select2();
        //     }, 1000);
        // });
    </script>
@endpush
@section('content')
    <div class="row">

        {{ Form::open(['url' => 'invoice', 'class' => 'w-100']) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div class="row">
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="form-group">
                                        {{ Form::label('store_from', __('Store From'), ['class' => 'form-label']) }}<span
                                            style="color: red"> *</span>
                                        {{-- {{ Form::select('store_from', $store_from, isset($_GET['store_from']) ? $_GET['store_from'] : '', ['class' => 'form-control select']) }} --}}
                                        <select name="store_from" class="form-control custom-select"
                                            value="{{ isset($_GET['store_from']) ? $_GET['store_from'] : '' }}" required>
                                            <option value="{{ $store_from->id }} "> {{ $store_from->name }} </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="form-group">
                                        {{ Form::label('store_to', __('Store To'), ['class' => 'form-label']) }}<span
                                            style="color: red"> *</span>
                                        {{ Form::select('store_to', $store_to, isset($_GET['store_to']) ? $_GET['store_to'] : '', ['class' => 'form-control select custom-select']) }}
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('invoice_number', __('Invoice Number'), ['class' => 'form-label']) }}<span
                                            style="color: red"> *</span>
                                        <input type="text" class="form-control" value="{{ $invoice_number }}" readonly>
                                    </div>
                                </div>

                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}<span
                                            style="color: red"> *</span>
                                        {{ Form::date('issue_date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
                                    </div>
                                </div>
                                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}<span
                                            style="color: red"> *</span>
                                        {{ Form::date('due_date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
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
            <h5 class=" d-inline-block mb-4">{{ __('Product / Items') }}</h5>
            <div class="card repeater">
                <div class="item-section py-2">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                            <div class="all-button-box me-2">
                                <a href="#" data-repeater-create="" class="btn btn-outline-primary"
                                    data-bs-toggle="modal" data-target="#add-bank">
                                    <span class="btn-inner--icon"> Create</span> {{ __('Add item') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style mt-2" style="max-height: 600px; overflow: auto;">
                    <div class="table-responsive">
                        <table class="table  mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                                <tr>
                                    <th width="25%">{{ __('Items') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Price') }} </th>
                                    <th width="10%">{{ __('type') }}</th>
                                    <th>{{ __('Tax') }} (%)</th>
                                    <th class="text-end">{{ __('Amount') }} <br><small
                                            class="text-danger font-weight-bold">{{ __('after tax & discount') }}</small>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody class="ui-sortable" data-repeater-item>
                                <tr>

                                    {{-- <td width="25%" class="form-group pt-0">
                                    {{ Form::select('item', $product_services,'', array('class' => 'form-control select2 item','data-url'=>route('invoice.product'),'required'=>'required')) }}
                                </td> --}}
                                    <td width="25%" class="form-group">
                                        {{-- <select name="item" class="form-control item" data-url="{{route('invoice.product')}}"  required>
                                        <option value=""> Select Item </option>
                                        @foreach ($product_services as $item)
                                            <option value="{{ $item['id'] }} " {{ $item['quantity'] == 0 ? 'disabled' : '' }} data-quantity="{{ $item['quantity'] }}">
                                                {{ $item['name'] }}
                                            </option>
                                        @endforeach
                                    </select> --}}
                                        {{ Form::select('item', $product_services, null, ['class' => 'form-control custom-select item', 'style' => 'font-size: smaller; padding:5px 10px', 'data-url' => route('invoice.product')]) }}

                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('quantity', '', ['class' => 'form-control quantity', 'required' => 'required', 'placeholder' => __('Qty'), 'style' => 'font-size: smaller; padding:5px 10px', 'required' => 'required']) }}
                                            <span class="unit input-group-text bg-transparent"
                                                style='font-size: smaller; padding:5px 10px'></span>
                                        </div>
                                    </td>


                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('price', '', ['class' => 'form-control price', 'required' => 'required', 'placeholder' => __('Price'), 'style' => 'font-size: smaller; padding:5px 10px', 'required' => 'required']) }}
                                            <span class="input-group-text bg-transparent"
                                                style='font-size: smaller; padding:5px 10px'>{{ \Auth::user()->currencySymbol() }}</span>
                                        </div>
                                        {{ Form::hidden('discount', '', ['class' => 'form-control discount', 'placeholder' => __('Discount')]) }}
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::select(
                                                'type',
                                                ['new' => 'New', 'use' => 'Used', 'damage' => 'Damage'],
                                                isset($_GET['type']) ? $_GET['type'] : 'new',
                                                ['class' => 'form-control select type'],
                                            ) }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="form-group">
                                            <div class="input-group colorpickerinput">
                                                <div class="taxes"></div>
                                                {{ Form::hidden('tax', '', ['class' => 'form-control tax text-dark']) }}
                                                {{ Form::hidden('itemTaxPrice', '', ['class' => 'form-control itemTaxPrice']) }}
                                                {{ Form::hidden('itemTaxRate', '', ['class' => 'form-control itemTaxRate']) }}
                                            </div>
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
                                            <a class="btn btn-sm btn-outline-danger del pt-2"
                                                title="{{ __('Delete') }}">
                                                <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                            </a>
                                        </span>
                                    </td>
                                </tr>
                                {{-- <tr>
                                <td colspan="2">
                                    <div class="form-group">
                                        {{ Form::textarea('description', null, ['class'=>'form-control pro_description','rows'=>'2','placeholder'=>__('Description')]) }}
                                    </div>
                                </td>
                                <td colspan="5"></td>
                            </tr> --}}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td></td>
                                    <td><strong>{{ __('Sub Total') }} ({{ \Auth::user()->currencySymbol() }})</strong>
                                    </td>
                                    <td class="text-end subTotal">0.00</td>
                                    <td></td>
                                </tr>
                                {{-- <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td><strong>{{__('Discount')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                <td class="text-end totalDiscount">0.00</td>
                                <td></td>
                            </tr> --}}
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td></td>
                                    <td><strong>{{ __('Tax') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalTax">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td class="blue-text"><strong>{{ __('Total Amount') }}
                                            ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalAmount blue-text"></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" onclick="location.href = '{{ route('invoice.index') }}';"
                class="btn btn-light">
            <input type="submit" value="{{ __('Create') }}" class="btn btn-outline-primary">
        </div>
        {{ Form::close() }}

    </div>
@endsection
