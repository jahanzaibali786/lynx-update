@extends('layouts.admin')

@section('page-title')
    {{ __('Bulk Section Change') }}
@endsection

@push('script-page')
    <script>
        function branchcustomer(id) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('branch.session_class') }}",
                type: "POST",
                data: {
                    id: id
                },
                success: function(result) {
                    $('#class_id').empty().append('<option value="">Select Class</option>');

                    result.class.forEach(function(cls) {
                        $('#class_id').append(`<option value="${cls.id}">${cls.name}</option>`);
                    });
                }
            });
        }

        function updateSections() {

            let studentData = [];
            if ($('.student-checkbox:checked').length === 0) {
                alert('Please select at least one student');
                return;
            }
            $('.student-checkbox:checked').each(function() {
                let row = $(this).closest('tr');

                let enrollId = $(this).data('enroll-id');
                let sectionTo = row.find('.section-select').val();
                let date = $('#date').val();
                if (!sectionTo) {
                    alert('Please select a section for all selected students');
                    studentData = [];
                    return false; // break out of the loop
                }

                studentData.push({
                    enroll_id: enrollId,
                    section_id: sectionTo,
                    date: date
                });
            });

            if (studentData.length === 0) {
                alert('Please select students and sections');
                return;
            }

            $.ajax({
                url: "{{ route('section.bulkupdate') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    studentData: studentData
                },
                success: function(res) {
                    alert(res.message);
                    location.reload();
                },
                error: function() {
                    alert('Something went wrong');
                }
            });
        }

        $(document).ready(function() {

            $('#select-all').on('change', function() {
                $('.student-checkbox').prop('checked', this.checked);
            });

            $('.student-checkbox').on('change', function() {
                $('#select-all').prop(
                    'checked',
                    $('.student-checkbox:checked').length === $('.student-checkbox').length
                );
            });

            // Select/Deselect all
            $('#select-all').on('change', function() {
                $('.student-checkbox').prop('checked', this.checked);
            });

            $('.student-checkbox').on('change', function() {
                $('#select-all').prop(
                    'checked',
                    $('.student-checkbox:checked').length === $('.student-checkbox').length
                );
            });

            // 🔥 BULK SECTION APPLY
            $('#bulk-section').on('change', function() {                
                let selectedSection = $(this).val();                
                if (!selectedSection) return;

                // apply only to checked rows
                $('.student-checkbox:checked').each(function() {
                    let row = $(this).closest('tr');
                    
                    row.find('.section-select').val(selectedSection);
                });

            });

        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Bulk Section Change') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">

                    {{-- FILTER FORM --}}
                    {{ Form::open(['route' => ['section.bulkindex'], 'method' => 'GET']) }}

                    <div class="row">

                        <div class="col-md-3">
                            {{ Form::label('branches', __('Branch')) }}
                            {{ Form::select('branches', $branches, request('branches'), [
                                'class' => 'form-control',
                                'onchange' => 'branchcustomer(this.value)',
                            ]) }}
                        </div>

                        <div class="col-md-3">
                            {{ Form::label('class_id', __('Class')) }}
                            {{ Form::select('class_id', $classes, request('class_id'), [
                                'class' => 'form-control',
                                'id' => 'class_id',
                                'required',
                            ]) }}
                        </div>
                        {{-- //date filter  --}}
                        <div class="col-md-3">
                            {{ Form::label('date', __('Date')) }}
                            {{ Form::date('date', request('date') ?? now(), [
                                'class' => 'form-control',
                                'id' => 'date',
                            ]) }}
                        </div>
                        <div class="col-md-3 mt-4">
                            <button class="btn btn-primary">Search</button>
                            <a href="{{ route('section.bulkindex') }}" class="btn btn-danger">Clear</a>
                        </div>

                    </div>

                    {{ Form::close() }}

                </div>
            </div>
        </div>
    </div>

    {{-- STUDENT TABLE --}}
    <div class="row mt-3">
        <div class="col-12">

            <div class="card">
                <div class="d-flex justify-content-end align-items-center gap-2 m-2">
                    {{-- Bulk Section Dropdown --}}
                    <div style="min-width:200px;">
                        {!! Form::select('bulk_section', $sections, null, [
                            'class' => 'form-control',
                            'id' => 'bulk-section',
                            'placeholder' => 'Bulk Change Section',
                        ]) !!}
                    </div>

                    <button class="btn btn-success" onclick="updateSections()">
                        Update Sections
                    </button>

                </div>
                <div class="card-body">

                    <table class="table table-bordered">
                        <thead class="table_heads">
                            <tr>
                                <th>#</th>
                                <th>Roll No</th>
                                <th>Name</th>
                                <th>Father</th>
                                <th>Class</th>
                                <th>Current Section</th>
                                <th>Change Section</th>
                                <th><input type="checkbox" id="select-all"></th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($students as $student)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $student->enrollId }}</td>
                                    <td>{{ @$student->StudentRegistration->stdname }}</td>
                                    <td>{{ @$student->StudentRegistration->fathername }}</td>
                                    <td>{{ @$student->class->name }}</td>
                                    <td>{{ @$student->section->name }}</td>

                                    <td>
                                        {!! Form::select('section_to', $sections, @$student->section_id, [
                                            'class' => 'form-control section-select',
                                        ]) !!}
                                    </td>

                                    <td>
                                        <input type="checkbox" class="student-checkbox"
                                            data-enroll-id="{{ $student->regId }}">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No students found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>



                </div>
            </div>

        </div>
    </div>
@endsection
