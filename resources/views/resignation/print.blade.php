<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <style>
        body {
            font-size: 0.8rem;
        }

        table {
            width: 100%;
            margin-top: 50px;
        }

        table,
        thead,
        tbody,
        tr,
        td,
        th {
            border: 1px solid black;
            border-collapse: collapse;
            text-align:center;
        }

        th {
            background-color: gray;
        }
    </style>
</head>

<body>
    <h2 style="text-align: center;">{{ 'Resignations Report' }}</h2>
    <table class="datatable">
        <thead>
            <tr>
                @role('company')
                    <th>{{ __('Employee Name') }}</th>
                @endrole
                <th>{{ __('Branch') }}</th>
                <th>{{ __('Resignation Date') }}</th>
                <th>{{ __('Last Working Date') }}</th>
                <th>{{ __('Reason') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Description') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($resignations as $resignation)
                <tr>
                    @role('company')
                        <td>{{ $resignation->employee->name ?? '' }}</td>
                    @endrole
                    <td>{{ \Auth::user()->getBranch($resignation->branch_id)->name ?? '' }}</td>
                    <td>{{ \Auth::user()->dateFormat($resignation->notice_date) }}</td>
                    <td>{{ \Auth::user()->dateFormat($resignation->resignation_date) }}</td>
                    <td>{{ $resignation->description }}</td>
                    <td>
                        @if ($resignation->status == 0)
                            <span class="">{{ __('Pending') }}</span>
                        @elseif($resignation->status == 1)
                            <span class="">{{ __('Approved') }}</span>
                        @else
                            <span class="">{{ __('Rejected') }}</span>
                        @endif
                    </td>
                    <td>
                        @if ($resignation->status == 1 || $resignation->status == 2)
                            {{ $resignation->appr_rej_desc }}
                        @else
                            {{-- Leave blank if not approved/rejected --}}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
