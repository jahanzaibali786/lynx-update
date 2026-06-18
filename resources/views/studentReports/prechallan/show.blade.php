{!! Form::open([
    'route' => ['prechallan.update', $report->id],
    'method' => 'POST',
    'enctype' => 'multipart/form-data',
]) !!}
{!! csrf_field() !!}
<div class="modal-body">
    <div class="row">
        <div class="form-group row">
            <div class="col-6">
                {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('branches', $branches, $report->branch_id, ['class' => 'form-control', 'required' => 'required', 'disabled' => 'disabled']) }}
            </div>
            <div class="col-6">
                {{ Form::label('month', __('Month'), ['class' => 'form-label']) }}<span style="color: red">*</span>
                {!! Form::month('month', $report->month, [
                    'class' => 'form-control',
                    'required' => 'required',
                    'disabled' => 'disabled',
                ]) !!}
            </div>
            {{-- //only company user can re-upload the file --}}
            @if (Auth::user()->type == 'company' || ($report->status != 'approved' && $report->status != 'sent_for_approval'))
                <div class="col-6 mt-1">
                    {{ Form::label('file', __('Upload File'), ['class' => 'form-label']) }}<span
                        style="color: red">*</span>
                    {{ Form::file('file', ['class' => 'form-control', 'accept' => '.xlsx,.xls']) }}
                </div>
            @else
                <div class="col-6 mt-1">
                    {{ Form::label('file', __('Upload File'), ['class' => 'form-label']) }}<span
                        style="color: red">*</span>
                    {{ Form::file('file', ['class' => 'form-control', 'accept' => '.xlsx,.xls', 'disabled' => 'disabled']) }}
                </div>
            @endif
            {{-- /./upload ho dfile  --}}
            @if (Auth::user()->type == 'company')
                <div class="col-6 mt-1">
                    {{ Form::label('ho_file', __('Upload HO File'), ['class' => 'form-label']) }}
                    {{ Form::file('ho_file', ['class' => 'form-control', 'accept' => '.xlsx,.xls']) }}
                </div>
            @else
                <div class="col-6 mt-1">
                    {{ Form::label('ho_file', __('Upload HO File'), ['class' => 'form-label']) }}
                    {{ Form::file('ho_file', ['class' => 'form-control', 'accept' => '.xlsx,.xls', 'disabled' => 'disabled']) }}
                </div>
            @endif
            {{-- //textarea for remarks --}}
            <div class="col-12 mt-1">
                {{ Form::label('remarks', __('Remarks'), ['class' => 'form-label']) }}
                {{ Form::textarea('remarks', $report->remarks, ['class' => 'form-control', 'rows' => 3]) }}
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn  btn-outline-primary">
</div>

{!! Form::close() !!}
