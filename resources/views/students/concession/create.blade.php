{{ Form::open(['url' => 'concession', 'id' => 'concessionForm']) }}
<div class="modal-body">
    <style>
        .custom-select-display{
            word-break: break-all;
        }
        .custom-select-option{
            word-wrap: break-word;
        }
    </style>
    <div class="row">
        @for ($i = 0; $i < 3; $i++)
            <div class="col-4">
                <div class="row">
                    <div class="col-7">
                        <div class="form-group">
                            {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="form-group">
                            {{ Form::label('concession', __('Concession %'), ['class' => 'form-label']) }}
                        </div>
                    </div>
                </div>
            </div>
        @endfor
        @foreach ($heads as $account)
            @if ($account->status == 0)
                {{ Form::hidden('title_id[]', $account->id, ['class' => 'form-control', 'placeholder' => __('Enter Title')]) }}
                {{ Form::hidden('head_name[]', $account->fee_head, ['class' => 'form-control', 'placeholder' => __('Enter Title'), 'readonly' => 'readonly']) }}
                {{ Form::hidden('concession[]', 0, ['class' => 'form-control', 'step' => 'any']) }}
            @else
                <div class="col-4">
                    <div class="row">
                        <div class="col-7">
                            <div class="form-group">
                                {{ Form::hidden('title_id[]', $account->id, ['class' => 'form-control', 'placeholder' => __('Enter Title')]) }}
                                {{ Form::text('head_name[]', $account->fee_head, ['class' => 'form-control', 'placeholder' => __('Enter Title'), 'readonly' => 'readonly']) }}
                            </div>
                        </div>
                        <div class="col-5">
                            <div class="form-group">
                                {{ Form::number('concession[]', 0, ['class' => 'form-control', 'step' => 'any', 'min' => '0']) }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
        <div class="col-4" style="float: right;">
            {{ Form::button('Search', ['id' => 'search', 'class' => 'btn btn-sm btn-primary']) }}
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-4">
            <div class="form-group" id="re">
                {{ Form::label('concession_id', __('Concession Policy'), ['class' => 'form-label']) }}<span
                    style="color: red"> *</span>
                {{-- {{ Form::select('concession_id', $concession_policy, null, ['class' => 'form-control select js-searchBox' ,'id' => 'conc']) }} --}}
                {{ Form::select('concession_id', $concession_policy, null, ['class' => 'form-control  custom-select', 'id' => 'conc']) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('period_from', __('Period From'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::date('period_from', null, ['class' => 'form-control ', 'placeholder' => __('Enter Period From'), 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('period_to', __('Period To'), ['class' => 'form-label']) }}
                {{ Form::date('period_to', null, ['class' => 'form-control ', 'placeholder' => __('Enter Period To')]) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">

                {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select', 'required' => 'required', 'id' => 'branch']) }}

                {{-- <!-- // {{ Form::label('student_id', __('Students'),['class'=>'form-label']) }}<span style="color: red"> *</span>
               // {{ Form::select('student_id', ['' => 'Select  a Student'], null, ['class' => 'form-control select','id' => 'class_students']) }}
              --> --}}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('class_id', __('Class'), ['class' => 'form-label']) }}<span style="color: red"> *</span>
                {{ Form::select('class_id', $classes, null, ['class' => 'form-control select', 'id' => 'class_id', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group">
                {{ Form::label('concession_type', __('Concession Type'), ['class' => 'form-label']) }}<span
                    style="color: red"> *</span>
                {{ Form::select('concession_type', ['regular' => 'Regular Concession', 'registration' => 'Registration Concession'], null, ['class' => 'form-control select', 'id' => 'concession_type', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-4">
            <div class="form-group" id="std_names">
                {{ Form::label('student_id', __('Students'), ['class' => 'form-label']) }}<span style="color: red">
                    *</span>
                {{ Form::select('student_id', $students ?? [], null, ['class' => 'form-control select', 'id' => 'student_select', 'required' => 'required']) }}
            </div>
        </div>
        <div class="col-8">
            <div class="form-group">
                {{ Form::label('bill_remarks', __('Bill Remarks'), ['class' => 'form-label']) }}
                {{ Form::textarea('bill_remarks', null, ['class' => 'form-control', 'placeholder' => __('Enter Bill Remarks'), 'rows' => 2]) }}
            </div>
        </div>

        <div class="col-12">
            <div id="student-details" class="mt-3"></div>
        </div>
        <div class="col-4 av d-none">
            <div class="form-group">
                {{ Form::label('cancle_date', __('Prev Cancle Date'), ['class' => 'form-label']) }}<span
                    style="color: red"> *</span>
                {{ Form::date('cancle_date', null, ['class' => 'form-control ', 'placeholder' => __('Enter Date')]) }}
            </div>
        </div>
        <div class="col-8 av d-none">
            <div class="form-group">
                {{ Form::label('cancle_remarks', __('Prev Cancle Remarks'), ['class' => 'form-label']) }}
                {{ Form::textarea('cancle_remarks', null, ['class' => 'form-control', 'placeholder' => __('Enter Cancle Remarks'), 'rows' => 2]) }}
            </div>
        </div>

    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Submit') }}" class="btn  btn-outline-primary">
</div>

{{ Form::close() }}

<script>
    document.getElementById('search').addEventListener('click', function() {
        console.log('search');
        
        var form = document.getElementById('concessionForm');
        var formData = new FormData(form);

        fetch('{{ url('concession_list') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Rebuild the container HTML fresh
                document.getElementById('re').innerHTML = `
        <label for="concession_id" class="form-label">
            {{ __('Concession Policy') }} <span style="color:red;">*</span>
        </label>
        <select name="concession_id" id="conc" class="form-control custom-select"></select>
    `;

                const dropdown = document.getElementById('conc');
                dropdown.innerHTML = '';

                if (!data || data.length === 0) {
                    show_toastr('danger', 'No Concession Policy Found', 'danger');
                    return;
                }

                // Separate exact and partial matches
                const exact = data.filter(p => p.is_exact);
                const partial = data.filter(p => !p.is_exact);

                if (exact.length > 0) {
                    const eg = document.createElement('optgroup');
                    eg.label = '✔ Exact Match';
                    exact.forEach(policy => {
                        const opt = document.createElement('option');
                        opt.value = policy.id;
                        opt.textContent = policy.order_no + '  -  ' + policy.title;
                        eg.appendChild(opt);
                    });
                    dropdown.appendChild(eg);
                }

                if (partial.length > 0) {
                    const pg = document.createElement('optgroup');
                    pg.label = '~ Partial Match';
                    partial.forEach(policy => {
                        const opt = document.createElement('option');
                        opt.value = policy.id;
                        opt.textContent = policy.order_no + '  -  ' + policy.title;
                        pg.appendChild(opt);
                    });
                    dropdown.appendChild(pg);
                }

                // Auto-select first exact match
                if (exact.length > 0) {
                    dropdown.value = exact[0].id;
                }

                // ── Re-initialize your custom select plugin ──────────────────
                // Destroy any existing instance first to avoid double-binding,
                // then reinit on the freshly populated element.
                const $conc = $('#conc');

                // If your plugin attaches itself via a class name, cover all common
                // plugin patterns below — keep only the one that matches yours:

                // Pattern 1 — Select2
                if ($.fn.select2) {
                    try {
                        $conc.select2('destroy');
                    } catch (e) {}
                    $conc.select2();
                }

                // Pattern 2 — Chosen
                if ($.fn.chosen) {
                    try {
                        $conc.chosen('destroy');
                    } catch (e) {}
                    $conc.chosen();
                }
                // Pattern 4 — plain CustomSelect / bootstrap-select
                if ($.fn.selectpicker) {
                    try {
                        $conc.selectpicker('destroy');
                    } catch (e) {}
                    $conc.selectpicker();
                }
            });
    });
</script>

<script>
    function classStudents(id) {
        var type = $('#concession_type').val();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('class.students') }}",
            type: "POST",
            data: {
                class_id: id,
                type: type
            },
            dataType: 'json',
            success: function(result) {
                console.log(result);
                if (result.status == 'success') {
                    var s = ` {{ Form::label('student_id', __('Students'), ['class' => 'form-label']) }}<span style="color: red">
                                    *</span><select name="student_id"  class="form-control select " id="student_select" required>
                                    <option value="all" selected >All Students</option> `;


                    for (var id in result.students) {
                        if (result.students.hasOwnProperty(id)) {
                            s += `<option value="` + id + `">` + result.students[id] + `</option>`;
                            // $('#student_select').append($('<option>', { value: id, text: result.students[id] }));
                        }
                    }
                    s += `</select>`;
                    $('#std_names').empty();
                    $('#std_names').html(s);
                    if (result.length != 0) {
                        $('#student_select').addClass('js-searchBox');
                        JsSearchBox();
                        updateWidths();
                    }
                    $('#student_select').val('all');
                }

            }
        });
    }

    $(document).on('change', '#class_id', function() {
        var classId = $(this).val();
        $('.av').addClass('d-none');
        $('#student-details').empty('');
        if (classId) {
            classStudents(classId);
        } else {
            $('#student_select').empty();
        }
    });
    $(document).on('change', '#concession_type', function() {
        var classId = $('#class_id').val();
        console.log(classId);
        if (classId) {
            classStudents(classId);
        } else {
            $('#student_select').empty();
        }
    });

    $(document).on('change', '#branch', function() {
        var branch = $(this).val();
        $.ajax({
            url: '{{ route('branch.class') }}',
            type: 'POST',
            data: {
                "branch_id": branch,
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {
                $('#class_id').empty();
                $('#class_id').append(
                    '<option value="" selected>{{ __('Select Class') }}</option>');
                for (let index = 0; index < data.length; index++) {
                    $('#class_id').append('<option value="' + data[index]['id'] + '">' + data[index]
                        ['name'] + '</option>');
                }
            }
        });
    });

    $(document).on('change', '#student_select', function() {
        var studentId = this.value;
        $('.av').addClass('d-none');
        $('#student-details').empty('');
        if (studentId) {
            fetchStudentDetails(studentId);
        } else {
            document.getElementById('student-details').innerHTML = '';
        }
    });

    function fetchStudentDetails(studentId) {
        fetch('{{ url('concession/student-detail') }}/' + studentId)
            .then(response => response.json())
            .then(data => {
                displayStudentDetails(data);
            })
            .catch(error => console.error('Error:', error));
    }

    function displayStudentDetails(data) {
        var detailsDiv = document.getElementById('student-details');
        if (data) {
            detailsDiv.innerHTML =
                `<div style="display:grid; grid-template-columns:auto auto auto;"><p><strong>Student Name:</strong>${data.data.stdname}</p><p><strong>Father Name:</strong>${data.data.fathername}</p><p><strong>Father CNIC:</strong>${data.data.fathercnic}</p><p><strong>Email:</strong>${data.data.email}</p><p><strong>Roll No:</strong>${data.enroll.enrollId}</p><p><strong>Class:</strong>${data.class}</p><p><strong>Section:</strong>${data.section}</p><p><strong>Concession:</strong>${data.concession}</p></div>`;
            if (data.concession != 'No Previous Concession') {
                $('.av').removeClass('d-none');
            }
        } else {
            detailsDiv.innerHTML = '<p>No details available for this student.</p>';
        }
    }
</script>
{{-- <script>
    JsSearchBox();

    function updateWidths() {
        // Get all elements with the class 'refineText'
        var refineTextElements = document.querySelectorAll('.refineText');
        // Iterate over each 'refineText' element
        refineTextElements.forEach(function(refineText) {
            // Get the parent element of the current refineText element
            var parentElement = refineText.parentNode;

            // Check if the parent element exists and has a valid width
            if (parentElement && parentElement.offsetWidth) {
                // Get the width of the parent element
                var parentWidth = parentElement.offsetWidth;
                // Set the width of the refineText element to match its parent's width
                refineText.style.width = parentWidth + 'px';
            }
        });

        // Get the width of the first refineText element's parent
        var parentElement = refineTextElements[0].parentNode;
        var parentWidth = parentElement.offsetWidth;
        // Apply the width to all elements with the class 'searchBoxElement'
        var searchBoxElements = document.querySelectorAll('.searchBoxElement');
        searchBoxElements.forEach(function(element) {
            element.style.width = parentWidth + 'px';
        });

        // Set the border color for the first refineText element
        refineTextElements[0].style.borderColor = '#100773';
    }

    // Call the function initially to set the initial widths based on the parent's width
    setTimeout(function() {
        updateWidths();
    }, 1000); // Delay of 1 second (1000 milliseconds)
</script> --}}
