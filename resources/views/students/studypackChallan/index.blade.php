@extends('layouts.admin')
@section('page-title')
    {{ __('Manage StudyPack Challans') }}
@endsection
@push('script-page')
    <script>
        $(document).on('change', '#class_select', function() {
            var classId = $(this).val();
            if (classId) {
                classStudents(classId);
            }
        });
    </script>
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
                    
                    if (result.status == 'success') {
                        var $classSelect = $('#class_select');
                        // Remove previous custom select wrapper and instance
                        if ($classSelect[0] && $classSelect[0].customSelectInstance) {
                            $classSelect[0].customSelectInstance.destroy();
                            delete $classSelect[0].customSelectInstance;
                        }
                        if ($classSelect.next('.custom-select-wrapper').length) {
                            $classSelect.next('.custom-select-wrapper').remove();
                        }
                        $classSelect.removeClass('custom-select');

                        // Clear and append new options
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

                        // Re-add class and re-init
                        $classSelect.addClass('custom-select');
                        $classSelect.show();
                        // Directly create new CustomSelect instance for this select only
                        if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                            window.CustomSelect.create($classSelect[0]);
                        }

                        // Session select update (unchanged)
                        $('#session').empty();
                        $('#session').append($('<option>', {
                            value: 'all',
                            text: 'All Session'
                        }));
                        for (var i = 0; i < result.session.length; i++) {
                            var session = result.session[i];
                            $('#session').append($('<option>', {
                                value: session.id,
                                text: session.title
                            }));
                        }
                    }
                    if (result.status == 'error') {}

                }
            });
        }
    
        function classStudyPack(id) {
            var customer = $('#customerselect').val();
            var session = $('#session').val();
            // var branchId = $('#branches').val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('branch.session_class') }}",
                type: "POST",
                data: {
                    id: id,
                    session: session,
                    type: 'studypack'
                },
                dataType: 'json',
                success: function(result) {
                    console.log(result);
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
                    }
                    if (result.status == 'error') {}
    
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

                // Destroy old custom select instance if exists
                if ($studentSelect[0] && $studentSelect[0].customSelectInstance) {
                    $studentSelect[0].customSelectInstance.destroy();
                    delete $studentSelect[0].customSelectInstance;
                }
                if ($studentSelect.next('.custom-select-wrapper').length) {
                    $studentSelect.next('.custom-select-wrapper').remove();
                }
                $studentSelect.removeClass('custom-select');

                // Clear and append new options
                $studentSelect.empty();
                $studentSelect.append($('<option>', {
                    value: 'all',
                    text: 'All Students'
                }));
                for (var id in result.students) {
                    if (result.students.hasOwnProperty(id)) {
                        $studentSelect.append($('<option>', {
                            value: id,
                            text: result.students[id]
                        }));
                    }
                }

                // Re-add class and re-init custom select
                $studentSelect.addClass('custom-select');
                $studentSelect.show();
                if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                    window.CustomSelect.create($studentSelect[0]);
                }

                // Set default to 'all'
                $studentSelect.val('all');
            }
        }
    });
}
    function validateGenerateForm() {
        var branch = $('#branches').val();
        var session = $('#session').val();
        var cls = $('#class_select').val();
        var studypack = $('#stdy_select').val();
        var student = $('#student_select').val();
        var challanDate = $('#challan_date').val();

        if (!branch || branch === 'all') {
            alert('Please select a specific Branch to generate challans.');
            return false;
        }
        if (!session || session === 'all') {
            alert('Please select a specific Session to generate challans.');
            return false;
        }
        if (!cls || cls === 'all') {
            alert('Please select a specific Class to generate challans.');
            return false;
        }
        if (!studypack) {
            alert('Please select a StudyPack.');
            return false;
        }
        if (!student) {
            alert('Please select a Student.');
            return false;
        }
        if (!challanDate) {
            alert('Please select a Challan Date.');
            return false;
        }
        return true;
    }
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('All StudyPack Challans') }}</li>
@endsection

{{-- @section('action-btn')
<div class="float-end">
    <a href="#!" data-url="{{ route('studypackchallan.create') }}" data-bs-title="{{__('Create')}}"
        data-bs-title="{{__('Create')}}" class="btn mx-1 btn-sm btn-outline-primary" data-ajax-popup="true">
        <span class="btn-inner--icon">Create</span>
    </a>
</div>
@endsection --}}
@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body filter_change">
                    {{ Form::open(['route' => ['studypackchallan.index'], 'method' => 'GET', 'id' => 'studypack_challan_form']) }}
                    @csrf
                    <div class="row d-flex align-items-center">
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                            <div class="btn-box">
                                {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                {{ Form::select('branches', $branches, $filterBranchId, ['class' => 'form-control select', 'onchange' => 'branchcustomer(this.value)', 'id' => 'branches']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                            <div class="btn-box">
                                {{ Form::label('session', __('Session'), ['class' => 'form-label']) }}
                                {{ Form::select('session', $session, $filterSessionId, ['class' => 'form-control select', 'id' => 'session']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                            <div class="btn-box">
                                {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                {{ Form::select('class', $class, $filterClassId, ['class' => 'form-control select custom-select', 'id' => 'class_select', 'onchange' => 'classStudyPack(this.value)']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                            <div class="btn-box">
                                {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                                {{ Form::select('status', ['' => 'All Statuses', 'Assigned' => 'Assigned', 'Paid' => 'Paid', 'Partial Paid' => 'Partial Paid'], $filterStatus, ['class' => 'form-control select', 'id' => 'status']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                            <div class="btn-box">
                                {{ Form::label('student', __('Students'), ['class' => 'form-label']) }}
                                {{ Form::select('student', [], 'all', ['class' => 'form-control select custom-select', 'id' => 'student_select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                            <div class="btn-box">
                                {{ Form::label('Studypack', __('StudyPack'), ['class' => 'form-label']) }}
                                {{ Form::select('Studypack', $stdy_pack, '', ['class' => 'form-control select', 'id' => 'stdy_select']) }}
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mb-3">
                            <div class="btn-box">
                                {{ Form::label('challan_date', __('Challan Date'), ['class' => 'form-label']) }}<span style="color: red">&nbsp;(for the month)</span>
                                {!! Form::date('challan_date', null, ['class' => 'form-control', 'id' => 'challan_date']) !!}
                            </div>
                        </div>
                        <div class="col-auto mt-4 pt-1 mb-3 d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-sm btn-primary">{{ __('Search') }}</button>
                            <a href="{{ route('studypackchallan.index') }}" class="btn btn-sm btn-danger">{{ __('Reset') }}</a>
                            <button type="submit" formmethod="POST" formaction="{{ route('studypackchallan.store') }}" class="btn btn-sm btn-success" onclick="return validateGenerateForm()">{{ __('Generate Challan') }}</button>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    {{-- @endif --}}

    <!-- <button id="printButton" class="btn mx-1 btn-sm btn-outline-success" onclick="getCheckedRowData()">Print Challan</button> -->
    <div class="modal fade" id="printModal" tabindex="-1" aria-labelledby="printModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printModalLabel">Download Options</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Select Download options:</p>
                    <button class="btn btn-primary" onclick="printSeparatePDF()">Separate PDF</button>
                    <button class="btn btn-primary" onclick="printSinglePDF()">Single PDF</button>
                </div>
            </div>
        </div>
    </div>
    <table class="datatable">
        <thead>
            <tr class="table_heads">
                <th>#</th>
                <th>{{ __('Challan No.') }}</th>
                <th>{{ __('Student Name') }}</th>
                <th>{{ __('Challan Month') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('status') }}</th>
                <th>{{ __('Issue Date') }}</th>
                <th>{{ __('Due Date') }}</th>
                <th>{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($studypacks as $challan)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $challan->challanNo }}</td>
                    @php
                        $st_id = $challan->student_id;
                        $studentData = App\Models\StudentRegistration::with('enrollment.class', 'enrollment.section')
                            ->where('id', $st_id)
                            ->first();
                    @endphp
                    <td>{{ $studentData->stdname }}</td>
                    <td>{{ \Carbon\Carbon::parse($challan->challan_date)->format('F,Y') }}</td>
                    @php
                        $challan_heads = \App\Models\StudyPackChallanItems::where('challan_id', $challan->id)->get();
                        $totalAmount = 0;
                        foreach ($challan_heads as $head) {
                            $totalAmount += $head['price'];
                        }
                    @endphp
                    <td>{{ $totalAmount }}</td>
                    <td>{{ $challan->status }}</td>
                    <td>{{ $challan->issue_date }}</td>
                    <td>{{ $challan->due_date }}</td>
                    <td>
                        <div class="action-btn ms-2">
                            <a href="{{ route('studypackchallan.show', $challan->id) }}" target="_blank"
                                class="btn btn-sm btn-outline-primary pt-2" data-bs-title="view">
                                <span class="btn-inner--icon"><i class="ti ti-eye"></i></span>
                            </a>
                            <a href="{{ route('studypackchallan.edit', $challan->id) }}"
                                class="mx-1 btn btn-sm btn-outline-primary pt-2" data-bs-title="edit">
                                <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                            </a>
                            @can('create payment invoice')
                                <a href="#" data-url="{{ route('studypack.payment', $challan->id) }}"
                                    data-ajax-popup="true" class="btn btn-sm btn-outline-success pt-2"
                                    data-bs-title="{{ __('Add Payment') }}"><span class="btn-inner--icon"><i
                                            class="ti ti-report-money"></i></span></a><br>
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

