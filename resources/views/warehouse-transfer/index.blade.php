@extends('layouts.admin')
@section('page-title')
    {{ __('Warehouse Transfer') }}
@endsection
@push('script-page')
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Warehouse Transfer') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="lg" data-url="{{ route('store-transfer.create') }}" data-ajax-popup="true"
             data-bs-title="{{ __('Create') }}" data-bs-toggle="{{ __('Create Store Transfer') }}"
            class="btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">Create</span>
        </a>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-xl-12">
            <div class="table-responsive">
                <table class="datatable">
                    <thead class="table_heads">
                        <tr>
                            <th>{{ __('From Store') }}</th>
                            <th>{{ __('To Store') }}</th>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Quantity') }}</th>
                            <th>{{ __('Date') }}</th>
                            {{-- <th>{{ __('Action') }}</th> --}}
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($warehouse_transfers as $warehouse_transfer)
                            <tr class="font-style">
                                <td>{{ !empty($warehouse_transfer->fromWarehouse()) ? $warehouse_transfer->fromWarehouse()->name : '' }}
                                </td>
                                <td>{{ !empty($warehouse_transfer->toWarehouse()) ? $warehouse_transfer->toWarehouse()->name : '' }}
                                </td>
                                @if (!empty($warehouse_transfer->product()))
                                    <td>{{ !empty($warehouse_transfer->product()) ? $warehouse_transfer->product()->name : '' }}
                                    </td>
                                @endif
                                <td>{{ $warehouse_transfer->quantity }}</td>
                                <td>{{ Auth::user()->dateFormat($warehouse_transfer->date) }}</td>

                                {{-- @if (Gate::check('edit warehouse') || Gate::check('delete warehouse'))
                                            <td class="Action">
                                                <div class="action-btn ms-2">
                                                   @can('edit warehouse')
                                                            <a href="#" class="mx-1 btn mx-1 btn-sm btn-outline-info  align-items-center" data-url="{{ route('warehouse-transfer.edit',$warehouse_transfer->id) }}" data-ajax-popup="true"  data-size="lg "  data-bs-title="{{__('Edit')}}"  data-bs-toggle="{{__('Edit Warehouse')}}">
                                                                <span class="btn-inner--icon"><i class="ti ti-pencil "></i></span>
                                                            </a>
                                                        </div>
                                                    @endcan
                                                    @can('delete warehouse')
                                                        {!! Form::open([
                                                            'method' => 'DELETE',
                                                            'route' => ['warehouse-transfer.destroy', $warehouse_transfer->id],
                                                            'id' => 'delete-form-' . $warehouse_transfer->id,
                                                        ]) !!}
                                                        <a href="#"
                                                            class="mx-1 btn btn-sm  align-items-center bs-pass-para"
                                                             data-bs-title="{{ __('Delete') }}"><span class="btn-inner--icon"><i
                                                                class="ti ti-trash"></i></span></a>
                                                        {!! Form::close() !!}
                                                    @endcan
                                                </div>
                                            </td>
                                        @endif --}}
                            </tr>
                        @endforeach

                    </tbody>
                </table>
                @if ($warehouse_transfers != '')
                    <div class="pagination">
                        <ul>
                            @if ($warehouse_transfers->onFirstPage())
                                <li class="disabled">&laquo;</li>
                            @else
                                <li><a href="{{ $warehouse_transfers->appends(request()->query())->previousPageUrl() }}"
                                        rel="prev">&laquo;</a></li>
                            @endif
                            @for ($page = 1; $page <= $warehouse_transfers->lastPage(); $page++)
                                <li class="{{ $page == $warehouse_transfers->currentPage() ? 'active' : '' }}">
                                    <a
                                        href="{{ $warehouse_transfers->appends(request()->query())->url($page) }}">{{ $page }}</a>
                                </li>
                            @endfor
                            @if ($warehouse_transfers->hasMorePages())
                                <li><a href="{{ $warehouse_transfers->appends(request()->query())->nextPageUrl() }}"
                                        rel="next">&raquo;</a></li>
                            @else
                                <li class="disabled">&raquo;</li>
                            @endif
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            var w_id = $('#warehouse_id').val();
            getProduct(w_id);
        });
        $(document).on('change', 'select[name=from_warehouse]', function() {
            var warehouse_id = $(this).val();
            getProduct(warehouse_id);
        });

        function getProduct(wid) {
            $.ajax({
                url: '{{ route('warehouse-transfer.getproduct') }}',
                type: 'POST',
                data: {
                    "warehouse_id": wid,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    $('#product_id').empty();

                    $("#product_div").html('');
                    $('#product_div').append(
                        '<label for="product" class="form-label">{{ __('Product') }}</label>');
                    $('#product_div').append(
                        '<select class="form-control" id="product_id" name="product_id"></select>');
                    $('#product_id').append('<option value="">{{ __('Select Product') }}</option>');

                    $.each(data.ware_products, function(key, value) {
                        $('#product_id').append('<option value="' + key + '">' + value + '</option>');
                    });

                    $('select[name=to_warehouse]').empty();
                    $.each(data.to_warehouses, function(key, value) {
                        var option = '<option value="' + key + '">' + value + '</option>';
                        $('select[name=to_warehouse]').append(option);
                    });
                }

            });
        }

        $(document).on('change', '#product_id', function() {
            var product_id = $(this).val();
            var warehouse_id = $('#warehouse_id').val();
            getQuantity(product_id, warehouse_id);
        });

        function getQuantity(pid, wid) {
            $.ajax({
                url: '{{ route('warehouse-transfer.getquantity') }}',
                type: 'POST',
                data: {
                    "product_id": pid,
                    "warehouse_id": wid,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    console.log(data);
                    $('#quantity').val(data);
                }
            });
        }
    </script>
@endpush
