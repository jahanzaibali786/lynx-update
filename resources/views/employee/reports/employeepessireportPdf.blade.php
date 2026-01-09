<style>
    table, tr, th, td {
        border: 1px solid black;
        border-collapse: collapse;
        font-size: 0.6rem;
    }
</style>
<div class=" p-4">
    <p style="text-align:center; font-weight:900; font-size:1rem;">
    {{-- @isset($_GET['branches'])
    {{!empty(\Auth::user()->getBranch($_GET['branches'])) ? \Auth::user()->getBranch($_GET['branches'])->name : 'All Branches'}}
    @endif --}}
    </p>
    {{-- <p style="font-weight:600; font-size:0.8rem;">pessi Contribution for the Month of<span><b>&nbsp;<u>{{ \Carbon\Carbon::createFromFormat('m', $month)->format('F') }}-{{ \Carbon\Carbon::createFromFormat('Y', $year)->format('Y') }}</u></b></span></p> --}}
    
    <table style="width: 100%; table-layout: fixed; word-wrap: break-word; margin: 0 20px; margin-top: -150px;">
        <tr style="background-color: grey; font-size: 0.9rem;">
            <th>Sr. No.</th>
            <th>Emp Code</th>
            <th>Employee Name</th>
            <th>Father Name</th>
            <th>CNIC</th>
            <th>Designation</th>
            <th>Monthly Wages</th>
            <th>Working Days</th>
            <th>Wages Status</th>
            <th>Contribution Amnt.</th>
        </tr>
        @foreach ($reportData as $branchName => $employees)
            <tr>
                <th colspan="3">{{ $branchName }}</th>
                <th colspan="7"></th>
            </tr>
            @foreach ($employees as $index => $employee)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $employee->employee_id }}</td>
                    <td>{{ $employee->employee->name }}</td>
                    <td>{{ $employee->employee->f_name }}</td>
                    <td>{{ $employee->employee->cnic }}</td>
                    <td>{{ $employee->employee->designation->name }}</td>
                    <td style="text-align:right;">{{ number_format($employee->basics, 2) }}</td>
                    <td style="text-align:right;">{{ $employee->sal_days }}</td>
                    <td style="text-align:right;">Monthly</td>
                    <td style="text-align:right;">{{ $employee->pessi }}</td>
                </tr>
            @endforeach
        @endforeach
    </table>
</div>