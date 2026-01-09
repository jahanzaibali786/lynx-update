@extends('layouts.admin')
@section('page-title')
{{__('Transfer Application')}}
@endsection
@push('script-page')
<script src="{{ asset('js/jquery.min.js') }}"></script>
{{-- <script src="{{asset('js/jquery.repeater.min.js')}}"></script> --}}
<script>
$(document).ready(function() {
    $('#calculateBalance').on('click', function(event) {
        event.preventDefault();

        var studentId = {{@$studenttransfer->student_id}};
        var transferId = {{@$studenttransfer->id}};

        $.ajax({
            url: '{{ route('transfer_balance.calculate') }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                student_id: studentId,
                transfer_id: transferId,
            },
            success: function(response) {
                if (response.error) {
                    show_toastr('error', response.error, 'error');
                    return;
                }
                console.log(response.actual_fee);
                $('#actual_fee').val(response.actual_fee);
                $('#security_payable').val(response.security_deposit);
                $('#transfer_fee').val(response.transfer_fee);
                $('#total_receivables').val(response.total_receivables);
                // $('#net_balance').val(response.net_balance);
            },
            error: function(xhr, status, error) {

                alert('An error occurred while calculating the balance.');
                return;
            }
        });
    });
});
$(document).ready(function() {
    const maxRows = 6; // Set the maximum number of rows
    // Add a new row on clicking the plus button
    $('#dropdown-container').on('click', '.add-more', function() {
        let button = $(this);
        let rowCount = $('#dropdown-container .dropdown-wrapper').length;

        if (rowCount < maxRows) {
        // Change the current plus button to a minus button
        button.removeClass('btn-outline-primary add-more').addClass('btn-outline-danger remove-row');
        button.html('<span class="btn-inner--icon"><i class="ti ti-minus"></i></span>');

        // Clone the dropdown-wrapper and append it to the container
        let newDropdown = `
            <div class="dropdown-wrapper col-6 p-1" style="display: flex; align-items: center; gap: 12px;">
                <select name="fee_head[]" class="fee-head-dropdown form-control" required>
                    <option value="" selected disabled>Select Fee Head</option>
                    @foreach($classfee as $head_id => $fee_head)
                        <option value="{{ $head_id }}">{{ $fee_head }}</option>
                    @endforeach
                </select>
                <input type="number" name="val[]" min="1" class="form-control val" required>
                <button type="button" class="btn btn-sm add-more btn-outline-primary">
                    <span class="btn-inner--icon">Create</span>
                </button>
            </div>
        `;

        // Append the new dropdown
        $('#dropdown-container').append(newDropdown);
        } else {
            show_toastr('danger', 'You can only add up to ' + maxRows + ' rows.', 'danger');
        }
    });

    // Remove the row when the minus button is clicked
    $('#dropdown-container').on('click', '.remove-row', function() {
        $(this).closest('.dropdown-wrapper').remove();
    });

     // Submit the form using AJAX
    $('#submit-form').on('click', function() {
        // Collect data from the form
        const formData = $('#fee-structure-form').serialize();

        // Validate the form
        if ($('input.val').filter(function() { return this.value === ""; }).length > 0) {
            alert('Please fill in all fields.');
            return;
        }

        // Send data to the server
        $.ajax({
            url: '{{ route('transfer.add_head') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if(response.success == 'success'){
                    show_toastr('success', response.data, 'success');
                    // Optionally reset the form or handle UI changes
                    $('#fee-structure-form')[0].reset();
                    $('#dropdown-container').empty(); // Clear the dropdown container
                    $('.a').addClass('d-none'); // Clear the dropdown container
                }else{
                    show_toastr('error', response.data, 'error');
                }
            },
            error: function(xhr) {
                alert('An error occurred while submitting the form.');
            }
        });
    });

});
</script>
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item"><a href="{{route('transferstudent.index')}}">{{__('Transfer Student')}}</a></li>
<li class="breadcrumb-item">{{__('Transfer Application Detail')}}</li>
@endsection
@section('action-btn')
<div class="float-end"></div>
@endsection
@section('content')
<div class="card mt-4 p-4">
    <div class="row d-flex justify-content-start ">
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('student_name', __('Student Name'), ['class' => 'form-label']) }}
                {{ Form::text('student_name',@$studenttransfer->student->stdname,['class' => 'form-control' ,'readonly'=>'readonly']) }}
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('father_name', __('Fahter Name'), ['class' => 'form-label']) }}
                {{ Form::text('father_name',@$studenttransfer->student->fathername,['class' => 'form-control','readonly'=>'readonly']) }}
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('transfer_date', __('Transfer Date'), ['class' => 'form-label']) }}
                {{ Form::text('transfer_date',@$studenttransfer->transfer_date,['class' => 'form-control','readonly'=>'readonly']) }}
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('transfer_type', __('Transfer Type'), ['class' => 'form-label']) }}
                {{ Form::text('transfer_type',@$studenttransfer->transfer_type,['class' => 'form-control' ,'readonly'=>'readonly']) }}
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('branch_from', __('Branch From'), ['class' => 'form-label']) }}
                {{ Form::text('branch_from',@$studenttransfer->branchfrom->name,['class' => 'form-control' ,'readonly'=>'readonly']) }}
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('class_from', __('Class From'), ['class' => 'form-label']) }}
                {{ Form::text('class_from',@$studenttransfer->classfrom->name,['class' => 'form-control' ,'readonly'=>'readonly']) }}
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('section_from', __('Section From'), ['class' => 'form-label']) }}
                {{ Form::text('section_from',@$studenttransfer->sectionfrom->name,['class' => 'form-control' ,'readonly'=>'readonly']) }}
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('branch_to', __('Branch To'), ['class' => 'form-label']) }}
                {{ Form::text('branch_to',@$studenttransfer->branchto->name,['class' => 'form-control' ,'readonly'=>'readonly']) }}
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('class_to', __('Class To'), ['class' => 'form-label']) }}
                {{ Form::text('class_to',@$studenttransfer->classto->name,['class' => 'form-control' ,'readonly'=>'readonly']) }}
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('section_to', __('Section To'), ['class' => 'form-label']) }}
                {{ Form::text('section_to',@$studenttransfer->sectionto->name,['class' => 'form-control' ,'readonly'=>'readonly']) }}
            </div>
        </div>
        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('transfer_reason', __('Reason'), ['class' => 'form-label']) }}
                {{ Form::text('transfer_reason',@$studenttransfer->reason,['class' => 'form-control','readonly'=>'readonly']) }}
            </div>
        </div>
        @if(@$studenttransfer->challan)
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('challan_no', __('Challan No'), ['class' => 'form-label']) }}
                    {{ Form::text('challan_no',@$studenttransfer->challan->challanNo,['class' => 'form-control' ,'readonly'=>'readonly']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('challan_date', __('Challan Date'), ['class' => 'form-label']) }}
                    {{ Form::text('challan_date',@$studenttransfer->challan->challan_date,['class' => 'form-control' ,'readonly'=>'readonly']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}
                    {{ Form::text('issue_date',@$studenttransfer->challan->issue_date,['class' => 'form-control' ,'readonly'=>'readonly']) }}
                </div>
            </div>
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                <div class="btn-box">
                    {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}
                    {{ Form::text('due_date',@$studenttransfer->challan->due_date,['class' => 'form-control' ,'readonly'=>'readonly']) }}
                </div>
            </div>
        @endif
    </div>
    {{--
    <hr>
    @if(@$studenttransfer->challan)
        @if(@$studenttransfer->status != 'approved' && @$studenttransfer->status != 'rejected')
        <form id="fee-structure-form" class="row">
            @csrf
            <input type="hidden" name="transfer_id" value={{@$studenttransfer->id}}>
            <div id="dropdown-container" class="col-12" style="display: contents">
                <div class="dropdown-wrapper col-6 p-1" style="display: flex; align-items: center; gap: 12px;">
                    <select name="fee_head[]" class="fee-head-dropdown form-control" required>
                        <option value="" selected disabled>Select Fee Head</option>
                        @foreach($classfee as $head_id => $fee_head)
                            <option value="{{ $head_id }}">{{ $fee_head }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="val[]" min="1" class="form-control val" required>
                    <button type="button" class="btn btn-sm add-more btn-outline-primary">
                        <span class="btn-inner--icon">Create</span>
                    </button>
                </div>
            </div>
            <button type="button" id="submit-form" class="btn mx-1 btn-sm btn-outline-primary a" style="width:100px; margin-top: 8px; float: right">Submit</button>
        </form>
        @endif
    @endif
    --}}
    <hr>
    <div class="row d-flex justify-content-end mt-1 ">
        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('actual_fee', __('Actual Fee'), ['class' => 'form-label']) }}
                {{ Form::text('actual_fee', '', ['id' => 'actual_fee', 'class' => 'form-control', 'readonly' => 'readonly']) }}
            </div>
        </div>
        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('security_payable', __('Security Payable'), ['class' => 'form-label']) }}
                {{ Form::text('security_payable', '', ['id' => 'security_payable', 'class' => 'form-control', 'readonly' => 'readonly']) }}
            </div>
        </div>

        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('transfer_fee', __('Transfer Fee'), ['class' => 'form-label']) }}
                {{ Form::text('transfer_fee', '0', ['id' => 'transfer_fee', 'class' => 'form-control', 'readonly' => 'readonly']) }}
            </div>
        </div>

        {{-- <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('other_fee', __('Other Fee'), ['class' => 'form-label']) }}
                {{ Form::text('other_fee', '0', ['id' => 'other_fee', 'class' => 'form-control', 'readonly' => 'readonly']) }}
            </div>
        </div> --}}
        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
            <div class="btn-box">
                {{ Form::label('total_receivables', __('Total Receivable'), ['class' => 'form-label']) }}
                {{ Form::text('total_receivables', '0', ['id' => 'total_receivables', 'class' => 'form-control', 'readonly' => 'readonly']) }}
            </div>
        </div>

        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 d-flex justify-content-end mt-4">
            <div class="btn-box">
                <button id="calculateBalance"  title="Calculate User Balance" class="btn mx-1 btn-sm btn-outline-success">Calculate Balance</button>
                @if(@$studenttransfer->status != 'approved' && @$studenttransfer->status != 'rejected'  )
                    @if(@$studenttransfer->status != 'pending')
                        <a href="{{route('adminsend',@$studenttransfer->id)}}"  title="Send to Head Office" class="btn mx-1 btn-sm btn-outline-primary">Send to HO</a>
                    @endif
                @endif

                @if(Auth::user()->type == 'company')
                    @if(@$studenttransfer->status == 'pending' )
                        <a href="{{ route('transfer.change_status', [@$studenttransfer->id, 'approved']) }}" id="approve-btn"
                            class=" btn btn-sm  btn-outline-primary"  data-bs-title="{{ __('Approved Application') }}">
                            Approved
                        </a>
                        <a href="{{ route('transfer.change_status', [@$studenttransfer->id, 'rollback']) }}" id="reject-btn"
                            class=" btn btn-sm  btn-outline-danger"  data-bs-title="{{ __('Rollback Application') }}">
                            Rollback
                        </a>
                        <a href="{{ route('transfer.change_status', [@$studenttransfer->id, 'rejected']) }}" id="reject-btn"
                            class=" btn btn-sm  btn-outline-danger"  data-bs-title="{{ __('Rejected Application') }}">
                            Rejected
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
