@extends('layouts.admin')
@section('page-title')
    {{ __('Appointment Letter') }}
@endsection
@push('script-page')
    <script src="https://cdn.ckeditor.com/4.21.0/full/ckeditor.js"></script>

    <script>
    function previewAppointmentLetter(employeeId) {
        // alert('hhh');
        $.ajax({
            url: "{{ route('generate_appointment_letter', ['id' => '__employeeId__']) }}".replace('__employeeId__', employeeId),
            method: 'GET',
            data: {
                title: 'Appointment Letter',
                content: 'This is a sample content for the PDF.'
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                const base64Pdf = response.base64Pdf;
                const byteCharacters = atob(base64Pdf);
                const byteNumbers = new Array(byteCharacters.length);
                for (let i = 0; i < byteCharacters.length; i++) {
                    byteNumbers[i] = byteCharacters.charCodeAt(i);
                }
                const byteArray = new Uint8Array(byteNumbers);
                const blob = new Blob([byteArray], { type: 'application/pdf' });
                const blobUrl = URL.createObjectURL(blob);
                window.open(blobUrl, '_blank');
            },
            error: function (xhr) {
                //json 
                alert(xhr.responseJSON.error);
                console.log(xhr.responseText);
            }
        });
    }
    </script>

    <!-- <script src="https://cdn.ckeditor.com/ckeditor5/35.3.0/decoupled-document/ckeditor.js"></script> -->
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"> {{ __('Appointment Letter') }}</li>
@endsection
@section('action-btn')
    <div class="float-end">
        <a href="#" data-size="xl" 
            data-url="{{ route('appointment-letter-create') }}" data-ajax-popup="true"
            data-bs-title="{{ __('Create Appointment Letter') }}" class="btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">Create</span>
        </a>
         {{-- preview --}}
        <a target="_blank" onclick="previewAppointmentLetter(1)" class="btn mx-1 btn-sm btn-outline-primary">
            <span class="btn-inner--icon">Preview</span>
        </a>
    </div>
@endsection
@section('content')
    <div class="mt-4">
        <table>
            <thead>
            <tr class="table_heads report_table">
                <th>Sr no.</th>
                <th>Date</th>
                <th>Type</th>
                <th>Template</th>
                <th>action</th>
            </tr>
            </thead>
            <tbody>
                @foreach ($letters as $letter)
                    <tr>
                        {{-- <td>{{ @$letter->no }}</td> --}}
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ \Carbon\Carbon::parse(@$letter->date)->format('d-M-Y') }}</td>
						<td>{{ ucfirst($letter->type )}}</td>
                        <td>{{ \Illuminate\Support\Str::limit(@$letter->datacontent, 180, '...') }}</td>
                        <td>
                            <div class="action-btn ms-2">
                                <a href="#" data-size="xl"
                                    data-url="{{ route('appointment-letter-edit', @$letter->id) }}" data-ajax-popup="true"
                                    class="mx-1 btn mx-1 btn-sm btn-outline-primary" data-bs-toggle="tooltip"
                                    data-bs-title="{{ __('Edit Appointment Letter') }}"><span
                                        class="btn-inner--icon"><i class="ti ti-pencil "></i></span></a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($letters->hasPages())
        <div class="pagination">
            <ul>
                @if ($letters->onFirstPage())
                    <li class="disabled">&laquo; Previous</li>
                @else
                    <li><a href="{{ $letters->appends(request()->query())->previousPageUrl() }}"
                            rel="prev">&laquo; Previous</a></li>
                @endif
                @if ($letters->currentPage() > 1)
                    <li><a href="{{ $letters->appends(request()->query())->url(1) }}">First</a></li>
                @endif
                @php
                    $currentPage = $letters->currentPage();
                    $lastPage = $letters->lastPage();
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
                    <li class="{{ $page == $letters->currentPage() ? 'active' : '' }}">
                        <a href="{{ $letters->appends(request()->query())->url($page) }}">{{ $page }}</a>
                    </li>
                @endfor
                @if ($letters->hasMorePages())
                    <li><a href="{{ $letters->appends(request()->query())->nextPageUrl() }}" rel="next">Next
                            &raquo;</a></li>
                @else
                    <li class="disabled">Next &raquo;</li>
                @endif
                @if ($letters->currentPage() < $letters->lastPage())
                    <li><a
                            href="{{ $letters->appends(request()->query())->url($letters->lastPage()) }}">Last</a>
                    </li>
                @endif
            </ul>
        </div>
    @endif
@endsection
