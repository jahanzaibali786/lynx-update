<style>
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
</style>

@php
    $i = 1; // Counter for serial numbers
@endphp

    <div style="width: 100%; margin-top: 20px;">
        <p style="text-align: center; font-weight: 600; font-size: 1rem;">
            {{ request()->get('branches') ? $branches[request()->get('branches')] : 'All Branches' }}
        </p> 
        <span style="font-size: 1rem; font-weight: 600; padding: 10px;">
        </span>

        <table style="width: 100%; font-size: 0.9rem;">
            <thead>
                <tr style="background-color: grey; font-size: 0.6rem;">
                    <th style="width: 10%;">{{ __('Class') }}</th>
                    @foreach ($heads as $head)
                        <th style="width: 10%;">{{ $head->fee_head }}</th>
                    @endforeach
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($classes as $class)
                    <tr style="font-size: 0.8rem;">
                        <td >
                            {{ $class->name ?? '-' }}
                        </td>
                        @php $total = 0; @endphp
                        @foreach ($heads as $head)
                        <td>
                            @foreach ($class->classhead as $classhead)
                            @if ($classhead->head_id == $head->id)
                            {{ $classhead->amount }}
                            @php $total += $classhead->amount; @endphp
                            @break
                            @endif
                            @endforeach
                        </td>
                        @endforeach
                        <td>{{ $total }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
