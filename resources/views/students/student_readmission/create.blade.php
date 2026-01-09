        <script>
            $(document).on('change', '#branch', function() {
                let branch = $(this).val();
                $.ajax({
                    url: "{{ route('branch.class') }}",
                    type: "POST",
                    data: {
                        branch_id: branch,
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: 'json',
                    success: function(result) {
                        var $classSelect = $('#class_select');
                        // Remove previous custom select wrapper and instance
                        if ($classSelect[0] && $classSelect[0].customSelectInstance) {
                            $classSelect[0].customSelectInstance.destroy();
                            delete $classSelect[0].customSelectInstance;
                        }
                        if ($classSelect.next('.custom-select-wrapper').length) {
                            $classSelect.next('.custom-select-wrapper').remove();
                        }
                        $classSelect.removeClass('custom-select');
    
                        // Clear and append new options
                        $classSelect.empty();
                        $classSelect.append($('<option>', {
                            value: 'all',
                            text: 'All Class'
                        }));
                        for (var j = 0; j < result.length; j++) {
                            var cls = result[j];
                            $classSelect.append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
    
                        // Re-add class and re-init
                        $classSelect.addClass('custom-select');
                        $classSelect.show();
                        // Directly create new CustomSelect instance for this select only
                        if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                            window.CustomSelect.create($classSelect[0]);
                        }
    
                        $('#student_select').html('<option value="">Select Student</option>');
                    }
                });
            });
    
            $(document).on('change', '#class_select', function() {
                let classId = $(this).val();
                $.ajax({
                    url: "{{ route('class.student_head') }}",
                    type: "POST",
                    data: {
                        class_id: classId,
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: 'json',
                    success: function(data) {
                        var $studentSelect = $('#student_select');
                        // Remove previous custom select wrapper and instance
                        if ($studentSelect[0] && $studentSelect[0].customSelectInstance) {
                            $studentSelect[0].customSelectInstance.destroy();
                            delete $studentSelect[0].customSelectInstance;
                        }
                        if ($studentSelect.next('.custom-select-wrapper').length) {
                            $studentSelect.next('.custom-select-wrapper').remove();
                        }
                        $studentSelect.removeClass('custom-select');
    
                        // Clear and append new options
                        $studentSelect.empty();
                        $studentSelect.append($('<option>', {
                            value: '',
                            text: 'Select Student'
                        }));
                        
                        for (var j = 0; j < data.student.length; j++) {
                            var std = data.student[j];
                            $studentSelect.append($('<option>', {
                                value: std.roll_no,
                                text: std.roll_no + ' - ' + std.stdname + ' s/d/o ' + std.fathername
                            }));
                        }
    
                        // Re-add class and re-init
                        $studentSelect.addClass('custom-select');
                        $studentSelect.show();
                        // Directly create new CustomSelect instance for this select only
                        if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                            window.CustomSelect.create($studentSelect[0]);
                        }
                    }
                });
            });
        </script>
{{ Form::open(array('url' => 'readmissionstudent')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('branch_id', __('Branch'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select', 'id' => 'branch_from', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('class_id', __('Class'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('class_id', ['' => 'Select Class'], null, ['class' => 'form-control select','id' => 'class_from', 'required' => 'required',]) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group std_data" >
                {{ Form::label('student_id', __('Student'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('student_id', ['' => 'Select Student'], null, ['class' => 'form-control select','id' => 'class_students', 'required' => 'required',]) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group" >
                {{ Form::label('session_id', __('Session'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('session_id', $session, null, ['class' => 'form-control select', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('readmission_date', __('Re-Admission Date'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::date('readmission_date', date('Y-m-d'), array('class' => 'form-control ','placeholder'=>__('Enter Transfer Date'),'required'=>'required')) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('month_date', __('Fee Month'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::month('month_date', date('Y-m'), array('class' => 'form-control ','placeholder'=>__('Enter Month'),'required'=>'required')) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('issue_date', __('Issue Date'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::date('issue_date', date('Y-m-d'), array('class' => 'form-control ','placeholder'=>__('Enter Issue Date'),'required'=>'required')) }}
            </div>
        </div>
        <div class="col-6">
            <div class="form-group">
                {{ Form::label('due_date', __('Due Date'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::date('due_date', date('Y-m-d', strtotime('+7 days')), array('class' => 'form-control ','placeholder'=>__('Enter Due Date'),'required'=>'required')) }}
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('reason', __('Re-Admission Reason'),['class'=>'form-label']) }}<span style="color: red"> *</span>
                {{ Form::textarea('reason', null, array('class' => 'form-control','placeholder'=>__('Enter Reason'),'required'=>'required','rows' => 2)) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-outline-primary">
</div>
{{ Form::close() }}
<script>
    $(document).ready(function() {
            $('#branch_from').trigger('change');
        });
    JsSearchBox();

</script>

