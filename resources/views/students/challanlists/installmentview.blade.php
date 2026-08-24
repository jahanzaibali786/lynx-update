@extends('layouts.admin')
@section('page-title')
{{__('Manage Installments')}}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
<li class="breadcrumb-item">{{ __('Installments') }}</li>
@endsection
@section('action-btn')
@endsection
@section('content')
<script>
    $(document).ready(function() {
        $('#save-btn').on('click', function(event) {
            event.preventDefault();  // Prevent form submission
            $('#type').val('submit');
            $('#install').submit();
        });

        $('#view').on('click', function(event) {
            event.preventDefault();  // Prevent form submission
            $('#type').val('view');
            $('#install').submit();
        });
    });
</script>

<div class="card p-4 mt-5">
    <div class="row">
        <div class="col-md-6">
            <h4 class="mb-5 p-2 rounded text-center border-bottom"><b>Details</b></h4>
            <div class="row">
                <div class="col-md-6">
                    <span><b>Challan No :</b></span>
                    <span>{{ $challan->challanNo }}</span>
                </div>
                <div class="col-md-6">
                    <span><b>Challan Month :</b> </span>
                    <span>{{ \Carbon\Carbon::parse($challan->challan_date)->format('F,Y') }}</span>
                </div>
            </div><br>
            <div class="row">
                <div class="col-md-6">
                    <span><b>Issue Date :</b> </span>
                    <span>{{ @$challan->issue_date ? $challan->issue_date : ''}}</span>
                </div>
                <div class="col-md-6">
                    <span><b>Due Date :</b></span>
                    <span>{{ @$challan->due_date ? $challan->due_date : '' }}</span>
                </div>
            </div><br>
            <div class="row">
                <div class="col-md-6">
                    <span><b>Student Name :</b></span>
                    <span>{{@$studentData->stdname ? $studentData->stdname : ''}}</span>
                </div>
                <div class="col-md-6">
                    <span><b>Roll No :</b></span>
                    <span>{{@$studentData->id ? $studentData->id : ''}}</span>
                </div>
            </div><br>
            <div class="row">
                <div class="col-md-6">
                    <span><b>Branch :</b></span>
                    <span>{{@$studentData->branches->name ? $studentData->branches->name : ''}}</span>
                </div>
                <div class="col-md-6">
                    <span><b>Class :</b></span>
                    <span>{{@$studentData->class->name ? $studentData->class->name : ''}}</span>
                </div>
            </div>
            <div>
                <table class="datatable">
                    <thead>
                        <tr class="table_heads">
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Discount (%)</th>
                            <th>Net Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($classfee as $fee)
                            @if (in_array($fee->head_id, $headIds))

                                @php
                                    $matchingHead = collect($allHeads)->firstWhere('head_id', $fee->head_id);
                                @endphp
                                <tr>
                                    <td><label type="text">{{ !empty($fee->feehead->fee_head) ? $fee->feehead->fee_head : '-' }}</label></td>
                                    <td><label for="">{{ !empty($matchingHead['price']) ? $matchingHead['price'] : '-' }}</label></td>
                                    <td><label type="text"> {{ $matchingHead && $matchingHead['price'] ? round(($matchingHead['concession'] / $matchingHead['price']) * 100) : 0 }}</label></td>
                                    <td><label for="">{{ @$matchingHead['price'] - @$matchingHead['concession']}}</label></td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="col-md-6">
             @if ($challan->challan_type == 'Readmission')
                <form action="{{ route('installment-challan-readmission', $challan->id) }}" method="POST" id="install">
            @else
                <form action="{{ route('installment-challan', $challan->id) }}" method="POST" id="install">
            @endif
                @csrf
                <input type="hidden" name="type" id="type" >
                <h4 class="mb-5 p-2 rounded text-center border-bottom"><b>Installment</b></h4>
                <table class="datatable">
                    <thead>
                        <tr class="table_heads">
                            <th>Description</th>
                            <th>Installment 1 (%)</th>
                            <th>Installment 2 (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($classfee as $fee)
                            @if (in_array($fee->head_id, $headIds))

                            @php
                                $inst1Selected = '100';
                                $inst2Selected = '0';

                                if (isset($installmentChallans) && $installmentChallans->isNotEmpty() ) {
                                    $existingInst1 = $installmentChallans->firstWhere('challan_date', $challan->challan_date);
                                    $nextMonth = \Carbon\Carbon::parse($challan->challan_date)
                                        ->addMonth()
                                        ->startOfMonth()
                                        ->toDateString();

                                    $existingInst2 = $installmentChallans->first(function ($item) use ($nextMonth) {
                                        return $item->challan_date === $nextMonth
                                            || $item->fee_month === $nextMonth;
                                    });                    
                                                    // dd($existingInst1,$existingInst2);
                                    if ($existingInst1 && optional($existingInst1->heads)->contains('head_id', $fee->feehead->id)) {
                                        $inst1Selected = '100';
                                    } else {
                                        $inst1Selected = '0';
                                    }

                                    if ($existingInst2 && optional($existingInst2->heads)->contains('head_id', $fee->feehead->id)) {
                                        $inst2Selected = '100';
                                    } else {
                                        $inst2Selected = '0';
                                    }
                                }
                            @endphp
                        <tr>
                            <td>
                                <label>{{ !empty($fee->feehead->fee_head) ? $fee->feehead->fee_head : '-' }}</label>
                                <input type="hidden" name="fee_head_id[]" value="{{ $fee->feehead->id }}">
                            </td>
                            @if (isset($installmentChallans) && $installmentChallans->count() > 1)
                                <td>{!! Form::select('head_amount_inst1[]', ['0' => '0', '100' => '100'], $inst1Selected, ['class' => 'form-control head_amount_inst1', 'data-index' => $loop->index, 'disabled' => (strpos(strtolower($fee->feehead->fee_head), 'tuition') !== false) ? 'disabled' : null,]) !!}
                                    @if (strpos(strtolower($fee->feehead->fee_head), 'tuition') !== false) {!! Form::hidden('head_amount_inst1[]', $inst1Selected) !!}@endif
                                </td>
                                <td>{!! Form::select('head_amount_inst2[]', ['0' => '0', '100' => '100'], $inst2Selected, ['class' => 'form-control head_amount_inst2', 'data-index' => $loop->index, 'disabled' => (strpos(strtolower($fee->feehead->fee_head), 'tuition') !== false) ? 'disabled' : null,]) !!}
                                    @if (strpos(strtolower($fee->feehead->fee_head), 'tuition') !== false) {!! Form::hidden('head_amount_inst2[]', $inst2Selected) !!} @endif
                                </td>
                            @else
                                <td>{!! Form::select('head_amount_inst1[]', ['0' => '0', '100' => '100'], $inst1Selected, ['class' => 'form-control head_amount_inst1', 'data-index' => $loop->index,  'disabled' => (strpos(strtolower($fee->feehead->fee_head), 'tuition') !== false) ? 'disabled' : null,]) !!}
                                    @if (strpos(strtolower($fee->feehead->fee_head), 'tuition') !== false) {!! Form::hidden('head_amount_inst1[]', $inst1Selected) !!} @endif
                                </td>
                                <td>{!! Form::select('head_amount_inst2[]', ['0' => '0', '100' => '100'], $inst2Selected, ['class' => 'form-control head_amount_inst2', 'data-index' => $loop->index,  'disabled' => (strpos(strtolower($fee->feehead->fee_head), 'tuition') !== false) ? 'disabled' : null,]) !!}
                                    @if (strpos(strtolower($fee->feehead->fee_head), 'tuition') !== false) {!! Form::hidden('head_amount_inst2[]', $inst2Selected) !!} @endif
                                </td>
                            @endif
                        </tr>
                        @endif
                        @endforeach

                    </tbody>
                </table>
                <br>
                @if (isset($installmentChallans) && $installmentChallans->count() > 1)
                    <button class="btn btn-info" style=" float: right" id="save-btn">Update Installment</button>
                @else
                    <button class="btn btn-info" style=" float: right" id="save-btn" >Save</button>
                @endif
                {{-- <button class="btn btn-info "  id="view"  style="margin-right:10px; float: right">Preview</button> --}}
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // if (!document.querySelector('.head_amount_inst1[disabled]')) {
        //     document.querySelectorAll('.head_amount_inst1').forEach(function(select) {
        //         select.value = '100';
        //     });
        //     document.querySelectorAll('.head_amount_inst2').forEach(function(select) {
        //         select.value = '0';
        //     });
        // }

        document.querySelectorAll('.head_amount_inst1').forEach(function(select) {
            select.addEventListener('change', function() {
                var index = this.getAttribute('data-index');
                var correspondingInst2 = document.querySelector('.head_amount_inst2[data-index="' + index + '"]');
                if (this.value === '0') {
                    correspondingInst2.value = '100';
                } else if (this.value === '100') {
                    correspondingInst2.value = '0';
                }
            });
        });
        document.querySelectorAll('.head_amount_inst2').forEach(function(select) {
            select.addEventListener('change', function() {
                var index = this.getAttribute('data-index');
                var correspondingInst1 = document.querySelector('.head_amount_inst1[data-index="' + index + '"]');
                if (this.value === '0') {
                    correspondingInst1.value = '100';
                } else if (this.value === '100') {
                    correspondingInst1.value = '0';
                }
            });
        });
    });
</script>
@endsection
