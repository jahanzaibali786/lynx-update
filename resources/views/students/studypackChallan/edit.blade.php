@extends('layouts.admin')
@section('page-title')
    {{ __('StudyPack Challan Edit') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchase.index') }}">{{ __('Purchase') }}</a></li>
    <li class="breadcrumb-item">{{ __('StudyPack Challan Edit') }}</li>
@endsection
<style>
    .select2-selection__arrow {
        display: none !important;
    }
    .m-header.main-logo img{
    display: flex !important;
    position: relative !important;
    align-items: center !important;
    left: 50% !important;
    top: -120px !important;
    transform: translateX(-65px) !important;
    }
</style>
@push('script-page')
    <script src="{{ asset('js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('js/jquery.repeater.min.js') }}"></script>
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
                    // if($('.select2').length) {
                    //     $('.select2').select2();
                    // }
                    // $('.selectBox').select2();
                },
                hide: function(deleteElement) {

                    $(this).slideUp(deleteElement);
                    $(this).remove();
                    var inputs = $(".amount");
                    var subTotal = 0;
                    for (var i = 0; i < inputs.length; i++) {
                        subTotal = parseFloat(subTotal) + parseFloat($(inputs[i]).html());
                    }
                    $('.totalAmount').html(subTotal.toFixed(2));

                },
                ready: function(setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },
                isFirstItemUndeletable: true
            });
            var value = $(selector + " .repeater").attr('data-value');
            // console.log(value);

            if (typeof value != 'undefined' && value.length != 0) {
                value = JSON.parse(value);
                $repeater.setList(value);
                for (var i = 0; i < value.length; i++) {
                    var tr = $('#sortable-table .id[value="' + value[i].id + '"]').parent();
                    tr.find('.item').val(value[i].product_id);
                    changeItem(tr.find('.item'));
                }
                // After all items are set, trigger calculation events for all rows to sum amounts
                setTimeout(function() {
                    $('#sortable-table tbody tr').each(function() {
                        $(this).find('.quantity').trigger('keyup');
                        $(this).find('.price').trigger('keyup');
                    });
                }, 500);
            }

        }
        $(document).on('change', '#vender', function() {
            $('#vender_detail').removeClass('d-none');
            $('#vender_detail').addClass('d-block');
            $('#vender-box').removeClass('d-block');
            $('#vender-box').addClass('d-none');
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
                        $('#vender_detail').html(data);
                    } else {
                        $('#vender-box').removeClass('d-none');
                        $('#vender-box').addClass('d-block');
                        $('#vender_detail').removeClass('d-block');
                        $('#vender_detail').addClass('d-none');
                    }
                },

            });
        });
        $(document).on('click', '#remove', function() {
            $('#vender-box').removeClass('d-none');
            $('#vender-box').addClass('d-block');
            $('#vender_detail').removeClass('d-block');
            $('#vender_detail').addClass('d-none');
        });

        var purchase_id = '{{ $purchase->id }}';

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
                        url: '{{ route('studypackchallan.items') }}',
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': jQuery('#token').val()
                        },
                        data: {
                            'purchase_id': purchase_id,
                            'product_id': iteams_id,
                        },
                        cache: false,
                        success: function(data) {
                            var purchaseItems = JSON.parse(data);

                            var $row = $(el).closest('tr');
                            if (purchaseItems != null) {
                                var amount = (purchaseItems.price * purchaseItems.qty);
                                $row.find('.quantity').val(purchaseItems.qty);
                                $row.find('.price').val(purchaseItems.price);
                                $row.find('.amount').html(amount.toFixed(2));
                            } else {
                                $row.find('.quantity').val(1);
                                $row.find('.price').val(item.product.purchase_price);
                                $row.find('.amount').html(parseFloat(item.product.purchase_price).toFixed(2));
                            }
                            // Update total amount for all rows
                            var totalItemPrice = 0;
                            var inputs_quantity = $(".quantity");
                            var priceInput = $('.price');
                            for (var j = 0; j < priceInput.length; j++) {
                                totalItemPrice += (parseFloat(priceInput[j].value) * parseFloat(inputs_quantity[j].value));
                            }
                            $('.totalAmount').html((parseFloat(totalItemPrice)).toFixed(2));
                            // Update challan total_amount input
                            $("input[name='total_amount']").val(parseFloat(totalItemPrice).toFixed(2));
                        }
                    });
                },
            });
        }
        $(document).on('change', '.item', function() {
            changeItem($(this));
        });

        $(document).on('keyup', '.quantity', function() {
            var el = $(this).parent().parent().parent().parent();
            var quantity = $(this).val();
            var price = $(el.find('.price')).val();
            var totalItemPrice = (quantity * price);
            var amount = (totalItemPrice);
            $(el.find('.amount')).html(parseFloat(amount));
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
            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));
            // Update challan total_amount input
            $("input[name='total_amount']").val(parseFloat(subTotal).toFixed(2));

        })

        $(document).on('keyup change', '.price', function() {

            var el = $(this).parent().parent().parent().parent();
            var price = $(this).val();
            var quantity = $(el.find('.quantity')).val();
            var totalItemPrice = (quantity * price);
            var amount = (totalItemPrice);
            $(el.find('.amount')).html(parseFloat(amount));
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
            $('.totalAmount').html((parseFloat(subTotal)).toFixed(2));
            // Update challan total_amount input
            $("input[name='total_amount']").val(parseFloat(subTotal).toFixed(2));

        })

        
        $(document).on('click', '[data-repeater-create]', function() {
            $('.item :selected').each(function() {
                var id = $(this).val();
                $(".item option[value=" + id + "]").prop("disabled", true);
            });
        })

        $(document).on('click', '.del', function() {
            //     // $('.delete_item').click(function () {
            //     if (confirm('Are you sure you want to delete this element?')) {
            //         var el = $(this).parent().parent();
            //         var id = $(el.find('.id')).val();
            //         alert('asdas')
            //         $.ajax({
            //             url: '{{ route('purchase.product.destroy') }}',
            //             type: 'POST',
            //             headers: {
            //                 'X-CSRF-TOKEN': jQuery('#token').val()
            //             },
            //             data: {
            //                 'id': id
            //             },
            //             cache: false,
            //             success: function (data) {

            //             },
            //         });

            //     }
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            })
            swalWithBootstrapButtons.fire({
                title: 'Are you sure?',
                text: "This action can Delete Item.!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {

                    var el = $(this).parent().parent();
                    var id = $(el.find('.id')).val();
                    el.remove();
                    
                    // swalWithBootstrapButtons.fire(
                    //     'Updated!',
                    //     'Your session status has been changed.',
                    //     'success'
                    // )
                } else if (
                    result.dismiss === Swal.DismissReason.cancel
                ) {
                    // swalWithBootstrapButtons.fire(
                    //     'Cancelled',
                    //     'Your session status is safe :)',
                    //     'error'
                    // )
                }
            })
            // });
        });
    </script>
    <script>
        $(document).on('click', '[data-repeater-delete]', function() {
            // Get the row and the item id before removal
            var $row = $(this).closest('tr');
            var deletedId = $row.find('.id').val();
            // If the deletedId is not empty and is a previously saved item, track it for deletion
            if (deletedId && !isNaN(deletedId) && parseInt(deletedId) > 0) {
                // Add a hidden input to track deleted ids
                var $deletedInput = $("#deleted_items");
                if (!$deletedInput.length) {
                    $deletedInput = $('<input>').attr({type: 'hidden', id: 'deleted_items', name: 'deleted_items[]'});
                    $('form').append($deletedInput);
                }
                // If already an array, push, else create array
                var current = $deletedInput.val() ? $deletedInput.val().split(',') : [];
                if (current.indexOf(deletedId) === -1) {
                    current.push(deletedId);
                    $deletedInput.val(current.join(','));
                }
            }
            // Clear the hidden id so it is not submitted
            $row.find('.id').val('');
            // Re-enable the option in all .item selects if exists
            if (deletedId) {
                $(".item option[value='" + deletedId + "']").prop('disabled', false);
            }
            $(".price").change();
        });

        // $(document).ready(function() {
        //     setTimeout(function() {
        //         $('.selectBox').select2();
        //     }, 1000);
        // });
    </script>
@endpush

@section('content')
    <div class="row">

        {{ Form::model($purchase, ['route' => ['studypackchallan.update', $purchase->id], 'method' => 'PUT', 'class' => 'w-100']) }}
        <div class="col-12">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('date', __('Challan Date'), ['class' => 'form-label']) }}<span
                                    style="color: red">
                                    *</span>
                                {{ Form::date('date', date('Y-m-d', strtotime($purchase->challan_date)), ['class' => 'form-control ', 'required' => 'required']) }}
                            </div>
                        </div>

                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('challan_no', __('Challan No'), ['class' => 'form-label']) }}<span
                                    style="color: red">
                                    *</span>
                                {{ Form::text('challanNo', $purchase->challanNo, ['class' => 'form-control', 'placeholder' => __('Enter StudyPack Title'), 'required' => 'required','readonly']) }}
                            </div>
                        </div>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                            <div class="btn-box">
                                {{ Form::label('total_amount', __('Total Amount'), ['class' => 'form-label']) }}<span
                                    style="color: red"> *</span>
                                {{ Form::text('total_amount', $purchase->total_amount, ['class' => 'form-control subtotal', 'placeholder' => __('Enter StudyPack Cost'), 'required' => 'required', 'readonly' => 'readonly']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <h5 class="d-inline-block mb-4">{{ __('Challan Items') }}</h5>
            <div class="card repeater" data-value='{!! json_encode($purchase->items) !!}'>
                <div class="item-section py-2">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                            <div class="all-button-box me-2">
                                <a href="#" data-repeater-create="" class="btn btn-outline-primary"
                                    data-bs-toggle="modal" data-target="#add-bank">
                                    <span class="btn-inner--icon">Create</span> {{ __('Add item') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style " style="max-height: 600px; overflow: auto;">
                    <div class="table-responsive">
                        <table class="table  mb-0" data-repeater-list="items" id="sortable-table">
                            <thead>
                                <tr>
                                    <th >{{ __('Items') }}</th>
                                    <th >{{ __('Quantity') }}</th>
                                    <th >{{ __('Price') }} </th>
                                    {{-- <th width="10%">{{ __('Discount') }}</th> --}}
                                    {{-- <th>{{ __('Tax') }} (%)</th> --}}
                                    {{-- <th width="13%">{{ __('Description') }}</th> --}}
                                    <th class="text-end">{{ __('Amount') }} <br><small
                                            class="text-danger font-weight-bold">{{ __('after tax & discount') }}</small>
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="ui-sortable" data-repeater-item>
                                <tr>
                                    {{ Form::hidden('id', null, ['class' => 'form-control id']) }}
                                    <td >
                                        <div class="form-group">
                                            {{ Form::select('item', $product_services, null, ['class' => 'form-control custom-select item', 'style' => 'font-size: smaller; padding:5px 10px', 'data-url' => route('studypackchallan.product')]) }}
                                        </div>
                                    </td>
                                    <td >
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('quantity', null, ['class' => 'form-control quantity', 'required' => 'required', 'placeholder' => __('Qty'), 'style' => 'font-size: smaller; padding:5px 10px', 'required' => 'required']) }}
                                            <span class="unit input-group-text bg-transparent"
                                                style='font-size: smaller; padding:5px 10px'></span>
                                        </div>
                                    </td>
                                    <td >
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('price', null, ['class' => 'form-control price', 'required' => 'required', 'placeholder' => __('Price'), 'style' => 'font-size: smaller; padding:5px 10px', 'required' => 'required']) }}
                                            <span class="input-group-text bg-transparent"
                                                style='font-size: smaller; padding:5px 10px'>{{ \Auth::user()->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    {{-- <td width="13%">
                                        <div class="form-group price-input input-group search-form">
                                            {{ Form::text('discount', null, ['class' => 'form-control discount', 'required' => 'required', 'style' => 'font-size: smaller; padding:5px 10px', 'placeholder' => __('Discount')]) }}
                                            <span class="input-group-text bg-transparent"
                                                style='font-size: smaller; padding:5px 10px'>{{ \Auth::user()->currencySymbol() }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="taxes"></div>
                                                {{ Form::hidden('tax', '', ['class' => 'form-control tax']) }}
                                                {{ Form::hidden('itemTaxPrice', '', ['class' => 'form-control itemTaxPrice']) }}
                                                {{ Form::hidden('itemTaxRate', '', ['class' => 'form-control itemTaxRate']) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td style="font-size: smaller">
                                        <div class="form-group">
                                            {{ Form::textarea('description', null, ['class' => 'form-control pro_description', 'style' => 'font-size: smaller; padding:5px 10px', 'rows' => '2', 'placeholder' => __('Description')]) }}
                                        </div>
                                    </td> --}}

                                    <td class="text-end amount" style='font-size: smaller; padding:5px 10px'>
                                        0.00
                                    </td>
                                    <td>
                                        <a class="mx-1 btn mx-1 btn-sm btn-outline-danger repeater-action-btn align-items-center pt-2"
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
                                    <td style="float: right" class="blue-text"><strong>{{ __('Total Amount') }}
                                            ({{ \Auth::user()->currencySymbol() }})</strong></td>
                                    <td class="blue-text text-end totalAmount">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <br><br>
                    <div class="modal-footer">
                        <input type="button" value="{{ __('Cancel') }}"
                            onclick="location.href = '{{ route('purchase.index') }}';" class="btn btn-outline-light">&nbsp;&nbsp;&nbsp;
                        <input type="submit" value="{{ __('Update') }}" class="btn btn-outline-primary">
                    </div>
                </div>
            </div>
        </div>

        {{ Form::close() }}
    </div>
@endsection
