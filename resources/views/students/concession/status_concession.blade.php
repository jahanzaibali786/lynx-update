
<div class="modal-body">
    <div class="row d-flex text-center">
        {{-- @dd($concession) --}}
        <h4 class="text-start">Student Detail</h4>
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"><b>Branch :</b> <br>
            @if(@$concession->student->student_status == 'Registered')    
            {{ @$concession->student->reg_branch->name }}
            @else
            {{ @$concession->student->branch->name }}
            @endif
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"><b>Roll No:</b> <br>
        {{ @$concession->student->enrollment->enrollId }}</div>
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"><b>Student Name : <br>
        </b>{{ $concession->student->stdname }}</div>
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"><b>Registration No :</b> <br>
        {{ @$concession->student->id }}</div>
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"><b>Father Name : <br>
            </b>{{ $concession->student->fathername }}</div>
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"><b>Class: </b>
            <br>{{ $concession->student->class->name }}
        </div>
        <hr>
        <h4 class="text-start">Concession Detail</h4>
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"><b>Order No : <br>
            </b>{{ @$concession->id }}
        </div>
        <div class="col-xl-8 col-lg-8 col-md-6 col-sm-12 col-12"><b>Concession: <br>
            </b>{{ @$concession->concession->title }}
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"><b>Apply Date: <br> </b>{{ @$concession->apply_date }}
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"><b>Start Date: <br> </b>{{ @$concession->start_date }}
        </div>
        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-12"><b>End Date: <br> </b>{{ @$concession->end_date ?? '-' }}
        </div>
        <hr>
        <h4 class="text-start">Concession Heads</h4>
        @php
            $concessionHeads = \App\Models\ConcessionPolicyHead::where('concession_id', @$concession->concession->id)
                ->where('percentage', '!=', '0')
                ->get();
        @endphp
        @if ($concessionHeads)
            <table class="text-start">
                <thead>
                    <tr style="background: gray !important">
                        <th>Head</th>
                        <th>Percentage</th>
                        <th>Actual Value</th>
                        <th>Discounted Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach ($concessionHeads as $dta)
                            @php
                                $head = \App\Models\FeeHead::find($dta->head_id);
                                $classheads = \App\Models\ClassWiseFee::where(
                                    'class_id',
                                    @$concession->student->class_id,
                                )
                                    ->where('head_id', $head->id)
                                    ->first();

                                $actualAmount = @$classheads->amount;
                                $discountPercentage = @$dta->percentage;
                                $discountAmount = ($discountPercentage / 100) * $actualAmount;
                                $payableAmount = $actualAmount - $discountAmount;
                            @endphp
                            <td class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12">
                                {{ $head->fee_head }}
                            </td>
                            <td class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12">
                                {{ $discountPercentage }}%
                            </td>
                            <td class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12">
                                {{ $actualAmount }}
                            </td>
                            <td class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12">
                                {{ number_format($payableAmount, 2) }}
                            </td>
                        </tr>
                        @endforeach
                </tbody>
        </table>
        @endif
        @if (count($prev_concession) > 0)
        <hr>
        <h4 class="text-start">Active Concession</h4>
            <table class="text-start">
                <thead>
                    <tr style="background: #808080 !important">
                        <th>Con. No</th>
                        <th style="width:35%" >Concession </th>
                        {{-- <th>Apply Date</th> --}}
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Cancle Date</th>
                        <th>Cancle Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($prev_concession as $prevcon)
                    <tr>
                        <td>
                            {{ @$prevcon->id }}
                        </td>
                        <td style="width:35%">
                            {{ @$prevcon->concession->title }}
                        </td>
                        {{-- <td>
                            {{ @$concession->apply_date }}
                        </td> --}}
                        <td>
                            {{ @$prevcon->start_date }}
                        </td>
                        <td>
                            {{ @$prevcon->end_date }}
                        </td>
                        <td>
                            {{ @$prevcon->cancel_date }}
                        </td>
                        <td>
                           <textarea name="" id="" rows="2" cols="30" readonly>{{ @$prevcon->cancel_remarks }}</textarea>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
        </table>
        @endif
    </div>
    {{-- @dd($concession) --}}
    @if(Auth::user()->type == 'company')
    <div class="form-group align-items-end">
        <!-- Rejection Form -->
        <div id="rejection-form" class="d-none" style="animation: fadeInEaseIn 0.5s ease-in;">
            <form action="{{ route('concession.reject_reason', [$concession->id]) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="reject_reason">&nbsp; Reason for Rejection :</label>
                    <textarea name="reject_reason" id="reject_reason" class="form-control" rows="2"
                        placeholder="Enter rejection reason"></textarea>
                        <input type="hidden" name="type" value="Rejected">
                </div>
                <button type="submit" class="btn btn-outline-danger mt-1">Submit</button>
                <a href="javascript:void(0);" id="rollback-rol-btn" class="mx-1 btn btn-outline-danger" 
                    data-bs-title="{{ __('RollBack') }}">
                    RollBack
                </a>
                <a href="{{ route('concession.change_status', [$concession->id, 'Approved']) }}"
                    class="mx-1 btn btn-outline-primary"  data-bs-title="{{ __('Approved') }}">
                    Approved
                </a>
            </form>
        </div>

        <!-- Rollback Form -->
        <div id="rollback-form" class="d-none" style="animation: fadeInEaseIn 0.5s ease-in;">
            <form action="{{ route('concession.reject_reason', [$concession->id]) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="rollback_reason">&nbsp; Reason for Rollback :</label>
                    <textarea name="rollback_reason" id="rollback_reason" class="form-control" rows="2"
                        placeholder="Enter Rollback Reason"></textarea>
                        <input type="hidden" name="type" value="Rollback">
                </div>
                <button type="submit" class="btn btn-outline-danger mt-1">Submit</button>
                <a href="javascript:void(0);" id="reject-rol-btn" class="mx-1 btn btn-outline-danger" 
                    data-bs-title="{{ __('Rejected') }}">
                    Rejected
                </a>
                <a href="{{ route('concession.change_status', [$concession->id, 'Approved']) }}"
                    class="mx-1 btn btn-outline-primary"  data-bs-title="{{ __('Approved') }}">
                    Approved
                </a>
            </form>
        </div>

        <!-- Buttons to trigger forms -->
        <a href="javascript:void(0);" id="reject-btn" class="mx-1 btn btn-outline-danger" 
            data-bs-title="{{ __('Rejected') }}">
            Rejected
        </a>
        <a href="javascript:void(0);" id="rollback-btn" class="mx-1 btn btn-outline-danger" 
            data-bs-title="{{ __('RollBack') }}">
            RollBack
        </a>
        <a href="{{ route('concession.change_status', [$concession->id, 'Approved']) }}" id="approve-btn"
            class="mx-1 btn btn-outline-primary"  data-bs-title="{{ __('Approved') }}">
            Approved 
        </a>
    </div>
    @endif
    <script>
        $(document).ready(function() {
            // Show the rejection form and hide the buttons
            $('#reject-btn').on('click', function() {
                $(this).hide();
                $('#rollback-btn').hide();
                $('#approve-btn').hide();
                $('#rejection-form').removeClass('d-none').addClass('fadeInEaseIn');
            });

            // Show the rollback form and hide the buttons
            $('#rollback-btn').on('click', function() {
                $(this).hide();
                $('#reject-btn').hide();
                $('#approve-btn').hide();
                $('#rollback-form').removeClass('d-none').addClass('fadeInEaseIn');
            });

            // Switch between rollback and rejection form
            $('#rollback-rol-btn').on('click', function() {
                $('#rejection-form').addClass('d-none');
                $('#rollback-form').removeClass('d-none');
            });

            $('#reject-rol-btn').on('click', function() {
                $('#rollback-form').addClass('d-none');
                $('#rejection-form').removeClass('d-none');
            });
        });
    </script>

    <style>
        @keyframes fadeInEaseIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }
        .d-none {
            display: none;
        }
    </style>
