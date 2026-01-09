<!DOCTYPE html>
<html>

<head>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <table class="datatable">

        <thead>
            <tr>
                <th colspan="3" style="text-align: center;">Product Stock Report</th>
            </tr>
            <tr>
                <th>Product Name</th>
                <th>Product Code</th>
                <th>Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($warehouse as $warehouses)
                <tr class="font-style">
                    @if (!empty($warehouses->product()))
                        <td>{{ !empty($warehouses->product()) ? $warehouses->product()->name : '' }}</td>
                        <td>{{ !empty($warehouses->product()) ? $warehouses->product()->sku : '' }}</td>
                        <td>{{ $warehouses->quantity }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>