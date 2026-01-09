@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th>
                Sr#</th>
            <th>
                Beneficiary Name</th>
            @if (isset($requestdata) &&
                    (strtolower($requestdata['paymode']) != 'cheque' && strtolower($requestdata['paymode']) != 'cash'))
                <th>
                    Beneficiary Account Number</th>
            @elseif(isset($requestdata) && strtolower($requestdata['paymode']) == 'cheque')
                <th>
                    Cheque Number</th>
            @elseif(isset($requestdata) && strtolower($requestdata['paymode']) == 'cash')
                <th>
                    Cash</th>
            @endif

            <th>
                Transaction Amount</th>
            <th>
                Customer Reference Amount</th>
            <th>
                Email</th>
            <th>
                Contact Number</th>

        </tr>

    </thead>
    <tbody>
        @php
            $gross = 0;
            $tot_pay = 0;
        @endphp
        @foreach ($datas as $key => $data)
            @php
                $tot_pay += !empty($data->net_pay) ? $data->net_pay : '';
                $gross += $data->gross;
                $payscale = $data->employee->employee_payscale_details->last();

            @endphp
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>
                    {{ !empty($data->employee->name) ? @$data->employee->name : '' }}</td>
                @if (isset($requestdata) && strtolower($requestdata['paymode']) != 'cash')
                    <td>
                        {{ !empty($payscale->account_number) ? @$payscale->account_number : '' }}</td>
                @elseif(isset($requestdata) && strtolower($requestdata['paymode']) == 'cash')
                    <td>
                        cash</td>
                @endif
                <td>
                    {{ !empty($data->net_pay) ? $data->net_pay : '' }}</td>

                <td>
                    {{ !empty($data->salary_date) ? date('d-M', strtotime($data->salary_date)) : '' }} -
                    {{ !empty($data->id) ? $data->id : '' }}
                </td>
                <td>
                    {{ !empty($data->employee->email) ? @$data->employee->email : '' }}</td>
                <td>
                    {{ !empty($data->employee->phone) ? @$data->employee->phone : '' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')
