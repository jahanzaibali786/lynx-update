<style>
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: .65rem;
    }

    th,
    td {
        border: 2px solid #000;
        padding: 4px;
        text-align: center;
    }

    .group-row {
        background: #ddd;
        font-weight: 700;
    }

    .school-name {
        font-family: 'Edwardian Script ITC', cursive;
        font-size: 2rem;
        font-weight: 800;
    }

    .report-title {
        font-weight: 600;
        font-size: 1rem;
    }

    .enabled {
        color: green;
        font-weight: 700;
    }

    .disabled {
        color: red;
        font-weight: 700;
    }
</style>

@php
    /* ----------------------------------------------------------
       work out how many columns we really have (Balance is optional)
    ---------------------------------------------------------- */
    $hasBalance = !empty($filter['showBalance']);
    $colspan = 5 + ($hasBalance ? 1 : 0);
@endphp

<table class="datatable">
    {{-- ========== big header block ========== --}}
    <thead>
        <tr>
            <td colspan="{{ $colspan }}"
                style="text-align: center; font-family: 'Edwardian Script ITC'; font-weight: 800; font-size: 25rem; white-space:nowrap;">
                The Lynx School
            </td>
        </tr>
        <tr>
            <td colspan="{{ $colspan }}" style="text-align: center; font-weight: 600; font-size: 15rem;">
                <span>Chart of Accounts</span>
            </td>
        </tr>
        <tr>
            <td colspan="{{ $colspan }}" style="text-align: center;">
            </td>
        </tr>

        <tr>
            <td colspan="{{ $colspan }}" style="height:8px;"></td>
        </tr>

        {{-- ========== column headers ========== --}}
        <tr style="background:#b5b5b5; font-weight:600;">
            <th
                style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">
                Code</th>
            <th
                style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">
                Name</th>
            <th
                style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">
                Type</th>
            <th
                style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">
                Parent Account Name</th>
            @if ($hasBalance)
                <th
                    style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">
                    Balance</th>
            @endif
            <th
                style=" background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">
                Status</th>
        </tr>
    </thead>

    <tbody>
        {{-- ========== loop over account‑types ========== --}}
        @foreach ($chartAccounts as $type => $accounts)
            {{-- group row for the type --}}
            <tr class="group-row">
                <td colspan="{{ $colspan }}"
                    style="background:gray; font-size: 12rem; font-weight: 800; border: 2px solid black; border-collapse: collapse;">
                    {{ $type }}</td>
            </tr>

            @foreach ($accounts as $account)
                @php
                    $totalBalance = $hasBalance
                        ? ltrim(
                            App\Models\Utility::getAccountBalance(
                                $account->id,
                                $filter['startDateRange'],
                                $filter['endDateRange'],
                                $filter['branch'],
                            ),
                            '-',
                        )
                        : null;
                @endphp

                <tr>
                    <td style="font-size: 10rem;  border: 2px solid black; border-collapse: collapse;">
                        {{ $account->code }}</td>
                    <td style="font-size: 10rem;  border: 2px solid black; border-collapse: collapse;">
                        {{-- link removed for printable version; add back if you need it --}}
                        {{ $account->name }}
                    </td>
                    <td style="font-size: 10rem;  border: 2px solid black; border-collapse: collapse;">
                        {{ optional($account->subType)->name ?? '-' }}</td>
                    <td style="font-size: 10rem;  border: 2px solid black; border-collapse: collapse;">
                        {{ optional($account->parentAccount)->name ?? '-' }}</td>

                    @if ($hasBalance)
                        <td style="font-size: 10rem;  border: 2px solid black; border-collapse: collapse;">
                            {{ $totalBalance !== null && $totalBalance !== '' ? \Auth::user()->priceFormat($totalBalance) : '-' }}
                        </td>
                    @endif

                    <td style="font-size: 10rem;  border: 2px solid black; border-collapse: collapse;">
                        <span class="{{ $account->is_enabled ? 'enabled' : 'disabled' }}">
                            {{ $account->is_enabled ? 'Enabled' : 'Disabled' }}
                        </span>
                    </td>
                </tr>
                @if ($account->subAccounts->count() > 0)
                    @foreach ($account->subAccounts as $account2)
                    <tr>
                        <td style="font-size: 10rem;  border: 2px solid black; border-collapse: collapse;">
                            {{ $account2->childaccounts->code }}</td>
                        <td style="font-size: 10rem;  border: 2px solid black; border-collapse: collapse;">
                            {{-- link removed for printable version; add back if you need it --}}
                            {{ $account2->childaccounts->name }}
                        </td>
                        <td style="font-size: 10rem;  border: 2px solid black; border-collapse: collapse;">
                            {{ optional($account2->childaccounts->subType)->name ?? '-' }}</td>
                        <td style="font-size: 10rem;  border: 2px solid black; border-collapse: collapse;">
                            {{ optional($account2->childaccounts->parentAccount)->name ?? '-' }}</td>
                        @if ($hasBalance)
                            <td style="font-size: 10rem;  border: 2px solid black; border-collapse: collapse;">
                                {{ $totalBalance !== null && $totalBalance !== '' ? \Auth::user()->priceFormat($totalBalance) : '-' }}
                            </td>
                        @endif
                        <td style="font-size: 10rem;  border: 2px solid black; border-collapse: collapse;">
                            <span class="{{ $account2->childaccounts->is_enabled ? 'enabled' : 'disabled' }}">
                                {{ $account2->childaccounts->is_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                @endif

            @endforeach
        @endforeach
    </tbody>
</table>
