{{-- {{ Form::open(['url' => 'employee-salary-proporal', 'method' => 'post']) }} --}}
{{ Form::model($salaryProposal, ['route' => ['employee-salary-proporal.update', $salaryProposal->id], 'method' => 'PUT']) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('emp_no', __('Employee No.'), ['class' => 'form-label']) }}
            {{ Form::text('emp_no', null, ['class' => 'form-control ', 'id' => 'emp_no']) }}
        </div>
        <div class="form-group col-md-4  mt-4">
            <div class="btn btn-md btn-outline-primary" id="search_empNO">Search</div>
        </div>
        <div class="col-md-2"></div>
        {{-- @if (\Auth::user()->type != 'Employee') --}}
        <div class="form-group col-md-6">
            {{ Form::label('employee', __('Employee*'), ['class' => 'form-label']) }}
            {{ Form::text('employee', @$salaryProposal->employees->name, ['class' => 'form-control ', 'readonly' => 'readonly', 'id' => 'employee']) }}
        </div>
        {{-- @endif --}}
        <div class="form-group col-md-6">
            {{ Form::label('department', __('Department'), ['class' => 'form-label']) }}
            {{ Form::text('department', @$salaryProposal->employees->department->name, ['class' => 'form-control ', 'readonly' => 'readonly', 'id' => 'department']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('designation', __('Designation'), ['class' => 'form-label']) }}
            {{ Form::text('designation', @$salaryProposal->employees->designation->name, ['class' => 'form-control ', 'readonly' => 'readonly', 'id' => 'designation']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
            {{ Form::text('status', @$salaryProposal->employees->category, ['class' => 'form-control ', 'readonly' => 'readonly', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-12">
            {{ Form::label('Payscale', __('Payscale'), ['class' => 'form-label']) }}
            {{ Form::select('payscale', $payscale, null, ['class' => 'form-control ', 'required' => 'required', 'id' => 'payscale']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('income_tax', __('Income Tax'), ['class' => 'form-label']) }}
            {{ Form::text('income_tax', null, ['class' => 'form-control ', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('other_deduction', __('Other Deduction'), ['class' => 'form-label']) }}
            {{ Form::text('other_deduction', null, ['class' => 'form-control ', 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('EOBI', __('EOBI'), ['class' => 'form-label']) }}
            {{ Form::text('EOBI', null, ['class' => 'form-control ', 'required' => 'required']) }}
        </div>
        {{-- // all fields gROSS,income tax,other deduction,EOBI,Net salary / --}}
        <div class="form-group col-md-6">
            {{ Form::label('gross', __('Gross'), ['class' => 'form-label']) }}
            {{ Form::text('gross', null, ['class' => 'form-control ', 'required' => 'required', 'readonly' => 'readonly']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('net_salary', __('Net Salary'), ['class' => 'form-label']) }}
            {{ Form::text('net_salary', null, ['class' => 'form-control ', 'required' => 'required', 'readonly' => 'readonly']) }}
        </div>

        {{-- /// pay method , bank name , bank account  --}}
        <div class="form-group col-md-4">
            {{ Form::label('pay_method', __('Pay Method'), ['class' => 'form-label']) }}
            {{ Form::text('pay_method', null, ['class' => 'form-control ', 'required' => 'required', 'id' => 'pay_method']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('bank_name', __('Bank Name'), ['class' => 'form-label']) }}
            {{ Form::text('bank_name', null, ['class' => 'form-control ', 'required' => 'required', 'id' => 'bank_name']) }}
        </div>
        <div class="form-group col-md-4">
            {{ Form::label('bank_account', __('Bank Account'), ['class' => 'form-label']) }}
            {{ Form::text('bank_account', null, ['class' => 'form-control ', 'required' => 'required', 'id' => 'bank_account']) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn  btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" id="submit_btn" value="{{ __('Update') }}" class="btn  btn-outline-primary">
</div>
{{ Form::close() }}
<script>
    // on click search btn
    $(document).ready(function() {
        $('#search_empNO').click(function() {
            var empNo = $('#emp_no').val();
            if (empNo) {
                $.ajax({
                    url: '{{ route('employee.detail') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        emp_no: empNo
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#employee').val(response.employee[0].name);
                            $('#department').val(response.department.name);
                            $('#designation').val(response.designation.name);

                            if (response.employee[0].employee_payscale_details.length > 0) {
                                var payscale_det = response.employee[0]
                                    .employee_payscale_details[0];
                                $('#bank_account').val(payscale_det.account_number);
                                $('#bank_name').val(payscale_det.paymode);
                                $('#pay_method').val(payscale_det.paymode);
                            }
                            $('#status').val(response.employee[0].category);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 404) {
                            alert("Employee not found. Please check the Employee Number.");
                        } else {
                            alert("An error occurred. Please try again.");
                        }
                    }
                });
            }
        });

        $('#payscale').change(function() {
            $('#EOBI').val('');
            $('#income_tax').val('');
            $('#other_deduction').val('');
            $('#net_salary').val('');
            $('#gross').val('');
            var payscale = $(this).val();
            if (payscale) {
                $.ajax({
                    url: '{{ url('/employee-scale-details') }}/' + payscale, // Pass ID in URL
                    type: 'GET',
                    success: function(response) {
                        $('#gross').val(response.e);
                    }
                });
            }
        });
        // now on enter values of (eobi,income tax,other deduction) from gross and put in net salary
        function calculateNetSalary() {
            var gross = parseFloat($('#gross').val()) || 0;
            var eobi = parseFloat($('#EOBI').val()) || 0;
            var incomeTax = parseFloat($('#income_tax').val()) || 0;
            var otherDeduction = parseFloat($('#other_deduction').val()) || 0;
            var netSalary = gross - (eobi + incomeTax + otherDeduction);
            $('#net_salary').val(netSalary.toFixed(2));
        }
        $('#EOBI, #income_tax, #other_deduction').on('input', function() {
            if ($('#gross').val() == '') {
                alert('Please select payscale first.');
                $('#EOBI').val('');
                $('#income_tax').val('');
                $('#other_deduction').val('');
                return;
            } else {
                calculateNetSalary();
            }
        });
    });
</script>
