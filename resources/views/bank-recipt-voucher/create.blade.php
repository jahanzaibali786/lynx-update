@extends('layouts.admin')
@section('page-title')
    {{__('Bank Recipt Voucher Create')}}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Double Entry')}}</li>
    <li class="breadcrumb-item">{{__('Vouchers')}}</li>
    <li class="breadcrumb-item">{{__('Bank Recipt Voucher')}}</li>
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{asset('js/jquery.repeater.min.js')}}"></script>
    <script src="{{ asset('js/jquery-searchbox.js') }}"></script>

    <script>
        var selector = "body";
        if ($(selector + " .repeater").length) {
            // var $dragAndDrop = $("body .repeater tbody").sortable({
            //     handle: '.sort-handler'
            // });
            var $repeater = $(selector + ' .repeater').repeater({
                initEmpty: false,
                defaultValues: {
                    'status': 1
                },
                show: function () {
                    scrollTop = $(window).scrollTop();
                    $('body').css('overflow', 'hidden'); // Disable scroll

                    $(this).stop(true, true).slideDown('fast', function () {
                        $('body').css('overflow', '');   // Re-enable scroll
                        $(window).scrollTop(scrollTop);  // Restore scroll position
                    });
                    var file_uploads = $(this).find('input.multi');
                    if (file_uploads.length) {
                        $(this).find('input.multi').MultiFile({
                            max: 3,
                            accept: 'png|jpg|jpeg',
                            max_size: 2048
                        });
                    }
                    // for item SearchBox ( this function is  custom Js )
                    JsSearchBox();


                    // if($('.select2').length) {
                    //     $('.select2').select2();
                    // }
                },
                hide: function (deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {
                        $(this).slideUp(deleteElement);
                        $(this).remove();
                        $(this).slideDown();
                        var scrollTop = $(window).scrollTop(); // Capture current scroll position at the time of action

                            $(this).slideDown('fast', function () {
                                $(window).scrollTop(scrollTop); // Restore scroll position after animation completes
                            });


                        var inputs = $(".debit");
                        var totalDebit = 0;
                        for (var i = 0; i < inputs.length; i++) {
                            totalDebit = parseFloat(totalDebit) + parseFloat($(inputs[i]).val());
                        }
                        $('.totalDebit').html(totalDebit.toFixed(2));


                        var inputs = $(".credit");
                        var totalCredit = 0;
                        for (var i = 0; i < inputs.length; i++) {
                            totalCredit = parseFloat(totalCredit) + parseFloat($(inputs[i]).val());
                        }
                        $('.totalCredit').html(totalCredit.toFixed(2));


                    }
                },
                ready: function (setIndexes) {
                    // $dragAndDrop.on('drop', setIndexes);
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

        }

        $(document).on('keyup', '.debit', function () {
            var el = $(this).parent().parent().parent().parent();
            var debit = $(this).val();
            var credit = 0;
            el.find('.credit').val(credit);
            el.find('.amount').html(debit);


            var inputs = $(".debit");
            var totalDebit = 0;
             // for (var i = 0; i < inputs.length; i++) {
            //     totalDebit = parseFloat(totalDebit) + parseFloat($(inputs[i]).val());
            // }
            $('.debit').each(function() {
            const val = parseFloat($(this).val());
            if (!isNaN(val)) {
                totalDebit += val;
                }
            });
            $('.totalDebit').html(totalDebit.toFixed(2));

            el.find('.credit').attr("disabled", true);
            if (debit == '') {
                el.find('.credit').attr("disabled", false);
            }
        })

        $(document).on('keyup', '.credit', function () {
            var el = $(this).parent().parent().parent().parent();
            var credit = $(this).val();
            var debit = 0;
            el.find('.debit').val(debit);
            el.find('.amount').html(credit);

            var inputs = $(".credit");
            var totalCredit = 0;
            // for (var i = 0; i < inputs.length; i++) {
            //     totalCredit = parseFloat(totalCredit) + parseFloat($(inputs[i]).val());
            // }
            $('.credit').each(function() {
            const val = parseFloat($(this).val());
            if (!isNaN(val)) {
                totalCredit += val;
                }
            });
            $('.totalCredit').html(totalCredit.toFixed(2));

            el.find('.debit').attr("disabled", true);
            if (credit == '') {
                el.find('.debit').attr("disabled", false);
            }
        })
        function parseCleanNumber(selector) {
            let text = $(selector).text().replace(/,/g, '').trim();
            return parseFloat(text) || 0;
        }
      // Form submission handler
      $(document).on('submit', '#journal-form', function(e) {
            e.preventDefault(); // Stop default form submission

            let form = $(this);
            let totalDebit = parseCleanNumber('.totalDebit');
            let totalCredit = parseCleanNumber('.totalCredit');

            if (totalDebit !== totalCredit) {
                show_toastr('error', 'Total Debit and Total Credit must be equal', 'error');
                return; // Don't proceed
            }

            let url = form.attr('action');
            let method = form.attr('method') || 'POST';
            let formData = new FormData(this);

            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        show_toastr('success', response.message, 'success');
                        if (response.redirect) {
                            setTimeout(function() {
                                location.reload();
                                window.location.href = response.redirect;
                            }, 300);
                        }
                    } else {
                        show_toastr('error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Unexpected error occurred.';
                    show_toastr('error', msg, 'error');
                }
            });
        });
    </script>
    
@endpush

@section('content')

    {{ Form::open(array('url' => 'bank-recipt-voucher','class'=>'w-100','id'=>'journal-form')) }}
    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
    <div class="row mt-4">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
            <div class="row">
                <div class="col-lg-4 col-md-4">
                    <div class="form-group">
                        {{ Form::label('journal_number', __('Voucher Number'),['class'=>'form-label']) }}
                        <input type="text" class="form-control" value="{{\Auth::user()->BRVNumberFormat($journalId)}}" readonly>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4">
                    <div class="form-group">
                        {{ Form::label('date', __('Transaction Date'),['class'=>'form-label']) }}
                        {{Form::date('date',null,array('class'=>'form-control','required'=>'required'))}}
                    </div>
                </div>
                <div class="col-lg-4 col-md-4">
                    <div class="form-group">
                        {{ Form::label('reference', __('Reference'),['class'=>'form-label']) }}
                        {{ Form::text('reference', '', array('class' => 'form-control')) }}
                    </div>
                </div>
                <div class="col-lg-8 col-md-8">
                    <div class="form-group">
                        {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
                        {{ Form::textarea('description', '', array('class' => 'form-control','rows'=>'2')) }}
                </div>
            </div>
            </div>
        </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card repeater">
                <div class="item-section py-4">
                    <div class="row justify-content-between align-items-center">
                        <div class="col-md-12 d-flex align-items-center justify-content-between justify-content-md-end">
                            <a href="#" data-repeater-create="" class="btn btn-outline-primary me-4" data-toggle="modal" data-target="#add-bank">
                                 <span class="btn-inner--icon"> Create {{__('Add Accounts')}} </span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0" data-repeater-list="accounts" id="sortable-table">
                            <thead>
                            <tr>
                                <th>{{__('Account')}}</th>
                                <th>{{__('Debit')}}</th>
                                <th>{{__('Credit')}} </th>
                                <th>{{__('Description')}}</th>
                                <th class="text-end">{{__('Amount')}} </th>
                                <th width="2%"></th>
                            </tr>
                            </thead>

                            <tbody class="ui-sortable" data-repeater-item>
                            <tr>
                                <td width="25%" class="form-group pt-0">
                                    {{-- {{ Form::select('account', $accounts,'', array('class' => 'form-control js-searchBox','required'=>'required')) }} --}}
                                    <select name="account" class="form-control js-searchBox" required="required">
                                        @foreach ($chartAccounts as $chartAccount)
                                            <option value="{{ $chartAccount['id'] }}" class="subAccount">{{ $chartAccount['code_name'] }}</option>
                                            @foreach ($subAccounts as $subAccount)
                                                @if ($chartAccount['id'] == $subAccount['account'])
                                                    <option value="{{ $subAccount['id'] }}" class="ms-5"> &nbsp; &nbsp;&nbsp; {{ $subAccount['code_name'] }}</option>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    </select>
                                </td>

                                <td>
                                    <div class="form-group price-input">
                                        {{ Form::text('debit','', array('class' => 'form-control debit','required'=>'required','placeholder'=>__('Debit'),'required'=>'required')) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group price-input">
                                        {{ Form::text('credit','', array('class' => 'form-control credit','required'=>'required','placeholder'=>__('Credit'),'required'=>'required')) }}
                                    </div>
                                </td>
                                <td>
                                    <div class="form-group">
                                        {{ Form::text('description','', array('class' => 'form-control','placeholder'=>__('Description'))) }}
                                    </div>
                                </td>
                                <td class="text-end amount">0.00</td>
                                <td>
                                    <a href="#" class="ti ti-trash text-white repeater-action-btn btn-outline-danger ms-2 text-danger" data-repeater-delete></a>
                                </td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td></td>
                                <td class="text-end"><strong>{{__('Total Credit')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                <td class="text-end totalCredit">0.00</td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="text-end"><strong>{{__('Total Debit')}} ({{\Auth::user()->currencySymbol()}})</strong></td>
                                <td class="text-end totalDebit">0.00</td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" onclick="location.href = '{{route("bank-recipt-voucher.index")}}';" class="btn btn-light">
        <span class="btn-inner--icon"><input type="submit" value="{{__('Save')}}" class="btn btn-outline-primary"> </span>
    </div>
    {{ Form::close() }}

@endsection


