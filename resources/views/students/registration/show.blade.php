@extends('layouts.admin')
@section('page-title')
    {{ __('Student Detail') }}
@endsection
@push('script-page')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
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
        document.getElementById('guardiancnic').addEventListener('keyup', function() {
            formatCNIC(this);
        });
		$(document).ready(function() {

            $('#profileInput').on('change', function() {
                let file = this.files[0];
                if (!file) return;
                $('#imageMsg').text('');

                // Validate size (1MB = 1024 * 1024)
                if (file.size > 500 * 1024) {
                    $('#imageError').text('Image must be less than 500KB');
                    $(this).val('');
                    return;
                } else {
                    $('#imageError').text('');
                }

                // Preview Image
                let reader = new FileReader();

                reader.onload = function(e) {
                    $('#profilePreview').attr('src', e.target.result);
                }

                reader.readAsDataURL(file);
            });

        });
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        var checkAllCheckbox = document.getElementById('checkAll');
        var rowCheckboxes = document.querySelectorAll('input[name="checked[]"]');
        checkAllCheckbox.addEventListener('change', function() {
            if (this.checked) {
                rowCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = true;
                });
            } else {
                rowCheckboxes.forEach(function(checkbox) {
                    checkbox.checked = false;
                });
            }
        });
        rowCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    checkAllCheckbox.checked = false;
                }
            });
        });
    </script>
    <script>
        function getCheckedRowData() {
            var challanDate = document.getElementById('challan_date').value;
            var issueDate = document.getElementById('issueDate').value;
            var dueDate = document.getElementById('dueDate').value;

            // Check if any of the date inputs is empty
            if (!challanDate || !issueDate || !dueDate) {
                alert('Please select all dates before proceeding.');
                return;
            }

            var checkedRowsData = [];
            var checkboxes = document.getElementsByName("checked[]");
            checkboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    var rowData = [];
                    var row = checkbox.closest("tr");
                    var cells = row.querySelectorAll("td");
                    cells.forEach(function(cell) {
                        var cellContent;
                        var input = cell.querySelector("input");
                        var label = cell.querySelector("label");
                        if (input && input.tagName.toLowerCase() === "input") {
                            cellContent = input.value;
                        } else if (label && label.tagName.toLowerCase() === "label") {
                            cellContent = label.textContent.trim();
                        } else {
                            cellContent = cell.textContent.trim();
                        }
                        rowData.push(cellContent);
                    });
                    checkedRowsData.push(rowData);
                }
            });
            // console.log("Data of Checked Rows:", checkedRowsData);
            // console.log("challan date:", challanDate);
            // console.log("issuedate:", issueDate);
            // console.log("due date:", dueDate);
            url = '{{ route('generateChallan') }}';
            var appurl = '{{ env('APP_URL') }}';
            var instview = '{{ route('installmentview', ':id') }}';
   
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            var xhr = new XMLHttpRequest();
            xhr.open("POST", url, true);
            xhr.setRequestHeader("Content-Type", "application/json");
            xhr.setRequestHeader("X-CSRF-TOKEN", csrfToken); // Move this line after xhr.open
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText); // Parse the JSON response
                        const basePath = window.location.pathname.split('/')[1];
                        window.location.href = instview.replace(':id', response.data.id); 
					} else if (xhr.status === 422) { // ✅ Correct way to check HTTP status
                        console.error('Challan already generated.');
                        var response = JSON.parse(xhr.responseText);
                        alert(response.message); // Optionally show a message to the user
                    } else {
                        console.error("Error generating challan:", xhr.responseText);
                    }
                }
            };
            var student_id = {{ $student->id }}
            var requestData = {
                checkedRowsData: checkedRowsData,
                challanDate: challanDate,
                issueDate: issueDate,
                dueDate: dueDate,
                student_id: student_id
            };
            xhr.send(JSON.stringify(requestData));

        }
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        document.getElementById('copyAddressBtn').addEventListener('click', function() {
            var ids = ['House', 'Street', 'Area', 'Sector', 'CityAddr', 'DistrictAddr'];
            ids.forEach(function(id) {
                var src = document.getElementById('present' + id);
                var dst = document.getElementById('permanent' + id);
                if (src && dst) {
                    dst.value = src.value;
                }
            });
        });
        $(document).ready(function() {
            function validateSection(sectionId) {
                var valid = true;
                $(sectionId + ' [required]:not(:disabled)').each(function() {
                    if (!$(this).val() || $(this).val().trim() == '') {
                        $(this).css('border-color', 'red');
                        valid = false;
                    } else {
                        $(this).css('border-color', '');
                    }
                });
                if (!valid) {
                    show_toastr('error', 'Please fill all required fields', 'error');
                }
                return valid;
            }
            $('[required]').each(function() {
                var $this = $(this);
                var label = $this.closest('div').find('label[for="' + $this.attr('id') + '"]');
                if (!label.length) {
                    label = $this.prevAll('label').first();
                }
                if (!label.length) {
                    label = $this.closest('.form-group').find('label').first();
                }
                if (label.length && !label.find('.required-star').length && !label.next('span[style*="color:red"], span[style*="color: red"]').length && label.text().indexOf('*') === -1) {
                    label.append('<span class="required-star" style="color:red"> *</span>');
                }
            });
            // Function to handle AJAX request
            function sendFormData(formData) {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                $.ajax({
                    url: '{{ route('registration.update', $student->id) }}',
                    method: 'PUT',
                    data: formData,
                    success: function(response) {
                        show_toastr('success', response.success, 'success');
                        window.location.reload();
                    },
                    error: function(xhr, status, error) {
                        // Handle error
                        show_toastr('error', error, 'error');
                        // alert('asdas')
                    }
                });
            }
            // $('#submitBtnSection1').click(function() {
            //     var formData = {
            //         sectionName: 'section1',
            //         sectionData: $('#section1 :input').serializeArray()
            //     };
            //     sendFormData(formData);
            // });
$('#submitBtnSection1').click(function() {
				if (!validateSection('#section1')) return;
                let sectionData = $('#section1 :input').serializeArray();

                let file = $('#profileInput')[0].files[0];

                // function that sends final payload
                function submitRequest(imageValue = null) {

                    if (imageValue) {
                        sectionData.push({
                            name: 'profile_image',
                            value: imageValue
                        });
                    }

                    var formData = {
                        sectionName: 'section1',
                        sectionData: sectionData
                    };

                    sendFormData(formData);
                }

                // if image exists → convert then send
                if (file) {

                    let reader = new FileReader();

                    reader.onload = function(e) {
                        submitRequest(e.target.result); // base64 image
                    };

                    reader.readAsDataURL(file);

                } else {
                    // no image → send immediately
                    submitRequest();
                }
            });
            $('#submitBtnSection2').click(function() {
			if (!validateSection('#section2')) return;
                var formData = {
                    sectionName: 'section2',
                    sectionData: $('#section2 :input').serializeArray()
                };
                sendFormData(formData);
            });

            $('#submitBtnSection3').click(function() {
				if (!validateSection('#section3')) return;
                var checkedRowsData = [];
                var uncheckedRowsData = [];
                var checkboxes = document.getElementsByName("checked[]");

                checkboxes.forEach(function(checkbox) {
                    var rowData = [];
                    var row = checkbox.closest("tr");
                    var cells = row.querySelectorAll("td");

                    cells.forEach(function(cell) {
                        var cellContent;
                        var input = cell.querySelector("input");
                        var label = cell.querySelector("label");
                        if (input && input.tagName.toLowerCase() === "input") {
                            if (input.classList.contains('discount') && input.disabled) {
                                cellContent = 0;
                            } else {
                                cellContent = input.value;
                            }
                            console.log(input);
                        } else if (label && label.tagName.toLowerCase() === "label") {
                            cellContent = label.textContent.trim();
                        } else {
                            cellContent = cell.textContent.trim();
                        }
                        rowData.push(cellContent);
                    });

                    if (checkbox.checked) {
                        checkedRowsData.push(rowData);
                    } else {
                        uncheckedRowsData.push(rowData);
                    }
                });

                // serializeArray() skips disabled controls. Build the payload first,
                // then explicitly include Adm Session and Adm Class even when disabled.
                var sectionData = $('#section3 :input').serializeArray();

                function setSectionValue(name, value) {
                    var existing = sectionData.find(function(item) {
                        return item.name === name;
                    });

                    if (existing) {
                        existing.value = (value == null ? '' : value);
                    } else {
                        sectionData.push({
                            name: name,
                            value: (value == null ? '' : value)
                        });
                    }
                }

                setSectionValue('adm_session', $('#adm_session').val());
                setSectionValue('adm_class', $('#adm_class').val());

                var formData = {
                    sectionName: 'section3',
                    checkedRows: checkedRowsData,
                    uncheckedRows: uncheckedRowsData,
                    sectionData: sectionData
                };
                sendFormData(formData);
            });
        });
        // $(document).ready(function() {
        //     $('#submitBtn').click(function() {
        //         var checkedRowsData = [];
        //         var checkboxes = document.getElementsByName("checked[]");
        //         checkboxes.forEach(function(checkbox) {
        //             if (checkbox.checked) {
        //                 var rowData = [];
        //                 var row = checkbox.closest("tr");
        //                 var cells = row.querySelectorAll("td");
        //                 cells.forEach(function(cell) {
        //                     var cellContent;
        //                     var input = cell.querySelector("input");
        //                     var label = cell.querySelector("label");
        //                     if (input && input.tagName.toLowerCase() === "input") {
        //                         cellContent = input.value;
        //                     } else if (label && label.tagName.toLowerCase() === "label") {
        //                         cellContent = label.textContent.trim();
        //                     } else {
        //                         cellContent = cell.textContent.trim();
        //                     }
        //                     rowData.push(cellContent);
        //                 });
        //                 checkedRowsData.push(rowData);
        //             }
        //         });
        //         console.log("Data of Checked Rows:", checkedRowsData);
        //         var formData = {};
        //         formData['checkedRows'] = checkedRowsData;
        //         $('.tab-pane').each(function(index) {
        //             var tabId = $(this).attr('id');
        //             var tabData = {};
        //             $(this).find('input, select, textarea').each(function() {
        //                 var fieldName = $(this).attr('name');
        //                 var fieldValue = $(this).val();
        //                 tabData[fieldName] = fieldValue;
        //             });
        //             formData[tabId] = tabData;
        //         });
        //         // console.log(formData);
        //         var csrfToken = $('meta[name="csrf-token"]').attr('content');
        //         $.ajaxSetup({
        //             headers: {
        //                 'X-CSRF-TOKEN': csrfToken
        //             }
        //         });
        //         $.ajax({
        //             url: '{{ route('registration.update', $student->id) }}',
        //             method: 'PUT',
        //             data: formData,
        //             success: function(response) {
        //               alert(response.success);
        //               window.location.reload();
        //             },
        //             error: function(xhr, status, error) {
        //                 // Handle error
        //             }
        //         });
        //     });
        // });

        function formatCellNumber(value) {
    // strip non‑digits
    value = value.replace(/\D/g, '');

    // limit total digits
    if (value.length > 44) {
      value = value.substring(0, 44);
    }

    // insert commas after 11th, 23rd, 35th digits
    // note: do this in descending order so indexes don't shift
    if (value.length > 35) {
      value = value.slice(0, 35) + ',' + value.slice(35);
    }
    if (value.length > 23) {
      value = value.slice(0, 23) + ',' + value.slice(23);
    }
    if (value.length > 11) {
      value = value.slice(0, 11) + ',' + value.slice(11);
    }

    return value;
  }

  document.addEventListener('DOMContentLoaded', function() {
    const cellInput = document.getElementById('fathercell');

    // 1️⃣ On page load, re‑format whatever came from the DB
    cellInput.value = formatCellNumber(cellInput.value);

    // 2️⃣ On each keyup, re‑format live
    cellInput.addEventListener('keyup', function() {
      this.value = formatCellNumber(this.value);
    });
  });
        document.getElementById('fatherphone').addEventListener('keyup', function() {
            // Remove all non-digit characters
            var value = this.value.replace(/\D/g, '');
            // Limit the length to 23 digits
            if (value.length > 11) {
                value = value.substring(0, 11);
            }
            this.value = value;
        });

        // Branch change handler - fetch classes and sessions
        $(document).on('change', '#branch', function() {
            var branchId = $(this).val();
            if (branchId) {
                // Fetch classes for this branch
                $.ajax({
                    url: '{{ route('branch.class') }}',
                    type: 'POST',
                    data: {
                        branch_id: branchId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(data) {
                        $('#adm_class').empty();
                        $('#adm_class').append('<option value="">Select Class</option>');
                        for (let index = 0; index < data.length; index++) {
                            $('#adm_class').append('<option value="' + data[index]['id'] + '">' + data[index]['name'] + '</option>');
                        }
                    }
                });

                // Fetch sessions for this branch (sessions are independent of class)
                $.ajax({
                    url: '{{ route('branch.session_class') }}',
                    type: 'POST',
                    data: {
                        id: branchId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(data) {
                        $('#adm_session').empty();
                        $('#adm_session').append('<option value="">Select Session</option>');
                        if (data.session && data.session.length > 0) {
                            for (let index = 0; index < data.session.length; index++) {
                                $('#adm_session').append('<option value="' + data.session[index]['id'] + '">' + data.session[index]['year'] + '</option>');
                            }
                        }
                    }
                });
            }
        });

        // Class change handler - should NOT affect session
        $(document).on('change', '#adm_class', function() {
            // Class change does not affect session - they are independent
            console.log('Class changed to: ' + $(this).val());
        });

        // Session change handler - should NOT affect class
        $(document).on('change', '#adm_session', function() {
            // Session change does not affect class - they are independent
            console.log('Session changed to: ' + $(this).val());
        });
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Detail') }}</li>
@endsection
@section('content')
    <style>
        .row>* {
            padding: 0px !important;
        }

        .head {
            padding: 10px;
            color: white;
        }

        .sec1>div,
        .sec2>div {
            border-radius: 20px;
        }

        .head {
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }
    </style>
<style>
            #paddingModal{
                display:none !important;
            }
        </style>
    <div class="card py-2 px-4 mt-4">
        <div class="container">
            <!-- <button id="submitBtn" class="btn btn-primary mt-3" style="position: relative; left: 90%;">Save</button> -->
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="section1-tab" data-toggle="tab" href="#section1" role="tab"
                        aria-controls="section1" aria-selected="true">General Details</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="section2-tab" data-toggle="tab" href="#section2" role="tab"
                        aria-controls="section2" aria-selected="false">Family & Guardian Details</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="section3-tab" data-toggle="tab" href="#section3" role="tab"
                        aria-controls="section3" aria-selected="false">Fee Structure</a>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="section1" role="tabpanel" aria-labelledby="section1-tab">
                    ` <div class="header px-3 row">
                        <div class="text col-12 col-md-6 col-lg-6">
                            <p style="color:red;">Fields with * Mandatory </p>
                            <div class="form-group">
                                {{ Form::label('regdate', __('Registration Date '), ['class' => 'form-label']) }}
                                {{ Form::date('regdate', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required', 'readonly' => 'readonly']) }}
                            </div>
                            <div class="form-group">
                                {{ Form::label('regby', __('Register By '), ['class' => 'form-label']) }}
                                {{ Form::text('regby', Auth::user()->name, ['class' => 'form-control', 'required' => 'required', 'disabled' => 'disabled']) }}
                            </div>
                            <div class="form-group">
                                {{ Form::label('student_status', __('Student Status'), ['class' => 'form-label']) }}
                                {{ Form::text('student_status', $student->student_status, ['class' => 'form-control', 'required' => 'required', 'disabled' => 'disabled']) }}
                            </div>
                            {{-- <div class="form-group">
                            {!! Form::label('description', __('Description'), ['class' => 'form-label']) !!}
                            {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3]) !!}
                        </div>
                        <div class="form-group">
                            {{ Form::label('pass_out_date', __('Passed Out Date'), ['class' => 'form-label']) }}
                            {{ Form::date('pass_out_date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        <div class="form-group">
                            <button class="btn btn-sm btn-success"><input type="checkbox" name="is_active" id="">Is
                                Active</button>
                            <button class="btn btn-sm btn-danger"><input type="checkbox" name="is_withdarawl" id="">Is
                                Withdrawal</button>
                        </div> --}}
                        </div>
                         <div class="img col-6 col-md-6 col-lg-6 d-flex justify-content-end align-items-start"
                            style="margin-top: 30px; display:grid !important;">

                            <label for="profileInput" style="cursor:pointer;">
                                @php
                                    $profile =
                                        $student->profile_image && file_exists(storage_path('app/public/' . $student->profile_image))
                                            ? asset('storage/' . $student->profile_image)
                                            : asset('assets/images/Student_profile.png');
                                @endphp

                                <img id="profilePreview" src="{{ $profile }}" alt="Profile"
                                    style="border:1px solid var(--primary); width:200px; height:200px; object-fit:cover;">
                                {{-- <img id="profilePreview" src="{{ asset('assets/images/Student_profile.png') }}"
                                    alt="Profile"
                                    style="border:1px solid var(--primary); width:200px; height:200px; object-fit:cover;"> --}}
                            </label>

                            <input type="file" id="profileInput" name="profile_image" accept="image/*"
                                style="display:none;">
                            <small class="text-primary" id="imageMsg">Upload Image</small>
                            <small class="text-danger" id="imageError"></small>
                        </div>
                    </div>
                    <div class="general-details col-12 col-md-12 col-lg-12">
                        <div class="mt-2 px-2">
                            <div class="form-group">
                                 <div class="d-flex" style="gap: 10px;">
                                    <div style="flex: 1;">
                                        {{ Form::label('branchname', __('Branch'), ['class' => 'form-label']) }}<span
                                            style="color: red"> *</span>
                                        <select name="branch" id="branch" class="form-control select" @if($student->student_status == 'Registered') @else disabled @endif>
                                            @foreach ($branches as $key => $values)
                                                <option value="{{ $key }}"
                                                    {{ $key == $student->owned_by ? 'selected' : '' }}>{{ $values }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div style="flex: 1;">
                                        {{ Form::label('admission_date', __('Admission Date '), ['class' => 'form-label']) }}
                                        {{ Form::date('admission_date', isset($student->enrollment) ? date('Y-m-d', strtotime($student->enrollment->adm_date)) : null, ['class' => 'form-control', 'readonly' => 'readonly']) }}

                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="d-flex" style="justify-content: space-between; gap: 10px;">
									<div style="flex: 1;">
                                        {{ Form::label('reg_no', __('Registration No'), ['class' => 'form-label']) }}
                                        {{ Form::text('reg_no', $student->reg_no, ['class' => 'form-control', 'required' => 'required', 'disabled' => 'disabled']) }}
                                    </div>
									<div style="flex: 1;">
                                        {{ Form::label('roll_no', __('Roll No'), ['class' => 'form-label']) }}
                                        {{ Form::text('roll_no', @$student->roll_no, ['class' => 'form-control' ,'disabled' => 'disabled']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="d-flex" style="justify-content: space-between; gap: 10px;">
									<div style="flex: 1;">
                                        {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                                        {{ Form::text('name', $student->stdname, ['class' => 'form-control', 'required' => 'required', 'style' => 'text-transform: uppercase;']) }}
                                    </div>
									<div style="flex: 1;">
                                        {{ Form::label('dob', __('D.O.B'), ['class' => 'form-label']) }}<span
                                            style="color: red">*</s>
                                            {{ Form::date('dob', $student->dob, ['class' => 'form-control', 'id' => 'dob', 'required' => 'required']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="d-flex" style="justify-content: space-between; gap: 10px;">
									<div style="flex: 1;">
                                        {!! Form::label('gender', __('Gender'), ['class' => 'form-label']) !!}
                                        <span style="color: red">*</span>
                                        {!! Form::select('gender', ['' => 'Select Gender', 'male' => 'Male', 'female' => 'Female'], $student->gender, [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                        ]) !!}
                                    </div>
									<div style="flex: 1;">
                                        {{ Form::label('birth_place', __('Birth Place'), ['class' => 'form-label']) }}
                                        {{ Form::text('birth_place', $student->birth_place, ['class' => 'form-control', 'required' => 'required']) }}
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="d-flex" style="justify-content: space-between; gap: 10px;">
									<div style="flex: 1;">
                                        {{ Form::label('religion', __('Religion'), ['class' => 'form-label']) }}
                                        {{ Form::text('religion', $student->religion, ['class' => 'form-control', 'required' => 'required']) }}
                                    </div>
									<div style="flex: 1;">
                                        {{ Form::label('nationality', __('Nationality'), ['class' => 'form-label']) }}
                                        {{ Form::text('nationality', $student->nationality, ['class' => 'form-control', 'required' => 'required']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="d-flex" style="justify-content: space-between; gap: 10px;">
                                    <div class="col-md-6">
                                        {!! Form::label('register_option', __('Register Option'), ['class' => 'form-label']) !!}<span style="color: red"> *</span>
                                        {!! Form::select('register_option', $registerOption, $selectedOptionId, [
                                            'class' => 'form-control',
                                            'required' => 'required',
                                        ]) !!}
                                    </div>
									<div style="flex: 1;">
                                        {{ Form::label('mobile_phone', __('Phone Mobile'), ['class' => 'form-label']) }}
                                        {{ Form::text('mobile_phone', $student->fatherphone, ['class' => 'form-control', 'required' => 'required', 'id' => 'fatherphone']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="d-flex" style="justify-content: space-between; gap: 10px;">
									<div style="flex: 1;">
                                        {{ Form::label('mobile_cell', __('Father Cell'), ['class' => 'form-label']) }}
<!-- in your Blade -->
<input type="text"
       name="mobile_cell"
       id="fathercell"
       value="{{ $student->fatherphone }}"
       class="form-control"
       required>
                                    </div>
									<div style="flex: 1;">
                                        {{ Form::label('prevschool', __('Previous School'), ['class' => 'form-label']) }}
                                        {{ Form::text('prevschool', $student->prevschool, ['class' => 'form-control', 'placeholder' => __('Previous School'), 'required' => 'required']) }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="d-flex" style="gap: 10px;">
                                    <div style="flex: 1;">
                                        {{ Form::label('email', __('Email'), ['class' => 'form-label']) }}
                                        {{ Form::email('email', $student->email, ['class' => 'form-control', 'placeholder' => __('Email')]) }}
                                    </div>
                                    <div style="flex: 1;">
                                        {{ Form::label('city', __('City'), ['class' => 'form-label']) }}
                                        {{ Form::text('city', $student->city, ['class' => 'form-control', 'required' => 'required']) }}
                                    </div>
                                    <div style="flex: 1;">
                                        {{ Form::label('district', __('District'), ['class' => 'form-label']) }}
                                        {{ Form::text('district', @$student->district, ['class' => 'form-control', 'required' => 'required']) }}
                                    </div>
                                </div>
                            </div>
                            @php
                                $presentParts = !empty($student->address) ? explode(', ', $student->address) : [];
                                $permanentParts = !empty($student->permanent_address) ? explode(', ', $student->permanent_address) : [];
                            @endphp
                            <div class="form-group">
                                <div class="row mx-0">
                                    <div style="flex: 1;">
                                        <h6>{{ __('Present Address') }}</h6>
                                        <div class="row mx-0">
                                            <div class="col-lg-11 mb-2">
                                                {{ Form::label('present_house', __('House #'), ['class' => 'form-label']) }}
                                                {{ Form::text('present_house', $presentParts[0] ?? '', ['class' => 'form-control', 'placeholder' => __('House No'), 'id' => 'presentHouse', 'style' => 'text-transform: uppercase;']) }}
                                            </div>
                                            <div class="col-lg-11 mb-2">
                                                {{ Form::label('present_street', __('Street'), ['class' => 'form-label']) }}
                                                {{ Form::text('present_street', $presentParts[1] ?? '', ['class' => 'form-control', 'id' => 'presentStreet', 'placeholder' => __('Street'), 'style' => 'text-transform: uppercase;']) }}
                                            </div>
                                            <div class="col-lg-11 mb-2">
                                                {{ Form::label('present_area', __('Area'), ['class' => 'form-label']) }}
                                                {{ Form::text('present_area', $presentParts[2] ?? '', ['class' => 'form-control', 'id' => 'presentArea', 'placeholder' => __('Area'), 'style' => 'text-transform: uppercase;']) }}
                                            </div>
                                            <div class="col-lg-11 mb-2">
                                                {{ Form::label('present_sector', __('Sector'), ['class' => 'form-label']) }}
                                                {{ Form::text('present_sector', $presentParts[3] ?? '', ['class' => 'form-control', 'id' => 'presentSector', 'placeholder' => __('Sector'), 'style' => 'text-transform: uppercase;']) }}
                                            </div>
                                            <div class="col-lg-11 mb-2">
                                                {{ Form::label('present_city_addr', __('City'), ['class' => 'form-label']) }}
                                                {{ Form::text('present_city_addr', $presentParts[4] ?? '', ['class' => 'form-control', 'id' => 'presentCityAddr', 'placeholder' => __('City'), 'style' => 'text-transform: uppercase;']) }}
                                            </div>
                                            <div class="col-lg-11 mb-2">
                                                {{ Form::label('present_district_addr', __('District'), ['class' => 'form-label']) }}
                                                {{ Form::text('present_district_addr', $presentParts[5] ?? '', ['class' => 'form-control', 'id' => 'presentDistrictAddr', 'placeholder' => __('District'), 'style' => 'text-transform: uppercase;']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <h6 class="mb-0">{{ __('Permanent Address') }}</h6>
                                            <button type="button" id="copyAddressBtn" class="btn btn-sm btn-info">Same As Present
                                                Address</button>
                                        </div>
                                        <div class="row mx-0">
                                            <div class="col-lg-11 mb-2">
                                                {{ Form::label('permanent_house', __('House #'), ['class' => 'form-label']) }}
                                                {{ Form::text('permanent_house', $permanentParts[0] ?? '', ['class' => 'form-control', 'id' => 'permanentHouse', 'placeholder' => __('House No'), 'style' => 'text-transform: uppercase;']) }}
                                            </div>
                                            <div class="col-lg-11 mb-2">
                                                {{ Form::label('permanent_street', __('Street'), ['class' => 'form-label']) }}
                                                {{ Form::text('permanent_street', $permanentParts[1] ?? '', ['class' => 'form-control', 'id' => 'permanentStreet', 'placeholder' => __('Street'), 'style' => 'text-transform: uppercase;']) }}
                                            </div>
                                            <div class="col-lg-11 mb-2">
                                                {{ Form::label('permanent_area', __('Area'), ['class' => 'form-label']) }}
                                                {{ Form::text('permanent_area', $permanentParts[2] ?? '', ['class' => 'form-control', 'id' => 'permanentArea', 'placeholder' => __('Area'), 'style' => 'text-transform: uppercase;']) }}
                                            </div>
                                            <div class="col-lg-11 mb-2">
                                                {{ Form::label('permanent_sector', __('Sector'), ['class' => 'form-label']) }}
                                                {{ Form::text('permanent_sector', $permanentParts[3] ?? '', ['class' => 'form-control', 'id' => 'permanentSector', 'placeholder' => __('Sector'), 'style' => 'text-transform: uppercase;']) }}
                                            </div>
                                            <div class="col-lg-11 mb-2">
                                                {{ Form::label('permanent_city_addr', __('City'), ['class' => 'form-label']) }}
                                                {{ Form::text('permanent_city_addr', $permanentParts[4] ?? '', ['class' => 'form-control', 'id' => 'permanentCityAddr', 'placeholder' => __('City'), 'style' => 'text-transform: uppercase;']) }}
                                            </div>
                                            <div class="col-lg-11 mb-2">
                                                {{ Form::label('permanent_district_addr', __('District'), ['class' => 'form-label']) }}
                                                {{ Form::text('permanent_district_addr', $permanentParts[5] ?? '', ['class' => 'form-control', 'id' => 'permanentDistrictAddr', 'placeholder' => __('District'), 'style' => 'text-transform: uppercase;']) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='d-flex justify-content-end'>
                                <button id="submitBtnSection1" class="btn btn-primary mt-3">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade mt-3" id="section2" role="tabpanel" aria-labelledby="section2-tab">
                    <div class="form-group">
                        <div class="d-flex" style="justify-content: space-between; gap: 10px;">
							<div style="flex: 1;">
                                {{ Form::label('father_name', __('Father Name'), ['class' => 'form-label']) }}
                                {{ Form::text('father_name', $student->fathername, ['class' => 'form-control', 'id' => 'fathername', 'required' => 'required']) }}
                            </div>
                            <div style="flex: 1;">
                                {!! Form::label('father_cnic', __('Father CNIC'), ['class' => 'form-label']) !!}
                                {!! Form::text('father_cnic', $student->fathercnic, [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'id' => 'fathercnic',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="d-flex" style="gap: 10px;">
                            <div style="flex: 1;">
                                {{ Form::label('father_occupation', __('Father Occupation'), ['class' => 'form-label']) }}
                                {{ Form::text('father_occupation', $student->fatherprofession, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                            <div style="flex: 1;">
                                {{ Form::label('father_email', __('Father Email'), ['class' => 'form-label']) }}
                                {{ Form::email('father_email', $student->father_email, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="d-flex" style="gap: 10px;">
                            <div style="flex: 1;">
                                {{ Form::label('home_phone', __('Phone Home'), ['class' => 'form-label']) }}
                                {{ Form::text('home_phone', $student->fathercell, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                            <div style="flex: 1;">
                                {{ Form::label('is_alive', __('Is Alive'), ['class' => 'form-label']) }}
                                {!! Form::select('is_alive', ['alive' => 'Alive', 'died' => 'Died'], $student->is_alive, [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="d-flex" style="gap: 10px;">
                            <div style="flex: 1;">
                                {{ Form::label('mother_name', __('Mother Name'), ['class' => 'form-label']) }}
                                {{ Form::text('mother_name', $student->mothername, ['class' => 'form-control', 'id' => 'mothername', 'required' => 'required']) }}
                            </div>
                            <div style="flex: 1;">
                                {!! Form::label('mother_cnic', __('Mother CNIC'), ['class' => 'form-label']) !!}
                                {!! Form::text('mother_cnic', $student->mothercnic, [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'id' => 'mothercnic',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="d-flex" style="gap: 10px;">
                            <div style="flex: 1;">
                                {{ Form::label('mother_occupation', __('Mother Occupation'), ['class' => 'form-label']) }}
                                {{ Form::text('mother_occupation', $student->motherprofession, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                            <div style="flex: 1;">
                                {{ Form::label('mother_email', __('Mother Email'), ['class' => 'form-label']) }}
                                {{ Form::email('mother_email', $student->mother_email, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="d-flex" style="gap: 10px;">
                            <div style="flex: 1;">
                                {{ Form::label('guardian_name', __('Guardian Name'), ['class' => 'form-label']) }}
                                {{ Form::text('guardian_name', $student->guardianname, ['class' => 'form-control', 'id' => 'guardianname']) }}
                            </div>
                            <div style="flex: 1;">
                                {{ Form::label('guardian_relation', __('Guardian Relation'), ['class' => 'form-label']) }}
                                {{ Form::text('guardian_relation', $student->guardianrelation, ['class' => 'form-control', 'id' => 'guardianname']) }}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="d-flex" style="gap: 10px;">
                            <div style="flex: 1;">
                                {{ Form::label('guardian_occupation', __('Guardian Occupation'), ['class' => 'form-label']) }}
                                {{ Form::text('guardian_occupation', $student->guardianprofession, ['class' => 'form-control']) }}
                            </div>
                            <div style="flex: 1;">
                                {!! Form::label('guardian_cnic', __('Guardian CNIC'), ['class' => 'form-label']) !!}
                                {!! Form::text('guardian_cnic', $student->guardiancnic, [
                                    'class' => 'form-control',
                                    'id' => 'guardiancnic',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="d-flex" style="gap: 10px;">
                            <div style="flex: 1;">
                                {{ Form::label('guardian_phone', __('Guardian Phone'), ['class' => 'form-label']) }}
                                {{ Form::text('guardian_phone', $student->guardianphone, ['class' => 'form-control']) }}
                            </div>
                            <div style="flex: 1;">
                                {!! Form::label('guardian_address', __('Guardian Address'), ['class' => 'form-label']) !!}
                                {!! Form::textarea('guardian_address', $student->guardianaddress, ['class' => 'form-control', 'rows' => 3]) !!}
                            </div>
                        </div>
                    </div>
                    {{-- <div class="form-group">
                    <div class="d-flex" style="gap: 10px;">
                        <div class="col-md-6 col-lg-6">
                            {{ Form::label('no_of_brothers', __('No of Brothers'), ['class' => 'form-label']) }}
                            {!! Form::select('no_of_brothers', [ '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5'
                            =>
                            '5'], null, ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                        <div class="col-md-6 col-lg-6">
                            {{ Form::label('no_of_sisters', __('No of Sisters'), ['class' => 'form-label']) }}
                            {!! Form::select('no_of_sisters', [ '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5'
                            =>
                            '5'],
                            null, ['class' => 'form-control', 'required' => 'required']) !!}
                        </div>
                    </div>
                </div> --}}
                    <div class="form-group">
                        {!! Form::label('remarks', __('Remarks'), ['class' => 'form-label']) !!}
                        {!! Form::textarea('remarks', $student->remarks, ['class' => 'form-control', 'rows' => 6]) !!}
                    </div>
                    <div class='d-flex justify-content-end'>
                        <button id="submitBtnSection2" class="btn btn-primary mt-3">Save</button>
                    </div>
                </div>
                <div class="tab-pane fade mt-3" id="section3" role="tabpanel" aria-labelledby="section3-tab">
                    <div class="form-group">
                        <div class="d-flex" style="gap: 10px;">
                            {{-- <div style="flex: 1;">
                            {!! Form::label('school_address', __('School Address'), ['class' => 'form-label']) !!}
                            {!! Form::textarea('school_address', null, ['class' => 'form-control', 'rows' =>1]) !!}
                        </div> --}}
                            <div style="flex: 1;">
                                {{ Form::label('adm_session', __('Adm Session'), ['class' => 'form-label']) }}
                                <select name="adm_session" id="adm_session" class="form-control select" required @if($student->student_status == 'Registered') @else disabled @endif>
                                    @foreach ($sessions as $key => $values)
                                        <option value="{{ $key }}"
                                            {{ $key == $student->session_id ? 'selected' : '' }}>{{ $values }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="flex: 1;">
                                {{ Form::label('adm_class', __('Adm Class'), ['class' => 'form-label']) }}
                                 <select name="adm_class" id="adm_class" class="form-control select" required @if($student->student_status == 'Registered') @else disabled @endif>
                                    @foreach ($classes as $key => $values)
                                        <option value="{{ $key }}"
                                            {{ $key == $student->reg_class ? 'selected' : '' }}>{{ $values }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="d-flex" style="gap: 10px;">
                            <div style="flex: 1;">
                                {{ Form::label('current_session', __('Current Session'), ['class' => 'form-label']) }}
                                {!! Form::text(
                                    'current_session', optional(optional($student->enrollment)->session)->year ?? optional($student->session)->year ?? '',
                                    ['class' => 'form-control', 'required' => 'required', 'disabled' => 'disabled'],
                                ) !!}
                            </div>
                            <div style="flex: 1;">
                                {{ Form::label('current_class', __('Current Class'), ['class' => 'form-label']) }}
                                {!! Form::text(
                                    'current_class', optional($student->class)->name ?? '',
                                    ['class' => 'form-control', 'required' => 'required', 'disabled' => 'disabled'],
                                ) !!}
                            </div>
                        </div>
                    </div>
                    @if (!empty($showJunJulFeeExempt))
                        @php
                            $canUncheckJunJulExempt = \Auth::user()->type === 'company';
                            $disableJunJulExempt = !$canUncheckJunJulExempt && !empty($student->fee_exempt_jun_jul);
                        @endphp
                        <div class="form-group">
                            <div class="form-check">
                                @if ($canUncheckJunJulExempt)
                                    <input type="hidden" name="fee_exempt_jun_jul" value="0">
                                @endif
                                <input type="checkbox" class="form-check-input" id="fee_exempt_jun_jul"
                                    name="fee_exempt_jun_jul" value="1"
                                    {{ !empty($student->fee_exempt_jun_jul) ? 'checked' : '' }}
                                    {{ $disableJunJulExempt ? 'disabled' : '' }}>
                                <label class="form-check-label" for="fee_exempt_jun_jul">
                                    {{ __('Fee Exempt (Jun-Jul)') }}
                                </label>
                            </div>
                        </div>
                    @endif
                    <div class="form-group">
                        <div class="d-flex" style="gap: 10px;">
                            <div style="flex: 1;">
                                {{ Form::label('section', __('Section'), ['class' => 'form-label']) }}
                                {!! Form::text('section', optional(optional($student->enrollment)->section)->name ?? '',
                                   [ 'class' => 'form-control','disabled' => 'disabled',]) !!}
                            </div>
                            <div style="flex: 1;">
                                {{ Form::label('discount_policy', __('Discount Policy'), ['class' => 'form-label']) }}
                                {{ Form::text('discount_policy', @$concession->concession->title, ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                {{ Form::text('discount_policy_id', @$concession->concession_id, ['hidden' => 'hidden', 'class' => 'form-control']) }}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex " style="justify-content: space-between;">
                    <div class="form-group">
                        <a href="{{ route('student.fee_generate', $student->id) }}"
                            class="btn btn-sm btn-success">Generate
                            fee structure for this class in this session</a>
                    </div>
                    <div class=''>
                        <button id="submitBtnSection3" class="btn btn-primary m-1">Save</button>
                    </div>
                    </div>
                    @if ($studentchallanexist && Auth::user()->type !== 'company')

                        {{-- Existing challan: non-company users cannot generate another --}}
                        <div class="card py-4 px-4">
                            <div>
                                Admission challan already exists in the system.
                    
                                @if (!empty($studentchallanexist->challanNo))
                                    Challan No:
                                    <strong>{{ $studentchallanexist->challanNo }}</strong>
                                @endif
                            </div>
                        </div>
                    
                    @else
                    
                        {{-- Show existing challan information to company user --}}
                        @if ($studentchallanexist && Auth::user()->type === 'company')
                            <div class="card py-4 px-4">
                                <div>
                                    Admission challan already exists in the system.
                                    Challan No:
                                    <strong>{{ $studentchallanexist->challanNo }}</strong>
                    
                                    <br>
                    
                                    <span class="text-warning">
                                        As a company user, you are allowed to create another admission challan.
                                    </span>
                                </div>
                            </div>
                        @endif
                    
                        {{-- 
                            Form is available when:
                            1. No admission challan exists, for any user.
                            2. Admission challan exists, but logged-in user is company.
                        --}}
                        <div class="card py-4 px-4">
                            <div class="row" style="gap: 20px; align-items: center;">
                    
                                <div class="col-md-3 col-lg-3">
                                    {!! Form::label('challan_date', __('Billing Month'), [
                                        'class' => 'form-label',
                                    ]) !!}
                    
                                    <span style="color: red;">
                                        &nbsp;(for the month date)
                                    </span>
                    
                                    {!! Form::month('challan_date', date('Y-m'), [
                                        'class' => 'form-control',
                                        'id' => 'challan_date',
                                        'required' => true,
                                    ]) !!}
                                </div>
                    
                                <div class="col-md-3 col-lg-3">
                                    {!! Form::label('issueDate', __('Issue Date'), [
                                        'class' => 'form-label',
                                    ]) !!}
                    
                                    <span style="color: red;">*</span>
                    
                                    {!! Form::date('issueDate', date('Y-m-d'), [
                                        'class' => 'form-control',
                                        'id' => 'issueDate',
                                        'required' => true,
                                    ]) !!}
                                </div>
                    
                                <div class="col-md-3 col-lg-3">
                                    {!! Form::label('dueDate', __('Due Date'), [
                                        'class' => 'form-label',
                                    ]) !!}
                    
                                    <span style="color: red;">*</span>
                    
                                    {!! Form::date(
                                        'dueDate',
                                        date('Y-m-d', strtotime('+3 days')),
                                        [
                                            'class' => 'form-control',
                                            'id' => 'dueDate',
                                            'required' => true,
                                        ],
                                    ) !!}
                                </div>
                    
                                <div
                                    class="col-md-2 col-lg-2"
                                    style="position: relative; top: 10px;"
                                >
                                    <button
                                        type="button"
                                        onclick="getCheckedRowData()"
                                        class="btn btn-primary"
                                    >
                                        Generate Admission Challan
                                    </button>
                                </div>
                    
                            </div>
                        </div>
                    
                    @endif
                    <table class="">
                        <thead>
                            <tr>
                                {{-- <th>Update</th> --}}
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Discount (%)</th> <!-- Updated heading -->
                                <th>Net Amount</th>
                                <th><input type="checkbox" id="checkAll"></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($classfee as $fee)
                                <tr>
                                    @php
                                        $i=0;
                                        $headNames = ['TUITION FEE', 'ADMISSION FEE', 'SECURITY FEE'];
                                    @endphp
                                    @if (!empty($fee->feehead->fee_head) && in_array($fee->feehead->fee_head, $headNames))
                                        @php
                                            $i = 1;
                                        @endphp
                                    @endif
                                    <td>
                                        <label>{{ !empty($fee->feehead->fee_head) ? $fee->feehead->fee_head : '-' }}</label>
                                    </td>
                                    <td>
                                        <input type="number" name="amount" class="form-control amount"
                                            value="{{ !empty($fee->amount) ? $fee->amount : '0' }}" disabled {{ $i == 1 ? 'disabled' : '' }}>
                                    </td>
                                    <td>
    @php
        $concessionPolicyHeads = null;
        $amount = !empty($fee->amount) ? $fee->amount : 0;
        $discountPercentage = 0;
        $discountedAmount = 0; // Initialize this variable

        // Check if student has concession
        if ($concession) {
            $concessionPolicyHeads = \App\Models\ConcessionPolicyHead::where(
                'concession_id',
                $concession->concession_id,
            )->where('head_id', @$fee->head_id)->first();
        }

        // Helper function to clean decimal display
        $cleanPercentageDisplay = function($value) {
            if ($value == 0) {
                return '0';
            }
            $floatVal = (float)$value;
            // If whole number, show without decimals
            if ($floatVal == floor($floatVal)) {
                return (string)intval($floatVal);
            }
            // Otherwise remove trailing zeros
            return rtrim(rtrim(number_format($floatVal, 8, '.', ''), '0'), '.');
        };

        // Determine discount percentage
        if ($concessionPolicyHeads && $concessionPolicyHeads->percentage > 0) {
            // Use concession policy percentage
            $discountPercentage = $concessionPolicyHeads->percentage;
        } else {
            // Use individual fee structure discount
            $discountPercentage = !empty($fee->discount) ? $fee->discount : 0;
        }

        // Calculate the discounted amount (final amount after discount)
        $discountAmount = ($amount * $discountPercentage) / 100;
        $discountedAmount = $amount - $discountAmount;
        
        // Clean percentage for display
        $displayPercentage = $cleanPercentageDisplay($discountPercentage);
    @endphp

    @if ($concessionPolicyHeads && $concessionPolicyHeads->percentage > 0)
        {{-- Policy has discount for this head - use policy percentage and disable input --}}
        <label for="discount" class="discountLabel" style="color: red;">
            Discounted Amount : {{ number_format($discountAmount, 2) }}
        </label>
        <input type="text" class="form-control discount"
            value="{{ $displayPercentage }}"
            disabled
            {{ $i == 1 ? 'disabled' : '' }}
        >
    @else
        {{-- No policy OR policy has 0% for this head - use fee structure discount and enable input --}}
        <label for="discount" class="discountLabel" style="color: red;">
            Discounted Amount : {{ number_format($discountAmount, 2) }}
        </label>
        <input type="text" class="form-control discount" 
            value="{{ $cleanPercentageDisplay(!empty($fee->discount) ? $fee->discount : '0') }}" 
            {{ $i == 1 ? 'disabled' : '' }}
        >
    @endif
</td>

                                    <td>
    <input type="text"
        class="form-control discounted-amount"
        value="{{ number_format($discountedAmount, 2, '.', '') }}"
        disabled>
</td>


                                    <td>
                                        <input type="checkbox" name="checked[]"
                                            value="{{ !empty($fee->account_id) ? $fee->account_id : '-' }}"
                                            @if(\Auth::user()->type != 'company')
                                            {{ $fee->checked_status == 1 ? 'checked' : '' }} {{ $i == 1 ? 'disabled' : '' }}
                                            @else
                                           {{ $fee->checked_status == 1 ? 'checked' : '' }} 
                                        @endif
                                           >

                                    </td>
                                    <td style="display: none;">
                                        <input type="hidden" name="headid"
                                            value="{{ !empty($fee->feehead->id) ? $fee->feehead->id : '-' }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>

                    <div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        class DiscountCalculator {
            constructor() {
                this.discountInputs = document.querySelectorAll('.discount');
                this.amountInputs = document.querySelectorAll('.amount');
                this.discountedAmountInputs = document.querySelectorAll('.discounted-amount');
                this.discountLabel = document.querySelectorAll('.discountLabel');
                this.initialize();
            }
            initialize() {
                this.discountInputs.forEach((discountInput, index) => {
                    discountInput.addEventListener('input', () => this.calculateDiscount(index));
                });
            }
            calculateDiscount(index) {
                this.discountLabel[index].innerHTML = 'Discounted Amount : ' + this.amountInputs[index].value * (this.discountInputs[index].value / 100);
                let discountPercentage = parseFloat(this.discountInputs[index].value) || 0;
                if (discountPercentage > 100) {
                    discountPercentage = 100;
                    this.discountInputs[index].value = discountPercentage;
                }
                const amount = parseFloat(this.amountInputs[index].value) || 0;
                const discount =amount * (discountPercentage / 100);
                const finalAmount = amount - discount;
                this.discountedAmountInputs[index].value = finalAmount < 0 ? 0 : finalAmount;
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            new DiscountCalculator();
        });
    </script>
@endsection