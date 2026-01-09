{{ Form::open(['url' => 'contract' , 'onsubmit' => 'disableButton()' ]) }}
<div class="modal-body">
    <div class="row">
        <div class="card-body table-border-style">
            <div class="table-responsive">
                <table class="table mb-0" data-repeater-list="items" id="sortable-table">
                    <thead>
                    {{-- <tr>
                        <th width="20%">{{__('Items')}}</th>
                        <th>{{__('Quantity')}}</th>
                        <th>{{__('Price')}} </th>
                        <th>{{__('Discount')}}</th>
                        <th>{{__('Tax')}} (%)</th>
                        <th class="text-end">{{__('Amount')}}
                            <br><small class="text-danger font-bold">{{__('after tax & discount')}}</small>
                        </th>
                        <th></th>
                    </tr> --}}
                    <tr>
                        <th >{{__('Account')}}</th>
                        <th class="text-end">{{__( 'Amount')}}
                        {{-- <th>{{__('Price')}} </th>  --}}
                        <th>{{__('Description')}}</th>
                        </th>
                        {{-- <th></th> --}}
                    </tr>
                    </thead>
                    <tbody class="ui-sortable" data-repeater-item>
                    <tr>
                        <td class="form-group">
                            {{ Form::select('chart_account_id', $chartAccounts,'', array('class' => 'form-control select2 js-searchBox')) }}
                        </td>
                        <td class="form-group">
                            <div class="input-group ">
                                {{ Form::text('amount','', array('class' => 'form-control accountAmount','placeholder'=>__('Amount'))) }}
                                <span class="input-group-text bg-transparent">{{\Auth::user()->currencySymbol()}}</span>
                            </div>
                        </td>
                        <td colspan="2" class="form-group">
                            {{ Form::textarea('description', null, ['class'=>'form-control pro_description','rows'=>'1','placeholder'=>__('Description')]) }}
                        </td>
                        <td></td>
                        {{-- <td class="text-end accountamount">
                            0.00
                        </td> --}}
                        {{-- <td>
                            @can('delete proposal product')
                                <a href="#" class="ti ti-trash text-white repeater-action-btn bg-danger ms-2" data-repeater-delete></a>
                            @endcan
                        </td> --}}
                    </tr>

                    </tbody>
              
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" id="myButton" value="{{ __('Create') }}" class="btn  btn-primary">
</div>
{{ Form::close() }}

<script src="{{ asset('assets/js/plugins/choices.min.js') }}"></script>
<script>
        document.getElementById('myButton').disabled = true;
        setTimeout(function() {
            document.getElementById('myButton').disabled = false;
        }, 3000); 

    if ($(".multi-select").length > 0) {
        $($(".multi-select")).each(function(index, element) {
            var id = $(element).attr('id');
            var multipleCancelButton = new Choices(
                '#' + id, {
                    removeItemButton: true,
                }
            );
        });
    }
</script>
<script>
    if ($(".chair_select").length > 0) {
        $($(".chair_select")).each(function(index, element) {
            var id = $(element).attr('id');
            var multipleCancelButton = new Choices(
                '#' + id, {
                    removeItemButton: true,
                }
            );
        });
    }
</script>

<script type="text/javascript">
    $(".client_select").change(function() {

        var client_id = $(this).val();
        getparent(client_id);
    });

    function getparent(bid) {

        $.ajax({
            url: `{{ url('contract/clients/select') }}/${bid}`,
            type: 'GET',
            success: function(data) {
                console.log(data);
                $("#project_id").html('');
                $('#project_id').append(
                    '<select class="form-control" id="project_id" name="project_id[]"  ></select>');
                //var sdfdsfd = JSON.parse(data);
                $.each(data, function(i, item) {
                    console.log(item);
                    $('#project_id').append('<option value="' + item.id + '">' + item.name +
                        '</option>');
                });

                // var multipleCancelButton = new Choices('#project_id', {
                //     removeItemButton: true,
                // });

                if (data == '') {
                    $('#project_id').empty();
                }
            }
        });
    }

    $('#addpropcheck').on('change', function() {
        if ($(this).is(":checked")) {
            $('#companySelect').css('display', 'none');
            $('.companyText').removeClass('d-none');
            $('#companySelect').prop('required', false);
            $('.req').prop('required', true);
        } else {
            $('#companySelect').prop('required', true);
            $('.req').prop('required', false);
            $('.companyText').addClass('d-none');
            $('#companySelect').css('display', 'block');
        }
    });


    function getchairs(ids) {
        $.ajax({
            url: `{{ url('space_chair') }}/${ids}`,
            type: 'GET',
            success: function(data) {
                if (data.success == 'true') {
                    var s = ` {{ Form::label('chair', __('Chair'), ['class' => 'form-label']) }}
                <select name="chair[]"  class="form-control select chair_select" id="chair"   multiple="multiple">
                <option value="" disabled >Select Chairs</option> `;
                    $("#ch").empty();

                    for (var i = 0; i < data.data.length; i++) {

                        s += `<option value="` + data.data[i]['id'] +
                            `" ${data.assignchair.indexOf(data.data[i]['id']) !== -1 ? 'disabled' : ''}>` +
                            data.data[i]['name'] + `</option>`;
                    }
                    s += `</select>`;
                    $('#ch').html(s);
                    if ($(".chair_select").length > 0) {
                        $($(".chair_select")).each(function(index, element) {
                            var id = $(element).attr('id');
                            var multipleCancelButton = new Choices(
                                '#' + id, {
                                    removeItemButton: true,
                                }
                            );
                        });
                    }
                }
            }
        });
    }
</script>
