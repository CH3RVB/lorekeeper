@extends('layouts.app')

@section('title') 
    Shops :: 
    @yield('home.user_shops-title')
@endsection

@section('sidebar')
    @include('home.showcases._sidebar')
@endsection

@section('content')
    @yield('home.showcases-content')
@endsection

@section('scripts')
@parent
@endsection