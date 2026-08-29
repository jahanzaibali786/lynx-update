@extends('layouts.admin')

@section('page-title')
    {{ __('Average Monthly Fee & Discount Percentage Report') }}
@endsection

@push('script-page')

    <script src="{{ asset('js/jquery.min.js') }}"></script>

    <script>

        function exportToExcel() {

            var form =
                document.getElementById(
                    'averageMonthlyReportForm'
                );

            var formData =
                new FormData(form);

            formData.append(
                'export',
                'excel'
            );

            var queryString =
                new URLSearchParams(
                    formData
                ).toString();

            window.location.href =
                "{{ route('average_monthly_report') }}"
                + "?"
                + queryString;
        }

        function exportToPdf() {

            var form =
                document.getElementById(
                    'averageMonthlyReportForm'
                );

            var formData =
                new FormData(form);

            formData.append(
                'print',
                'pdf'
            );

            var queryString =
                new URLSearchParams(
                    formData
                ).toString();

            window.location.href =
                "{{ route('average_monthly_report') }}"
                + "?"
                + queryString;
        }

    </script>

@endpush

@section('breadcrumb')

    <li class="breadcrumb-item">

        <a href="{{ route('dashboard') }}">
            {{ __('Dashboard') }}
        </a>

    </li>

    <li class="breadcrumb-item">
        {{ __('Average Monthly Fee & Discount Percentage Report') }}
    </li>

@endsection

@section('action-btn')

    <div class="float-end"></div>

@endsection

@section('content')

    <div class="row">

        <div class="col-sm-12">

            <div class="mt-2">

                <div class="card">

                    <div
                        class="card-body"
                        style="padding: 12px;"
                    >

                        {{ Form::open([
                            'route' => [
                                'average_monthly_report'
                            ],
                            'method' => 'GET',
                            'id' => 'averageMonthlyReportForm'
                        ]) }}

                        <div
                            class="
                                row
                                d-flex
                                justify-content-end
                            "
                        >

                            {{-- BRANCH --}}
                            <div
                                class="
                                    col-xl-3
                                    col-lg-3
                                    col-md-6
                                    col-sm-12
                                    col-12
                                    mr-2
                                "
                            >

                                <div class="btn-box">

                                    {{
                                        Form::label(
                                            'branches',
                                            __('Branches'),
                                            [
                                                'class' =>
                                                    'form-label'
                                            ]
                                        )
                                    }}

                                    @if (
                                        auth()->user()->type
                                        === 'branch'
                                    )

                                        {{
                                            Form::select(
                                                'branches',
                                                $branches,
                                                $selectedBranch,
                                                [
                                                    'class' =>
                                                        'form-control select custom-select',

                                                    'disabled' =>
                                                        'disabled'
                                                ]
                                            )
                                        }}

                                        <input
                                            type="hidden"
                                            name="branches"
                                            value="{{ $selectedBranch }}"
                                        >

                                    @else

                                        {{
                                            Form::select(
                                                'branches',
                                                $branches,
                                                $selectedBranch,
                                                [
                                                    'class' =>
                                                        'form-control select custom-select'
                                                ]
                                            )
                                        }}

                                    @endif

                                </div>

                            </div>

                            {{-- FROM --}}
                            <div
                                class="
                                    col-xl-3
                                    col-lg-3
                                    col-md-6
                                    col-sm-12
                                    col-12
                                    mr-2
                                "
                            >

                                <div class="btn-box">

                                    {{
                                        Form::label(
                                            'month_from',
                                            __('From'),
                                            [
                                                'class' =>
                                                    'form-label'
                                            ]
                                        )
                                    }}

                                    {{
                                        Form::month(
                                            'month_from',
                                            request()->get(
                                                'month_from'
                                            )
                                            ?? now()
                                                ->startOfYear()
                                                ->format('Y-m'),
                                            [
                                                'class' =>
                                                    'form-control select'
                                            ]
                                        )
                                    }}

                                </div>

                            </div>

                            {{-- TO --}}
                            <div
                                class="
                                    col-xl-3
                                    col-lg-3
                                    col-md-6
                                    col-sm-12
                                    col-12
                                    mr-2
                                "
                            >

                                <div class="btn-box">

                                    {{
                                        Form::label(
                                            'month_to',
                                            __('To'),
                                            [
                                                'class' =>
                                                    'form-label'
                                            ]
                                        )
                                    }}

                                    {{
                                        Form::month(
                                            'month_to',
                                            request()->get(
                                                'month_to'
                                            )
                                            ?? now()
                                                ->endOfYear()
                                                ->format('Y-m'),
                                            [
                                                'class' =>
                                                    'form-control select'
                                            ]
                                        )
                                    }}

                                </div>

                            </div>

                            {{-- ACTIONS --}}
                            <div
                                class="
                                    col-xl-2
                                    col-lg-2
                                    col-md-6
                                    col-sm-12
                                    col-12
                                    mr-2
                                    mt-4
                                    d-flex
                                    align-items-center
                                    gap-2
                                "
                            >

                                <a
                                    href="#"
                                    class="
                                        btn
                                        btn-sm
                                        btn-primary
                                    "
                                    onclick="
                                        document
                                            .getElementById(
                                                'averageMonthlyReportForm'
                                            )
                                            .submit();

                                        return false;
                                    "
                                    data-bs-title="Search"
                                >

                                    <span
                                        class="btn-inner--icon"
                                    >
                                        Search
                                    </span>

                                </a>

                                <div class="dropdown">

                                    <button
                                        class="
                                            btn
                                            btn-sm
                                            btn-outline-success
                                            dropdown-toggle
                                        "
                                        type="button"
                                        id="averageMonthlyActionDropdown"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                    >
                                        Export
                                    </button>

                                    <ul
                                        class="dropdown-menu"
                                        aria-labelledby="
                                            averageMonthlyActionDropdown
                                        "
                                    >

                                        <li>

                                            <button
                                                class="dropdown-item"
                                                type="submit"
                                                onclick="
                                                    exportToExcel();
                                                    return false;
                                                "
                                            >

                                                <i
                                                    class="
                                                        ti
                                                        ti-file
                                                        me-2
                                                    "
                                                ></i>

                                                Excel

                                            </button>

                                        </li>

                                        <li>

                                            <button
                                                class="dropdown-item"
                                                type="submit"
                                                onclick="
                                                    exportToPdf();
                                                    return false;
                                                "
                                            >

                                                <i
                                                    class="
                                                        ti
                                                        ti-download
                                                        me-2
                                                    "
                                                ></i>

                                                Pdf

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

    @include(
        'studentReports.partials.average_monthly_report_content'
    )

@endsection