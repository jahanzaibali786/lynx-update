<style>
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
        font-size: 0.7rem;
    }
    td {
        padding-left: 3px;
    }
</style>

<div class="">
    <div>
        {{-- <div style="width: 100%; text-align: center;">
            <p style="font-family: Edwardian Script ITC; font-size: 3rem; text-align: center;"><b>The Lynx School
                     </b></p>
            <h4><b>{{@$branches[request('branch')]}}</b></h4>
            <h2><b>Student Security Report</b></h2>
        </div> --}}
    </div>
    <table style="width:100%; font-size:0.9rem; border: 1px solid black; border-collapse: collapse;">
        <thead>
            <tr style="background-color:grey; font-size:0.9rem; border: 1px solid black;">
                <th style="width:5%; border: 1px solid black;">{{ __('Sr No.') }}</th>
                <th style="width:5%; border: 1px solid black;">{{ __('Name') }}</th>
                <th style="width:5%; border: 1px solid black;">{{ __('Father Name') }}</th>
                <th style="width:5%; border: 1px solid black;">{{ __('Roll No #') }}</th>
                <th style="width:5%; border: 1px solid black;">{{ __('Class') }}</th>
                <th style="width:5%; border: 1px solid black;">{{ __('Admission Date') }}</th>
                <th style="width:5%; border: 1px solid black;">{{ __('WithDrawal Date') }}</th>
                <th style="width:5%; border: 1px solid black;">{{ __('Security deposit') }}</th>
                <th style="width:5%; border: 1px solid black;">{{ __('Security adjusted') }}</th>
                <th style="width:5%; border: 1px solid black;">{{ __('Balance') }}</th>
                <th style="width:5%; border: 1px solid black;">{{ __('Security Paid') }}</th>
            </tr>
        </thead>
        <tbody>
           @foreach ($records as $data)
                        @php
                            $stats = $journals->get($data->challan_id, ['deposit' => 0, 'paid' => 0]);
                            $securityDeposit = $stats['deposit'];
                            $securityPaid = $stats['paid'];
                            $balance = $securityDeposit - $securityPaid;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ @@$data->challan->student->stdname ?? '-' }}</td>
                            <td>{{ @$data->challan->student->fathername }}</td>
                            <td>{{ @$data->challan->enrollstudent->enrollId}}</td>
                            <td>{{ @$data->challan->student->class->name }}</td>
                            <td>{{ \Carbon\Carbon::parse(@$data->challan->enrollstudent->created_at)->format('d-M-Y') }}</td>
                            <td>{{\Carbon\Carbon::parse(@$data->challan->student->withdrawal->withdraw_date)->format('d-M-Y')}}</td>
                            <td>{{ number_format($securityDeposit, 2) }}</td>
                             <td></td>
                            <td>{{ number_format($balance, 2) }}</td>
                            <td>{{ number_format($securityPaid, 2) }}</td>
                        </tr>
                    @endforeach
        </tbody>
    </table>
</div>