@extends('layouts.admin')
@section('page-title')
{{__('Manage Employee Final Settlement')}}
@endsection
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{__('Employee Final Settlement')}}</li>
@endsection

{{--@section('action-btn')
<div class="col text-end">
    <a href="{{ route('emp-final-settlement.create') }}" class="apply-btn btn mx-1 btn-sm btn-outline-primary">
        Create
    </a>
</div>
@endsection--}}
@push('script-page')
<script>
    function finalsetttlement(employeeId) {
    $.ajax({
        url: "{{ route('final_settlement', ['id' => '__employeeId__']) }}".replace('__employeeId__',
            employeeId),
        method: 'GET',
        data: {
            title: 'Sample PDF Title',
            content: 'This is a sample content for the PDF.'
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            const base64Pdf = response.base64Pdf;
            const byteCharacters = atob(base64Pdf);
            const byteNumbers = new Array(byteCharacters.length);
            for (let i = 0; i < byteCharacters.length; i++) {
                byteNumbers[i] = byteCharacters.charCodeAt(i);
            }
            const byteArray = new Uint8Array(byteNumbers);
            const blob = new Blob([byteArray], {
                type: 'application/pdf'
            });
            const blobUrl = URL.createObjectURL(blob);
            window.open(blobUrl, '_blank');
        },
        error: function(xhr) {
            console.log(xhr.responseText);
        }
    });
}

</script>
@endpush
@section('content')
<div class="table-responsive">
    <table class="datatable">
        <thead>
            <tr class="table_heads">
                <th>Emp. Id</th>
                <th>Emp. Name</th>
                <th>Tanure</th>
                <th>Basic Sal</th>
                <th>Working Days</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($emplsetlement as $settlment)
                <tr>
                    <td>{{$settlment->emp_id}}</td>
                    <td>{{@$settlment->employee->name}}</td>
                    <td>{{$settlment->tenure}}</td>
                    <td>{{$settlment->basic_sal}}</td>
                    <td>{{$settlment->working_days}}</td>
                    <td>
                        <div class="action-btn ms-2">
                            @if($settlment->status != 1)
                            @can('edit resignation')
                                <a href="{{route('emp-final-settlement.edit', $settlment->id)}}"
                                    class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center"  title="Edit">
                                    <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                </a>
                            @endcan
                            {{-- //finalize  --}}
                            <a href="{{route('emp-final-settlement.finalize', $settlment->id)}}"
                                class="mx-1 btn mx-1 btn-sm btn-outline-success align-items-center"  title="Finalize">
                                <span class="btn-inner--icon"><i class="ti ti-check"></i></span>
                            </a>
                            @else
                            <a class="mx-1 btn mx-1 btn-sm btn-outline-warning"
                                onclick="finalsetttlement('{{$settlment->emp_id}}')"  title="Print"><span class="btn-inner--icon"><i
                                        class="fas fa-print"></i></span></a>
                            @endif
                            @can('delete resignation')
                                {{--{!! Form::open([
                                'method' => 'DELETE',
                                'route' => ['resignation.destroy', $resignation->id],
                                'id' =>
                                'delete-form-' . $resignation->id
                                ]) !!}
                                <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"
                                     data-bs-title="{{__('Delete')}}" data-bs-title="{{__('Delete')}}"
                                    data-confirm="{{__('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?')}}"
                                    data-confirm-yes="document.getElementById('delete-form-{{$resignation->id}}').submit();">
                                    <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                </a>
                                {!! Form::close() !!}--}}
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

      {{-- @if ($emplsetlement->hasPages())
    <div class="pagination">
        <ul>
            @if ($emplsetlement->onFirstPage())
                <li class="disabled">&laquo; Previous</li>
            @else
                <li><a href="{{ $emplsetlement->appends(request()->query())->previousPageUrl() }}"
                        rel="prev">&laquo; Previous</a></li>
            @endif
            @if ($emplsetlement->currentPage() > 1)
                <li><a href="{{ $emplsetlement->appends(request()->query())->url(1) }}">First</a></li>
            @endif
            @php
                $currentPage = $emplsetlement->currentPage();
                $lastPage = $emplsetlement->lastPage();
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
                <li class="{{ $page == $emplsetlement->currentPage() ? 'active' : '' }}">
                    <a href="{{ $emplsetlement->appends(request()->query())->url($page) }}">{{ $page }}</a>
                </li>
            @endfor
            @if ($emplsetlement->hasMorePages())
                <li><a href="{{ $emplsetlement->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                        &raquo;</a></li>
            @else
                <li class="disabled">Next &raquo;</li>
            @endif
            @if ($emplsetlement->currentPage() < $emplsetlement->lastPage())
                <li><a
                        href="{{ $emplsetlement->appends(request()->query())->url($emplsetlement->lastPage()) }}">Last</a>
                </li>
            @endif
        </ul>
    </div>
@endif --}}
</div>

{{-- @if ($emplsetlement->hasPages())
            <div class="pagination">
                <ul>
                    @if ($emplsetlement->onFirstPage())
                        <li class="disabled">&laquo; Previous</li>
                    @else
                        <li><a href="{{ $emplsetlement->appends(request()->query())->previousPageUrl() }}"
                                rel="prev">&laquo; Previous</a></li>
                    @endif
                    @if ($emplsetlement->currentPage() > 1)
                        <li><a href="{{ $emplsetlement->appends(request()->query())->url(1) }}">First</a></li>
                    @endif
                    @php
                        $currentPage = $emplsetlement->currentPage();
                        $lastPage = $emplsetlement->lastPage();
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
                        <li class="{{ $page == $emplsetlement->currentPage() ? 'active' : '' }}">
                            <a href="{{ $emplsetlement->appends(request()->query())->url($page) }}">{{ $page }}</a>
                        </li>
                    @endfor
                    @if ($emplsetlement->hasMorePages())
                        <li><a href="{{ $emplsetlement->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                                &raquo;</a></li>
                    @else
                        <li class="disabled">Next &raquo;</li>
                    @endif
                    @if ($emplsetlement->currentPage() < $emplsetlement->lastPage())
                        <li><a
                                href="{{ $emplsetlement->appends(request()->query())->url($emplsetlement->lastPage()) }}">Last</a>
                        </li>
                    @endif
                </ul>
            </div>
        @endif --}}

@endsection