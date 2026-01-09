@extends('layouts.admin')
@section('page-title')
{{__('Edit Registration')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Edit Registration')}}</li>
@endsection
@section('content')
<div class="card mt-6 p-4">
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    {!! Form::model($student, ['route' => ['registration.update', $student->id], 'method' => 'PUT']) !!}
    {!! csrf_field() !!}
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('regdate', __('Reg Date.'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::date('regdate', $student->regdate, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
            <div class="col-md-6">
                {{ Form::label('regno', __('Reg No.'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::text('regno', $student->regno, ['class' => 'form-control', 'placeholder' => __('Registration No.'), 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('stname', __('Student Name'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::text('stname', $student->stname, ['class' => 'form-control', 'placeholder' => __('Student name'), 'required' => 'required']) }}
            </div>
            <div class="col-md-6">
                {{ Form::label('dob', __('D.O.B'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::date('dob', $student->dob, ['class' => 'form-control','id'=>'dob', 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('religion', __('Religion'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::text('religion', $student->religion, ['class' => 'form-control', 'placeholder' => __('Religion'), 'required' => 'required']) }}
            </div>
            <div class="col-md-6">
                {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}<span style="color: red"> *</span>
                {!! Form::select('gender', ['' => 'Select Gender', 'male' => 'Male', 'female' => 'Female'],
                $student->gender,
                ['class' => 'form-control', 'required' => 'required']) !!}
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('prevschool', __('Previos School'), ['class' => 'form-label']) }}<span
                    style="color: red">
                    *</span>
                {{ Form::text('prevschool', $student->prevschool, ['class' => 'form-control', 'placeholder' => __('Previos School'), 'required' => 'required']) }}
            </div>
            <div class="col-md-6">
                {{ Form::label('prevclass', __('Previos Class'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::text('prevclass', $student->prevclass, ['class' => 'form-control', 'placeholder' => __('Previos Class'), 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('fathername', __('Father Name'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::text('fathername', $student->fathername, ['class' => 'form-control','id' => 'fathername', 'required' => 'required']) }}
            </div>
            <div class="col-md-6">
                {!! Form::label('fathercnic', __('Father CNIC'), ['class' => 'form-label']) !!}<span style="color: red">
                    *</span>
                {!! Form::text('fathercnic', $student->fathercnic, ['class' => 'form-control',
                'required'
                => 'required', 'id' => 'fathercnic']) !!}
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('fatherphone', __('Phone'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::text('fatherphone', $student->fatherphone, ['class' => 'form-control',  'required' => 'required']) }}
            </div>
            <div class="col-md-6">
                {{ Form::label('fathercell', __('Cell'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::text('fathercell', $student->fathercell, ['class' => 'form-control',  'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('fatherprofession', __('Father Profession'), ['class' => 'form-label']) }}<span
                    style="color: red">
                    *</span>
                {{ Form::text('fatherprofession', $student->fatherprofession, ['class' => 'form-control',  'required' => 'required']) }}
            </div>
            <div class="col-md-6">
                {{ Form::label('city', __('City'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::text('city', $student->city, ['class' => 'form-control',  'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('mothername', __('Mother Name'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::text('mothername', $student->mothername, ['class' => 'form-control',  'required' => 'required']) }}
            </div>
            <div class="col-md-6">
                {!! Form::label('mothercnic', __('Mother CNIC'), ['class' => 'form-label']) !!}<span style="color: red">
                    *</span>
                {!! Form::text('mothercnic', $student->mothercnic, ['class' => 'form-control',
                'required'
                => 'required', 'id' => 'mothercnic']) !!}
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('motherprofession', __('Mother Profession'), ['class' => 'form-label']) }}<span
                    style="color: red">
                    *</span>
                {{ Form::text('motherprofession', $student->motherprofession, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
            <div class="col-md-6">
                {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::email('email', $student->email, ['class' => 'form-control', 'placeholder' => __('Email'), 'required' => 'required']) }}
            </div>
        </div>
    </div>

    <div class="form-group">
        <div class="row">
            <div class="col-md-12">
                {{ Form::label('address', __('Address'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::text('address', $student->address, ['class' => 'form-control', 'placeholder' => __('Address'), 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('branchname', __('Branch Name'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {!! Form::select('branchname', ['PWD Branch Islamabad' => 'PWD Branch Islamabad'], $student->branchname,
                ['class' => 'form-control', 'required' => 'required']) !!}
            </div>
            <div class="col-md-6">
                {{ Form::label('classname', __('Class Name'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {!! Form::select('classname', ['' => 'Select Class'] + $classes->toArray(), $student->classname,
                ['class' => 'form-control', 'required' => 'required']) !!}
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-6">
                {{ Form::label('session', __('Session'), ['class' => 'form-label']) }}
                <span style="color: red">*</span>
                {!! Form::select('session', ['' => 'Select Session'] + $session->toArray(), $student->session,
                ['class' => 'form-control', 'required' => 'required', 'id' => 'session']) !!}
            </div>

            <div class="col-md-6">
                {{ Form::label('registrationfee', __('Registration Fee'), ['class' => 'form-label']) }}
                {{ Form::text('registrationfee', $student->registrationfee, ['class' => 'form-control', 'placeholder' => __('Fee will be auto saved'), 'disabled' => 'disabled']) }}
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-12">
                {!! Form::label('remarks', __('Remarks'), ['class' => 'form-label']) !!}
                {!! Form::textarea('remarks', $student->remarks, ['class' => 'form-control', 'rows' => 5]) !!}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn  btn-outline-light" data-bs-dismiss="modal">
        <input type="submit" value="{{__('Update')}}" class="btn  btn-outline-primary">
    </div>    
    {!! Form::close() !!}
</div>
<script>
function formatCNIC(input) {
    var value = input.value.replace(/\D/g, '');
    if (value.length > 13) {
        value = value.substring(0, 13);
    }
    if (value.length > 5) {
        value = value.substring(0, 5) + '-' + value.substring(5);
    }
    if (value.length > 13) {
        value = value.substring(0, 13) + '-' + value.substring(13);
    }
    input.value = value;
}

document.getElementById('fathercnic').addEventListener('keyup', function() {
    formatCNIC(this);
});

document.getElementById('mothercnic').addEventListener('keyup', function() {
    formatCNIC(this);
});
</script>
<script>
    document.getElementById('session').value = '{{ $student->session }}';
</script>



@endsection