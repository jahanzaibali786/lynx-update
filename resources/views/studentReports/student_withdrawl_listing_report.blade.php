<style>
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
</style>
<div class="card p-4">
    <div class="mt-4" style="margin: 0 auto; padding: 30px;">
        <div style="width: 100%; position: relative; bottom: 1px; display: table; margin: 30px 0px 20px 0px;">
            <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
                <p style="margin: 0; text-align:left;"><b>Period From:
                    </b>{{ date('d M Y', strtotime($request->input('date_from'))) }}</p>
            </div>
            <div style="display: table-cell; width: 75%; text-align: center; vertical-align: middle;">
            </div>
            <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
                <p style="margin: 0; text-align:right;"><b>Period To:
                    </b>{{ date('d M Y', strtotime($request->input('date_to'))) }}</p>
            </div>
        </div>
    <table style="width: 100%; font-size: 0.9rem;">
        <tr style="background-color: grey; font-size: 0.9rem;">
            <th>Sr no.</th>
            <th>Roll No</th>
            <th>Student Name</th>
            <th>Class</th>
            <th>Adm Date</th>
            <th>Father Name</th>
            <th>Address</th>
            <th>Phone</th>
            <th>Withdrawal Date</th>
            <th>Net Payable / Receivable</th>
            <th>Reason for Leaving</th>
        </tr>
        <tbody>
            @foreach ($all_data as $data)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ !empty($data->student) ? $data->student->roll_no : '-' }}</td>
                    <td>{{ !empty($data->student) ? $data->student->stdname : '-' }}</td>
                    <td>{{ !empty($data->student->class) ? $data->student->class->name : '-' }}
                    </td>
                    <td>{{ !empty($data->student->enrollment) ? \Carbon\Carbon::parse($data->student->enrollment->adm_date)->format('d-M-Y') : '-' }}
                    </td>
                    <td>{{ !empty($data->student) ? $data->student->fathername : '-' }}
                    </td>
                    <td>{{ !empty($data->student) ? $data->student->address : '-' }}</td>
                    <td>{!! !empty($data->student) ? str_replace(',', '<br>', $data->student->fatherphone) : '-' !!}
                    </td>
                    <td style="width:50px;">
                        {{ !empty($data) ? $data->withdraw_date : '-' }}
                    <td>
                        @php
                            $arrears = 0;
                            $total = 0;
                            $challans = \App\Models\Challans::select(
                                \DB::raw('(total_amount - (paid_amount + concession_amount)) as total'),
                            )
                                ->where('student_id', @$data->student_id)
                                ->where('status', '!=', 'Paid')
                                ->get();
                            foreach ($challans as $challan) {
                                $arrears += $challan->total;
                            }
                        @endphp
                        {{ $arrears }}

                    </td>
                    <td>{{ !empty($data) ? $data->reason : '-' }}</td>
                </tr>
            @endforeach

        </tbody>

    </table>
</div>