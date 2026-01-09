{{ Form::model($challan, array('route' => array('challan.update', $challan->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('challan Id', __('Challan Id'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{Form::text('challan_id',$challan->id,['class' => 'form-control', 'required' => 'required','disabled'=>'disabled'])}}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('Student Id', __('Roll No'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{Form::text('student_id',@$challan->student ? @$challan->student->roll_no : '',['class' => 'form-control', 'required' => 'required','disabled'=>'disabled'])}}
            </div>
        </div>
        <div class="col-6">
                {{ Form::label('challan_type', __('Challan Type'), ['class' => 'form-label']) }}<span
                    style="color: red">
                    *</span>
                {!! Form::select('challanType', [
                '' => 'Select Challan Type',
                'Regular Challan' => 'Regular Challan',
                'Admission Challan' => 'Admission Challan',
                'ReAdmission Challan' => 'Re-Admission Challan',
                'CIE Challan' => 'CIE Challan',
                'Withdrawal Challan' => 'Withdrawal Challan',
                'Studypack Challan' => 'Studypack',
                'Transfer' => [
                'InterBranch' => 'Inter Branch',
                'InterCity' => 'Inter City',
                'International' => 'International'
                ]
                ], $challan->challan_type, ['class' => 'form-control', 'id' => 'challanType']) !!}
            </div>
            <div class="col-6">
                {{ Form::label('challan_date', __('Challan Date'), ['class' => 'form-label']) }}<span
                    style="color: red">&nbsp;(for the month date)</span>
                {!! Form::date('challan_date', $challan->challan_date, ['class' => 'form-control', 'id' => 'challan_date']) !!}
            </div><br>
            <div class="col-12 my-4" id="head">
                <!-- Select dropdown will be appended here -->
            </div><br>
            <div class="col-6">
                {!! Form::label('issueDate', 'Issue Date:') !!}
                {!! Form::date('issueDate', $challan->issue_date, ['class' => 'form-control', 'id' => 'issueDate']) !!}
            </div>
            <div class="col-6">
                {!! Form::label('dueDate', 'Due Date:') !!}
                {!! Form::date('dueDate', $challan->due_date, ['class' => 'form-control', 'id' => 'dueDate']) !!}
            </div>

            <div class="col-12">

            </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>


{{Form::close()}}

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
                                    var selected = '';
                                    if ($.inArray(data[i].head_id, selectedHeads) !== -1) {
                                        selected = 'selected';
                                    }
                                    s +=
                                    `<option value="${data[i].head_id}" selected>${data[i].head_name}</option>`;
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

    var studentId = "{{ $challan->student_id }}";
    var selectedHeads = {!! json_encode($challan->heads) !!};
    fetchHeadsAndAmounts(studentId);
});
</script>
