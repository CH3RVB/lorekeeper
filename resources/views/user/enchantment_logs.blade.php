@extends('user.layout')

@section('profile-title') {{ $user->name }}'s Enchantment Logs @endsection

@section('profile-content')
{!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Armoury' => $user->url . '/armoury', 'Logs' => $user->url.'/enchantment-logs']) !!}

<h1>
    {!! $user->displayName !!}'s Enchantment Logs
</h1>

{!! $logs->render() !!}
<div class="row ml-md-2 mb-4">
  <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
    <div class="col-6 col-md-2 font-weight-bold">Sender</div>
    <div class="col-6 col-md-2 font-weight-bold">Recipient</div>
    <div class="col-6 col-md-2 font-weight-bold">Enchantment</div>
    <div class="col-6 col-md-4 font-weight-bold">Log</div>
    <div class="col-6 col-md-2 font-weight-bold">Date</div>
  </div>
    @foreach($logs as $log)
        @include('user._enchantment_log_row', ['log' => $log, 'owner' => $user])
    @endforeach
</div>
{!! $logs->render() !!}

@endsection
