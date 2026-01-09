@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th>{{ __('Sr No') }}</th>
            <th>{{ __('Invoice') }}</th>
            <th>{{ __('Store From') }}</th>
            <th>{{ __('Store To') }}</th>
            <th>{{ __('Issue Date') }}</th>
            <th>{{ __('Due Date') }}</th>
            <th>{{ __('Due Amount') }}</th>
            <th>{{ __('Total Amount') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['invoices'] as $invoice)
            {{-- @dd($invoice) --}}
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ Auth::user()->invoiceNumberFormat($invoice->invoice_id) }}</td>
                <td>{{ @$invoice->store_from->name }}</td>
                <td>{{ @$invoice->store_to->name }}</td>
                <td>{{ Auth::user()->dateFormat($invoice->issue_date) }}</td>
                <td>
                    @if ($invoice->due_date < date('Y-m-d'))
                        <p class="text-danger mt-3">{{ \Auth::user()->dateFormat($invoice->due_date) }}
                        </p>
                    @else
                        {{ \Auth::user()->dateFormat($invoice->due_date) }}
                    @endif
                </td>
                <td>{{ \Auth::user()->priceFormat($invoice->getDue()) }}</td>
                <td>{{ \Auth::user()->priceFormat($invoice->getTotal()) }}</td>
                <td>
                    <span>{{ __(\App\Models\Invoice::$statues[$invoice->status]) }}</span>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')