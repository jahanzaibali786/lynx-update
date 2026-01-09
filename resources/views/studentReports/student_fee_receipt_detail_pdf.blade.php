<div class="mt-4" style="margin: 0 auto; padding: 30px;">
    {{-- <div style="width: 100%; text-align: center;">
        <p style="font-family:Edwardian Script ITC; font-size:3rem; text-align: center;"><b>The Lynx School  </b></p>
    </div>
    <div style="width: 100%; text-align: center;">
        <p style="font-size:1rem; text-align: center; font-weight: 800;">Fee Receipt Detail</p>
    </div>
    <div style="width: 100%; text-align: center;">
        <p style="font-size:1rem; text-align: center; font-weight: 800;">PWD BRANCH ISLAMABAD</p>
    </div>
    <div class="" style="width:100%">
        <p style="width: 34%; float:left;"><b>From Date: </b>{{ request()->get('from_date') ?? date('Y-M-d') }}</p>
        <p style="width: 34%; float:left;"></p>
        <p style="width: 30%; float:left; padding-left:100px;"><b>To Date: </b>{{ request()->get('to_date') ?? date('Y-M-d') }}</p>
    </div> --}}
    <div style="margin-top: -100px;">
        <table class="">
            <thead>
                <tr class="table_heads" style="font-size:0.8rem;">
                    <th>{{ __('Sr No.') }}</th>
                    <th>{{ __('BSr No.') }}</th>
                    <th>{{ __('Rpt. Date') }}</th>
                    <th>{{ __('Ch Type') }}</th>
                    <th>{{ __('Roll No') }}</th>
                    <th>{{ __('Student') }}</th>
                    <th>{{ __('Class') }}</th>
                    <th>{{ __('Challan No') }}</th>
                    <th>{{ __('Billing Period') }}</th>
                    <th>{{ __('Bank') }}</th>
                    <th>{{ __('T.Head') }}</th>
                    <th>{{ __('Ref.') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Over Receipt.') }}</th>
                </tr>
            </thead>
            <tbody>
                @php $globalSr = 1; @endphp

                @foreach ($groupedReceipts as $branchId => $receipts)
                    @php $branchSr = 1; @endphp

                    <tr style="background-color: #f0f0f0;">
                        <td colspan="14" style="font-weight: bold;">
                           {{ $branchNames[$branchId] ?? 'Unknown Branch' }}
                        </td>
                    </tr>

                    @foreach ($receipts as $receipt)
                        @foreach ($receipt->voucher as $voucher)
                            <tr style="font-size:0.7rem;">
                                <td>{{ $globalSr++ }}</td> {{-- Global Sr No. --}}
                                <td>{{ $branchSr++ }}</td> {{-- Branch Sr No. --}}
                                <td>{{ \Carbon\Carbon::createFromFormat('Y-m-d', $receipt->recipt_date)->format('d-M-Y') }}
                                </td>
                                <td>{{ $receipt->challan?->challan_type }}</td>
                                <td>{{ $receipt->challan?->enrollstudent?->id }}</td>
                                <td>{{ $receipt->challan?->student?->stdname }}</td>
                                <td>{{ $receipt->challan?->class?->name }}</td>
                                <td>{{ $receipt->challan?->challanNo }}</td>
                                <td>
                                    {{ $receipt->challan?->fee_month ? \Carbon\Carbon::parse($receipt->challan->fee_month)->format('F Y') : '' }}
                                </td>
                                <td>{{ $receipt->bank?->bank_name }}</td>
                                <td>{{ $voucher->heads?->fee_head ?? '' }}</td>
                                <td>{{ $receipt->referance }}</td>
                                <td>{{ number_format($voucher->credit, 2) }}</td>
                                <td>0.0</td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
            </tbody>

        </table>
    </div>
</div>
