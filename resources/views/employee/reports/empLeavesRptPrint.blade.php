<!-- resources/views/employee/reports/empLeavesRptPrint.blade.php -->
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.6rem;
        }

        th,
        td {
            border: 1px solid #aaa;
            padding: 4px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .branch-title {
            font-weight: bold;
            text-align: left;
            background-color: #ddd;
        }

        .no-border {
            border: none !important;
            background-color: transparent;
        }
    </style>

    <h2 style="text-align: center;">Employee Leaves Report</h2>
    <p style="text-align: center;">Period: {{ $fromDate->toDateString() }} to {{ $toDate->toDateString() }}</p>

    @foreach ($reportData as $branchName => $employeesData)
        <br>
        <table class="datatable">
            <thead>
                <tr class="branch-title">
                    <td colspan="23">{{ $branchName }}</td>
                </tr>
                <tr>
                    <th>{{ __('Sr.') }}</th>
                    <th>{{ __('Br.sr') }}</th>
                    <th>{{ __('D/O/J') }}</th>
                    <th>{{ __('Emp No.') }}</th>
                    <th>{{ __('Employee Name') }}</th>
                    <th class="no-border"></th>
                    <th>{{ __('CL OB') }}</th>
                    <th>{{ __('CL Avail') }}</th>
                    <th>{{ __('CL CB') }}</th>
                    <th class="no-border"></th>
                    <th>{{ __('AL OB') }}</th>
                    <th>{{ __('AL Avail') }}</th>
                    <th>{{ __('AL CB') }}</th>
                    <th class="no-border"></th>
                    <th>{{ __('ML OB') }}</th>
                    <th>{{ __('ML Avail') }}</th>
                    <th>{{ __('ML CB') }}</th>
                    <th class="no-border"></th>
                    <th>{{ __('Un-Paid') }}</th>
                    <th>{{ __('Un Paid Total') }}</th>
                    <th class="no-border"></th>
                    <th>{{ __('Total Availed') }}</th>
                    <th>{{ __('Total Balance') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employeesData as $idx => $item)
                    @php $emp = $item['employee']; @endphp
                    <tr class="text-center">
                        <td>{{ $loop->parent->index + 1 }}.{{ $idx + 1 }}</td>
                        <td>{{ $emp->userbranch->id ?? '' }}</td>
                        <td>{{ \Carbon\Carbon::parse($emp->company_doj)->format('Y-m-d') }}</td>
                        <td>{{ $emp->employee_id }}</td>
                        <td>{{ $emp->name }}</td>
                        <td class="no-border"></td>
                        {{-- CL --}}
                        <td>{{ $item['ob']['CL'] }}</td>
                        <td>{{ $item['availed']['CL'] }}</td>
                        <td>{{ $item['cb']['CL'] }}</td>
                        <td class="no-border"></td>

                        {{-- AL --}}
                        <td>{{ $item['ob']['AL'] }}</td>
                        <td>{{ $item['availed']['AL'] }}</td>
                        <td>{{ $item['cb']['AL'] }}</td>
                        <td class="no-border"></td>

                        {{-- ML --}}
                        <td>{{ $item['ob']['ML'] }}</td>
                        <td>{{ $item['availed']['ML'] }}</td>
                        <td>{{ $item['cb']['ML'] }}</td>
                        <td class="no-border"></td>

                        {{-- Unpaid --}}
                        <td>{{ $item['availed']['Unpaid'] }}</td>
                        <td>{{ $item['availed']['Unpaid'] }}</td>
                        <td class="no-border"></td>

                        {{-- Totals --}}
                        <td>{{ $item['totalAvailed'] }}</td>
                        <td>{{ $item['totalBalance'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
