@include('student.exports.header')
<table class="datatable" style="width:100%; border-collapse: collapse;">
    <thead>
        <style>
            tr th,
            tr td {
                font-size: 8px !important;
                font-family: Arial, Helvetica, sans-serif !important;
            }
        </style>
        <tr style="font-size: 2rem; font-weight: 800; border: 2px solid black; border-collapse: collapse; background-color:gray; font-family:Arial,Helvetica,sans-serif;">
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 40px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">{{ __('Sr No') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 40px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">{{ __('Br Sr#') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 60px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">{{ __('Session') }}</th>
            <th style="font-size: 8px; font-weight: bold; text-align:center; border: 2px solid black; border-collapse: collapse; width: 100px; background-color:gray; font-family:Arial,Helvetica,sans-serif;">{{ __('Class') }}</th>
            @foreach ($heads as $head)
            <th style="font-size: 8px; 
                      font-weight: bold; 
                      text-align: center; 
                      border: 2px solid black; 
                      border-collapse: collapse; 
                      width: 60px; /* Fixed width for all columns */
                      min-width: 60px; 
                      max-width: 60px;
                      background-color: gray; 
                      font-family: Arial, Helvetica, sans-serif;
                      word-wrap: break-word;
                      white-space: normal;">
                {{ $head->fee_head }}
            </th>
        @endforeach
        </tr>
    </thead>
    <tbody style="border: 2px solid #000000; border-collapse: collapse;">
        @php $sr = 1; @endphp
        @foreach ($class_wise_fee as $branchId => $sessions)
            <tr>
                <td colspan="{{ 4 + count($heads) }}" style="font-weight:bold; background:#f2f2f2; border: 2px solid black; text-align:left;">{{ $branches[$branchId] ?? 'All Branches' }}</td>
            </tr>
            @php $branch_sr = 1; @endphp
            @foreach ($sessions as $sessionId => $students)
                {{-- <tr>
                    <td colspan="{{ 4 + count($heads) }}" style="font-weight:bold; background:#e2e2e2; border: 2px solid black; text-align:left;">{{ optional($students->first()->session)->year ?? '' }}</td>
                </tr> --}}
                @php $class_sr = 1; @endphp
                @foreach ($students->groupBy('class.name') as $className => $groupedStudents)
                    <tr>
                        <td style="text-align:center; border: 2px solid #D3D3D3;">{{ $sr }}</td>
                        <td style="text-align:center; border: 2px solid #D3D3D3;">{{ $branch_sr }}</td>
                        <td style="text-align:center; border: 2px solid #D3D3D3;">{{ optional($groupedStudents->first()->session)->year ?? '' }}</td>
                        <td style="text-align:left; border: 2px solid #D3D3D3;">{{ $className }}</td>
                        @foreach ($heads as $head)
    @php
        $amount = optional($groupedStudents->where('feehead.fee_head', $head->fee_head)->first())->amount ?? '0';
    @endphp
    <td style="text-align: center; 
              border: 2px solid #D3D3D3;
              width: 60px; /* Match header width */
              min-width: 60px;
              max-width: 60px;
              word-wrap: break-word;
              white-space: normal;">
        {{ $amount }}
    </td>
@endforeach
                    </tr>
                    @php $sr++; $branch_sr++; $class_sr++; @endphp
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>
@include('student.exports.footer')
