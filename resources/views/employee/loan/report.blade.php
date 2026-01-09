@if (!$loans->isEmpty())
    <table>
        <thead>
            @include('student.exports.header')
            <tr>
                <th>{{ __('Sr. No.') }}</th>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Loan Amount') }}</th>
                <th>{{ __('Deduction start Date') }}</th>
                <th>{{ __('End Date') }}</th>
                <th>{{ __('Received Amount') }}</th>
                <th>{{ __('charge amnt/mon') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($loans as $loan)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td style='width:100px;'>
                        <span>{{ !empty($loan->employee->name) ? $loan->employee->name : '' }}</span>
                    </td>
                    <td>{{ $loan->title }}</td>
                    <td>{{ @$loan->amount }}</td>
                    <td>{{ @$loan->from_pay_month }}</td>
                    <td>{{ @$loan->loan_ended }}</td>
                    <td>{{ @$loan->received_amount }}</td>
                    <td>{{ @$loan->per_month_amount }}</td>
                    <td>
                        @if ($loan->status == 0)
                            <span>{{ $loan->status == 0 ? 'Pending' : ($loan->status == 2 ? 'Rejected' : 'Approved') }}</span>
                        @else
                            <span>{{ $loan->status == 1 ? 'Approved' : ($loan->status == 2 ? 'Rejected' : 'Approved') }}</span>
                        @endif
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="mt-2 text-center">
        No Loan Data Found!
    </div>
@endif
@include('student.exports.footer')