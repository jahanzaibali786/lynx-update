@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Department') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Department') }}</li>
@endsection
@push('script-page')
    <script>
        $(document).on("click", ".new_data", function() {
            let id = $(this).data('confirm-id');
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            })
            swalWithBootstrapButtons.fire({
                title: 'Are you sure?',
                text: "This action can change status.!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Change it!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('update-status-form-' + id).submit();
                    swalWithBootstrapButtons.fire(
                        'Updated!',
                        'Your Department status has been changed.',
                        'success'
                    )
                } else if (
                    result.dismiss === Swal.DismissReason.cancel
                ) {
                    swalWithBootstrapButtons.fire(
                        'Cancelled',
                        'Your Department status is safe :)',
                        'error'
                    )
                }
            })
        });
    </script>
@endpush


@section('action-btn')
    <div class="float-end">
        @can('create department')
            <a href="#" data-url="{{ route('department.create') }}" data-ajax-popup="true"
                 data-bs-title="{{ __('Create') }}"
                class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon"> Create</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-3">
            @include('layouts.hrm_setup')
        </div>
        <div class="col-9">
            {{-- @if (\Auth::user()->type == 'company')
            <div class="row">
                <div class="col-sm-12">
                    <div class="mt-2 " id="multiCollapseExample1">
                        <div class="card">
                            <div class="card-body">
                                {{ Form::open(['route' => ['department.index'], 'method' => 'GET', 'id' => 'department_submit']) }}
                                <div class="row d-flex justify-content-end ">

                                    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                        <div class="btn-box">
                                            {{ Form::label('branches', __('Branches'),['class'=>'form-label'])}}
                                            {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select' , 'onchange' => 'branchtype(this.value)']) }}
                                        </div>
                                    </div>

                                    <div class="col-auto float-end ms-2 mt-4">
                                        <a href="#" class="btn btn-sm btn-primary"
                                            onclick="document.getElementById('department_submit').submit(); return false;"
                                             data-bs-title="{{ __('apply') }}">
                                            <span class="btn-inner--icon">Search</span>
                                        </a>
                                        <a href="{{ route('department.index') }}" class="btn btn-sm btn-danger" 
                                            data-bs-title="{{ __('Reset') }}">
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
            @endif --}}

            <table class="">
                <thead>
                    <tr class="table_heads">
                        {{-- <th>{{__('Branch')}}</th> --}}
                        <th>#</th>
                        <th>{{ __('Department') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th width="200px">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="font-style">
                    @foreach ($departments as $department)
                        <tr>
                            <td>{{ ($departments->currentPage() - 1) * $departments->perPage() + $loop->iteration }}</td>
                            {{-- <td>{{ !empty($department->branch)?$department->branch->name:'' }}</td> --}}
                            <td>{{ $department->name }}</td>
                            <td>
                                {!! Form::open([
                                    'method' => 'POST',
                                    'route' => ['update_department_status', $department->id],
                                    'id' => 'update-status-form-' . $department->id,
                                ]) !!} <input type="hidden" name="active_status"
                                    value="{{ $department->status ? 0 : 1 }}">
                                <a href="#"
                                    class="mx-1 btn btn-sm {{ $department->status ? 'btn-danger' : 'btn-success' }} new_data"
                                     data-bs-title="{{ __('Update Status') }}"
                                    data-confirm="{{ __('Are You Sure?') . '|' . __('This action can change other department inactive and this department active. Do you want to continue?') }}"
                                    data-confirm-yes="document.getElementById('update-status-form-{{ $department->id }}').submit();"
                                    data-confirm-id="{{ $department->id }}"> <span
                                        class="btn-inner--icon">{{ $department->status ? 'Inactive' : 'Activate' }}</span></a>
                                {!! Form::close() !!}
                            </td>
                            <td class="Action">
                                {{-- <span> --}}
                                <div class="action-btn ms-2">
                                    @can('edit department')
                                        <a href="#" data-url="{{ URL::to('department/' . $department->id . '/edit') }}"
                                            data-ajax-popup="true"
                                            class="mx-1 btn mx-1 btn-sm btn-outline-primary" 
                                            data-bs-title="{{ __('Edit') }}">
                                            <span class="btn-inner--icon"> <i class="ti ti-pencil"></i></span></a>
                                    @endcan
                                    @can('delete department')
                                        {!! Form::open([
                                            'method' => 'DELETE',
                                            'route' => ['department.destroy', $department->id],
                                            'id' => 'delete-form-' . $department->id,
                                        ]) !!}
                                        <a href="#" class="mx-1 btn btn-sm bs-pass-para  btn-outline-danger"
                                            
                                            data-bs-title="{{ __('Delete') }}"
                                            data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                            data-confirm-yes="document.getElementById('delete-form-{{ $department->id }}').submit();"><span
                                                class="btn-inner--icon"><i class="ti ti-trash"></i></span></a>
                                        {!! Form::close() !!}
                                    @endcan
                                    {{-- </span> --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @if ($departments->hasPages())
        <div class="pagination">
            <ul>
                @if ($departments->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $departments->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($departments->currentPage() > 1)
                    <li><a href="{{ $departments->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $departments->currentPage();
                    $lastPage = $departments->lastPage();
                    $startPage = max(1, $currentPage - 4);
                    $endPage = min($lastPage, $currentPage + 5);
                    if ($endPage - $startPage < 9) {
                        if ($currentPage < $lastPage - 9) {
                            $endPage = $startPage + 9;
                        } else {
                            $startPage = max(1, $lastPage - 9);
                        }
                    }
                @endphp
                @for ($page = $startPage; $page <= $endPage; $page++)
                    <li class="{{ $page == $departments->currentPage() ? 'active' : '' }}">
                        <a href="{{ $departments->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($departments->hasMorePages())
                    <li><a href="{{ $departments->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($departments->currentPage() < $departments->lastPage())
                    <li><a
                            href="{{ $departments->appends(request()->query())->url($departments->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif
@endsection
