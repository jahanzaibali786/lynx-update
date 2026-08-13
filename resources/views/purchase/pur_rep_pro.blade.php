@extends('layouts.admin')

@section('page-title')
    {{ __('Purchase Product Report') }}
@endsection

@push('script-page')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        function generatePDF() {
            const element = document.getElementById('report-content');
            const opt = {
                filename: 'Purchase_Product_Report.pdf',
                html2canvas: {
                    scale: 1
                },
                jsPDF: {
                    unit: 'pt',
                    format: 'a4',
                    orientation: 'landscape'
                }
            };
            html2pdf().from(element).set(opt).save();
        }

        function printPDF() {
            const element = document.getElementById('report-content');
            const opt = {
                filename: 'Purchase_Product_Report.pdf',
                html2canvas: {
                    scale: 1
                },
                jsPDF: {
                    unit: 'pt',
                    format: 'a4',
                    orientation: 'landscape'
                }
            };
            html2pdf().from(element).set(opt).output('bloburl').then(function(pdf) {
                window.open(pdf);
            });
        }
    </script>
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


        //     function printReport() {
        //         // alert('student')
        //     let form = document.getElementById('customer_submit');
        //     let formData = new FormData(form);
        //     let queryString = new URLSearchParams(formData).toString();
        //     window.location.href = "{{ route('purch_product.report') }}?" + queryString;
        // }
        function printReport() {
            var form = document.getElementById('customer_submit');
            var formData = new FormData(form);
            var queryString = new URLSearchParams(formData).toString();

            $.ajax({
                url: "{{ route('purch_product.report') }}?" + queryString,
                method: 'GET',
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

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Purchase Product Report') }}</li>
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
                        {{ Form::open(['route' => ['purchaseproduct.report'], 'method' => 'GET', 'id' => 'customer_submit']) }}
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
                            @if (\Auth::user()->type == 'company')
                                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                    <div class="btn-box">
                                        {{ Form::label('vender', __('Vender'), ['class' => 'form-label']) }}
                                        {{ Form::select('vender', $vender, isset($_GET['vender']) ? $_GET['vender'] : '', ['class' => 'form-control select', 'onchange' => 'venderwise(this.value)']) }}
                                    </div>
                                </div>
                            @endif
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12 mr-2">
                                <div class="btn-box">
                                    {{ Form::label('categories', __('Categories'), ['class' => 'form-label']) }}
                                    {{ Form::select('categories', $categories ?? [], isset($_GET['categories']) ? $_GET['categories'] : '', ['class' => 'form-control select', 'id' => 'categories']) }}
                                </div>
                            </div>
                            <div class="col-auto float-end ms-2 mt-4">
                                <a href="#" class="btn mx-1 btn-sm btn-outline-primary"
                                    onclick="document.getElementById('customer_submit').submit(); return false;"
                                     data-bs-title="{{ __('apply') }}">
                                    <span class="btn-inner--icon">Search</span>
                                </a>
                                <a href="{{ route('purchaseproduct.report') }}" class="btn mx-1 btn-sm btn-outline-danger"
                                     data-bs-title="{{ __('Reset') }}">
                                    <span class="btn-inner--icon">Clear</span>
                                </a>
                                {{-- <a href="#" class="btn mx-1 btn-sm btn-outline-success"
                                    onclick="printPDF(); return false;" 
                                    data-bs-title="{{ __('Print Report') }}">
                                    <span class="btn-inner--icon">Print</span>
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
    <div class="content" id="report-content">
        <div class="card p-4 mt-3">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center; line-height:2.2rem;"><b>The
                    Lynx School <br><span style="font-family: arial; font-weight:600; font-size:1rem;"> Pvt
                        Limited</span> </b></p>
            {{-- <p style="text-align: center; font-weight: 600; font-size: 1rem;">PWD BRANCH ISLAMABAD</p> --}}
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 1rem;">
                    Start Date: {{ date('d M Y', strtotime($request->input('start_date'))) }}
                </span>
                <p style="text-align: center; font-weight: 900; font-size: 1rem; margin: 0;">Purchase Report</p>
                <span style="font-size: 1rem;">
                    End Date: {{ date('d M Y', strtotime($request->input('end_date'))) }}
                </span>
            </div>
            <table class="">
                <thead class="table_heads">
                    <tr>
                        <th>{{ __('Purchase Number') }}</th>
                        <th>{{ __('Vendor') }}</th>
                        <th>{{ __('Category') }}</th>
                        <th>{{ __('Product Code') }}</th>
                        <th>{{ __('Product Name') }}</th>
                        <th>{{ __('Quantity') }}</th>
                        <th>{{ __('Purchase Price') }}</th>
                        <th>{{ __('Tax') }}</th>
                        <th>{{ __('Discount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $overallPurchasePriceTotal = 0;
                        $overalltax = 0;
                        $overalldiscount = 0;
                    @endphp

                    @foreach ($purchases as $purchase)
                        @php
                            $purchasePurchasePriceTotal = 0;
                            $purchasetax = 0;
                            $purchasediscount = 0;
                        @endphp
                        <tr>
                            <td colspan="9">
                                <strong>{{ Auth::user()->purchaseNumberFormat($purchase->id) }}</strong>
                            </td>
                        </tr>
                        @foreach ($purchase->items as $item)
                            @php
                                $productName = @$item->products->name;
                                $productCode = @$item->products->sku;
                                $productCategory = @$item->category->name;
                                $itemQuantity = is_numeric(@$item->quantity) ? $item->quantity : 0;
                                $purchasePrice = is_numeric(@$item->price) ? $item->price : 0;
                                $itemTax = is_numeric(@$item->tax) ? $item->tax : 0;
                                $itemdiscount = is_numeric(@$item->discount) ? $item->discount : 0;

                                $itemPurchasePriceTotal = $itemQuantity * $purchasePrice;

                                $purchasePurchasePriceTotal = isset($purchasePurchasePriceTotal)
                                    ? $purchasePurchasePriceTotal + $itemPurchasePriceTotal
                                    : $itemPurchasePriceTotal;
                                $purchasetax = isset($purchasetax) ? $purchasetax + $itemTax : $itemTax;
                                $purchasediscount = isset($purchasediscount)
                                    ? $purchasediscount + $itemdiscount
                                    : $itemdiscount;
                            @endphp
                            <tr>
                                <td></td>
                                <td>{{ $purchase->vender->display_name }}</td>
                                <td>{{ $productCategory }}</td>
                                <td>{{ $productCode }}</td>
                                <td>{{ $productName }}</td>
                                <td>{{ $itemQuantity }}</td>
                                <td>{{ $purchasePrice }}</td>
                                <td>{{ $itemTax }}</td>
                                <td>{{ $itemdiscount }}</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="5" class="text-right"><strong>Purchase Totals:</strong></td>
                            <td><strong>{{ $purchasePurchasePriceTotal }}</strong></td>
                            <td><strong>{{ $purchasetax }}</strong></td>
                            <td><strong>{{ $purchasediscount }}</strong></td>
                            <td></td>
                        </tr>

                        @php
                            $overallPurchasePriceTotal += $purchasePurchasePriceTotal;
                            $overalltax += $purchasetax;
                            $overalldiscount += $purchasediscount;
                        @endphp
                    @endforeach
                    <tr>
                        <td colspan="5" class="text-right"><strong>Sub-Totals:</strong></td>
                        <td><strong>{{ $overallPurchasePriceTotal }}</strong></td>
                        <td><strong>{{ $overalltax }}</strong></td>
                        <td><strong>{{ $overalldiscount }}</strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
@endsection
