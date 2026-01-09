<div class="content" id="report-content">
    <div class="card p-4">
        {{-- <p style="text-align: center; font-weight: 600; font-size: 1rem;">
            {{ @$brnches_name->name ?? 'All Branches' }}
        </p> --}}

        <!-- Date and Report Title Section in One Row -->
        {{-- <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
            <!-- Start Date -->
            <span style="font-size: 1rem; font-weight: 600;">
                Start Date: {{ date('d M Y', strtotime($request->input('start_date'))) }}
            </span>

            <!-- Report Title in the Center -->
            <p style="text-align: center; font-weight: 900; font-size: 1rem; margin: 0; flex-grow: 1;">
                Invoice Report
            </p>

            <!-- End Date -->
            <span style="font-size: 1rem; font-weight: 600;">
                End Date: {{ date('d M Y', strtotime($request->input('end_date'))) }}
            </span>
        </div> --}}

        <!-- Table CSS Styling -->
        <style>
            table, tr, th, td {
                border: 1px solid black;
                border-collapse: collapse;
                font-size: 0.7rem;
            }
        </style>

        @php
            $i = 1;
        @endphp

        <!-- Loop through Invoices Grouped by Store To -->
        @foreach($invoices as $storeToId => $storeInvoices)
        <div style="width: 100%; margin-top: 20px;">
            <!-- Display Store To Name -->
            <span style="font-size: 1rem; font-weight: 600; width: 100%;"><br>
                {{ $store_to[$storeToId] ?? 'Unknown Store' }}
            </span>
            <table style="width: 100%; font-size: 0.9rem;">
                <thead>
                    <tr style="background-color: grey; font-size: 0.9rem;">
                        <th style="width: 5%;">{{ __('Sr No') }}</th>
                        <th style="width: 20%;">{{ __('Invoice') }}</th>
                        <th style="width: 20%;">{{ __('Store From') }}</th>
                        <th style="width: 20%;">{{ __('Store To') }}</th>
                        <th style="width: 10%;">{{ __('Issue Date') }}</th>
                        <th style="width: 10%;">{{ __('Due Date') }}</th>
                        <th style="width: 10%;">{{ __('Due Amount') }}</th>
                        <th style="width: 5%;">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($storeInvoices as $invoice)
                    <tr style="font-size: 0.7rem;">
                        <td style="width: 5%;">{{ $i }}</td>
                        <td style="width: 20%;">{{ Auth::user()->invoiceNumberFormat($invoice->invoice_id) }}</td>
                        <td style="width: 20%;">{{ @$invoice->store_from->name }}</td>
                        <td style="width: 20%;">{{ @$invoice->store_to->name }}</td>
                        <td style="width: 10%;">{{ Auth::user()->dateFormat($invoice->issue_date) }}</td>
                        <td style="width: 10%;">
                            @if ($invoice->due_date < date('Y-m-d')) 
                            <p class="text-danger mt-3">{{ \Auth::user()->dateFormat($invoice->due_date) }}</p>
                            @else
                            {{ \Auth::user()->dateFormat($invoice->due_date) }}
                            @endif
                        </td>
                        <td style="width: 10%;">{{ \Auth::user()->priceFormat($invoice->getDue()) }}</td>
                        <td style="width: 5%;">
                            <span>{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                        </td>
                    </tr>
                    @php
                    $i++;
                    @endphp
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
        <div style="margin-top: 40px; margin-bottom: 20px; text-align: center;">
            <div style="width: 30%; display: inline-block; text-align: center; vertical-align: top; margin: 0 1%;">
                <div style="border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 5px; min-height: 20px;"></div>
                <p>{{ __('Received By') }}</p>
            </div>
            <div style="width: 30%; display: inline-block; text-align: center; vertical-align: top; margin: 0 1%;">
                <div style="border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 5px; min-height: 20px;"></div>
                <p>{{ __('Authorized By') }}</p>
            </div>
            <div style="width: 30%; display: inline-block; text-align: center; vertical-align: top; margin: 0 1%;">
                <div style="border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 5px; min-height: 20px;"></div>
                <p>{{ __('Approved By') }}</p>
            </div>
        </div>
        
    </div>
</div>
