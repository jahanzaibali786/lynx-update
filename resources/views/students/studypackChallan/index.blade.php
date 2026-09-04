@extends('layouts.admin')
@section('page-title')
    {{ __('Manage StudyPack Challans') }}
@endsection

@php
    $route = 'studypackchallan.index';
@endphp

@push('script-page')
    <script>
        var bulkLoaderStyle = document.createElement('style');
        bulkLoaderStyle.innerHTML = `
            @keyframes studypackBulkSpin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(bulkLoaderStyle);

        function showBulkProcessingLoader(message) {
            var overlay = document.getElementById('studypack-bulk-loader');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'studypack-bulk-loader';
                overlay.style.cssText = 'position:fixed; inset:0; z-index:999999; display:flex; align-items:center; justify-content:center; flex-direction:column; background:rgba(0,0,0,0.74); color:#fff;';
                overlay.innerHTML = '<div style="width:56px; height:56px; border:5px solid rgba(255,255,255,0.2); border-top-color:#fff; border-radius:50%; animation:studypackBulkSpin 1s linear infinite;"></div><div id="studypack-bulk-loader-text" style="margin-top:16px; font-weight:700; font-size:16px;">Preparing downloads...</div><div id="studypack-bulk-loader-subtext" style="margin-top:6px; font-size:13px; opacity:0.9;">Please wait while the selected challans are processed.</div>';
                document.body.appendChild(overlay);
            }
            overlay.style.display = 'flex';
            var textEl = document.getElementById('studypack-bulk-loader-text');
            var subTextEl = document.getElementById('studypack-bulk-loader-subtext');
            if (textEl) {
                textEl.textContent = message || 'Preparing downloads...';
            }
            if (subTextEl) {
                subTextEl.textContent = 'Please wait while the selected challans are processed.';
            }
        }

        function hideBulkProcessingLoader() {
            var overlay = document.getElementById('studypack-bulk-loader');
            if (overlay) {
                overlay.style.display = 'none';
            }
        }

        $(document).ready(function() {
            var branchId = $('#branches_select').val();
            if (branchId) {
                branchcustomer(branchId);
            }
        });
        $(document).on('change', '#class_select', function() {
            var classId = $(this).val();
            if (classId) {
                classStudents(classId);
            }
        });

        function branchcustomer(id) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('branch.session_class') }}",
                type: "POST",
                data: {
                    id: id,
                },
                dataType: 'json',
                success: function(result) {
                    if (result.status == 'success') {
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
                        $classSelect.append($('<option>', {
                            value: 'all',
                            text: 'All Class'
                        }));
                        for (var j = 0; j < result.class.length; j++) {
                            var cls = result.class[j];
                            $classSelect.append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                        $classSelect.addClass('custom-select').show();
                        if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                            window.CustomSelect.create($classSelect[0]);
                        }
                    }
                }
            });
        }

        function classStudyPack(id) {
            var session = $('#sessionselect').val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('branch.session_class') }}",
                type: "POST",
                data: {
                    id: id,
                    class_id: id,
                    session: session,
                    type: 'studypack'
                },
                dataType: 'json',
                success: function(result) {
                    if (result.status == 'success') {
                        $('#stdy_select').empty();
                        $('#stdy_select').append($('<option>', {
                            value: '',
                            text: 'Select StudyPack'
                        }));
                        for (var j = 0; j < result.Studypack.length; j++) {
                            var cls = result.Studypack[j];
                            $('#stdy_select').append($('<option>', {
                                value: cls.id,
                                text: cls.title
                            }));
                        }
                        var params = new URLSearchParams(window.location.search);
                        var selectedStudypack = params.get('Studypack');
                        if (selectedStudypack) {
                            $('#stdy_select').val(selectedStudypack);
                        }
                    }
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
                    if (result.status == 'success') {
                        var $studentSelect = $('#student_select');
                        if ($studentSelect[0] && $studentSelect[0].customSelectInstance) {
                            $studentSelect[0].customSelectInstance.destroy();
                            delete $studentSelect[0].customSelectInstance;
                        }
                        if ($studentSelect.next('.custom-select-wrapper').length) {
                            $studentSelect.next('.custom-select-wrapper').remove();
                        }
                        $studentSelect.removeClass('custom-select');
                        $studentSelect.empty();
                        $studentSelect.append($('<option>', {
                            value: 'all',
                            text: 'All Students'
                        }));
                        for (var studentId in result.students) {
                            if (result.students.hasOwnProperty(studentId)) {
                                $studentSelect.append($('<option>', {
                                    value: studentId,
                                    text: result.students[studentId]
                                }));
                            }
                        }
                        $studentSelect.addClass('custom-select').show();
                        if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                            window.CustomSelect.create($studentSelect[0]);
                        }
                        $studentSelect.val('all');
                        if (window.location.search && /[?&]student=/.test(window.location.search)) {
                            var params = new URLSearchParams(window.location.search);
                            var selectedStudent = params.get('student');
                            if (selectedStudent) {
                                $studentSelect.val(selectedStudent);
                            }
                        }
                    }
                }
            });
        }

        function generateStudypackChallans() {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('studypackchallan.store') }}';
            form.style.display = 'none';

            var csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            ['branches', 'sessionselect', 'class_select', 'stdy_select', 'student_select', 'fee_month', 'issue_date',
                'due_date'
            ].forEach(function(id) {
                var el = document.getElementById(id);
                if (!el) return;
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = (id === 'sessionselect') ? 'session' : (id === 'class_select' ? 'class' : (id ===
                    'stdy_select' ? 'Studypack' : (id === 'student_select' ? 'student' : id)));
                input.value = el.value;
                form.appendChild(input);
            });

            document.body.appendChild(form);
            form.submit();
        }

        var currentPrintDocumentType = 'challan';

        function openPrintModal(e) {
            if (e) e.preventDefault();
            currentPrintDocumentType = 'challan';
            $('#printModalLabel').text('Download Challan Options');
            $('#printModal .modal-body p').text('Select challan download options:');
            $('#printModal').modal('show');
        }

        function openSpPrintModal(e) {
            if (e) e.preventDefault();
            currentPrintDocumentType = 'sp';
            $('#printModalLabel').text('Download SP Options');
            $('#printModal .modal-body p').text('Select SP download options:');
            $('#printModal').modal('show');
        }

        function printSeparatePDF() {
            getCheckedRowData('separate', currentPrintDocumentType);
            $('#printModal').modal('hide');
        }

        function printSinglePDF() {
            getCheckedRowData('single', currentPrintDocumentType);
            $('#printModal').modal('hide');
        }

        function getCheckedRowData(printType, documentType) {
            var checkedRowsData = [];
            document.querySelectorAll('input[name="checked[]"]:checked').forEach(function(checkbox) {
                var row = checkbox.closest('tr');
                if (!row) return;
                var cells = row.querySelectorAll('td');
                var sessionEl = document.getElementById('sessionselect');
                var sessionText = sessionEl && sessionEl.selectedOptions && sessionEl.selectedOptions[0] ? sessionEl
                    .selectedOptions[0].textContent.trim().replace(/\s+/g, '_') : '';
                checkedRowsData.push({
                    id: checkbox.value,
                    rollNo: cells[1] ? cells[1].textContent.trim() : '',
                    studentName: cells[3] ? cells[3].textContent.trim() : '',
                    sessionYear: sessionText
                });
            });

            if (!checkedRowsData.length) return;

            var printBtn = document.getElementById(documentType === 'sp' ? 'printSpButton' : 'printChallanButton');
            if (printBtn) {
                printBtn.disabled = true;
                printBtn.innerText = 'Processing...';
            }

            showBulkProcessingLoader('Preparing the first batch of challans...');
            var requestedBatchSize = 10;
            var batchSize = Math.max(10, requestedBatchSize);
            var batchIndex = 0;
            var totalChallans = checkedRowsData.length;
            var totalBatches = Math.ceil(totalChallans / batchSize);

            function triggerDownload(base64, filename) {
                var byteCharacters = atob(base64);
                var byteNumbers = new Array(byteCharacters.length);
                for (var i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                var blob = new Blob([new Uint8Array(byteNumbers)], {
                    type: 'application/pdf'
                });
                var url = window.URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = filename;
                a.style.display = 'none';
                document.body.appendChild(a);
                a.click();
                setTimeout(function() {
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }, 1200);
            }

            function buildStudyPackDownloadFilename(meta, printType, documentType) {
                if (documentType === 'sp') {
                    if (printType === 'single') {
                        return 'SP-Booklet.pdf';
                    }
                    return ['SP-Booklet', meta.rollNo, meta.studentName, meta.sessionYear]
                        .filter(Boolean)
                        .join('-') + '.pdf';
                }

                return [meta.rollNo, meta.studentName, 'studypackchallan', meta.sessionYear]
                    .filter(Boolean)
                    .join('-') + '.pdf';
            }

            function triggerDownloadsQueue(pdfList, metadataList) {
                var delayMs = 500;
                pdfList.forEach(function(pdfBase64, index) {
                    var meta = metadataList[index] || {};
                    var filename = buildStudyPackDownloadFilename(meta, printType, documentType);
                    setTimeout(function() {
                        triggerDownload(pdfBase64, filename);
                    }, index * delayMs);
                });
            }

            function processBatch() {
                var startIdx = batchIndex * batchSize;
                var endIdx = Math.min(startIdx + batchSize, totalChallans);
                var currentBatchIds = checkedRowsData.slice(startIdx, endIdx);
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: '{{ route('studypackchallans.printbulk') }}',
                    method: 'POST',
                    data: {
                        rowsdata: checkedRowsData.map(function(row) {
                            return row.id;
                        }),
                        printType: printType,
                        documentType: documentType,
                        batchSize: batchSize,
                        batchIndex: batchIndex
                    },
                    success: function(response) {
                        if (response.error) {
                            console.error('StudyPack bulk batch error', response.error);
                            alert(response.error);
                            hideBulkProcessingLoader();
                            if (printBtn) {
                                printBtn.innerText = documentType === 'sp' ? 'Download SP' : 'Download Challan';
                                printBtn.disabled = false;
                            }
                            return;
                        }

                        if (response.pdfs) {
                            if (printType === 'separate') {
                                var batchMetadata = currentBatchIds.map(function(row) {
                                    return {
                                        rollNo: row.rollNo || '',
                                        studentName: row.studentName || '',
                                        sessionYear: row.sessionYear || ''
                                    };
                                });
                                triggerDownloadsQueue(response.pdfs, batchMetadata);
                            }
                        }

                        var shouldContinue = response.hasMoreBatches === true || (batchIndex + 1) < totalBatches;
                        if (shouldContinue) {
                            batchIndex++;
                            showBulkProcessingLoader('Processing batch ' + (batchIndex + 1) + ' of ' + totalBatches + '...');
                            setTimeout(function() {
                                processBatch();
                            }, 300);
                        } else {
                            if (printType === 'single' && response.pdfs && response.pdfs.length > 0) {
                                var firstMeta = checkedRowsData[0] || {};
                                var singleName = buildStudyPackDownloadFilename(firstMeta, printType, documentType);
                                triggerDownload(response.pdfs[0], singleName);
                            }

                            hideBulkProcessingLoader();
                            if (printBtn) {
                                printBtn.innerText = documentType === 'sp' ? 'Download SP' : 'Download Challan';
                                printBtn.disabled = false;
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('StudyPack bulk batch ajax error', { status: status, error: error, response: xhr.responseText });
                        alert('Failed to fetch PDF content: ' + error);
                        hideBulkProcessingLoader();
                        if (printBtn) {
                            printBtn.innerText = 'Download SP';
                            printBtn.disabled = false;
                        }
                    }
                });
            }

            processBatch();
        }

        function refreshStudyPackButtons() {
            var challanBtn = document.getElementById('printChallanButton');
            var spBtn = document.getElementById('printSpButton');
            var rollbackBtn = document.getElementById('rollbackButton');
            var checkAll = document.getElementById('checkAll');
            var rowCheckboxes = Array.prototype.slice.call(document.querySelectorAll('input[name="checked[]"]'));
            var checkedCount = rowCheckboxes.filter(function(cb) {
                return cb.checked;
            }).length;
            var anyChecked = checkedCount > 0;

            if (challanBtn) challanBtn.disabled = !anyChecked;
            if (spBtn) spBtn.disabled = !anyChecked;
            if (rollbackBtn) rollbackBtn.disabled = !anyChecked;
            if (checkAll) checkAll.checked = rowCheckboxes.length > 0 && checkedCount === rowCheckboxes.length;

        }

        function rollbackStudyPackChallans() {

            var rows = Array.prototype.slice.call(document.querySelectorAll('input[name="checked[]"]:checked')).map(
                function(cb) {
                    return cb.value;
                });


            if (!rows.length) {
                show_toastr('error', 'Please select at least one challan to rollback.', 'error');
                return;
            }


            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to rollback the selected studypack challans?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, rollback!'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                if (!{{ Route::has('studypackchallan.rollback') ? 'true' : 'false' }}) {
                    Swal.fire('Error!', 'Rollback route is missing.', 'error');
                    return;
                }

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{ Route::has('studypackchallan.rollback') ? route('studypackchallan.rollback') : '#' }}',
                    type: 'POST',
                    data: {
                        rows: rows
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Rollback Result', response.message, 'success').then(function() {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error!', response.message ||
                                'Unable to rollback selected challans.', 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', (xhr.responseJSON && xhr.responseJSON.message) ? xhr
                            .responseJSON.message : 'Unable to rollback selected challans.', 'error'
                        );
                    }
                });
            });
        }

        document.addEventListener('change', function(e) {
            if (!e.target) return;
            if (e.target.id === 'checkAll') {
                var checked = e.target.checked;
                document.querySelectorAll('input[name="checked[]"]').forEach(function(cb) {
                    cb.checked = checked;
                });
                console.log('checkAll changed', checked);
                refreshStudyPackButtons();
                return;
            }
            if (e.target.name === 'checked[]') {
                console.log('row checkbox changed', e.target.value, e.target.checked);
                refreshStudyPackButtons();
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            refreshStudyPackButtons();
        });
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('All StudyPack Challans') }}</li>
@endsection

{{--@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="lg" data-url="{{ route('studypackchallan.create') }}" data-ajax-popup="true"
            data-bs-title="{{ __('Create') }}" class="btn btn-sm btn-primary">
            Create
        </a>
    </div>
@endsection--}}

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['studypackchallan.index'], 'method' => 'GET', 'id' => 'admission_challan_form']) }}
                        <div class="row d-flex" style="width: 100%;">
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('session', __('Session'), ['class' => 'form-label']) }}
                                    {{ Form::select('session', $session, request('session'), ['class' => 'form-control select', 'id' => 'sessionselect']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, request('branches', $selectedBranch ?? ''), ['class' => 'form-control select custom-select', 'id' => 'branches_select', 'onchange' => 'branchcustomer(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                    {{ Form::select('class', $class, request('class', 'all'), ['class' => 'form-control select custom-select', 'id' => 'class_select', 'onchange' => 'classStudyPack(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('Studypack', __('StudyPack'), ['class' => 'form-label']) }}
                                    {{ Form::select('Studypack', $stdy_pack, request('Studypack'), ['class' => 'form-control select', 'id' => 'stdy_select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mt-2">
                                <div class="btn-box">
                                    {{ Form::label('student', __('Students'), ['class' => 'form-label']) }}
                                    {{ Form::select('student', [], request('student', 'all'), ['class' => 'form-control select custom-select', 'id' => 'student_select']) }}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mt-2">
                                <div class="btn-box">
                                    {{ Form::label('fee_month', __('Fee Month'), ['class' => 'form-label']) }}
                                    {!! Form::month('fee_month', request('fee_month', now()->format('Y-m')), [
                                        'class' => 'form-control',
                                        'id' => 'fee_month',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mt-2">
                                <div class="btn-box">
                                    {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}
                                    {!! Form::date('issue_date', request('issue_date', now()->toDateString()), [
                                        'class' => 'form-control',
                                        'id' => 'issue_date',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mt-2">
                                <div class="btn-box">
                                    {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}
                                    {!! Form::date('due_date', request('due_date', now()->addDays(10)->toDateString()), [
                                        'class' => 'form-control',
                                        'id' => 'due_date',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4 d-flex gap-2 align-items-end">
                                <button type="submit" class="btn mx-1 btn-sm btn-outline-primary">Search</button>
                                    <button type="button" class="btn mx-1 btn-sm btn-outline-primary"
                                        id="generateChallanButton" onclick="generateStudypackChallans()">
                                        @if (Auth::user()->type == 'company')
                                        Generate Bulk
                                        Challan
                                        @else
                                        Generate Challan 
                                        @endif</button>
                                    <button type="button" id="rollbackButton" class="btn mx-1 btn-sm btn-outline-danger"
                                        onclick="rollbackStudyPackChallans()" disabled>Rollback Challan</button>
                                <button type="button" id="printChallanButton" class="btn mx-1 btn-sm btn-outline-success"
                                    onclick="openPrintModal(event)" disabled>Download Challan</button>
                                <button type="button" id="printSpButton" style="background: rgb(22, 162, 255) !important; color : #fff !important;" class="btn mx-1 btn-sm btn-outline-info"
                                    onclick="openSpPrintModal(event)" disabled>Download SP</button>

                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="printModalLabel">Download Challan Options</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Select challan download options:</p>
                        <button class="btn btn-primary" onclick="printSeparatePDF()">Separate PDF</button>
                        <button class="btn btn-primary" onclick="printSinglePDF()">Single PDF</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="studypack-bulk-loader" style="display:none; position:fixed; inset:0; z-index:999999; align-items:center; justify-content:center; flex-direction:column; background:rgba(0,0,0,0.74); color:#fff;">
            <div style="width:56px; height:56px; border:5px solid rgba(255,255,255,0.2); border-top-color:#fff; border-radius:50%; animation:studypackBulkSpin 1s linear infinite;"></div>
            <div id="studypack-bulk-loader-text" style="margin-top:16px; font-weight:700; font-size:16px;">Preparing downloads...</div>
            <div id="studypack-bulk-loader-subtext" style="margin-top:6px; font-size:13px; opacity:0.9;">Please wait while the selected challans are processed.</div>
        </div>

        <div class="card mt-3">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="">
                        <thead class="table_heads">
                            <tr>
                                <th><input id="checkAll" type="checkbox"></th>
                                <th>#</th>
                                <th>{{ __('Challan No.') }}</th>
                                <th>{{ __('Roll No.') }}</th>
                                <th>{{ __('Student Name') }}</th>
                                <th>{{ __('Challan Type') }}</th>
                                <th>{{ __('Challan Month') }}</th>
                                <th>{{ __('Total Amount') }}</th>
                                <th>{{ __('Receivable Amount') }}</th>
                                <th>{{ __('Rem Amount') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Issue Date') }}</th>
                                <th>{{ __('Due Date') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($studypacks as $challan)
                                @php
                                    $paidAmount = (float) ($challan->receipts->sum('recipt_amount') ?? 0);
                                    $receivableAmount = max((float) ($challan->total_amount ?? 0), 0);
                                    $remAmount = max((float) ($challan->total_amount ?? 0) - $paidAmount, 0);
                                @endphp
                                <tr>
                                    <td><input type="checkbox" name="checked[]" value="{{ $challan->id }}"></td>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $challan->challanNo }}</td>
                                    <td>{{ $challan->student->roll_no ?? 'N/A' }}</td>
                                    <td class="student-name">{{ $challan->student->stdname ?? '' }}</td>
                                    <td>{{ $challan->challan_type ?? 'Studypack' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($challan->fee_month)->format('F, Y') }}</td>
                                    <td>{{ number_format((float) ($challan->total_amount ?? 0), 0) }}</td>
                                    <td>{{ number_format($receivableAmount, 0) }}</td>
                                    <td>{{ number_format($remAmount, 0) }}</td>
                                    <td>{{ $challan->status }}</td>
                                    <td>{{ $challan->issue_date }}</td>
                                    <td>{{ $challan->due_date }}</td>
                                    <td>
                                        <div class="action-btn ms-2">
                                            <a href="{{ route('studypackchallan.show', $challan->id) }}" target="_blank"
                                                class="btn btn-sm btn-outline-primary pt-2">
                                                <span class="btn-inner--icon"><i class="ti ti-eye"></i></span>
                                            </a>
                                            <a href="{{ route('studypackchallan.booklist', $challan->id) }}" target="_blank"
                                                class="mx-1 btn btn-sm btn-outline-info pt-2" title="Booklist View">
                                                <span class="btn-inner--icon"><i class="ti ti-book"></i></span>
                                            </a>
                                            @if (strtolower($challan->status) == 'assigned' && in_array(Auth::user()->type, ['super admin', 'company', 'branch']))
                                                <a href="{{ route('studypackchallan.edit', $challan->id) }}"
                                                    class="mx-1 btn btn-sm btn-outline-primary pt-2" title="Edit">
                                                    <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                                </a>
                                            @endif

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
@endsection



