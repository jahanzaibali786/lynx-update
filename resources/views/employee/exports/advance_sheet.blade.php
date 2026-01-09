@include('student.exports.header')
<table>
    <thead>
        <tr>
            <th>sr.#</th>
            <th>Name</th>
            <th>Designation</th>
            <th>Adv</th>
            <th>EOBI</th>
            <th>I.Tax</th>
            <th>Loan</th>
        </tr>
    </thead>
    @php
        $gross = 0;
        $tot_adv = 0;
        $tot_eobi = 0;
        $tot_tax = 0;
        $tot_loan = 0;
    @endphp
    <tbody>
        @foreach ($datas as $key => $data)
            @php
                $payscale = $data->employee->employee_payscale_details->last();
                $tot_adv += !empty($payscale->advance) ? @$payscale->advance : '0';
                $tot_eobi += !empty($data->eobi) ? @$data->eobi : '0';
                $tot_tax += !empty($data->it) ? @$data->it : '0';
                $tot_loan += !empty($data->loan) ? @$data->loan : '0';
            @endphp

            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ !empty($data->employee->name) ? @$data->employee->name : '' }}</td>
                <td>{{ !empty($data->employee->designation->name) ? @$data->employee->designation->name : '' }}</td>
                <td>{{ !empty($payscale->advance) ? @$payscale->advance : '0' }}</td>
                <td>{{ !empty($data->eobi) ? @$data->eobi : '0' }}</td>
                <td>{{ !empty($data->it) ? @$data->it : '0' }}</td>
                <td>{{ !empty($data->loan) ? @$data->loan : '0' }}</td>
            </tr>
        @endforeach
        <tr>
            <td style="border: 2px solid black; font-weight: bold; background-color:gray; border-collapse: collapse; text-align:center" colspan="3"> Total </td>
            <td style="border: 2px solid black; font-weight: bold; background-color:gray; border-collapse: collapse;">{{ @$tot_adv }}</td>
            <td style="border: 2px solid black; font-weight: bold; background-color:gray; border-collapse: collapse;">{{ @$tot_eobi }}</td>
            <td style="border: 2px solid black; font-weight: bold; background-color:gray; border-collapse: collapse;">{{ @$tot_tax }}</td>
            <td style="border: 2px solid black; font-weight: bold; background-color:gray; border-collapse: collapse;">{{ @$tot_loan }}</td>
        </tr>
    </tbody>
</table>
@include('student.exports.footer')