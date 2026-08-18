@extends('layouts.admin')
@section('page-title')
    {{ __('Search Student Receipts by Reference') }}
@endsection
@push('script-page')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('student_receipt.index') }}">{{ __('Student Receipts') }}</a></li>
    <li class="breadcrumb-item">{{ __('Search by Reference') }}</li>
@endsection
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2">
                <div class="card">
                    <div class="card-body" style="padding:12px;">
                        {{ Form::open(['route' => ['student_receipt.search_by_reference'], 'method' => 'GET', 'id' => 'reference_search_form']) }}
                        <div class="row d-flex justify-content-end">
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12 mr-2">
                                {{ Form::label('reference', __('Reference'), ['class' => 'form-label']) }}
                                {{ Form::text('reference', old('reference', $reference ?? ''), ['class' => 'form-control', 'placeholder' => 'Enter reference number', 'required' => 'required']) }}
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <button type="submit" class="btn mx-1 btn-sm btn-outline-primary">
                                    <span class="btn-inner--icon">Search</span>
                                </button>
                                <a href="{{ route('student_receipt.search_by_reference') }}" class="btn mx-1 btn-sm btn-outline-danger">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($searchResults) && $searchResults !== null)
        <div class="row mt-3">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h5>Search Results</h5>
                        <p><strong>Reference:</strong> {{ $reference }}</p>
                        <!-- <p><strong>Total Students Found:</strong> {{ $searchResults->count() }}</p> -->
                        <hr>
                        @if($searchResults->count() > 0)
                            <table class="datatable">
                                <thead class="table_heads">
                                    <tr>
                                        <th>Branch</th>
                                        <th>Student Name</th>
                                        <th>Roll No</th>
                                        <th>Father Name</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($searchResults as $student)
                                        <tr>
                                            <td>{{ $student['branch'] }}</td>
                                            <td>{{ $student['name'] }}</td>
                                            <td>{{ $student['roll_no'] }}</td>
                                            <td>{{ $student['fathername'] }}</td>
                                            <td>{{ $student['class'] }}</td>
                                            <td>{{ $student['section'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p>No student receipts found for this reference.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
