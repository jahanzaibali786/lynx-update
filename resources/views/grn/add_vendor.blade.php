<div class="modal-body">
    <div id="grn-vendor-form" class="p-2" data-action="{{ route('vender.store') }}">
        <div class="row g-3">
            <div class="col-6">
                <label class="form-label mb-0 w-100 text-start" for="grn_vendor_first_name">{{ __('First Name') }}<span class="text-danger">*</span></label>
                <input type="text" id="grn_vendor_first_name" class="form-control" required>
            </div>
            <div class="col-6">
                <label class="form-label mb-0 w-100 text-start" for="grn_vendor_last_name">{{ __('Last Name') }}<span class="text-danger">*</span></label>
                <input type="text" id="grn_vendor_last_name" class="form-control" required>
            </div>
            <div class="col-6">
                <label class="form-label mb-0 w-100 text-start" for="grn_vendor_main_phone">{{ __('Phone') }}<span class="text-danger">*</span></label>
                <input type="text" id="grn_vendor_main_phone" class="form-control" required>
            </div>
            <div class="col-6">
                <label class="form-label mb-0 w-100 text-start" for="grn_vendor_email">{{ __('Email') }}<span class="text-danger">*</span></label>
                <input type="email" id="grn_vendor_email" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label mb-0 w-100 text-start" for="grn_vendor_account_id">{{ __('Account') }}<span class="text-danger">*</span></label>
                <select id="grn_vendor_account_id" class="form-control select custom-select" required>
                    <option value="">{{ __('Select Account') }}</option>
                    @foreach ($vendorAccounts as $chartAccount)
                        <option value="{{ $chartAccount->id }}">{{ $chartAccount->code . ' - ' . $chartAccount->name }}</option>
                        @foreach ($vendorSubAccounts as $subAccount)
                            @if ($chartAccount->id == $subAccount->account)
                                <option value="{{ $subAccount->id }}">&nbsp;&nbsp;&nbsp;{{ $subAccount->code . ' - ' . $subAccount->name }}</option>
                            @endif
                        @endforeach
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-12 text-end">
            <button type="button" id="grn-vendor-submit" class="btn btn-outline-primary">{{ __('Add Vendor') }}</button>
        </div>
    </div>
    <script>
        $(document).off('click', '#grn-vendor-submit').on('click', '#grn-vendor-submit', function(e) {
            e.preventDefault();
            const $form = $('#grn-vendor-form');
            const $button = $(this);
            $button.prop('disabled', true).text('Saving...');

            $.ajax({
                url: $form.data('action'),
                type: 'POST',
                data: {
                    first_name: $('#grn_vendor_first_name').val(),
                    last_name: $('#grn_vendor_last_name').val(),
                    main_phone: $('#grn_vendor_main_phone').val(),
                    email: $('#grn_vendor_email').val(),
                    account_id: $('#grn_vendor_account_id').val(),
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (!response.success || !response.vendor) {
                        show_toastr('error', response.message || 'Vendor could not be created.', 'error');
                        return;
                    }
                    const vendor = response.vendor;
                    const $vendorSelect = $('#grn_vendor_id');
                    $vendorSelect.append(new Option(vendor.name, vendor.id, true, true));
                    $vendorSelect.val(vendor.id).trigger('change');
                    if (typeof rebuildCustomSelect === 'function') rebuildCustomSelect($vendorSelect);
                    $form.find('input').val('');
                    $('#grn_vendor_account_id').val('').trigger('change');
                    if (typeof rebuildCustomSelect === 'function') rebuildCustomSelect($('#grn_vendor_account_id'));
                    $('#commonModalOver').modal('hide');
                    show_toastr('success', response.message || 'Vendor created successfully.', 'success');
                },
                error: function(xhr) {
                    const message = xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON.error)
                        ? (xhr.responseJSON.message || xhr.responseJSON.error)
                        : 'Vendor could not be created.';
                    show_toastr('error', message, 'error');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Add Vendor');
                }
            });
        });
    </script>
</div>
