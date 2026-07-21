{{ Form::model($chartOfAccount, array('route' => array('chart-of-account.update', $chartOfAccount->id), 'method' => 'PUT')) }}
<div class="modal-body">

    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('name', __('Name'),['class'=>'form-label']) }}
            {{ Form::text('name', null, array('class' => 'form-control','required'=>'required')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('code', __('Code'),['class'=>'form-label']) }}
            {{ Form::text('code', null, array('class' => 'form-control','required'=>'required','maxlength'=>'50')) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('sub_type', __('Account Type'), ['class' => 'form-label']) }}
            {{ Form::select('sub_type', $account_type, null, ['class' => 'form-control select', 'required' => 'required']) }}
        </div>

        @php
            $isSubAccount = ($chartOfAccount->parent > 0);
            $actualParentId = $isSubAccount ? optional($chartOfAccount->parentAccount)->account : null;
        @endphp

        <div class="col-md-2">
            <div class="form-group">
                {{Form::label('is_enabled',__('Is Enabled'),array('class'=>'form-label')) }}
                <div class="form-check form-switch">
                    <input type="checkbox" class="form-check-input" name="is_enabled" id="is_enabled" {{$chartOfAccount->is_enabled==1?'checked':''}}>
                    <label class="custom-control-label form-check-label" for="is_enabled"></label>
                </div>
            </div>
        </div>

        <div class="col-md-4 mt-4 acc_check {{ $chartOfAccount->sub_type ? '' : 'd-none' }}">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="account" name="is_sub_account" {{ $isSubAccount ? 'checked' : '' }}>
                <label class="form-check-label" for="account">{{__('Make this a sub-account')}}</label>
            </div>
        </div>

        <div class="form-group col-md-6 acc_type {{ $isSubAccount ? '' : 'd-none' }}">
            {{ Form::label('parent', __('Parent Account'), ['class' => 'form-label']) }}
            <select class="form-control select custom-select" name="parent" id="parent" >
                <option value="0">{{ __('Select Parent Account') }}</option>
                @foreach($parentAccounts as $pid => $pname)
                    <option value="{{ $pid }}" {{ $pid == $actualParentId ? 'selected' : '' }}>{{ $pname }}</option>
                @endforeach
            </select>
        </div>


        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
            {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'2']) !!}
        </div>

    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Update')}}" class="btn  btn-primary">
</div>
{{ Form::close() }}
