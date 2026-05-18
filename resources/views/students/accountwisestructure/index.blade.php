@extends('layouts.admin')

@section('page-title')
    {{ __('Manage AccountWiseFeeStructure') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('All AccountWiseFees') }}</li>
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
                dataType: 'json',
                success: function(result) {
                    if (result.status == 'success') {
                        $('#class_create').empty();
                        $('#class_create').append($('<option>', {
                            value: '',
                            text: 'Select Class'
                        }));
                        for (var j = 0; j < result.class.length; j++) {
                            var cls = result.class[j];
                            $('#class_create').append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                    }
                }
            });
        }
    </script>
    <script>
        $(document).on('change', '#branch_from', function() {
            var branch = $(this).val();
            $.ajax({
                url: '{{ route('branch.class') }}',
                type: 'POST',
                data: {
                    "branch_id": branch,
                    "_token": "{{ csrf_token() }}"
                },
                success: function(data) {
                    $('#class_from').empty();
                    $('#class_from').append('<option value="">{{ __('Select Class ') }}</option>');
                    for (let index = 0; index < data.length; index++) {
                        $('#class_from').append('<option value="' + data[index]['id'] + '">' + data[
                            index]['name'] + '</option>');
                    }
                }
            });
        });
    </script>
@endpush

@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="lg" data-url="{{ route('account-wise-fee.create') }}" data-ajax-popup="true"
            data-bs-title="{{ __('Create') }}" class="btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">Create</span>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['account-wise-fee.index'], 'method' => 'GET', 'id' => 'employee_submit']) }}
                        <div class="row d-flex justify-content-end">
                            @if (\Auth::user()->type == 'company')
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="form-group">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'id' => 'branch_from']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="form-group">
                                        {{ Form::label('class_id', __('Class'), ['class' => 'form-label']) }}
                                        {{ Form::select('class_id', $classes, isset($_GET['class_id']) ? $_GET['class_id'] : '', ['class' => 'form-control select', 'id' => 'class_from']) }}
                                    </div>
                                </div>
                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="form-group">
                                        {{ Form::label('head_id', __('Fee Head'), ['class' => 'form-label']) }}
                                        {{ Form::select('head_id', $heads, isset($_GET['head_id']) ? $_GET['head_id'] : '', ['class' => 'form-control select']) }}
                                    </div>
                                </div>
                            @endif
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('employee_submit').submit(); return false;">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('account-wise-fee.index') }}" class="btn mx-1 btn-sm btn-outline-danger">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <a class="btn mx-1 btn-sm btn-outline-warning" onclick="applyDiscountToChecked()">
                                    Apply Discount to Checked
                                </a>
                                <a class="btn mx-1 btn-sm btn-outline-danger" onclick="detachAllChecked()">
                                    ✕ Detach Checked <span id="checkedCount" class="badge bg-white text-danger ms-1"
                                        style="display:none;">0</span>
                                </a>
                                <a id="submitChecked" class="btn mx-1 btn-sm btn-outline-success" onclick="submitChanges()"
                                    style="display:none;">
                                    Save Changes <span id="dirtyCount" class="badge bg-danger ms-1"
                                        style="display:none;">0</span>
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        input[type="text"] {
            width: 100%;
        }

        .apply-to-all-link {
            font-size: 11px;
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
            display: block;
            margin-top: 2px;
        }

        .apply-to-all-link:hover {
            color: #0056b3;
        }

        /* Highlight dirty (modified) rows */
        tr.row-dirty {
            background-color: #fff8e1 !important;
        }

        tr.row-dirty td {
            border-left: 3px solid #ffc107;
        }

        /* Per-row detach button (only on already-checked rows) */
        .btn-detach {
            font-size: 11px;
            padding: 2px 7px;
            line-height: 1.4;
            border-radius: 3px;
            color: #dc3545;
            border: 1px solid #dc3545;
            background: transparent;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn-detach:hover {
            background: #dc3545;
            color: #fff;
        }
    </style>

    <div class="table-responsive">
        <table class="table" id="feeTable">
            <thead class="table_heads">
                <tr>
                    <th>{{ __('Roll No') }}</th>
                    <th>{{ __('Student Name') }}</th>
                    <th>{{ __('Father Name') }}</th>
                    <th>{{ __('Class') }}</th>
                    <th>{{ __('Student Amount') }}</th>
                    <th>{{ __('Class Amount') }}</th>
                    <th>{{ __('Discount %.') }}</th>
                    <th>{{ __('Net Amnt.') }}</th>
                    <th>
                        {{ __('Active') }}<br>
                        <input id="checkAll" type="checkbox" title="Check/Uncheck All">
                    </th>
                    <th>{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $firstRow = true;
                    $rowCounter = 0;
                @endphp
                @foreach ($class_wise_fee as $fee)
                    @php
                        $student = \App\Models\StudentFeeStructure::with(['student', 'student.enrollment'])
                            ->where('head_id', @$fee->head_id)
                            ->where('class_id', @$fee->class_id)
                            ->whereHas('student.enrollment', function ($q) {
                                $q->where('active_status', 1);
                            })
                            ->get();
                    @endphp
                    @foreach ($student as $student_fee)
                        @php $rowCounter++; @endphp
                        <tr id="row_{{ $rowCounter }}" data-original-amount="{{ @$fee->amount }}"
                            data-original-discount="{{ @$student_fee->discount }}">
                            <td>{{ @$student_fee->student->enrollment->enrollId }}</td>
                            <td>{{ @$student_fee->student->stdname }}</td>
                            <td>{{ @$student_fee->student->fathername }}</td>
                            <td>{{ @$student_fee->student->enrollment->class->name }}</td>
                            <td><input type="text" value="{{ @$student_fee->amount }}" disabled></td>
                            @php
                                $discountAmount =
                                    @$student_fee->amount - (@$student_fee->discount / 100) * @$student_fee->amount;
                            @endphp
                            <td>
                                <input type="text" id="amount_{{ $rowCounter }}" value="{{ @$fee->amount }}"
                                    oninput="markDirty({{ $rowCounter }}); calculateNetAmount({{ $rowCounter }})">
                            </td>
                            <td>
                                <input type="text" id="discount_{{ $rowCounter }}"
                                    value="{{ @$student_fee->discount }}"
                                    oninput="markDirty({{ $rowCounter }}); calculateNetAmount({{ $rowCounter }})"
                                    max="100">
                                @if ($firstRow)
                                    <span class="apply-to-all-link" onclick="applyDiscountToAll()">
                                        {{ __('Apply to all below') }}
                                    </span>
                                    @php $firstRow = false; @endphp
                                @endif
                            </td>
                            <td>
                                <input type="text" id="netAmount_{{ $rowCounter }}" value="{{ @$discountAmount }}"
                                    readonly>
                            </td>
                            <td>
                                <input class="form-check-input active_checkbox" type="checkbox" name="checked[]"
                                    id="activeCheck_{{ $rowCounter }}" onchange="markDirty({{ $rowCounter }})"
                                    {{ @$student_fee->checked_status ? 'checked' : '' }}>

                                {{-- Hidden fields --}}
                                <input type="hidden" class="branch_id" id="brnch_{{ $rowCounter }}"
                                    value="{{ @$student_fee->student->enrollment->owned_by }}">
                                <input type="hidden" class="class_id" id="clsId_{{ $rowCounter }}"
                                    value="{{ @$fee->class_id }}">
                                <input type="hidden" class="student_id" id="stdid_{{ $rowCounter }}"
                                    value="{{ @$student_fee->student_id }}">
                                <input type="hidden" class="fee_val" value="{{ $rowCounter }}">
                                <input type="hidden" class="reg_student_id" id="stdregid_{{ $rowCounter }}"
                                    value="{{ @$student_fee->reg_id }}">
                                <input type="hidden" class="head_id" id="headid_{{ $rowCounter }}"
                                    value="{{ @$fee->head_id }}">
                            </td>
                            <td>
                                @if (@$student_fee->checked_status)
                                    <button class="btn-detach"
                                        onclick="detachFeeHead({{ $rowCounter }}, '{{ @$student_fee->student_id }}', '{{ @$fee->class_id }}', '{{ @$student_fee->student->enrollment->owned_by }}', '{{ @$fee->head_id }}', '{{ @$student_fee->reg_id }}')"
                                        title="Remove this fee head from student">
                                        ✕ Detach
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        // ─── Dirty Row Tracking ───────────────────────────────────────────────────
        const dirtyRows = new Set();

        function markDirty(rowId) {
            dirtyRows.add(rowId);
            const row = document.getElementById('row_' + rowId);
            if (row) row.classList.add('row-dirty');
            updateDirtyBadge();
        }

        function clearDirty(rowId) {
            dirtyRows.delete(rowId);
            const row = document.getElementById('row_' + rowId);
            if (row) row.classList.remove('row-dirty');
            updateDirtyBadge();
        }

        function updateDirtyBadge() {
            const badge = document.getElementById('dirtyCount');
            if (dirtyRows.size > 0) {
                badge.style.display = 'inline-block';
                badge.textContent = dirtyRows.size;
            } else {
                badge.style.display = 'none';
            }
        }

        // ─── Net Amount Calculation ───────────────────────────────────────────────
        function calculateNetAmount(id) {
            const amountField = document.getElementById('amount_' + id);
            const discountField = document.getElementById('discount_' + id);
            const netField = document.getElementById('netAmount_' + id);
            if (!amountField || !discountField || !netField) return;

            let amount = parseFloat(amountField.value) || 0;
            let discount = parseFloat(discountField.value) || 0;

            if (discount > 100) {
                discount = 100;
                discountField.value = 100;
            }
            if (discount < 0) {
                discount = 0;
                discountField.value = 0;
            }

            netField.value = (amount - (amount * discount / 100)).toFixed(2);
        }

        // ─── Apply Discount to ALL rows below first ───────────────────────────────
        function applyDiscountToAll() {
            const firstDiscount = document.getElementById('discount_1');
            if (!firstDiscount) {
                alert('First discount field not found!');
                return;
            }

            const val = parseFloat(firstDiscount.value) || 0;
            if (val > 100 || val < 0) {
                alert('Discount must be between 0 and 100');
                return;
            }

            const allFields = document.querySelectorAll('input[id^="discount_"]');
            let count = 0;
            allFields.forEach(function(field, index) {
                if (index > 0) {
                    const rowId = parseInt(field.id.replace('discount_', ''));
                    field.value = val;
                    calculateNetAmount(rowId);
                    markDirty(rowId);
                    count++;
                }
            });
            alert('Discount of ' + val + '% applied to ' + count + ' rows below.');
        }

        // ─── Apply Discount ONLY to CHECKED rows ─────────────────────────────────
        function applyDiscountToChecked() {
            const firstDiscount = document.getElementById('discount_1');
            if (!firstDiscount) {
                alert('First discount field not found!');
                return;
            }

            const val = parseFloat(firstDiscount.value) || 0;
            if (val > 100 || val < 0) {
                alert('Discount must be between 0 and 100');
                return;
            }

            const checkboxes = document.querySelectorAll('input[name="checked[]"]');
            let count = 0;
            checkboxes.forEach(function(checkbox) {
                if (checkbox.checked) {
                    const row = checkbox.closest('tr');
                    const rowId = parseInt(row.querySelector('.fee_val').value);
                    const discountField = document.getElementById('discount_' + rowId);
                    if (discountField) {
                        discountField.value = val;
                        calculateNetAmount(rowId);
                        markDirty(rowId);
                        count++;
                    }
                }
            });

            if (count === 0) {
                alert('No checked rows found. Please check the rows you want to apply the discount to.');
            } else {
                alert('Discount of ' + val + '% applied to ' + count + ' checked row(s).');
            }
        }

        // ─── Check All + Live Checked Count Badge ────────────────────────────────
        var checkAllCheckbox = document.getElementById('checkAll');
        var rowCheckboxes = document.querySelectorAll('input[name="checked[]"]');

        function updateCheckedCount() {
            const checkedBadge = document.getElementById('checkedCount');
            const total = document.querySelectorAll('input[name="checked[]"]:checked').length;
            if (total > 0) {
                checkedBadge.style.display = 'inline-block';
                checkedBadge.textContent = total;
            } else {
                checkedBadge.style.display = 'none';
            }
        }

        checkAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(function(checkbox) {
                const row = checkbox.closest('tr');
                const rowId = parseInt(row.querySelector('.fee_val').value);
                checkbox.checked = checkAllCheckbox.checked;
                markDirty(rowId);
            });
            updateCheckedCount();
        });

        rowCheckboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    checkAllCheckbox.checked = false;
                } else {
                    checkAllCheckbox.checked = Array.from(rowCheckboxes).every(cb => cb.checked);
                }
                updateCheckedCount();
            });
        });

        // Run once on load to set badge from server-rendered checked state
        updateCheckedCount();

        // ─── Save ONLY Dirty (changed) Rows ──────────────────────────────────────
        function submitChanges() {
            if (dirtyRows.size === 0) {
                alert('No changes detected. Please modify some rows before saving.');
                return;
            }

            if (!confirm('Save changes to ' + dirtyRows.size + ' modified row(s)?')) return;

            let selectedData = [];

            dirtyRows.forEach(function(rowId) {
                const row = document.getElementById('row_' + rowId);
                if (!row) return;

                const checkbox = document.getElementById('activeCheck_' + rowId);

                selectedData.push({
                    rowid: rowId,
                    studentId: document.getElementById('stdid_' + rowId).value,
                    classId: document.getElementById('clsId_' + rowId).value,
                    branchId: document.getElementById('brnch_' + rowId).value,
                    amount: document.getElementById('amount_' + rowId).value,
                    discount: document.getElementById('discount_' + rowId).value,
                    netAmount: document.getElementById('netAmount_' + rowId).value,
                    regId: document.getElementById('stdregid_' + rowId).value,
                    headId: document.getElementById('headid_' + rowId).value,
                    checkedStatus: checkbox.checked ? 1 : 0,
                });
            });

            fetch('{{ route('account-wise-fee.save') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(selectedData)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        alert(data.message);
                        // Clear dirty state without reloading (or reload if preferred)
                        dirtyRows.forEach(id => clearDirty(id));
                        dirtyRows.clear();
                        updateDirtyBadge();
                        // Update original values so re-edits are tracked fresh
                        selectedData.forEach(function(item) {
                            const row = document.getElementById('row_' + item.rowid);
                            if (row) {
                                row.dataset.originalAmount = item.amount;
                                row.dataset.originalDiscount = item.discount;
                            }
                        });
                    } else {
                        alert('Error saving data: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('An error occurred while saving data.');
                });
        }

        // ─── Detach ALL Checked Rows (Bulk) ──────────────────────────────────────
        function detachAllChecked() {
            const checkedBoxes = document.querySelectorAll('input[name="checked[]"]:checked');

            if (checkedBoxes.length === 0) {
                alert('No checked rows found. Please check the rows you want to detach.');
                return;
            }

            if (!confirm('Detach fee head from ' + checkedBoxes.length +
                    ' checked student(s)?\n\nThis will permanently remove the fee head from all selected students. This cannot be undone.'
                    )) return;

            // Build payload from all checked rows in one shot
            let payload = [];
            checkedBoxes.forEach(function(checkbox) {
                const row = checkbox.closest('tr');
                const rowId = parseInt(row.querySelector('.fee_val').value);
                payload.push({
                    rowId: rowId,
                    studentId: document.getElementById('stdid_' + rowId).value,
                    classId: document.getElementById('clsId_' + rowId).value,
                    branchId: document.getElementById('brnch_' + rowId).value,
                    headId: document.getElementById('headid_' + rowId).value,
                    regId: document.getElementById('stdregid_' + rowId).value,
                });
            });

            // Single POST — backend bulk-deletes in one transaction
            fetch('{{ route('account-wise-fee.detach-bulk') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Fade-out and remove each detached row from DOM
                        payload.forEach(function(item) {
                            const row = document.getElementById('row_' + item.rowId);
                            if (row) {
                                row.style.transition = 'opacity 0.3s, background 0.3s';
                                row.style.background = '#ffe0e0';
                                row.style.opacity = '0';
                                setTimeout(() => {
                                    row.remove();
                                    clearDirty(item.rowId);
                                }, 350);
                            }
                        });
                        setTimeout(() => {
                            updateCheckedCount();
                            alert(data.message || payload.length + ' fee head(s) detached successfully.');
                        }, 450);
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('An error occurred while detaching.');
                });
        }

        // ─── Detach Single Fee Head ───────────────────────────────────────────────
        function detachFeeHead(rowId, studentId, classId, branchId, headId, regId) {
            const row = document.getElementById('row_' + rowId);
            const studentName = row ? row.cells[1].textContent.trim() : 'this student';

            if (!confirm('Remove this fee head from ' + studentName +
                    '?\n\nThis will permanently detach the fee head from the student.')) return;

            fetch('{{ route('account-wise-fee.detach') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        studentId: studentId,
                        classId: classId,
                        branchId: branchId,
                        headId: headId,
                        regId: regId,
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Remove the row from table visually
                        if (row) {
                            row.style.transition = 'opacity 0.3s';
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 300);
                        }
                        // Remove from dirty set if it was there
                        clearDirty(rowId);
                        alert(data.message || 'Fee head detached successfully.');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    alert('An error occurred while detaching.');
                });
        }
    </script>
@endsection
