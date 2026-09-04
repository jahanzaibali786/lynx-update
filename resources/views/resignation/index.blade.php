@extends('layouts.admin')

@section('page-title')
    {{ __('Manage Resignation') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Resignation') }}</li>
@endsection
@push('script-page')
    <script>
        function branchemployees(id) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{ route('branch.employees') }}",
                type: "POST",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function(result) {
                    console.log(result);
                    if (result.status == 'success') {
                        $('#employee_id').empty();
                        $('#employee_id').append($('<option>', {
                            value: '',
                            text: 'Select Employee'
                        }));

                        for (var j = 0; j < result.employee.length; j++) {
                            var cls = result.employee[j];
                            $('#employee_id').append($('<option>', {
                                value: cls.id,
                                text: cls.name
                            }));
                        }
                    }
                    if (result.status == 'error') {}
                }
            });
        }

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
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        alert(response.error);
                    } else {
                        alert('An error occurred while generating the PDF.');
                    }
                }
            });
        }
    </script>
    <script>
        function submitWithPrintFlag(type = "pdf") {
            const form = document.getElementById('resignation_submit');
            let input = document.getElementById('is_print');
            if(type === "excel") input = document.getElementById('is_excel')
            input.value = 1;

            if(type !== "excel") form.target = '_blank';
            form.submit();
            form.target = '';
        }
    </script>
@endpush
@section('action-btn')
    <div class="float-end">
        @can('create resignation')
            <a href="#" data-size="lg" data-url="{{ route('resignation.create') }}" data-ajax-popup="true"
                data-bs-title="{{ __('Create') }}" data-bs-toggle="{{ __('Create New Resignation') }}"
                class="btn btn-sm btn-outline-primary">
                <span class="btn-inner--icon"> Create</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    {{-- @if (\Auth::user()->type == 'company') --}}
    <div class="row w-100">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body filter_change">
                        {{ Form::open(['route' => ['resignation.index'], 'method' => 'GET', 'id' => 'resignation_submit']) }}
                        <div class="row d-flex justify-content-end ">

                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'onchange' => 'branchtype(this.value)']) }}
                                </div>
                            </div>
                            <input type="hidden" name="is_excel" id="is_excel" value="0">
                            <input type="hidden" name="is_print" id="is_print" value="0">
                            <div class="col-auto float-end ms-2 mt-4">
                                {{-- //print  button --}}
                                {{-- //search  --}}
                                <a href="#" class="btn btn-sm btn-outline-primary"
                                    onclick="document.getElementById('resignation_submit').submit(); return false;"
                                    data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('resignation.index') }}" class="btn btn-sm btn-outline-danger"
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                {{-- <a href="#" class="btn btn-sm btn-outline-primary"
                                    onclick="submitWithPrintFlag(); return false;" data-bs-title="{{ __('Print') }}">
                                    <span class="btn-inner--icon">Print</span>
                                </a>
                                <a href="#" class="btn btn-sm btn-outline-primary"
                                    onclick="submitWithPrintFlag('excel'); return false;"
                                    data-bs-title="{{ __('ExportExcel') }}">
                                    <span class="btn-inner--icon">Export</span>
                                </a> --}}
                                <!-- Actions Dropdown -->
                                <div class="dropdown d-inline-block mx-1">
                                    <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button"
                                        id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        Export
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="actionDropdown">
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
            </div>
        </div>
    </div>
    {{-- @endif --}}
    <table class="datatable">
        <thead>
            <tr class="table_heads">
                <th>#</th>
                <th>{{ __('Emp No.') }}</th>
                <th>{{ __('Employee Name') }}</th>
                <th>{{ __('Branch') }}</th>
                <th>{{ __('Resignation Date') }}</th>
                <th>{{ __('Last Working Date') }}</th>
                <th>{{ __('Reason') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Description') }}</th> {{-- Always include; hide via empty cell in loop if needed --}}
                @if (Gate::check('edit resignation') || Gate::check('delete resignation'))
                    <th width="200px">{{ __('Action') }}</th>
                @endif
            </tr>
        </thead>
        <tbody class="font-style">
            @foreach ($resignations as $resignation)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $resignation->employee->employee_id ?? '' }}</td>
                    <td>{{ $resignation->employee->name ?? '' }}</td>
                    <td>{{ \Auth::user()->getBranch(@$resignation->employee->owned_by)->name ?? '' }}</td>
                    <td>{{ \Auth::user()->dateFormat($resignation->notice_date) }}</td>
                    <td>{{ \Auth::user()->dateFormat($resignation->resignation_date) }}</td>
                    <td>{{ $resignation->description }}</td>
                    <td>
                        @if ($resignation->status == 0)
                            <span class="badge bg-warning p-2 px-3 rounded">{{ __('Pending') }}</span>
                        @elseif($resignation->status == 1)
                            <span class="badge bg-success p-2 px-3 rounded">{{ __('Approved') }}</span>
                        @else
                            <span class="badge bg-danger p-2 px-3 rounded">{{ __('Rejected') }}</span>
                        @endif
                    </td>
                    <td>
                        @if ($resignation->status == 1 || $resignation->status == 2)
                            {{ $resignation->appr_rej_desc }}
                        @else
                            {{-- Leave blank if not approved/rejected --}}
                        @endif
                    </td>
                    @if (Gate::check('edit resignation') || Gate::check('delete resignation'))
                        <td style="width: 25% !important;">
                            <div class="action-btn ms-2" style=" width: auto;">



                                {{-- @role('company')
                                    @if ($resignation->status == 0 || $resignation->status == 2)
                                        <a href="#" data-size="md" data-ajax-popup="true"
                                            data-url="{{ route('resignation.show', $resignation->id) }}"
                                            class="mx-1 btn btn-sm btn-outline-primary" data-bs-title="{{ __('Approval') }}">
                                            <span class="btn-inner--icon"><i class="ti ti-check"></i></span>
                                        </a>
                                    @endif
                                @endrole --}}

                                @if ($resignation->status !== 1)
                                    @php
                                        $settlement = App\Models\EmployeeFinalSettlement::where(
                                            'emp_id',
                                            $resignation->employee_id,
                                        )->first();
                                    @endphp
                                    @if (!$settlement)
                                        <a href="{{ route('EmployeeSettlement.create', $resignation->employee_id) }}"
                                            data-bs-title="{{ __('Create') }}"
                                            class="apply-btn btn btn-sm btn-outline-primary"><span
                                                class="btn-inner--icon"><i class="ti ti-plus"></i></span></a>
                                        @can('edit resignation')
                                            <a href="#" class="mx-1 btn btn-sm btn-outline-primary" data-size="lg"
                                                data-url="{{ URL::to('resignation/' . $resignation->id . '/edit') }}"
                                                data-ajax-popup="true" data-bs-title="{{ __('Edit Resignation') }}">
                                                <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span>
                                            </a>
                                        @endcan
                                        @can('delete resignation')
                                            {!! Form::open([
                                                'method' => 'DELETE',
                                                'route' => ['resignation.destroy', $resignation->id],
                                                'id' => 'delete-form-' . $resignation->id,
                                            ]) !!}
                                            <a href="#" class="mx-1 btn btn-sm btn-outline-danger bs-pass-para"
                                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone.') }}"
                                                data-confirm-yes="document.getElementById('delete-form-{{ $resignation->id }}').submit();"
                                                title="{{ __('Delete') }}">
                                                <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                            </a>
                                            {!! Form::close() !!}
                                        @endcan
                                    @else
                                        <a class="mx-1 btn btn-sm btn-outline-success"
                                            onclick="finalsetttlement('{{ $resignation->employee_id }}')"
                                            title="{{ __('Print') }}">
                                            <span class="btn-inner--icon"><i class="fas fa-print"></i></span>
                                        </a>
                                    @endif
                                @else
                                    <a class="mx-1 btn btn-sm btn-outline-success"
                                        onclick="finalsetttlement('{{ $resignation->employee_id }}')"
                                        title="{{ __('Print') }}">
                                        <span class="btn-inner--icon"><i class="fas fa-print"></i></span>
                                    </a>
                                @endif
                            </div>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
    {{-- @if ($resignations->hasPages())
        <div class="pagination">
            <ul>
                @if ($resignations->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $resignations->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($resignations->currentPage() > 1)
                    <li><a href="{{ $resignations->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $resignations->currentPage();
                    $lastPage = $resignations->lastPage();
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
                    <li class="{{ $page == $resignations->currentPage() ? 'active' : '' }}">
                        <a href="{{ $resignations->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($resignations->hasMorePages())
                    <li><a href="{{ $resignations->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($resignations->currentPage() < $resignations->lastPage())
                    <li><a
                            href="{{ $resignations->appends(request()->query())->url($resignations->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif --}}
@endsection
