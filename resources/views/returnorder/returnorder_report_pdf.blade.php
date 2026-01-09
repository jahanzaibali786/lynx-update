<div class="content" id="report-content">
    <div class="card ">

        <!-- Table CSS Styling -->
        <style>
            table, tr, th, td {
                border: 1px solid black;
                border-collapse: collapse;
                padding: 0% !imprtant;
                /* width: auto; Set to auto for dynamic width */
            }
            td{
                padding-left: 10px;
            }
            thead {
                background-color: grey;
                font-size: 0.9rem;
            }
            tbody tr {
                font-size: 0.6rem;
            }
            .sticky-header {
                /* position: sticky; */
                top: 0;
                background-color: white;
                z-index: 1;
            }
            .table-container {
                margin: 20px; /* Add margin around the table */
                overflow-x: auto; /* Allow horizontal scrolling if necessary */
            }
        </style>

        {{-- <div style="text-align: center;">
            <p style="font-size: 1rem; font-weight: 800;">Fee Receipt Summary</p>
        </div> --}}

        <div style="width: 100%; margin-top: 20px;">
            {{-- <div class="table-container" style="width: 100% !important; "> --}}
                <!-- Table Display -->
                {{-- <table style="width: 100% !important; font-size: 0.6rem; position:relative; left:-20px; top:30px;"> --}}
            <table style="width: 100%; font-size: 0.9rem;">
                <thead>
                    <tr style="background-color: grey; font-size: 0.9rem;">
                        <th style="width: 10%;">{{ __('Sr.') }}</th>
                        <th style="width: 20%;">{{ __('Return Order') }}</th>
                        <th style="width: 20%;">{{ __('Store From') }}</th>
                        <th style="width: 15%;">{{ __('Store To') }}</th>
                        <th style="width: 15%;">{{ __('Returnorder Date') }}</th>
                        <th style="width: 15%;">{{ __('Approve Amount') }}</th>
                        <th style="width: 10%;">{{ __('Pending Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $purchase)
                    {{-- @dd($purchase->purchase_id) --}}
                    <tr style="font-size: 0.7rem;">
                        <td >{{ $loop->iteration }}</td>
                        <td class="Id" style="width: 20%;">{{ Auth::user()->quotationNumberFormat($purchase->quotation_id) }}</td>
                        <td style="width: 20%;"> {{ !empty($purchase->customer) ? $purchase->customer->name : '' }}</td>
                        <td style="width: 15%;">{{ !empty($purchase->warehouse) ? $purchase->warehouse->name : '' }}</td>
                        <td style="width: 15%;">{{ Auth::user()->dateFormat($purchase->quotation_date) }}</td>
                        <td style="width: 15%;">{{ \Auth::user()->priceFormat($purchase->getapproveTotal()) }}</td>
                        <td style="width: 10%;"><span>{{ \Auth::user()->priceFormat($purchase->getpendingTotal()) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
