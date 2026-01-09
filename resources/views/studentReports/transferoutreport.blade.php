<style>
    table,
    tr,
    th,
    td {
        border: 1px solid black;
        border-collapse: collapse;
    }
</style>
<div class="card mt-2 p-4">
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
            <tr class="table_heads">
                <th colspan="6"></th>
                <th colspan="2">{{ __('Transfer From') }}</th>
                <th colspan="2">{{ __('Transfer To') }}</th>
                <th></th>
                <th></th>
            </tr>

            <tr style="background-color: grey; font-size: 0.9rem;">
                <th>{{ __('Type') }}</th>
                <th>{{ __('Transfer Date') }}</th>
                <th>{{ __('Reg No') }}</th>
                <th>{{ __('Roll No') }}</th>
                <th>{{ __('Student Name') }}</th>
                <th>{{ __('Father Name') }}</th>
                <th>{{ __('Branch') }}</th>
                <th>{{ __('Class') }}</th>
                <th>{{ __('Branch') }}</th>
                <th>{{ __('Class') }}</th>
                <th>{{ __('Amount') }}</th>
                <th>{{ __('Reason') }}</th>
            </tr>
            @foreach ($studenttransfer as $transfer)
                <tr>
                    <td>{{ !empty($transfer->transfer_type) ? $transfer->transfer_type : '-' }}</td>
                    <td>{{ !empty($transfer->transfer_date) ? @$transfer->transfer_date : '-' }}</td>
                    <td>{{ !empty($transfer->enrollment) ? @$transfer->enrollment->regId : '-' }}</td>
                    <td>{{ !empty($transfer->enrollment) ? @$transfer->enrollment->enrollId : '-' }}</td>

                    <td>{{ !empty($transfer->student) ? @$transfer->student->stdname : '-' }}</td>
                    <td>{{ !empty($transfer->student) ? @$transfer->student->fathername : '-' }}</td>
                    <td>{{ !empty($transfer->branchfrom) ? @$transfer->branchfrom->name : '-' }}</td>
                    <td>{{ !empty($transfer->classfrom) ? @$transfer->classfrom->name : '-' }}</td>
                    <td>{{ !empty($transfer->branchto) ? @$transfer->branchto->name : '-' }}</td>
                    <td>{{ !empty($transfer->classto) ? @$transfer->classto->name : '-' }}</td>
                    <td>0</td>
                    <td>{{ !empty($transfer->status) ? $transfer->status : '-' }}</td>
                </tr>
            @endforeach
        </table>
    </div>
