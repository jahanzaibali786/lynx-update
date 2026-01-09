@extends('layouts.admin')
@section('page-title')
    {{ __('Student StudyPack Report') }}
@endsection
@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.searchbox').searchbox();
        });
    </script>
@endpush
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['studypackStudent'], 'method' => 'GET', 'id' => 'studypackStudent']) }}
                        <div class="row align-items-center justify-content-end ">
                            <div class="col-xl-10 col-lg-10 col-md-10 col-10 ">
                                <div class="row d-flex justify-content-end ">
                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                            {{ Form::select('branches', $branches, request()->get('branches'), ['class' => 'form-control select custom-select', 'onchange' => 'branchcustomer(this.value)']) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn btn-sm btn-primary"
                                    onclick="document.getElementById('studypackStudent').submit(); return false;"
                                    data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('studypackStudent') }}" class="btn btn-sm btn-danger"
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                {{ Form::close() }}

                                

                                                                    <!-- Actions Dropdown -->
                                    <div class="dropdown d-inline-block mx-1">
                                        <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                            id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            Export
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="actionDropdown">
                                            <li>
                                        <button class="dropdown-item" type="submit" name="export"
                                    value="exportallreportexcel" data-bs-title="exportallreportexcel"><span
                                        class="btn-inner--icon"><i class="ti ti-file me-2"></i>Excel</span></button>
                                            </li>
                                        </ul>
                                    </div>



                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="text-center">
                        <p style="font-family: Edwardian Script ITC; font-size: 3.5rem; text-align: center;"><b>The Lynx
                                School</b></p>
                        <h2 class="font-bolder">Student StudyPack Report</h2>
                    </div>
                    <div class="table-responsive maximumHeightNew">
                        <table class="table datatable">
                            <thead class="table_heads sticky-headerNew">
                                <tr>
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
                                            $studentData = App\Models\StudentRegistration::with(
                                                'enrollment.class',
                                                'enrollment.section',
                                            )
                                                ->where('id', $st_id)
                                                ->first();
                                        @endphp
                                        <td>{{ $studentData->stdname }}</td>
                                        <td>{{ \Carbon\Carbon::parse($challan->challan_date)->format('F,Y') }}</td>
                                        @php
                                            $challan_heads = \App\Models\StudyPackChallanItems::where(
                                                'challan_id',
                                                $challan->id,
                                            )->get();
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
                                                {{-- //export submit form --}}
                                                <a href="#" class="btn btn-sm btn-success"
                                                    onclick="document.getElementById('exportInput').value='excel';  document.querySelector('.challanno').value = ''; document.querySelector('.challanno').value = {{$challan->id}}; document.getElementById('studypackStudent').submit(); return false;"
                                                    data-bs-title="{{ __('Export') }}">
                                                    <span class="btn-inner--icon">Export</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
