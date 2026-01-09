<style>
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
    }
</style>
<div class="card mt-2 p-4" style="width: 100%; max-width: 100%; overflow-x: auto;">
    <div class="mt-4" style="margin: 0 auto; padding: 30px;">
        <div style="width: 100%; position: relative; bottom: 1px; display: table; margin: 30px 0px 20px 0px;">
            <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
                <p style="margin: 0; text-align:left;"><b>Period From:
                    </b>{{ date('d M Y', strtotime($request->input('date'))) }}</p>
            </div>
            <div style="display: table-cell; width: 75%; text-align: center; vertical-align: middle;">
            </div>
            <div style="display: table-cell; width: 50%; text-align: center; vertical-align: middle;">
                <p style="margin: 0; text-align:right;">
                    {{-- <b>Period To:
                    </b>{{ date('d M Y', strtotime($request->input('date_to'))) }} --}}
                </p>
            </div>
        </div>
    <table  style="width: 100%; font-size: 0.9rem;">
        <thead class="table_heads report_table">
            <tr style="background-color: grey; font-size: 0.9rem;">
                <th>Branch</th>
                <th>Class</th>
                <th>Section</th>
                <th>Student</th>
                <th>Section Change Date</th>
            </tr>
        </thead>
        <tbody>
            @php
                $prevBranch = null;
                $prevClass = null;
                $prevSection = null;
            @endphp
            @forelse ($all_data as $data)
                <tr>
                    <td>
                        @if ($data->student->branches->name !== $prevBranch)
                            {{ $prevBranch = $data->student->branches->name }}
                        @else
                            //
                        @endif
                    </td>
                    <td>
                        @if ($data->student->class->name !== $prevClass)
                            {{ $prevClass = $data->student->class->name }}
                        @else
                            //
                        @endif
                    </td>
                    <td>
                        @if ($data->sectionto->name !== $prevSection)
                            {{ $prevSection = $data->sectionto->name }}
                        @else
                            //
                        @endif
                    </td>
                    <td>{{ $data->student->stdname }}</td>
                    <td>{{ $data->transfer_date }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">No data available</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>