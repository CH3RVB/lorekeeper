@extends('account.layout')

@section('account-title') Settings @endsection

@section('account-content')
{!! breadcrumbs(['My Account' => Auth::user()->url, 'Settings' => 'account/settings']) !!}

<h1>Settings</h1>


<div class="card p-3 mb-2">
    <h3>Avatar</h3>
    <div class="text-left"><div class="alert alert-warning">Please note a hard refresh may be required to see your updated avatar. Also please note that uploading a .gif will display a 500 error after; the upload should still work, however.</div></div>
    @if(Auth::user()->isStaff)
        <div class="alert alert-danger">For admins - note that .GIF avatars leave a tmp file in the directory (e.g php2471.tmp). There is an automatic schedule to delete these files.
        </div>
    @endif
    <form enctype="multipart/form-data" action="avatar" method="POST">
        <label>Update Profile Image</label><br>
        <input type="file" name="avatar">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="submit" class="pull-right btn btn-sm btn-primary">
    </form>
</div>


<div class="card p-3 mb-2">
    <h3>Profile</h3>
    {!! Form::open(['url' => 'account/profile']) !!}
        <div class="form-group">
            {!! Form::label('text', 'Profile Text') !!}
            {!! Form::textarea('text', Auth::user()->profile->text, ['class' => 'form-control wysiwyg']) !!}
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

<div class="card p-3 mb-2">
    <h3>Birthday Publicity</h3>
    {!! Form::open(['url' => 'account/dob']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Setting</label>
            <div class="col-md-10">
                {!! Form::select('birthday_setting', ['0' => '0: No one can see your birthday.', '1' => '1: Members can see your day and month.', '2' => '2: Anyone can see your day and month.', '3' => '3: Full date public.'],Auth::user()->settings->birthday_setting, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

<div class="card p-3 mb-2">
    <h3>Email Address</h3>
    <p>Changing your email address will require you to re-verify your email address.</p>
    {!! Form::open(['url' => 'account/email']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Email Address</label>
            <div class="col-md-10">
                {!! Form::text('email', Auth::user()->email, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

<div class="card p-3 mb-2">
    <h3>Change Password</h3>
    {!! Form::open(['url' => 'account/password']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Old Password</label>
            <div class="col-md-10">
                {!! Form::password('old_password', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-form-label">New Password</label>
            <div class="col-md-10">
                {!! Form::password('new_password', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Confirm New Password</label>
            <div class="col-md-10">
                {!! Form::password('new_password_confirmation', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

<div class="card p-3 mb-2">
        <h3>User Sales Notification</h3>
        <p>You may toggle this setting on and off to get pings whenever there is a new sales post by users, similar to site sales and news. </p>
        {!! Form::open(['url' => 'account/user-sales']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Notification Setting</label>
            <div class="col-md-10">
                {!! Form::select('get_user_sale_pings', ['0' => '0: DO NOT receive pings.', '1' => '1: Receive pings.'], Auth::user()->settings->get_user_sale_pings, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    <div class="card p-3 mb-2">
        <h3>Paypal Address</h3>
        <p>This is the paypal email you will use to send and receive payments from. This is not viewable to regular users unless payment through paypal is being conducted. Staff may see this while moderating sales posts. </p>
        {!! Form::open(['url' => 'account/paypal-email']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Paypal Address</label>
            <div class="col-md-10">
                {!! Form::text('paypal_email', Auth::user()->settings->paypal_email, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

@endsection
