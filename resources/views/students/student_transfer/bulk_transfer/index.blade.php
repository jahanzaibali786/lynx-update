@extends('layouts.admin')
@section('page-title')
    {{ __('Bulk Student Transfer') }}
@endsection

@push('script-page')
    <script>
        function loadBranchClasses(branchId, targetSelector) {
            if (!branchId) {
                $(targetSelector).empty().append($('<option>', { value: '', text: 'Select Class' }));
                return;
            }

            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: "{{ route('branch.session_class') }}",
                type: "POST",
                data: { id: branchId },
                dataType: 'json',
                success: function(result) {
                    if (result.status !== 'success') {
                        return;
                    }

                    $(targetSelector).empty().append($('<option>', { value: '', text: 'Select Class' }));
                    for (let j = 0; j < result.class.length; j++) {
                        let cls = result.class[j];
                        $(targetSelector).append($('<option>', { value: cls.id, text: cls.name }));
                    }
                }
            });
        }

        function loadClassSections(classId, targetSelector) {
            if (!classId) {
                $(targetSelector).empty().append($('<option>', { value: '', text: 'Select Section' }));
                return;
            }

            $.ajax({
                url: '{{ route('class.section') }}',
                type: 'POST',
                data: {
                    class_id: classId,
                    _token: "{{ csrf_token() }}",
                },
                success: function(data) {
                    $(targetSelector).empty().append('<option value="">Select Section</option>');
                    for (let index = 0; index < data.length; index++) {
                        $(targetSelector).append('<option value="' + data[index].id + '">' + data[index].name + '</option>');
                    }
                }
            });
        }

        function Checked(e) {
            e.preventDefault();
            var form = document.getElementById('transfer_submit');
            if (form.checkValidity()) {
                form.submit();
            } else {
                form.reportValidity();
            }
        }

        function activeFilterValue(name) {
            return $('[name="' + name + '"]').val();
        }

        function submitChecked() {
            var form = document.getElementById('transfer_submit');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            let checkedBoxes = $('.student-checkbox:checked');
            if (checkedBoxes.length === 0) {
                show_toastr('error', 'Please select students to transfer.', 'error');
                return;
            }

            let invalidSection = false;
            let studentIds = [];
            
            checkedBoxes.each(function() {
                let row = $(this).closest('tr');
                let sectionTo = row.find('.section-to-select').val();
                if (!sectionTo) {
                    row.find('.section-to-select').focus();
                    invalidSection = true;
                    return false;
                }
                studentIds.push($(this).val());
            });

            if (invalidSection) {
                show_toastr('error', 'Please select Section To for all selected students.', 'error');
                return;
            }

            if (!confirm('{{ __('Are you sure you want to transfer the selected students?') }}')) {
                return;
            }

            // Create a form programmatically and submit it
            var submitForm = $('<form>', {
                'action': '{{ route('bulk-transfer.process') }}',
                'method': 'POST'
            }).append($('<input>', {
                'name': '_token',
                'value': '{{ csrf_token() }}',
                'type': 'hidden'
            }));

            // Add selected filters required for processing
            submitForm.append($('<input>', { name: 'session_to', value: $('[name="session_to"]').val(), type: 'hidden' }));
            submitForm.append($('<input>', { name: 'branch_to', value: $('[name="branch_to"]').val(), type: 'hidden' }));
            submitForm.append($('<input>', { name: 'class_to', value: $('[name="class_to"]').val(), type: 'hidden' }));
            submitForm.append($('<input>', { name: 'section_to', value: $('[name="section_to"]').val(), type: 'hidden' }));

            // Add student IDs
            studentIds.forEach(function(id) {
                submitForm.append($('<input>', { name: 'student_ids[]', value: id, type: 'hidden' }));
            });

            submitForm.appendTo('body').submit();
        }

        $(document).ready(function() {
            $(document).on('change', '[data-role="branch-from"]', function() {
                loadBranchClasses(this.value, $('[name="class_from"]'));
            });

            $(document).on('change', '[data-role="branch-to"]', function() {
                loadBranchClasses(this.value, $('[name="class_to"]'));
            });

            $(document).on('change', '[data-role="class-from"]', function() {
                loadClassSections(this.value, $('[name="section_from"]'));
            });

            $(document).on('change', '[data-role="class-to"]', function() {
                loadClassSections(this.value, $('[name="section_to"]'));
                loadClassSections(this.value, '.section-to-select');
            });

            $(document).on('change', '[data-role="section-to-filter"]', function() {
                if (this.value) {
                    $('.section-to-select').val(this.value);
                }
            });

            $('#select-all').on('change', function() {
                $('.student-checkbox').prop('checked', $(this).prop('checked'));
            });

            $('.student-checkbox').on('change', function() {
                $('#select-all').prop('checked', $('.student-checkbox:checked').length === $('.student-checkbox').length);
            });
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Bulk Student Transfer') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card mt-2">
                <div class="card-body">
                    {{ Form::open(['route' => ['bulk-transfer.index'], 'method' => 'GET', 'id' => 'transfer_submit']) }}
                    <input type="hidden" name="search" value="1">

                    <style>
                        .promotion-filter-row {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 14px 20px;
                            align-items: end;
                            margin-bottom: 14px;
                        }
                        .promotion-filter-row.branch-with-actions {
                            grid-template-columns: repeat(5, minmax(0, 1fr));
                        }
                        .promotion-filter-actions {
                            display: flex;
                            grid-column: -1;
                            justify-content: flex-end;
                            align-items: center;
                            gap: 6px;
                            padding-bottom: 2px;
                            white-space: nowrap;
                        }
                        @media (max-width: 1199px) {
                            .promotion-filter-row,
                            .promotion-filter-row.branch-with-actions {
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                            }
                            .promotion-filter-actions {
                                grid-column: auto;
                                justify-content: flex-start;
                            }
                        }
                        @media (max-width: 575px) {
                            .promotion-filter-row,
                            .promotion-filter-row.branch-with-actions {
                                grid-template-columns: 1fr;
                            }
                        }
                    </style>

                    <div class="branch-promotion-filter-layout">
                        <div class="promotion-filter-row">
                            <div>
                                <div class="btn-box">
                                    {{ Form::label('session_from', __('Session From'), ['class' => 'form-label']) }}
                                    {{ Form::select('session_from', $sessions, request('session_from'), ['class' => 'form-control select', 'placeholder' => 'Select Session']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('session_to', __('Session To'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                    {{ Form::select('session_to', $sessions, request('session_to'), ['class' => 'form-control select', 'required' => 'required', 'placeholder' => 'Select Session']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('branch_from', __('Branch From'), ['class' => 'form-label']) }}
                                    {{ Form::select('branch_from', $branches, request('branch_from'), ['class' => 'form-control select', 'required' => 'required', 'data-role' => 'branch-from', 'placeholder' => 'Select Branch']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('branch_to', __('Branch To'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                    {{ Form::select('branch_to', $branches, request('branch_to'), ['class' => 'form-control select', 'required' => 'required', 'data-role' => 'branch-to', 'placeholder' => 'Select Branch']) }}
                                </div>
                            </div>
                        </div>

                        <div class="promotion-filter-row branch-with-actions">
                            <div>
                                <div class="btn-box">
                                    {{ Form::label('class_from', __('Class From'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                    {{ Form::select('class_from', $classesFrom, request('class_from'), ['class' => 'form-control select', 'required' => 'required', 'data-role' => 'class-from', 'placeholder' => 'Select Class']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('class_to', __('Class To'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                    {{ Form::select('class_to', $classesTo, request('class_to'), ['class' => 'form-control select', 'required' => 'required', 'data-role' => 'class-to', 'placeholder' => 'Select Class']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('section_from', __('Section From'), ['class' => 'form-label']) }}
                                    {{ Form::select('section_from', $sectionsFrom, request('section_from'), ['class' => 'form-control select', 'placeholder' => 'Select Section']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('section_to', __('Section To'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                    {{ Form::select('section_to', $sectionsTo, request('section_to'), ['class' => 'form-control select', 'data-role' => 'section-to-filter', 'required' => 'required', 'placeholder' => 'Select Section']) }}
                                </div>
                            </div>

                            <div class="promotion-filter-actions">
                                <a href="#" class="btn btn-sm btn-outline-primary" onclick="Checked(event)" title="Search data">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('bulk-transfer.index') }}" class="btn btn-sm btn-outline-danger" title="Clear Filter">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <a title="Transfer Selected Students" class="btn btn-sm btn-outline-warning" onclick="submitChecked()">
                                    <span class="btn-inner--icon">Transfer Students</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    @if(request()->has('search'))
    <div class="row">
        <div class="col-xl-12">
            <div>
                <table class="table">
                    <thead class="table_heads">
                        <tr>
                            <th>Sr. No</th>
                            <th>Roll No</th>
                            <th style="width: 70px">Name</th>
                            <th>Father Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Section To</th>
                            <th><input type="checkbox" id="select-all"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ optional($student->StudentRegistration)->roll_no }}</td>
                                <td>{{ optional($student->StudentRegistration)->student_name ?? optional($student->StudentRegistration)->stdname }}</td>
                                <td>{{ optional($student->StudentRegistration)->father_name ?? optional($student->StudentRegistration)->fathername }}</td>
                                <td>{{ optional($student->class)->name }}</td>
                                <td>{{ optional($student->section)->name }}</td>
                                <td>
                                    {!! Form::select('section_to_row', $sectionsTo, request('section_to'), [
                                        'class' => 'form-control section-to-select',
                                        'required' => 'required',
                                    ]) !!}
                                </td>
                                <td>
                                    <input type="checkbox" class="student-checkbox" name="student_ids[]" value="{{ $student->id }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
@endsection
