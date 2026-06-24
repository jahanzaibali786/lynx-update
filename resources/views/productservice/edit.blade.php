@extends('layouts.admin')

@section('page-title')
    {{ __('Edit Product') }}
@endsection

@push('css-page')
    <link rel="stylesheet" href="{{ asset('public/acron/select2.css') }}" />
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('productservice.index') }}">{{ __('Products') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit') }}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="{{ route('productservice.index') }}" class="btn btn-sm btn-outline-secondary">
            {{ __('Back') }}
        </a>
    </div>
@endsection

@section('content')
    {{ Form::model($productService, ['route' => ['productservice.update', $productService->id], 'method' => 'PUT', 'enctype' => 'multipart/form-data']) }}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Item Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-3" id="item_type_div">
                            {{ Form::label('item_type', __('Type'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                            {{ Form::select('item_type', $itemTypes, old('item_type', $productService->item_type ?: ($productService->type === 'service' ? 'service' : 'inventory_part')), ['class' => 'form-control select', 'id' => 'item_type', 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-3" id="name_div">
                            {{ Form::label('name', __('Item Name / Number'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                            {{ Form::text('name', old('name', $productService->name), ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-3" id="sku_div">
                            {{ Form::label('sku', __('Product Code'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                            {{ Form::text('sku', old('sku', $productService->sku), ['class' => 'form-control', 'required' => 'required']) }}
                        </div>
                        <div class="form-group col-md-3" id="manufacturer_part_number_div">
                            {{ Form::label('manufacturer_part_number', __('Manufacturer Part Number'), ['class' => 'form-label']) }}
                            {{ Form::text('manufacturer_part_number', old('manufacturer_part_number', $productService->manufacturer_part_number), ['class' => 'form-control']) }}
                        </div>

                        <div class="form-group col-md-4">
                            <div class="d-flex align-items-center justify-content-between">
                                {{ Form::label('parent_id', __('Parent Item'), ['class' => 'form-label mb-0']) }}
                                <div class="form-check mb-0">
                                    {{ Form::label('is_subitem', __('Subitem of'), ['class' => 'form-check-label']) }}
                                    {{ Form::checkbox('is_subitem', 1, old('is_subitem', $productService->is_subitem), ['class' => 'form-check-input', 'id' => 'is_subitem', 'style' => 'margin-left: 10px !important; float: right !important;']) }}
                                </div>
                            </div>
                            {{ Form::select('parent_id', $parentItems, old('parent_id', $productService->parent_id), ['class' => 'form-control select', 'id' => 'parent_id']) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('unit_id', __('Unit of Measure'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                            {{ Form::select('unit_id', $unit, old('unit_id', $productService->unit_id), ['class' => 'form-control select', 'id' => 'unit_id', 'required' => 'required']) }}
                            <div class="text-xs">
                                {{ __('Please add constant unit. ') }}<a href="#" data-url="{{ route('product-unit.create') }}" data-ajax-popup="true" data-bs-toggle="{{ __('Create New Unit') }}" class="add-unit-modal-btn"><b>{{ __('Add Unit') }}</b></a>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('category_id', __('Category'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                            {{ Form::select('category_id', $category, old('category_id', $productService->category_id), ['class' => 'form-control select', 'id' => 'category_id', 'required' => 'required']) }}
                            <div class="text-xs">
                                {{ __('Please add constant category. ') }}<a href="#" data-url="{{ route('product-category.create') }}" data-ajax-popup="true" data-bs-toggle="{{ __('Create New Category') }}" class="add-category-modal-btn"><b>{{ __('Add Category') }}</b></a>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('sub_category_id', __('SubCategory'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                            {{ Form::select('sub_category_id', [], old('sub_category_id', $productService->sub_category_id), ['class' => 'form-control select', 'id' => 'sub_category_id', 'required' => 'required']) }}
                            <div class="text-xs">
                                {{ __('Please add constant sub category. ') }}<a href="#" data-url="{{ route('product-sub-category.create') }}" data-ajax-popup="true" data-bs-toggle="{{ __('Create New SubCategory') }}" class="add-subcategory-modal-btn"><b>{{ __('Add Subcategory') }}</b></a>
                            </div>
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('tax_id', __('Tax'), ['class' => 'form-label']) }}
                            {{ Form::select('tax_id[]', $tax, old('tax_id', $productService->tax_id), ['class' => 'form-control select2', 'id' => 'choices-multiple1', 'multiple' => 'multiple']) }}
                        </div>
                        <div class="form-group col-md-4">
                            {{ Form::label('pro_image', __('Product Image'), ['class' => 'form-label']) }}
                            {{ Form::file('pro_image', ['class' => 'form-control', 'id' => 'pro_image']) }}
                            <img id="image" class="mt-3" width="100" src="@if ($productService->pro_image) {{ asset(Storage::url('uploads/pro_image/' . $productService->pro_image)) }} @else {{ asset(Storage::url('uploads/pro_image/user-2_1654779769.jpg')) }} @endif" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md" id="purchase_information_div">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Purchase Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        {{ Form::label('purchase_description', __('Description on Purchase Transactions'), ['class' => 'form-label']) }}
                        {{ Form::textarea('purchase_description', old('purchase_description', $productService->purchase_description ?: $productService->description), ['class' => 'form-control', 'rows' => 3]) }}
                    </div>
                    <div class="form-group">
                        {{ Form::label('purchase_price', __('Cost'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                        {{ Form::number('purchase_price', old('purchase_price', $productService->purchase_price), ['class' => 'form-control', 'id' => 'purchase_price', 'required' => 'required', 'step' => '0.01']) }}
                    </div>
                    <div class="form-group">
                        {{ Form::label('expense_chartaccount_id', __('COGS Account'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                        {{ Form::select('expense_chartaccount_id', $expenseChartAccounts, old('expense_chartaccount_id', $productService->expense_chartaccount_id), ['class' => 'form-control select custom-select', 'id' => 'expense_chartaccount_id', 'required' => 'required']) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md" id="sales_information_div">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Sales Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        {{ Form::label('sales_description', __('Description on Sales Transactions'), ['class' => 'form-label']) }}
                        {{ Form::textarea('sales_description', old('sales_description', $productService->sales_description ?: $productService->description), ['class' => 'form-control', 'rows' => 3]) }}
                    </div>
                    <div class="form-group">
                        {{ Form::label('sale_price', __('Sales Price'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                        {{ Form::number('sale_price', old('sale_price', $productService->sale_price), ['class' => 'form-control', 'required' => 'required', 'step' => '0.01']) }}
                    </div>
                    <div class="form-group">
                        {{ Form::label('sale_chartaccount_id', __('Income Account'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                        {{ Form::select('sale_chartaccount_id', $incomeChartAccounts, old('sale_chartaccount_id', $productService->sale_chartaccount_id), ['class' => 'form-control select custom-select', 'id' => 'sale_chartaccount_id', 'required' => 'required']) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12" id="inventory_information_div">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Inventory Information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="form-group col-md-6">
                            {{ Form::label('inventory_asset_account_id', __('Inventory Asset Account'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                            {{ Form::select('inventory_asset_account_id', $inventoryAssetAccounts, old('inventory_asset_account_id', $productService->inventory_asset_account_id), ['class' => 'form-control select custom-select', 'id' => 'inventory_asset_account_id', 'required' => 'required']) }}
                        </div>
                        @if (!$customFields->isEmpty())
                            <div class="form-group col-md-6">
                                @include('customFields.formBuilder')
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 mt-3 text-end">
            <a href="{{ route('productservice.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
        </div>
    </div>
    {{ Form::close() }}

    <script src="{{ asset('public/acron/select2.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category_id');
            const subCategorySelect = document.getElementById('sub_category_id');
            const selectedSubCategory = @json(old('sub_category_id', $productService->sub_category_id));
            const subItemCheckbox = document.getElementById('is_subitem');
            const parentSelect = document.getElementById('parent_id');
            const imageInput = document.getElementById('pro_image');
            const imagePreview = document.getElementById('image');

            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#choices-multiple1').select2({
                    width: '100%',
                    placeholder: '{{ __('Select Tax') }}',
                    allowClear: true
                });
            }

            function toggleParentItem() {
                if (!subItemCheckbox || !parentSelect) {
                    return;
                }

                parentSelect.disabled = !subItemCheckbox.checked;
                parentSelect.required = subItemCheckbox.checked;
                if (!subItemCheckbox.checked) {
                    parentSelect.value = '';
                }
            }

            function loadSubCategories(selectNewId) {
                if (!categorySelect || !subCategorySelect || !categorySelect.value) {
                    if (subCategorySelect) {
                        subCategorySelect.innerHTML = '<option value="">{{ __('Select SubCategory') }}</option>';
                    }
                    return;
                }

                fetch('{{ url('/get-subcategories') }}/' + categorySelect.value)
                    .then(response => response.json())
                    .then(data => {
                        subCategorySelect.innerHTML = '<option value="">{{ __('Select SubCategory') }}</option>';
                        var idToSelect = selectNewId || selectedSubCategory;
                        (data.subcategories || []).forEach(function(subcategory) {
                            const option = document.createElement('option');
                            option.value = subcategory.id;
                            option.textContent = subcategory.name;
                            if (String(idToSelect) === String(subcategory.id)) {
                                option.selected = true;
                            }
                            subCategorySelect.appendChild(option);
                        });
                    });
            }

            if (categorySelect) {
                categorySelect.addEventListener('change', function() { loadSubCategories(); });
                loadSubCategories();
            }

            if (subItemCheckbox) {
                subItemCheckbox.addEventListener('change', toggleParentItem);
                toggleParentItem();
            }

            if (imageInput && imagePreview) {
                imageInput.addEventListener('change', function() {
                    if (this.files && this.files[0]) {
                        imagePreview.src = URL.createObjectURL(this.files[0]);
                    }
                });
            }

            // --- Toggle fields based on Item Type ---
            const itemTypeSelect = document.getElementById('item_type');
            const manufacturerPartNumDiv = document.getElementById('manufacturer_part_number_div');
            const inventoryInfoDiv = document.getElementById('inventory_information_div');
            const inventoryAssetSelect = document.getElementById('inventory_asset_account_id');
            const purchaseInfoDiv = document.getElementById('purchase_information_div');
            const purchasePriceInput = document.getElementById('purchase_price');
            const expenseChartAccountSelect = document.getElementById('expense_chartaccount_id');

            const itemTypeDiv = document.getElementById('item_type_div');
            const nameDiv = document.getElementById('name_div');
            const skuDiv = document.getElementById('sku_div');

            function toggleItemTypeFields() {
                if (!itemTypeSelect) return;
                const type = itemTypeSelect.value;
                
                if (type === 'service') {
                    if (manufacturerPartNumDiv) manufacturerPartNumDiv.classList.add('d-none');
                    if (itemTypeDiv) itemTypeDiv.className = 'form-group col-md-4';
                    if (nameDiv) nameDiv.className = 'form-group col-md-4';
                    if (skuDiv) skuDiv.className = 'form-group col-md-4';
                    
                    if (inventoryInfoDiv) inventoryInfoDiv.classList.add('d-none');
                    if (inventoryAssetSelect) inventoryAssetSelect.removeAttribute('required');
                    
                    if (purchaseInfoDiv) purchaseInfoDiv.classList.add('d-none');
                    if (purchasePriceInput) purchasePriceInput.removeAttribute('required');
                    if (expenseChartAccountSelect) expenseChartAccountSelect.removeAttribute('required');
                    
                } else if (type === 'non_inventory_part') {
                    if (manufacturerPartNumDiv) manufacturerPartNumDiv.classList.remove('d-none');
                    if (itemTypeDiv) itemTypeDiv.className = 'form-group col-md-3';
                    if (nameDiv) nameDiv.className = 'form-group col-md-3';
                    if (skuDiv) skuDiv.className = 'form-group col-md-3';
                    
                    if (inventoryInfoDiv) inventoryInfoDiv.classList.add('d-none');
                    if (inventoryAssetSelect) inventoryAssetSelect.removeAttribute('required');
                    
                    if (purchaseInfoDiv) purchaseInfoDiv.classList.add('d-none');
                    if (purchasePriceInput) purchasePriceInput.removeAttribute('required');
                    if (expenseChartAccountSelect) expenseChartAccountSelect.removeAttribute('required');
                    
                } else {
                    // inventory_part
                    if (manufacturerPartNumDiv) manufacturerPartNumDiv.classList.remove('d-none');
                    if (itemTypeDiv) itemTypeDiv.className = 'form-group col-md-3';
                    if (nameDiv) nameDiv.className = 'form-group col-md-3';
                    if (skuDiv) skuDiv.className = 'form-group col-md-3';
                    
                    if (inventoryInfoDiv) inventoryInfoDiv.classList.remove('d-none');
                    if (inventoryAssetSelect) inventoryAssetSelect.setAttribute('required', 'required');
                    
                    if (purchaseInfoDiv) purchaseInfoDiv.classList.remove('d-none');
                    if (purchasePriceInput) purchasePriceInput.setAttribute('required', 'required');
                    if (expenseChartAccountSelect) expenseChartAccountSelect.setAttribute('required', 'required');
                }
            }

            if (itemTypeSelect) {
                // When using select2, the change event might need to be bound via jQuery
                if (window.jQuery && jQuery(itemTypeSelect).hasClass('select2-hidden-accessible')) {
                    jQuery(itemTypeSelect).on('change', toggleItemTypeFields);
                } else {
                    itemTypeSelect.addEventListener('change', toggleItemTypeFields);
                }
                toggleItemTypeFields();
            }

            // --- AJAX modal form submit handlers for Add Category / SubCategory / Unit ---
            $(document).on('submit', '#commonModal form', function(e) {
                var $form = $(this);
                var actionUrl = $form.attr('action');

                // Only intercept forms targeting category, subcategory, or unit create routes
                var isCategoryForm = actionUrl && actionUrl.indexOf('product-category') !== -1 && actionUrl.indexOf('product-sub-category') === -1;
                var isSubCategoryForm = actionUrl && actionUrl.indexOf('product-sub-category') !== -1;
                var isUnitForm = actionUrl && actionUrl.indexOf('product-unit') !== -1;

                if (!isCategoryForm && !isSubCategoryForm && !isUnitForm) {
                    return; // Let other forms submit normally
                }

                e.preventDefault();

                $.ajax({
                    url: actionUrl,
                    type: 'POST',
                    data: $form.serialize(),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(response) {
                        if (response.success) {
                            $('#commonModal').modal('hide');
                            show_toastr('success', response.message);

                            if (isCategoryForm && response.category) {
                                // Refresh category dropdown and select the new one
                                var newOpt = new Option(response.category.name, response.category.id, true, true);
                                $('#category_id').append(newOpt);
                                $('#category_id').val(response.category.id);
                                // Trigger subcategory reload
                                loadSubCategories();
                            }

                            if (isSubCategoryForm && response.subcategory) {
                                // Refresh subcategories for current category and select the new one
                                loadSubCategories(response.subcategory.id);
                            }

                            if (isUnitForm && response.unit) {
                                // Refresh unit dropdown and select the new one
                                var newUnitOpt = new Option(response.unit.name, response.unit.id, true, true);
                                $('#unit_id').append(newUnitOpt);
                                $('#unit_id').val(response.unit.id);
                            }
                        } else {
                            show_toastr('error', response.message || '{{ __('Something went wrong.') }}');
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? (xhr.responseJSON.message || xhr.responseJSON.error) : '{{ __('Something went wrong.') }}';
                        show_toastr('error', msg);
                    }
                });
            });
        });
    </script>
@endsection
