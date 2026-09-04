@extends('layouts.admin')
@section('page-title')
    {{ __('StudyPack Challan Edit') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('studypackchallan.index') }}">{{ __('StudyPack Challan') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection

@push('script-page')
    <script>
        var isCompany = {{ $isCompany ? 'true' : 'false' }};
        var canEditPrice = isCompany; // branch users can change quantity but not price
        var fixedStudentId = '{{ $challan->student_id }}';
        var currentBranchId = '{{ $challan->owned_by }}';

        function recalcTotals() {
            var subTotal = 0;
            $('#items-table tbody tr').each(function() {
                var $row = $(this);
                var checked = $row.find('.item-check').is(':checked');
                var qty = parseFloat($row.find('.quantity').val()) || 0;
                var price = parseFloat($row.find('.price').val()) || 0;
                var amount = checked ? (qty * price) : 0;
                $row.find('.amount').text(amount.toFixed(2));
            });
            $('#items-table tbody tr').each(function() {
                var $row = $(this);
                if ($row.find('.item-check').is(':checked')) {
                    subTotal += parseFloat($row.find('.amount').text()) || 0;
                }
            });
            $('.totalAmount').text(subTotal.toFixed(2));
            $("input[name='total_amount_display']").val(subTotal.toFixed(2));
        }

        // Enable/disable a row's inputs based on its checkbox.
        // Quantity is editable by both roles. Price is company-only: branch price
        // inputs are rendered readonly and must keep submitting their current value,
        // so we never touch them here.
        function toggleRow($row) {
            var checked = $row.find('.item-check').is(':checked');
            $row.find('.quantity').prop('disabled', !checked);
            if (canEditPrice) {
                $row.find('.price').prop('disabled', !checked);
            }
            if (checked) {
                $row.removeClass('text-muted');
            } else {
                $row.addClass('text-muted');
            }
        }

        $(document).ready(function() {
            // Initialise row states.
            $('#items-table tbody tr').each(function() {
                toggleRow($(this));
            });
            recalcTotals();

            $(document).on('change', '.item-check', function() {
                toggleRow($(this).closest('tr'));
                recalcTotals();
            });

            $(document).on('keyup change', '.quantity, .price', function() {
                recalcTotals();
            });

            // ---- Company only: branch -> reload classes ----
            $(document).on('change', '#branch_select', function() {
                var branchId = $(this).val();
                currentBranchId = branchId;
                if (!branchId) return;
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('#token').val()
                    },
                    url: "{{ route('branch.session_class') }}",
                    type: "POST",
                    data: {
                        id: branchId
                    },
                    dataType: 'json',
                    success: function(result) {
                        if (result.status === 'success') {
                            $('#class_select').empty();
                            $('#class_select').append($('<option>', {
                                value: '',
                                text: 'Select Class'
                            }));
                            for (var j = 0; j < result.class.length; j++) {
                                var cls = result.class[j];
                                $('#class_select').append($('<option>', {
                                    value: cls.id,
                                    text: cls.name
                                }));
                            }
                            // Force class re-selection + re-validation.
                            verifyStudentInClass();
                        }
                    }
                });
            });

            // ---- Company only: class -> verify the fixed student belongs ----
            $(document).on('change', '#class_select', function() {
                verifyStudentInClass();
            });

            function verifyStudentInClass() {
                if (!isCompany) return;
                var classId = $('#class_select').val();
                var branchId = $('#branch_select').val();
                var $warn = $('#student-class-warning');
                if (!classId) {
                    $warn.addClass('d-none');
                    $('#update-btn').prop('disabled', false);
                    return;
                }
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('#token').val()
                    },
                    url: "{{ route('class.students') }}",
                    type: "POST",
                    data: {
                        class_id: classId,
                        branch_id: branchId,
                        type: 'regular'
                    },
                    dataType: 'json',
                    success: function(result) {
                        var found = false;
                        if (result.status === 'success' && result.students) {
                            if (result.students.hasOwnProperty(fixedStudentId)) {
                                found = true;
                            }
                        }
                        if (found) {
                            $warn.addClass('d-none');
                            $('#update-btn').prop('disabled', false);
                        } else {
                            $warn.removeClass('d-none');
                            $('#update-btn').prop('disabled', true);
                        }
                    }
                });
            }
        });
    </script>
@endpush

@section('content')
    <div class="row">
        {{ Form::open(['route' => ['studypackchallan.update', $challan->id], 'method' => 'PUT', 'class' => 'w-100']) }}
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        {{-- Challan No (locked) --}}
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="form-group">
                                {{ Form::label('challanNo', __('Challan No'), ['class' => 'form-label']) }}
                                {{ Form::text('challanNo_display', $challan->challanNo, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                            </div>
                        </div>

                        {{-- Voucher / JV number (locked) --}}
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="form-group">
                                {{ Form::label('voucher', __('Voucher (JV) No'), ['class' => 'form-label']) }}
                                {{ Form::text('voucher_display', optional($challan->voucher)->journal_id ?? $challan->voucher_id, ['class' => 'form-control', 'readonly' => 'readonly']) }}
                            </div>
                        </div>

                        {{-- Student (fixed / disabled) --}}
                        <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                            <div class="form-group">
                                {{ Form::label('student', __('Student'), ['class' => 'form-label']) }}
                                {{ Form::text('student_display', trim((optional($student)->roll_no ? optional($student)->roll_no . ' - ' : '') . optional($student)->stdname . ' s/d/o ' . optional($student)->fathername), ['class' => 'form-control', 'disabled' => 'disabled']) }}
                            </div>
                        </div>
                    </div>

                    <div class="row mt-1">
                        {{-- Branch (company editable, branch locked) --}}
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="form-group">
                                {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
                                @if ($isCompany)
                                    {{ Form::select('branches', $branches, $challan->owned_by, ['class' => 'form-control', 'id' => 'branch_select']) }}
                                @else
                                    {{ Form::text('branch_display', optional($challan->branch)->name ?? '-', ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                @endif
                            </div>
                        </div>

                        {{-- Class (company editable, branch locked) --}}
                        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-12 col-12">
                            <div class="form-group">
                                {{ Form::label('class', __('Class'), ['class' => 'form-label']) }}
                                @if ($isCompany)
                                    {{ Form::select('class', $classes, $challan->class_id, ['class' => 'form-control', 'id' => 'class_select']) }}
                                @else
                                    {{ Form::text('class_display', optional($challan->class)->name ?? '-', ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                @endif
                            </div>
                        </div>

                        {{-- Fee Month (both roles) --}}
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12">
                            <div class="form-group">
                                {{ Form::label('fee_month', __('Challan Month'), ['class' => 'form-label']) }}
                                {{ Form::month('fee_month', \Carbon\Carbon::parse($challan->fee_month)->format('Y-m'), ['class' => 'form-control']) }}
                            </div>
                        </div>

                        {{-- Issue Date (both roles) --}}
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12">
                            <div class="form-group">
                                {{ Form::label('issue_date', __('Issue Date'), ['class' => 'form-label']) }}
                                {{ Form::date('issue_date', $challan->issue_date ? \Carbon\Carbon::parse($challan->issue_date)->format('Y-m-d') : null, ['class' => 'form-control']) }}
                            </div>
                        </div>

                        {{-- Due Date (both roles) --}}
                        <div class="col-xl-2 col-lg-2 col-md-6 col-sm-12 col-12">
                            <div class="form-group">
                                {{ Form::label('due_date', __('Due Date'), ['class' => 'form-label']) }}
                                {{ Form::date('due_date', $challan->due_date ? \Carbon\Carbon::parse($challan->due_date)->format('Y-m-d') : null, ['class' => 'form-control']) }}
                            </div>
                        </div>
                    </div>

                    @if ($isCompany)
                        <div id="student-class-warning" class="alert alert-danger py-2 mt-2 d-none">
                            {{ __('Student not in this selected class.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="d-inline-block mb-0">
                    {{ __('Challan Items') }}
                    <small class="text-muted">— {{ optional($studyPack)->title }}
                        ({{ __('check to include, uncheck to remove') }})</small>
                </h5>
                <div class="text-end">
                    <input type="button" value="{{ __('Cancel') }}"
                        onclick="location.href = '{{ route('studypackchallan.index') }}';"
                        class="btn btn-outline-secondary">&nbsp;
                    <button type="submit" id="update-btn" class="btn btn-primary text-white">{{ __('Update') }}</button>
                </div>
            </div>
            <div class="card">
                <div class="card-body table-border-style" style="max-height: 600px; overflow: auto;">
                    <div class="table-responsive">
                        <table class="table mb-0" id="items-table">
                            <thead>
                                <tr>
                                    <th style="width: 4%;">{{ __('#') }}</th>
                                    <th style="width: 5%;">{{ __('Include') }}</th>
                                    <th>{{ __('Item') }}</th>
                                    <th style="width: 15%;">{{ __('Quantity') }}</th>
                                    <th style="width: 20%;">{{ __('Price') }}</th>
                                    <th class="text-end" style="width: 15%;">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    <tr>
                                        <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                        <td class="text-center align-middle">
                                            <input type="checkbox" class="item-check" name="selected[{{ $row['product_id'] }}]"
                                                value="1" {{ $row['checked'] ? 'checked' : '' }}>
                                        </td>
                                        <td class="align-middle">
                                            {{ $row['product_name'] }}
                                        </td>
                                        <td>
                                            <input type="number" min="0" step="any"
                                                class="form-control quantity"
                                                name="qty[{{ $row['product_id'] }}]"
                                                value="{{ $row['qty'] }}">
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <input type="number" min="0" step="any"
                                                    class="form-control price"
                                                    name="price[{{ $row['product_id'] }}]"
                                                    value="{{ $row['price'] }}" {{ $isCompany ? '' : 'readonly' }}>
                                                <span class="input-group-text bg-transparent">{{ \Auth::user()->currencySymbol() }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end amount align-middle">0.00</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            {{ __('No items found in the attached studypack.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4"></td>
                                    <td class="text-end blue-text">
                                        <strong>{{ __('Total Amount') }} ({{ \Auth::user()->currencySymbol() }})</strong>
                                    </td>
                                    <td class="text-end blue-text totalAmount">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <input type="hidden" name="total_amount_display" value="0">
                </div>
            </div>
        </div>

        {{ Form::close() }}
    </div>
@endsection
