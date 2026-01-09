@include('student.exports.header')

<table>
    <thead>
        <tr>
            <th>
                Class</th>
            @foreach ($heads as $head)
                <th>
                    {{ $head->fee_head }}</th>
            @endforeach
            <th>
                Total</th>
        </tr>
    </thead>
    <tbody> 
        @foreach ($classes as $class)
            <tr>
                <td>
                    {{ $class->name }}</td>
                @php $total = 0; @endphp
                @foreach ($heads as $head)
                    @php
                        $amount = optional($class->classhead->firstWhere('head_id', $head->id))->amount;
                        $total += $amount ?? 0;
                    @endphp
                    <td>
                        {{ $amount ? number_format($amount) : '' }}
                    </td>
                @endforeach
                <td>
                    {{ $total ? number_format($total) : '' }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

@include('student.exports.footer')