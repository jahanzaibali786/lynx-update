@extends('layouts.admin')
@section('page-title')
    {{ __('Edit Challan') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit Challan') }}</li>
@endsection
@push('script-page')
    <script>
        $(document).ready(function() {
            // Map subscription tokens to months
            const subscriptionMap = {
                'monthly': 1,
                'bi-monthly': 2,
                'quarterly': 3,
                '4-monthly': 4,
                '5-monthly': 5,
                '6-monthly': 6,
                '7-monthly': 7,
                '8-monthly': 8,
                '9-monthly': 9,
                '10-monthly': 10,
                '11-monthly': 11,
                'yearly': 12
            };

            function getDuration() {
                const sub = $('select[name="fee_subscription"]').val();
                return subscriptionMap[sub] || 1;
            }

            function updateTotal() {
                let total = 0;
                let paid_amount = parseFloat($('input[name="paid_amount"]').val()) || 0;
                total += paid_amount;
                $('.fee-row').each(function() {
                    if ($(this).find('input[type="checkbox"]').is(':checked')) {
                        let val = parseFloat($(this).find('.head-amount').val());
                        if (!isNaN(val)) {
                            total += val;
                        }
                    }
                });
                $('#total_amount').val(total.toFixed(2));
            }

            function updateHeadAmounts() {
                const duration = getDuration();
                const issueDateVal = $('input[name="issue_date"]').val();
                let isYearChange = false;

                if (issueDateVal) {
                    const date = new Date(issueDateVal);
                    // Check year change
                    // Start month (0-11)
                    const startMonth = date.getMonth();
                    const endMonthIndex = startMonth + duration - 1;
                    // If endMonthIndex >= 12, means we crossed into next year (e.g. Dec is 11. 11+2-1 = 12. 12>=12 -> True)
                    if (endMonthIndex >= 12) {
                        isYearChange = true;
                    }
                }

                $('.fee-row').each(function() {
                    const row = $(this);
                    // Use a safe fallback if data-head-name is missing or undefined
                    const headName = (row.data('head-name') || '').toLowerCase();
                    const basePrice = parseFloat(row.find('.discounted-amount').val()) || 0;
                    let finalAmount = 0;
                    const checkbox = row.find('input[type="checkbox"]');

                    if (headName.includes('admission fee')) {
                        // Admission Fee Logic: Always 1x (one-time charge)
                        finalAmount = basePrice * 1;
                    } else if (headName.includes('annual fee')) {
                        // Annual Fee Logic: Generally 1x
                        finalAmount = basePrice * 1;

                        // If year changes and not checked, check it.
                        if (isYearChange) {
                            if (!checkbox.is(':checked')) {
                                checkbox.prop('checked', true);
                            }
                        }
                    } else if (headName.includes('late fee')) {
                        // Late Fee Logic: Fixed amount (1x), never multiplied by duration
                        // If structure amount (basePrice) is 0, try to use original existing value
                        if (basePrice === 0) {
                            finalAmount = parseFloat(row.find('.head-amount').data('original-value')) || 0;
                        } else {
                            finalAmount = basePrice * 1;
                        }
                    } else {
                        // Standard Logic: Multiply by duration
                        finalAmount = basePrice * duration;
                    }

                    // If unchecked, set amount to 0
                    if (!checkbox.is(':checked')) {
                        finalAmount = 0;
                    }

                    row.find('.head-amount').val(finalAmount);
                });

                updateTotal();
            }

            // Listeners
            $(document).on('change', 'select[name="fee_subscription"]', function() {
                updateHeadAmounts();
            });

            $(document).on('change', 'input[name="issue_date"]', function() {
                updateHeadAmounts();
            });

            $(document).on('change', 'input[type="checkbox"]', function() {
                updateHeadAmounts();
            });

            // Re-calculate when discounted amount changes (triggered by DiscountCalculator)
            $(document).on('change', '.discounted-amount', function() {
                updateHeadAmounts();
            });

            // Initial calculation? 
            // The user requested "as i change", so we might not want to override on load unless necessary.
            // However, updateTotal should be called to ensure consistency with checkboxes.
            updateTotal();
        });

        class DiscountCalculator {
            constructor() {
                this.discountInputs = document.querySelectorAll('.discount');
                this.amountInputs = document.querySelectorAll('.amount');
                this.discountedAmountInputs = document.querySelectorAll('.discounted-amount');

                this.initialize();
            }
            initialize() {
                this.discountInputs.forEach((discountInput, index) => {
                    discountInput.addEventListener('input', () => this.calculateDiscount(index));
                });
            }
            calculateDiscount(index) {
                let discountPercentage = parseFloat(this.discountInputs[index].value) || 0;
                if (discountPercentage > 100) {
                    discountPercentage = 100;
                    this.discountInputs[index].value = Math.round(discountPercentage);
                }
                const amount = parseFloat(this.amountInputs[index].value) || 0;
                const discount = Math.round(amount * (discountPercentage / 100));
                const finalAmount = amount - discount;
                this.discountedAmountInputs[index].value = finalAmount < 0 ? 0 : finalAmount.toFixed(1);

                // Trigger change for jQuery listener
                $(this.discountedAmountInputs[index]).trigger('change');
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            new DiscountCalculator();
        });
    </script>
@endpush

@section('content')
    @php
        $challanHeads = $challan->heads->keyBy('head_id');

        $anyPaid = $challanHeads->contains(function ($item) {
            return $item->paid > 0;
        });
    @endphp
    <div class="row">
        {{ Form::model($challan, ['route' => ['challan.update', $challan->id], 'method' => 'POST']) }}
        <div class="col-sm-12">
            <div class="mt-2" id="multiCollapseExample1">
                <div class="card">
                    <div class="card-header">
                        <h5>Edit Challan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row justify-center">
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('challan_id', __('Challan No'), ['class' => 'form-label']) }}
                                    {{ Form::text('challan_id', $challan->challanNo, ['class' => 'form-control', 'required' => 'required', 'disabled' => 'disabled']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('student_id', __('Roll No'), ['class' => 'form-label']) }}
                                    {{ Form::text('student_id', @$challan->student ? @$challan->student->roll_no : '', ['class' => 'form-control', 'required' => 'required', 'disabled' => 'disabled']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('student_name', __('Student Name'), ['class' => 'form-label']) }}
                                    {{ Form::text('student_name', @$challan->student->stdname, ['class' => 'form-control', 'required' => 'required', 'readonly' => 'readonly']) }}
                                </div>
                            </div>
                            @php
                                $col_class = 'col-md-4';
                                if (@$challan->challan_type == 'Admission') {
                                    $col_class = 'col-md-3';
                                }
                            @endphp
                            @if ($challan->challan_type == 'Admission')
                                <div class="{{ $col_class }}">
                                    <div class="form-group"> {{-- fee month- --}}
                                        {{ Form::label('fee_month', __('Fee Month'), ['class' => 'form-label']) }}
                                        {{ Form::month(
                                            'fee_month',
                                            @$challan->fee_month ? \Carbon\Carbon::parse($challan->fee_month)->format('Y-m') : null,
                                            [
                                                'class' => 'form-control',
                                                'required' => 'required',
                                                'readonly' => $anyPaid ? 'readonly' : null,
                                            ],
                                        ) }}
                                    </div>
                                </div>
                            @endif
                            <div class="{{ $col_class }}">
                                <div class="form-group">
                                    {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('issue_date', @$challan->issue_date, ['class' => 'form-control', 'required' => 'required', 'readonly' => $anyPaid ? 'readonly' : null]) }}
                                </div>
                            </div>
                            <div class="{{ $col_class }}">
                                <div class="form-group">
                                    {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('due_date', @$challan->due_date, ['class' => 'form-control', 'required' => 'required', 'readonly' => $anyPaid ? 'readonly' : null]) }}
                                </div>
                            </div>
                            {{-- Fee Subscription --}}
                            <div class="{{ $col_class }}">
                                <div class="form-group">
                                    {{ Form::label('fee_subscription', __('Fee Subscription'), ['class' => 'form-label']) }}
                                    {{-- //select of months from 1-12 alphabets --}}
                                    @php
                                        $subscription = [
                                            'monthly' => 'Monthly',
                                            'bi-monthly' => 'Bi-Monthly',
                                            'quarterly' => 'Quarterly',
                                            '4-monthly' => '4 Month Subscription',
                                            '5-monthly' => '5 Month Subscription',
                                            '6-monthly' => '6 Month Subscription',
                                            '7-monthly' => '7 Month Subscription',
                                            '8-monthly' => '8 Month Subscription',
                                            '9-monthly' => '9 Month Subscription',
                                            '10-monthly' => '10 Month Subscription',
                                            '11-monthly' => '11 Month Subscription',
                                            'yearly' => 'Annual Subscription',
                                        ];

                                        // Count months from other_months
                                        $challanMonths = $challan->other_months
                                            ? array_filter(array_map('trim', explode(',', $challan->other_months)))
                                            : [];

                                        $monthCount = count($challanMonths);

                                        // Map count → subscription key
                                        $subscriptionByCount = [
                                            1 => 'monthly',
                                            2 => 'bi-monthly',
                                            3 => 'quarterly',
                                            4 => '4-monthly',
                                            5 => '5-monthly',
                                            6 => '6-monthly',
                                            7 => '7-monthly',
                                            8 => '8-monthly',
                                            9 => '9-monthly',
                                            10 => '10-monthly',
                                            11 => '11-monthly',
                                            12 => 'yearly',
                                        ];

                                        // Auto-select based on month count (fallback to saved value)
                                        $selectedSubscription =
                                            $subscriptionByCount[$monthCount] ?? ($challan->fee_subscription ?? null);
                                    @endphp

                                    {{ Form::select('fee_subscription', $subscription, $selectedSubscription, [
                                        'class' => 'form-control',
                                        'required',
                                        'readonly' => $anyPaid ? 'readonly' : null,
                                    ]) }}

                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('total_amount', __('Total Amount'), ['class' => 'form-label']) }}
                                    {{ Form::text('total_amount', @$challan->total_amount, ['id' => 'total_amount', 'class' => 'form-control', 'required' => 'required', 'readonly' => 'readonly']) }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                {{ Form::label('discount_policy', __('Discount Policy'), ['class' => 'form-label']) }}
                                {{ Form::text('discount_policy', @$concession->concession->title, ['class' => 'form-control', 'required' => 'required', 'readonly' => 'readonly']) }}
                                {{ Form::text('discount_policy_id', @$concession->concession_id, ['hidden' => 'hidden', 'class' => 'form-control']) }}
                            </div>
                            {{-- //remarks  --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    {{ Form::label('remarks', __('Remarks'), ['class' => 'form-label']) }}
                                    {{ Form::textarea('remarks', @$challan->remarks, ['class' => 'form-control', 'rows' => 2, 'readonly' => $anyPaid ? 'readonly' : null]) }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="">
                        <table class="">
                            <thead class="table_heads">
                                <tr>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Discount (%)</th>
                                    <th>Net Amount</th>
                                    <th>Challan Amount</th>
                                    <th>
                                        <input type="checkbox" id="checkAll" {{ $anyPaid ? 'disabled' : '' }}>
                                    </th>
                                    <th style="display: none;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($classfee as $fee)
                                    @php
                                        $policy = null; // Reset policy for this iteration
                                        // Identify protected heads
                                        $headNames = ['TUITION FEE', 'ADMISSION FEE', 'SECURITY FEE'];
                                        $isProtected = in_array($fee->feehead->fee_head ?? '', $headNames);

                                        // Base and discounted amounts
                                        $baseAmount = $fee->amount ?? 0;
                                        $discountedAmount = $baseAmount;
                                        if ($concession) {
                                            $policy = \App\Models\ConcessionPolicyHead::where(
                                                'concession_id',
                                                $concession->concession_id,
                                            )
                                                ->where('head_id', $fee->head_id)
                                                ->first();
                                        }
                                        if (!empty($policy)) {
                                            $discountedAmount -= $baseAmount * ($policy->percentage / 100);
                                        } else {
                                            $discountedAmount -= $baseAmount * ($fee->discount / 100);
                                        }

                                        // Compute challan outstanding
                                        $ch = $challanHeads->get($fee->head_id);
                                        $challanValue = $ch
                                            ? (int) str_replace(',', '', $ch->price ?? 0) -
                                                (int) str_replace(',', '', $ch->concession ?? 0)
                                            : 0;
                                        // dump($ch);
                                    @endphp
                                    <tr class="fee-row" data-head-name="{{ strtolower($fee->feehead->fee_head ?? '') }}">
                                        {{-- Description --}}
                                        <td>{{ $fee->feehead->fee_head ?? '-' }}</td>

                                        {{-- Original Amount --}}
                                        <td>
                                            <input type="number" name="amount[{{ $fee->feehead->id }}]"
                                                class="form-control amount" value="{{ $baseAmount }}" readonly
                                                @if ($isProtected) readonly @endif>
                                        </td>

                                        {{-- Discount % --}}
                                        <td>
                                            <input type="text" class="form-control discount"
                                                value="{{ $policy->percentage ?? ($fee->discount ?? 0) }}"
                                                @if ((!empty($policy) && $policy->percentage != 0) || $anyPaid) readonly @endif>
                                        </td>

                                        {{-- Net Amount --}}
                                        <td>
                                            <input type="number" class="form-control discounted-amount"
                                                name="actual_amount[{{ $fee->feehead->id }}]"
                                                value="{{ round($discountedAmount) }}" readonly
                                                @if ($isProtected) readonly @endif>
                                        </td>

                                        {{-- Challan Amount --}}
                                        <td>
                                            @if ($ch)
                                                <label for="challan_amount"><span style="color: red;"> Challan Amount :
                                                        {{ @$ch->price }} - Concession Amount :
                                                        {{ @$ch->concession }}</span></label>
                                            @endif
                                            <input type="number" class="form-control head-amount" id="challan_amount"
                                                name="challan_amount[{{ $fee->feehead->id }}]" value="{{ $challanValue }}"
                                                readonly data-original-value="{{ $challanValue }}">
                                        </td>

                                        {{-- Checkbox --}}
                                        @php
                                            $head = $challanHeads->firstWhere('head_id', $fee->head_id);
                                        @endphp
                                        <td>
                                            <input type="checkbox" name="checked[]" value="{{ $fee->head_id }}"
                                                {{ $head ? 'checked' : '' }}
                                                {{ $head && $head->paid > 0 ? 'disabled' : '' }}>
                                        </td>
                                        {{-- Hidden head ID --}}
                                        <td style="display: none;">
                                            <input type="hidden" name="headid[]" value="{{ $fee->feehead->id }}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                    <br>
                    @if (!$anyPaid)
                        <div class="modal-footer p-4">
                            <input type="submit" value="{{ __('Update') }}" class="btn  btn-primary">
                        </div>
                    @else
                        <div class="modal-footer p-4">
                            {{-- the challan is already partially / fully piad so can't be edit --}}
                            <span class="text-danger">Note: This challan has already been partially or fully paid. Editing
                                is restricted to prevent inconsistencies.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{ Form::close() }}
@endsection
