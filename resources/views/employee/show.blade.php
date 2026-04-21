@extends('layouts.admin')

@section('page-title')
    {{ __('Employee') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('employee.index') }}">{{ __('Employee') }}</a></li>
    <li class="breadcrumb-item">{{ $employeesId }}</li>
@endsection
@push('script-page')
    <script>
        $(document).on('change', '#start_date, #end_date', function() {
            var startDate = $('#start_date').val();
            var endDate = $('#end_date').val();

            if (startDate && endDate) {
                var start = new Date(startDate);
                var end = new Date(endDate);
                var timeDifference = end.getTime() - start.getTime();
                var dayDifference = timeDifference / (1000 * 3600 * 24) + 1;

                if (dayDifference > 0) {
                    $('#total_days').val(dayDifference);
                } else {
                    $('#total_days').val(0);
                }
            }
        });
    </script>
    <script>
        function branchcustomer(id) {
            var customer = $('#customerselect').val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('branch.session_class') }}",
                type: "POST",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(result) {
                    console.log(result);
                    if (result.status == 'success') {
                        $('#sessionselect').empty();
                        $('#sessionselect').append($('<option>', {
                            value: '',
                            text: 'Select Session'
                        }));
                        for (var i = 0; i < result.session.length; i++) {
                            var session = result.session[i];
                            $('#sessionselect').append($('<option>', {
                                value: session.id,
                                text: session.title
                            }));
                        }
                        $('#class_select').empty();
                        $('#class_select').append($('<option>', {
                            value: '',
                            text: 'Select Class'
                        }));

                        for (var j = 0; j < result.class.length; j++) {
                            var cls = result.class[j];
                            $('#class_select').append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                    }
                    if (result.status == 'error') {}

                }
            });
        }

        function classStudents(id) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('class.students') }}",
                type: "POST",
                data: {
                    class_id: id
                },
                dataType: 'json',
                success: function(result) {
                    console.log(result);
                    if (result.status == 'success') {
                        $('#student_select').empty();
                        $('#student_select').append($('<option>', {
                            value: '',
                            text: 'Select Student'
                        }));
                        for (var id in result.students) {
                            if (result.students.hasOwnProperty(id)) {
                                $('#student_select').append($('<option>', {
                                    value: id,
                                    text: result.students[id]
                                }));
                            }
                        }
                        $('#student_select').val('');
                    }
                }
            });
        }
        $(document).on('change', '#class_select', function() {
            var classId = $(this).val();
            if (classId) {
                classStudents(classId);
            }
        });
    </script>
    <script>
        document.getElementById('pro_date').addEventListener('keyup', function() {
            updateProbationEndDate();
        });
        document.getElementById('joining_date').addEventListener('change', function() {
            updateProbationEndDate();
        });

        function updateProbationEndDate() {
            // document.getElementById('pro_date').addEventListener('keyup', function() {
            var p_id = parseInt($('#pro_date').val());
            var currentDate = new Date($('#joining_date').val()); // Get current date
            if (!isNaN(currentDate.getTime())) {

                if (isNaN(p_id)) {
                    var currentDate = new Date($('#joining_date').val());
                    var formattedDate = currentDate.toISOString().slice(0, 10);
                    document.getElementById('pro_end_date').value = formattedDate;
                } else {

                    var futureDate = new Date(currentDate.setMonth(currentDate.getMonth() + p_id)); // Add months

                    // Format the future date as YYYY-MM-DD
                    var formattedDate = futureDate.toISOString().slice(0, 10);

                    // Set the value of the 'joining_date' input field
                    document.getElementById('pro_end_date').value = formattedDate;
                }
            }

            // });
        }
    </script>
    <script>
        // document.getElementById('profileImageInput').addEventListener('change', function(event) {
        //     const file = event.target.files[0];
        //     if (file) {
        //         const reader = new FileReader();
        //         reader.onload = function(e) {
        //             document.getElementById('profileImage').src = e.target.result;
        //         };
        //         reader.readAsDataURL(file);
        //     }
        // });
        document.getElementById('profileImageInput').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const maxSizeMB = 2;
                const maxSizeBytes = maxSizeMB * 1024 * 1024;

                if (file.size > maxSizeBytes) {
                    alert(
                        'The selected file exceeds the maximum allowed size of 2MB. Please choose a smaller image.'
                    );
                    event.target.value = '';
                } else {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('profileImage').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }
        });
    </script>
    <script>
        function editExperience(btn) {
            var organization = btn.getAttribute('data-organization');
            var designation = btn.getAttribute('data-designation');
            var from = btn.getAttribute('data-from');
            var to = btn.getAttribute('data-to');
            var reason = btn.getAttribute('data-reason');
            var id = btn.getAttribute('data-id');

            $('#organization').val(organization);
            $('#designation').val(designation);
            $('#from').val(from);
            $('#to').val(to);
            $('#reason').val(reason);
            $('#experience_id').val(id);
        }

        // Reset form when adding new experience
        function resetExperienceForm() {
            $('#exp-info-form')[0].reset();
            $('#experience_id').val('');
        }

        // Call reset function when the tab is shown or when clicking "Add New"
        $('#experience-tab').on('shown.bs.tab', function() {
            resetExperienceForm();
        });

        function editEducation(btn) {
            var institute = btn.getAttribute('data-institute');
            var degree = btn.getAttribute('data-degree');
            var title = btn.getAttribute('data-title');
            var subject = btn.getAttribute('data-subject');
            var adm_date = btn.getAttribute('data-adm_date');
            var pass_date = btn.getAttribute('data-pass_date');
            var grade = btn.getAttribute('data-grade');
            var reason = btn.getAttribute('data-reason');
            var id = btn.getAttribute('data-id');

            $('#institute_name').val(institute);
            $('#degree_level').val(degree);
            $('#degree_title').val(title);
            $('#subject').val(subject);
            $('#adm_date').val(adm_date);
            $('#passing_year').val(pass_date);
            $('#grade').val(grade);
            $('#reason_of_leaving').val(reason);
            $('#education_id').val(id);
            $('#edu-info-save-btn-label').text('Update');
        }

        // Reset form when adding new education
        function resetEducationForm() {
            $('#exp-edu-form')[0].reset();
            $('#education_id').val('');
            $('#edu-info-save-btn-label').text('Save');
        }

        // Call reset function when the tab is shown or when clicking "Add New"
        $('#education-tab').on('shown.bs.tab', function() {
            resetEducationForm();
        });

        function editFacility(btn) {
            var title = btn.getAttribute('data-title');
            var type = btn.getAttribute('data-type');
            var from = btn.getAttribute('data-from');
            var to = btn.getAttribute('data-to');
            var detail = btn.getAttribute('data-detail');
            var id = btn.getAttribute('data-id');

            $('#facility_title').val(title);
            $('#facility_type').val(type);
            $('#facility_from').val(from);
            $('#facility_to').val(to);
            $('#facility_detail').val(detail);
            $('#facility_id').val(id);
            $('#facility-info-save-btn-label').text('Update');
        }

        // Reset form when adding new facility
        function resetFacilityForm() {
            $('#exp-facility-form')[0].reset();
            $('#facility_id').val('');
            $('#facility-info-save-btn-label').text('Save');
        }

        // Call reset function when the tab is shown or when clicking "Add New"
        $('#facility-tab').on('shown.bs.tab', function() {
            resetFacilityForm();
        });
    </script>
    <script>
        let employeeId = "{{ $employee->id }}";

        /* ------------------------
        ADD MORE ROW (NO SAVE)
        ------------------------ */
        $(document).on('click', '.add-row', function() {

            let row = `
                <div class="row contact-row mb-2">
                    <div class="col-md-4">
                        <input type="text" name="contact_name[]" class="form-control emg_c" placeholder="Contact Name">
                    </div>

                    <div class="col-md-4">
                        <select name="relationship[]" class="form-control relationship_drop emg_c">
                            <option value="Father">Father</option>
                            <option value="Mother">Mother</option>
                            <option value="Spouse">Spouse</option>
                            <option value="Brother">Brother</option>
                            <option value="Sister">Sister</option>
                            <option value="Friend">Friend</option>
                            <option value="Other">Other</option>
                        </select>
                        <input type="text" class="form-control mt-1 other-input emg_c" placeholder="Specify" style="display:none;">
                    </div>

                    <div class="col-md-3">
                        <input type="text" name="emgphone[]" class="form-control emg_c" placeholder="Phone">
                    </div>

                    <div class="col-md-1 d-flex gap-1">
                        <button type="button" class="btn btn-danger remove-row">-</button>
                    </div>
                </div>
            `;

            $('#contact-wrapper').append(row);
        });

        /* remove row */
        $(document).on('click', '.remove-row', function() {
            $(this).closest('.contact-row').remove();
        });

        /* ------------------------
        SAVE ALL CONTACTS (AJAX)
        ------------------------ */
        $('#saveContacts').click(function() {

            let contacts = [];

            $('.contact-row').each(function() {

                let name = $(this).find('input[name="contact_name[]"]').val();
                let rel = $(this).find('select[name="relationship[]"]').val();
                if (rel == 'Other') {
                    rel = $(this).find('.other-input').val();
                }
                let phone = $(this).find('input[name="emgphone[]"]').val();

                if (name || phone) {
                    contacts.push({
                        contact_name: name,
                        relationship: rel,
                        phone: phone
                    });
                }
            });

            let saveUrl = "{{ route('employee.emergency.save', ':id') }}";
            saveUrl = saveUrl.replace(':id', employeeId);
            $.ajax({
                url: saveUrl,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    contacts: contacts
                },
                success: function(res) {

                    show_toastr('success', res.message, 'success');
                    $('.emg_c').val('');
                    loadContacts();
                }
            });
        });

        /* ------------------------
        LOAD CONTACTS
        ------------------------ */
        function loadContacts() {

            let listUrl = "{{ route('employee.emergency.list', ':id') }}";
            listUrl = listUrl.replace(':id', employeeId);

            $.get(listUrl, function(data) {

                let html = '';

                if (data.length === 0) {
                    html = `
                <tr>
                    <td colspan="5" class="text-center text-muted">
                        No emergency contacts found
                    </td>
                </tr>
            `;
                } else {

                    data.forEach(function(c, index) {
                        html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${c.contact_name}</td>
                        <td>${c.relationship ?? '-'}</td>
                        <td>${c.phone}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm deleteContact" data-id="${c.id}">
                                Delete
                            </button>
                        </td>
                    </tr>
                `;
                    });
                }

                $('#contactList').html(html);
            });
        }

        loadContacts();

        /* ------------------------
        DELETE CONTACT
        ------------------------ */
        $(document).on('click', '.deleteContact', function(e) {
            e.preventDefault();

            let id = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "This contact will be deleted permanently!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {

                if (result.isConfirmed) {

                    let deleteUrl = "{{ route('emergency.delete', ':id') }}";
                    deleteUrl = deleteUrl.replace(':id', id);
                    $.ajax({
                        url: deleteUrl,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {

                            if (!res.status) {
                                show_toastr('error', res.message, 'error');
                                return;
                            }

                            show_toastr('success', res.message, 'success');
                            loadContacts(); // refresh table only
                        }
                    });

                }
            });
        });
        $(document).on('change', '.relationship_drop', function() {
            let val = $(this).val();
            let otherInput = $(this).closest('.col-md-4').find('.other-input');
            let rel_ship = $(this).closest('.col-md-4').find('.relationship_drop');
            if (val == 'Other') {
                rel_ship.hide();
                otherInput.show();
            } else {
                rel_ship.show();
                otherInput.hide().val('');
            }
        });


        function employeeChild() {
            $.ajax({
                url: '{{ route('employee.children_cnic.list') }}',
                type: 'GET',
                data: {
                    employee_id: employeeId
                },
                success: function(response) {
                    if (response.siblings.length > 0) {
                        populateSiblingTable(response.siblings, response.head);
                        // $('#siblingtable').show();
                    } else {
                        // $('#siblingtable').hide();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        employeeChild();

        function populateSiblingTable(siblings, head) {
            var tableBody = $('#siblingtable tbody');
            tableBody.empty();
            var dis = 0;
            siblings.forEach(function(sibling) {
                $.ajax({
                    url: '{{ route('get.concession') }}',
                    type: 'GET',
                    data: {
                        studentId: sibling.id
                    },
                    success: function(response) {
                        const fee_data = sibling.fee_structure.find(item => item.head_id == head.id);

                        if (!fee_data) {
                            console.error('No matching fee_data found');
                            return;
                        }

                        let dis = 0;

                        if (response == 0) {
                            dis = fee_data.amount;
                        } else {
                            dis = Math.round(fee_data.amount * (1 - response / 100));
                        }

                        var row = $('<tr>');
                        row.append($('<td>').text(sibling.roll_no));
                        row.append($('<td>').text(sibling.stdname));
                        row.append($('<td>').text(sibling.branches.name));
                        row.append($('<td>').text(sibling.class.name));
                        row.append($('<td>').text(fee_data.amount));
                        row.append($('<td>').text(response));
                        row.append($('<td>').text(dis));
                        row.append($('<td>').text(sibling.student_status));
                        tableBody.append(row);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });


            });
        }

        function fetchSubTotal(studentId) {

        }
    </script>
    
@endpush
@section('action-btn')
    @if (!empty($employee))
        <div class="float-end mt-3 m-2">
            <a href="{{ route('employee.show', \Illuminate\Support\Facades\Crypt::encrypt($employee->id)) }}?print=1"
                class="btn btn-sm btn-primary" target="_blank">
                <i class="ti ti-printer"></i>
            </a>
        </div>
        {{-- <div class="float-end mt-3 m-2">
    @can('edit employee')

    <a href="{{route('employee.edit', \Illuminate\Support\Facades\Crypt::encrypt($employee->id))}}"
 data-bs-title="{{__('Edit')}}" class="btn btn-sm btn-primary">
<i class="ti ti-pencil"></i>
</a>

@endcan
</div> --}}

        {{-- <div class="text-end">
    <div class="d-flex justify-content-end drp-languages">
        <ul class="list-unstyled mb-0 m-2">
            <li class="dropdown dash-h-item status-drp">
                <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false">
                    <span class="drp-text hide-mob text-primary"> {{__('Joining Letter')}}</span>
<i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
</a>
<div class="dropdown-menu dash-h-dropdown">
    <a href="{{route('joiningletter.download.pdf', $employee->id)}}" class="btn-icon dropdown-item"
         data-bs-placement="top" target="_blank"><i
            class="ti ti-download ">&nbsp;</i>{{__('PDF')}}</a>

    <a href="{{route('joininglatter.download.doc', $employee->id)}}" class="btn-icon dropdown-item"
         data-bs-placement="top" target="_blank"><i
            class="ti ti-download ">&nbsp;</i>{{__('DOC')}}</a>
</div>
</li>
</ul>
<ul class="list-unstyled mb-0 m-2">
    <li class="dropdown dash-h-item status-drp">
        <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button"
            aria-haspopup="false" aria-expanded="false">
            <span class="drp-text hide-mob text-primary"> {{__('Experience Certificate')}}</span>
            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
        </a>
        <div class="dropdown-menu dash-h-dropdown">
            <a href="{{route('exp.download.pdf', $employee->id)}}" class="btn-icon dropdown-item"
                 data-bs-placement="top" target="_blank"><i
                    class="ti ti-download ">&nbsp;</i>{{__('PDF')}}</a>

            <a href="{{route('exp.download.doc', $employee->id)}}" class="btn-icon dropdown-item"
                 data-bs-placement="top" target="_blank"><i
                    class="ti ti-download ">&nbsp;</i>{{__('DOC')}}</a>
        </div>
    </li>
</ul>
<ul class="list-unstyled mb-0 m-2">
    <li class="dropdown dash-h-item status-drp">
        <a class="dash-head-link dropdown-toggle arrow-none me-0" data-bs-toggle="dropdown" href="#" role="button"
            aria-haspopup="false" aria-expanded="false">
            <span class="drp-text hide-mob text-primary"> {{__('NOC')}}</span>
            <i class="ti ti-chevron-down drp-arrow nocolor hide-mob"></i>
        </a>
        <div class="dropdown-menu dash-h-dropdown">
            <a href="{{route('noc.download.pdf', $employee->id)}}" class="btn-icon dropdown-item"
                 data-bs-placement="top" target="_blank"><i
                    class="ti ti-download ">&nbsp;</i>{{__('PDF')}}</a>

            <a href="{{route('noc.download.doc', $employee->id)}}" class="btn-icon dropdown-item"
                 data-bs-placement="top" target="_blank"><i
                    class="ti ti-download ">&nbsp;</i>{{__('DOC')}}</a>
        </div>
    </li>
</ul>
</div>
</div> --}}
    @endif
@endsection

@section('content')
    @if (!empty($employee))
        {{ Form::open(['route' => ['employee-personal-info', $employee->id], 'id' => 'employee_personal_info', 'method' => 'post', 'enctype' => 'multipart/form-data']) }}
        @csrf
        <div class="row mt-4">
            <div class="col-xl-3 col-md-3">
                <div class="mt-6" style="height: 500px;">
                    @if ($employee->profile_img)
                        <img id="profileImage" src="{{ Storage::url('emp_profile_images/' . $employee->profile_img) }}"
                            alt=""
                            style="border:1px solid var(--primary); width:100%; height: 100%; object-fit: fill; border-radius: 20px;">
                    @else
                        <img id="profileImage" src="{{ Storage::url('emp_profile_images/avatar.png') }}" alt=""
                            style="border:1px solid var(--primary); width:100%; height: 100%; object-fit: fill; border-radius: 20px;">
                    @endif
                </div>
                <div class="mb-2">

                    <input type="file" class="form-control mt-1" name="profile_img" id="profileImageInput">
                    <span style="color:red; font-size:0.7rem;">Size of image should not be more than 1MB.</span>
                </div>
                <h3>{{ !empty($employee) ? $employee->name : '' }} <span style="font-size: 1rem;"></h3>
                ({{ !empty($employee->designation) ? $employee->designation->name : '' }})</span>
            </div>
            <div class="col-xl-9 col-md-9">
                <ul class="nav nav-tabs" id="employeeTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="personal-detail-tab" data-bs-toggle="tab" href="#personal-detail"
                            role="tab" aria-controls="personal-detail"
                            aria-selected="true">{{ __('Personal Info') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="job-info-tab" data-bs-toggle="tab" href="#job-info" role="tab"
                            aria-controls="job-info" aria-selected="false">{{ __('Job Info') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="emergency-contacts-tab" data-bs-toggle="tab" href="#emergency-contacts"
                            role="tab" aria-controls="emergency-contacts"
                            aria-selected="false">{{ __('Emergency Contacts') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="experience-tab" data-bs-toggle="tab" href="#experience" role="tab"
                            aria-controls="experience" aria-selected="false">{{ __('Experience') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="education-tab" data-bs-toggle="tab" href="#education" role="tab"
                            aria-controls="education" aria-selected="false">{{ __('Education') }}</a>
                    </li>
                    {{-- <li class="nav-item">
                <a class="nav-link" id="job-history-tab" data-bs-toggle="tab" href="#job-history" role="tab"
                    aria-controls="job-history" aria-selected="false">{{__('Job History')}}</a>
            </li> --}}
                    <li class="nav-item">
                        <a class="nav-link" id="facility-tab" data-bs-toggle="tab" href="#facility" role="tab"
                            aria-controls="facility" aria-selected="false">{{ __('Facility') }}</a>
                    </li>
                    {{-- <li class="nav-item">
                <a class="nav-link" id="leaves-tab" data-bs-toggle="tab" href="#leaves" role="tab"
                    aria-controls="leaves" aria-selected="false">{{__('Leaves')}}</a>
            </li> --}}
                    <li class="nav-item">
                        <a class="nav-link" id="employee-childrens-tab" data-bs-toggle="tab" href="#employee-childrens"
                            role="tab" aria-controls="employee-childrens"
                            aria-selected="false">{{ __('Employee Childrens') }}</a>
                    </li>
                </ul>
                <div class="tab-content" id="employeeTabContent">
                    <div class="tab-pane fade show active" id="personal-detail" role="tabpanel"
                        aria-labelledby="personal-detail-tab">
                        <div class="card">
                            <div class="card-body employee-detail-body fulls-card">

                                <div class="row">
                                    <div class="form-group col-md-3">
                                        {!! Form::label('employee_number', __('Employee No'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('employee_number', $employeesId, [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                            'readonly' => 'readonly',
                                        ]) !!}
                                    </div>

                                    <div class="form-group col-md-3">
                                        @php
                                            $salutes = ['Mr' => 'Mr', 'Mrs' => 'Mrs', 'Miss' => 'Miss', 'Ms' => 'Ms'];
                                        @endphp
                                        {!! Form::label('salute', __('Salute'), ['class' => 'form-label']) !!}
                                        <span class="text-danger pl-1">*</span>
                                        {{ Form::select('salute', $salutes, !empty($employee) ? $employee->salute : '', [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                        ]) }}
                                    </div>

                                    <div class="form-group col-md-6">
                                        {!! Form::label('name', __('Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('name', !empty($employee) ? $employee->name : '', [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('f_name', __('Father/Husband Name'), ['class' => 'form-label']) !!}
                                        {!! Form::text('f_name', !empty($employee) ? $employee->f_name : '', [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                        ]) !!}
                                    </div>
                                    <div class="col-md-3">
                                        {!! Form::label('dob', __('Date of Birth'), ['class' => 'form-label']) !!}
                                        {!! Form::date('dob', !empty($employee) ? $employee->dob : '', ['class' => 'form-control']) !!}
                                        {{-- {!! Form::text('dob', old('dob'), ['class' => 'form-control datepicker']) !!} --}}
                                    </div>
                                    <div class="col-md-3">
                                        {!! Form::label('cnic', __('CNIC'), ['class' => 'form-label']) !!}
                                        {!! Form::text('cnic', !empty($employee) ? $employee->cnic : '', [
                                            'class' => 'form-control',
                                            'id' => 'cnic',
                                        ]) !!}
                                    </div>


                                    <div class="form-group col-md-3">
                                        {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}
                                        <div class="d-flex radio-check mt-2 ms-2">
                                            <div class="form-check form-check-inline form-group">
                                                <input type="radio" id="g_male" value="Male" name="gender"
                                                    class="form-check-input"
                                                    {{ $employee->gender == 'Male' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="g_male">{{ __('Male') }}</label>
                                            </div>
                                            <div class="form-check form-check-inline form-group">
                                                <input type="radio" id="g_female" value="Female" name="gender"
                                                    class="form-check-input"
                                                    {{ $employee->gender == 'Female' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="g_female">{{ __('Female') }}</label>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="form-group col-md-3">
                                        {!! Form::label('category', __('Category'), ['class' => 'form-label']) !!}
                                        <div class="d-flex radio-check mt-2 ms-2">
                                            <div class="form-check form-check-inline form-group">
                                                <input type="radio" id="cate_reg" value="Regular" name="category"
                                                    class="form-check-input"
                                                    {{ $employee->category == 'Regular' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="cate_reg">{{ __('Regular') }}</label>
                                            </div>
                                            <div class="form-check form-check-inline form-group">
                                                <input type="radio" id="cate_adh" value="Adhoc" name="category"
                                                    class="form-check-input"
                                                    {{ $employee->category == 'Adhoc' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="cate_adh">{{ __('Adhoc') }}</label>
                                            </div>
                                            <div class="form-check form-check-inline form-group">
                                                <input type="radio" id="cate_vis" value="Visiting" name="category"
                                                    class="form-check-input"
                                                    {{ $employee->category == 'Visiting' ? 'checked' : '' }}>
                                                <label class="form-check-label"
                                                    for="cate_vis">{{ __('Visiting') }}</label>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="form-group col-md-3">
                                        {!! Form::label('religion', __('Religion'), ['class' => 'form-label']) !!}
                                        {{ Form::select('religion', ['Muslim' => 'Muslim', 'Non Muslim' => 'Non Muslim'], !empty($employee) ? $employee->religion : '', ['class' => 'form-control', 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-3">
                                        {!! Form::label('blood_group', __('Blood Group'), ['class' => 'form-label']) !!}
                                        {{ Form::select('blood_group', ['A+' => 'A+', 'A-' => 'A-', 'B+' => 'B+', 'B-' => 'B-', 'AB+' => 'AB+', 'AB-' => 'AB-', 'O+' => 'O+', 'O-' => 'O-'], null, ['class' => 'form-control', 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('phone', __('Phone'), ['class' => 'form-label']) !!}
                                        {!! Form::number('phone', !empty($employee) ? $employee->phone : '', ['class' => 'form-control', 'min' => '0']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('email', __('Email'), ['class' => 'form-label']) !!}
                                        {!! Form::email('email', !empty($employee) ? $employee->email : '', [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('eobi_id', __('EOBI ID'), ['class' => 'form-label']) !!}
                                        {!! Form::text('eobi_id', !empty($employee) ? $employee->eobi_id : '', [
                                            'class' => 'form-control',
                                            'min' => '0',
                                        ]) !!}
                                    </div>

                                    <div class="form-group col-md-6">
                                        {!! Form::label('ssc', __('SSC ID'), ['class' => 'form-label']) !!}
                                        {!! Form::text('ssc_id', !empty($employee) ? $employee->ssc_id : '', ['class' => 'form-control', 'min' => '0']) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('present_address', __('Present Address'), ['class' => 'form-label']) !!}
                                        {!! Form::textarea('present_address', !empty($employee) ? $employee->present_address : '', [
                                            'class' => 'form-control',
                                            'rows' => 2,
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('address', __('Permanent Address'), ['class' => 'form-label']) !!}
                                        {!! Form::textarea('address', !empty($employee) ? $employee->address : '', [
                                            'class' => 'form-control',
                                            'rows' => 2,
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
                                        {{ Form::text('branch', $branches[$employee->owned_by] ?? '', ['class' => 'form-control select', 'readonly' => 'readonly']) }}
                                    </div>
                                    {{-- if auth user is company then change else simple readonly  --}}
                                    @if (\Auth::user()->type == 'company')
                                        <div class="form-group col-md-4">
                                            {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                            {{ Form::select('department_id', $departments, $employee->department_id, ['class' => 'form-control  ', 'id' => 'department_id', 'required' => 'required']) }}
                                        </div>
                                        <div class="form-group col-md-4">
                                            {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                            {{ Form::select('designation_id', $designations, $employee->designation_id, ['class' => 'form-control  ', 'id' => 'designation_id', 'required' => 'required']) }}
                                        </div>
                                    @else
                                        <div class="form-group col-md-4">
                                            {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
                                            {{ Form::text('department', $departments[$employee->department_id] ?? '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
                                        </div>
                                        <div class="form-group col-md-4">
                                            {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
                                            {{ Form::text('designation', $designations[$employee->designation_id] ?? '', ['class' => 'form-control', 'readonly' => 'readonly']) }}
                                        </div>
                                    @endif
                                </div>
                                </form>
                                <div class='d-flex justify-content-end'>
                                    <button id=""
                                        onclick="document.getElementById('employee_personal_info').submit(); return false;"
                                        class="btn btn-outline-primary mt-3">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $lastPayscaleDetail = @$employee->employee_payscale_details->last();
                        $payscalesauto = \App\Models\EmployeeScale::with(
                            'employeeScaleHeads',
                            'employeeScaleHeads.SalaryHeads',
                            'employeepayScaledetailHeads',
                        )
                            ->where('id', @$lastPayscaleDetail->pay_scale_id)
                            ->first();
                    @endphp
                    <div class="tab-pane fade" id="job-info" role="tabpanel" aria-labelledby="job-info-tab">
                        <div class="card">
                            <div class="card-body employee-detail-body fulls-card">
                                {{ Form::open(['route' => ['employee_job_info', $employee->id], 'id' => 'job-info-form', 'method' => 'post']) }}
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('company_doj', 'Joining Date', ['class' => 'form-label']) !!}
                                        {!! Form::date('company_doj', !empty($employee->company_doj) ? $employee->company_doj : '', [
                                            'class' => 'form-control ',
                                            'required' => 'required',
                                            'id' => 'joining_date',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('effect_from', 'Effective From', ['class' => 'form-label']) !!}
                                        {!! Form::date('effect_from', !empty($payscalesauto) ? $payscalesauto->effect_from : '', [
                                            'class' => 'form-control ',
                                            'readonly' => 'readonly',
                                        ]) !!}
                                    </div>

                                    <div class="form-group col-md-4">

                                        {{ Form::label('pay_scale', __('Pay Scale'), ['class' => 'form-label']) }}
                                        {{ Form::text('pay_scale', !empty($payscalesauto->id) ? $payscalesauto->id : '', ['class' => 'form-control  ', 'required' => 'required', 'readonly' => 'readonly']) }}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('resignation_date', 'Resignation Date', ['class' => 'form-label']) !!}
                                        {!! Form::date('resignation_date', !empty($resignation) ? $resignation->resignation_date : '', [
                                            'class' => 'form-control ',
                                            'readonly' => 'readonly',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-2 pt-5">
                                        {!! Form::checkbox('is_resigned', 1, $isResigned, [
                                            'id' => 'is_resigned',
                                            'disabled' => 'disabled',
                                        ]) !!}
                                        {!! Form::hidden('is_resigned', 0) !!}
                                        {{ Form::label('is_resigned', __('Is Resigned'), ['class' => 'form-label']) }}
                                    </div>
                                    @php
                                        $isProbationEnded =
                                            !is_null($employee->probation_period) &&
                                            !empty($employee->probation_end) &&
                                        $employee->probation_end <= now(); @endphp <div class="form-group col-md-2 pt-5">
                                        {!! Form::hidden('includeIn_sal', 0) !!}
                                        {!! Form::checkbox('includeIn_sal', 1, !empty($employee->is_res_ter), [
                                            'id' => 'includeIn_sal',
                                        ]) !!}
                                        {{ Form::label('includeIn_sal', __('Inc.Sal'), ['class' => 'form-label']) }}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('probation_end', __('Probation End Date'), ['class' => 'form-label']) !!}
                                        {!! Form::date('probation_end', !empty($employee->probation_end) ? $employee->probation_end : '', [
                                            'class' => 'form-control',
                                            'id' => 'pro_end_date',
                                            'readonly' => 'readonly',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-3">
                                        {{-- if user is  company then can ernnter other wise readonly --}}
                                        {!! Form::label('probation_period', __('Probation Months'), ['class' => 'form-label']) !!}
                                        {!! Form::number('probation_period', !empty($employee->probation_period) ? $employee->probation_period : '', [
                                            'class' => 'form-control',
                                            'id' => 'pro_date',
                                            'required' => 'required',
                                            'min' => '1',
                                            'readonly' => \Auth::user()->type != 'company'
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-3 pt-5">
                                        {!! Form::checkbox('probation_ended', 1, $isProbationEnded, [
                                            'id' => 'probation_ended',
                                        ]) !!}
                                        {{ Form::label('probation_ended', __('Probation End'), ['class' => 'form-label']) }}
                                        {!! Form::hidden('probation_ended', 0) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('emp_security', __('Employee Security %'), ['class' => 'form-label']) !!}
                                        {!! Form::number('emp_security', !empty($employee->security) ? $employee->security : '', [
                                            'class' => 'form-control',
                                            'min' => '0',
                                        ]) !!}
                                    </div>
                                    {{--
                    <div class="form-group col-md-3 pt-5">
                        {!! Form::checkbox('is_cal_leave', 1, $isProbationEnded, [
                        'id' =>
                        'is_cal_leave'
                        ]) !!}
                        {{ Form::label('is_cal_leave', __('Is Cal Leave ?'), ['class' => 'form-label']) }}
                        {!! Form::hidden('is_cal_leave', 0) !!}
                    </div> --}}
                                    @php
                                        $service_tenure = '';
                                        if ($employee->company_doj) {
                                            $company_doj = \Carbon\Carbon::parse($employee->company_doj);
                                            $current_date = \Carbon\Carbon::now();
                                            $years = $current_date->diffInYears($company_doj);
                                            $months = $current_date->diffInMonths($company_doj) % 12;
                                            $service_tenure = $months . ' / ' . $years;
                                        }
                                    @endphp
                                    <div class="form-group col-md-4">
                                        {!! Form::label('service_tenure', __('Service Tenure (M/Y)'), ['class' => 'form-label']) !!}
                                        {!! Form::text('service_tenure', $service_tenure, [
                                            'class' => 'form-control',
                                            'readonly' => 'readonly',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('job_description', __('Job Description'), ['class' => 'form-label']) !!}
                                        {!! Form::textarea('job_description', !empty($employee) ? $employee->permanent_address : '', [
                                            'class' => 'form-control',
                                            'rows' => 2,
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('pessi', __('PESSI %'), ['class' => 'form-label']) !!}
                                        {!! Form::number('pessi', !empty($employee->pessi) ? $employee->pessi : '', [
                                            'class' => 'form-control',
                                            'min' => '0',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('pessi_employer', __('PESSI Employer %'), ['class' => 'form-label']) !!}
                                        {!! Form::number('pessi_employer', !empty($employee->pessi_employer) ? $employee->pessi_employer : '', [
                                            'class' => 'form-control',
                                            'min' => '0',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('pessi_value', __('PESSI Value %'), ['class' => 'form-label']) !!}
                                        {!! Form::number('pessi_value', !empty($branches_school->pessi_values) ? $branches_school->pessi_values : '0', [
                                            'class' => 'form-control',
                                            'readonly' => 'readonly',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('eobi', __('EOBI %'), ['class' => 'form-label']) !!}
                                        {!! Form::number('eobi', !empty($employee->eobi) ? $employee->eobi : '', [
                                            'class' => 'form-control',
                                            'min' => '0',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('eobi_employer', __('EOBI Employer %'), ['class' => 'form-label']) !!}
                                        {!! Form::number('eobi_employer', !empty($employee->eobi_employer) ? $employee->eobi_employer : '', [
                                            'class' => 'form-control',
                                            'min' => '0',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('eobi_value', __('EOBI Value %'), ['class' => 'form-label']) !!}
                                        {!! Form::number('eobi_value', !empty($branches_school->eobi_values) ? $branches_school->eobi_values : '0', [
                                            'class' => 'form-control',
                                            'readonly' => 'readonly',
                                        ]) !!}
                                    </div>
                                    <div class="col-md-12">
                                        <p>Note: Minimum amount of EOBI and PESSI will be set on Company setup </p>
                                    </div>
                                </div>
                                </form>
                                <div class='d-flex justify-content-end'>
                                    <button id=""
                                        onclick="document.getElementById('job-info-form').submit(); return false;"
                                        class="btn btn-outline-primary mt-3">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="emergency-contacts" role="tabpanel"
                        aria-labelledby="emergency-contacts-tab">
                        <div class="card">
                            <div class="card-body employee-detail-body fulls-card">
                                <div id="contact-wrapper">

                                    <!-- Row Template -->
                                    <div class="row contact-row mb-2">
                                        <div class="col-md-4">
                                            <input type="text" name="contact_name[]" class="form-control emg_c"
                                                placeholder="Contact Name">
                                        </div>

                                        <div class="col-md-4">
                                            <select name="relationship[]" class="form-control relationship_drop emg_c">
                                                <option value="Father">Father</option>
                                                <option value="Mother">Mother</option>
                                                <option value="Spouse">Spouse</option>
                                                <option value="Brother">Brother</option>
                                                <option value="Sister">Sister</option>
                                                <option value="Friend">Friend</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            <input type="text" class="form-control mt-1 other-input emg_c"
                                                placeholder="Specify" style="display:none;">
                                        </div>

                                        <div class="col-md-3">
                                            <input type="text" name="emgphone[]" class="form-control emg_c"
                                                placeholder="Phone">
                                        </div>

                                        <div class="col-md-1 d-flex gap-1">
                                            <button type="button" class="btn btn-success add-row">+</button>
                                        </div>
                                    </div>

                                </div>
                                <button type="button" class="btn btn-primary" style='float: right;' id="saveContacts">
                                    Save Contacts
                                </button>



                                <div id="msg" style="margin-top: 70px;"></div>

                                <hr>

                                <table class="">
                                    <thead>
                                        <tr class="table_heads">
                                            <th>#</th>
                                            <th>Contact Name</th>
                                            <th>Relationship</th>
                                            <th>Phone</th>
                                            <th width="100">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody id="contactList">
                                        <!-- AJAX rows will load here -->
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="experience" role="tabpanel" aria-labelledby="experience-tab">
                        <div class="card">
                            <div class="card-body employee-detail-body fulls-card">
                                {{ Form::open(['route' => ['employee_exp_info', $employee->id], 'id' => 'exp-info-form', 'method' => 'post']) }}

                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('exp_organization', __('Organization'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('exp_organization', null, [
                                            'class' => 'form-control',
                                            'id' => 'organization',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('exp_designation', __('Designation'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('exp_designation', null, [
                                            'class' => 'form-control',
                                            'id' => 'designation',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('exp_from', __('From'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::date('exp_from', null, [
                                            'class' => 'form-control',
                                            'id' => 'from',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('exp_to', __('To'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::date('exp_to', null, [
                                            'class' => 'form-control',
                                            'id' => 'to',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-12">
                                        {!! Form::label('reason_of_leaving', __('Reason Of Leaving'), ['class' => 'form-label']) !!}
                                        {!! Form::textarea('reason_of_leaving', null, [
                                            'class' => 'form-control',
                                            'rows' => 2,
                                            'id' => 'reason',
                                        ]) !!}
                                    </div>
                                </div>
                                <input type="hidden" id="experience_id" name="experience_id" value="">
                                </form>
                                <div class='d-flex justify-content-end'>
                                    <button id="exp-info-save-btn"
                                        onclick="document.getElementById('exp-info-form').submit(); return false;"
                                        class="btn btn-outline-primary mt-3">Save</button>
                                </div>
                                <div class="col-md-12 mx-3 table-responsive" style="width:100%; margin-top: 20px;">
                                    <table class="table-auto w-full border-collapse">
                                        <thead>
                                            <tr class="table_heads">
                                                <th class="px-2 py-1">{{ __('Sr.') }}</th>
                                                <th class="px-2 py-1">{{ __('Organization') }}</th>
                                                <th class="px-2 py-1">{{ __('Designation') }}</th>
                                                <th class="px-2 py-1">{{ __('From') }}</th>
                                                <th class="px-2 py-1">{{ __('To') }}</th>
                                                <th class="px-2 py-1">{{ __('Reason Of Leaving') }}</th>
                                                <th class="px-2 py-1 w-[160px] text-center">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($emp_exp as $exp)
                                                <tr data-id="{{ $exp->id }}"
                                                    data-employee-id="{{ $employee->id }}">
                                                    <td class="px-2 py-1">{{ strtoupper($loop->iteration) }}</td>
                                                    <td class="px-2 py-1">{{ strtoupper($exp->organization) }}</td>
                                                    <td class="px-2 py-1">{{ strtoupper($exp->designation) }}</td>
                                                    <td class="px-2 py-1 text-center">{{ strtoupper($exp->from) }}</td>
                                                    <td class="px-2 py-1 text-center">{{ strtoupper($exp->to) }}</td>
                                                    <td class="px-2 py-1">{{ strtoupper($exp->reason) }}</td>

                                                    <!-- Action column -->
                                                    <td class="px-2 py-1 text-center">
                                                        <div class="flex gap-2 justify-center">
                                                            <!-- Edit button -->
                                                            <a href="#" class="btn btn-sm edit-experience-btn"
                                                                style="background: linear-gradient(141.55deg,#100773 3.46%,#100773 99.86%) !important;"
                                                                onclick="editExperience(this)"
                                                                data-organization="{{ $exp->organization }}"
                                                                data-designation="{{ $exp->designation }}"
                                                                data-from="{{ $exp->from }}"
                                                                data-to="{{ $exp->to }}"
                                                                data-reason="{{ $exp->reason }}"
                                                                data-id="{{ $exp->id }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>

                                                            <!-- Delete button -->
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['employee_exp_info.destroy', $exp->id],
                                                                'id' => 'delete-form-' . $exp->id,
                                                            ]) !!}
                                                            <a href="#"
                                                                class="btn btn-sm bg-red-600 hover:bg-red-700 text-white flex items-center justify-center bs-pass-para"
                                                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{ $exp->id }}').submit();">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="education" role="tabpanel" aria-labelledby="education-tab">
                        <div class="card">
                            <div class="card-body employee-detail-body fulls-card">
                                {{ Form::open(['route' => ['employee_edu_info', $employee->id], 'id' => 'exp-edu-form', 'method' => 'post']) }}
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-4">
                                        {!! Form::label('institute_name', __('Institute Name'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('institute_name', null, [
                                            'class' => 'form-control',
                                        ]) !!}
                                    </div>
                                    @php
                                        $degreestitle = [
                                            'phd' => 'PHD',
                                            'master' => 'Masters / M.Phil',
                                            'bachelor' => 'Bachelors',
                                            'intermediate' => 'Intermediate',
                                            'matric' => 'Matric',
                                            'middle' => 'Middle',
                                            'primary' => 'Primary',
                                            'illiterate' => 'Illiterate',
                                        ];
                                    @endphp

                                    <div class="form-group col-md-4">
                                        {!! Form::label('degree_level', __('Degree Level'), ['class' => 'form-label']) !!}
                                        <span class="text-danger pl-1">*</span>
                                        {{ Form::select('degree_level', $degreestitle, null, ['class' => 'form-control', 'required' => 'required']) }}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('degree_title', __('Degree Title'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('degree_title', null, [
                                            'class' => 'form-control',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('subject', __('Subject'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::text('subject', null, [
                                            'class' => 'form-control',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('adm_date', __('Admission Date'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::date('adm_date', null, [
                                            'class' => 'form-control',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('passing_year', __('Passing Year'), ['class' => 'form-label']) !!}<span class="text-danger pl-1">*</span>
                                        {!! Form::date('passing_year', null, [
                                            'class' => 'form-control',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {!! Form::label('grade', __('Grade'), ['class' => 'form-label']) !!}
                                        <span class="text-danger pl-1">*</span>

                                        {!! Form::select(
                                            'grade',
                                            ['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E', 'F' => 'F'],
                                            old('grade'),
                                            ['class' => 'form-control', 'required' => true],
                                        ) !!}
                                    </div>

                                    <div class="form-group col-md-8">
                                        {!! Form::label('description', __('Description'), ['class' => 'form-label']) !!}
                                        {!! Form::textarea('description', !empty($employee) ? $employee->permanent_address : '', [
                                            'class' => 'form-control',
                                            'rows' => 1,
                                        ]) !!}
                                    </div>
                                </div>
                                <input type="hidden" id="education_id" name="education_id" value="">
                                <div class='d-flex justify-content-end'>
                                    <button id="edu-info-save-btn"
                                        onclick="document.getElementById('exp-edu-form').submit(); return false;"
                                        class="btn btn-outline-primary mt-3"><span
                                            id="edu-info-save-btn-label">Save</span></button>
                                </div>
                                </form>

                                <div class="col-md-12 mx-3 table-responsive" style="width:100%; margin-top: 20px;">
                                    <table class="table-auto w-full border-collapse">
                                        <thead>
                                            <tr class="table_heads">
                                                <th class="px-2 py-1 w-[40px]">Sr.</th>
                                                <th class="px-2 py-1">Institute Name</th>
                                                <th class="px-2 py-1">Degree Level</th>
                                                <th class="px-2 py-1">Degree Title</th>
                                                <th class="px-2 py-1">Subject</th>
                                                <th class="px-2 py-1 w-[80px]">Grade</th>
                                                <th class="px-2 py-1 w-[70px]">Admission Date</th>
                                                <th class="px-2 py-1 w-[70px]">Passing Year</th>
                                                <th class="px-2 py-1">Details</th>
                                                <th class="px-2 py-1 w-[90px]">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($emp_edu as $edu)
                                                <tr data-id="{{ @$edu->id }}"
                                                    data-employee-id="{{ @$employee->id }}">
                                                    <td class="px-2 py-1 text-center">{{ $loop->iteration }}</td>
                                                    <td class="px-2 py-1">{{ strtoupper(@$edu->institute) }}</td>
                                                    <td class="px-2 py-1">{{ strtoupper(@$edu->degree) }}</td>
                                                    <td class="px-2 py-1">{{ strtoupper(@$edu->title) }}</td>
                                                    <td class="px-2 py-1">{{ strtoupper(@$edu->subject) }}</td>
                                                    <td class="px-2 py-1 text-center">{{ strtoupper(@$edu->grade) }}</td>
                                                    <td class="px-2 py-1">{{ strtoupper(@$edu->adm_date) }}</td>
                                                    <td class="px-2 py-1">{{ strtoupper(@$edu->pass_date) }}</td>
                                                    <td class="px-2 py-1">{{ strtoupper(@$edu->reason) }}</td>
                                                    <td class="px-2 py-1 text-center">
                                                        <div class="flex gap-2 justify-center">
                                                            <!-- Edit button -->
                                                            <a href="#" class="btn btn-sm edit-education-btn"
                                                                style="background: linear-gradient(141.55deg,#100773 3.46%,#100773 99.86%) !important;"
                                                                onclick="editEducation(this)"
                                                                data-institute="{{ @$edu->institute }}"
                                                                data-degree="{{ @$edu->degree }}"
                                                                data-title="{{ @$edu->title }}"
                                                                data-subject="{{ @$edu->subject }}"
                                                                data-adm_date="{{ @$edu->adm_date }}"
                                                                data-pass_date="{{ @$edu->pass_date }}"
                                                                data-grade="{{ @$edu->grade }}"
                                                                data-reason="{{ @$edu->reason }}"
                                                                data-id="{{ @$edu->id }}">
                                                                <i class="ti ti-pencil text-white"></i>
                                                            </a>

                                                            <!-- Delete button -->
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['employee_education.destroy', $edu->id],
                                                                'id' => 'delete-form-' . $edu->id,
                                                            ]) !!}
                                                            <a href="#" class="btn btn-sm bs-pass-para"
                                                                style="background-color:rgb(255,58,110) !important;"
                                                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{ $edu->id }}').submit();">
                                                                <i class="ti ti-trash text-white"></i>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="tab-pane fade" id="job-history" role="tabpanel" aria-labelledby="job-history-tab">
    <div class="card">
        <div class="card-body employee-detail-body fulls-card">
            <div class="row">
                <div class="form-group col-md-6">
                    {{ Form::label('job_his_company', __('Company'), ['class' => 'form-label']) }}
                    {{ Form::select('job_his_company', $branches, $employee->branch_id, array('class' => 'form-control select', 'required' => 'required', 'id' => 'branch_id')) }}
                </div>

                <div class="form-group col-md-6">
                    {{ Form::label('job_department_id', __('Department'), ['class' => 'form-label']) }}
                    {{ Form::select('job_department_id', $departments, !empty($employee->department) ? $employee->department->name : '', array('class' => 'form-control  ', 'id' => 'department_id', 'required' => 'required')) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('job_designation_id', __('Designation'), ['class' => 'form-label']) }}
                    {{ Form::select('job_designation_id', $designations, !empty($employee->designation) ? $employee->designation->name : '', array('class' => 'form-control  ', 'id' => 'department_id', 'required' => 'required')) }}
                </div>
                <div class="form-group col-md-6">
                    {{ Form::label('area', __('Area'), ['class' => 'form-label']) }}
                    {{ Form::select('area', ['' => 'Select One', 'rwp' => 'Rawalpindi', 'isb' => 'Islamabad'], (!empty($employee) ? $employee->religion : ''), ['class' => 'form-control', 'required' => 'required']) }}
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('job_from', __('Job From'), ['class' => 'form-label']) !!}
                    {!! Form::date('job_from', null, [
                    'class' => 'form-control',
                    ]) !!}
                </div>
                <div class="form-group col-md-6">
                    {!! Form::label('job_to', __('Job To'), ['class' => 'form-label']) !!}
                    {!! Form::date('job_to', null, [
                    'class' => 'form-control',
                    ]) !!}
                </div>
                <div class="form-group col-md-12">
                    {!! Form::label('job_detail', __('Detail'), ['class' => 'form-label']) !!}
                    {!! Form::textarea('job_detail', (!empty($employee) ? $employee->permanent_address : ''), [
                    'class' =>
                    'form-control',
                    'rows' => 2
                    ]) !!}
                </div>
            </div>
            <div class='d-flex justify-content-end'>
                <button id="submitBtnSection1" class="btn btn-outline-primary mt-3">Save</button>
            </div>
            <div class="col-md-12 mx-3 table-responsive" style="width: 100%;">
                <table class="">
                    <thead>
                        <tr class="table_heads">
                            <th width="200px">{{__('Action')}}</th>
                            <th>{{__('Company') }}</th>
                            <th> {{__('Department')}}</th>
                            <th> {{__('Designation')}}</th>
                            <th> {{__('Area')}}</th>
                            <th> {{__('Job From')}}</th>
                            <th> {{__('Job To')}}</th>
                            <th> {{__('Details')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leaves as $leave)
                        @if (Gate::check('edit employee') || Gate::check('delete employee'))
                        <td>
                            @can('delete leave')
                            <div class="action-btn bg-danger ms-2">
                                {!! Form::open(['method' => 'DELETE', 'route' => ['leave.destroy',
                                $leave->id],'id'=>'delete-form-'.$leave->id]) !!}
                                <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" 
                                    data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}"
                                    data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}"
                                    data-confirm-yes="document.getElementById('delete-form-{{$leave->id}}').submit();">
                                    <i class="ti ti-trash text-white"></i></a>
                                {!! Form::close() !!}
                            </div>
                            @endif
                        </td>
                        @endif
                        <td>{{$leave->id}}</td>
                        <td><input type="checkbox" name="" id="" @if (@$leave->$leaveType->status !== 'paid')
                            checked
                            @endif
                            ></td>
                        <td>{{ !empty(\Auth::user()->getLeaveType($leave->leave_type_id))?\Auth::user()->getLeaveType($leave->leave_type_id)->title:'' }}
                        </td>
                        <td>{{ \Auth::user()->dateFormat($leave->applied_on )}}</td>
                        <td>{{ \Auth::user()->dateFormat($leave->start_date ) }}</td>
                        <td>{{ \Auth::user()->dateFormat($leave->end_date )  }}</td>
                        <td>{{ $leave->total_leave_days }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> --}}
                    <div class="tab-pane fade" id="facility" role="tabpanel" aria-labelledby="facility-tab">
                        <div class="card">
                            <div class="card-body employee-detail-body fulls-card">
                                {{ Form::open(['route' => ['employee_facility_info', $employee->id], 'id' => 'exp-facility-form', 'method' => 'post']) }}
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        {!! Form::label('facility_title', __('Facility Title'), ['class' => 'form-label']) !!}
                                        {!! Form::text('facility_title', null, [
                                            'class' => 'form-control',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {{ Form::label('facility_type', __('Facility Type'), ['class' => 'form-label']) }}
                                        {!! Form::text('facility_type', null, [
                                            'class' => 'form-control',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('facility_from', __('Given Date'), ['class' => 'form-label']) !!}
                                        {!! Form::date('facility_from', null, [
                                            'class' => 'form-control',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-6">
                                        {!! Form::label('facility_to', __('UpTo Date'), ['class' => 'form-label']) !!}
                                        {!! Form::date('facility_to', null, [
                                            'class' => 'form-control',
                                        ]) !!}
                                    </div>
                                    <div class="form-group col-md-12">
                                        {!! Form::label('facility_detail', __('Detail'), ['class' => 'form-label']) !!}
                                        {!! Form::textarea('facility_detail', !empty($employee) ? $employee->permanent_address : '', [
                                            'class' => 'form-control',
                                            'rows' => 2,
                                        ]) !!}
                                    </div>
                                </div>
                                <input type="hidden" id="facility_id" name="facility_id" value="">
                                <div class='d-flex justify-content-end'>
                                    <button id="facility-info-save-btn"
                                        onclick="document.getElementById('exp-facility-form').submit(); return false;"
                                        class="btn btn-outline-primary mt-3"><span
                                            id="facility-info-save-btn-label">Save</span></button>
                                </div>
                                </form>
                                <div class="col-md-12 mx-3 table-responsive" style="width:100%; margin-top: 20px;">
                                    <table class="">
                                        <thead>
                                            <tr class="table_heads">
                                                <th>{{ __('Sr.') }}</th>
                                                <th>{{ __('Title') }}</th>
                                                <th> {{ __('Type') }}</th>
                                                <th> {{ __('Given Date') }}</th>
                                                <th> {{ __('UpTo Date') }}</th>
                                                <th> {{ __('Date') }}</th>
                                                <th width="200px">{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($emp_fac as $fac)
                                                <tr data-id="{{ @$fac->id }}"
                                                    data-employee-id="{{ @$employee->id }}">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ strtoupper(@$fac->title) }}</td>
                                                    <td>{{ strtoupper(@$fac->type) }}</td>
                                                    <td>{{ strtoupper(@$fac->given_date) }}</td>
                                                    <td>{{ strtoupper(@$fac->upto_date) }}</td>
                                                    <td>{{ strtoupper(@$fac->detail) }}</td>
                                                    {{-- add edit and delete --}}
                                                    <td style="display: flex">
                                                        <div class="action-btn ms-2">
                                                            <a href="#"
                                                                class=" btn btn-sm align-items-center edit-facility-btn"
                                                                style="background: linear-gradient(141.55deg, #100773 3.46%, #100773 99.86%), #100773 !important;"
                                                                onclick="editFacility(this)"
                                                                data-title="{{ @$fac->title }}"
                                                                data-type="{{ @$fac->type }}"
                                                                data-from="{{ @$fac->given_date }}"
                                                                data-to="{{ @$fac->upto_date }}"
                                                                data-detail="{{ @$fac->detail }}"
                                                                data-id="{{ @$fac->id }}"
                                                                data-employee-id="{{ @$employee->id }}">
                                                                <i class="ti ti-pencil text-white"></i></a>
                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['employee_facility.destroy', $fac->id],
                                                                'id' => 'delete-form-' . $fac->id,
                                                            ]) !!}
                                                            <a href="#"
                                                                class="ms-2 btn btn-sm  align-items-center bs-pass-para"
                                                                style="background-color: rgb(255, 58, 110) !important;"
                                                                data-bs-title="{{ __('Delete') }}"
                                                                data-bs-title="{{ __('Delete') }}"
                                                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{ $fac->id }}').submit();">
                                                                <i class="ti ti-trash text-white"></i></a>
                                                        </div>
                                                        <div class="action-btn ms-2" style="width: 0px !important;">

                                                            {!! Form::close() !!}
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- <div class="tab-pane fade" id="leaves" role="tabpanel" aria-labelledby="leaves-tab">
                    <div class="card">
                        <div class="card-body employee-detail-body fulls-card">
                            <div class="row">
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="form-group">
                                        {{Form::label('leave_type_id', __('Leave Type'), ['class' => 'form-label'])}}
                                <select name="leave_type_id" id="leave_type_id" required class="form-control select">
                                    <option value="">{{ __('Select Leave Type') }}</option>
                                    @foreach ($leavetypes as $leave)
                                    <option value="{{ $leave->id }}">{{ $leave->title }} (<p class="float-right pr-5">
                                            {{ $leave->days }}
                                        </p>)</option>
                                    @endforeach
                                </select>
                                </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="form-group">
                                        {{Form::label('Status', __('Status'), ['class' => 'form-label'])}}
                                        <div class="d-flex justify-content-between radio-check">
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="paid" value="paid" name="Status" class="custom-control-input">
                                                <label class="custom-control-label" for="paid">{{__('Paid')}}</label>
                                            </div>
                                            <div class="custom-control custom-radio custom-control-inline">
                                                <input type="radio" id="unpaid" value="unpaid" name="Status" class="custom-control-input">
                                                <label class="custom-control-label" for="unpaid">{{__('UnPaid')}}</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="form-group">
                                        {{ Form::label('alternate_person', __('Alternate Person'), ['class' => 'form-label']) }}
                                        {!! Form::text('alternate_person', null, [
                                        'class' => 'form-control',
                                        ]) !!}
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="form-group">
                                        {{ Form::label('leave_reason', __('Leave Reason'), ['class' => 'form-label']) }}
                                        {{ Form::select('leave_reason', [
                                    'sick_leave' => __('Sick Leave'),
                                    'domestic_problem' => __('Domestic Problem'),
                                    'maternity' => __('Maternity')
                                ], null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('Select Leave Reason')]) }}
                                    </div>
                                </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('start_date', null, ['class' => 'form-control', 'id' => 'start_date', 'required' => 'required']) }}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                            {{ Form::date('end_date', null, ['class' => 'form-control', 'id' => 'end_date', 'required' => 'required']) }}
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {{ Form::label('total_days', __('Total Days'), ['class' => 'form-label']) }}
                                            {{ Form::text('total_days', null, ['class' => 'form-control', 'id' => 'total_days', 'disabled' => 'disabled']) }}
                                        </div>
                                    </div>
                                </div>
                                <div class='d-flex justify-content-end'>
                                    <button id="submitBtnSection1" class="btn btn-primary mt-3">Save</button>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mx-3 table-responsive">
                                        <table class="">
                                            <thead>
                                                <tr class="table_heads">
                                                    <th width="200px">{{__('Action')}}</th>
                                                    <th>{{__('Leave') }}</th>
                                                    <th> {{__('Paid')}}</th>
                                                    <th> {{__('Type')}}</th>
                                                    <th> {{__('Application Date')}}</th>
                                                    <th> {{__('Leave From')}}</th>
                                                    <th> {{__('Leave To')}}</th>
                                                    <th> {{__('Total Days')}}</th>
                                                    <th> {{__('Reason')}}</th>
                                                    <th> {{__('Alternate')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($leaves as $leave)
                                                @if (Gate::check('edit employee') || Gate::check('delete employee'))
                                                <td>
                                                    @can('delete leave')
                                                    <div class="action-btn bg-danger ms-2">
                                                        {!! Form::open(['method' => 'DELETE', 'route' => ['leave.destroy',
                                                        $leave->id],'id'=>'delete-form-'.$leave->id]) !!}
                                                        <a href="#" class="mx-3 btn btn-sm  align-items-center bs-pass-para" 
                                                            data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}"
                                                            data-confirm="{{__('Are You Sure?').'|'.__('This action can not be undone. Do you want to continue?')}}"
                                                            data-confirm-yes="document.getElementById('delete-form-{{$leave->id}}').submit();">
                                                            <i class="ti ti-trash text-white"></i></a>
                                                        {!! Form::close() !!}
                                                    </div>
                                                    @endif
                                                </td>
                                                @endif
                                                <td>{{$leave->id}}</td>
                                                <td><input type="checkbox" name="" id="" @if (@$leave->$leaveType->status !== 'paid')
                                                    checked
                                                    @endif
                                                    ></td>
                                                <td>{{ !empty(\Auth::user()->getLeaveType($leave->leave_type_id))?\Auth::user()->getLeaveType($leave->leave_type_id)->title:'' }}
                                                </td>
                                                <td>{{ \Auth::user()->dateFormat($leave->applied_on )}}</td>
                                                <td>{{ \Auth::user()->dateFormat($leave->start_date ) }}</td>
                                                <td>{{ \Auth::user()->dateFormat($leave->end_date )  }}</td>
                                                <td>{{ $leave->total_leave_days }}</td>
                                                <td>{{ $leave->leave_reason }}</td>
                                                <td>{{!empty($leave->alternate_person) ? $leave->alternate_person : ''}}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    <div class="tab-pane fade" id="employee-childrens" role="tabpanel"
                        aria-labelledby="employee-childrens-tab">
                        <div class="card">
                            
                            <div class="col-md-12 table-responsive"  style="width:100%; margin-top: -9px; margin-left: 0px;">
                               <table id="siblingtable">
                                <thead>
                                    <tr class="table_heads">
                                        <th>Roll No</th>
                                        <th>Name</th>
                                        <th>Branch</th>
                                        <th>Class</th>
                                        <th>Fee</th>
                                        <th>Disount %</th>
                                        <th>Pay Fee</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>

                            </div>
                        </div>
                    </div>
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script>
                        function fetchAmount(studentId) {
                            if (!studentId) {
                                $('#amount_field').val(''); // Clear amount if no student selected
                                return;
                            }

                            $.ajax({
                                url: "{{ route('fetch_student_amount') }}", // Your route for fetching the amount
                                type: 'GET',
                                data: {
                                    id: studentId
                                },
                                success: function(response) {
                                    $('#amount_field').val(response.amount); // Populate amount field
                                },
                                error: function() {
                                    console.error('Failed to fetch amount.');
                                }
                            });
                        }

                        // Attach the function to the student select change event
                        $('#student_select').change(function() {
                            const studentId = $(this).val();
                            fetchAmount(studentId);
                        });
                    </script>
    @endif
@endsection
