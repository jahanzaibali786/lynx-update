@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Appraisal') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Appraisal') }}</li>
@endsection
@push('css-page')
    <style>
        @import url({{ asset('css/font-awesome.css') }});
    </style>
@endpush
@push('script-page')
    <script src="{{ asset('js/bootstrap-toggle.js') }}"></script>
    <script>
        $('document').ready(function() {
            $('.toggleswitch').bootstrapToggle();
            $("fieldset[id^='demo'] .stars").click(function() {
                alert($(this).val());
                $(this).attr("checked");
            });
        });

        $(document).ready(function() {
            var employee = $('#employee').val();
            getEmployee(employee);
        });

        $(document).on('change', 'select[name=branch]', function() {
            var branch = $(this).val();
            getEmployee(branch);
        });

        function getEmployee(did) {
            $.ajax({
                url: '{{ route('branch.employee.json') }}',
                type: 'POST',
                data: {
                    "branch": did,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#employee').empty();
                    $('#employee').append('<option value="">{{ __('Select Employee') }}</option>');
                    $.each(data, function(key, value) {
                        $('#employee').append('<option value="' + key + '">' + value + '</option>');
                    });
                }
            });
        }
    </script>
@endpush

@section('action-btn')
    <div class="float-end">
        @can('create appraisal')
            <a href="#" data-size="lg" data-url="{{ route('appraisal.create') }}" data-ajax-popup="true"
                 data-bs-title="{{ __('Create New Appraisal') }}"
                class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        @endcan
    </div>
@endsection

@section('content')
    {{-- @if (\Auth::user()->type == 'company') --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="mt-2 " id="multiCollapseExample1">
                    <div class="card">
                        <div class="card-body">
                            {{ Form::open(['route' => ['appraisal.index'], 'method' => 'GET', 'id' => 'appraisal_submit']) }}
                            <div class="row d-flex justify-content-end ">

                                <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('branches', __('Branches'), ['class' => 'form-label']) }}
                                        {{ Form::select('branches', $branches, isset($_GET['branches']) ? $_GET['branches'] : '', ['class' => 'form-control select', 'onchange' => 'branchtype(this.value)']) }}
                                    </div>
                                </div>

                                <div class="col-auto float-end ms-2 mt-4">
                                    <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                        onclick="document.getElementById('appraisal_submit').submit(); return false;"
                                         data-bs-title="{{ __('apply') }}">
                                        <span class="btn-inner--icon">Search</span>
                                    </a>
                                    <a href="{{ route('appraisal.index') }}" class="btn mx-1 btn-sm btn-outline-danger"
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
    {{-- @endif --}}

    <table class="datatable">
        <thead>
            <tr class="table_heads">
                <th>{{ __('#') }}</th>
                <th>{{ __('Branch') }}</th>
                <th>{{ __('Department') }}</th>
                <th>{{ __('Designation') }}</th>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Target Rating') }}</th>
                <th>{{ __('Overall Rating') }}</th>
                <th>{{ __('Appraisal Date') }}</th>
                @if (Gate::check('edit appraisal') || Gate::check('delete appraisal') || Gate::check('show appraisal'))
                    <th width="200px">{{ __('Action') }}</th>
                @endif
            </tr>
        </thead>
        <tbody class="font-style">
            @foreach ($appraisals as $appraisal)
                @php
                    $designation = !empty($appraisal->employees) ? $appraisal->employees->designation->id : 0;
                    $targetRating = Utility::getTargetrating($designation, $competencyCount);
                    if (!empty($appraisal->rating) && $competencyCount != 0) {
                        $rating = json_decode($appraisal->rating, true);
                        $starsum = !empty($rating) ? array_sum($rating) : 0;
                        $overallrating = $starsum != 0 ? $starsum / $competencyCount : 0;
                    } else {
                        $overallrating = 0;
                    }
                @endphp

                @php
                    if (!empty($appraisal->rating)) {
                        $rating = json_decode($appraisal->rating, true);
                        $starsum = !empty($rating) ? array_sum($rating) : 0;
                        $overallrating = $starsum != 0 ? $starsum / count($rating) : 0;
                    } else {
                        $overallrating = 0;
                    }
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ !empty($appraisal->branchuser) ? $appraisal->branchuser->name : '' }}</td>
                    <td>{{ !empty($appraisal->employees) ? (!empty($appraisal->employees->department) ? $appraisal->employees->department->name : '') : '' }}
                    </td>
                    <td>{{ !empty($appraisal->employees) ? (!empty($appraisal->employees->designation) ? $appraisal->employees->designation->name : '') : '' }}
                    </td>
                    <td>{{ !empty($appraisal->employees) ? $appraisal->employees->name : '' }}</td>

                    <td>
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($targetRating < $i)
                                @if (is_float($targetRating) && round($targetRating) == $i)
                                    <i class="text-warning fas fa-star-half-alt"></i>
                                @else
                                    <i class="fas fa-star"></i>
                                @endif
                            @else
                                <i class="text-warning fas fa-star"></i>
                            @endif
                        @endfor
                        <span class="theme-text-color">({{ number_format($targetRating, 1) }})</span>
                    </td>


                    <td>

                        @for ($i = 1; $i <= 5; $i++)
                            @if ($overallrating < $i)
                                @if (is_float($overallrating) && round($overallrating) == $i)
                                    <i class="text-warning fas fa-star-half-alt"></i>
                                @else
                                    <i class="fas fa-star"></i>
                                @endif
                            @else
                                <i class="text-warning fas fa-star"></i>
                            @endif
                        @endfor
                        <span class="theme-text-color">({{ number_format($overallrating, 1) }})</span>
                    </td>
                    <td>{{ $appraisal->appraisal_date }}</td>
                    @if (Gate::check('edit appraisal') || Gate::check('delete appraisal') || Gate::check('show appraisal'))
                        <td>
                            @can('show appraisal')
                                <div class="action-btn ms-2">
                                    <a href="#" data-url="{{ route('appraisal.show', $appraisal->id) }}" data-size="lg"
                                        data-ajax-popup="true" data-bs-toggle="{{ __('Appraisal Detail') }}"
                                         data-bs-title="{{ __('View') }}"
                                        data-bs-title="{{ __('View Detail') }}"
                                        class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center">
                                        <span class="btn-inner--icon"> <i class="ti ti-eye"></i>
                                        </span>
                                    </a>
                                @endcan
                                @can('edit appraisal')
                                    <a href="#" data-url="{{ route('appraisal.edit', $appraisal->id) }}" data-size="lg"
                                        data-ajax-popup="true" data-bs-toggle="{{ __('Edit Appraisal') }}" 
                                        data-bs-title="{{ __('Edit') }}" data-bs-title="{{ __('Edit') }}"
                                        class="mx-1 btn mx-1 btn-sm btn-outline-warning align-items-center">
                                        <span class="btn-inner--icon">
                                            <i class="ti ti-pencil "></i>
                                        </span>
                                    </a>
                                @endcan
                                @can('delete appraisal')
                                    {!! Form::open([
                                        'method' => 'DELETE',
                                        'route' => ['appraisal.destroy', $appraisal->id],
                                        'id' => 'delete-form-' . $appraisal->id,
                                    ]) !!}
                                    <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"
                                        data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                         data-bs-title="{{ __('Delete') }}"
                                        data-bs-title="{{ __('Delete') }}"
                                        data-confirm-yes="document.getElementById('delete-form-{{ $appraisal->id }}').submit();">
                                        <span class="btn-inner--icon"><i class="ti ti-trash"></i></span>
                                        {{-- <i class="ti ti-trash text-white"></i> --}}
                                    </a>
                                    {!! Form::close() !!}
                                </div>
                            @endcan
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
          {{-- @if ($appraisals->hasPages())
    <div class="pagination">
        <ul>
            @if ($appraisals->onFirstPage())
                <li class="disabled">&laquo; Previous</li>
            @else
                <li><a href="{{ $appraisals->appends(request()->query())->previousPageUrl() }}"
                        rel="prev">&laquo; Previous</a></li>
            @endif
            @if ($appraisals->currentPage() > 1)
                <li><a href="{{ $appraisals->appends(request()->query())->url(1) }}">First</a></li>
            @endif
            @php
                $currentPage = $appraisals->currentPage();
                $lastPage = $appraisals->lastPage();
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
                <li class="{{ $page == $appraisals->currentPage() ? 'active' : '' }}">
                    <a href="{{ $appraisals->appends(request()->query())->url($page) }}">{{ $page }}</a>
                </li>
            @endfor
            @if ($appraisals->hasMorePages())
                <li><a href="{{ $appraisals->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                        &raquo;</a></li>
            @else
                <li class="disabled">Next &raquo;</li>
            @endif
            @if ($appraisals->currentPage() < $appraisals->lastPage())
                <li><a
                        href="{{ $appraisals->appends(request()->query())->url($appraisals->lastPage()) }}">Last</a>
                </li>
            @endif
        </ul>
    </div>
@endif --}}
@endsection
