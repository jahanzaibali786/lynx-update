{!! Form::open(['route' => 'prechallan.store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
{!! csrf_field() !!}
<div class="modal-body">
    <div class="row">
        <div class="form-group row">
            <div class="col-6">
                {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('branches', $branches, null, ['class' => 'form-control', 'required' => 'required']) }}
            </div>
            @php
                $nextMonth = \Carbon\Carbon::now()->addMonth()->format('Y-m');
            @endphp


            <div class="col-6">
                {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}<span style="color: red">*</span>
                {!! Form::month('month', $nextMonth, [
                    'class' => 'form-control',
                    'required' => 'required',
                    'min' => now()->format('Y-m'),
                    'max' => $nextMonth,
                ]) !!} </div>
            {{-- //upload file only excel files --}}
            <div class="col-12 mt-1">
                {{ Form::label('file', __('Upload File'), ['class' => 'form-label']) }}<span style="color: red">*</span>
                {{ Form::file('file', ['class' => 'form-control', 'required' => 'required', 'accept' => '.xlsx,.xls']) }}
            </div>
            {{-- //textarea for remarks --}}
            <div class="col-12 mt-1">
                {{ Form::label('remarks', __('Remarks'), ['class' => 'form-label']) }}
                {{ Form::textarea('remarks', null, ['class' => 'form-control', 'rows' => 3]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn  btn-outline-primary">
</div>

{!! Form::close() !!}
