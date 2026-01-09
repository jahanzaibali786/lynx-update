{!! Form::open(['route' => 'challan.store', 'method' => 'POST']) !!}
{!! csrf_field() !!}
<div class="modal-body">
    <div class="row">
        <div class="form-group row">
            <div class="col">
                {{ Form::label('student_id', __('Student ID'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::text('student_id', $id, ['class' => 'form-control', 'required' => 'required','readonly'=>'readonly']) }}
            </div>
            <div class="col">
                {{ Form::label('challan_date', __('Challan Date'), ['class' => 'form-label']) }}<span style="color: red">&nbsp;(for the month date)</span>
                {!! Form::date('challan_date', null, ['class' => 'form-control', 'id' => 'challan_date']) !!}
            </div>
        </div>

        <div class="form-group row">
            <div class="col">
                {{ Form::label('challan_type', __('Challan Type'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {!! Form::select('challanType', [
                '' => 'Select Challan Type',
                'Regular' => 'Regular Challan',
                'Admission' => 'Admission Challan',
                'ReAdmission' => 'Re-Admission Challan',
                'CIE' => 'CIE Challan',
                'Withdrawal' => 'Withdrawal Challan',
                'Studypack' => 'Studypack',
                'Transfer' => [
                'InterBranch' => 'Inter Branch',
                'InterCity' => 'Inter City',
                'International' => 'International'
                ]
                ], null, ['class' => 'form-control', 'id' => 'challanType']) !!}
            </div>
            <div class="col-md-6" id="head">
                <label for="heads" class="form-label">{{ __('Heads') }}<span style="color: red">*</span></label>
                <select id="heads" name="heads[]" class="form-control select sec" style="border:1px solid var(--primary) !important;" >
                        <option value="" disabled>{{ __('Select Heads') }}</option>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <div class="col">
                {!! Form::label('issueDate', 'Issue Date:') !!}<span style="color: red"> *</span>
                {!! Form::date('issueDate', null, ['class' => 'form-control', 'id' => 'issueDate']) !!}
            </div>
            <div class="col">
                {!! Form::label('dueDate', 'Due Date:') !!}<span style="color: red"> *</span>
                {!! Form::date('dueDate', null, ['class' => 'form-control', 'id' => 'dueDate']) !!}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-outline-primary">
</div>

{!! Form::close() !!}

<script>
$(document).ready(function() {
    function fetchHeadsAndAmounts(studentId) {
        $.ajax({
            url: "{{ route('fetch_class_id') }}",
            type: 'GET',
            data: {
                id: studentId
            },
            success: function(response) {
                if (response.class_id) {
                    $.ajax({
                        url: "{{ route('fetch_heads_and_amounts') }}",
                        type: 'GET',
                        data: {
                            class_id: response.class_id
                        },
                        success: function(data) {
                            console.log(data);
                            if (data && data.length > 0) {
                                var s = `<label for="heads" class="form-label">{{ __('Heads') }}<span style="color: red">*</span></label>
                                        <select id="heads" name="heads[]" class="form-control select sec" style="border:1px solid var(--primary) !important;" multiple="multiple">
                                            <option value="" disabled>{{ __('Select Heads') }}</option>`;

                                for (var i = 0; i < data.length; i++) {
                                    s +=
                                    `<option value="${data[i].head_id}">${data[i].head_name}</option>`;
                                }

                                s += `</select>`;
                                $('#head').html(s);
                                if ($(".sec").length > 0) {
                                    $(".sec").each(function(index, element) {
                                        var id = $(element).attr('id');
                                        var multipleCancelButton = new Choices(
                                            '#' + id, {
                                                removeItemButton: true,
                                            }
                                        );
                                    });
                                }
                            } else {
                                console.log('No data available');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error fetching data:', error);
                        }
                    });
                } else {
                    console.log('Class ID not found');
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }

    var studentId = "{{ $id }}";
    fetchHeadsAndAmounts(studentId);
});
</script>