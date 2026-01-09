@extends('layouts.admin')
@section('page-title')
    {{ __('Quotation Create') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('returnorder.index') }}">{{ __('Return Order') }}</a></li>
    <li class="breadcrumb-item">{{ __('Return Order Create') }}</li>
@endsection
<style>
    .select2-selection__arrow {
    display: none !important;
}
</style>
@push('script-page')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        var selector = "body";
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
                    $(this).slideDown();
                    var file_uploads = $(this).find('input.multi');
                    if (file_uploads.length) {
                        $(this).find('input.multi').MultiFile({
                            max: 3,
                            accept: 'png|jpg|jpeg',
                            max_size: 2048
                        });
                    }
                    // $('.select2').select2();
                    $('.selectBox').select2();
                },
                hide: function(deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {
                        $(this).slideUp(deleteElement);
                        $(this).remove();

                        var inputs = $(".amount");
                        var subTotal = 0;
                        for (var i = 0; i < inputs.length; i++) {
                            subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                            // subTotal = parseFloat(subTotal) ;
                        }
                        $('.subTotal').html(subTotal.toFixed(2));
                        $('.totalAmount').html(subTotal.toFixed(2));
                    }
                },
                ready: function(setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },
                isFirstItemUndeletable: true
            });
            var value = $(selector + " .repeater").attr('data-value');
            if (typeof value != 'undefined' && value.length != 0) {
                value = JSON.parse(value);
                $repeater.setList(value);
                for (var i = 0; i < value.length; i++) {
                    var tr = $('#sortable-table .id[value="' + value[i].id + '"]').parent();
                    tr.find('.item').val(value[i].product_id);
                    changeItem(tr.find('.item'));
                }
            }
            $('.selectBox').select2();
        }

        // $(document).on('change', '#vender', function () {
        //     $('#vender_detail').removeClass('d-none');
        //     $('#vender_detail').addClass('d-block');
        //     $('#vender-box').removeClass('d-block');
        //     $('#vender-box').addClass('d-none');
        //     var id = $(this).val();
        //     var url = $(this).data('url');
        //     $.ajax({
        //         url: url,
        //         type: 'POST',
        //         headers: {
        //             'X-CSRF-TOKEN': jQuery('#token').val()
        //         },
        //         data: {
        //             'id': id
        //         },
        //         cache: false,
        //         success: function (data) {
        //             if (data != '') {
        //                 $('#vender_detail').html(data);
        //             } else {
        //                 $('#vender-box').removeClass('d-none');
        //                 $('#vender-box').addClass('d-block');
        //                 $('#vender_detail').removeClass('d-block');
        //                 $('#vender_detail').addClass('d-none');
        //             }
        //         },
        //     });
        // });

        // $(document).on('click', '#remove', function () {
        //     $('#vender-box').removeClass('d-none');
        //     $('#vender-box').addClass('d-block');
        //     $('#vender_detail').removeClass('d-block');
        //     $('#vender_detail').addClass('d-none');
        // })
        // $(document).ready(function() {
        // $('.select2.item').change(function() {
        // var el = $(this).parent().parent().parent();
        // // var price = $(el.find('.price')).val();
        // // console.log(price);
        // var url = $(this).data('url');
        // var productId = $(this).val();
        // var store = $('.store_id').val();
        // $.ajax({
        //             url:url,
        //             type: 'Post',
        //             headers: {
        //             'X-CSRF-TOKEN': jQuery('#token').val()
        //         },
        //             data: { product_id: productId,
        //                 store_id: store,
        //             },
        //             success: function(response) {
        //                 console.log(response);
        //                 if (response.success) {
        //                     $(el.find('.price')).val(response.price);
        //                 } else {
        //                     alert('Product not found.');
        //                 }
        //             },
        //             error: function() {
        //                 alert('Error fetching product details.');
        //             }
        //         });
        //     });
        // });

        $(document).on('change', '.item', function() {
            var iteams_id = $(this).val();
            var url = $(this).data('url');
            // var el = $(this).parent().parent().parent();
            var el = $(this);
            var store = $('.store_id').val();
            var quantity = $('.quantity').val();
            var subTotal = 0;
            $.ajax({
                url: url,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': jQuery('#token').val()
                },
                data: {
                    'product_id': iteams_id,
                    store_id: store,
                    quantity_id: quantity,
                },
                cache: false,
                success: function(data) {
                    var item = JSON.parse(data);
                    console.log(item);

                    if (item.productquantity < 1) {
                        show_toastr('Error', "{{ __('This product is out of stock!') }}", 'error');
                        return false;
                    }
                    if (item.product == null) {
                        if(item.totalAmount == 0){
                            $(el.parent().parent().parent().find('.price')).val(0);
                        }
                    }else{
                        // console.log(item)//
                        // $(el.find('.price')).val(item.price.price);
                        $(el.parent().parent().parent().find('.quantity')).val(1);
                        $(el.parent().parent().parent().find('.price')).val(item.product.price);
                        $(el.parent().parent().parent().parent().find('.pro_description')).val(item.pro.description);
                    }
                    // var taxes = '';
                    // var tax = [];
                    // var totalItemTaxRate = 0;
                    // if (item.taxes == 0) {
                    //     taxes += '-';
                    // } else {
                    //     for (var i = 0; i < item.taxes.length; i++) {

                    //         taxes += '<span class="badge bg-primary mt-1 mr-2">' + item.taxes[i].name +
                    //             ' ' + '(' + item.taxes[i].rate + '%)' + '</span>';
                    //         tax.push(item.taxes[i].id);
                    //         totalItemTaxRate += parseFloat(item.taxes[i].rate);

                    //     }
                    // }
                    // var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (item.product.sale_price *
                    //     1));

                    // $(el.parent().parent().parent().find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));
                    // $(el.parent().parent().parent().find('.itemTaxRate')).val(totalItemTaxRate.toFixed(
                    //     2));
                    // $(el.parent().parent().parent().find('.taxes')).html(taxes);
                    // $(el.parent().parent().parent().find('.tax')).val(tax);
                    // $(el.parent().parent().parent().find('.unit')).html(item.unit);
                    // $(el.parent().parent().parent().find('.discount')).val(0);
                    $(el.parent().parent().parent().find('.amount')).html(item.totalAmount);


                    var inputs = $(".amount");

                    for (var i = 0; i < inputs.length; i++) {
                        subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                        // subTotal = parseFloat(subTotal);
                    }
                    $('.subTotal').html(subTotal.toFixed(2));


                    var totalItemPrice = 0;
                    var priceInput = $('.price');
                    for (var j = 0; j < priceInput.length; j++) {
                        totalItemPrice += parseFloat(priceInput[j].value);
                    }

                    // var totalItemTaxPrice = 0;
                    // var itemTaxPriceInput = $('.itemTaxPrice');
                    // for (var j = 0; j < itemTaxPriceInput.length; j++) {
                    //     totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
                    // }

                    // $('.totalTax').html(totalItemTaxPrice.toFixed(2));
                    $('.totalAmount').html((parseFloat(subTotal)));
                    $('.selectBox').select2();

                },
            });
        });

        $(document).on('keyup', '.quantity', function() {
            var el = $(this).closest('tr');

            var quantity = $(this).val() || 0;
            var price = $(el).find('.price').val() || 0;
            var item_id = $(el).find('.item').val();
            var store = $('.store_id').val();
            var subTotal = 0;

            var url = '{{ url('quantity/product') }}';

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    "item_id": item_id,
                    "quantity": quantity,
                    "store_id": store,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    if (data.data.quantity < quantity) {
                        console.log(data);
                        $(el.parent().find('.quantity')).val(data.data.quantity);
                        show_toastr('Error', "{{ __('This product is out of stock!') }}", 'error');
                        // return false;
                        quantity =data.data.quantity;
                    }

                    var totalItemPrice = (quantity * price);
                    var amount = totalItemPrice;

                    // Update the amount in the current row
                    $(el).find('.amount').text(amount.toFixed(2));

                    // Now, sum all the amounts from the table
                    var inputs = $(".amount");
                    for (var i = 0; i < inputs.length; i++) {
                        var value = parseFloat($(inputs[i]).text()) ||
                        0; // Get the text from the td and convert to float
                        subTotal += value;
                    }

                    console.log(subTotal, 'SubTotal');

                    // Update the subtotal display
                    $('.subTotal').html(subTotal.toFixed(2));
                    $('.totalAmount').html((parseFloat(subTotal)));
                }
            });
        });


        $(document).on('keyup change', '.price', function() {
            var el = $(this).parent().parent().parent().parent();
            var price = $(this).val();
            var quantity = $(el.find('.quantity')).val();

            // var discount = $(el.find('.discount')).val();
            // if (discount.length <= 0) {
            //     discount = 0;
            // }
            var totalItemPrice = (quantity * price);

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
                console.log(inputs_quantity[j].value);

                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
            }

            var inputs = $(".amount");

            var subTotal = 0;
            for (var i = 0; i < inputs.length; i++) {
                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
            }

            $('.subTotal').html(totalItemPrice.toFixed(2));
            // $('.totalTax').html(totalItemTaxPrice.toFixed(2));

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
                // subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                subTotal = parseFloat(subTotal);
            }


            var totalItemDiscountPrice = 0;
            var itemDiscountPriceInput = $('.discount');

            for (var k = 0; k < itemDiscountPriceInput.length; k++) {

                totalItemDiscountPrice += parseFloat(itemDiscountPriceInput[k].value);
            }


            $('.subTotal').html(totalItemPrice.toFixed(2));
            // $('.totalTax').html(totalItemTaxPrice.toFixed(2));

            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));
            $('.totalDiscount').html(totalItemDiscountPrice.toFixed(2));




        })

        $(document).ready(function() {
            setTimeout(function() {
                $('.selectBox').select2();
            }, 1000);
        });

        // var vendorId = '{{ $customer }}';
        // if (vendorId > 0) {
        //     $('#vender').val(vendorId).change();
        // }
    </script>

    <script>
        $(document).on('click', '[data-repeater-delete]', function() {
            $(".price").change();
            $(".discount").change();
        });
    </script>
@endpush

@section('content')
    <div class="row">
        {{ Form::open(['url' => 'returnorder', 'class' => 'w-100']) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    {{-- <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" id="vender-box">
                                {{ Form::label('customer_id', __('Customer'), ['class' => 'form-label']) }}
                                {{ Form::text('customer_id', $customer->name, ['class' => 'form-control select', 'required' => 'required', 'readonly' => 'readonly']) }}
                            </div>
                            <div id="vender_detail" class="d-none">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('warehouse_id', __('Warehouse'), ['class' => 'form-label']) }}
                                        {{ Form::text('warehouse_id', $warehouse->name, ['class' => 'form-control warehouse_id', 'required' => 'required', 'readonly' => 'readonly']) }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('quotation_date', __('Return Order Date'), ['class' => 'form-label']) }}
                                        {{ Form::date('quotation_date', $quotation_date, ['class' => 'form-control', 'required' => 'required']) }}

                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {{ Form::label('quotation_number', __('Return Order Number'), ['class' => 'form-label']) }}
                                        {{ Form::text('quotation_number', $quotation_number, ['class' => 'form-control', 'readonly' => 'readonly']) }}

                                    </div>
                                </div>
                            </div>


                        </div>
                    </div> --}}
                    <div class="row">
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-group flex-grow-1 me-2" id="vender-box">
                                {{ Form::label('customer_id', __('Store Form'), ['class' => 'form-label']) }}
                                {{ Form::text('customer_id', $customer->name, ['class' => 'form-control select', 'id' => 'customer_id',  'readonly' => 'readonly']) }}
                                {{ Form::hidden('store_id', $store_id, ['class' => 'form-control store_id']) }}
                            </div>
                            <div id="vender_detail" class="d-none"></div>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-group flex-grow-1 me-2">
                                {{ Form::label('warehouse_id', __('Store To'), ['class' => 'form-label']) }}
                                {{ Form::text('warehouse_id', $warehouse->name, ['class' => 'form-control warehouse_id', 'required' => 'required', 'readonly' => 'readonly']) }}
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-group flex-grow-1 me-2">
                                {{ Form::label('quotation_date', __('Return Order Date'), ['class' => 'form-label']) }}
                                {{ Form::date('quotation_date', $quotation_date, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-group flex-grow-1 me-2">
                                {{ Form::label('quotation_number', __('Return Order Number'), ['class' => 'form-label']) }}
                                {{ Form::text('quotation_number', $quotation_number, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-12">
            <h5 class=" d-inline-block mb-4">{{ __('Product Information ') }}</h5>
            <div class="card repeater" data-value=''>
                <div class="item-section py-2">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                            <div class="all-button-box me-2">
                                <a href="#" data-repeater-create="" class="btn btn-primary" data-bs-toggle="modal"
                                    data-target="#add-bank">
                                    Create {{ __('Add item') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0" data-repeater-list="items" id="   ">
                            <thead>
                                <tr>
                                    <th>{{ __('Items') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Price') }} </th>
                                    <th>{{ __('type') }}</th>
                                    {{-- <th>{{ __('Tax') }} (%)</th> --}}
                                    <th class="text-end">{{ __('Discription') }}
                                    </th>
                                    <th> </th>
                                    <th> </th>
                                </tr>
                            </thead>
                            <tbody class="ui-sortable" data-repeater-item>
                                <tr>
                                    <td width="25%" class="form-group pt-1">
                                        <div class="item_div">
                                            {{-- <select class="form-control item" name="item" placeholder="Select Employee">
                                            <option value="">{{ __('--') }}</option>
                                        </select> --}}
                                            {{ Form::select('item', $product_services, '', ['class' => 'form-control selectBox select2 item', 'style' => 'width:300px !important;  border-color: var(--primary) !important;', 'data-url' => route('returnorder.product'), 'required' => 'required']) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('quantity', '', ['class' => 'form-control quantity', 'required' => 'required', 'placeholder' => __('Qty'), 'required' => 'required']) }}
                                            {{-- <span class="unit input-group-text bg-transparent"></span> --}}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{-- {{ Form::label('price', __('Price'), ['class' => 'form-label']) }} --}}
                                            {{ Form::text('price', '', ['class' => 'form-control price', 'readonly' => 'readonly', 'required' => 'required', 'placeholder' => __('Price')]) }}
                                            {{-- {{ Form::hidden('price', '', ['class' => 'form-control price']) }} --}}
                                            <span
                                                class="input-group-text bg-transparent">{{ \Auth::user()->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::select(
                                                'type',
                                                ['use' => 'Used', 'damage' => 'Damage'], 'use',
                                                ['class' => 'form-control select','required' => 'required'],
                                            ) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{-- <div class="form-group"> --}}
                                            {{ Form::textarea('description', null, ['class' => 'form-control pro_description', 'rows' => '2', 'placeholder' => __('Description')]) }}
                                            {{-- </div> --}}
                                        </div>
                                    </td>
                                    {{-- <td>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="taxes"></div>
                                                {{ Form::hidden('tax', '', ['class' => 'form-control tax']) }}
                                                {{ Form::hidden('itemTaxPrice', '', ['class' => 'form-control itemTaxPrice']) }}
                                                {{ Form::hidden('itemTaxRate', '', ['class' => 'form-control itemTaxRate']) }}
                                            </div>
                                        </div>
                                    </td> --}}

                                    <td class="text-end amount">
                                        0.00
                                    </td>
                                    <td>
                                        <a href="#"
                                            class="ti ti-trash text-white text-white repeater-action-btn bg-danger ms-2"
                                            data-repeater-delete></a>
                                    </td>
                                </tr>
                                {{-- <tr>
                                    <td colspan="2">
                                        <div class="form-group">
                                            {{ Form::textarea('description', null, ['class' => 'form-control pro_description', 'rows' => '2', 'placeholder' => __('Description')]) }}
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
                                    <td><strong>{{ __(' TotalAmount') }} ({{ \Auth::user()->currencySymbol() }})</strong>
                                    </td>
                                    <td class="text-end subTotal">0.00</td>
                                    {{-- <td></td> --}}
                                </tr>
                                {{-- <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td></td>
                                    <td><strong>{{ __('Discount') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalDiscount">0.00</td>
                                    <td></td>
                                </tr> --}}
                                {{-- <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td></td>
                                    <td><strong>{{ __('Tax') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalTax">0.00</td>
                                    <td></td>
                                </tr> --}}
                                {{-- <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td class="blue-text"><strong>{{ __('Total Amount') }}
                                            ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="blue-text text-end totalAmount" id="totalAmount">0.00</td>
                                    <td></td>
                                </tr> --}}
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}"
                onclick="location.href = '{{ route('returnorder.index') }}';" class="btn btn-light">
            <input type="submit" value="{{ __('Create') }}" class="btn  btn-primary">
        </div>
        {{ Form::close() }}
    </div>
@endsection
