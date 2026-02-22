@extends('layouts.app')

@section('title')
    Site user_sales{!! View::hasSection('user_sales-title') ? ' :: ' . trim(View::getSection('user_sales-title')) : '' !!}
@endsection

@section('sidebar')
    @include('user_sales._sidebar')
@endsection

@section('content')
    @yield('user_sales-content')
@endsection
