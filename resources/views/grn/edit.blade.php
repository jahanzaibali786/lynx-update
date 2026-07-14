@extends('layouts.admin')

@section('page-title')
    {{ __('Edit GRN') }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Dashboard') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('grn.index') }}">{{ __('GRN') }}</a></li>
    <li class="breadcrumb-item">{{ __('Edit GRN') }}</li>
@endsection

@section('content')
    {{ Form::model($grn, ['route' => ['grn.update', $grn->id], 'method' => 'PUT', 'id' => 'grn-form', 'class' => 'grn-ajax-form', 'novalidate' => true]) }}
        @include('grn.form', ['grn' => $grn])
    {{ Form::close() }}
    <script>
        $(document).ready(function () {
            ajaxModalForm({ formSelector: '.grn-ajax-form', submitText: '{{ __('Updating...') }}', onSuccess: function (r) { $.ajax({ url: window.location.href, cache: false, dataType: 'html', success: function(html) { var el = new DOMParser().parseFromString(html, 'text/html').getElementById('content-area'); if (el) { document.getElementById('content-area').innerHTML = el.innerHTML; try { common_bind(); commonLoader(); } catch(e){} } else { location.reload(); } }, error: function() { location.reload(); } }); } });
        });
    </script>
@endsection
