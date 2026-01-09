{{ Form::open(array('url' => 'concession_policy')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-6">
        <div class="form-group">
            {{ Form::label('title', __('Title'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            {{ Form::text('title', null, array('class' => 'form-control','placeholder'=>__('Enter Title'),'required'=>'required')) }}
        </div>
        </div>
        <div class="col-6">
        <div class="form-group">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}<span style="color: red"> *</span>
            {{ Form::text('description', null, array('class' => 'form-control','placeholder'=>__('Enter Description'),'required'=>'required')) }}
        </div>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="row">
                <div class="col-7">
                    <div class="form-group">
                        {{ Form::label('title', __('Title'),['class'=>'form-label']) }}
                    </div>
                </div>
                <div class="col-5">
                    <div class="form-group">
                        {{ Form::label('concession', __('Concession %'),['class'=>'form-label']) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="row">
                <div class="col-7">
                    <div class="form-group">
                        {{ Form::label('title', __('Title'),['class'=>'form-label']) }}
                    </div>
                </div>
                <div class="col-5">
                    <div class="form-group">
                        {{ Form::label('concession', __('Concession %'),['class'=>'form-label']) }}
                    </div>
                </div>
            </div>
        </div>
        @foreach ($heads as $account)
            @if($account->status == 0)
            {{ Form::hidden('title_id[]', $account->id, array('class' => 'form-control','placeholder'=>__('Enter Title'))) }}
            {{ Form::hidden('head_name[]', $account->fee_head, array('class' => 'form-control','placeholder'=>__('Enter Title'),'readonly'=>'readonly')) }}
            {{ Form::hidden('concession[]', 0, array('class' => 'form-control', 'step' => 'any')) }}
            @else
            <div class="col-6">
                <div class="row">
                <div class="col-7">
                    <div class="form-group">
                        {{ Form::hidden('title_id[]', $account->id, array('class' => 'form-control','placeholder'=>__('Enter Title'))) }}
                        {{ Form::text('head_name[]', $account->fee_head, array('class' => 'form-control','placeholder'=>__('Enter Title'),'readonly'=>'readonly')) }}
                    </div>
                </div>
                <div class="col-5">
                    <div class="form-group">
                        {{ Form::number('concession[]', 0, array('class' => 'form-control', 'step' => 'any','min' => '0')) }}
                    </div>
                </div>
                </div>
            </div>
            @endif

        @endforeach
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Create')}}" class="btn  btn-outline-primary">
</div>

{{Form::close()}}

