@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Student Promotions') }}
@endsection

@push('script-page')
    <script>
        function setPromotionType(type) {
            $('#promotion_type').val(type);
            $('.promotion-filter-layout').toggle(type === 'promotion');
            $('.branch-promotion-filter-layout').toggle(type === 'branch_promotion');
            $('.promotion-filter-layout :input').prop('disabled', type !== 'promotion');
            $('.branch-promotion-filter-layout :input').prop('disabled', type !== 'branch_promotion');
        }

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
            var form = document.getElementById('concession_submit');
            if (form.checkValidity()) {
                form.submit();
            } else {
                form.reportValidity();
            }
        }

        function collectFeePercentages() {
            let feePercentages = [];
            $('.fee-revision-head:checked').each(function() {
                let row = $(this).closest('tr');
                feePercentages.push({
                    head_id: $(this).data('head-id'),
                    percentage: row.find('.fee-percentage').val() || 0
                });
            });
            return feePercentages;
        }

        function activeFilterValue(name) {
            return $('.promotion-filter-layout:visible [name="' + name + '"], .branch-promotion-filter-layout:visible [name="' + name + '"]').first().val();
        }

        function submitChecked() {
            var form = document.getElementById('concession_submit');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            let studentData = [];
            let checkedBoxes = $('.student-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Please check entries to promote.');
                return;
            }

            let invalidSection = false;
            checkedBoxes.each(function() {
                let row = $(this).closest('tr');
                let sectionTo = row.find('.section-to-select').val();
                if (!sectionTo) {
                    row.find('.section-to-select').focus();
                    invalidSection = true;
                    return false;
                }

                studentData.push({
                    student_id: $(this).data('student-id'),
                    enrollId: $(this).data('enroll-id'),
                    section_to: sectionTo
                });
            });

            if (invalidSection) {
                show_toastr('error', 'Please select Section To for all selected students.', 'error');
                return;
            }

            let filters = {
                promotion_type: $('#promotion_type').val(),
                branches: activeFilterValue('branches'),
                branch_to: activeFilterValue('branch_to'),
                class_id: activeFilterValue('class_id'),
                class_to: activeFilterValue('class_to'),
                section_from: activeFilterValue('section_from'),
                session_from_id: activeFilterValue('session_from_id'),
                session_id: activeFilterValue('session_id')
            };
            let feePercentages = collectFeePercentages();
            if (feePercentages.length === 0) {
                show_toastr('error', 'Please check at least one fee head for revision.', 'error');
                return;
            }

            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                url: "{{ route('student-promotion.store') }}",
                type: 'POST',
                data: {
                    studentData: studentData,
                    filters: filters,
                    feePercentages: feePercentages
                },
                success: function(response) {
                    if (response.status === 'success') {
                        show_toastr('success', response.message, 'success');
                        window.location.reload();
                    } else {
                        show_toastr('error', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An unexpected error occurred';
                    show_toastr('error', errorMessage, 'error');
                    console.log(xhr.responseText);
                }
            });
        }

        $(document).ready(function() {
            setPromotionType($('#promotion_type').val() || 'promotion');

            $('#promotion-tab').on('click', function(e) {
                e.preventDefault();
                setPromotionType('promotion');
                $(this).addClass('active');
                $('#branch-promotion-tab').removeClass('active');
            });

            $('#branch-promotion-tab').on('click', function(e) {
                e.preventDefault();
                setPromotionType('branch_promotion');
                $(this).addClass('active');
                $('#promotion-tab').removeClass('active');
            });

            $(document).on('change', '[data-role="branch-from"]', function() {
                let layout = $(this).closest('.promotion-filter-layout, .branch-promotion-filter-layout');
                loadBranchClasses(this.value, layout.find('[name="class_id"]'));
                if (layout.hasClass('promotion-filter-layout')) {
                    loadBranchClasses(this.value, layout.find('[name="class_to"]'));
                }
            });

            $(document).on('change', '[data-role="branch-to"]', function() {
                let layout = $(this).closest('.promotion-filter-layout, .branch-promotion-filter-layout');
                loadBranchClasses(this.value, layout.find('[name="class_to"]'));
            });

            $(document).on('change', '[data-role="class-from"]', function() {
                let layout = $(this).closest('.promotion-filter-layout, .branch-promotion-filter-layout');
                loadClassSections(this.value, layout.find('[name="section_from"]'));
            });

            $(document).on('change', '[data-role="class-to"]', function() {
                let layout = $(this).closest('.promotion-filter-layout, .branch-promotion-filter-layout');
                loadClassSections(this.value, layout.find('[name="section_to"]'));
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
    <li class="breadcrumb-item">{{ __('Student Promotions') }}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="card mt-2">
                <div class="card-body">
                    <ul class="nav nav-tabs mb-3">
                        <li class="nav-item">
                            <a href="#" id="promotion-tab" class="nav-link {{ $promotionType !== 'branch_promotion' ? 'active' : '' }}">Promotion</a>
                        </li>
                        <li class="nav-item">
                            <a href="#" id="branch-promotion-tab" class="nav-link {{ $promotionType === 'branch_promotion' ? 'active' : '' }}">Branch Promotion</a>
                        </li>
                    </ul>

                    {{ Form::open(['route' => ['student-promotion.index'], 'method' => 'GET', 'id' => 'concession_submit']) }}
                    <input type="hidden" name="promotion_type" id="promotion_type" value="{{ $promotionType }}">

                    <style>
                        .promotion-filter-row {
                            display: grid;
                            grid-template-columns: repeat(4, minmax(0, 1fr));
                            gap: 14px 20px;
                            align-items: end;
                            margin-bottom: 14px;
                        }
                        .promotion-filter-row.with-actions {
                            grid-template-columns: repeat(4, minmax(0, 1fr));
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
                            .promotion-filter-row.with-actions,
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
                            .promotion-filter-row.with-actions,
                            .promotion-filter-row.branch-with-actions {
                                grid-template-columns: 1fr;
                            }
                        }
                    </style>

                    <div class="promotion-filter-layout">
                        <div class="promotion-filter-row">
                            <div>
                                <div class="btn-box">
                                    {{ Form::label('session_from_id', __('Session From'), ['class' => 'form-label']) }}
                                    {{ Form::select('session_from_id', $session, request('session_from_id'), ['class' => 'form-control select']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('session_id', __('Session To'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                    {{ Form::select('session_id', $session, request('session_id'), ['class' => 'form-control select', 'required' => 'required']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branch From'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request('branches'), ['class' => 'form-control select', 'required' => 'required', 'data-role' => 'branch-from']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('class_id', __('Class From'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                    {{ Form::select('class_id', $classesFrom, request('class_id'), ['class' => 'form-control select', 'required' => 'required', 'data-role' => 'class-from']) }}
                                </div>
                            </div>
                        </div>

                        <div class="promotion-filter-row with-actions">
                            <div>
                                <div class="btn-box">
                                    {{ Form::label('class_to', __('Class To'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                    {{ Form::select('class_to', $classesTo, request('class_to'), ['class' => 'form-control select', 'required' => 'required', 'data-role' => 'class-to']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('section_from', __('Section From'), ['class' => 'form-label']) }}
                                    {{ Form::select('section_from', $sectionsFrom, request('section_from'), ['class' => 'form-control select']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('section_to', __('Section To'), ['class' => 'form-label']) }}
                                    {{ Form::select('section_to', $sectionsTo, request('section_to'), ['class' => 'form-control select', 'data-role' => 'section-to-filter']) }}
                                </div>
                            </div>

                            <div class="promotion-filter-actions">
                                <a href="#" class="btn btn-sm btn-outline-primary" onclick="Checked(event)" title="Search data">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('student-promotion.index') }}" class="btn btn-sm btn-outline-danger" title="Clear Filter">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <a title="Selected Students Promote" class="btn btn-sm btn-outline-warning" onclick="submitChecked()">
                                    <span class="btn-inner--icon">Apply Promotion</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="branch-promotion-filter-layout">
                        <div class="promotion-filter-row">
                            <div>
                                <div class="btn-box">
                                    {{ Form::label('session_from_id', __('Session From'), ['class' => 'form-label']) }}
                                    {{ Form::select('session_from_id', $session, request('session_from_id'), ['class' => 'form-control select']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('session_id', __('Session To'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                    {{ Form::select('session_id', $session, request('session_id'), ['class' => 'form-control select', 'required' => 'required']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branch From'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request('branches'), ['class' => 'form-control select', 'required' => 'required', 'data-role' => 'branch-from']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('branch_to', __('Branch To'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                    {{ Form::select('branch_to', $branches, request('branch_to'), ['class' => 'form-control select', 'required' => 'required', 'data-role' => 'branch-to']) }}
                                </div>
                            </div>
                        </div>

                        <div class="promotion-filter-row with-actions branch-with-actions">
                            <div>
                                <div class="btn-box">
                                    {{ Form::label('class_id', __('Class From'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                    {{ Form::select('class_id', $classesFrom, request('class_id'), ['class' => 'form-control select', 'required' => 'required', 'data-role' => 'class-from']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('class_to', __('Class To'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                                    {{ Form::select('class_to', $classesTo, request('class_to'), ['class' => 'form-control select', 'required' => 'required', 'data-role' => 'class-to']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('section_from', __('Section From'), ['class' => 'form-label']) }}
                                    {{ Form::select('section_from', $sectionsFrom, request('section_from'), ['class' => 'form-control select']) }}
                                </div>
                            </div>

                            <div>
                                <div class="btn-box">
                                    {{ Form::label('section_to', __('Section To'), ['class' => 'form-label']) }}
                                    {{ Form::select('section_to', $sectionsTo, request('section_to'), ['class' => 'form-control select', 'data-role' => 'section-to-filter']) }}
                                </div>
                            </div>

                            <div class="promotion-filter-actions">
                                <a href="#" class="btn btn-sm btn-outline-primary" onclick="Checked(event)" title="Search data">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('student-promotion.index') }}" class="btn btn-sm btn-outline-danger" title="Clear Filter">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <a title="Selected Students Promote" class="btn btn-sm btn-outline-warning" onclick="submitChecked()">
                                    <span class="btn-inner--icon">Apply Promotion</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4">
            <div>
                <span style="font-size: large; text-align: center;">Fee Revision Percentages</span>
                <table class="">
                    <thead class="table_heads">
                        <tr>
                            <th>Sr No</th>
                            <th></th>
                            <th>Fee Head</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($feeHeads as $head)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <input type="checkbox" class="fee-revision-head" data-head-id="{{ $head->id }}">
                                </td>
                                <td>{{ $head->fee_head }}</td>
                                <td>
                                    <input type="number" step="0.01" style="width:90px;" value="0" class="fee-percentage" data-head-id="{{ $head->id }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-xl-8">
            <div>
                <table class="">
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
                                <td>{{ @$student->enrollId }}</td>
                                <td>{{ @$student->StudentRegistration->stdname }}</td>
                                <td>{{ @$student->StudentRegistration->fathername }}</td>
                                <td>{{ @$student->class->name }}</td>
                                <td>{{ @$student->section->name }}</td>
                                <td>
                                    {!! Form::select('section_to', $sectionsTo, request('section_to'), [
                                        'class' => 'form-control section-to-select',
                                        'required' => 'required',
                                    ]) !!}
                                </td>
                                <td>
                                    <input type="checkbox" class="student-checkbox" data-student-id="{{ @$student->id }}" data-enroll-id="{{ @$student->enrollId }}">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
