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
    {{ Form::open(['route' => 'grn.store', 'method' => 'POST', 'id' => 'grn-form']) }}
        @include('grn.form', ['grn' => null])
    {{ Form::close() }}
@endsection
