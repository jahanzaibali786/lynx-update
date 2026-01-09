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
            var customer = $('#customerselect').val();
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
                    console.log(result);
                    if (result.status == 'success') {
                        // $('#session_id').empty();
                        // $('#session_id').append($('<option>', {
                        //     value: '',
                        //     text: 'Select Session'
                        // }));
                        // for (var i = 0; i < result.session.length; i++) {
                        //     var session = result.session[i];
                        //     $('#session_id').append($('<option>', {
                        //         value: session.id,
                        //         text: session.year
                        //     }));
                        // }
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
                    if (result.status == 'error') {}

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
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#class_from').empty();
                    $('#class_from').append('<option value="">{{ __('Select Class ') }}</option>');
                    for (let index = 0; index < data.length; index++) {
                        $('#class_from').append('<option value="' + data[index]['id'] + '">' + data[
                            index][
                            'name'
                        ] + '</option>');
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
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['account-wise-fee.index'], 'method' => 'GET', 'id' => 'employee_submit']) }}
                        <div class="row d-flex justify-content-end ">
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
                                    onclick="document.getElementById('employee_submit').submit(); return false;"
                                     data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('account-wise-fee.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                     data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                <a id="submitChecked" class="btn mx-1 btn-sm btn-outline-warning" onclick="submitChecked()">Update</a>
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
    </style>
    <div class="table-responsive">
        <table class="table">
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
                    <th><input id="checkAll" type="checkbox"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($class_wise_fee as $fee)
                    @php
                        $student = \App\Models\StudentFeeStructure::with('student', 'student.enrollment')
                            ->where('head_id', @$fee->head_id)
                            ->where('class_id', @$fee->class_id)
                            ->get();
                    @endphp
                    @foreach ($student as $student_fee)
                    <tr>
                        <td> {{ @$student_fee->student->enrollment->enrollId }} </td>
                        <td> {{ @$student_fee->student->stdname }}</td>
                        <td> {{ @$student_fee->student->fathername }}</td>
                        <td> {{ @$student_fee->student->enrollment->class->name }}</td>
                        <td><input type="text" name="" id="" value="{{ @$student_fee->amount }}"
                                disabled></td>
                                @php
                            $discountAmount = @$student_fee->amount - (@$student_fee->discount / 100) * @$student_fee->amount;
                            
                            @endphp

                        <td><input type="text" id="amount_{{$loop->iteration}}" value="{{ @$fee->amount }}" oninput="calculateNetAmount({{$loop->iteration}})"></td>
                        <td><input type="text" id="discount_{{$loop->iteration}}" value="{{ @$student_fee->discount }}" oninput="calculateNetAmount({{$loop->iteration}})"></td>
                        <td><input type="text" id="netAmount_{{$loop->iteration}}" value="{{ @$discountAmount }}" readonly></td>            
                        <td>
                            <input type="checkbox" name="checked[]">
                            <input type="hidden" class="branch_id" id="brnch_{{$loop->iteration}}" value="{{ @$student_fee->student->enrollment->owned_by }}">
                            <input type="hidden" class="class_id" id="clsId_{{$loop->iteration}}" value="{{ @$fee->class_id }}">
                            <input type="hidden" class="student_id" id="stdid_{{$loop->iteration}}" value="{{ @$student_fee->student_id }}">
                            <input type="hidden" class="fee_val" value="{{$loop->iteration}}">
                            <input type="hidden" class="reg_student_id" id="stdregid_{{$loop->iteration}}" value="{{ @$student_fee->reg_id }}">
                            <input type="hidden" class="head_id" id="headid_{{$loop->iteration}}" value="{{ @$fee->head_id }}">
                        </td>                        
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    <script>
           function calculateNetAmount(id) {
    console.log(id);
    let amount = parseFloat(document.getElementById('amount_' + id).value) || 0;
    let discount = parseFloat(document.getElementById('discount_' + id).value) || 0;
    if (discount > 100) {
        discount = 100;
        document.getElementById('discount_' + id).value = 100;
    }
    if (isNaN(amount) || isNaN(discount)) {
        document.getElementById('netAmount_' + id).value = '0.00';
        return;
    }
    let discountAmount = amount - (amount * discount / 100);
    document.getElementById('netAmount_' + id).value = discountAmount.toFixed(2);
}

    </script>
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
    function submitChecked() {
        console.log('Update button clicked');
        
        let selectedData = [];
        var rowCheckboxes = document.querySelectorAll('input[name="checked[]"]');

        rowCheckboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                const row = checkbox.closest('tr');
                const rowid = parseFloat(row.querySelector('.fee_val').value);
                const amount = row.querySelector('#amount_' + rowid).value;
                const discount = row.querySelector('#discount_' + rowid).value;
                const netAmount = row.querySelector('#netAmount_' + rowid).value;
                const studentId = row.querySelector('#stdid_' + rowid).value;
                const regId = row.querySelector('#stdregid_' + rowid).value;
                const classId = row.querySelector('#clsId_' + rowid).value;
                const branchId = row.querySelector('#brnch_' + rowid).value;
                const headId = row.querySelector('#headid_' + rowid).value;

                console.log(rowid, studentId, classId, branchId, amount, discount, netAmount);
                
                selectedData.push({
                    rowid: rowid,
                    studentId: studentId,
                    classId: classId,
                    branchId: branchId,
                    amount: amount,
                    discount: discount,
                    netAmount: netAmount,
                    regId: regId,
                    headId: headId,
                });
            }
        });

        fetch('{{ route('account-wise-fee.save') }}', { 
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
            },
            body: JSON.stringify(selectedData)
        })
        .then(response => response.json()) 
        .then(data => {
            if (data.status === 'success') {
                alert(data.message); // Show success message
                window.location.reload(); // Reload the window on success
            } else {
                alert('Error saving data: ' + data.message); // Show error message
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>
    
@endsection
