@extends('layouts.admin')
@section('page-title')
    {{ __('Student Bulk Update') }}
@endsection
@push('script-page')
    <script>
        $(document).on('change', '#branch', function() {
            let branch = $(this).val();
            $.ajax({
                url: "{{ route('branch.class') }}",
                type: "POST",
                data: {
                    branch_id: branch,
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(result) {
                    var $classSelect = $('#class_select');
                    if ($classSelect[0] && $classSelect[0].customSelectInstance) {
                        $classSelect[0].customSelectInstance.destroy();
                        delete $classSelect[0].customSelectInstance;
                    }
                    if ($classSelect.next('.custom-select-wrapper').length) {
                        $classSelect.next('.custom-select-wrapper').remove();
                    }
                    $classSelect.removeClass('custom-select');
                    $classSelect.empty();
                    $classSelect.append($('<option>', { value: 'all', text: 'All Classes' }));
                    for (var j = 0; j < result.length; j++) {
                        var cls = result[j];
                        $classSelect.append($('<option>', { value: cls.id, text: cls.name }));
                    }
                    $classSelect.addClass('custom-select');
                    $classSelect.show();
                    if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                        window.CustomSelect.create($classSelect[0]);
                    }
                }
            });
        });
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('registration.index') }}">{{ __('Registration') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Bulk Update') }}</li>
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        {{ __('Download the sample file with current student data for your branch. Edit the editable fields in Excel, then upload the same file. Student Name and Roll No are used for matching and cannot be changed. Fields like Class, Branch, Session are restricted from editing.') }}
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Download Sample') }}</h5>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => 'student.import.sample', 'method' => 'GET', 'target' => '_blank']) }}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('branch', __('Branch'), ['class' => 'form-label']) }}
                                {{ Form::select('branch', $branches, request('branch'), ['class' => 'form-control select', 'id' => 'branch']) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('class_id', __('Class'), ['class' => 'form-label']) }}
                                {{ Form::select('class_id', $classes, request('class_id'), ['class' => 'form-control select', 'id' => 'class_select']) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('session_id', __('Session'), ['class' => 'form-label']) }}
                                {{ Form::select('session_id', $sessions, request('session_id'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                {{ Form::select('status', $statuses, request('status'), ['class' => 'form-control select']) }}
                            </div>
                        </div>
                        <div class="col-md-12 mt-3">
                            {{ Form::submit(__('Download Sample'), ['class' => 'btn btn-primary']) }}
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>

            @if (session('warning'))
                <div class="alert alert-warning">
                    {{ session('warning') }}
                </div>
            @endif

            @if (session('import_errors'))
                <div class="alert alert-danger">
                    <strong>{{ __('Skipped / Error Details:') }}</strong>
                    <ul class="mb-0 mt-1">
                        @foreach (session('import_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5>{{ __('Upload Updated File') }}</h5>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => 'student.import.store', 'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {{ Form::label('file', __('Excel File'), ['class' => 'form-label']) }}
                                {{ Form::file('file', ['class' => 'form-control', 'required' => 'required', 'accept' => '.xlsx,.xls,.csv']) }}
                                <small class="form-text text-muted">{{ __('Accepted formats: xlsx, xls, csv') }}</small>
                            </div>
                        </div>
                        <div class="col-md-12 mt-3">
                            {{ Form::submit(__('Update Students'), ['class' => 'btn btn-primary']) }}
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
