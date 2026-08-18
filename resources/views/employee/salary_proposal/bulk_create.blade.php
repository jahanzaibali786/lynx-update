{{ Form::open(['route' => ['employee-salary-proporal.bulk.store'], 'method' => 'post', 'id' => 'bulk_salary_proposal_form']) }}
<div class="modal-body">
    <div class="row g-3 align-items-end mb-3">
        <div class="col-md-3">
            {{ Form::label('session_id', __('Session'), ['class' => 'form-label']) }}
            {{ Form::select('session_id', $sessions, $activeSessionId ?? null, ['class' => 'form-control select', 'id' => 'session_id']) }}
        </div>
        <div class="col-md-3">
            {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
            @if ($branchLocked)
                {{ Form::select('branch_id_display', $branches, $defaultBranchId, ['class' => 'form-control select', 'id' => 'branch_id_display', 'disabled' => 'disabled']) }}
                {{ Form::hidden('branch_id', $defaultBranchId, ['id' => 'branch_id']) }}
            @else
                {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select', 'id' => 'branch_id']) }}
            @endif
        </div>
        <div class="col-md-2">
            {{ Form::label('department_id', __('Department'), ['class' => 'form-label']) }}
            {{ Form::select('department_id', $departments, null, ['class' => 'form-control select', 'id' => 'department_id']) }}
        </div>
        <div class="col-md-2">
            {{ Form::label('designation_id', __('Designation'), ['class' => 'form-label']) }}
            <select class="select form-control" id="designation_id" name="designation_id" data-placeholder="{{ __('Select Designation ...') }}">
                <option value="">{{__('Select any Designation')}}</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-primary w-100" id="bulk-search-btn">Search</button>
        </div>
    </div>

    <div id="bulk-result-summary" class="alert alert-info d-none"></div>
    <div id="bulk-employee-results" class="d-grid gap-3"></div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create Bulk Proposals') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        var payScaleOptions = [];
        var searchUrl = '{{ route('employee-salary-proporal.bulk.search') }}';
        var previewUrl = '{{ route('employee-salary-proporal.bulk.preview') }}';

        if ($('#branch_id').length && $('#branch_id').val() === '' && $('#branch_id_display').length) {
            $('#branch_id').val($('#branch_id_display').val());
        }

        function escapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function money(value) {
            var number = parseFloat(value || 0);
            if (isNaN(number)) {
                number = 0;
            }
            return number.toFixed(2);
        }

        function scaleOptionsHtml(selectedId) {
            var html = '<option value="">Select Scale</option>';
            $.each(payScaleOptions, function(index, scale) {
                var selected = String(scale.id) === String(selectedId) ? 'selected' : '';
                html += '<option value="' + scale.id + '" ' + selected + '>' + escapeHtml(scale.label) + '</option>';
            });
            return html;
        }

        function loadDesignations(departmentId, selectedDesignationId) {
            $('#designation_id').html('<option value="">{{__('Select any Designation')}}</option>');
            if (!departmentId) {
                return;
            }

            $.ajax({
                url: '{{ route('employee.json') }}',
                type: 'POST',
                data: {
                    department_id: departmentId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    $.each(data, function(key, value) {
                        var selected = String(key) === String(selectedDesignationId || '') ? 'selected' : '';
                        $('#designation_id').append('<option value="' + key + '" ' + selected + '>' + value + '</option>');
                    });
                }
            });
        }

        function buildHiddenInputs(index, row) {
            return ''
                + '<div class="d-none bulk-hidden-inputs">'
                + '<input type="hidden" name="proposals[' + index + '][employee_id]" value="' + escapeHtml(row.employee_id) + '">'
                + '<input type="hidden" name="proposals[' + index + '][emp_no]" value="' + escapeHtml(row.employee_no) + '">'
                + '<input type="hidden" class="proposal-payscale" name="proposals[' + index + '][payscale]" value="' + escapeHtml(row.selected_scale_id || '') + '">'
                + '<input type="hidden" class="proposal-income-tax" name="proposals[' + index + '][income_tax]" value="' + money(row.new_tax) + '">'
                + '<input type="hidden" class="proposal-other-deduction" name="proposals[' + index + '][other_deduction]" value="' + money(row.new_child_amount) + '">'
                + '<input type="hidden" class="proposal-eobi" name="proposals[' + index + '][EOBI]" value="0">'
                + '<input type="hidden" class="proposal-gross" name="proposals[' + index + '][gross]" value="' + money(row.new_gross) + '">'
                + '<input type="hidden" class="proposal-net-salary" name="proposals[' + index + '][net_salary]" value="' + money(row.new_ctc) + '">'
                + '<input type="hidden" class="proposal-pay-method" name="proposals[' + index + '][pay_method]" value="">'
                + '<input type="hidden" class="proposal-bank-name" name="proposals[' + index + '][bank_name]" value="">'
                + '<input type="hidden" class="proposal-bank-account" name="proposals[' + index + '][bank_account]" value="">'
                + '</div>';
        }

        function buildEmployeeRow(row, index) {
            return ''
                + '<tr class="bulk-employee-row" id="bulk-row-' + row.employee_id + '" data-employee-id="' + row.employee_id + '" data-index="' + index + '">'
                + '<td class="text-center">'
                + '<div class="d-flex align-items-center justify-content-center">'
                + '<input type="checkbox" class="bulk-row-check" checked>'
                + '</div>'
                + '</td>'
                + '<td class="text-center">' + (index + 1) + '</td>'
                + '<td>' + escapeHtml(row.employee_no) + '</td>'
                + '<td>' + escapeHtml(row.employee_name) + '</td>'
                + '<td>' + escapeHtml(row.department_name) + '</td>'
                + '<td>' + escapeHtml(row.designation_name) + '</td>'
                + '<td>' + escapeHtml(row.doj) + '</td>'
                + '<td>' + escapeHtml(row.service_duration) + '</td>'
                + '<td>' + escapeHtml(row.probation_end_date) + '</td>'
                + '<td>' + escapeHtml(row.current_scale_label) + '</td>'
                + '<td>' + money(row.current_gross) + '</td>'
                + '<td>' + money(row.current_tax) + '</td>'
                + '<td>' + money(row.current_deduction) + '</td>'
                + '<td>' + escapeHtml(row.child_count) + '</td>'
                + '<td>' + money(row.child_amount) + '</td>'
                + '<td>' + money(row.current_ctc) + '</td>'
                + '<td>'
                + '<select class="form-control bulk-payscale-select" style="min-width:180px;" data-index="' + index + '" data-employee-id="' + row.employee_id + '">'
                + scaleOptionsHtml(row.selected_scale_id)
                + '</select>'
                + buildHiddenInputs(index, row)
                + '</td>'
                + '<td class="bulk-new-gross">' + money(row.new_gross) + '</td>'
                + '<td class="bulk-new-tax">' + money(row.new_tax) + '</td>'
                + '<td class="bulk-new-child">' + money(row.new_child_amount) + '</td>'
                + '<td class="bulk-new-ctc">' + money(row.new_ctc) + '</td>'
                + '</tr>';
        }

        function syncHiddenFields($row, employee) {
            $row.find('.proposal-payscale').val(employee.selected_scale_id || '');
            $row.find('.proposal-income-tax').val(money(employee.new_tax));
            $row.find('.proposal-other-deduction').val(money(employee.new_child_amount));
            $row.find('.proposal-eobi').val('0');
            $row.find('.proposal-gross').val(money(employee.new_gross));
            $row.find('.proposal-net-salary').val(money(employee.new_ctc));
        }

        function setRowSelectionState($row, enabled) {
            $row.toggleClass('table-secondary', !enabled);
            $row.find('input, select, textarea, button').not('.bulk-row-check').prop('disabled', !enabled);
        }

        function syncCheckAllState() {
            var $checks = $('.bulk-row-check');
            var total = $checks.length;
            var checked = $checks.filter(':checked').length;
            $('#bulk-check-all').prop('checked', total > 0 && checked === total);
            $('#bulk-check-all').prop('indeterminate', checked > 0 && checked < total);
        }

        function renderEmployees(rows) {
            if (!rows.length) {
                $('#bulk-employee-results').html('<div class="alert alert-warning mb-0">No active employees matched the selected filters.</div>');
                return;
            }

            var html = ''
                + '<div class="table-responsive">'
                + '<table class="table table-bordered table-sm align-middle mb-0">'
                + '<thead class="table-light">'
                + '<tr>'
                + '<th rowspan="2" class="text-center" style="width:48px;"><input type="checkbox" id="bulk-check-all" title="Select all"></th>'
                + '<th rowspan="2" class="text-center" style="width:48px;">Sr.</th>'
                + '<th colspan="7" class="text-center">Basic Details</th>'
                + '<th colspan="7" class="text-center">Previous Salary Detail</th>'
                + '<th colspan="5" class="text-center">New Salary Detail</th>'
                + '</tr>'
                + '<tr>'
                + '<th>Emp No #</th>'
                + '<th>Emp. Name</th>'
                + '<th>DOJ</th>'
                + '<th>Tenure</th>'
                + '<th>Prob. End</th>'
                + '<th>Department</th>'
                + '<th>Designation</th>'
                + '<th>Scale No</th>'
                + '<th>Gross</th>'
                + '<th>Tax</th>'
                + '<th>Deduction</th>'
                + '<th>Children</th>'
                + '<th>Child Fee</th>'
                + '<th>CTC</th>'
                + '<th style="min-width:180px;">Pay Scale</th>'
                + '<th>Gross</th>'
                + '<th>Tax</th>'
                + '<th>Child Amount</th>'
                + '<th>CTC</th>'
                + '</tr>'
                + '</thead>'
                + '<tbody>';

            $.each(rows, function(index, row) {
                html += buildEmployeeRow(row, index);
            });

            html += '</tbody></table></div>';
            $('#bulk-employee-results').html(html);
            syncCheckAllState();
        }

        function applyPreview(rowId, employee) {
            var $row = $('#bulk-row-' + rowId);
            if (!$row.length) {
                return;
            }

            $row.find('.bulk-new-gross').text(money(employee.new_gross));
            $row.find('.bulk-new-tax').text(money(employee.new_tax));
            $row.find('.bulk-new-child').text(money(employee.new_child_amount));
            $row.find('.bulk-new-ctc').text(money(employee.new_ctc));
            $row.find('.bulk-payscale-select').val(employee.selected_scale_id || '');
            syncHiddenFields($row, employee);
        }

        function loadPreview(rowId) {
            var $row = $('#bulk-row-' + rowId);
            if (!$row.length) {
                return;
            }

            $.ajax({
                url: previewUrl,
                type: 'GET',
                dataType: 'json',
                data: {
                    employee_id: $row.data('employee-id'),
                    payscale_id: $row.find('.bulk-payscale-select').val(),
                    department_id: $('#department_id').val(),
                    session_id: $('#session_id').val()
                },
                success: function(response) {
                    if (response.success && response.employee) {
                        applyPreview(rowId, response.employee);
                    }
                }
            });
        }

        $(document).on('change', '.bulk-payscale-select', function() {
            var rowId = $(this).closest('.bulk-employee-row').attr('id').replace('bulk-row-', '');
            loadPreview(rowId);
        });

        $(document).on('change', '.bulk-row-check', function() {
            var $row = $(this).closest('.bulk-employee-row');
            setRowSelectionState($row, $(this).is(':checked'));
            syncCheckAllState();
        });

        $(document).on('change', '#bulk-check-all', function() {
            var checked = $(this).is(':checked');
            $('.bulk-row-check').each(function() {
                $(this).prop('checked', checked);
                setRowSelectionState($(this).closest('.bulk-employee-row'), checked);
            });
            syncCheckAllState();
        });

        $('#department_id').on('change', function() {
            loadDesignations($(this).val(), '');
        });

        if ($('#department_id').val()) {
            loadDesignations($('#department_id').val(), $('#designation_id').val());
        }

        $('#bulk-search-btn').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Searching...');
            $.ajax({
                url: searchUrl,
                type: 'GET',
                dataType: 'json',
                data: $('#bulk_salary_proposal_form').serialize(),
                success: function(response) {
                    if (response.success) {
                        payScaleOptions = response.pay_scales || [];
                        $('#bulk-result-summary').removeClass('d-none').text((response.employees || []).length + ' employee(s) found.');
                        renderEmployees(response.employees || []);
                    } else {
                        $('#bulk-employee-results').html('<div class="alert alert-danger mb-0">Unable to load employees.</div>');
                    }
                },
                error: function(xhr) {
                    var message = 'Unable to load employees.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    $('#bulk-employee-results').html('<div class="alert alert-danger mb-0">' + escapeHtml(message) + '</div>');
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        ajaxModalForm({
            formSelector: '#bulk_salary_proposal_form',
            submitText: 'Creating...',
            closeOnSuccess: false,
            showToast: true,
            onSuccess: function() {
                closeActiveBootstrapModal();
            }
        });
    });
</script>

