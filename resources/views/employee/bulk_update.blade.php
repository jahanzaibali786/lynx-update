@extends('layouts.admin')
@section('page-title')
    {{__('Employee Scale Bulk Update')}}
@endsection
@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">{{__('Bulk Update Employee Scales')}}</h5>
                <a href="{{ route('employee.bulk.sample') }}" class="btn btn-sm btn-outline-primary">
                    <span class="btn-inner--icon"><i class="ti ti-download"></i> {{__('Download Sample / Export Current Data')}}</span>
                </a>
            </div>

            @if(Session::has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ Session::get('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(Session::has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ Session::get('error') }}
                    @if(Session::has('error_file'))
                        <br>
                        <a href="{{ Session::get('error_file') }}" class="btn btn-sm btn-light mt-2" target="_blank">{{__('Download Error Log')}}</a>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('employee.bulk.store') }}" method="POST" enctype="multipart/form-data" class="mt-3">
                @csrf
                <div class="form-group mb-4">
                    <label for="excel_file" class="form-label font-bold">{{__('Excel File (CSV)')}} <span class="text-danger">*</span></label>
                    <input style="width:100%;" type="file" id="excel_file" name="excel_file" accept=".csv" required class="form-control">
                    <small class="text-muted">{{__('Accepted format: CSV (UTF-8 encoded). Columns: Employee name - emp id, Scale, Gross, Net, Effect From')}}</small>
                </div>

                <div class="text-end">
                    <a href="{{ route('employee.index') }}" class="btn btn-light me-2">{{__('Cancel')}}</a>
                    <button style="background-color: var(--primary) !important; color: #fff;" type="submit" class="btn">
                        {{__('Upload & Update Scales')}}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
