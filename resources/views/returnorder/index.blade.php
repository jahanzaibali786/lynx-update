@extends('layouts.admin')
@section('page-title')
    {{ __('Manage Return Order') }}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item">{{ __('Return Order') }}</li>
@endsection
@push('script-page')
    <script>
        $('.copy_link').click(function(e) {
            e.preventDefault();
            var copyText = $(this).attr('href');

            document.addEventListener('copy', function(e) {
                e.clipboardData.setData('text/plain', copyText);
                e.preventDefault();
            }, true);

            document.execCommand('copy');
            show_toastr('success', 'Url copied to clipboard', 'success');
        });
    </script>
@endpush


@section('action-btn')
    <div class="float-end">

            {{-- <a href="{{ route('quotations.create', 0) }}" class="btn btn-sm btn-primary" 
                data-bs-title="{{ __('Create') }}">
                Create
            </a> --}}
            <a href="#" data-size="lg" data-url="{{ route('returnorder.create') }}" data-ajax-popup="true"
                 data-bs-title="{{ __('Return Order Create') }}" data-bs-custom-class="custom-tooltip-reurnorder" class="returnBtn btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>

    </div>
@endsection


@section('content')
    <div class="row">
        <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="datatable">
                            <thead class="table_heads">
                                <tr>
                                    <th>{{ __('S.No') }}</th>
                                    <th> {{ __('ReturnOrder ID') }}</th>
                                    <th> {{ __('Date') }}</th>
                                    <th> {{ __('Store From') }}</th>
                                    <th> {{ __('Store To') }}</th>
                                    <th> {{ __('Action') }}</th>

                                </tr>
                            </thead>
                            <tbody>


                                @foreach ($quotations as $quotation)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td class="Id">
                                            <a href="{{ route('returnorder.show', \Crypt::encrypt($quotation->id)) }}"
                                                class="btn btn-outline-primary btnpurchase1">{{ Auth::user()->quotationNumberFormat($quotation->quotation_id) }}</a>
                                        </td>
                                        <td>{{ Auth::user()->dateFormat($quotation->quotation_date) }}</td>
                                        <td> {{ !empty($quotation->customer) ? $quotation->customer->name : '' }} </td>
                                        <td>{{ !empty($quotation->warehouse) ? $quotation->warehouse->name : '' }}</td>

                                            <td class="Action">
                                                <span>

                                                    {{-- @if ($quotation->is_converted == 0)
                                                        @can('convert quotation')
                                                            <div class="action-btn bg-warning ms-2">
                                                                <a href="{{ route('poses.index', $quotation->id) }}"
                                                                    class="mx-3 btn btn-sm align-items-center"
                                                                     data-bs-title="{{ __('Convert to POS') }}"
                                                                    data-bs-title="{{ __('Detail') }}">
                                                                    <i class="ti ti-exchange text-white"></i>
                                                                </a>
                                                            </div>
                                                        @endcan
                                                        @else

                                                            <div class="action-btn bg-warning ms-2">
                                                                <a href="{{ route('pos.show', \Crypt::encrypt($quotation->converted_pos_id)) }}" class="mx-3 btn btn-sm align-items-center"
                                                                    
                                                                    data-bs-title="{{ __('Already convert to POS') }}"
                                                                    data-bs-title="{{ __('Detail') }}">
                                                                    <i class="ti ti-file text-white"></i>
                                                                </a>
                                                            </div>

                                                    @endif --}}


                                                        <div class="action-btn ms-2">
                                                            <a href="{{ route('returnorder.show', \Crypt::encrypt($quotation->id)) }}"
                                                                class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center"
                                                                 title="Edit"
                                                                data-bs-title="{{ __('Show') }}">
                                                                <span class="btn-inner--icon"><i class="ti ti-eye "></i></span>
                                                            </a>
                                                            <a href="{{ route('returnorder.edit', \Crypt::encrypt($quotation->id)) }}"
                                                                class="mx-1 btn mx-1 btn-sm btn-outline-primary align-items-center"
                                                                 title="Edit"
                                                                data-bs-title="{{ __('Edit') }}">
                                                                <span class="btn-inner--icon"><i class="ti ti-pencil "></i></span>
                                                            </a>

                                                            {!! Form::open([
                                                                'method' => 'DELETE',
                                                                'route' => ['returnorder.destroy', $quotation->id],
                                                                'class' => 'delete-form-btn',
                                                                'id' => 'delete-form-' . $quotation->id,
                                                            ]) !!}
                                                            <a href="#"
                                                                class="mx-1 btn mx-1 btn-sm btn-outline-danger align-items-center bs-pass-para"
                                                                 data-bs-title="{{ __('Delete') }}"
                                                                data-bs-title="{{ __('Delete') }}"
                                                                data-confirm="{{ __('Are You Sure?') . '|' . __('This action can not be undone. Do you want to continue?') }}"
                                                                data-confirm-yes="document.getElementById('delete-form-{{ $quotation->id }}').submit();">
                                                                <span class="btn-inner--icon"><i class="ti ti-trash "></i></span>
                                                            </a>
                                                            {!! Form::close() !!}
                                                        </div>
                                                </span>
                                            </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

    </div>
@endsection
