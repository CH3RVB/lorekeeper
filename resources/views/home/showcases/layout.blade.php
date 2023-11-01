@extends('layouts.app')

@section('title') 
    {{ ucfirst(__('showcase.showcase')) }} :: 
    @yield('home.showcases-title')
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