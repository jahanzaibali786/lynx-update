<style>
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
</style>

@php
    $i = 1;
@endphp

{{-- @if ($class_wise_fee && count($class_wise_fee) > 0) --}}
@php $i = 1; @endphp
@foreach ($class_wise_fee as $branchId => $students)
    <div style="width: 100%; margin-top: 20px;">
        <span style="font-size:1rem; font-weight:600; padding:10px;">
            {{ $branches[$branchId] ?? 'All Branches' }} 
        </span>
        {{-- @dd($students) --}}
        <table style="width:100%; font-size:0.9rem;">
            <thead>
                <tr style="background-color:grey; font-size:0.6rem;">
                    <th style="width:5%;">{{ __('Sr No') }}</th>
                    <th style="width:10%;">{{ __('Class') }}</th>
                    @foreach ($heads as $head)
                        <th style="width:10%;">{{ $head->fee_head }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($students as $session => $value)
                <tr  style="font-size:0.8rem;">
                    <td colspan="{{count($heads) + 2}}" > <strong>{{ @$value[0]->session->year }}</strong> </td>
                    
                </tr>
                @foreach ($value->groupBy('class.name') as $className => $groupedStudents)
                    <tr style="font-size:0.8rem;">
                        <td>{{ $i }}</td>
                        <td>{{ $className }}</td>
                        @foreach ($heads as $head)
                            @php
                                $amount = optional($groupedStudents->where('feehead.fee_head', $head->fee_head)->first())->amount ?? '0';
                            @endphp
                            <td>{{ $amount }}</td>
                        @endforeach
                    </tr>
                    @php $i++; @endphp
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
@endforeach
{{-- @endif --}}