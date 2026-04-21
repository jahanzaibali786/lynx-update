{{Form::model($employeeScale,array('route' => array('employee_scale.update', $employeeScale->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('department_id',__('Department'),['class'=>'form-label'])}}<span style="color: red"> *</span>
                {{Form::select('department_id',$department,null,array('class'=>'form-control select','required'=>'required'))}}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('scale_no',__('Scale No'),['class'=>'form-label'])}}<span style="color: red"> *</span>
                {{Form::text('scale_no',null,array('class'=>'form-control','required'=>'required' ,'readonly'=>'readonly'))}}
            </div>
        </div>
        @foreach ($heads as $account)
        @php
        $headValue = $employeeScale->employeeScaleHeads->firstWhere('head', $account->id);
        @endphp
        <div class="col-md-6">
            <div class="form-group">
                <input type="hidden" name="account_id[]" id="" value="{{ $account->id }}">
                <label class="form-label" for="">{{ !empty($account->head) ? @$account->head : '-' }}<span style="color: red"> *</span></label>
                <input type="number" name="account_value[]" id="" placeholder="0" value="{{ $headValue? $headValue->head_value : 0 }}" class="form-control" min="0">
            </div>
        </div>
        @endforeach
        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('effect_from', __('Effect From'), ['class' => 'form-label']) }}<span
                    style="color: red">*</span>
                {{ Form::date('effect_from', null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('adhoc',__('Is Adhoc'),['class'=>'form-label'])}}<span style="color: red"> *</span>
                {{ Form::select('adhoc', [ '0' => 'No','1' => 'Yes',], null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {{Form::label('status',__('Status'),['class'=>'form-label'])}}<span style="color: red"> *</span>
                {{ Form::select('status', ['1' => 'Active', '0' => 'IN-Active'], null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
        </div>


    </div>
</div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn  btn-outline-primary">
    </div>
{{Form::close()}}
