@extends('layouts.admin')
@section('page-title')
    {{__('Manage School Session')}}
@endsection
@push('script-page')
<script>
    $(document).on("click",".new_data",function() {
    let id = $(this).data('confirm-id');
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        title: 'Are you sure?',
        text: "This action can change status.!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Change it!',
        cancelButtonText: 'No, cancel!',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('update-status-form-'+id).submit();
            swalWithBootstrapButtons.fire(
                'Updated!',
                'Your session status has been changed.',
                'success'
            )
        } else if (
            result.dismiss === Swal.DismissReason.cancel
        ) {
            swalWithBootstrapButtons.fire(
                'Cancelled',
                'Your session status is safe :)',
                'error'
            )
        }
    })
});
</script>
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('All School Session')}}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        {{-- @can('create session') --}}
            <a href="#" data-size="md" data-url="{{ route('session.create') }}" data-ajax-popup="true"   data-bs-title="{{__('Create')}}"  class="btn mx-1 btn-sm btn-outline-primary">
                <span class="btn-inner--icon">Create</span>
            </a>
        {{-- @endcan --}}
    </div>
@endsection
@section('content')

    <div class="table-responsive">
        <table class="">
            <thead class="table_heads">
                <tr>
                    <th>#</th>
                    <th>{{__('Year')}}</th>
                    <th>{{__('Branch')}}</th>
                    <th>{{__('Starting Date')}}</th>
                    <th>{{__('Ending Date')}}</th>
                    <th>{{__('Status')}}</th>
                    <th>{{__('Action')}}</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($sessions as $session)
                <tr>
                    <td>{{ ($sessions->currentPage() - 1) * $sessions->perPage() + $loop->iteration }}</td>
                    <td>
                        {{ (!empty($session->year)) ? $session->year : '-' }}
                    </td>
                    <td>
                        {{(Auth::user()->ownedBy()->name)}}
                    </td>
                    <td>
                        {{ (!empty($session->starting_date)) ? \Auth::user()->dateFormat($session->starting_date) : '-' }}
                    </td>
                    <td>
                        {{ (!empty($session->ending_date)) ? \Auth::user()->dateFormat($session->ending_date) : '-' }}
                    </td>
                    <td>
                        {{-- <form method="POST" action="{{ route('update_session_status', $session->id) }}" style="display: inline;">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="active_status" value="{{ $session->active_status ? 0 : 1 }}">
                            <button type="submit" class="btn btn-sm {{ $session->active_status ? 'btn-danger' : 'btn-success' }}">
                                {{ $session->active_status ? 'Inactive' : 'Activate' }}
                            </button>
                        </form> --}}
                                    {!! Form::open(['method' => 'POST', 'route' => ['update_session_status', $session->id],'id'=>'update-status-form-'.$session->id]) !!} <input type="hidden" name="active_status" value="{{ $session->active_status ? 0 : 1 }}">
                                        <a href="#" class="mx-1 btn btn-sm {{ $session->active_status ? 'btn-danger' : 'btn-success' }} new_data"  data-bs-title="{{__('Update Status')}}" data-bs-title="{{__('Submit')}}" data-confirm="{{__('Are You Sure?').'|'.__('This action can change other session inactive and this session active. Do you want to continue?')}}" data-confirm-yes="document.getElementById('update-status-form-{{$session->id}}').submit();" data-confirm-id="{{$session->id}}"> <span class="btn-inner--icon">{{ $session->active_status ? 'Inactive' : 'Activate' }}</span></a>
                                    {!! Form::close() !!}
                    </td>
                    {{-- @if(Gate::check('edit session') || Gate::check('delete session')) --}}
                        <td>
                            <div class="action-btn ms-2">
                                {{-- @can('edit session') --}}
                                    <a href="#!" data-url="{{route('session.edit',$session->id)}}"  data-ajax-popup="true" class="mx-1 btn mx-1 btn-sm btn-outline-primary"  data-bs-title="{{__('Edit')}}"
                                    data-bs-title="{{__('Edit')}}"> <span class="btn-inner--icon"><i class="ti ti-pencil"></i></span></a>


                            </div>

                        </td>
                    {{-- @endif --}}
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if ($sessions->hasPages())
        <div class="pagination">
            <ul>
                @if ($sessions->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $sessions->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($sessions->currentPage() > 1)
                    <li><a href="{{ $sessions->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $sessions->currentPage();
                    $lastPage = $sessions->lastPage();
                    $startPage = max(1, $currentPage - 4);
                    $endPage = min($lastPage, $currentPage + 5);
                    if ($endPage - $startPage < 9) {
                        if ($currentPage < $lastPage - 9) {
                            $endPage = $startPage + 9;
                        } else {
                            $startPage = max(1, $lastPage - 9);
                        }
                    }
                @endphp
                @for ($page = $startPage; $page <= $endPage; $page++)
                    <li class="{{ $page == $sessions->currentPage() ? 'active' : '' }}">
                        <a href="{{ $sessions->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($sessions->hasMorePages())
                    <li><a href="{{ $sessions->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($sessions->currentPage() < $sessions->lastPage())
                    <li><a
                            href="{{ $sessions->appends(request()->query())->url($sessions->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif

@endsection
