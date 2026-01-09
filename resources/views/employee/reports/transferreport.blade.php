<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title></title>
    <style>
        body{
            font-size: 0.8rem;
        }
        table{
            width: 100%;
            margin-top: 50px;
        }
        table,thead,tbody,tr,td,th{
            border: 1px solid black;
            border-collapse: collapse;
        }
        th{
            background-color: gray;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">{{ !empty($type) ? ($type == 'transfer_in' ? 'Transfer In' : 'Transfer Out') : 'Transfer Report' }}</h2>
    <table class="datatable">
        <thead>
            <tr>
                <th>sr.</th>
                <th>Emp.no</th>
                <th>Name</th>
                <th>Transfer Date</th>
                <th>Transfer Status</th>
                <th>From branch</th>
                <th>To branch</th>
                <th>From Department</th>
                <th>To Department</th>
                <th>Reason</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfers as $trans)
                <tr>
                    <td>{{$loop->iteration}}</td>
                    <td>{{@$trans->employee->id}}</td>
                    <td>{{@$trans->employee->name}}</td>
                    <td>{{@$trans->transfer_date}}</td>
                    <td>
                        @if ($trans->branch_to_id == $trans->branch_from_id) 
                            Transfer In
                        @else
                            Transfer Out
                        @endif
                    </td>
                    <td>{{@$trans->branch_from->name}}</td>
                    <td>{{@$trans->branch_to->name}}</td>
                    <td>{{@$trans->department_from->name}}</td>
                    <td>{{@$trans->department_to->name}}</td>
                    <td>{{@$trans->transfer_reason}}</td>
                    <td>{{@$trans->status==0 ? 'Pending' : 'Approved'}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>