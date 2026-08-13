@include('student.exports.header')

<table>
    <thead>
        <tr>
            <th style="background: #d8d8d8; border: 1px solid black; font-weight: bold;">Sr.No</th>
            <th style="background: #d8d8d8; border: 1px solid black; font-weight: bold;">Item</th>
            <th style="background: #d8d8d8; border: 1px solid black; font-weight: bold;">Qty</th>
        </tr>
    </thead>
    <tbody>
        @php
            $globalSr = 1;
            $grandTotalQty = 0;
            $grandTotalItems = 0;
        @endphp

        @foreach ($studypacks as $studypack)
            @php
                $classIds = json_decode($studypack->class, true);
                if (!is_array($classIds)) {
                    $classIds = array_filter(array_map('trim', explode(',', (string) $studypack->class)));
                }

                if (empty($classIds)) {
                    continue;
                }

                $classNames = \App\Models\Classes::whereIn('id', $classIds)->pluck('name')->unique()->toArray();
                $items = $studypack->items ?? collect();
                if ($items->count() === 0) {
                    continue;
                }
            @endphp

            @foreach($classNames as $className)
                @php
                    $classTotalQty = 0;
                    $classItemCount = 0;
                @endphp

                <tr>
                    <td colspan="3" style="background: #eeeeee; font-weight: bold; font-size: 10px;">
                        {{ $className }}
                    </td>
                </tr>

                @foreach($items as $item)
                    @php
                        $qty = (int) ($item->quantity ?? 0);
                        $classTotalQty += $qty;
                        $classItemCount++;
                    @endphp
                    <tr>
                        <td style="border: 1px solid gray; text-align: center;">{{ $globalSr++ }}</td>
                        <td style="border: 1px solid gray;">{{ optional($item->product)->name ?? '' }}</td>
                        <td style="border: 1px solid gray; text-align: center;">{{ $qty }}</td>
                    </tr>
                @endforeach

                <tr style="background: #f8f6f6; color: #000; font-weight: bold;">
                    <td colspan="2" style="text-align: center; border: 1px solid gray; font-weight: bold;">Total Items</td>
                    <td style="text-align: center; border: 1px solid gray; font-weight: bold;">{{ $classItemCount }}</td>
                </tr>
                <tr style="background: #f8f6f6; color: #000; font-weight: bold;">
                    <td colspan="2" style="text-align: center; border: 1px solid gray; font-weight: bold;">Total Qty</td>
                    <td style="text-align: center; border: 1px solid gray; font-weight: bold;">{{ $classTotalQty }}</td>
                </tr>

                @php
                    $grandTotalQty += $classTotalQty;
                    $grandTotalItems += $classItemCount;
                @endphp
            @endforeach
        @endforeach

        <tr style="background: #d8d8d8; border: 1px solid black; font-weight: bold;">
            <td colspan="2" style="text-align: center; border: 1px solid black; font-weight: bold; background: #d8d8d8;">Grand Total Items</td>
            <td style="text-align: center; border: 1px solid black; font-weight: bold; background: #d8d8d8;">{{ $grandTotalItems }}</td>
        </tr>
        <tr style="background: #d8d8d8; border: 1px solid black; font-weight: bold;">
            <td colspan="2" style="text-align: center; border: 1px solid black; font-weight: bold; background: #d8d8d8;">Grand Total Qty</td>
            <td style="text-align: center; border: 1px solid black; font-weight: bold; background: #d8d8d8;">{{ $grandTotalQty }}</td>
        </tr>
    </tbody>
</table>

@include('student.exports.footer')
