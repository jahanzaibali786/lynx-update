@extends('layouts.admin')
@section('page-title')
    {{ __('Journal Entry Create') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Double Entry') }}</li>
    <li class="breadcrumb-item">{{ __('Vouchers') }}</li>
    <li class="breadcrumb-item">{{ __('Journal Entry') }}</li>
@endsection

@push('script-page')
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script>
        // ─── Safe destroy helper ──────────────────────────────────────────────────────
        // Destroys the CustomSelect instance AND removes any orphaned wrappers
        // that may have been left behind by previous create() calls.
        // ─── Safe destroy helper ──────────────────────────────────────────────────────
        // Pass the select element to destroy. It removes the CustomSelect wrapper
        // that belongs to THIS element only, without touching any other selects.
        function destroyCustomSelect(selectEl) {
            if (!selectEl) return;

            // Destroy tracked instance if present
            if (selectEl.customSelectInstance) {
                selectEl.customSelectInstance.destroy(); // removes its own wrapper from DOM
                selectEl.customSelectInstance = null;
                return; // wrapper already removed by destroy(), we're done
            }

            // Fallback: instance ref was lost — find wrapper by data attribute
            // The wrapper is inserted AFTER the select in the DOM (nextSibling)
            var $next = $(selectEl).next('.custom-select-wrapper');
            if ($next.length) {
                $next.remove();
            }
        }
        // ─── State ────────────────────────────────────────────────────────────────────
        var journalEntries = [];
        var rowCounter = 0;
        var MAX_OPEN_ROWS = 4;

        // ─── Static data from Blade ───────────────────────────────────────────────────
        var BRANCHES = @json($branches);
        var DEPARTMENTS = @json($departments->mapWithKeys(fn($d) => [$d->id => $d->name]));

        // Build account options HTML once (reused per row)
        var ACCOUNT_OPTS = '<option value="">— Select Account —</option>';
        @foreach ($chartAccounts as $chartAccount)
            ACCOUNT_OPTS += '<option value="{{ $chartAccount['id'] }}"' +
                ' data-category="{{ $chartAccount['category'] ?? 'general' }}"' +
                ' data-path="[{{ $chartAccount['code'] ?? $chartAccount['id'] }}] {{ addslashes($chartAccount['code_name']) }}">' +
                '{{ addslashes($chartAccount['code_name']) }}</option>';
            @foreach ($subAccounts as $subAccount)
                @if ($chartAccount['id'] == $subAccount['account'])
                    ACCOUNT_OPTS += '<option value="{{ $subAccount['id'] }}"' +
                        ' data-category="{{ $subAccount['category'] ?? 'general' }}"' +
                        ' data-path="[{{ $subAccount['code'] ?? $subAccount['id'] }}] {{ addslashes($chartAccount['code_name']) }} \u2192 {{ addslashes($subAccount['code_name']) }}">' +
                        '\u00a0\u00a0\u00a0{{ addslashes($subAccount['code_name']) }}</option>';
                @endif
            @endforeach
        @endforeach

        // Build branch options HTML once
        var BRANCH_OPTS = '<option value="">— Branch —</option>';
        $.each(BRANCHES, function(id, name) {
            BRANCH_OPTS += '<option value="' + id + '">' + name + '</option>';
        });

        // Build department options HTML once
        var DEPT_OPTS = '<option value="">— Department —</option>';
        $.each(DEPARTMENTS, function(id, name) {
            DEPT_OPTS += '<option value="' + id + '">' + name + '</option>';
        });

        // ─── Voucher number ───────────────────────────────────────────────────────────
        $(document).on('change', '#voucher_type, #branches', function() {
            getVoucherNumber($('#voucher_type').val(), $('#branches').val());
        });

        function getVoucherNumber(vt, bid) {
            var labels = {
                jv: 'Journal Number',
                cpv: 'Cash Payment Voucher Number',
                bpv: 'Bank Payment Voucher Number',
                crv: 'Cash Receipt Voucher Number',
                brv: 'Bank Receipt Voucher Number'
            };
            $('#journal-number').text(labels[vt] || 'Journal Number');
            $('#journal-number-inp').val('');
            if (!vt) return;
            $.ajax({
                url: '{{ route('getVoucherNumber') }}',
                type: 'GET',
                data: {
                    voucher_type: vt,
                    branch_id: bid
                },
                success: function(r) {
                    $('#journal-number-inp').val(r.voucher_number);
                },
                error: function() {
                    show_toastr('error', 'Failed to fetch voucher number', 'error');
                }
            });
        }

        // ─── Add line button ──────────────────────────────────────────────────────────
        $(document).on('click', '#addAccountBtn', function() {
            var openRows = $('#inline-entry-tbody tr[data-row-id]').length;
            if (openRows >= MAX_OPEN_ROWS) {
                show_toastr('warning', 'Please confirm the existing rows before adding more (max ' + MAX_OPEN_ROWS +
                    ' open).', 'warning');
                return;
            }
            appendInlineRow();
        });

        function appendInlineRow() {
            rowCounter++;
            var rid = rowCounter;

            var row = '<tr data-row-id="' + rid + '" data-cat="general" class="inline-edit-row">' +

                // ① Account + category fields stacked inside same td
                '<td class="col-account">' +
                '<select class="form-control form-control-sm row-account custom-select" data-rid="' + rid + '">' +
                ACCOUNT_OPTS +
                '</select>' +
                '<div class="path-pill" id="path-pill-' + rid + '"></div>' +
                '<div id="catcell-' + rid + '" class="catcell-wrap"></div>' +
                '</td>' +

                // ② Debit
                '<td class="col-debit">' +
                '<input type="number" class="form-control form-control-sm row-debit" data-rid="' + rid +
                '" placeholder="0.00" min="0" step="0.01">' +
                '</td>' +

                // ③ Credit
                '<td class="col-credit">' +
                '<input type="number" class="form-control form-control-sm row-credit" data-rid="' + rid +
                '" placeholder="0.00" min="0" step="0.01">' +
                '</td>' +

                // ④ Description
                '<td class="col-desc">' +
                '<input type="text" class="form-control form-control-sm row-desc" data-rid="' + rid +
                '" placeholder="Description…">' +
                '</td>' +

                // ⑤ Actions
                '<td class="col-actions" style="white-space:nowrap;">' +
                '<button type="button" class="btn btn-sm btn-primary confirm-row-btn" data-rid="' + rid +
                '" title="Confirm"><i class="ti ti-check"></i></button> ' +
                '<button type="button" class="btn btn-sm btn-outline-danger discard-row-btn" data-rid="' + rid +
                '" title="Discard"><i class="ti ti-x" style="color: #fff !important;"></i></button>' +
                '</td>' +

                '</tr>';

            $('#inline-entry-tbody').append(row);
            CustomSelect.initContainer(
                $('#inline-entry-tbody tr[data-row-id="' + rid + '"]')[0]
            );
            $('#empty-row').hide();
        }

        // ─── Account change → render category fields ──────────────────────────────────
        $(document).on('change', '.row-account', function() {
            var rid = $(this).data('rid');
            var opt = $(this).find('option:selected');
            var cat = opt.data('category') || 'general';
            var path = opt.data('path') || '';

            $('tr[data-row-id="' + rid + '"]').attr('data-cat', cat);
            var pill = $('#path-pill-' + rid);
            pill.text($(this).val() ? path : '');

            renderCatFields(rid, cat);
        });

        function renderCatFields(rid, cat) {
            var cell = $('#catcell-' + rid);
            if (cat === 'hr') {
                cell.html(
                    '<div class="catcell-fields mt-1">' +
                    '<div class="catcell-row">' +
                    '<select class="form-control form-control-sm hr-branch custom-select" data-rid="' + rid + '">' +
                    BRANCH_OPTS +
                    '</select>' +
                    '<select class="form-control form-control-sm hr-dept custom-select" data-rid="' + rid + '">' +
                    DEPT_OPTS +
                    '</select>' +
                    '</div>' +
                    '<div class="catcell-row mt-1">' +
                    '<select class="form-control form-control-sm hr-desig custom-select" data-rid="' + rid +
                    '" disabled>' +
                    '<option value="">— Designation —</option>' +
                    '</select>' +
                    '<select class="form-control form-control-sm hr-emp custom-select" data-rid="' + rid +
                    '" disabled>' +
                    '<option value="">— Employee —</option>' +
                    '</select>' +
                    '</div>' +
                    '</div>'
                );
            } else if (cat === 'student') {
                cell.html(
                    '<div class="catcell-fields mt-1">' +
                    '<div class="catcell-row">' +
                    '<select class="form-control form-control-sm stu-branch custom-select" data-rid="' + rid + '">' +
                    BRANCH_OPTS +
                    '</select>' +
                    '<select class="form-control form-control-sm stu-student custom-select" data-rid="' + rid +
                    '" disabled>' +
                    '<option value="">— Student —</option>' +
                    '</select>' +
                    '</div>' +
                    '</div>'
                );
            } else if (cat === 'inventory') {
                cell.html(
                    '<div class="catcell-fields mt-1">' +
                    '<div class="catcell-row">' +
                    '<select class="form-control form-control-sm inv-branch custom-select" data-rid="' + rid + '">' +
                    BRANCH_OPTS +
                    '</select>' +
                    '<select class="form-control form-control-sm inv-vendor custom-select" data-rid="' + rid +
                    '" disabled>' +
                    '<option value="">— Vendor —</option>' +
                    '</select>' +
                    '</div>' +
                    '</div>'
                );
            } else {
                cell.html('');
            }
        }

        $(document).on('change', '.hr-dept', function() {
            var rid = $(this).data('rid');
            var deptId = $(this).val();

            // Only reset downstream selects — never touch the select that fired this event
            var $desig = $('.hr-desig[data-rid="' + rid + '"]');
            var $emp = $('.hr-emp[data-rid="' + rid + '"]');

            destroyCustomSelect($desig[0]);
            $desig.html('<option value="">— Designation —</option>').prop('disabled', true);
            destroyCustomSelect($emp[0]);
            $emp.html('<option value="">— Employee —</option>').prop('disabled', true);

            CustomSelect.create($desig[0]);
            CustomSelect.create($emp[0]);

            if (!deptId) return;

            $.ajax({
                url: '{{ route('employee.json') }}',
                type: 'POST',
                data: {
                    department_id: deptId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    var opts = '<option value="">— Designation —</option>';
                    $.each(data, function(k, v) {
                        opts += '<option value="' + k + '">' + v + '</option>';
                    });
                    destroyCustomSelect($desig[0]);
                    $desig.html(opts).prop('disabled', false);
                    CustomSelect.create($desig[0]);
                },
                error: function() {
                    show_toastr('error', 'Failed to load designations', 'error');
                }
            });
        });

        $(document).on('change', '.hr-desig', function() {
            var rid = $(this).data('rid');
            var deptId = $('.hr-dept[data-rid="' + rid + '"]').val();
            var desigId = $(this).val();

            // Only reset downstream — employee is the only dependent here
            var $emp = $('.hr-emp[data-rid="' + rid + '"]');
            destroyCustomSelect($emp[0]);
            $emp.html('<option value="">— Employee —</option>').prop('disabled', true);
            CustomSelect.create($emp[0]);

            if (!deptId || !desigId) return;

            $.ajax({
                url: '{{ route('employeedesiganddeprtment') }}',
                type: 'GET',
                data: {
                    department_id: deptId,
                    designation_id: desigId
                },
                success: function(data) {
                    var opts = '<option value="">— Employee —</option>';
                    $.each(data, function(id, name) {
                        opts += '<option value="' + id + '">' + name + '</option>';
                    });
                    destroyCustomSelect($emp[0]);
                    $emp.html(opts).prop('disabled', false);
                    CustomSelect.create($emp[0]);
                },
                error: function() {
                    show_toastr('error', 'Failed to load employees', 'error');
                }
            });
        });

        $(document).on('change', '.stu-branch', function() {
            var rid = $(this).data('rid'),
                bid = $(this).val();

            var $stu = $('.stu-student[data-rid="' + rid + '"]');
            destroyCustomSelect($stu[0]);
            $stu.html('<option value="">— Student —</option>').prop('disabled', true);
            CustomSelect.create($stu[0]);

            if (!bid) return;

            $.ajax({
                url: '{{ route('get.branch-students') }}',
                type: 'GET',
                data: {
                    branch_id: bid
                },
                success: function(r) {
                    var opts = '<option value="">— Student —</option>';
                    $.each(r.students, function(id, name) {
                        opts += '<option value="' + id + '">' + name + '</option>';
                    });
                    destroyCustomSelect($stu[0]);
                    $stu.html(opts).prop('disabled', false);
                    CustomSelect.create($stu[0]);
                },
                error: function() {
                    show_toastr('error', 'Failed to load students', 'error');
                }
            });
        });

        $(document).on('change', '.inv-branch', function() {
            var rid = $(this).data('rid'),
                bid = $(this).val();

            var $vendor = $('.inv-vendor[data-rid="' + rid + '"]');
            destroyCustomSelect($vendor[0]);
            $vendor.html('<option value="">— Vendor —</option>').prop('disabled', true);
            CustomSelect.create($vendor[0]);

            if (!bid) return;

            $.ajax({
                url: '{{ route('get.vendors') }}',
                type: 'GET',
                data: {
                    branch_id: bid
                },
                success: function(data) {
                    var opts = '<option value="">— Vendor —</option>';
                    $.each(data, function(i, v) {
                        opts += '<option value="' + v.id + '">' + v.name + '</option>';
                    });
                    destroyCustomSelect($vendor[0]);
                    $vendor.html(opts).prop('disabled', false);
                    CustomSelect.create($vendor[0]);
                },
                error: function() {
                    show_toastr('error', 'Failed to load vendors', 'error');
                }
            });
        });
        // ─── Debit / Credit mutual exclusion ─────────────────────────────────────────
        $(document).on('input', '.row-debit', function() {
            var rid = $(this).data('rid');
            if (parseFloat($(this).val()) > 0) {
                $('.row-credit[data-rid="' + rid + '"]').val('').prop('disabled', true);
            } else {
                $('.row-credit[data-rid="' + rid + '"]').prop('disabled', false);
            }
        });
        $(document).on('input', '.row-credit', function() {
            var rid = $(this).data('rid');
            if (parseFloat($(this).val()) > 0) {
                $('.row-debit[data-rid="' + rid + '"]').val('').prop('disabled', true);
            } else {
                $('.row-debit[data-rid="' + rid + '"]').prop('disabled', false);
            }
        });

        // ─── Confirm row ──────────────────────────────────────────────────────────────
        $(document).on('click', '.confirm-row-btn', function() {
            var rid = $(this).data('rid');
            var tr = $('tr[data-row-id="' + rid + '"]');
            var cat = tr.attr('data-cat') || 'general';
            var accSel = $('.row-account[data-rid="' + rid + '"]');
            var accOpt = accSel.find('option:selected');
            var accId = accSel.val();
            var path = accOpt.data('path') || accOpt.text();
            var debit = parseFloat($('.row-debit[data-rid="' + rid + '"]').val()) || 0;
            var credit = parseFloat($('.row-credit[data-rid="' + rid + '"]').val()) || 0;
            var desc = $('.row-desc[data-rid="' + rid + '"]').val();

            if (!accId) {
                show_toastr('error', 'Please select an account.', 'error');
                return;
            }
            if (debit === 0 && credit === 0) {
                show_toastr('error', 'Enter a debit or credit amount.', 'error');
                return;
            }

            var meta = {},
                metaLabel = '';
            if (cat === 'hr') {
                meta = {
                    branch_id: $('.hr-branch[data-rid="' + rid + '"]').val(),
                    branch_name: $('.hr-branch[data-rid="' + rid + '"] option:selected').text(),
                    dept_id: $('.hr-dept[data-rid="' + rid + '"]').val(),
                    dept_name: $('.hr-dept[data-rid="' + rid + '"] option:selected').text(),
                    designation_id: $('.hr-desig[data-rid="' + rid + '"]').val(),
                    designation_name: $('.hr-desig[data-rid="' + rid + '"] option:selected').text(),
                    employee_id: $('.hr-emp[data-rid="' + rid + '"]').val(),
                    employee_name: $('.hr-emp[data-rid="' + rid + '"] option:selected').text(),
                };
                metaLabel = [meta.branch_name, meta.dept_name, meta.designation_name, meta.employee_name]
                    .filter(function(v) {
                        return v && !v.startsWith('—');
                    }).join(' › ');
            } else if (cat === 'student') {
                meta = {
                    branch_id: $('.stu-branch[data-rid="' + rid + '"]').val(),
                    branch_name: $('.stu-branch[data-rid="' + rid + '"] option:selected').text(),
                    student_id: $('.stu-student[data-rid="' + rid + '"]').val(),
                    student_name: $('.stu-student[data-rid="' + rid + '"] option:selected').text(),
                };
                metaLabel = [meta.branch_name, meta.student_name]
                    .filter(function(v) {
                        return v && !v.startsWith('—');
                    }).join(' › ');
            } else if (cat === 'inventory') {
                meta = {
                    branch_id: $('.inv-branch[data-rid="' + rid + '"]').val(),
                    branch_name: $('.inv-branch[data-rid="' + rid + '"] option:selected').text(),
                    vendor_id: $('.inv-vendor[data-rid="' + rid + '"]').val(),
                    vendor_name: $('.inv-vendor[data-rid="' + rid + '"] option:selected').text(),
                };
                metaLabel = [meta.branch_name, meta.vendor_name]
                    .filter(function(v) {
                        return v && !v.startsWith('—');
                    }).join(' › ');
            }

            var entry = {
                id: Date.now(),
                account_id: accId,
                path,
                cat,
                meta,
                metaLabel,
                debit,
                credit,
                desc
            };
            journalEntries.push(entry);
            tr.replaceWith(buildLockedRow(entry));
            renderHiddenInputs();
            updateTotals();
            checkEmptyState();
        });

        function buildLockedRow(e) {
            var badges = {
                hr: '<span class="cat-badge cat-hr">HR</span>',
                student: '<span class="cat-badge cat-student">Student</span>',
                inventory: '<span class="cat-badge cat-inventory">Inventory</span>',
                general: '<span class="cat-badge cat-general">General</span>'
            };
            var meta = e.metaLabel ? '<div class="entry-meta">&#8594; ' + e.metaLabel + '</div>' : '';
            var dr = e.debit ? '<span class="text-danger fw-semibold">' + e.debit.toFixed(2) + '</span>' :
                '<span class="text-muted">—</span>';
            var cr = e.credit ? '<span class="text-primary fw-semibold">' + e.credit.toFixed(2) + '</span>' :
                '<span class="text-muted">—</span>';
            return '<tr data-entry-id="' + e.id + '" class="confirmed-row">' +
                '<td>' + (badges[e.cat] || '') + '<code class="account-path">' + e.path + '</code>' + meta + '</td>' +
                '<td class="text-right">' + dr + '</td>' +
                '<td class="text-right">' + cr + '</td>' +
                '<td style="font-size:13px;color:#6c757d;">' + (e.desc || '—') + '</td>' +
                '<td class="text-center">' +
                '<a href="#" class="edit-entry-btn text-primary me-1" data-id="' + e.id +
                '" title="Edit"><i class="ti ti-pencil"></i></a>' +
                '<a href="#" class="remove-entry-btn text-danger" data-id="' + e.id +
                '" title="Remove"><i class="ti ti-trash"></i></a>' +
                '</td>' +
                '</tr>';
        }

        // ─── Discard open row ─────────────────────────────────────────────────────────
        $(document).on('click', '.discard-row-btn', function() {
            $('tr[data-row-id="' + $(this).data('rid') + '"]').remove();
            checkEmptyState();
        });

        // ─── Remove confirmed entry ───────────────────────────────────────────────────
        $(document).on('click', '.remove-entry-btn', function(e) {
            e.preventDefault();
            var id = parseInt($(this).data('id'));
            if (!confirm('Remove this entry?')) return;
            journalEntries = journalEntries.filter(function(e) {
                return e.id !== id;
            });
            $('tr[data-entry-id="' + id + '"]').remove();
            renderHiddenInputs();
            updateTotals();
            checkEmptyState();
        });

        // ─── Helpers ──────────────────────────────────────────────────────────────────
        function renderHiddenInputs() {
            var w = $('#hidden-inputs');
            w.empty();
            $.each(journalEntries, function(i, e) {
                var p = 'accounts[' + i + ']';
                w.append('<input type="hidden" name="' + p + '[account_id]"  value="' + e.account_id + '">');
                w.append('<input type="hidden" name="' + p + '[debit]"        value="' + e.debit + '">');
                w.append('<input type="hidden" name="' + p + '[credit]"       value="' + e.credit + '">');
                w.append('<input type="hidden" name="' + p + '[description]"  value="' + e.desc + '">');
                w.append('<input type="hidden" name="' + p + '[category]"     value="' + e.cat + '">');
                if (e.cat === 'hr') {
                    w.append('<input type="hidden" name="' + p + '[branch_id]"      value="' + (e.meta.branch_id ||
                        '') + '">');
                    w.append('<input type="hidden" name="' + p + '[dept_id]"        value="' + (e.meta.dept_id ||
                        '') + '">');
                    w.append('<input type="hidden" name="' + p + '[designation_id]" value="' + (e.meta
                        .designation_id || '') + '">');
                    w.append('<input type="hidden" name="' + p + '[employee_id]"    value="' + (e.meta
                        .employee_id || '') + '">');
                } else if (e.cat === 'student') {
                    w.append('<input type="hidden" name="' + p + '[branch_id]"  value="' + (e.meta.branch_id ||
                        '') + '">');
                    w.append('<input type="hidden" name="' + p + '[student_id]" value="' + (e.meta.student_id ||
                        '') + '">');
                } else if (e.cat === 'inventory') {
                    w.append('<input type="hidden" name="' + p + '[branch_id]"  value="' + (e.meta.branch_id ||
                        '') + '">');
                    w.append('<input type="hidden" name="' + p + '[vendor_id]"  value="' + (e.meta.vendor_id ||
                        '') + '">');
                }
            });
        }

        function updateTotals() {
            var d = 0,
                c = 0;
            $.each(journalEntries, function(i, e) {
                d += e.debit;
                c += e.credit;
            });
            $('.totalDebit').text(d.toFixed(2));
            $('.totalCredit').text(c.toFixed(2));
        }

        function checkEmptyState() {
            var hasAny = $('#inline-entry-tbody tr[data-row-id], #inline-entry-tbody tr[data-entry-id]').length > 0;
            $('#empty-row').toggle(!hasAny);
        }

        // ─── Keyboard shortcuts for inline rows ──────────────────────────────────────
        // Enter  → confirm new row  OR  save edit
        // Escape → discard new row  OR  cancel edit
        // Backspace/Delete (on empty input) → cancel edit only (don't discard a new row mid-fill)

        $(document).on('keydown', '.row-account, .row-debit, .row-credit, .row-desc, .catcell-wrap select', function(e) {

            var rid = $(this).data('rid') || $(this).closest('[data-rid]').data('rid');
            if (!rid) return;

            // SHIFT + ENTER => Confirm current row + add new row
            if (e.key === 'Enter' && e.shiftKey) {
                e.preventDefault();

                var confirmBtn = $('.confirm-row-btn[data-rid="' + rid + '"]');

                // Confirm current row first
                confirmBtn.trigger('click');

                // Add new row after short delay
                setTimeout(function() {
                    var openRows = $('#inline-entry-tbody tr[data-row-id]').length;

                    if (openRows < MAX_OPEN_ROWS) {
                        appendInlineRow();

                        // Focus first field of newly added row
                        $('#inline-entry-tbody tr[data-row-id]:last')
                            .find('.row-account')
                            .focus();
                    }
                }, 100);

            }

            // ENTER => Confirm row only
            else if (e.key === 'Enter') {
                e.preventDefault();
                $('.confirm-row-btn[data-rid="' + rid + '"]').trigger('click');

            }

            // ESC => Discard row
            else if (e.key === 'Escape') {
                e.preventDefault();
                $('.discard-row-btn[data-rid="' + rid + '"]').trigger('click');
            }
        });

        $(document).on('keydown', '.edit-debit, .edit-credit', function(e) {
            var id = $(this).data('id');
            if (!id) return;

            if (e.key === 'Enter') {
                e.preventDefault();
                $('.save-edit-btn[data-id="' + id + '"]').trigger('click');

            } else if (e.key === 'Escape') {
                e.preventDefault();
                $('.cancel-edit-btn[data-id="' + id + '"]').trigger('click');

            } else if ((e.key === 'Backspace' || e.key === 'Delete') && $(this).val() === '') {
                // Only cancel if the field is already empty (user cleared it then pressed delete again)
                e.preventDefault();
                $('.cancel-edit-btn[data-id="' + id + '"]').trigger('click');
            }
        });

        // ─── Edit confirmed entry (make debit/credit editable inline) ────────────────
        $(document).on('click', '.edit-entry-btn', function(e) {
            e.preventDefault();
            var id = parseInt($(this).data('id'));
            var entry = journalEntries.find(function(e) {
                return e.id === id;
            });
            if (!entry) return;

            var tr = $('tr[data-entry-id="' + id + '"]');

            // Replace debit/credit tds with editable inputs, keep account & desc as-is
            var drVal = entry.debit || '';
            var crVal = entry.credit || '';

            // td index: 0=account, 1=debit, 2=credit, 3=desc, 4=actions
            // Whichever has a value is enabled; the other is disabled.
            // Both enable again once the filled one is cleared.
            var drDisabled = (drVal === '' && crVal !== '') ? 'disabled' : '';
            var crDisabled = (crVal === '' && drVal !== '') ? 'disabled' : '';

            tr.find('td').eq(1).html(
                '<input type="number" class="form-control form-control-sm edit-debit" ' +
                'value="' + drVal + '" placeholder="0.00" min="0" step="0.01" data-id="' + id + '" ' +
                drDisabled + ' style="width:90px;">'
            );
            tr.find('td').eq(2).html(
                '<input type="number" class="form-control form-control-sm edit-credit" ' +
                'value="' + crVal + '" placeholder="0.00" min="0" step="0.01" data-id="' + id + '" ' +
                crDisabled + ' style="width:90px;">'
            );
            tr.find('td').eq(4).html(
                '<a href="#" class="save-edit-btn text-success me-1" data-id="' + id +
                '" title="Save"><i class="ti ti-check"></i></a>' +
                '<a href="#" class="cancel-edit-btn text-muted" data-id="' + id +
                '" title="Cancel"><i class="ti ti-x"></i></a>'
            );
        });

        // Debit/credit mutual exclusion in edit mode
        // — field with a value stays enabled and locks the other
        // — clearing the value re-enables both
        $(document).on('input', '.edit-debit', function() {
            var val = $(this).val();
            var other = $(this).closest('tr').find('.edit-credit');
            if (val !== '' && parseFloat(val) >= 0) {
                other.val('').prop('disabled', true);
            } else {
                other.prop('disabled', false);
            }
        });
        $(document).on('input', '.edit-credit', function() {
            var val = $(this).val();
            var other = $(this).closest('tr').find('.edit-debit');
            if (val !== '' && parseFloat(val) >= 0) {
                other.val('').prop('disabled', true);
            } else {
                other.prop('disabled', false);
            }
        });

        // Save edit
        $(document).on('click', '.save-edit-btn', function(e) {
            e.preventDefault();
            var id = parseInt($(this).data('id'));
            var tr = $('tr[data-entry-id="' + id + '"]');
            var debit = parseFloat(tr.find('.edit-debit').val()) || 0;
            var credit = parseFloat(tr.find('.edit-credit').val()) || 0;
            if (debit === 0 && credit === 0) {
                show_toastr('error', 'Enter a debit or credit amount.', 'error');
                return;
            }
            var entry = journalEntries.find(function(e) {
                return e.id === id;
            });
            entry.debit = debit;
            entry.credit = credit;
            tr.replaceWith(buildLockedRow(entry));
            renderHiddenInputs();
            updateTotals();
        });

        // Cancel edit — just redraw the locked row as-is
        $(document).on('click', '.cancel-edit-btn', function(e) {
            e.preventDefault();
            var id = parseInt($(this).data('id'));
            var entry = journalEntries.find(function(e) {
                return e.id === id;
            });
            $('tr[data-entry-id="' + id + '"]').replaceWith(buildLockedRow(entry));
        });

        // ─── Form submit ──────────────────────────────────────────────────────────────
        $(document).on('submit', '#journal-form', function(e) {
            e.preventDefault();
            var d = parseFloat($('.totalDebit').text()) || 0;
            var c = parseFloat($('.totalCredit').text()) || 0;
            if (journalEntries.length === 0) {
                show_toastr('error', 'Please confirm at least one account entry.', 'error');
                return;
            }
            if (d !== c) {
                show_toastr('error', 'Total Debit (' + d.toFixed(2) + ') ≠ Total Credit (' + c.toFixed(2) + ').',
                    'error');
                return;
            }
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: new FormData(this),
                processData: false,
                contentType: false,
                success: function(r) {
                    if (r.status === 'success') {
                        show_toastr('success', r.message, 'success');
                        if (r.redirect) setTimeout(function() {
                            window.location.href = r.redirect;
                        }, 300);
                    } else {
                        show_toastr('error', r.message, 'error');
                    }
                },
                error: function(xhr) {
                    show_toastr('error', xhr.responseJSON?.message || 'Unexpected error.', 'error');
                }
            });
        });
    </script>

    <style>
        /* ══ Entries table ═══════════════════════════════════════════════════════════ */
        #entries-table-wrap {
            overflow-x: auto;
        }

        #inline-entry-table {
            min-width: 860px;
            table-layout: auto;
        }

        /* Column sizing */
        #inline-entry-table .col-debit,
        #inline-entry-table .col-credit {
            width: 100px;
        }

        #inline-entry-table .col-desc {
            min-width: 130px;
        }

        #inline-entry-table .col-actions {
            width: 80px;
        }

        /* Editable row highlight */
        .inline-edit-row {
            background: #f5f8ff !important;
        }

        .inline-edit-row:hover {
            background: #edf2ff !important;
        }

        .inline-edit-row td {
            vertical-align: top;
            padding: 8px 6px;
        }

        /* Confirmed row */
        .confirmed-row {
            background: #fff;
        }

        .confirmed-row:hover {
            background: #fafafa;
        }

        .confirmed-row td {
            vertical-align: middle;
            padding: 8px 6px;
        }

        /* Small inputs */
        .inline-edit-row .form-control-sm {
            height: 31px;
            font-size: 12px;
            padding: 3px 7px;
        }

        /* Category fields stacked inside account td */
        .catcell-wrap {}

        .catcell-fields {}

        .catcell-row {
            display: flex;
            gap: 5px;
        }

        .catcell-row .form-control-sm {
            flex: 1 1 0;
            min-width: 0;
        }

        /* Make account col wider to accommodate stacked selects */
        #inline-entry-table .col-account {
            min-width: 280px;
            width: 280px;
        }

        /* Path pill */
        .path-pill {
            font-size: 10px;
            color: #6c757d;
            font-family: monospace;
            margin-top: 3px;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 175px;
        }

        /* Badges */
        .cat-badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            margin-right: 4px;
            vertical-align: middle;
        }

        .cat-hr {
            background: #cfe2ff;
            color: #084298;
        }

        .cat-student {
            background: #d1e7dd;
            color: #0a3622;
        }

        .cat-inventory {
            background: #fff3cd;
            color: #664d03;
        }

        .cat-general {
            background: #e2e3e5;
            color: #41464b;
        }

        /* Account code in locked rows */
        .account-path {
            font-size: 11px;
            background: transparent;
            padding: 0;
            color: inherit;
            vertical-align: middle;
        }

        .entry-meta {
            font-size: 11px;
            color: #999;
            margin-top: 2px;
        }

        /* Add-line footer row */
        .add-row-footer {
            background: #f8f9fa;
        }

        .add-row-footer td {
            padding: 8px 10px;
            border-top: 2px dashed #dee2e6;
        }
    </style>
@endpush

@section('content')
    {{ Form::open(['url' => 'journal-entry', 'class' => 'w-100', 'id' => 'journal-form']) }}
    <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
    <div id="hidden-inputs"></div>

    {{-- ── Header card ────────────────────────────────────────────────────────── --}}
    <div class="row mt-4">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 col-md-4">
                            <div class="form-group">
                                {{ Form::label('branches', __('Branch'), ['class' => 'form-label']) }}
                                {{ Form::select('branches', $branches, null, ['class' => 'form-control', 'id' => 'branches']) }}
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <div class="form-group">
                                {{ Form::label('voucher_type', __('Voucher Type'), ['class' => 'form-label']) }}
                                {{ Form::select(
                                    'voucher_type',
                                    [
                                        'jv' => 'Journal Voucher',
                                        'cpv' => 'Cash Payment Voucher',
                                        'bpv' => 'Bank Payment Voucher',
                                        'crv' => 'Cash Receipt Voucher',
                                        'brv' => 'Bank Receipt Voucher',
                                    ],
                                    null,
                                    ['class' => 'form-control', 'id' => 'voucher_type'],
                                ) }}
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <div class="form-group">
                                {{ Form::label('journal_number', __('Journal Number'), ['class' => 'form-label', 'id' => 'journal-number']) }}
                                <input type="text" class="form-control" id="journal-number-inp"
                                    value="{{ \Auth::user()->journalNumberFormat($journalId) }}" readonly>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <div class="form-group">
                                {{ Form::label('date', __('Transaction Date'), ['class' => 'form-label']) }}
                                {{ Form::date('date', null, ['class' => 'form-control', 'required' => 'required']) }}
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <div class="form-group">
                                {{ Form::label('mode', __('Payment Mode'), ['class' => 'form-label']) }}
                                {{ Form::select(
                                    'mode',
                                    [
                                        'dd' => 'DD',
                                        'cd' => 'CD',
                                        'bank-transfer' => 'Bank Transfer',
                                        'chq' => 'Cheque',
                                        'others' => 'Others',
                                    ],
                                    null,
                                    ['class' => 'form-control'],
                                ) }}
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <div class="form-group">
                                {{ Form::label('amount', __('Amount'), ['class' => 'form-label']) }}
                                {{ Form::number('amount', '', ['class' => 'form-control', 'step' => '0.01']) }}
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6">
                            <div class="form-group">
                                {{ Form::label('reference', __('Reference'), ['class' => 'form-label']) }}
                                {{ Form::text('reference', '', ['class' => 'form-control']) }}
                            </div>
                        </div>
                        <div class="col-lg-8 col-md-6">
                            <div class="form-group">
                                {{ Form::label('narration', __('Narration'), ['class' => 'form-label']) }}
                                {{ Form::textarea('narration', '', ['class' => 'form-control', 'rows' => '2']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Inline entries ──────────────────────────────────────────────────────── --}}
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{{ __('Account Entries') }}</h6>
                </div>
                <div id="entries-table-wrap" class="card-body table-border-style pt-0">
                    <table class="table table-sm mb-0" id="inline-entry-table">
                        <thead>
                            <tr>
                                <th class="col-account">{{ __('Account') }}</th>
                                <th class="col-debit text-right">{{ __('Debit') }}</th>
                                <th class="col-credit text-right">{{ __('Credit') }}</th>
                                <th class="col-desc">{{ __('Description') }}</th>
                                <th class="col-actions"></th>
                            </tr>
                        </thead>
                        <tbody id="inline-entry-tbody">
                            <tr id="empty-row">
                                <td colspan="5" class="text-center text-muted py-4" style="font-size:13px;">
                                    Click <strong>+ Add Account Line</strong> below to start adding entries.
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="add-row-footer">
                                <td colspan="7">
                                    <button style="color: #fff !important;" type="button" id="addAccountBtn"
                                        class="btn btn-outline-primary btn-sm">
                                        <i class="ti ti-plus" style="color: #fff !important;"></i>
                                        {{ __('Add Account Line') }}
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2"></td>
                                <td class="text-right">
                                    <strong>{{ __('Total Credit') }} ({{ \Auth::user()->currencySymbol() }})</strong>
                                </td>
                                <td class="text-right totalCredit fw-bold" style="text-align: end !important;">0.00</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="2"></td>
                                <td class="text-right">
                                    <strong>{{ __('Total Debit') }} ({{ \Auth::user()->currencySymbol() }})</strong>
                                </td>
                                <td class="text-right totalDebit fw-bold" style="text-align: end !important;">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" onclick="location.href = '{{ route('journal-entry.index') }}';"
            class="btn btn-light">
        <input type="submit" value="{{ __('Save') }}" class="btn btn-outline-primary">
    </div>

    {{ Form::close() }}
@endsection
