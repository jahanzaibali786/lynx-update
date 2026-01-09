{{-- @extends('layouts.admin') --}}
@php
    $settings = Utility::settings();
    $color = !empty($setting['color']) ? $setting['color'] : 'theme-3';
@endphp
<html lang="en" dir="{{ $settings == 'on' ? 'rtl' : '' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('assets/css/plugins/main.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/plugins/style.css') }}">

    <!-- font css -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/tabler-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/material.css') }}">

    <!-- vendor css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}" id="main-style-link">

    <link rel="stylesheet" href="{{ asset('assets/css/customizer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

    <title>{{ env('APP_NAME') }} - Receivable Report</title>
    @if (isset($settings['SITE_RTL']) && $settings['SITE_RTL'] == 'on')
        <link rel="stylesheet" href="{{ asset('assets/css/style-rtl.css') }}" id="main-style-link">
    @endif


</head>

<script src="{{ asset('js/jquery.min.js') }}"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>

<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
    var filename = $('#filename').val();

    function saveAsPDF() {
        var element = document.getElementById('printableArea');
        var opt = {
            margin: 0.3,
            filename: filename,
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 4,
                dpi: 72,
                letterRendering: true
            },
            jsPDF: {
                unit: 'in',
                format: 'A2'
            }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $("#filter").click(function() {
            $("#show_filter").toggle();
        });
    });
</script>

<script>
    window.print();
    window.onafterprint = back;

    function back() {
        window.close();
        window.history.back();
    }
</script>

<body class="{{ $color }}">
    <div class="mt-4">
        @php
            $authUser = \Auth::user()->creatorId();
            $user = App\Models\User::find($authUser);
        @endphp

<div class="row">
    <div class="col-12" id="invoice-container">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="tab-content" id="myTabContent2">
                            <div class="tab-pane fade fade show active" id="item" role="tabpanel"
                                aria-labelledby="profile-tab3">
                                <table class="table table-flush" id="report-dataTable">
                                    <thead>
                                        <tr>
                                            <th width="33%"> {{ __('Customer Name') }}</th>
                                            <th width="33%"> {{ __('Invoice Balance') }}</th>
                                            <th width="33%"> {{ __('Available Credits') }}</th>
                                            <th class="text-end"> {{ __('Balance') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $total = 0;
                                        @endphp
                                        @foreach ($invoiceCustomers as $invoiceCustomer)
                                            <tr>
                                                @php
                                                    $invoiceBalance = $invoiceCustomer['total_price'] - $invoiceCustomer['pay_price'];
                                                    $balance = $invoiceBalance - $invoiceCustomer['credit_price'];
                                                    $total += $balance;
                                                @endphp
                                                <td> {{ $invoiceCustomer['name'] }}</td>
                                                <td> {{ \Auth::user()->priceFormat($invoiceBalance) }} </td>
                                                <td> {{ !empty($invoiceCustomer['credit_price']) ? \Auth::user()->priceFormat($invoiceCustomer['credit_price']) : \Auth::user()->priceFormat(0) }}
                                                </td>
                                                <td> {{ \Auth::user()->priceFormat($balance) }} </td>
                                            </tr>
                                        @endforeach
                                        @if ($invoiceCustomers != [])
                                            <tr>
                                                <th>{{ __('Total') }}</th>
                                                <td></td>
                                                <td></td>
                                                <th>{{ \Auth::user()->priceFormat($total) }}</th>
                                            </tr>
                                        @endif
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
    </div>
</body>
</html>
