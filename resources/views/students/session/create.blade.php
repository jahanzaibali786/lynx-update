{{ Form::open(array('url' => 'session')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group">
            {{ Form::label('year', __('Year'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            {{ Form::text('year', null, array('class' => 'form-control','placeholder'=>__('Enter Year'),'required'=>'required')) }}
        </div>
        {{-- <div class="form-group">
            {{ Form::label('title', __('Title'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            {{ Form::text('title', null, array('class' => 'form-control','placeholder'=>__('Enter Title'),'required'=>'required')) }}
        </div> --}}
        <div class="form-group">
            {{ Form::label('starting_date', __('Starting Date'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            {{ Form::date('starting_date', null, array('class' => 'form-control','placeholder'=>__('Enter Date'),'required'=>'required')) }}
        </div>
        <div class="form-group">
            {{ Form::label('ending_date', __('Ending Date'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            {{ Form::date('ending_date', null, array('class' => 'form-control','placeholder'=>__('Enter Date'),'required'=>'required')) }}
        </div>

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-outline-primary">
</div>

{{Form::close()}}


