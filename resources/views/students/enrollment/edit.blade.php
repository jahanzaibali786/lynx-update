{!! Form::model($enrollment, ['route' => ['enrollment.edit', $enrollment->id], 'method' => 'POST']) !!}
{!! csrf_field() !!}
<div class="modal-body">
    <div class="row">
        <div class="form-group row">
            <div class="col">
                {{ Form::label('regId', __('Reg ID.'), ['class' => 'form-label']) }}
                {{ Form::text('regId', null, ['class' => 'form-control', 'placeholder' => __('Registration ID.'), 'readonly' => 'readonly']) }}
            </div>
            <div class="col">
                {{ Form::label('enrollId', __('Enrollment ID.'), ['class' => 'form-label']) }}
                {{ Form::text('enrollId', null, ['class' => 'form-control', 'placeholder' => __('Enrollment ID.'), 'readonly' => 'readonly']) }}
            </div>
        </div>
        <div class="form-group row">
            <div class="col">
                {{ Form::label('classname', __('Class Name'), ['class' => 'form-label']) }} <span style="color: red"> *</span>
                {!! Form::select('classname', ['' => 'Select Class'] + $classes->toArray(), $enrollment->class_id, ['class' => 'form-control','required' => 'required','id' => 'classname',]) !!}
            </div>

            <div class="col">
                {{ Form::label('section', __('Section'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {!! Form::select('section', ['' => 'Select Section'], $enrollment->section_id, ['class' => 'form-control','required' => 'required','id' => 'section',]) !!}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-outline-primary">
</div>

{!! Form::close() !!}
<script>
        var classId = <?php echo $enrollment->class_id ; ?> ;
        var sectionId = <?php echo $enrollment->section_id; ?>;
        var classnameDropdown = document.getElementById('classname');
        var sectionDropdown = document.getElementById('section');
        if (classId) {
            classnameDropdown.value = classId;
        }
        if (classId) {
            fetch('{{ url('/get-sections/') }}/' + classId)
                .then(response => response.json())
                .then(data => {
                    sectionDropdown.innerHTML = '<option value="">Select Section</option>';
                    data.forEach(section => {
                        var option = document.createElement('option');
                        option.value = section.id;
                        option.text = section.name;
                        sectionDropdown.appendChild(option);
                    });
                    if (sectionId) {
                        sectionDropdown.value = sectionId;
                    }
                });
        }
        classnameDropdown.addEventListener('change', function() {
            var classId = this.value;
            sectionDropdown.innerHTML = '<option value="">Select Section</option>';
            if (classId) {
                fetch('{{ url('/get-sections/') }}/' + classId)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(section => {
                            var option = document.createElement('option');
                            option.value = section.id;
                            option.text = section.name;
                            sectionDropdown.appendChild(option);
                        });
                    });
            }
        });
</script>
