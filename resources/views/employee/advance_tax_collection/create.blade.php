<script>
    function advanceTaxCollectionModalEmployees(id, target) {
        var prevVal = $(target).val();
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ route('branch.employees') }}",
            type: "POST",
            data: {
                id: id
            },
            dataType: 'json',
            success: function(result) {
                if (result.status == 'success') {
                    var $employee = $(target);
                    if ($employee[0] && $employee[0].customSelectInstance) {
                        try {
                            $employee[0].customSelectInstance.destroy();
                        } catch (e) {}
                        delete $employee[0].customSelectInstance;
                    }
                    if ($employee.next('.custom-select-wrapper').length) {
                        $employee.next('.custom-select-wrapper').remove();
                    }
                    $employee.removeClass('custom-select');
                    $employee.empty().append($('<option>', {
                        value: '',
                        text: 'Select Employee'
                    }));
                    var added = 0;
                    for (var j = 0; j < result.employee.length; j++) {
                        var emp = result.employee[j];
                        $employee.append($('<option>', {
                            value: emp.id,
                            text: emp.name
                        }));
                        added++;
                    }
                    if (added === 0) {
                        $employee.append($('<option>', {
                            value: '',
                            text: 'No active employees',
                            disabled: true
                        }));
                    }

                    $employee.addClass('custom-select').show();
                    if (window.CustomSelect && typeof window.CustomSelect.create === 'function') {
                        window.CustomSelect.create($employee[0]);
                    }

                    if (prevVal && $employee.find('option[value="' + prevVal + '"]').length) {
                        $employee.val(prevVal);
                        if ($employee[0] && $employee[0].customSelectInstance && typeof $employee[0].customSelectInstance.refresh === 'function') {
                            $employee[0].customSelectInstance.refresh();
                        }
                    }
                }
            }
        });
    }
</script>
{{ Form::open(['route' => 'advance-tax-collection.store', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::select('branches', $branches, null, ['class' => 'form-control select', 'required' => 'required', 'onchange' => 'advanceTaxCollectionModalEmployees(this.value, "#advance_tax_employee_id")']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('employee_id', __('Employee'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::select('employee_id', $employees, null, ['class' => 'form-control select custom-select', 'required' => 'required', 'id' => 'advance_tax_employee_id']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('tax_month', __('Tax Month'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::month('tax_month', null, ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('collection_date', __('Collection Date'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::date('collection_date', date('Y-m-d'), ['class' => 'form-control', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}<span class="text-danger"> *</span>
            {{ Form::number('amount', null, ['class' => 'form-control', 'required' => 'required', 'step' => '0.01', 'min' => '0.01']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('payment_method', __('Payment Method'), ['class' => 'form-label']) }}
            {{ Form::select('payment_method', ['' => __('Select Payment Method')] + $paymentMethods, null, ['class' => 'form-control select']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('reference', __('Reference'), ['class' => 'form-label']) }}
            {{ Form::text('reference', null, ['class' => 'form-control', 'maxlength' => 191]) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('remarks', __('Remarks'), ['class' => 'form-label']) }}
            {{ Form::textarea('remarks', null, ['class' => 'form-control', 'rows' => 2]) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}
