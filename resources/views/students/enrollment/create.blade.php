
{!! Form::open(['route' => 'enrollment.store', 'method' => 'POST']) !!}
{!! csrf_field() !!}
<div class="modal-body">
    <div class="row">
    <div class="form-group row">
        <div class="col">
            {{ Form::label('regId', __('Registration ID.'), ['class' => 'form-label']) }} <span style="color: red"> *</span>
            {{ Form::text('regId', $id, ['class' => 'form-control', 'placeholder' => __('Registration ID.'), 'required' => 'required', 'readonly' => 'readonly']) }}
        </div>

        <div class="col">
            {{ Form::label('enrollId', __('Enrollment ID.'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
            {{ Form::text('enrollId', null, ['class' => 'form-control', 'placeholder' => __('Enrollment ID.'), 'required' => 'required']) }}
        </div>
    </div>
    <div class="form-group row">
        <div class="col">
            {{ Form::label('classname', __('Class'), ['class' => 'form-label']) }}<span style="color: red">*</span>
            {!! Form::select('classname', ['' => 'Select Class'] + $classes->toArray(), null, ['class' =>'form-control', 'required' => 'required', 'id' => 'classname']) !!}
        </div>

        <div class="col-md-6">
            {{ Form::label('section', __('Section'), ['class' => 'form-label']) }} <span style="color: red">*</span>
            {!! Form::select('section', ['' => 'Select Section'], null, ['class' => 'form-control', 'required' => 'required', 'id' => 'section']) !!}
        </div>
    </div>
    <div class="form-group row">
        <div class="col-6">
            {{ Form::label('roll_no', __('Roll No'), ['class' => 'form-label']) }}<span style="color: red">*</span>
            {{ Form::text('roll_no', null, ['class' => 'form-control', 'placeholder' => __('Roll No'), 'required' => 'required',]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-outline-primary">
</div>

{{Form::close()}}

<script>
    // document.addEventListener("DOMContentLoaded", function () {
        var classnameDropdown = document.getElementById('classname');
        var sectionDropdown = document.getElementById('section');
        var classId = this.value;
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
    // });
</script>
