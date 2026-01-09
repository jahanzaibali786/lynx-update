<table class="datatable">
    <thead>
        @include('student.exports.header')
        <tr style="font-weight: 800; border: 2px solid black; background-color: gray;">
            <th
                style="width: 50px; background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;
">
                {{ __('Sr No') }}</th>
            <th
              colspan="3"   style="width: 75px;background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;
">
                {{ __('Vendor') }}</th>
            <th
                style="width: 75px;background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;
">
                {{ __('Total Purchase') }}</th>
            <th
                style="width: 75px;background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;
">
                {{ __('Total Due') }}</th>
            <th
                style="width: 75px;background-color: gray; font-weight: bold; text-align: center; border: 2px solid black; border-collapse: collapse;
">
                {{ __('Balance') }}</th>


        </tr>
    </thead>
    <tbody>
        @php
            $sr = 1;
        @endphp
        @foreach ($data['vendorSummary'] as $summary)
            <tr>
                <td style="font-size: 8px; font-family: Arial, Helvetica, sans-serif; text-align: center;">{{ $sr++ }}</td>
                <td colspan="3" style="font-size: 8px; font-family: Arial, Helvetica, sans-serif; text-align: left;">{{ $summary['vendor_name'] }}</td>
                <td style="font-size: 8px; font-family: Arial, Helvetica, sans-serif; text-align: right;">{{ number_format($summary['total_purchase'], 2) }}</td>
                <td style="font-size: 8px; font-family: Arial, Helvetica, sans-serif; text-align: right;">{{ number_format($summary['total_due'], 2) }}</td>
                <td style="font-size: 8px; font-family: Arial, Helvetica, sans-serif; text-align: right;">{{ number_format($summary['total_balance'], 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')
