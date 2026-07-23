@extends('layouts.admin')
@section('page-title')
    {{ __('Student Profile Report') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script>
        $(document).on('change', '#branch', function() {
            let branch = $(this).val();
            $.ajax({
                url: "{{ route('branch.class') }}",
                type: "POST",
                data: {
                    branch_id: branch,
                    _token: "{{ csrf_token() }}"
                },
                dataType: 'json',
                success: function(result) {
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
                    $classSelect.append($('<option>', { value: 'all', text: 'All Class' }));
                    for (var j = 0; j < result.length; j++) {
                        var cls = result[j];
                        $classSelect.append($('<option>', { value: cls.id, text: cls.name }));
                    }
                    $classSelect.addClass('custom-select');
                    $classSelect.show();
                    if (window.CustomSelect && typeof window.CustomSelect.create == 'function') {
                        window.CustomSelect.create($classSelect[0]);
                    }
                }
            });
        });
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Student Profile Report') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
    </div>
@endsection
@section('content')
    <div class="card mt-5">
        <div class="card-body filter_change">
            {{ Form::open(['route' => 'student_profile_report', 'method' => 'GET', 'id' => 'student_profile_report']) }}
            <div class="row d-flex justify-content-start">
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('branch', __('Branches'), ['class' => 'form-label']) }}
                        {{ Form::select('branch', $branches, request('branch'), ['class' => 'form-control select custom-select', 'id' => 'branch']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                        {{ Form::select('class', $classes, request('class'), ['class' => 'form-control select custom-select', 'id' => 'class_select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('session_id', __('Session'), ['class' => 'form-label']) }}
                        {{ Form::select('session_id', $sessions, request('session_id'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                    <div class="btn-box">
                        {{ Form::label('status', __('Status'), ['class' => 'form-label']) }}
                        {{ Form::select('status', $status, request('status'), ['class' => 'form-control select']) }}
                    </div>
                </div>
                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2 mt-4 d-flex align-items-center gap-2">
                    <a href="#" class="btn btn-sm btn-outline-primary"
                        onclick="document.getElementById('student_profile_report').submit(); return false;"
                        data-bs-title="Search">
                        <span class="btn-inner--icon">Search</span>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                id="reportActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Export
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="reportActionsDropdown">
                            <li>
                                <button class="dropdown-item" type="submit" name="export" value="excel">
                                    <i class="ti ti-file me-2"></i>Excel
                                </button>
                            </li>
                            <li>
                                <button class="dropdown-item" type="submit" name="export" value="pdf">
                                    <i class="ti ti-download me-2"></i>Pdf
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            {{ Form::close() }}
        </div>
    </div>

    <div id="printableArea">
        <div class="card mt-2 p-2">
            <div class="mt-1"
                style="margin: 0 auto; padding: 10px; width:100%; display:flex; justify-content: center; align-items: center; flex-direction: column;">
                <div style="width: 100%; text-align: center;">
                    <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School</b></p>
                </div>
                <div style="width: 100%; text-align: center;">
                    <p style="font-size:1rem; text-align: center; font-weight: 800;">Student Profile Report</p>
                </div>
                <div style="width: 100%; display: flex; justify-content: space-between;">
                    <p><b>Branch: </b>{{ @$branches[request('branch')] ?? 'All Branches' }}</p>
                </div>

                <div class="table-responsive maximumHeightNew mt-2" style="width: 100%;">
                    <table class="datatable">
                        <thead class="table_heads sticky-headerNew">
                            <tr class="table_heads">
                                <th>{{ __('Sr No.') }}</th>
                                <th>{{ __('Reg. #') }}</th>
                                <th>{{ __('Roll No') }}</th>
                                <th>{{ __('Reg. Date') }}</th>
                                <th>{{ __('Student Name') }}</th>
                                <th>{{ __('Father Name') }}</th>
                                <th>{{ __('Mother Name') }}</th>
                                <th>{{ __('DOB') }}</th>
                                <th>{{ __('Gender') }}</th>
                                <th>{{ __('Religion') }}</th>
                                <th>{{ __('Nationality') }}</th>
                                <th>{{ __('Father CNIC') }}</th>
                                <th>{{ __('Father Phone') }}</th>
                                <th>{{ __('Father Cell') }}</th>
                                <th>{{ __('Mother CNIC') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('City') }}</th>
                                <th>{{ __('District') }}</th>
                                <th>{{ __('Address') }}</th>
                                <th>{{ __('Prev School') }}</th>
                                <th>{{ __('Prev Class') }}</th>
                                <th>{{ __('Session') }}</th>
                                <th>{{ __('Class') }}</th>
                                <th>{{ __('Branch') }}</th>
                                <th>{{ __('Reg. Option') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Reg. Fee') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $index => $student)
                                <tr class="trNew">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $student->reg_no }}</td>
                                    <td>{{ $student->roll_no }}</td>
                                    <td>{{ date('d M Y', strtotime($student->regdate)) }}</td>
                                    <td>{{ @$student->stdname }}</td>
                                    <td>{{ @$student->fathername }}</td>
                                    <td>{{ @$student->mothername }}</td>
                                    <td>{{ @$student->dob }}</td>
                                    <td>{{ strtoupper(@$student->gender) }}</td>
                                    <td>{{ @$student->religion }}</td>
                                    <td>{{ @$student->nationality }}</td>
                                    <td>{{ @$student->fathercnic }}</td>
                                    <td>{{ @$student->fatherphone }}</td>
                                    <td>{{ @$student->fathercell }}</td>
                                    <td>{{ @$student->mothercnic }}</td>
                                    <td>{{ @$student->email }}</td>
                                    <td>{{ @$student->city }}</td>
                                    <td>{{ @$student->district }}</td>
                                    <td>{{ @$student->address }}</td>
                                    <td>{{ @$student->prevschool }}</td>
                                    <td>{{ @$student->prevclass }}</td>
                                    <td>{{ @$student->session->year ?? '-' }}</td>
                                    <td>{{ @$student->class->name ?? '-' }}</td>
                                    <td>{{ @$student->branches->name ?? '-' }}</td>
                                    <td>{{ @$student->registeroption->name }}</td>
                                    <td>{{ $student->roll_no ? 'Enrolled' : 'Not Enrolled' }}</td>
                                    <td>{{ @$student->registrationfee }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="27" class="text-center">{{ __('No students found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
