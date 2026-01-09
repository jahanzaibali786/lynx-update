<style>
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
</style>
<div class="content" id="report-content">
    <div class="card p-4">
        <div style="width: 100%; position: relative; bottom: 1px; display: table; margin: 30px 0px 20px 0px;">
            <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
                <p style="margin: 0; text-align:left;"><b>Period From:
                    </b>{{ date('d M Y', strtotime($request->input('date_from'))) }}</p>
            </div>
            <div style="display: table-cell; width: 75%; text-align: center; vertical-align: middle;">
                {{-- <p style="text-align: center; font-weight: 600; font-size: 1rem;">
            {{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}
        </p>  --}}
            </div>
            <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
                <p style="margin: 0; text-align:right;"><b>Period To:
                    </b>{{ date('d M Y', strtotime($request->input('date_to'))) }}</p>
            </div>
        </div>
        {{-- <p style="text-align: center; font-weight: 600; font-size: 1rem;">
            {{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}
        </p>  --}}
        <table style="width: 100%; font-size: 0.9rem;">
            <tr style="background-color: grey; font-size: 0.6rem;">
                <th>id</th>
                <th>Admission Date</th>
                <th>Student</th>
                <th>Date of Birth</th>
                <th>Father Name</th>
                <th>Occupation</th>
                <th>Residence</th>
                <th>Phone No</th>
                <th>Admitted Class</th>
                <th>Arrears Dues</th>
                <th>Class From which withdrawal</th>
                <th>Withdrawal Date</th>
                <th>Remarks</th>
            </tr>
            <tbody>
                @foreach ($all_data as $data)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{ \Carbon\Carbon::parse(@$data->created_at)->format('d-M-Y') }}</td>
                                    <td>{{ (!empty($data->StudentRegistration)) ? $data->StudentRegistration->stdname : '-' }}</td>
                                    <td>{{ (!empty($data->StudentRegistration)) ? $data->StudentRegistration->dob : '-' }}</td>
                                    <td>{{ (!empty($data->StudentRegistration)) ? $data->StudentRegistration->fathername : '-' }}</td>
                                    <td>{{ (!empty($data->StudentRegistration)) ? $data->StudentRegistration->fatherprofession : '-' }}
                                    </td>
                                    <td>{{ (!empty($data->StudentRegistration)) ? $data->StudentRegistration->address : '-' }}</td>
                                    <td>{{ (!empty($data->StudentRegistration)) ? $data->StudentRegistration->fatherphone : '-' }}</td>
                                    <td>{{ (!empty($data->StudentRegistration->class)) ? $data->StudentRegistration->class->name : '-' }}
                                    </td>
                                    <td>
                                        @php
                                            $arrears = 0;
                                            $total = 0;
                                            $challans = \App\Models\Challans::select(
                                                \DB::raw('(total_amount - (paid_amount + concession_amount)) as total')
                                            )
                                                ->where('student_id', @$data->StudentRegistration->id)
                                                ->where('status', '!=', 'Paid')->get();
                                            foreach ($challans as $challan) {
                                                $arrears += $challan->total;
                                            }
                                        @endphp
                                        {{$arrears}}
                                    </td>
                                    <td>{{ (!empty($data->withdrawal->class)) ? $data->withdrawal->class->name : '-' }}</td>
                                    <td style="width:50px;">{{ (!empty($data->withdrawal)) ? $data->withdrawal->withdraw_date : '-' }}
                                    </td>
                                    <td>{{ (!empty($data->withdrawal)) ? $data->withdrawal->remark : '-' }}</td>
                                </tr>
                @endforeach

            </tbody>

        </table>
    </div>
</div>