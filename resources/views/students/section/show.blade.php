{{ Form::open(['route' => ['section.change', $studentEnrollments->regId]]) }}
<style>
    .section-history-scroll thead th {
        position: sticky;
        top: 0;
        background: #fff;
        z-index: 2;
    }

    .section-history-scroll {
        max-height: 250px;
        /* adjust height */
        overflow-y: auto;
    }
</style>
<div class="modal-body">

    <!-- Date -->
    <div class="row">
        <div class="form-group col-md-6 col-sm-12 col-lg-6">
            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }} <span style="color:red">*</span>
            {{ Form::date('date', now(), ['class' => 'form-control', 'required']) }}
        </div>
        <div class="form-group col-md-6 col-sm-12 col-lg-6">
            {{ Form::label('section', __('Section'), ['class' => 'form-label']) }} <span style="color:red">*</span>

            {{ Form::select('section', $sections->pluck('name', 'id'), $studentEnrollments->section_id, [
                'class' => 'form-control',
                'placeholder' => 'Select Section',
                'required',
            ]) }}
        </div>
    </div>

</div>

<div class="modal-footer">
    <input type="submit" value="{{ __('Change Section') }}" class="btn btn-outline-primary">
</div>

{{ Form::close() }}

<div class="p-3">

    <div class="card-header">
        <h4>{{ __('Section History') }}</h4>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <div>
            <strong>{{ __('Roll No.') }}:</strong>
            {{ @$studentEnrollments->StudentRegistration->roll_no }}
        </div>

        <div>
            <strong>{{ __('Student Name') }}:</strong>
            {{ @$studentEnrollments->StudentRegistration->stdname }}
        </div>
    </div>

    @if ($sectionHistory->isEmpty())
        <p class="text-muted mt-3">{{ __('No section history available.') }}</p>
    @else
        <!-- 🔥 SCROLLABLE TABLE WRAPPER -->
        <div class="table-responsive section-history-scroll">
            <table class="table table-bordered mb-0">
                <thead class="table_heads">
                    <tr>
                        <th>{{ __('Section Name') }}</th>
                        <th>{{ __('Changed At') }}</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($sectionHistory as $history)
                        <tr>
                            <td>{{ @$history->section->name }}</td>
                            <td>
                                {{ $history->date ? \Carbon\Carbon::parse($history->date)->format('d-M-Y') : '-' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

    @endif
</div>
