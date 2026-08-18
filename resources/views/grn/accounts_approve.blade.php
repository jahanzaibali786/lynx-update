@extends('layouts.admin')

@section('page-title')
    {{ __('GRN Voucher Approval') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('grn.accounts_index') }}">{{ __('Accounts GRN Approval') }}</a></li>
    <li class="breadcrumb-item">{{ $voucherPreview['grn_number'] ?? __('GRN') }}</li>
@endsection

@push('script-page')
    <script>
        $(function() {
            var accountOptions = @json($chartAccountOptions);
            var branchOptions = @json($branchOptions);
            var grnAmount = parseFloat({!! json_encode(number_format((float) $grn->getSubTotal(), 2, '.', '')) !!}) || 0;
            var defaultDate = {!! json_encode($grn->grn_date) !!};
            var defaultRef = {!! json_encode($grn->reference_no ?: ($voucherPreview['grn_number'] ?? '')) !!};
            var defaultBranch = {!! json_encode($grn->owned_by) !!};

            function accountSelect(name, selected) {
                var html = '<select name="' + name + '[account_id]" class="form-control form-control-sm select custom-select account-select" style="width:190px;max-width:220px;" required>';
                html += '<option value="">{{ __('Select Account') }}</option>';
                $.each(accountOptions, function(id, label) {
                    html += '<option value="' + id + '"' + (String(selected || '') === String(id) ? ' selected' : '') + '>' + label + '</option>';
                });
                html += '</select>';
                return html;
            }

            function groupAccountSelect(groupKey, selected) {
                var html = '<select class="form-control form-control-sm select custom-select group-account-select" data-group-key="' + groupKey + '" style="width:190px;max-width:220px;">';
                html += '<option value="">{{ __('Select Account') }}</option>';
                $.each(accountOptions, function(id, label) {
                    html += '<option value="' + id + '"' + (String(selected || '') === String(id) ? ' selected' : '') + '>' + label + '</option>';
                });
                html += '</select>';
                return html;
            }

            function groupRowHtml(groupKey, accountId) {
                return '<tr class="group-row table-light" data-group-key="' + groupKey + '">' +
                    '<td colspan="3" class="align-middle">' +
                        '<div class="small text-muted text-uppercase">{{ __('Account Group') }}</div>' +
                        '<div class="fw-semibold">{{ __('New Group') }}</div>' +
                    '</td>' +
                    '<td style="width:190px;max-width:220px;">' +
                        groupAccountSelect(groupKey, accountId || '') +
                    '</td>' +
                    '<td style="min-width:220px;">' +
                        '<div class="small text-muted">{{ __('Group Total') }}</div>' +
                        '<div class="group-count fw-semibold">1 {{ __('entry') }}</div>' +
                        '<button type="button" class="btn btn-link btn-sm px-0 toggle-group-lines" data-group-key="' + groupKey + '" data-expanded="0">' +
                            '<i class="ti ti-chevron-down"></i> <span class="toggle-label">{{ __('Show Items') }}</span>' +
                        '</button>' +
                    '</td>' +
                    '<td class="text-end fw-semibold group-debit">0.00</td>' +
                    '<td class="text-end fw-semibold group-credit">0.00</td>' +
                    '<td class="text-center"></td>' +
                '</tr>';
            }

            function branchSelect(name, selected) {
                var html = '<select name="' + name + '[branch_id]" class="form-control form-control-sm branch-select" style="width:170px;max-width:170px;" required>';
                $.each(branchOptions, function(id, label) {
                    html += '<option value="' + id + '"' + (String(selected || defaultBranch) === String(id) ? ' selected' : '') + '>' + label + '</option>';
                });
                html += '</select>';
                return html;
            }

            function rowHtml(index, line, removable, groupKey) {
                line = line || {};
                var name = 'accounts[' + index + ']';
                var debit = parseFloat(line.debit || 0) || 0;
                var credit = parseFloat(line.credit || 0) || 0;
                var accountId = line.account_id || '';
                return '<tr class="voucher-line" data-index="' + index + '" data-group-key="' + (groupKey || '') + '">' +
                    '<td><input type="date" name="' + name + '[tra_date]" class="form-control form-control-sm line-date" value="' + (line.tra_date || defaultDate || '') + '"></td>' +
                    '<td><input type="text" name="' + name + '[ref_no]" class="form-control form-control-sm line-ref" value="' + $('<div>').text(line.ref_no || defaultRef || '').html() + '" placeholder="Ref No"></td>' +
                    '<td style="width:170px;">' + branchSelect(name, line.branch_id || defaultBranch) + '</td>' +
                    '<td style="width:190px;max-width:220px;">' + accountSelect(name, accountId) + '</td>' +
                    '<td style="min-width:220px;"><textarea name="' + name + '[description]" class="form-control form-control-sm line-description" rows="2">' + $('<div>').text(line.description || '').html() + '</textarea></td>' +
                    '<td><input type="number" name="' + name + '[debit]" class="form-control form-control-sm text-end line-debit" min="0" step="0.01" value="' + (line.debit || '') + '"' + (credit > 0 ? ' disabled' : '') + '></td>' +
                    '<td><input type="number" name="' + name + '[credit]" class="form-control form-control-sm text-end line-credit" min="0" step="0.01" value="' + (line.credit || '') + '"' + (debit > 0 ? ' disabled' : '') + '></td>' +
                    '<td class="text-center">' + (removable ? '<button type="button" class="btn btn-sm btn-danger remove-line"><i class="ti ti-trash"></i></button>' : '') + '</td>' +
                    '</tr>';
            }

            function syncDebitCredit(row) {
                var debitInput = row.find('.line-debit');
                var creditInput = row.find('.line-credit');
                var debit = parseFloat(debitInput.val()) || 0;
                var credit = parseFloat(creditInput.val()) || 0;

                creditInput.prop('disabled', debit > 0);
                debitInput.prop('disabled', credit > 0);
            }

            function syncAllDebitCredit() {
                $('#voucher-lines tr.voucher-line').each(function() {
                    syncDebitCredit($(this));
                });
            }

            function refreshCustomSelect(selectEl) {
                if (!selectEl) {
                    return;
                }

                if (selectEl.customSelectInstance && typeof selectEl.customSelectInstance.destroy === 'function') {
                    selectEl.customSelectInstance.destroy();
                    selectEl.customSelectInstance = null;
                }

                if (typeof CustomSelect !== 'undefined' && CustomSelect.create) {
                    selectEl.customSelectInstance = CustomSelect.create(selectEl);
                }
            }

            function initCustomSelects(container) {
                if (typeof CustomSelect !== 'undefined' && CustomSelect.initContainer) {
                    CustomSelect.initContainer(container || document);
                }
            }

            function reindexLines() {
                $('#voucher-lines tr.voucher-line').each(function(index) {
                    $(this).attr('data-index', index);
                    $(this).find('[name]').each(function() {
                        this.name = this.name.replace(/accounts\[[^\]]+\]/, 'accounts[' + index + ']');
                    });
                });
            }

            function refreshGroupTotals(groupKey) {
                var debit = 0;
                var credit = 0;
                var count = 0;

                $('#voucher-lines tr.voucher-line[data-group-key="' + groupKey + '"]').each(function() {
                    debit += parseFloat($(this).find('.line-debit').val()) || 0;
                    credit += parseFloat($(this).find('.line-credit').val()) || 0;
                    count++;
                });

                $('#voucher-lines tr.group-row[data-group-key="' + groupKey + '"] .group-debit').text(debit.toFixed(2));
                $('#voucher-lines tr.group-row[data-group-key="' + groupKey + '"] .group-credit').text(credit.toFixed(2));
                $('#voucher-lines tr.group-row[data-group-key="' + groupKey + '"] .group-count').text(count + ' ' + (count === 1 ? '{{ __('entry') }}' : '{{ __('entries') }}'));
            }

            function refreshAllGroupTotals() {
                $('#voucher-lines tr.group-row').each(function() {
                    refreshGroupTotals($(this).data('groupKey'));
                });
            }

            function syncGroupAccount(groupKey, accountId) {
                var $groupSelect = $('#voucher-lines .group-account-select[data-group-key="' + groupKey + '"]');
                if ($groupSelect.length) {
                    $groupSelect.val(accountId);
                    refreshCustomSelect($groupSelect[0]);
                }

                $('#voucher-lines tr.voucher-line[data-group-key="' + groupKey + '"] .account-select').each(function() {
                    $(this).val(accountId);
                    refreshCustomSelect(this);
                });
            }

            function refreshTotals() {
                var debit = 0;
                var credit = 0;

                $('.line-debit').each(function() {
                    debit += parseFloat($(this).val()) || 0;
                });
                $('.line-credit').each(function() {
                    credit += parseFloat($(this).val()) || 0;
                });

                var balanced = debit.toFixed(2) === credit.toFixed(2);
                var withinAmount = Math.max(debit, credit) <= grnAmount;

                $('#total-debit').text(debit.toFixed(2));
                $('#total-credit').text(credit.toFixed(2));
                $('#voucher-amount-display, #voucher-amount').val(Math.max(debit, credit).toFixed(2));
                refreshAllGroupTotals();
                $('#voucher-warning').toggleClass('d-none', balanced && withinAmount);
                $('#approve-btn').prop('disabled', !balanced || !withinAmount);
            }

            $(document).on('input', '.line-debit, .line-credit', function() {
                var row = $(this).closest('tr');
                if ($(this).hasClass('line-debit') && parseFloat($(this).val()) > 0) {
                    row.find('.line-credit').val('');
                }
                if ($(this).hasClass('line-credit') && parseFloat($(this).val()) > 0) {
                    row.find('.line-debit').val('');
                }
                syncDebitCredit(row);
                refreshTotals();
            });

            $(document).off('change', '.group-account-select').on('change', '.group-account-select', function() {
                syncGroupAccount($(this).data('groupKey'), $(this).val());
            });

            $(document).off('click', '.toggle-group-lines').on('click', '.toggle-group-lines', function() {
                var groupKey = $(this).data('groupKey');
                var expanded = $(this).data('expanded') === 1;
                $('#voucher-lines tr.voucher-line[data-group-key="' + groupKey + '"]').toggleClass('d-none', expanded);
                $(this).data('expanded', expanded ? 0 : 1);
                $(this).find('i').toggleClass('ti-chevron-down ti-chevron-up');
                $(this).find('.toggle-label').text(expanded ? '{{ __('Show Items') }}' : '{{ __('Hide Items') }}');
            });

            $('#add-line').off('click').on('click', function() {
                var nextIndex = $('#voucher-lines tr.voucher-line').length;
                var $newRow = $(rowHtml(nextIndex, {
                    tra_date: defaultDate,
                    ref_no: defaultRef
                }, true, '')).removeClass('d-none group-child-row').removeAttr('data-group-key');
                $('#voucher-lines').append($newRow);
                initCustomSelects($newRow[0]);
                refreshTotals();
            });

            $(document).on('click', '.remove-line', function() {
                var $row = $(this).closest('tr');
                var groupKey = $row.data('groupKey');
                $row.remove();
                reindexLines();
                if (groupKey) {
                    refreshGroupTotals(groupKey);
                    if ($('#voucher-lines tr.voucher-line[data-group-key="' + groupKey + '"]').length === 0) {
                        $('#voucher-lines tr.group-row[data-group-key="' + groupKey + '"]').remove();
                    }
                }
                refreshTotals();
            });

            syncAllDebitCredit();
            initCustomSelects(document);
            refreshAllGroupTotals();
            refreshTotals();
        });
    </script>
@endpush

@section('content')
  <style>
      .custom-select-display {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 13px;
            padding: 6px 6px;
        }
    </style>
    @php
        $debitLines = $voucherPreview['debit_lines'] ?? [];
        $creditLine = $voucherPreview['credit_line'] ?? null;
        $voucherTotal = (string) ($voucherPreview['total'] ?? '0.00');
        $grnTotal = number_format((float) $grn->getSubTotal(), 2, '.', '');
        $allLines = $debitLines;
        if ($creditLine) {
            $allLines[] = $creditLine;
        }
        $groupedLines = [];
        foreach ($allLines as $lineIndex => $line) {
            $accountId = optional($line['account'])->id ?: 'line-' . $lineIndex;
            $groupKey = 'group-' . $accountId;

            if (! isset($groupedLines[$groupKey])) {
                $groupedLines[$groupKey] = [
                    'key' => $groupKey,
                    'account_id' => optional($line['account'])->id,
                    'account' => $line['account'] ?? null,
                    'lines' => [],
                    'tra_date' => $line['tra_date'] ?? $grn->grn_date,
                    'ref_no' => $line['ref_no'] ?? ($grn->reference_no ?: ($voucherPreview['grn_number'] ?? '')),
                    'branch_id' => $line['branch_id'] ?? $grn->owned_by,
                    'description' => trim($line['description'] ?? ''),
                    'debit_total' => 0,
                    'credit_total' => 0,
                ];
            }

            $groupedLines[$groupKey]['lines'][] = $line;
            if (empty($groupedLines[$groupKey]['description']) && ! empty($line['description'])) {
                $groupedLines[$groupKey]['description'] = trim($line['description']);
            }
            $groupedLines[$groupKey]['debit_total'] += (float) ($line['debit'] ?? 0);
            $groupedLines[$groupKey]['credit_total'] += (float) ($line['credit'] ?? 0);
        }
        $lineIndex = 0;
    @endphp

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ __('Voucher Review') }} - {{ $voucherPreview['grn_number'] ?? '' }}</h5>
                    <a href="{{ route('grn.accounts_index') }}" class="btn btn-sm btn-outline-secondary">
                        {{ __('Back') }}
                    </a>
                </div>
                <div class="card-body">
                    {{ Form::open(['route' => ['grn.accounts_approve', $grn->id], 'method' => 'POST']) }}
                    <div class="border rounded p-3 mb-3">
                        <div class="row">
                            <div class="col-lg-3 col-md-3">
                                <div class="form-group">
                                    {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
                                    {{ Form::select('branches', $branchOptions, $grn->owned_by, ['class' => 'form-control', 'id' => 'branches']) }}
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <div class="form-group">
                                    {{ Form::label('voucher_type', __('Voucher Type'), ['class' => 'form-label']) }}
                                    {{ Form::select('voucher_type', ['JV' => 'Journal Voucher', 'CPV' => 'Cash Payment Voucher', 'BPV' => 'Bank Payment Voucher', 'CRV' => 'Cash Receipt Voucher', 'BRV' => 'Bank Receipt Voucher'], 'JV', ['class' => 'form-control', 'id' => 'voucher_type']) }}
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <div class="form-group">
                                    {{ Form::label('journal_number', __('Journal Number'), ['class' => 'form-label']) }}
                                    <input type="text" class="form-control" id="journal-number-inp" value="{{ $displayVoucherNumber }}" readonly>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <div class="form-group">
                                    {{ Form::label('category_type_id', __('Voucher Category Type'), ['class' => 'form-label']) }}
                                    {{ Form::select('category_type_id', $voucherCategoryTypes, null, ['class' => 'form-control', 'id' => 'category_type_id']) }}
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <div class="form-group">
                                    {{ Form::label('date', __('Transaction Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('date', $grn->grn_date, ['class' => 'form-control', 'required' => 'required']) }}
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <div class="form-group">
                                    {{ Form::label('payment_date', __('Payment Date'), ['class' => 'form-label']) }}
                                    {{ Form::date('payment_date', null, ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <div class="form-group">
                                    {{ Form::label('payment_mode', __('Payment Mode'), ['class' => 'form-label']) }}
                                    {{ Form::select('payment_mode', ['' => 'Select Payment Mode', 'dd' => 'DD', 'cd' => 'CD', 'online' => 'Online', 'bank-transfer' => 'Bank Transfer', 'chq' => 'Cheque', 'others' => 'Others'], null, ['class' => 'form-control', 'id' => 'payment_mode']) }}
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <div class="form-group">
                                    {{ Form::label('bank_id', __('Bank Name'), ['class' => 'form-label']) }}
                                    {{ Form::select('bank_id', $bankAccounts, null, ['class' => 'form-control custom-select', 'id' => 'bank_id']) }}
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <div class="form-group">
                                    {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
                                    <input type="number" class="form-control" id="voucher-amount-display" value="{{ number_format((float) $voucherTotal, 2, '.', '') }}" step="0.01" disabled>
                                    <input type="hidden" name="amount" id="voucher-amount" value="{{ number_format((float) $voucherTotal, 2, '.', '') }}">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <div class="form-group">
                                    {{ Form::label('reference', __('Payment Reference'), ['class' => 'form-label']) }}
                                    {{ Form::text('reference', $grn->reference_no ?: ($voucherPreview['grn_number'] ?? ''), ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <div class="form-group">
                                    {{ Form::label('transaction_no', __('Transaction / Invoice No'), ['class' => 'form-label']) }}
                                    {{ Form::text('transaction_no', $grn->purchase_order ?? '', ['class' => 'form-control']) }}
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3">
                                <div class="form-group">
                                    {{ Form::label('party_type', __('Party Type'), ['class' => 'form-label']) }}
                                    {{ Form::select('party_type', ['Vender' => 'Vendor'], 'Vender', ['class' => 'form-control', 'disabled' => 'disabled']) }}
                                    <input type="hidden" name="user_type" value="Vender">
                                    <input type="hidden" name="user_id" value="{{ $grn->vendor_id }}">
                                </div>
                            </div>
                            <div class="col-lg-12 col-md-12">
                                <div class="form-group">
                                    {{ Form::label('narration', __('Note for Payment'), ['class' => 'form-label']) }}
                                    {{ Form::textarea('narration', __('Stock received against').' '.($voucherPreview['grn_number'] ?? ''), ['class' => 'form-control', 'rows' => '2']) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th class="text-uppercase" style="width:120px;">{{ __('Date') }}</th>
                                    <th class="text-uppercase" style="width:110px;">{{ __('Ref No') }}</th>
                                    <th class="text-uppercase" style="width:170px;">{{ __('Branch') }}</th>
                                        <th class="text-uppercase" style="width:190px;max-width:220px;">{{ __('Account') }}</th>
                                        <th class="text-uppercase" style="min-width:220px;">{{ __('Description') }}</th>
                                    <th class="text-uppercase text-end" style="width:120px;">{{ __('Debit') }}</th>
                                    <th class="text-uppercase text-end" style="width:120px;">{{ __('Credit') }}</th>
                                    <th style="width:70px;"></th>
                                </tr>
                            </thead>
                            <tbody id="voucher-lines">
                                @foreach ($groupedLines as $group)
                                    <tr class="group-row table-light" data-group-key="{{ $group['key'] }}">
                                        <td colspan="3" class="align-middle">
                                            <div class="small text-muted text-uppercase">{{ __('Account Group') }}</div>
                                            <div class="fw-semibold">{{ optional($group['account'])->code ?? '' }} {{ optional($group['account'])->name ?? '' }}</div>
                                        </td>
                                        <td style="width:190px;max-width:220px;">
                                            <select class="form-control form-control-sm select custom-select group-account-select" data-group-key="{{ $group['key'] }}" style="width:190px;max-width:220px;">
                                                <option value="">{{ __('Select Account') }}</option>
                                                @foreach ($chartAccountOptions as $id => $label)
                                                    <option value="{{ $id }}" {{ (string) $group['account_id'] === (string) $id ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td style="min-width:220px;">
                                            <div class="small text-muted">{{ __('Group Total') }}</div>
                                            <div class="group-count fw-semibold">{{ count($group['lines']) }} {{ count($group['lines']) === 1 ? __('entry') : __('entries') }}</div>
                                            <button type="button" class="btn btn-link btn-sm px-0 toggle-group-lines" data-group-key="{{ $group['key'] }}" data-expanded="0">
                                                <i class="ti ti-chevron-down"></i> <span class="toggle-label">{{ __('Show Items') }}</span>
                                            </button>
                                        </td>
                                        <td class="text-end fw-semibold group-debit">{{ number_format((float) $group['debit_total'], 2, '.', '') }}</td>
                                        <td class="text-end fw-semibold group-credit">{{ number_format((float) $group['credit_total'], 2, '.', '') }}</td>
                                        <td class="text-center"></td>
                                    </tr>
                                    @foreach ($group['lines'] as $groupLine)
                                        <tr class="voucher-line group-child-row d-none" data-index="{{ $lineIndex }}" data-group-key="{{ $group['key'] }}">
                                            <td>
                                                <input type="date" name="accounts[{{ $lineIndex }}][tra_date]" class="form-control form-control-sm line-date" value="{{ $groupLine['tra_date'] ?? $group['tra_date'] }}">
                                            </td>
                                            <td>
                                                <input type="text" name="accounts[{{ $lineIndex }}][ref_no]" class="form-control form-control-sm line-ref" value="{{ $groupLine['ref_no'] ?? $group['ref_no'] }}" placeholder="{{ __('Ref No') }}">
                                            </td>
                                            <td style="width:170px;">
                                                {{ Form::select(
                                                    'accounts['.$lineIndex.'][branch_id]',
                                                    $branchOptions,
                                                    $groupLine['branch_id'] ?? $group['branch_id'],
                                                    ['class' => 'form-control form-control-sm branch-select', 'style' => 'width:170px;max-width:170px;', 'required' => 'required']
                                                ) }}
                                            </td>
                                            <td style="width:190px;max-width:220px;">
                                                {{ Form::select(
                                                    'accounts['.$lineIndex.'][account_id]',
                                                    ['' => __('Select Account')] + $chartAccountOptions,
                                                    optional($groupLine['account'])->id,
                                                    ['class' => 'form-control form-control-sm select custom-select account-select', 'style' => 'width:190px;max-width:220px;', 'required' => 'required']
                                                ) }}
                                            </td>
                                            <td style="min-width:220px;">
                                                <textarea name="accounts[{{ $lineIndex }}][description]" class="form-control form-control-sm line-description" rows="2">{{ $groupLine['description'] ?? '' }}</textarea>
                                            </td>
                                            <td>
                                                <input type="number" name="accounts[{{ $lineIndex }}][debit]" class="form-control form-control-sm text-end line-debit" min="0" step="0.01" value="{{ number_format((float) ($groupLine['debit'] ?? 0), 2, '.', '') }}" {{ ((float) ($groupLine['credit'] ?? 0)) > 0 ? 'disabled' : '' }}>
                                            </td>
                                            <td>
                                                <input type="number" name="accounts[{{ $lineIndex }}][credit]" class="form-control form-control-sm text-end line-credit" min="0" step="0.01" value="{{ number_format((float) ($groupLine['credit'] ?? 0), 2, '.', '') }}" {{ ((float) ($groupLine['debit'] ?? 0)) > 0 ? 'disabled' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-danger remove-line"><i class="ti ti-trash"></i></button>
                                            </td>
                                        </tr>
                                        @php $lineIndex++; @endphp
                                    @endforeach
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">{{ __('Voucher Amount') }}</th>
                                    <th class="text-end" id="total-debit">{{ number_format((float) $voucherTotal, 2) }}</th>
                                    <th class="text-end" id="total-credit">{{ number_format((float) $voucherTotal, 2) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-line">
                            <i class="ti ti-plus"></i> {{ __('Add Row') }}
                        </button>
                        <div class="d-flex gap-2">
                            <a href="{{ route('grn.accounts_index') }}" class="btn btn-outline-secondary">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit" class="btn btn-success" id="approve-btn" onclick="return confirm('{{ __('Approve this GRN from Accounts? Stock will be updated and voucher will be created.') }}')">
                                {{ __('Approve') }}
                            </button>
                        </div>
                    </div>

                    <div class="alert alert-danger mt-3 d-none" id="voucher-warning">
                        {{ __('Voucher must be balanced and voucher amount cannot be greater than GRN amount.') }}
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
