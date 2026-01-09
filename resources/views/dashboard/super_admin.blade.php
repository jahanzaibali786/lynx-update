@extends('layouts.admin')
@section('page-title')
    {{__('Dashboard')}}
@endsection

@push('theme-script')
    <script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
@endpush
@push('script-page')
    <script>
        (function () {
            var chartBarOptions = {
                series: [
                    {
                        name: '{{ __("Income") }}',
                        data:  {!! json_encode ($chartData['data']) !!},

                    },
                ],

                chart: {
                    height: 300,
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
                    categories: {!! json_encode($chartData['label']) !!},
                    title: {
                        text: '{{ __("Months") }}'
                    }
                },
                colors: ['#6fd944', '#6fd944'],

                grid: {
                    strokeDashArray: 4,
                },
                legend: {
                    show: false,
                },
                // markers: {
                //     size: 4,
                //     colors: ['#ffa21d', '#FF3A6E'],
                //     opacity: 0.9,
                //     strokeWidth: 2,
                //     hover: {
                //         size: 7,
                //     }
                // },
                yaxis: {
                    title: {
                        text: '{{ __("Income") }}'
                    },

                }

            };
            var arChart = new ApexCharts(document.querySelector("#chart-sales"), chartBarOptions);
            arChart.render();
        })();
    </script>
@endpush
@section('content')
    <div class="row">
    <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center mb-3 mt-3">
                            <div class="account-dashboard-svg">
                            <svg width="64px" height="64px" viewBox="-12 -12 48.00 48.00" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#100773"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M11 21H5.6C5.03995 21 4.75992 21 4.54601 20.891C4.35785 20.7951 4.20487 20.6422 4.10899 20.454C4 20.2401 4 19.9601 4 19.4V17.6841C4 17.0485 4 16.7306 4.04798 16.4656C4.27087 15.2344 5.23442 14.2709 6.46558 14.048C6.5425 14.0341 6.6237 14.0242 6.71575 14.0172C6.94079 14 7.05331 13.9914 7.20361 14.0026C7.35983 14.0143 7.4472 14.0297 7.59797 14.0722C7.74302 14.1131 8.00429 14.2315 8.52682 14.4682C8.98953 14.6778 9.48358 14.8304 10 14.917M19.8726 15.2038C19.8044 15.2079 19.7357 15.21 19.6667 15.21C18.6422 15.21 17.7077 14.7524 17 14C16.2923 14.7524 15.3578 15.2099 14.3333 15.2099C14.2643 15.2099 14.1956 15.2078 14.1274 15.2037C14.0442 15.5853 14 15.9855 14 16.3979C14 18.6121 15.2748 20.4725 17 21C18.7252 20.4725 20 18.6121 20 16.3979C20 15.9855 19.9558 15.5853 19.8726 15.2038ZM15 7C15 9.20914 13.2091 11 11 11C8.79086 11 7 9.20914 7 7C7 4.79086 8.79086 3 11 3C13.2091 3 15 4.79086 15 7Z" stroke="#100773" stroke-width="0.6" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                            </div>
                            <div class="ms-3 mb-3 mt-3">
                                <h6 class="ml-4">{{__('Total Users')}}</h6>
                            </div>
                        </div>

                        <div class="number-icon ms-3 mb-3 mt-3"><h3>{{$user->total_user}}</h3></div>
                     
                            <div class="ms-3 mb-3 mt-3">
                                <h6>{{__('Paid Users')}} : {{$user['total_paid_user']}}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center mb-3 mt-3">
                            <div class="account-dashboard-svg">
                            <svg fill="#100773" xmlns="http://www.w3.org/2000/svg" width="64px" height="64px" viewBox="-50 -50 200.00 200.00" xml:space="preserve" stroke="#100773" stroke-width="0.001"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g> <g> <path d="M78.8,62.1l-3.6-1.7c-0.5-0.3-1.2-0.3-1.7,0L52,70.6c-1.2,0.6-2.7,0.6-3.9,0L26.5,60.4 c-0.5-0.3-1.2-0.3-1.7,0l-3.6,1.7c-1.6,0.8-1.6,2.9,0,3.7L48,78.5c1.2,0.6,2.7,0.6,3.9,0l26.8-12.7C80.4,65,80.4,62.8,78.8,62.1z"></path> </g> <g> <path d="M78.8,48.1l-3.7-1.7c-0.5-0.3-1.2-0.3-1.7,0L52,56.6c-1.2,0.6-2.7,0.6-3.9,0L26.6,46.4 c-0.5-0.3-1.2-0.3-1.7,0l-3.7,1.7c-1.6,0.8-1.6,2.9,0,3.7L48,64.6c1.2,0.6,2.7,0.6,3.9,0l26.8-12.7C80.4,51.1,80.4,48.9,78.8,48.1 z"></path> </g> <g> <path d="M21.2,37.8l26.8,12.7c1.2,0.6,2.7,0.6,3.9,0l26.8-12.7c1.6-0.8,1.6-2.9,0-3.7L51.9,21.4 c-1.2-0.6-2.7-0.6-3.9,0L21.2,34.2C19.6,34.9,19.6,37.1,21.2,37.8z"></path> </g> </g> </g></svg>
                            </div>
                            <div class="ms-3 mb-3 mt-3">
                                <h6 class="ml-4">{{__('Total Orders')}}</h6>
                            </div>
                        </div>

                        <div class="number-icon ms-3 mb-3 mt-3"><h3>{{$user->total_orders}}</h3></div>
                       
                            <div class="ms-3 mb-3 mt-3">
                                <h6>{{__('Total Order Amount')}} : <span class="text-dark">{{env('CURRENCY_SYMBOL')}}{{$user['total_orders_price']}}</span></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center mb-3 mt-3">
                            <div class="account-dashboard-svg">
                            <svg fill="#100773" width="64px" height="64px" viewBox="-12 -12 48.00 48.00" xmlns="http://www.w3.org/2000/svg" stroke="#100773" stroke-width="0.00024000000000000003"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <title></title> <g id="signature"> <path d="M21.41,4.5a1.91,1.91,0,0,0-3.26-1.35L15.5,5.79,11.85,2.15A.48.48,0,0,0,11.63,2l-.07,0H3.5A1.5,1.5,0,0,0,2,3.5v17A1.5,1.5,0,0,0,3.5,22h11A1.5,1.5,0,0,0,16,20.5V10.71l2.94-2.94a1.63,1.63,0,0,1-.29,1.88l-.5.5a.48.48,0,0,0,0,.7.48.48,0,0,0,.7,0l.5-.5A2.61,2.61,0,0,0,19.67,7l1.18-1.19A1.9,1.9,0,0,0,21.41,4.5Zm-6.26,5.65h0L10.5,14.79,9.21,13.5,17,5.71,18.29,7ZM8.23,17.06l-1.94.65.65-1.94L8.5,14.21,9.79,15.5ZM12,3.71,14.29,6H12ZM15,20.5a.5.5,0,0,1-.5.5H3.5a.5.5,0,0,1-.5-.5V3.5A.5.5,0,0,1,3.5,3H11V6.5a.5.5,0,0,0,.5.5h2.79L6.15,15.15a.39.39,0,0,0-.12.19l-1,3a.48.48,0,0,0,.12.51A.47.47,0,0,0,5.5,19a.45.45,0,0,0,.16,0l3-1a.39.39,0,0,0,.19-.12L15,11.71ZM20.15,5.15,19,6.29,17.71,5l1.14-1.15a.92.92,0,0,1,1.3,1.3Z"></path> <path d="M5.5,6h3a.5.5,0,0,0,0-1h-3a.5.5,0,0,0,0,1Z"></path> <path d="M5.5,9h4a.5.5,0,0,0,0-1h-4a.5.5,0,0,0,0,1Z"></path> <path d="M5.5,12h1a.5.5,0,0,0,0-1h-1a.5.5,0,0,0,0,1Z"></path> </g> </g></svg>
                            </div>
                            <div class="ms-3 mb-3 mt-3">
                                <h6 class="ml-4">{{__('Total Plans')}}</h6>
                            </div>
                        </div>

                        <div class="number-icon ms-3 mb-3 mt-3"><h3>{{$user->total_plan}}</h3></div>
                            <div class="ms-3 mb-3 mt-3">
                                <h6>{{__('Most Purchase Plan')}} : <span class="text-dark">{{$user['most_purchese_plan']}}</span></h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-12">
            <h4 class="h4 font-weight-400">{{__('Recent Order')}}</h4>
            <div class="card">
                <div class="chart">
                    <div id="chart-sales" data-color="primary" data-height="280" class="p-3"></div>
                </div>
            </div>
        </div>
    </div>


@endsection
