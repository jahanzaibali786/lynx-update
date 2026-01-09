<table class="datatable">
    <thead>
        @include('student.exports.header')
        <tr>
            <td colspan="3"
                style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif; font-weight: bold">
                Student Name:
            </td>
            <td colspan="4" style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                {{ @$studypack->student->stdname ?? '' }}
            </td>
        </tr>
        <tr>
            <td colspan="3"
                style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif; font-weight: bold">
                Roll NO: 
            </td>
            <td colspan="4" style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                {{ @$studypack->student->roll_no ?? '' }}
            </td>
        </tr>
        <tr>
            <td colspan="3"
                style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif; font-weight: bold">
                Class: 
            </td>
            <td colspan="4" style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                {{ @$studypack->student->enrollment->class->name ?? '' }}
            </td>
        </tr>
        <tr>
            <td colspan="3"
                style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif; font-weight: bold">
                SP Challan No: 
            </td>
            <td colspan="4" style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                {{ @$studypack->challanNo ?? '' }}
            </td>
        </tr>
        <tr
            style="font-size: 2rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; background-color:gray; font-family:Arial,Helvetica,sans-serif; ">
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Sr No') }}</th>
            <th colspan="5"
                style="font-size: 8px; font-weight: bold; text-align:left; border: 2px solid black; border-collapse: collapse; width: 75px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Item') }}</th>
            <th
                style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 50px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
                {{ __('Qty') }}</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sr = 1;
            $totalAmount = 0;
            $totalItems = 0;
        @endphp
        @forelse (@$studypack->getItems as $item)
            <tr>
                <td style="text-align: center; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                    {{ $sr++ }}
                </td>
                <td colspan="5" style="text-align: left; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                    {{ $item->product->name }}
                </td>
                <td style="text-align: center; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">
                    {{ $item->qty }}
                </td>
            </tr>
            @php
                $totalAmount += $item->qty * $item->price;
                $totalItems += $item->qty;
            @endphp
        @empty
            <tr>
                <td colspan="7"
                    style="text-align: center; font-size: 8px; font-family: Arial, Helvetica, sans-serif;">No Data Found
                </td>
            </tr>
        @endforelse
        <tr>
            <td colspan="6"
                style=" border: 2px solid black;  text-align: right; font-size: 8px; font-family: Arial, Helvetica, sans-serif; background-color: gray;font-weight: bolder;">
                Total Items:</td>
            <td colspan="1"
                style=" border: 2px solid black; text-align: center; font-size: 8px; font-family: Arial, Helvetica, sans-serif; background-color: gray;font-weight: bolder;">
                {{ $totalItems }}</td>
        </tr>
        <tr>
            <td colspan="6"
                style=" border: 2px solid black;  text-align: right; font-size: 8px; font-family: Arial, Helvetica, sans-serif; background-color: gray; font-weight: bolder;">
                Challan Amount:</td>
            <td colspan="1"
                style=" border: 2px solid black; text-align: center; font-size: 8px; font-family: Arial, Helvetica, sans-serif; background-color: gray; font-weight: bolder;">
                {{ $totalAmount }}</td>
        </tr>
    </tbody>
    @include('student.exports.footer')
</table>
