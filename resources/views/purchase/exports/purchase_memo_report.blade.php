@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th> {{ __('Purchase No.') }}</th>
            <th> {{ __('Vendor') }}</th>
            <th> {{ __('Category') }}</th>
            <th> {{ __('Purchase Date') }}</th>
            <th> {{ __('Total Amount') }}</th>
            <th> {{ __('Due Amount') }}</th>
            <th>{{ __('Status') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['purchases'] as $purchase)
            <tr>
                <td>
                    {{ Auth::user()->purchaseNumberFormat($purchase->purchase_id) }}
                </td>
                <td> {{ !empty($purchase->vender) ? $purchase->vender->display_name : '' }} </td>
                <td>{{ !empty($purchase->category) ? $purchase->category->name : '' }}</td>
                <td>{{ Auth::user()->dateFormat($purchase->purchase_date) }}</td>
                <td>{{ \Auth::user()->priceFormat($purchase->getTotal()) }}</td>
                <td>{{ \Auth::user()->priceFormat($purchase->getDue()) }}</td>
                <td>
                    {{ __(\App\Models\Purchase::$statues[$purchase->status]) }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')
