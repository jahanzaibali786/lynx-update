@extends('layouts.admin')

@section('page-title')
    {{ __('Create GRN') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('grn.index') }}">{{ __('GRN') }}</a></li>
    <li class="breadcrumb-item">{{ __('Create GRN') }}</li>
@endsection

@section('content')
    {{ Form::open(['route' => 'grn.store', 'method' => 'POST', 'id' => 'grn-form', 'class' => 'grn-ajax-form', 'novalidate' => true]) }}
        @include('grn.form', ['grn' => null])
    {{ Form::close() }}
    <script>
        $(document).ready(function () {
            ajaxModalForm({ formSelector: '.grn-ajax-form', submitText: '{{ __('Creating...') }}', onSuccess: function (r) { $.ajax({ url: window.location.href, cache: false, dataType: 'html', success: function(html) { var el = new DOMParser().parseFromString(html, 'text/html').getElementById('content-area'); if (el) { document.getElementById('content-area').innerHTML = el.innerHTML; try { common_bind(); commonLoader(); } catch(e){} } else { location.reload(); } }, error: function() { location.reload(); } }); } });
        });
    </script>
@endsection
