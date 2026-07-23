<script>
    (function() {
        var $form = $('.expense-voucher-form').last();
        if (!$form.length) {
            return;
        }

        var $branch = $form.find('#branches');
        var $bank = $form.find('#bank_id');
        var $amount = $form.find('[name="amount"]');
        var $submit = $form.find('button[type="submit"]');
        var selectedBankId = @json((string) ($selectedBankId ?? ''));
        var requestToken = 0;

        function bankBalanceWarning() {
            var $warning = $form.find('.head-imprest-balance-warning');

            if (!$warning.length) {
                $warning = $('<small>', {
                    class: 'text-danger head-imprest-balance-warning d-block mt-1'
                }).insertAfter($amount);
            }

            return $warning;
        }

        function selectedBankBalance() {
            var balance = $bank.find('option:selected').data('balance');

            if (balance === undefined || balance === null || balance === '') {
                return null;
            }

            return parseFloat(balance);
        }

        function validateHeadImprestAmount(showMessage) {
            var amount = parseFloat($amount.val());
            var balance = selectedBankBalance();
            var $warning = bankBalanceWarning();

            $warning.text('');
            $amount.removeClass('is-invalid');

            if (!amount || balance === null || isNaN(balance)) {
                $submit.prop('disabled', false);
                return true;
            }

            if (amount > balance) {
                var formattedBalance = $bank.find('option:selected').data('formatted-balance') || balance.toFixed(2);
                var message = '{{ __('Expense amount cannot be greater than the selected bank balance. Available balance:') }} ' + formattedBalance;

                $amount.addClass('is-invalid');
                $warning.text(message);
                $submit.prop('disabled', true);

                if (showMessage && typeof toastr !== 'undefined') {
                    toastr.error(message);
                }

                return false;
            }

            $submit.prop('disabled', false);
            return true;
        }

        function setBankOptions(banks, selectedId) {
            $bank.empty();
            $bank.append($('<option>', {
                value: '',
                text: '{{ __('Select Bank Account') }}'
            }));

            $.each(banks || [], function(index, bank) {
                $bank.append($('<option>', {
                    value: bank.id,
                    text: bank.text,
                    selected: selectedId && String(selectedId) === String(bank.id)
                }).attr({
                    'data-balance': bank.balance,
                    'data-formatted-balance': bank.formatted_balance
                }));
            });

            $bank.prop('disabled', false).trigger('change');
            validateHeadImprestAmount(false);
        }

        function loadHeadImprestBanks(branchId, selectedId) {
            var token = ++requestToken;

            $bank.prop('disabled', true)
                .empty()
                .append($('<option>', {
                    value: '',
                    text: '{{ __('Loading bank accounts...') }}'
                }))
                .trigger('change');

            $.ajax({
                url: '{{ route('expense-voucher.head-imprest-banks') }}',
                type: 'GET',
                data: {
                    branch_id: branchId
                },
                success: function(response) {
                    if (token !== requestToken) {
                        return;
                    }

                    if (response && response.success) {
                        setBankOptions(response.banks, selectedId);
                        return;
                    }

                    setBankOptions([], '');
                    if (typeof toastr !== 'undefined') {
                        toastr.error((response && response.message) || '{{ __('Unable to fetch Head Imprest bank accounts.') }}');
                    }
                },
                error: function(xhr) {
                    if (token !== requestToken) {
                        return;
                    }

                    setBankOptions([], '');
                    if (typeof toastr !== 'undefined') {
                        toastr.error((xhr.responseJSON && xhr.responseJSON.message) || '{{ __('Unable to fetch Head Imprest bank accounts.') }}');
                    }
                }
            });
        }

        $form.off('change.headImprestBank', '#branches').on('change.headImprestBank', '#branches', function() {
            selectedBankId = '';
            loadHeadImprestBanks($(this).val(), selectedBankId);
        });

        $form.off('change.headImprestAmount input.headImprestAmount', '#bank_id, [name="amount"]')
            .on('change.headImprestAmount input.headImprestAmount', '#bank_id, [name="amount"]', function() {
                validateHeadImprestAmount(false);
            });

        function blockInvalidHeadImprestSubmit(event) {
            if (!validateHeadImprestAmount(true)) {
                event.preventDefault();
                event.stopImmediatePropagation();
                return false;
            }

            return true;
        }

        $form.off('submit.headImprestAmount').on('submit.headImprestAmount', blockInvalidHeadImprestSubmit);
        if ($form[0]) {
            $form[0].addEventListener('submit', blockInvalidHeadImprestSubmit, true);
        }

        if ($branch.val()) {
            loadHeadImprestBanks($branch.val(), selectedBankId);
        }
    })();
</script>
