@extends('layouts.admin')

@section('page-title')
    {{ __('Purchase By Vendor Report') }}
@endsection

@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        function branchstore(id) {
            var branch = id;
            $.ajax({
                url: '{{ route('branch.store') }}',
                type: 'POST',
                data: {
                    "branch_id": branch,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {

                    $('#store').empty();
                    // $('#store').append('<option value="">{{ __('Select Store') }}</option>');

                    for (let index = 0; index < data.length; index++) {
                        $('#store').append('<option value="' + data[index]['id'] + '">' + data[index]['name'] +
                            '</option>');
                    }
                }
            });
        }

        document.getElementById('branchstore').addEventListener('change', function() {
            var id = this.value;
            branchstore(id);
        });

        function submitWithPrintFlag() {
            const form = document.getElementById('customer_submit');
            const input = document.getElementById('is_print');
            input.value = 1;
            form.target = '_blank';
            form.submit();
            form.target = '';
            resetPrintFlagAndSubmit();
        }

        function resetPrintFlagAndSubmit() {
            const form = document.getElementById('customer_submit');
            const input = document.getElementById('is_print');
            input.value = 0;

            form.submit();
        }
    </script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Purchase By Vendor Report') }}</li>
@endsection
{{-- @section('action-btn')
<div class="float-end">
    <a class="btn mx-1 btn-sm btn-outline-success" onclick="generatePDF()"><span
        class="btn-inner--icon">Print</span>
</a>     
</div>
@endsection --}}

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="mt-2 " id="multiCollapseExample1">
                <div class="card">
                    <div class="card-body">
                        {{ Form::open(['route' => ['purchaseproductbyvendor.report'], 'method' => 'GET', 'id' => 'customer_submit']) }}
                        <div class="row d-flex align-items-center justify-content-end">
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('start_date', __('Start Date'), ['class' => 'form-label']) }}

                                    {{ Form::date('start_date', isset($_GET['start_date']) ? $_GET['start_date'] : '', ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('end_date', __('End Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('end_date', isset($_GET['end_date']) ? $_GET['end_date'] : '', ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('vender', __('Vendors'), ['class' => 'form-label']) }}
                                    {{ Form::select('vender', $vender, isset($_GET['vender']) ? $_GET['vender'] : '', ['class' => 'form-control select', 'onchange' => 'branchstore(this.value)']) }}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('customer_submit').submit(); return false;"
                                     data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('invoice.index') }}" class="btn mx-1 btn-sm btn-outline-danger" 
                                    data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
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
    <div class="content" id="report-content">
        <div class="card p-4 mt-3">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center; line-height:2.2rem;"><b>The
                    Lynx School <br><span style="font-family: arial; font-weight:600; font-size:1rem;"> Pvt
                        Limited</span> </b></p>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1rem;">
                    Start Date: {{ date('d M Y', strtotime($request->input('start_date'))) }}
                </span>
                <p style="text-align: center; font-weight: 900; font-size: 1rem; margin: 0;">Purchases By Vendor Report</p>
                <span style="font-size: 1rem;">
                    End Date: {{ date('d M Y', strtotime($request->input('end_date'))) }}
                </span>
            </div>

            @php
                // Organize purchases by vendor
                $purchasesByVendor = [];
                foreach ($purchases as $purchase) {
                    $vendorId = $purchase->vender_id;
                    $vendorName = !empty($purchase->vender) ? $purchase->vender->display_name : 'Unknown Vendor';

                    if (!isset($purchasesByVendor[$vendorId])) {
                        $purchasesByVendor[$vendorId] = [
                            'name' => $vendorName,
                            'purchases' => [],
                        ];
                    }

                    $purchasesByVendor[$vendorId]['purchases'][] = $purchase;
                }
            @endphp
            @php
                $balance = 0;
                $totalcostAmount = 0;
                $totalAmount = 0;
            @endphp
            @foreach ($purchasesByVendor as $vendorId => $vendorData)
                <!-- Vendor Section Header -->
                <div class="vendor-section mt-4">
                    {{-- <h5 class="vendor-name" style="background-color: #f8f9fa; padding: 10px; border-left: 4px solid #4e73df; margin-bottom: 15px;">
                        <strong>{{ $vendorData['name'] }}</strong>
                    </h5> --}}

                    <!-- Purchases Table for this Vendor -->
                    <table class="">
                        <thead class="table_heads">
                            <tr>
                                <th> {{ __('Purchase Date') }}</th>
                                <th> {{ __('Purchase No.') }}</th>
                                <th> {{ __('Memo') }}</th>
                                <th> {{ __('Quantity') }}</th>
                                <th> {{ __('Cost Price') }}</th>
                                <th> {{ __('Amount') }}</th>
                                <th>{{ __('Balance') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style="background: #7a7979;">
                                <td colspan="7"><b>{{ $vendorData['name'] }}</b></td>
                            </tr>

                            @foreach ($vendorData['purchases'] as $purchase)
                                @foreach (@$purchase->items as $item)
                                    <tr>
                                        <td>{{ Auth::user()->dateFormat($purchase->purchase_date) }}</td>
                                        <td class="Id">
                                            {{ Auth::user()->purchaseNumberFormat($purchase->purchase_id) }}
                                        </td>
                                        <td>{{ !empty($item) ? $item->products->name : '' }}</td>
                                        <td>{{ \Auth::user()->priceFormat(@$item->products->quantity) }}</td>
                                        <td>{{ \Auth::user()->priceFormat(@$item->products->purchase_price) }}</td>
                                        <td>{{ \Auth::user()->priceFormat(@$item->products->purchase_price * @$item->products->quantity) }}
                                        </td>
                                        @php
                                            $balance += @$item->products->purchase_price * @$item->products->quantity;
                                            $totalcostAmount += @$item->products->purchase_price;
                                            $totalAmount += @$item->products->purchase_price * @$item->products->quantity;
                                        @endphp
                                        <td>
                                            {{ \Auth::user()->priceFormat($balance) }}
                                        </td>
                                    </tr>

                                @endforeach
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>{{ __('Total') }}:</strong></td>
                                <td></td>
                                <td>
                                    <strong>
                                        {{ \Auth::user()->priceFormat($totalcostAmount) }}
                                    </strong>
                                </td>
                                <td>
                                    <strong>
                                        {{ \Auth::user()->priceFormat($totalAmount) }}
                                    </strong>
                                </td>
                                <td>
                                    <strong>
                                        {{ \Auth::user()->priceFormat($balance) }}
                                    </strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
@endsection
