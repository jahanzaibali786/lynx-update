<table class="datatable">
    <thead>
        <style>
            tr th,
            tr td {
                font-size: 8px !important;
                font-family: Arial, Helvetica, sans-serif !important;
            }
        </style>
        @php
            $branch = $params['branch'] ?? '';
            $report_name = $params['report_name'] ?? __('Student Receipt Report');
            $is_period = $params['is_period'] ?? false;
            $is_signature = $params['is_signature'] ?? false;
        @endphp
        @include('student.exports.header', compact('branch', 'report_name', 'is_period'))
        <tr style="font-size: 2rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; background-color:gray; font-family:Arial,Helvetica,sans-serif; ">
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Sr No.') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 80px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Rpt Date') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 80px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Challan No.') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 80px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Rpt Amt') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 80px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Challan Amt') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 80px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Late Amt') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 80px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Arrears') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 80px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Total Fee') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 80px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Rem. Fee') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 120px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Bank Account') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 60px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('D Status') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 80px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Reference') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 120px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Received By') }}</th>
        </tr>
    </thead>
    <tbody style="border: 2px solid #000000; border-collapse: collapse;">
        @php
            $totalReceipt = 0;
            $totalChallan = 0;
        @endphp
        @foreach ($receipts as $recipt)
            @php
                $totalReceipt += floatval(@$recipt->recipt_amount);
                $totalChallan += floatval(@$recipt->challan_amount);
            @endphp
            <tr>
                <td style="text-align: center; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">{{ $loop->iteration }}</td>
                <td style="text-align: center; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">{{ $recipt->recipt_date ? date('d/m/Y', strtotime($recipt->recipt_date)) : '' }}</td>
                <td style="text-align: center; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">{{ @$recipt->challan->challanNo }}</td>
                <td style="text-align: right; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">{{ @$recipt->recipt_amount }}</td>
                <td style="text-align: right; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">{{ @$recipt->challan_amount }}</td>
                <td style="text-align: right; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">{{ @$recipt->late_amount }}</td>
                <td style="text-align: right; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">{{ @$recipt->arrears }}</td>
                <td style="text-align: right; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">{{ @$recipt->challan_amount + @$recipt->late_amount + @$recipt->arrears }}</td>
                <td style="text-align: right; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">{{ @$recipt->challan_amount + @$recipt->late_amount + @$recipt->arrears - @$recipt->recipt_amount }}</td>
                <td style="text-align: left; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">{{ @$recipt->bank ? @$recipt->bank->bank_name . ' ' . @$recipt->bank->holder_name : '' }}</td>
                <td style="text-align: center; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">{{ @$recipt->receive_type }}</td>
                <td style="text-align: left; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">{{ @$recipt->referance }}</td>
                <td style="text-align: left; font-family: Arial, Helvetica, sans-serif; font-size: 8px;">{{ @$recipt->received->name }}</td>
            </tr>
        @endforeach
        <tr style="font-weight:bold; background: gray;">
            <td colspan="3"></td>
            <td style="text-align: right; background: gray;">{{ number_format($totalReceipt, 2) }}</td>
            <td style="text-align: right; background: gray;">{{ number_format($totalChallan, 2) }}</td>
            <td colspan="8"></td>
        </tr>
        @include('student.exports.footer', compact('is_signature'))
    </tbody>
</table> 