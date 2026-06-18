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
    {{ Form::model($grn, ['route' => ['grn.update', $grn->id], 'method' => 'PUT', 'id' => 'grn-form']) }}
        @include('grn.form', ['grn' => $grn])
    {{ Form::close() }}
@endsection
