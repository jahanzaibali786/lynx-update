@extends('layouts.admin')
@section('page-title')
    {{__('Dashboard')}}
@endsection
@push('script-page')
    <script>
        @if(\Auth::user()->can('show account dashboard'))
        (function () {
            var chartBarOptions = {
                series: [
                    {
                        name: "{{__('Income')}}",
                        data:{!! json_encode($incExpLineChartData['income']) !!}
                    },
                    {
                        name: "{{__('Expense')}}",
                        data: {!! json_encode($incExpLineChartData['expense']) !!}
                    }
                ],

                chart: {
                    height: 250,
                    type: 'area',
                    // type: 'line',
                    dropShadow: {
                        enabled: true,
                        color: '#000',
                        top: 18,
                        left: 7,
                        blur: 10,
                        opacity: 0.2
                    },
                    toolbar: {
                        show: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                title: {
                    text: '',
                    align: 'left'
                },
                xaxis: {
                    categories:{!! json_encode($incExpLineChartData['day']) !!},
                    title: {
                        text: '{{ __("Date") }}'
                    }
                },
                colors: ['#6fd944', '#ff3a6e'],


                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                // markers: {
                //     size: 4,
                //     colors: ['#6fd944', '#FF3A6E'],
                //     opacity: 0.9,
                //     strokeWidth: 2,
                //     hover: {
                //         size: 7,
                //     }
                // },
                yaxis: {
                    title: {
                        text: '{{ __("Amount") }}'
                    },

                }

            };
            var arChart = new ApexCharts(document.querySelector("#cash-flow"), chartBarOptions);
            arChart.render();
        })();

        (function () {
            var options = {
                chart: {
                    height: 180,
                    type: 'bar',
                    toolbar: {
                        show: false,
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: 2,
                    curve: 'smooth'
                },
                series: [{
                    name: "{{__('Income')}}",
                    data: {!! json_encode($incExpBarChartData['income']) !!}
                }, {
                    name: "{{__('Expense')}}",
                    data: {!! json_encode($incExpBarChartData['expense']) !!}
                }],
                xaxis: {
                    categories: {!! json_encode($incExpBarChartData['month']) !!},
                },
                colors: ['#100773', '#FF3A6E'],
                fill: {
                    type: 'solid',
                },
                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: true,
                    position: 'top',
                    horizontalAlign: 'right',
                },
                // markers: {
                //     size: 4,
                //     colors:  ['#100773', '#FF3A6E',],
                //     opacity: 0.9,
                //     strokeWidth: 2,
                //     hover: {
                //         size: 7,
                //     }
                // }
            };
            var chart = new ApexCharts(document.querySelector("#incExpBarChart"), options);
            chart.render();
        })();

        (function () {
            var options = {
                chart: {
                    height: 140,
                    type: 'donut',
                },
                dataLabels: {
                    enabled: false,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                },
                series: {!! json_encode($expenseCatAmount) !!},
                colors: {!! json_encode($expenseCategoryColor) !!},
                labels: {!! json_encode($expenseCategory) !!},
                legend: {
                    show: true
                }
            };
            var chart = new ApexCharts(document.querySelector("#expenseByCategory"), options);
            chart.render();
        })();

        (function () {
            var options = {
                chart: {
                    height: 140,
                    type: 'donut',
                },
                dataLabels: {
                    enabled: false,
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                        }
                    }
                },
                series: {!! json_encode($incomeCatAmount) !!},
                colors: {!! json_encode($incomeCategoryColor) !!},
                labels:  {!! json_encode($incomeCategory) !!},
                legend: {
                    show: true
                }
            };
            var chart = new ApexCharts(document.querySelector("#incomeByCategory"), options);
            chart.render();
        })();

        (function () {
            var options = {
                series: [{{ round($storage_limit,2) }}],
                chart: {
                    height: 350,
                    type: 'radialBar',
                    offsetY: -20,
                    sparkline: {
                        enabled: true
                    }
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -90,
                        endAngle: 90,
                        track: {
                            background: "#e7e7e7",
                            strokeWidth: '97%',
                            margin: 5, // margin is in pixels
                        },
                        dataLabels: {
                            name: {
                                show: true
                            },
                            value: {
                                offsetY: -50,
                                fontSize: '20px'
                            }
                        }
                    }
                },
                grid: {
                    padding: {
                        top: -10
                    }
                },
                colors: ["#6FD943"],
                labels: ['Used'],
            };
            var chart = new ApexCharts(document.querySelector("#limit-chart"), options);
            chart.render();
        })();

        @endif
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Account')}}</li>
@endsection
@section('content')



    <div class="row">
        <div class="col-sm-12">
            <div class="row">
                <div class="col-xxl-7">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">

                                <div class="col-lg-3 col-6">
                                    <div class="card mb-5 hover-border-primary">

                                        <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                             <div class="account-dashboard-svg">

                                             <svg height="64px" width="74px" version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-302.08 -302.08 1116.16 1116.16" xml:space="preserve" fill="#100773" stroke="#100773" stroke-width="0.00512"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <style type="text/css"> .st0{fill:#100773;} </style> <g> <path class="st0" d="M464.496,431.904c-13.636-24.598-34.497-41.724-54.571-53.835c-20.086-12.104-39.685-19.308-51.156-23.611 c-8.74-3.246-17.968-7.456-24.307-11.92c-3.174-2.204-5.578-4.463-6.91-6.3c-1.363-1.883-1.635-3.04-1.646-3.958 c0-8.743,0-19.675,0-34.305c1.263-3.193,2.584-5.796,4.076-8.215c2.527-4.15,5.892-8.344,9.268-14.653 c2.802-5.26,5.302-11.882,7.235-20.548c1.7-0.827,3.395-1.784,5.048-2.94c5.068-3.499,9.356-8.598,12.862-15.266 c3.545-6.699,6.604-15.098,9.865-26.474l-0.027,0.076l0.027-0.084c1.677-5.902,2.481-11.086,2.484-15.802 c0.058-7.579-2.312-14.148-6.232-18.582c-1.366-1.569-2.844-2.756-4.329-3.782c0.494-5.834,1.385-13.574,2.231-22.5 c0.98-10.358,1.864-22.202,1.864-34.467c-0.038-19.408-2.105-39.902-10.316-57.587c-4.115-8.82-9.887-16.934-17.769-23.342 c-7.262-5.918-16.33-10.16-26.849-12.395c-7.139-5.474-14.052-10.266-22.669-13.597c-9.302-3.605-20.039-5.274-34.222-5.266 c-4.475,0-9.317,0.16-14.615,0.482C229.032,3.1,216.909,4.9,205.203,4.869c-8.165-0.008-16.223-0.789-25.559-3.446L174.642,0 l-3.985,3.338c-12.261,10.412-18.842,25.93-22.868,43.171c-3.978,17.318-5.267,36.786-5.279,56.087 c0.004,25.188,2.255,50.069,4.169,68.742c-1.459,0.949-2.909,2.044-4.284,3.506c-4.226,4.426-6.875,11.27-6.802,19.209 c0.004,4.708,0.808,9.891,2.484,15.786l0.008,0.031c4.356,15.158,8.295,25.088,13.624,32.621c2.656,3.744,5.742,6.806,9.095,9.126 c1.646,1.148,3.33,2.098,5.022,2.924c3.035,12.969,8.146,22.37,12.529,29.176c2.465,3.851,4.643,6.913,5.99,9.202 c1.29,2.144,1.673,3.369,1.75,3.928c0,15.228,0,26.451,0,35.431c0.004,0.713-0.276,1.968-1.792,3.982 c-2.197,2.978-6.99,6.775-12.735,9.991c-5.735,3.269-12.361,6.102-18.16,8.146c-15.614,5.528-45.239,16.315-71.468,37.315 c-13.114,10.511-25.425,23.665-34.478,40.094C38.405,448.22,32.72,467.91,32.74,490.794c0,3.973,0.169,8.054,0.517,12.226 l0.75,8.98h443.986l0.75-8.98c0.348-4.164,0.517-8.23,0.517-12.203C479.279,467.941,473.579,448.28,464.496,431.904z M459.592,492.401H52.408c-0.008-0.528-0.069-1.087-0.069-1.607c0.015-19.684,4.754-35.814,12.291-49.534 c11.289-20.533,29.242-35.692,47.474-46.686c18.217-10.994,36.453-17.692,47.818-21.696c8.954-3.169,19.354-7.756,28.212-13.903 c4.429-3.093,8.506-6.577,11.782-10.871c3.235-4.241,5.773-9.623,5.776-15.825c0-9.149,0-20.579,0-36.258v-0.505l-0.054-0.506 c-0.532-4.9-2.48-8.766-4.444-12.096c-3.013-5.023-6.4-9.394-9.497-15.059c-3.085-5.643-5.96-12.456-7.667-22.049l-0.992-5.604 l-5.359-1.914c-2.45-0.88-4.203-1.73-5.681-2.756c-2.174-1.546-4.187-3.613-6.744-8.368c-2.519-4.723-5.286-11.996-8.322-22.669 v-0.007c-1.305-4.548-1.742-7.993-1.742-10.435c0.077-4.196,1.01-5.183,1.516-5.819c0.543-0.612,1.512-1.079,2.587-1.317 l8.521-1.906l-0.915-8.682c-2.01-19.048-4.792-46.532-4.792-73.734c-0.008-18.382,1.297-36.626,4.777-51.685 c2.887-12.717,7.415-22.822,13.05-29.253c9.053,2.066,17.436,2.817,25.268,2.81c13.815-0.03,25.881-1.884,39.167-1.853h0.314 l0.333-0.015c4.98-0.306,9.436-0.452,13.436-0.452c12.754,0.007,20.705,1.462,27.159,3.95c6.446,2.488,11.966,6.278,19.43,12.089 l1.922,1.492l2.392,0.421c8.754,1.569,15.239,4.686,20.472,8.919c7.798,6.323,13.049,15.656,16.361,27.232 c3.3,11.53,4.501,25.05,4.49,38.486c0.004,11.316-0.822,22.569-1.776,32.622c-0.953,10.067-2.025,18.848-2.504,25.708h0.004 c-0.088,1.218-0.195,2.266-0.318,3.446l-0.869,8.352l8.119,2.136c1.037,0.275,1.856,0.72,2.377,1.332 c0.486,0.636,1.37,1.738,1.432,5.735c0,2.434-0.436,5.872-1.726,10.396l-0.008,0.024c-4.038,14.255-7.655,22.385-10.729,26.619 c-1.543,2.136-2.867,3.384-4.345,4.425c-1.478,1.026-3.231,1.876-5.68,2.756l-5.359,1.914l-0.992,5.604 c-1.168,6.606-2.572,11.369-4.054,15.105c-2.236,5.589-4.636,9.049-7.729,13.62c-3.062,4.509-6.752,10.121-9.635,18.168 l-0.582,1.608v1.714c0,15.679,0,27.109,0,36.258c-0.011,5.987,2.328,11.354,5.459,15.573c4.75,6.376,11.242,10.986,18.259,15.013 c7.032,3.989,14.684,7.258,21.876,9.952c15.212,5.658,42.64,15.902,65.836,34.444c11.592,9.256,22.06,20.487,29.613,34.137 c7.545,13.673,12.295,29.751,12.31,49.419C459.661,491.329,459.6,491.88,459.592,492.401z"></path> </g> </g></svg>

                                            </div>
                                          <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                              {{__('Total')}}
                                            <br />
                                            {{__('Customers')}}
                                          </div>
                                          <div class="display-6 text-primary">{{\Auth::user()->countCustomers()}}</div>
                                        </div>
                                      </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="card mb-5 hover-border-primary">

                                        <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                        <div class="account-dashboard-svg" >

                                        <svg width="64px" height="64px" viewBox="-614.4 -614.4 2252.80 2252.80" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg" fill="#100773" stroke="#100773"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M748.6 612.8l52.6-50.8c-43.5-45.1-95.5-78.7-152.6-99.3 49.4-40.3 81.1-101.6 81.1-170.2 0-121-98.4-219.4-219.4-219.4s-219.4 98.4-219.4 219.4c0 69.1 32.1 130.8 82.1 171.1-153.7 56.6-263.6 204.5-263.6 377.7h73.1c0-181.5 147.7-329.1 329.1-329.1 90.2-0.1 174.4 35.7 237 100.6zM510.3 146.3c80.7 0 146.3 65.6 146.3 146.3S591 438.9 510.3 438.9 364 373.2 364 292.6s65.6-146.3 146.3-146.3zM692.7 679.2l160.9 160.9 58.9-219.8-219.8 58.9z m132.5 54.9l-26.5-26.5 36.3-9.7-9.8 36.2zM616.2 913.9L836 855 675.1 694.1l-58.9 219.8z m87.3-113.8l26.5 26.5-36.3 9.7 9.8-36.2z" fill="#100773"></path></g></svg>

                                        </div>
                                        <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                              {{__('Total')}}
                                            <br />
                                            {{__('Vendors')}}
                                          </div>
                                          <div class="display-6 text-primary">{{\Auth::user()->countVenders()}}</div>
                                        </div>
                                      </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="card mb-5 hover-border-primary">

                                        <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                        <div class="account-dashboard-svg" >

                        <svg fill="#100773" height="64px" width="64px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-282.88 -282.88 1007.76 1007.76" xml:space="preserve" stroke="#100773" stroke-width="0.00442"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M312,160.829H130c-5.523,0-10,4.478-10,10v150c0,5.522,4.477,10,10,10h182c5.522,0,10-4.478,10-10v-30 c0-5.522-4.478-10-10-10s-10,4.478-10,10v20h-40.667v-80H302v25.889c0,5.522,4.478,10,10,10s10-4.478,10-10v-85.889 C322,165.307,317.522,160.829,312,160.829z M241.333,180.829v30h-40.667v-30H241.333z M180.667,180.829v30H140v-30H180.667z M140,230.829h40.667v80H140V230.829z M241.333,310.829h-40.667v-80h40.667V310.829z M261.333,210.829v-30H302v30H261.333z"></path> <path d="M381.95,79.002c-0.024-0.243-0.066-0.479-0.107-0.717c-0.015-0.084-0.022-0.17-0.039-0.254 c-0.057-0.286-0.131-0.566-0.212-0.843c-0.01-0.034-0.016-0.068-0.026-0.102c-0.086-0.281-0.187-0.556-0.296-0.826 c-0.012-0.031-0.021-0.063-0.034-0.093c-0.106-0.255-0.226-0.501-0.352-0.745c-0.024-0.046-0.043-0.095-0.068-0.141 c-0.117-0.219-0.248-0.428-0.381-0.637c-0.043-0.068-0.08-0.139-0.125-0.206c-0.125-0.187-0.263-0.363-0.4-0.54 c-0.063-0.082-0.119-0.168-0.185-0.248c-0.151-0.183-0.314-0.354-0.477-0.526c-0.061-0.064-0.115-0.133-0.177-0.196l-70-70 c-0.063-0.063-0.132-0.117-0.196-0.177c-0.172-0.163-0.343-0.326-0.526-0.477c-0.08-0.065-0.166-0.122-0.247-0.185 c-0.177-0.137-0.354-0.275-0.54-0.4c-0.067-0.044-0.138-0.082-0.205-0.125c-0.209-0.133-0.418-0.264-0.637-0.381 c-0.046-0.024-0.095-0.044-0.141-0.068c-0.243-0.126-0.49-0.246-0.745-0.352c-0.031-0.013-0.063-0.021-0.093-0.034 c-0.27-0.109-0.545-0.21-0.826-0.296c-0.034-0.01-0.068-0.016-0.102-0.026c-0.277-0.081-0.557-0.155-0.843-0.212 c-0.084-0.017-0.17-0.024-0.254-0.039c-0.237-0.041-0.474-0.083-0.716-0.107C302.668,0.017,302.335,0,302,0H70 c-5.523,0-10,4.478-10,10v422c0,5.522,4.477,10,10,10h302c5.522,0,10-4.478,10-10V80C382,79.665,381.983,79.332,381.95,79.002z M312,34.143L347.857,70H312V34.143z M80,422V20h212v60c0,5.522,4.478,10,10,10h60v332H80z"></path> <path d="M130,130.829h91c5.522,0,10-4.478,10-10s-4.478-10-10-10h-91c-5.523,0-10,4.478-10,10S124.477,130.829,130,130.829z"></path> <path d="M312,362h-91c-5.523,0-10,4.478-10,10s4.477,10,10,10h91c5.522,0,10-4.478,10-10S317.522,362,312,362z"></path> </g> </g></svg>

                                    </div>
                                          <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                              {{__('Total')}}
                                            <br />
                                            {{__('Invoices')}}
                                          </div>
                                          <div class="display-6 text-primary">{{\Auth::user()->countInvoices()}}</div>
                                        </div>
                                      </div>
                                </div>
                                <div class="col-lg-3 col-6">
                                    <div class="card mb-5 hover-border-primary">

                                        <div class="h-100 d-flex flex-column justify-content-between card-body align-items-center">
                                        <div class="account-dashboard-svg" >

                                 <svg fill="#100773" height="64px" width="64px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-265.19 -265.19 987.60 987.60" xml:space="preserve" stroke="#100773"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M453.473,209.361L143.917,30.638c-2.32-1.34-5.18-1.34-7.5,0L3.768,107.223c-2.32,1.339-3.75,3.815-3.75,6.494L0,241.367 c0,2.68,1.429,5.156,3.75,6.496l309.556,178.722c1.16,0.67,2.455,1.005,3.75,1.005s2.589-0.335,3.749-1.004l132.667-76.573 c2.321-1.339,3.751-3.816,3.751-6.496V215.856C457.223,213.176,455.793,210.7,453.473,209.361z M110.742,292.314l-95.741-55.276 l0.001-8.228l95.74,55.284V292.314z M110.742,266.774L15.004,211.49l0.001-8.198l95.737,55.283V266.774z M110.742,241.254 l-95.734-55.281l0.001-8.208l95.733,55.281V241.254z M110.742,215.724l-95.731-55.279l0.001-8.209l95.73,55.279V215.724z M110.742,190.193l-95.728-55.277l0.001-8.208l95.727,55.274V190.193z M22.517,113.719l117.65-67.926l95.526,55.152 l-117.969,67.748L22.517,113.719z M199.003,343.272l-73.261-42.297V190.644l73.261,42.302V343.272z M132.744,177.366 l117.969-67.749l73.334,42.34l-118.88,67.228L132.744,177.366z M309.556,407.1l-95.553-55.168v-8.215l95.553,55.18V407.1z M309.556,381.575l-95.553-55.18v-8.2l95.553,55.171V381.575z M309.556,356.046l-95.553-55.171v-8.209l95.553,55.171V356.046z M309.556,330.516l-95.553-55.171v-8.21l95.553,55.171V330.516z M309.556,304.986l-95.553-55.171v-8.208l95.553,55.174V304.986z M317.056,283.791l-96.812-55.901l118.881-67.228l95.598,55.193L317.056,283.791z M442.223,339.186l-117.667,67.915v-8.208 l117.667-67.938V339.186z M442.223,313.635l-117.667,67.938v-8.21l117.667-67.938V313.635z M442.223,288.104l-117.667,67.938 v-8.209l117.667-67.929V288.104z M442.223,262.584l-117.667,67.929v-8.21l117.667-67.929V262.584z M442.223,237.055 l-117.667,67.929v-8.202l117.667-67.935V237.055z"></path> <path d="M281.545,141.525c2.68-2.319,3.417-6.286,1.561-9.454c-2.095-3.575-6.689-4.774-10.263-2.68l-4.617,2.704 c-14.975-5.933-32.1-4.763-46.199,3.495l-0.157,0.091c-6.053,3.546-9.64,9.851-9.596,16.866c0.024,3.834,1.137,7.439,3.131,10.49 l-27.47,16.091c-0.929-1.06-1.06-2.292-1.062-2.873c-0.002-0.768,0.208-2.676,2.203-3.828c3.587-2.071,4.816-6.658,2.745-10.245 c-2.071-3.587-6.659-4.815-10.245-2.745c-6.096,3.52-9.723,9.824-9.702,16.863c0.011,3.857,1.123,7.482,3.126,10.549 c-2.712,2.314-3.467,6.303-1.602,9.487c1.396,2.383,3.903,3.71,6.478,3.71c1.288,0,2.593-0.332,3.784-1.03l4.668-2.734 c6.248,2.474,12.865,3.722,19.486,3.722c9.251,0,18.502-2.414,26.724-7.234l0.145-0.084c6.051-3.548,9.637-9.853,9.591-16.867 c-0.025-3.83-1.136-7.43-3.127-10.477l27.467-16.09c0.928,1.059,1.059,2.292,1.062,2.872c0.002,0.768-0.207,2.677-2.202,3.829 l-4.733,2.733c-3.587,2.071-4.816,6.658-2.745,10.245c2.072,3.588,6.661,4.816,10.245,2.745l4.733-2.733 c6.098-3.521,9.725-9.825,9.702-16.866C284.665,148.218,283.552,144.593,281.545,141.525z M227.274,152.452 c-0.005-0.765,0.198-2.668,2.171-3.825l0.157-0.092c5.953-3.487,12.659-5.2,19.361-5.157l-20.531,12.027 C227.429,154.33,227.278,153.048,227.274,152.452z M229.275,175.916c0.005,0.765-0.197,2.668-2.179,3.83l-0.145,0.084 c-5.954,3.49-12.659,5.206-19.358,5.167l20.531-12.027C229.122,174.044,229.271,175.321,229.275,175.916z"></path> </g> </g></svg>
                                    </div>
                                        <div class="text-center mb-0 sh-8 d-flex align-items-center lh-1-5">
                                              {{__('Total')}}
                                            <br />
                                            {{__('Bills')}}
                                          </div>
                                          <div class="display-6 text-primary">{{\Auth::user()->countBills()}} </div>
                                        </div>
                                      </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12 mt-5">
                            <div class="card mb-5 hover-border-primary">
                                <div class="card-header">
                                    <h5>{{__('Income & Expense')}}
                                        <span class="float-end  text-primary">{{__('Current Year').' - '.$currentYear}}</span>
                                    </h5>

                                </div>
                                <div class="card-body">
                                    <div id="incExpBarChart"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="card hover-border-primary">
                                <div class="card-header">
                                    <h5 class="mt-1 mb-0">{{__('Account Balance')}}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>{{__('Bank')}}</th>
                                                <th>{{__('Holder Name')}}</th>
                                                <th>{{__('Balance')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($bankAccountDetail as $bankAccount)

                                                <tr class="font-style">
                                                    <td>{{$bankAccount->bank_name}}</td>
                                                    <td>{{$bankAccount->holder_name}}</td>
                                                    <td>{{\Auth::user()->priceFormat($bankAccount->opening_balance)}}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4">
                                                        <div class="text-center">
                                                            <h6>{{__('there is no account balance')}}</h6>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12 mt-5">
                            <div class="card hover-border-primary">
                                <div class="card-header">
                                    <h5 class="mt-1 mb-0">{{__('Latest Income')}}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>{{__('Date')}}</th>
                                                <th>{{__('Customer')}}</th>
                                                <th>{{__('Amount Due')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($latestIncome as $income)
                                                <tr>
                                                    <td>{{\Auth::user()->dateFormat($income->date)}}</td>
                                                    <td>{{!empty($income->customer)?$income->customer->name:'-'}}</td>
                                                    <td>{{\Auth::user()->priceFormat($income->amount)}}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4">
                                                        <div class="text-center">
                                                            <h6>{{__('There is no latest income')}}</h6>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12 mt-5">
                            <div class="card hover-border-primary">
                                <div class="card-header">
                                    <h5 class="mt-1 mb-0">{{__('Latest Expense')}}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>{{__('Date')}}</th>
                                                <th>{{__('Vendor')}}</th>
                                                <th>{{__('Amount Due')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($latestExpense as $expense)

                                                <tr>
                                                    <td>{{\Auth::user()->dateFormat($expense->date)}}</td>
                                                    <td>{{!empty($expense->vender)?$expense->vender->name:'-'}}</td>
                                                    <td>{{\Auth::user()->priceFormat($expense->amount)}}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4">
                                                        <div class="text-center">
                                                            <h6>{{__('There is no latest expense')}}</h6>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-xxl-12 mt-5">
                            <div class="card hover-border-primary">
                                <div class="card-header">
                                    <h5 class="mt-1 mb-0">{{__('Recent Invoices')}}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{__('Customer')}}</th>
                                                <th>{{__('Issue Date')}}</th>
                                                <th>{{__('Due Date')}}</th>
                                                <th>{{__('Amount')}}</th>
                                                <th>{{__('Status')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($recentInvoice as $invoice)
                                                <tr>
                                                    <td>{{\Auth::user()->invoiceNumberFormat($invoice->invoice_id)}}</td>
                                                    <td>{{!empty($invoice->customer)? $invoice->customer->name:'' }} </td>
                                                    <td>{{ Auth::user()->dateFormat($invoice->issue_date) }}</td>
                                                    <td>{{ Auth::user()->dateFormat($invoice->due_date) }}</td>
                                                    <td>{{\Auth::user()->priceFormat($invoice->getTotal())}}</td>
                                                    <td>
                                                        @if($invoice->status == 0)
                                                            <span class="p-2 px-3 rounded badge status_badge bg-secondary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                        @elseif($invoice->status == 1)
                                                            <span class="p-2 px-3 rounded badge status_badge bg-warning">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                        @elseif($invoice->status == 2)
                                                            <span class="p-2 px-3 rounded badge status_badge bg-danger">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                        @elseif($invoice->status == 3)
                                                            <span class="p-2 px-3 rounded badge status_badge bg-info">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                        @elseif($invoice->status == 4)
                                                            <span class="p-2 px-3 rounded badge status_badge bg-primary">{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6">
                                                        <div class="text-center">
                                                            <h6>{{__('There is no recent invoice')}}</h6>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12 mt-5">
                            <div class="card hover-border-primary">
                                <div class="card-header">
                                    <h5 class="mt-1 mb-0">{{__('Recent Bills')}}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{__('Vendor')}}</th>
                                                <th>{{__('Bill Date')}}</th>
                                                <th>{{__('Due Date')}}</th>
                                                <th>{{__('Amount')}}</th>
                                                <th>{{__('Status')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($recentBill as $bill)
                                                <tr>
                                                    <td>{{\Auth::user()->billNumberFormat($bill->bill_id)}}</td>
                                                    <td>{{!empty($bill->vender)? $bill->vender->name:'' }} </td>
                                                    <td>{{ Auth::user()->dateFormat($bill->bill_date) }}</td>
                                                    <td>{{ Auth::user()->dateFormat($bill->due_date) }}</td>
                                                    <td>{{\Auth::user()->priceFormat($bill->getTotal())}}</td>
                                                    <td>
                                                        @if($bill->status == 0)
                                                            <span class="p-2 px-3 rounded badge bg-secondary">{{ __(\App\Models\Bill::$statues[$bill->status]) }}</span>
                                                        @elseif($bill->status == 1)
                                                            <span class="p-2 px-3 rounded badge bg-warning">{{ __(\App\Models\Bill::$statues[$bill->status]) }}</span>
                                                        @elseif($bill->status == 2)
                                                            <span class="p-2 px-3 rounded badge bg-danger">{{ __(\App\Models\Bill::$statues[$bill->status]) }}</span>
                                                        @elseif($bill->status == 3)
                                                            <span class="p-2 px-3 rounded badge bg-info">{{ __(\App\Models\Bill::$statues[$bill->status]) }}</span>
                                                        @elseif($bill->status == 4)
                                                            <span class="p-2 px-3 rounded badge bg-primary">{{ __(\App\Models\Bill::$statues[$bill->status]) }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6">
                                                        <div class="text-center">
                                                            <h6>{{__('There is no recent bill')}}</h6>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="col-xxl-5">
                    <div class="row">
                        <div class="col-12 mt-5">
                            <div class="card hover-border-primary">
                                <div class="card-header">
                                    <h5 class="mt-1 mb-0">{{__('Cashflow')}}</h5>
                                </div>
                                <div class="card-body">
                                    <div id="cash-flow"></div>
                                </div>
                            </div>



                            <div class="card hover-border-primary mt-5">
                                <div class="card-header">
                                    <h5 class="mt-1 mb-0">{{__('Income Vs Expense')}}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 col-6 my-2">
                                            <div class="d-flex align-items-start mb-2">
                                                <div class="sw-8 sh-8 rounded-xl d-flex justify-content-center align-items-center account-dashboard-svg ">

                           <svg fill="#100773" width="64px" height="64px" viewBox="-747.52 -747.52 2519.04 2519.04" xmlns="http://www.w3.org/2000/svg" stroke="#100773"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M277.675 981.521c5.657 0 10.24-4.583 10.24-10.24V499.514c0-5.651-4.588-10.24-10.24-10.24h-81.92c-5.652 0-10.24 4.589-10.24 10.24v471.767c0 5.657 4.583 10.24 10.24 10.24h81.92zm0 40.96h-81.92c-28.278 0-51.2-22.922-51.2-51.2V499.514c0-28.271 22.924-51.2 51.2-51.2h81.92c28.276 0 51.2 22.929 51.2 51.2v471.767c0 28.278-22.922 51.2-51.2 51.2zm275.456-40.96c5.657 0 10.24-4.583 10.24-10.24V408.777c0-5.657-4.583-10.24-10.24-10.24h-81.92a10.238 10.238 0 00-10.24 10.24v562.504c0 5.657 4.583 10.24 10.24 10.24h81.92zm0 40.96h-81.92c-28.278 0-51.2-22.922-51.2-51.2V408.777c0-28.278 22.922-51.2 51.2-51.2h81.92c28.278 0 51.2 22.922 51.2 51.2v562.504c0 28.278-22.922 51.2-51.2 51.2zm275.456-40.016c5.657 0 10.24-4.583 10.24-10.24V318.974c0-5.651-4.588-10.24-10.24-10.24h-81.92c-5.652 0-10.24 4.589-10.24 10.24v653.251c0 5.657 4.583 10.24 10.24 10.24h81.92zm0 40.96h-81.92c-28.278 0-51.2-22.922-51.2-51.2V318.974c0-28.271 22.924-51.2 51.2-51.2h81.92c28.276 0 51.2 22.929 51.2 51.2v653.251c0 28.278-22.922 51.2-51.2 51.2zM696.848 40.96l102.39.154c11.311.017 20.494-9.138 20.511-20.449S810.611.171 799.3.154L696.91 0c-11.311-.017-20.494 9.138-20.511 20.449s9.138 20.494 20.449 20.511z"></path><path d="M778.789 20.571l-.307 101.827c-.034 11.311 9.107 20.508 20.418 20.542s20.508-9.107 20.542-20.418l.307-101.827C819.783 9.384 810.642.187 799.331.153s-20.508 9.107-20.542 20.418z"></path><path d="M163.84 317.682h154.184a51.207 51.207 0 0036.211-14.999L457.208 199.71a10.263 10.263 0 017.237-3.003h159.754a51.235 51.235 0 0036.198-14.976l141.13-141.13c7.998-7.998 7.998-20.965 0-28.963s-20.965-7.998-28.963 0L631.447 152.755a10.265 10.265 0 01-7.248 2.992H464.445a51.226 51.226 0 00-36.201 14.999L325.271 273.719a10.244 10.244 0 01-7.248 3.003H163.839c-11.311 0-20.48 9.169-20.48 20.48s9.169 20.48 20.48 20.48z"></path></g></svg>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-primary text-pt mb-0">{{__('Income Today')}}</p>
                                                    <h4 class="mb-0 text-success">{{\Auth::user()->priceFormat(\Auth::user()->todayIncome())}}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-6 my-2">
                                            <div class="d-flex align-items-start mb-2">
                                                <div class="sw-8 sh-8 rounded-xl d-flex justify-content-center align-items-center account-dashboard-svg">

                                                <svg width="64px" height="64px" viewBox="-17.28 -17.28 58.56 58.56" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" fill="#100773" stroke="#100773"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><defs><style>.cls-1{fill:none;stroke:#100773;stroke-miterlimit:10;stroke-width:1.91px;}</style></defs><path class="cls-1" d="M12,12H22.5a10.45,10.45,0,0,1-3.07,7.42L12,12V1.48A10.5,10.5,0,1,0,19.43,19.4"></path><path class="cls-1" d="M15.82,8.16h3.34a1.43,1.43,0,0,0,1.43-1.43h0A1.43,1.43,0,0,0,19.16,5.3h-1a1.44,1.44,0,0,1-1.43-1.44h0A1.43,1.43,0,0,1,18.2,2.43h3.35"></path><line class="cls-1" x1="18.68" y1="0.52" x2="18.68" y2="2.43"></line><line class="cls-1" x1="18.68" y1="8.16" x2="18.68" y2="10.07"></line></g></svg>

                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-primary text-sm mb-0">{{__('Expense Today')}}</p>
                                                    <h4 class="mb-0 text-info">{{\Auth::user()->priceFormat(\Auth::user()->todayExpense())}}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-6 my-2">
                                            <div class="d-flex align-items-start mb-2">
                                                <div class="sw-8 sh-8 rounded-xl d-flex justify-content-center align-items-center account-dashboard-svg">

<svg fill="#100773" height="64px" width="64px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-248.73 -248.73 985.16 985.16" xml:space="preserve" stroke="#100773"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <path d="M91.702,243.751c0,31.1,25.1,56.4,56.2,56.4c31,0,56.2-25.2,56.2-56.4s-25.1-56.4-56.2-56.4 C116.902,187.351,91.702,212.651,91.702,243.751z M166.402,214.651c0.4,0.2,0.8,0.3,1.2,0.6c1.5,0.9,1.9,2,1.2,3.7 s-1.4,3.4-2.2,5.1c-0.7,1.5-1.6,1.9-3.2,1.6c-2.1-0.5-4.3-1.2-6.4-1.6c-4.5-0.9-9-1-13.5,0.8c-3.8,1.5-6.1,4.4-7.7,8v0.1h13.2 c1.1,0,2,0.9,2,2v4.4c0,1.1-0.9,2-2,2h-15.1c0,1.2,0,2.3,0,3.5h15.1c1.1,0,2,0.9,2,2v4.4c0,1.1-0.9,2-2,2h-13.8 c1.7,4.8,4.4,8.5,9.4,10.1c4.1,1.3,8.2,1.2,12.3,0.3c2.1-0.4,4.2-1.1,6.3-1.6c1.7-0.4,2.6,0.1,3.3,1.6c0.8,1.7,1.5,3.5,2.2,5.2 c0.6,1.6,0.2,2.8-1.4,3.6c-1.7,0.8-3.5,1.4-5.2,1.9c-6.8,1.9-13.7,2-20.5-0.2c-8.2-2.7-13.7-8.3-16.9-16.2c-0.6-1.5-1-3.1-1.5-4.7 h-6.4c-1.1,0-2-0.9-2-2v-4.4c0-1.1,0.9-2,2-2h5.3c0-1.2,0-2.3,0-3.5h-5.3c-1.1,0-2-0.9-2-2v-4.4c0-1.1,0.9-2,2-2h6.8l0.2-0.7 c1.8-5.8,5-10.8,9.8-14.6c3.8-3,8.3-4.6,13-5.4C153.502,211.251,160.002,212.151,166.402,214.651z M71.302,234.051 c1.9,0,3.5,1.6,3.5,3.5v12.5c0,1.9-1.6,3.5-3.5,3.5h-21.7c-1.9,0-3.5-1.6-3.5-3.5v-12.5c0-1.9,1.6-3.5,3.5-3.5L71.302,234.051 L71.302,234.051z M398.502,317.451v12.7c0,3-2.4,5.4-5.4,5.4h-69.9c-3,0-5.4-2.4-5.4-5.4v-12.7c0-3,2.4-5.4,5.4-5.4h69.9 C396.102,312.151,398.502,314.551,398.502,317.451z M398.502,285.551v12.7c0,3-2.4,5.4-5.4,5.4h-69.9c-3,0-5.4-2.4-5.4-5.4v-12.7 c0-3,2.4-5.4,5.4-5.4h69.9C396.102,280.151,398.502,282.551,398.502,285.551z M398.502,253.551v12.7c0,3-2.4,5.4-5.4,5.4h-69.9 c-3,0-5.4-2.4-5.4-5.4v-12.7c0-3,2.4-5.4,5.4-5.4h69.9C396.102,248.251,398.502,250.651,398.502,253.551z M398.502,221.651v12.7 c0,3-2.4,5.4-5.4,5.4h-69.9c-3,0-5.4-2.4-5.4-5.4v-12.7c0-3,2.4-5.4,5.4-5.4h69.9C396.102,216.251,398.502,218.651,398.502,221.651 z M7.002,152.151h214.3l0,0c-0.7,1.7-1.1,3.5-1.1,5.4v12.7l0,0c0,0.7,0.1,1.4,0.2,2.1c0.1,0.6,0.2,1.1,0.4,1.6l0,0h-168.9 c0.3,1.5,0.4,3.1,0.4,4.8c0,14.3-11.6,26-26,26c-1.6,0-3.1-0.1-4.6-0.4v78.8c1.5-0.3,3-0.4,4.6-0.4c14.3,0,26,11.6,26,26 c0,1.6-0.2,3.2-0.4,4.8h168.9c-0.4,1.2-0.6,2.5-0.6,3.8v12.7c0,1.9,0.4,3.7,1.1,5.4H7.002c-3.9,0-7-3.2-7-7v-169.3 C-0.098,155.351,3.102,152.151,7.002,152.151z M309.302,317.251v12.7c0,3-2.4,5.4-5.4,5.4h-69.9c-3,0-5.4-2.4-5.4-5.4v-12.7 c0-3,2.4-5.4,5.4-5.4h69.9C306.902,311.951,309.302,314.351,309.302,317.251z M309.302,285.351v12.7c0,3-2.4,5.4-5.4,5.4h-69.9 c-3,0-5.4-2.4-5.4-5.4v-12.7c0-3,2.4-5.4,5.4-5.4h69.9C306.902,279.951,309.302,282.351,309.302,285.351z M309.302,253.351v12.7 c0,3-2.4,5.4-5.4,5.4h-69.9c-3,0-5.4-2.4-5.4-5.4v-12.7c0-3,2.4-5.4,5.4-5.4h69.9C306.902,247.951,309.302,250.451,309.302,253.351 z M309.302,221.451v12.7c0,3-2.4,5.4-5.4,5.4h-69.9c-3,0-5.4-2.4-5.4-5.4v-12.7c0-3,2.4-5.4,5.4-5.4h69.9 C306.902,216.051,309.302,218.451,309.302,221.451z M309.302,189.451v12.7c0,3-2.4,5.4-5.4,5.4h-69.9c-3,0-5.4-2.4-5.4-5.4v-12.7 c0-3,2.4-5.4,5.4-5.4h69.9C306.902,184.051,309.302,186.551,309.302,189.451z M309.302,157.551v12.7c0,3-2.4,5.4-5.4,5.4h-69.9 c-3,0-5.4-2.4-5.4-5.4v-12.7c0-3,2.4-5.4,5.4-5.4h69.9C306.902,152.151,309.302,154.551,309.302,157.551z M487.702,317.451v12.7 c0,3-2.4,5.4-5.4,5.4h-69.9c-3,0-5.4-2.4-5.4-5.4v-12.7c0-3,2.4-5.4,5.4-5.4h69.9C485.302,312.151,487.702,314.551,487.702,317.451 z M487.702,285.551v12.7c0,3-2.4,5.4-5.4,5.4h-69.9c-3,0-5.4-2.4-5.4-5.4v-12.7c0-3,2.4-5.4,5.4-5.4h69.9 C485.302,280.151,487.702,282.551,487.702,285.551z"></path> </g> </g></svg>

                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-primary text-sm mb-0">{{__('Income This Month')}}</p>
                                                        <h4 class="mb-0 text-warning">{{\Auth::user()->priceFormat(\Auth::user()->incomeCurrentMonth())}}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-6 my-2">
                                            <div class="d-flex align-items-start mb-2">

                                            <div class="sw-8 sh-8 rounded-xl d-flex justify-content-center align-items-center account-dashboard-svg">

<svg width="64px" height="64px" viewBox="-634.88 -634.88 2293.76 2293.76" fill="#100773" class="icon" version="1.1" xmlns="http://www.w3.org/2000/svg" stroke="#100773"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"><path d="M272.016 512c-79.394 0-143.992-64.598-143.992-143.992 0-79.392 64.598-143.99 143.992-143.99 79.392 0 143.99 64.598 143.99 143.99 0 79.394-64.598 143.992-143.99 143.992z m0-271.984c-70.574 0-127.992 57.42-127.992 127.992 0 70.574 57.418 127.992 127.992 127.992s127.992-57.418 127.992-127.992c0-70.572-57.418-127.992-127.992-127.992z" fill=""></path><path d="M272.016 543.998c-79.394 0-143.992-64.598-143.992-143.992 0-4.42 3.578-7.998 8-7.998s8 3.578 8 7.998c0 70.574 57.418 127.994 127.992 127.994s127.992-57.42 127.992-127.994a7.994 7.994 0 0 1 8-7.998 7.994 7.994 0 0 1 7.998 7.998c0 79.394-64.598 143.992-143.99 143.992z" fill=""></path><path d="M136.024 408.006c-4.422 0-8-3.578-8-8v-31.998c0-4.42 3.578-7.998 8-7.998s8 3.578 8 7.998v31.998c0 4.422-3.578 8-8 8zM408.008 408.006c-4.422 0-8-3.578-8-8v-31.998a7.994 7.994 0 0 1 8-7.998 7.994 7.994 0 0 1 7.998 7.998v31.998c0 4.422-3.578 8-7.998 8zM272.016 432.004c-35.288 0-63.996-28.708-63.996-63.996s28.708-63.996 63.996-63.996 63.996 28.708 63.996 63.996-28.71 63.996-63.996 63.996z m0-111.992c-26.466 0-47.998 21.53-47.998 47.996 0 26.468 21.532 47.998 47.998 47.998s47.996-21.53 47.996-47.998c0-26.466-21.53-47.996-47.996-47.996z" fill=""></path><path d="M325.7 392.008a8.008 8.008 0 0 1-7.672-5.718c-5.998-20.188-24.92-34.28-46.012-34.28s-40.014 14.092-46.014 34.28a7.994 7.994 0 0 1-9.952 5.39 8.012 8.012 0 0 1-5.39-9.954c8.008-26.912 33.232-45.716 61.356-45.716s53.348 18.804 61.356 45.716a8.012 8.012 0 0 1-5.39 9.954 8.348 8.348 0 0 1-2.282 0.328zM231.94 335.932a7.976 7.976 0 0 1-5.656-2.344l-56.074-56.074a8 8 0 0 1 11.312-11.31l56.074 56.074a7.996 7.996 0 0 1-5.656 13.654zM272.016 512c-4.422 0-8-3.578-8-8v-79.994c0-4.422 3.578-8 8-8s8 3.578 8 8V504c0 4.422-3.578 8-8 8zM528 96.026h-31.998c-4.422 0-8-3.578-8-8V56.028c0-4.422 3.578-8 8-8H528c4.422 0 8 3.578 8 8v31.998c0 4.422-3.578 8-8 8z m-23.998-16H520v-15.998h-15.998v15.998z" fill=""></path><path d="M983.972 767.984H40.03c-4.422 0-8-3.578-8-8V152.022c0-4.422 3.578-8 8-8h943.944c4.422 0 8 3.578 8 8v607.962a8 8 0 0 1-8.002 8z m-935.942-16h927.944V160.022H48.03v591.962z" fill=""></path><path d="M1015.97 160.022H8.032c-4.422 0-8-3.578-8-8V88.026c0-4.422 3.578-8 8-8h1007.94a7.994 7.994 0 0 1 7.998 8v63.996a7.998 7.998 0 0 1-8 8z m-999.938-16h991.94V96.026H16.032v47.996z" fill=""></path><path d="M967.972 128.024H56.03c-4.422 0-8-3.578-8-8s3.578-8 8-8h911.944c4.422 0 8 3.578 8 8s-3.58 8-8.002 8zM727.988 975.972a8.08 8.08 0 0 1-3.578-0.844l-215.988-107.994c-3.952-1.968-5.552-6.782-3.576-10.734s6.788-5.53 10.732-3.578l215.988 107.994a8 8 0 1 1-3.578 15.156zM791.984 975.972a8.08 8.08 0 0 1-3.578-0.844l-279.984-139.992c-3.952-1.968-5.552-6.782-3.576-10.734s6.788-5.546 10.732-3.578l279.984 139.992a8 8 0 1 1-3.578 15.156z" fill=""></path><path d="M791.984 975.972h-63.996c-4.422 0-8-3.578-8-8s3.578-8 8-8h63.996c4.422 0 8 3.578 8 8s-3.578 8-8 8zM296.022 975.972a8.012 8.012 0 0 1-7.164-4.422 7.996 7.996 0 0 1 3.578-10.734l215.986-107.994a8.02 8.02 0 0 1 10.734 3.578 7.996 7.996 0 0 1-3.578 10.734l-215.986 107.994a8.096 8.096 0 0 1-3.57 0.844zM232.026 975.972a8.012 8.012 0 0 1-7.164-4.422 7.996 7.996 0 0 1 3.578-10.734l279.982-139.992a8.006 8.006 0 0 1 10.734 3.578 7.996 7.996 0 0 1-3.578 10.734l-279.982 139.992a8.096 8.096 0 0 1-3.57 0.844z" fill=""></path><path d="M296.014 975.972h-63.996c-4.422 0-8-3.578-8-8s3.578-8 8-8h63.996c4.422 0 8 3.578 8 8s-3.578 8-8 8zM496.002 843.98c-4.422 0-8-3.578-8-8v-75.996c0-4.422 3.578-8 8-8s8 3.578 8 8v75.996c0 4.422-3.578 8-8 8zM528 843.98c-4.422 0-8-3.578-8-8v-75.996c0-4.422 3.578-8 8-8s8 3.578 8 8v75.996c0 4.422-3.578 8-8 8zM528 975.972c-4.422 0-8-3.578-8-8v-99.994c0-4.422 3.578-8 8-8s8 3.578 8 8v99.994c0 4.422-3.578 8-8 8z" fill=""></path><path d="M528 975.972h-31.998c-4.422 0-8-3.578-8-8s3.578-8 8-8H528c4.422 0 8 3.578 8 8s-3.578 8-8 8z" fill=""></path><path d="M496.002 975.972c-4.422 0-8-3.578-8-8v-99.994c0-4.422 3.578-8 8-8s8 3.578 8 8v99.994c0 4.422-3.578 8-8 8zM512 719.988a7.994 7.994 0 0 1-7.998-8V200.02c0-4.422 3.578-8 7.998-8 4.422 0 8 3.578 8 8v511.968c0 4.422-3.578 8-8 8zM599.996 528c-4.422 0-8-3.578-8-8V248.016c0-4.422 3.578-8 8-8s8 3.578 8 8V520c0 4.422-3.578 8-8 8z" fill=""></path><path d="M903.976 528h-303.98c-4.422 0-8-3.578-8-8s3.578-8 8-8h303.98c4.422 0 8 3.578 8 8s-3.578 8-8 8z" fill=""></path><path d="M903.976 607.994h-303.98c-4.422 0-8-3.578-8-8a7.994 7.994 0 0 1 8-7.998h303.98c4.422 0 8 3.578 8 7.998 0 4.422-3.578 8-8 8z" fill=""></path><path d="M871.978 639.992H631.994c-4.422 0-8-3.578-8-8s3.578-8 8-8h239.984c4.422 0 8 3.578 8 8s-3.578 8-8 8z" fill=""></path><path d="M823.982 671.99h-143.992a7.994 7.994 0 0 1-7.998-8 7.994 7.994 0 0 1 7.998-7.998h143.992a7.994 7.994 0 0 1 7.998 7.998c0 4.422-3.578 8-7.998 8z" fill=""></path><path d="M424.006 607.994H120.026c-4.422 0-8-3.578-8-8a7.994 7.994 0 0 1 8-7.998h303.98c4.422 0 8 3.578 8 7.998 0 4.422-3.578 8-8 8z" fill=""></path><path d="M392.008 639.992H152.024c-4.422 0-8-3.578-8-8s3.578-8 8-8h239.984c4.422 0 8 3.578 8 8s-3.578 8-8 8z" fill=""></path><path d="M344.012 671.99H200.02c-4.422 0-8-3.578-8-8a7.994 7.994 0 0 1 8-7.998h143.992a7.994 7.994 0 0 1 7.998 7.998c0 4.422-3.578 8-7.998 8z" fill=""></path><path d="M631.994 464.002a7.996 7.996 0 0 1-5.656-13.654l95.994-95.994a8 8 0 0 1 11.312 11.31l-95.996 95.994a7.968 7.968 0 0 1-5.654 2.344z" fill=""></path><path d="M775.984 416.006a7.976 7.976 0 0 1-5.656-2.344l-47.996-47.998a8 8 0 0 1 11.312-11.31l47.996 47.996a8 8 0 0 1-5.656 13.656z" fill=""></path><path d="M775.984 416.006a8 8 0 0 1-5.656-13.656l127.992-127.992a8 8 0 1 1 11.312 11.312l-127.992 127.992a7.974 7.974 0 0 1-5.656 2.344z" fill=""></path><path d="M903.976 320.012c-4.422 0-8-3.578-8-8v-31.998c0-4.422 3.578-8 8-8s8 3.578 8 8v31.998c0 4.422-3.578 8-8 8z" fill=""></path><path d="M903.976 288.014h-31.998c-4.42 0-7.998-3.578-7.998-8s3.578-8 7.998-8h31.998c4.422 0 8 3.578 8 8s-3.578 8-8 8z" fill=""></path></g></svg>

                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-primary text-sm mb-0">{{__('Expense This Month')}}</p>
                                                    <h4 class="mb-0 text-danger">{{\Auth::user()->priceFormat(\Auth::user()->expenseCurrentMonth())}}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-12 mt-5">
                            <div class="card hover-border-primary">
                                <div class="card-header">
                                    <h5>{{__('Storage Limit')}}
{{--                                        <span class="float-end text-muted">{{__('Year').' - '.$currentYear}}</span>--}}
                                        <small class="float-end text-primary">{{ $users->storage_limit . 'MB' }} / {{ $plan->storage_limit . 'MB' }}</small>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div id="limit-chart"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-12 mt-5">
                            <div class="card hover-border-primary">
                                <div class="card-header">
                                    <h5>{{__('Income By Category')}}
                                        <span class="float-end text-primary">{{__('Year').' - '.$currentYear}}</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div id="incomeByCategory"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12 mt-5">
                            <div class="card hover-border-primary">
                                <div class="card-header">
                                    <h5>{{__('Expense By Category')}}
                                        <span class="float-end text-primary">{{__('Year').' - '.$currentYear}}</span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div id="expenseByCategory"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xxl-12 mt-5">
                            <div class="card hover-border-primary">
                                <div class="card-body">

                                    <ul class="nav nav-pills mb-5" id="pills-tab" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" href="#invoice_weekly_statistics" role="tab" aria-controls="pills-home" aria-selected="true">{{__('Invoices Weekly Statistics')}}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" href="#invoice_monthly_statistics" role="tab" aria-controls="pills-profile" aria-selected="false">{{__('Invoices Monthly Statistics')}}</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="pills-tabContent">
                                        <div class="tab-pane fade show active" id="invoice_weekly_statistics" role="tabpanel" aria-labelledby="pills-home-tab">
                                            <div class="table-responsive">
                                                <table class="table align-items-center mb-0 ">
                                                    <tbody class="list">
                                                    <tr>
                                                        <td>
                                                            <h5 class="mb-0">{{__('Total')}}</h5>
                                                            <p class="text-primary text-sm mb-0">{{__('Invoice Generated')}}</p>

                                                        </td>
                                                        <td>
                                                            <h4 class="text-primary">{{\Auth::user()->priceFormat($weeklyInvoice['invoiceTotal'])}}</h4>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <h5 class="mb-0">{{__('Total')}}</h5>
                                                            <p class="text-primary text-sm mb-0">{{__('Paid')}}</p>
                                                        </td>
                                                        <td>
                                                            <h4 class="text-primary">{{\Auth::user()->priceFormat($weeklyInvoice['invoicePaid'])}}</h4>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <h5 class="mb-0">{{__('Total')}}</h5>
                                                            <p class="text-primary text-sm mb-0">{{__('Due')}}</p>
                                                        </td>
                                                        <td>
                                                            <h4 class="text-primary">{{\Auth::user()->priceFormat($weeklyInvoice['invoiceDue'])}}</h4>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="invoice_monthly_statistics" role="tabpanel" aria-labelledby="pills-profile-tab">
                                            <div class="table-responsive">
                                                <table class="table align-items-center mb-0 ">
                                                    <tbody class="list">
                                                    <tr>
                                                        <td>
                                                            <h5 class="mb-0">{{__('Total')}}</h5>
                                                            <p class="text-primary text-sm mb-0">{{__('Invoice Generated')}}</p>

                                                        </td>
                                                        <td>
                                                            <h4 class="text-primary">{{\Auth::user()->priceFormat($monthlyInvoice['invoiceTotal'])}}</h4>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <h5 class="mb-0">{{__('Total')}}</h5>
                                                            <p class="text-primary text-sm mb-0">{{__('Paid')}}</p>
                                                        </td>
                                                        <td>
                                                            <h4 class="text-primary">{{\Auth::user()->priceFormat($monthlyInvoice['invoicePaid'])}}</h4>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <h5 class="mb-0">{{__('Total')}}</h5>
                                                            <p class="text-primary text-sm mb-0">{{__('Due')}}</p>
                                                        </td>
                                                        <td>
                                                            <h4 class="text-primary">{{\Auth::user()->priceFormat($monthlyInvoice['invoiceDue'])}}</h4>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12 mt-5">
                            <div class="card hover-border-primary">
                                <div class="card-body">

                                    <ul class="nav nav-pills mb-5" id="pills-tab" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" href="#bills_weekly_statistics" role="tab" aria-controls="pills-home" aria-selected="true">{{__('Bills Weekly Statistics')}}</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" href="#bills_monthly_statistics" role="tab" aria-controls="pills-profile" aria-selected="false">{{__('Bills Monthly Statistics')}}</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="pills-tabContent">
                                        <div class="tab-pane fade show active" id="bills_weekly_statistics" role="tabpanel" aria-labelledby="pills-home-tab">
                                            <div class="table-responsive">
                                                <table class="table align-items-center mb-0 ">
                                                    <tbody class="list">
                                                    <tr>
                                                        <td>
                                                            <h5 class="mb-0">{{__('Total')}}</h5>
                                                            <p class="text-primary text-sm mb-0">{{__('Bill Generated')}}</p>

                                                        </td>
                                                        <td>
                                                            <h4 class="text-primary">{{\Auth::user()->priceFormat($weeklyBill['billTotal'])}}</h4>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <h5 class="mb-0">{{__('Total')}}</h5>
                                                            <p class="text-primary text-sm mb-0">{{__('Paid')}}</p>
                                                        </td>
                                                        <td>
                                                            <h4 class="text-primary">{{\Auth::user()->priceFormat($weeklyBill['billPaid'])}}</h4>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <h5 class="mb-0">{{__('Total')}}</h5>
                                                            <p class="text-primary text-sm mb-0">{{__('Due')}}</p>
                                                        </td>
                                                        <td>
                                                            <h4 class="text-primary">{{\Auth::user()->priceFormat($weeklyBill['billDue'])}}</h4>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="bills_monthly_statistics" role="tabpanel" aria-labelledby="pills-profile-tab">
                                            <div class="table-responsive">
                                                <table class="table align-items-center mb-0 ">
                                                    <tbody class="list">
                                                    <tr>
                                                        <td>
                                                            <h5 class="mb-0">{{__('Total')}}</h5>
                                                            <p class="text-primary text-sm mb-0">{{__('Bill Generated')}}</p>

                                                        </td>
                                                        <td>
                                                            <h4 class="text-primary">{{\Auth::user()->priceFormat($monthlyBill['billTotal'])}}</h4>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <h5 class="mb-0">{{__('Total')}}</h5>
                                                            <p class="text-primary text-sm mb-0">{{__('Paid')}}</p>
                                                        </td>
                                                        <td>
                                                            <h4 class="text-primary">{{\Auth::user()->priceFormat($monthlyBill['billPaid'])}}</h4>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>
                                                            <h5 class="mb-0">{{__('Total')}}</h5>
                                                            <p class="text-primary text-sm mb-0">{{__('Due')}}</p>
                                                        </td>
                                                        <td>
                                                            <h4 class="text-primary">{{\Auth::user()->priceFormat($monthlyBill['billDue'])}}</h4>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-12 mt-5">
                    <div class="card hover-border-primary">
                        <div class="card-header">
                            <h5>{{__('Goal')}}</h5>
                        </div>
                        <div class="card-body">
                            @forelse($goals as $goal)
                                @php
                                    $total= $goal->target($goal->type,$goal->from,$goal->to,$goal->amount)['total'];
                                    $percentage=$goal->target($goal->type,$goal->from,$goal->to,$goal->amount)['percentage'];
                                    $per=number_format($goal->target($goal->type,$goal->from,$goal->to,$goal->amount)['percentage'], Utility::getValByName('decimal_number'), '.', '');
                                @endphp
                                <div class="card border-success border-2 border-bottom-0 border-start-0 border-end-0">
                                    <div class="card-body">
                                        <div class="form-check">
                                            <label class="form-check-label d-block" for="customCheckdef1">
                                                <span>
                                                    <span class="row align-items-center">
                                                        <span class="col">
                                                            <span class="text-primary text-sm">{{__('Name')}}</span>
                                                            <h6 class="text-nowrap mb-3 mb-sm-0">{{$goal->name}}</h6>
                                                        </span>
                                                        <span class="col">
                                                            <span class="text-primary text-sm">{{__('Type')}}</span>
                                                            <h6 class="mb-3 mb-sm-0">{{ __(\App\Models\Goal::$goalType[$goal->type]) }}</h6>
                                                        </span>
                                                        <span class="col">
                                                            <span class="text-primary text-sm">{{__('Duration')}}</span>
                                                            <h6 class="mb-3 mb-sm-0">{{$goal->from .' To '.$goal->to}}</h6>
                                                        </span>
                                                        <span class="col">
                                                            <span class="text-primary text-sm">{{__('Target')}}</span>
                                                            <h6 class="mb-3 mb-sm-0">{{\Auth::user()->priceFormat($total).' of '. \Auth::user()->priceFormat($goal->amount)}}</h6>
                                                        </span>
                                                        <span class="col">
                                                            <span class="text-primary text-sm">{{__('Progress')}}</span>
                                                            <h6 class="mb-2 d-block">{{number_format($goal->target($goal->type,$goal->from,$goal->to,$goal->amount)['percentage'], Utility::getValByName('decimal_number'), '.', '')}}%</h6>
                                                            <div class="progress mb-0">
                                                                @if($per<=33)
                                                                    <div class="progress-bar bg-danger" style="width: {{$per}}%"></div>
                                                                @elseif($per>=33 && $per<=66)
                                                                    <div class="progress-bar bg-warning" style="width: {{$per}}%"></div>
                                                                @else
                                                                    <div class="progress-bar bg-primary" style="width: {{$per}}%"></div>
                                                                @endif
                                                            </div>
                                                        </span>
                                                    </span>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="card pb-0">
                                    <div class="card-body text-center">
                                        <h6>{{__('There is no goal.')}}</h6>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
