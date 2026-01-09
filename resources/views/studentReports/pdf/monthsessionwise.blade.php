<style>
    table,
    tr,
    th,
    td {
        border: 1px solid black;
        border-collapse: collapse;
        font-size: 0.8rem;
    }

    th {
        text-align: left;
    }

    #periodtext {
        display: none;
    }
</style>
<div class="card mt-3 table-responsive">
        <div class="text-center py-3" style="text-align: center;padding : 0px 10px;">
            <h5 class="fw-bold">
                {{ $branches[$selectedBranchId] ?? Auth::user()->name }}
            </h5>
            <p>
                <strong>{{ __('Period:') }}</strong>
                {{ $labelFrom }} — {{ $labelTo }}, {{ $selectedYear }}
            </p>
        </div>

        <!-- Registration Section -->
        <table class="table table-bordered mb-5">
            <thead class="table-secondary">
                <tr>
                    <th colspan="{{ count($schoolClasses ?? []) + 2 }}">{{ __('Registration') }}</th>
                </tr>
                <tr>
                    <th>{{ __('Month') }}</th>
                    @foreach ($schoolClasses as $class)
                        <th>{{ $class }}</th>
                    @endforeach
                    <th>{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($months as $m)
                    <tr>
                        <td>{{ $monthNames[$m] }}</td>
                        @foreach ($schoolClasses as $class)
                            <td>{{ $registrationCounts[$m][$class] ?? 0 }}</td>
                        @endforeach
                        <td>{{ $totalRegistrations[$m] ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Strength Section -->
        <table class="table table-bordered mb-5">
            <thead class="table-secondary">
                <tr>
                    <th colspan="{{ count($schoolClasses ?? []) + 2 }}">{{ __('Student Strength') }}</th>
                </tr>
                <tr>
                    <th>{{ __('Month') }}</th>
                    @foreach ($schoolClasses as $class)
                        <th>{{ $class }}</th>
                    @endforeach
                    <th>{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($months as $m)
                    <tr>
                        <td>{{ $monthNames[$m] }}</td>
                        @foreach ($schoolClasses as $class)
                            <td>{{ $strengthCounts[$m][$class] ?? 0 }}</td>
                        @endforeach
                        <td>{{ $totalStrength[$m] ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Enrollment Section -->
        <table class="table table-bordered mb-5">
            <thead class="table-secondary">
                <tr>
                    <th colspan="{{ count($schoolClasses ?? []) + 2 }}">{{ __('Enrollment') }}</th>
                </tr>
                <tr>
                    <th>{{ __('Month') }}</th>
                    @foreach ($schoolClasses as $class)
                        <th>{{ $class }}</th>
                    @endforeach
                    <th>{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($months as $m)
                    <tr>
                        <td>{{ $monthNames[$m] }}</td>
                        @foreach ($schoolClasses as $class)
                            <td>{{ $enrollmentCounts[$m][$class] ?? 0 }}</td>
                        @endforeach
                        <td>{{ $totalEnrollments[$m] ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Withdrawal Section -->
        <table class="table table-bordered mb-5">
            <thead class="table-secondary">
                <tr>
                    <th colspan="{{ count($schoolClasses ?? []) + 2 }}">{{ __('Withdrawal') }}</th>
                </tr>
                <tr>
                    <th>{{ __('Month') }}</th>
                    @foreach ($schoolClasses as $class)
                        <th>{{ $class }}</th>
                    @endforeach
                    <th>{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($months as $m)
                    <tr>
                        <td>{{ $monthNames[$m] }}</td>
                        @foreach ($schoolClasses as $class)
                            <td>{{ $withdrawalCounts[$m][$class] ?? 0 }}</td>
                        @endforeach
                        <td>{{ $totalWithdrawals[$m] ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>