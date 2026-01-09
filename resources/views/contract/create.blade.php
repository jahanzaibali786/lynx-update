{{ Form::open(['url' => 'contract' , 'onsubmit' => 'disableButton()' ]) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('subject', __('Subject'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
            {{ Form::text('subject', '', ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        @if($type == 'virtual')
            <input type="hidden" name="create_type" value="virtual" required>
        @else
            <input type="hidden" name="create_type" value="office" required>
        @endif

        <div class="form-group col-md-6 row">
            <div class="col-md-9">
                {{ Form::label('company', __('Company'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
            </div>
            <div class="col-md-3 d-flex">
                {{ Form::label('addpropcheck', __('New'), ['class' => 'form-check-label']) }}
                {{ Form::checkbox('new', '1', false, ['class' => 'form-check-input', 'id' => 'addpropcheck']) }}
            </div>
                {{ Form::text('newcompany', '', ['class' => 'form-control d-none companyText req' ]) }}
                {{ Form::select('company', $company, null, ['class' => 'form-control', 'placeholder' => __('Select Company'), 'id' => 'companySelect']) }}
        </div>

        <div class="form-group col-md-6 d-none companyText">
            {{ Form::label('phone_no', __('Phone No'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
            {{ Form::number('phone_no', '', ['class' => 'form-control req']) }}
        </div>
        <div class="form-group col-md-6 d-none companyText">
            {{ Form::label('ntn', __('NTN'), ['class' => 'form-label']) }}

            {{ Form::text('ntn', '', ['class' => 'form-control']) }}

        </div>

        <div class="form-group col-md-12 d-none companyText">
            {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
            {{ Form::email('email', '', ['class' => 'form-control req','placeholder' =>'123@gmail.com']) }}
        </div>
        
        <div class="form-group col-md-6">
            {{ Form::label('space', __('Space'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
            @if($type == 'virtual')
                <select name="space" class="form-control select space_select" id="space" required
                onchange="getchairs(this.value)">
                <option value="" disabled selected>Select a Space</option>
                @foreach ($spaces as $space)
                    <option value="{{ $space->id }}">{{ $space->name }} ( {{ @$space->space_types_name }} )</option>
                @endforeach
                </select>
            @else
                <select name="space" class="form-control select space_select" id="space" required
                    onchange="getchairs(this.value)">
                    <option value="" disabled selected>Select a Space</option>
                    @foreach ($spaces as $space)
                        <option value="{{ $space->id }}">{{ $space->name }} ( {{ @$space->type->name }} )</option>
                    @endforeach
                </select>
            @endif
        </div>
        @if($type == 'virtual')@else
            <div class="form-group col-md-6" id="ch">
                {{ Form::label('chair', __('Chair'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                <select name="chair[]" class="form-control select chair_select" id="chair" multiple="multiple">
                    <option value="" disabled>Select Chairs</option>
                </select>
            </div>
        @endif
        <div class="form-group col-md-6">
            {{ Form::label('type', __('Contract Type'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
            {{ Form::select('type', $contractTypes, null, ['class' => 'form-control', 'data-toggle="select"', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('value', __('Contract Value'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
            {{ Form::number('value', '', ['class' => 'form-control', 'required' => 'required', 'stage' => '0.01']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
            {{ Form::date('start_date', '', ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
            {{ Form::date('end_date', '', ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
            {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => '3']) !!}
        </div>
    </div>
    <div class="row">
        <div class=" col-md-6">
            {{ Form::label('servic', 'Services Charges', ['class' => 'form-label']) }}<span style="color: red"> *</span>
            <div class="d-flex col-md-12">
                <label class="form-label m-1" style="width:40%" for="{{ @$services->id }}">{{ ucfirst(@$services->name) }} : </label>
                <input type="hidden" name="services_id" class="form-label" value="{{ @$services->id }}">
                <input type="number" name="services_charges" id="{{ @$services->id }}" class="form-label m-1" style="width: 50%" required>

            </div>
        </div>
        <div class=" col-md-6">
            {{ Form::label('security', 'Security Deposit', ['class' => 'form-label']) }}<span style="color: red"> *</span>
            <div class="d-flex col-md-12">
                <label class="form-label m-1" style="width:40%" for="{{ @$security->id }}">{{ ucfirst(@$security->name) }} : </label>
                <input type="hidden" name="security_deposit_id" class="form-label" value="{{ @$security->id }}">
                <input type="number" name="security_deposit_price" id="{{ @$security->id }}" class="form-label m-1" style="width: 50%" required>

            </div>
        </div>
        <div class="row">
            <div class=" col-md-12">
                {{ Form::label('meeting_hours', 'Meeting Room & Board Room Hours', ['class' => 'form-label']) }}<span style="color: red"> *</span>
                @foreach ($ismeeting as $meeting)
                    <div class="d-flex col-md-12">

                        <label class="form-label m-1" style="width: 25%" for="{{ $meeting->id }}">{{ ucfirst($meeting->name) }} : </label>

                        <input type="hidden" name="room_hours_ids[]" value="{{ $meeting->id }}" class="form-label m-1"
                            style="width: 25%">
                        <input type="number" name="room_hours[]" id="{{ $meeting->id }}" class="form-label m-1"
                            style="width: 25%" required>
                        <label class="form-label m-1" for="{{ $meeting->id }}"> Hrs</label>
                        <input type="number" name="hourly_rate[]" id="hour{{ $meeting->id }}" class="form-label m-1"
                            style="width: 25%" required>
                        <label class="form-label m-1" for="hour{{ $meeting->id }}"> Hourly Rate</label>
                    </div>
                    @endforeach
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
                    var s = ` {{ Form::label('chair', __('Chair'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
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
