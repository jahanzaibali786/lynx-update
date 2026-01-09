<style>
    body {
        font-family: 'Arial', sans-serif;
    }

    .header-title {
        font-family: 'Edwardian Script ITC', cursive;
        font-size: 2rem;
        font-weight: 800;
        text-align: center;
        margin: 0;
    }

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

    .account-type {
        font-weight: bold;
        text-align: left;
        background-color: #ddd;
        padding: 6px;
        margin-top: 10px;
    }

    .status-enabled {
        color: green;
        font-weight: bold;
    }

    .status-disabled {
        color: red;
        font-weight: bold;
    }
</style>

{{-- HEADER --}}
<div style="width: 100%; position: relative; bottom: 30px; display: table;">
    <div style="display: table-cell; width: 20%; text-align: center; vertical-align: middle;">
        <div class="logo">
            <img src="{{ asset('assets/images/lynx2.jpg') }}" style="max-width: 90px; max-height: 90px;" alt="logo">
          
        </div>
    </div>
    <div style="display: table-cell; width: 5%;"></div>
    <div style="display: table-cell; width: 60%; text-align: center; vertical-align: middle;">
          <img src="{{ asset('assets/images/lynxheadertext.jpg') }}" style=" max-height: 40px; width: auto; margin-left:0px;" alt="logo">
        <h5 style="margin: 10px;">
          {{ $reportName }}
        </h5>
    </div>
    <div style="display: table-cell; width: 15%;"></div>
</div>



{{-- CHART ACCOUNTS TABLE --}}
@foreach ($chartAccounts as $type => $accounts)
    <div class="account-type">{{ $type }}</div>
    <table class="datatable">
        <thead>
            <tr>
                <th width="10%">Code</th>
                <th width="30%">Name</th>
                <th width="20%">Type</th>
                <th width="20%">Parent Account Name</th>
                @if($filter['showBalance'])
                <th width="20%">Balance</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($accounts as $account)
            <tr>
                <td>{{ $account->code }}</td>
                <td>{{ $account->name }}</td>
                <td>{{ !empty($account->subType) ? $account->subType->name : '-' }}</td>
                <td>{{ !empty($account->parentAccount) ? $account->parentAccount->name : '-' }}</td>

                @if($filter['showBalance'])
                <td>
                    @php
                        $totalBalance = App\Models\Utility::getAccountBalance(
                            $account->id,
                            $filter['startDateRange'],
                            $filter['endDateRange'],
                            $filter['branch']
                        );
                        $totalBalance = ltrim($totalBalance, '-');
                    @endphp
                    {{ !empty($totalBalance) ? \Auth::user()->priceFormat($totalBalance) : '-' }}
                </td>
                @endif
            </tr>
            @if ($account->subAccounts->count() > 0)
                @foreach ($account->subAccounts as $account2)
                <tr>
                    <td>{{ $account2->childaccounts->code }}</td>
                    <td>{{ $account2->childaccounts->name }}</td>
                    <td>{{ !empty($account2->childaccounts->subType) ? $account2->childaccounts->subType->name : '-' }}</td>
                    <td>{{ !empty($account2->childaccounts->parentAccount) ? $account2->childaccounts->parentAccount->name : '-' }}</td>

                    @if($filter['showBalance'])
                    <td>
                        @php
                            $totalBalance = App\Models\Utility::getAccountBalance(
                                $account2->childaccounts->id,
                                $filter['startDateRange'],
                                $filter['endDateRange'],
                                $filter['branch']
                            );
                            $totalBalance = ltrim($totalBalance, '-');
                        @endphp
                        {{ !empty($totalBalance) ? \Auth::user()->priceFormat($totalBalance) : '-' }}
                    </td>
                    @endif
                </tr>
                @endforeach
            @endif


            @endforeach
        </tbody>
    </table>
@endforeach
