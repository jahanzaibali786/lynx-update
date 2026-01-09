{{ Form::model($advance, array('route' => array('employee-advance.update', $advance->id), 'method' => 'PUT')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
                {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
                {{ Form::select('branches', $branches, null, ['class' => 'form-control select', 'onchange' => 'branchemployees(this.value)']) }}
            </div>
            @if(\Auth::user()->type != 'Employee')
            <div class="form-group col-md-6">
                {{Form::label('employee_id', __('Employee'), ['class' => 'form-label'])}}<span style="color: red">
                    *</span>
                {{Form::select('employee_id', $employee,null, array('class' => 'form-control select', 'required' => 'required', 'id' => 'employee_id', 'placeholder' => __('Select Employee')))}}
            </div>
            @endif
            <div class="form-group col-md-6">
                {{ Form::label('amount', __('Advnace Amount'), ['class' => 'form-label amount_label']) }}
                {{ Form::number('amount',$advance->advance_amount, array('class' => 'form-control ', 'required' => 'required', 'step' => '0.01','id'=>'loan_amount')) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('date', __('Advnace date'), ['class' => 'form-label amount_label']) }}
                {{ Form::date('date',$advance->advance_date, array('class' => 'form-control ', 'required' => 'required')) }}
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    {{ Form::label('reason', __('Reason')) }}
                    {{ Form::textarea('reason', $advance->advance_reason, array('class' => 'form-control ', 'required' => 'required', 'rows' => 3)) }}
                </div>
            </div>
    
        </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-outline-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Update') }}" class="btn btn-outline-primary">
</div>
{{ Form::close() }}

<script>
$(document).ready(function() {
    $('#employee_id').change(function() {
        var employeeId = $(this).val();
        if(employeeId) {
            $.ajax({
                url: '{{ url('get-employee-serv-sec') }}/' + employeeId,
                type: 'GET',
                success: function(response) {
                    if(response) {
                        $('#service_tenure').val(response.service_tenure);
                        $('#department').val(response.emp_department);
                        $('#total_sec').val(response.total_sec);
                        $('#max_amount').val((response.total_sec) / 2);
                        if (isNaN(response.total_sec) || response.total_sec <= 0) {
                            $('#submit_btn').prop('disabled', true);
                        } else {
                            $('#submit_btn').prop('disabled', false);
                        }
                    } else {
                        $('#service_tenure').val('0');
                        $('#total_sec').val('0');
                        $('#submit_btn').prop('disabled', true);
                    }
                },
                error: function() {
                    $('#service_tenure').val('');
                    $('#total_sec').val('');
                    $('#submit_btn').prop('disabled', true);
                }
            });
        } else {
            $('#service_tenure').val('');
            $('#total_sec').val('');
            $('#submit_btn').prop('disabled', true);
        }
    });

    $('#loan_amount').on('input', function() {
        var loanAmount = parseFloat($(this).val());
        var totalSecurity = parseFloat($('#total_sec').val());

        if (isNaN(totalSecurity) || totalSecurity <= 0) {
            $('#loan_error').text('(Security amount must be greater than 0 to take a loan.)');
            $('#submit_btn').prop('disabled', true);
        } else if (loanAmount > totalSecurity / 2) {
            $('#loan_error').text('(Loan amount cannot exceed ' + totalSecurity / 2 + ')');
            $('#submit_btn').prop('disabled', true);
        } else {
            $('#loan_error').text('');
            $('#submit_btn').prop('disabled', false);
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    var today = new Date().toISOString().split('T')[0];
    document.getElementById('from_pay_month').setAttribute('min', today);
});

document.getElementById('pay_date').addEventListener('keyup', function() {
    updateProbationEndDate();
});

document.getElementById('from_pay_month').addEventListener('change', function() {
    var selectedDate = new Date(this.value);
    var today = new Date();

    if (selectedDate < today) {
        alert('Please select today or a future date.');
        this.value = today.toISOString().split('T')[0];
    }

    updateProbationEndDate();
});

function updateProbationEndDate() {
    var p_id = parseInt($('#pay_date').val());
    var currentDate = new Date($('#from_pay_month').val());
    
    if (!isNaN(currentDate.getTime())) {
        if (isNaN(p_id)) {
            var formattedDate = currentDate.toISOString().slice(0, 10);
            document.getElementById('loan_ended').value = formattedDate;
        } else {
            var futureDate = new Date(currentDate.setMonth(currentDate.getMonth() + p_id));
            var formattedDate = futureDate.toISOString().slice(0, 10);
            document.getElementById('loan_ended').value = formattedDate;
        }
    }
}
</script>
