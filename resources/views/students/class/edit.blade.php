{{ Form::model($classes, array('route' => array('classes.update', $classes->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}<span style="color: red">
                *</span>
            {{ Form::select('branch_id', $branches, $classes->owned_by, ['class' => 'form-control select', 'required' => 'required',]) }}
        </div>
        <div class="form-group">
            {{ Form::label('name', __('Name'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            {{ Form::select('name', $grades, null, ['class' => 'form-control select', 'required' => 'required',]) }}
            {{-- {{ Form::text('name', null, array('class' => 'form-control','placeholder'=>__('Enter Class name'), 'style' => 'text-transform: uppercase;','required'=>'required')) }} --}}
        </div>
        <div class="form-group">
            {{ Form::label('section', __('Section'),['class'=>'form-label']) }}
            <select name="section[]" class="form-control select sec" id="section" multiple="multiple">
                <option value="" disabled>Select Section...</option>
                @foreach($sections as $section)
                    <option value="{{$section->id}}" {{ in_array($section->id, $class_sec) ? 'selected' : '' }}>{{$section->name}}</option>
                @endforeach
            </select>
        </div>
        {{-- //status active  --}}
        <div class="form-group">
            {{ Form::label('status', __('Status'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            {{ Form::select('status', ['1' => 'Active', '0' => 'IN-Active'], $classes->active_status, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-outline-primary">
</div>

{{Form::close()}}

<script>

    $(document).ready(function() {
        if ($(".sec").length > 0) {
            $($(".sec")).each(function(index, element) {
                var id = $(element).attr('id');
                var multipleCancelButton = new Choices(
                    '#' + id, {
                        removeItemButton: true,
                    }
                );
            });
        }
    });
    </script>
