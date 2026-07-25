{{ Form::open(array('route' => 'student-incomes.store', 'method' => 'POST', 'class' => 'ajax-modal-form')) }}
<div class="modal-body">
    <div class="row">
        <!-- Date -->
        <div class="form-group col-md-4">
            {{ Form::label('date', __('Date'), ['class' => 'form-label']) }}
            <span class="text-danger">*</span>
            {{ Form::date('date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>

        <!-- Branch Selector -->
        <div class="form-group col-md-4">
            {{ Form::label('branch_id', __('Branch'), ['class' => 'form-label']) }}
            <span class="text-danger">*</span>
            {{ Form::select('branch_id', $branches, null, ['class' => 'form-control select', 'required' => 'required', 'id' => 'modal_branch_id']) }}
        </div>

        <!-- Class Selector (Cascaded) -->
        <div class="form-group col-md-4">
            {{ Form::label('class_id', __('Class'), ['class' => 'form-label']) }}
            <span class="text-danger">*</span>
            <select name="class_id" id="modal_class_id" class="form-control select" required disabled>
                <option value="">{{ __('Select Class') }}</option>
            </select>
        </div>

        <!-- Income Type -->
        <div class="form-group col-md-4">
            {{ Form::label('income_type', __('Income / Fee Type'), ['class' => 'form-label']) }}
            <span class="text-danger">*</span>
            {{ Form::text('income_type', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => __('e.g. Trip Fee')]) }}
        </div>

        <!-- Bank Account (Head Imprest Only) -->
        <div class="form-group col-md-4">
            {{ Form::label('bank_id', __('Bank Account (Head Imprest)'), ['class' => 'form-label']) }}
            <span class="text-danger">*</span>
            <select name="bank_id" id="modal_bank_id" class="form-control select" required disabled>
                <option value="">{{ __('Select Bank Account') }}</option>
            </select>
        </div>

        <!-- Chart of Account (Income Category) -->
        <div class="form-group col-md-4">
            {{ Form::label('coa_id', __('Income Category (COA Head)'), ['class' => 'form-label']) }}
            <span class="text-danger">*</span>
            {{ Form::select('coa_id', $chartAccounts, null, ['class' => 'form-control select', 'required' => 'required', 'placeholder' => __('Select COA')]) }}
        </div>

        <!-- Description -->
        <div class="form-group col-md-12">
            {{ Form::label('description', __('Description / Narration'), ['class' => 'form-label']) }}
            {{ Form::textarea('description', '', ['class' => 'form-control', 'rows' => 2, 'placeholder' => __('Enter Description')]) }}
        </div>
    </div>

    <hr>
    
    <div class="row">
        <div class="col-12 d-flex justify-content-between align-items-center mb-3">
            <h5 class="m-0">{{ __('Student Entries') }}</h5>
            <button type="button" class="btn btn-sm btn-primary" id="add-student-row" data-bs-toggle="tooltip" title="{{ __('Add Row') }}"><i class="ti ti-plus"></i> {{ __('Add Row') }}</button>
        </div>
        <div class="col-12">
            <div style="max-height: 250px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px;">
                <table class="table table-bordered mb-0" id="student-entries-table">
                    <thead style="position: sticky; top: 0; background-color: #f8f9fa; z-index: 10;">
                        <tr>
                            <th>{{ __('Student') }} <span class="text-danger">*</span></th>
                            <th>{{ __('Amount') }} <span class="text-danger">*</span></th>
                            <th width="50"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="entries[0][student_id]" class="form-control student-select" required disabled>
                                    <option value="">{{ __('Select Student') }}</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="entries[0][amount]" class="form-control" required step="0.01" min="0.01" placeholder="{{ __('Amount') }}">
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-danger remove-row" data-bs-toggle="tooltip" title="{{ __('Remove Row') }}"><i class="ti ti-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{__('Record Income')}}" class="btn btn-primary">
</div>
{{ Form::close() }}

<script>
    $(document).ready(function() {
        // Initialize tooltips in modal
        if (typeof $.fn.tooltip === 'function') {
            $('[data-bs-toggle="tooltip"]').tooltip();
        }

        var rowIdx = 1;
        var studentOptions = '<option value="">{{ __("Select Student") }}</option>';
        var prevBranchId = $('#modal_branch_id').val();

        // Cascading classes when branch changes
        $('#modal_branch_id').on('change', function() {
            var branchId = $(this).val();
            var classSelect = $('#modal_class_id');
            var bankSelect = $('#modal_bank_id');

            // Check if any student is currently selected
            var anyStudentSelected = false;
            $('.student-select').each(function() {
                if ($(this).val()) {
                    anyStudentSelected = true;
                }
            });

            if (anyStudentSelected) {
                if (!confirm("Changing branch will lead to clear all the items to be cleared.")) {
                    $(this).val(prevBranchId);
                    return;
                }
            }

            prevBranchId = branchId;
            
            // Clear student entries and reset
            classSelect.empty().append('<option value="">{{ __("Select Class") }}</option>').prop('disabled', true);
            bankSelect.empty().append('<option value="">{{ __("Select Bank Account") }}</option>').prop('disabled', true);
            studentOptions = '<option value="">{{ __("Select Student") }}</option>';
            
            var tbody = $('#student-entries-table tbody');
            tbody.empty();
            rowIdx = 0;
            var tr = '<tr>' +
                        '<td>' +
                            '<select name="entries[' + rowIdx + '][student_id]" class="form-control student-select" required disabled>' +
                                studentOptions +
                            '</select>' +
                        '</td>' +
                        '<td>' +
                            '<input type="number" name="entries[' + rowIdx + '][amount]" class="form-control" required step="0.01" min="0.01" placeholder="{{ __("Amount") }}">' +
                        '</td>' +
                        '<td>' +
                            '<button type="button" class="btn btn-sm btn-danger remove-row" data-bs-toggle="tooltip" title="{{ __("Remove Row") }}"><i class="ti ti-trash"></i></button>' +
                        '</td>' +
                    '</tr>';
            tbody.append(tr);
            rowIdx++;
            if (typeof $.fn.tooltip === 'function') {
                tbody.find('[data-bs-toggle="tooltip"]').tooltip();
            }
            
            if (branchId) {
                $.ajax({
                    url: '{{ route("student-incomes.get-classes") }}',
                    type: 'GET',
                    data: { branch_id: branchId },
                    success: function(data) {
                        $.each(data, function(id, name) {
                            classSelect.append('<option value="' + id + '">' + name + '</option>');
                        });
                        classSelect.prop('disabled', false);
                    }
                });

                $.ajax({
                    url: '{{ route("student-incomes.get-bank-accounts") }}',
                    type: 'GET',
                    data: { branch_id: branchId },
                    success: function(data) {
                        $.each(data, function(id, name) {
                            bankSelect.append('<option value="' + id + '">' + name + '</option>');
                        });
                        bankSelect.prop('disabled', false);
                    }
                });
            }
        });

        // Cascading students when class changes
        $('#modal_class_id').on('change', function() {
            var classId = $(this).val();
            var branchId = $('#modal_branch_id').val();
            
            studentOptions = '<option value="">{{ __("Select Student") }}</option>';
            $('.student-select').each(function() {
                if (!$(this).val()) {
                    $(this).empty().append(studentOptions).prop('disabled', true);
                }
            });
            
            if (classId && branchId) {
                $.ajax({
                    url: '{{ route("student-incomes.get-students") }}',
                    type: 'GET',
                    data: { branch_id: branchId, class_id: classId },
                    success: function(data) {
                        $.each(data, function(index, student) {
                            studentOptions += '<option value="' + student.id + '">' + student.name + '</option>';
                        });
                        $('.student-select').each(function() {
                            if (!$(this).val()) {
                                $(this).empty().append(studentOptions).prop('disabled', false);
                            }
                        });
                    }
                });
            }
        });

        $('#add-student-row').on('click', function() {
            var isDisabled = $('#modal_class_id').val() ? '' : 'disabled';
            var tr = '<tr>' +
                        '<td>' +
                            '<select name="entries[' + rowIdx + '][student_id]" class="form-control student-select" required ' + isDisabled + '>' +
                                studentOptions +
                            '</select>' +
                        '</td>' +
                        '<td>' +
                            '<input type="number" name="entries[' + rowIdx + '][amount]" class="form-control" required step="0.01" min="0.01" placeholder="{{ __("Amount") }}">' +
                        '</td>' +
                        '<td>' +
                            '<button type="button" class="btn btn-sm btn-danger remove-row" data-bs-toggle="tooltip" title="{{ __("Remove Row") }}"><i class="ti ti-trash"></i></button>' +
                        '</td>' +
                    '</tr>';
            $('#student-entries-table tbody').prepend(tr);
            rowIdx++;
            if (typeof $.fn.tooltip === 'function') {
                $('#student-entries-table tbody tr:first [data-bs-toggle="tooltip"]').tooltip();
            }
        });

        $(document).on('click', '.remove-row', function() {
            if ($('#student-entries-table tbody tr').length > 1) {
                $(this).closest('tr').remove();
            } else {
                if (typeof show_toastr === 'function') {
                    show_toastr('error', '{{ __("At least one entry is required.") }}');
                } else {
                    alert('{{ __("At least one entry is required.") }}');
                }
            }
        });
    });
</script>
