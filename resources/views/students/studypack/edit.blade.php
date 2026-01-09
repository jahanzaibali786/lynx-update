@extends('layouts.admin')
@section('page-title')
    {{ __('StudyPack Edit') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('studypack.index') }}">{{ __('Study Pack') }}</a></li>
    <li class="breadcrumb-item">{{ __('StudyPack Edit') }}</li>
    <style>
        .select2-selection__arrow {
            display: none !important;
        }
    </style>
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('public/acron/select2.css') }}" />
    <script src="{{ asset('public/acron/select2.js') }}"></script>
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
                    $('.subtotal').val(subTotal.toFixed(2));

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
                    'product_id': iteams_id,
                },
                cache: false,
                success: function(data) {
                    var item = JSON.parse(data);

                    $.ajax({
                        url: '{{ route('studypack.items') }}',
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

                                $(el.parent().parent().find('.quantity')).val(invoiceItems
                                .quantity);
                                $(el.parent().parent().find('.price')).val(invoiceItems.price);
                                $(el.parent().parent().find('.discount')).val(invoiceItems
                                .discount);
                                $(el.parent().parent().parent().find('.pro_description')).val(item
                                    .product.description);
                                // $('.pro_description').text(invoiceItems.description);

                            } else {

                                $(el.parent().parent().find('.quantity')).val(1);
                                $(el.parent().parent().find('.price')).val(item.product.sale_price);
                                $(el.parent().parent().find('.discount')).val(0);
                                // $(el.parent().parent().find('.pro_description')).val(item.product.description);
                                $(el.parent().parent().parent().find('.pro_description')).val(item
                                    .product.description);
                                // $('.pro_description').text(item.product.description);

                            }

                            var taxes = '';
                            var tax = [];

                            var totalItemTaxRate = 0;
                            for (var i = 0; i < item.taxes.length; i++) {
                                taxes +=
                                    '<span class="badge bg-primary p-2 px-3 rounded mt-1 mr-1">' +
                                    item.taxes[i].name + ' ' + '(' + item.taxes[i].rate + '%)' +
                                    '</span>';
                                tax.push(item.taxes[i].id);
                                totalItemTaxRate += parseFloat(item.taxes[i].rate);
                            }

                            var discount = $(el.parent().parent().find('.discount')).val();
                            var discount = 0;


                            if (invoiceItems != null) {
                                var itemTaxPrice = parseFloat((totalItemTaxRate / 100)) *
                                    parseFloat((invoiceItems.price * invoiceItems.quantity) -
                                        discount);
                            } else {
                                var itemTaxPrice = parseFloat((totalItemTaxRate / 100)) *
                                    parseFloat((item.product.sale_price * 1) - discount);
                            }

                            $(el.parent().parent().find('.itemTaxPrice')).val(itemTaxPrice.toFixed(
                                2));
                            $(el.parent().parent().find('.itemTaxRate')).val(totalItemTaxRate
                                .toFixed(2));
                            $(el.parent().parent().find('.taxes')).html(taxes);
                            $(el.parent().parent().find('.tax')).val(tax);
                            $(el.parent().parent().find('.unit')).html(item.unit);
                            // $(el.parent().parent().find('.discount')).val(item.discount);


                            var inputs = $(".amount");
                            var subTotal = 0;
                            for (var i = 0; i < inputs.length; i++) {
                                subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                            }

                            var totalItemPrice = 0;
                            var inputs_quantity = $(".quantity");

                            var priceInput = $('.price');
                            for (var j = 0; j < priceInput.length; j++) {
                                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(
                                    inputs_quantity[j].value));
                            }


                            var totalItemTaxPrice = 0;
                            var itemTaxPriceInput = $('.itemTaxPrice');
                            for (var j = 0; j < itemTaxPriceInput.length; j++) {
                                totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
                                if (invoiceItems != null) {
                                    $(el.parent().parent().find('.amount')).html(parseFloat(
                                        amount) + parseFloat(itemTaxPrice) - parseFloat(
                                            discount));
                                } else {
                                    $(el.parent().parent().find('.amount')).html(parseFloat(item
                                        .totalAmount) + parseFloat(itemTaxPrice));
                                }

                            }

                            var totalItemDiscountPrice = 0;
                            var itemDiscountPriceInput = $('.discount');

                            for (var k = 0; k < itemDiscountPriceInput.length; k++) {
                                totalItemDiscountPrice += parseFloat(itemDiscountPriceInput[k]
                                    .value);
                            }


                            $('.subTotal').html(totalItemPrice.toFixed(2));
                            $('.totalTax').html(totalItemTaxPrice.toFixed(2));
                            $('.totalAmount').html((parseFloat(totalItemPrice) - parseFloat(
                                    totalItemDiscountPrice) + parseFloat(totalItemTaxPrice))
                                .toFixed(2));
                            $('.totalDiscount').html(totalItemDiscountPrice.toFixed(2));
                            $('.subtotal').val((parseFloat(totalItemPrice) - parseFloat(
                                    totalItemDiscountPrice) + parseFloat(totalItemTaxPrice))
                                .toFixed(2));
                            $('.selectBox').select2();
                        }
                    });


                },
            });
        }

        $(document).on('keyup', '.quantity', function() {
            var quntityTotalTaxPrice = 0;

            var el = $(this).parent().parent().parent().parent();

            var quantity = $(this).val();
            var price = $(el.find('.price')).val();


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
            $('.subtotal').val((parseFloat(subTotal)).toFixed(2));

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
            $('.subtotal').val((parseFloat(subTotal)).toFixed(2));

        })

        // $(document).on('keyup change', '.discount', function () {
        //     var el = $(this).parent().parent().parent();
        //     var discount = $(this).val();
        //     if(discount.length <= 0)
        //     {
        //         discount = 0 ;
        //     }
        //     var price = $(el.find('.price')).val();

        //     var quantity = $(el.find('.quantity')).val();
        //     var totalItemPrice = (quantity * price) - discount;

        //     var amount = (totalItemPrice);



        //     var totalItemTaxRate = $(el.find('.itemTaxRate')).val();
        //     var itemTaxPrice = parseFloat((totalItemTaxRate / 100) * (totalItemPrice));
        //     $(el.find('.itemTaxPrice')).val(itemTaxPrice.toFixed(2));

        //     $(el.find('.amount')).html(parseFloat(itemTaxPrice)+parseFloat(amount));


        //     var totalItemTaxPrice = 0;
        //     var itemTaxPriceInput = $('.itemTaxPrice');
        //     for (var j = 0; j < itemTaxPriceInput.length; j++) {
        //         totalItemTaxPrice += parseFloat(itemTaxPriceInput[j].value);
        //     }


        //     var totalItemPrice = 0;
        //     var inputs_quantity = $(".quantity");

        //     var priceInput = $('.price');
        //     for (var j = 0; j < priceInput.length; j++) {
        //         totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
        //     }

        //     var inputs = $(".amount");

        //     var subTotal = 0;
        //     for (var i = 0; i < inputs.length; i++) {
        //         subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
        //     }


        //     var totalItemDiscountPrice = 0;
        //     var itemDiscountPriceInput = $('.discount');

        //     for (var k = 0; k < itemDiscountPriceInput.length; k++) {

        //         totalItemDiscountPrice += parseFloat(itemDiscountPriceInput[k].value);
        //     }

        //     $('.subTotal').html(totalItemPrice.toFixed(2));
        //     $('.totalTax').html(totalItemTaxPrice.toFixed(2));

        //     $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));
        //     $('.totalDiscount').html(totalItemDiscountPrice.toFixed(2));
        //     $('.subtotal').val((parseFloat(subTotal)).toFixed(2));


        // })

        $(document).on('click', '[data-repeater-create]', function() {
            $('.item :selected').each(function() {
                var id = $(this).val();
                $(".item option[value=" + id + "]").prop("disabled", true);
            });
        })

        $(document).on('click', '[data-repeater-delete]', function() {
            // $('.delete_item').click(function () {
            if (confirm('Are you sure you want to delete this element?')) {
                var el = $(this).parent().parent();
                var id = $(el.find('.id')).val();
                var amount = $(el.find('.amount')).html();

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
                    cache: false,
                    success: function(data) {

                    },
                });

            }
        });
        $(document).on("change", ".b_class", function() {
            var branch = $(this).val();
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
                        $('#class').append('<option value="' + data[index]['id'] + '">' + data[index][
                            'name'
                        ] + '</option>');
                    }
                }
            });
            setTimeout(function() {
                var old_class = $('#old_class').val();
                $('#class').val(old_class);
            }, 1000);
        });
    </script>

    <script>
        $(document).on('click', '[data-repeater-delete]', function() {
            $(".price").change();
            // $(".discount").change();
        });
        $(document).ready(function() {
            setTimeout(function() {
                $(".b_class").trigger("change");
                // $('.selectBox').select2();
            }, 1000);
        });
    </script>
@endpush

@section('content')
    <div class="row">
        {{ Form::model($invoice, ['route' => ['studypack.update', $invoice->id], 'method' => 'PUT', 'class' => 'w-100']) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="form-group">
                                {{ Form::label('session', __('Session'), ['class' => 'form-label']) }}<span
                                    style="color: red"> *</span>
                                {{ Form::select('session', $session, isset($_GET['session']) ? $_GET['session'] : '', ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}<span style="color: red">
                                    *</span>
                                {{ Form::select('class', $class ?? [], $invoice->class, ['class' => 'form-control select custom-select cl_std', 'id' => 'class']) }}
                                <input type="hidden" name="old_class" id="old_class" value={{ $invoice->class }}>
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}<span style="color: red">
                                    *</span>
                                {{ Form::date('date', date('Y-m-d', strtotime($invoice->date)), ['class' => 'form-control ', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}<span style="color: red">
                                    *</span>
                                {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter StudyPack Title'), 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('study_pack_cost', __('Study Pack Cost'), ['class' => 'form-label']) }}<span
                                    style="color: red"> *</span>
                                {{ Form::text('study_pack_cost', null, ['class' => 'form-control subtotal', 'placeholder' => __('Enter StudyPack Cost'), 'required' => 'required', 'readonly' => 'readonly']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <h5 class=" d-inline-block mb-4">{{ __('Product / Items') }}</h5>
            <div class="card repeater" data-value='{!! json_encode($invoice->items) !!}'>
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
                        <table class="table mb-0 table-custom-style" data-repeater-list="items" id="sortable-table">
                            <thead>
                                <tr>
                                    <th width="25%">{{ __('Products') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Price') }} </th>
                                    {{-- <th>{{__('Discount')}}</th> --}}
                                    <th>{{ __('Tax') }}</th>
                                    <th class="text-end">{{ __('Amount') }} </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="ui-sortable" data-repeater-item>
                                <tr>
                                    {{ Form::hidden('id', null, ['class' => 'form-control id']) }}
                                    <td width="25%" class="form-group pt-0">
                                        {{ Form::select('item', $product_services, '', ['class' => 'form-control custom-select item', 'data-url' => route('studypack.product'), 'required' => 'required']) }}
                                    </td>
                                    <td>

                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('quantity', null, ['class' => 'form-control quantity', 'required' => 'required', 'placeholder' => __('Qty'), 'required' => 'required']) }}
                                            <span class="unit input-group-text bg-transparent"></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('price', null, ['class' => 'form-control price', 'required' => 'required', 'placeholder' => __('Price'), 'required' => 'required']) }}
                                            <span
                                                class="input-group-text bg-transparent">{{ \Auth::user()->currencySymbol() }}</span>
                                        </div>
                                        {{ Form::hidden('discount', null, ['class' => 'form-control discount', 'required' => 'required', 'placeholder' => __('Discount')]) }}
                                    </td>
                                    {{-- <td>
                                    <div class="form-group price-input input-group search-form">
                                        {{ Form::text('discount',null, array('class' => 'form-control discount','required'=>'required','placeholder'=>__('Discount'))) }}
                                        <span class="input-group-text bg-transparent">{{\Auth::user()->currencySymbol()}}</span>
                                    </div>
                                </td> --}}
                                    <td>
                                        <div class="form-group">
                                            <div class="input-group colorpickerinput">
                                                <div class="taxes"></div>
                                                {{ Form::hidden('tax', '', ['class' => 'form-control tax']) }}
                                                {{ Form::hidden('itemTaxPrice', '', ['class' => 'form-control itemTaxPrice']) }}
                                                {{ Form::hidden('itemTaxRate', '', ['class' => 'form-control itemTaxRate']) }}
                                            </div>
                                        </div>
                                    </td>

                                    <td class="text-end amount">0.00</td>

                                   <td>
                                        <a class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center pt-2"
                                            title="{{ __('Delete') }}" data-repeater-delete>
                                            <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                        </a>
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
                                    {{-- <td></td> --}}
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
                                    {{-- <td></td> --}}
                                    <td><strong>{{ __('Tax') }} ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalTax">0.00</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    {{-- <td>&nbsp;</td> --}}
                                    <td class="blue-text"><strong>{{ __('Total Amount') }}
                                            ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="text-end totalAmount blue-text">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" onclick="location.href = '{{ route('studypack.index') }}';"
                class="btn btn-light me-3">
            <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">
        </div>
        {{ Form::close() }}
    </div>
@endsection
