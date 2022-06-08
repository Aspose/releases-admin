@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>{{ $title }}</h1>
</div>

@if(Session::has('alert'))
    <div class="alert warning-info">
        {{Session::get('alert')}}
    </div>
@endif
@if(Session::has('success'))
    <div class="alert alert-success">
        {{Session::get('success')}}
    </div>
@endif

<form name="resetpasswordfrom" method="post" enctype="multipart/form-data" action="/admin/resetpassword" id="aspnetForm" class="form-horizontal">
<input name="_token" type="hidden" value="{{ csrf_token() }}" />
<input name="user_id" type="hidden" value="{{ $user->id }}" />
    <div class="control-group">
        <span id="usernameID" class="control-label">Username</span>
        <div class="controls">
            <input name="username" type="text" style="width:550px;" id="username" class="input-xlarge" value="{{ $user->name }}" readonly>
            <span id="ValidatetxtUsername" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('username') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="emailID" class="control-label">Email:</span>
        <div class="controls">
            <input name="email" type="text" style="width:550px;" id="email" class="input-xlarge" value="{{  $user->email  }}" readonly>
            <span id="Validateemail" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('email') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="passwordID" class="control-label">Password:</span>
        <div class="controls">
            <input name="password" type="text" style="width:550px;" id="password" class="input-xlarge" value="{{ Request::old('password') }}">
            <span id="Validatepassword" style="color:Red;visibility:hidden;">* Required</span>
        </div>
    </div>
    <div class="control-group">
        <span id="confpasswordID" class="control-label">Conf. Password:</span>
        <div class="controls">
            <input name="confpassword" type="text" style="width:550px;" id="confpassword" class="input-xlarge" value="{{ Request::old('confpassword') }}">
            <span id="Validateconfpassword" style="color:Red;visibility:hidden;">* Required</span>
        </div>
    </div>
    <div class="form-actions">
        <input type="submit" name="addnewpf" value="Update"  id="updatepwd" class="btn btn-success btn-large">


    </div>
</form>
@endsection
