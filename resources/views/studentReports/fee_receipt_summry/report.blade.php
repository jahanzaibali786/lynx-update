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

        <div class="table-container" style="width: 100% !important; ">
            <!-- Table Display -->
            <table style="width: 100% !important; font-size: 0.6rem; position:relative; left:-20px; top:30px;">
                <thead class="sticky-header">
                    <tr style="background-color:grey;">
                        <th >{{ __('Date') }}</th>
                        @foreach ($branches as $key => $branch)
                        @if ($key !== "" && ($selectedBranch === null || $selectedBranch == $key))
                        <th style="width: 20%; font-size: 0.6rem;" colspan="2" class="text-center">{{ $branch }}</th>
                        @endif
                        @endforeach
                        <th style="width: 20%;" colspan="2" class="text-center">{{ __('Total') }}</th>
                    </tr>
                    <tr>
                        <th></th>
                        @foreach ($branches as $key => $branch)
                        @if ($key !== "" && ($selectedBranch === null || $selectedBranch == $key))
                        <th style="width: 10%;">{{ __('Receipts') }}</th>
                        <th style="width: 10%;">{{ __('Amount') }}</th>
                        @endif
                        @endforeach
                        <th>{{ __('Receipts') }}</th>
                        <th>{{ __('Amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $startDate = \Carbon\Carbon::parse($start_date);
                    $endDate = \Carbon\Carbon::parse($end_date);
                    @endphp

                    @for ($date = $startDate; $date <= $endDate; $date->addDay())
                        @php
                        $hasData = false;
                        $dateTotalReceipts = 0;
                        $dateTotalAmount = 0;
                        @endphp

                        @foreach ($branches as $key => $branch)
                        @if ($key !== "" && ($selectedBranch === null || $selectedBranch == $key))
                        @php
                        $branchReceipts = $recipts->filter(function ($receipt) use ($date, $key) {
                            return \Carbon\Carbon::parse($receipt->recipt_date)->isSameDay($date) && $receipt->owned_by == $key;
                        });
                        @endphp
                        @if ($branchReceipts->isNotEmpty())
                        @php
                        $hasData = true;
                        @endphp
                        @endif
                        @endif
                        @endforeach

                        @if ($hasData)
                        <tr>
                            <td class="text-center">{{ $date->format('d-M-Y') }}</td>
                            @foreach ($branches as $key => $branch)
                            @if ($key !== "" && ($selectedBranch === null || $selectedBranch == $key))
                            @php
                            $branchReceipts = $recipts->filter(function ($receipt) use ($date, $key) {
                                return \Carbon\Carbon::parse($receipt->recipt_date)->isSameDay($date) && $receipt->owned_by == $key;
                            });
                            $receiptCount = $branchReceipts->count();
                            $totalAmount = $branchReceipts->sum(function ($receipt) {
                                return $receipt->voucher->sum('credit');
                            });
                            $dateTotalReceipts += $receiptCount;
                            $dateTotalAmount += $totalAmount;
                            @endphp
                            <td">{{ $receiptCount }}</td>
                            <td class="text-center">{{ $totalAmount }}</td>
                            @endif
                            @endforeach
                            <td class="text-center">{{ $dateTotalReceipts }}</td>
                            <td class="text-center">{{ $dateTotalAmount }}</td>
                        </tr>
                        @endif
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>
