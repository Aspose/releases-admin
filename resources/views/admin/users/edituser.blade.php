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

<form name="addnewuserfrom" method="post" enctype="multipart/form-data" action="/admin/updateuser" id="aspnetForm" class="form-horizontal">
    <input name="_token" type="hidden" value="{{ csrf_token() }}" />
    <input name="user_id" type="hidden" value="{{ $user->id }}" />
    <div class="control-group">
        <span id="usernameID" class="control-label">Username</span>
        <div class="controls">
            <input name="username" type="text" style="width:550px;" id="username" class="input-xlarge" value="{{ $user->name }}">
            <span id="ValidatetxtUsername" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('username') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="emailID" class="control-label">Email:</span>
        <div class="controls">
            <input name="email" type="text" style="width:550px;" id="email" class="input-xlarge" value="{{ $user->email }}">
            <span id="Validateemail" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('email') }} </p>
        </div>
    </div>
    <div class="control-group">
        <span id="userroleD" class="control-label">Role:</span>
        <div class="controls">
            <select name="userrole" id="userrole">
                @foreach($roles as $role)
                <option value="{{ $role->id }} " {{ ( $role->id == $user->is_admin) ? 'selected' : '' }} >{{ $role->name }}</option>
                @endforeach
            </select>
            <span id="Validateuserrole" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('userrole') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="is_activeD" class="control-label">Status:</span>
        <div class="controls">
            <select name="is_active" id="is_active">
                <option value="1" {{ ( '1' == $user->is_active) ? 'selected' : '' }} >Active</option>
                <option value="0" {{ ( '0' == $user->is_active) ? 'selected' : '' }} >Not Active</option>
            </select>
            <span id="Validateis_active" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('is_active') }} </p>
        </div>
    </div>
    
    
    <div class="form-actions">
        <input type="submit" name="addnewpf" value="Update User" id="addnewuser" class="btn btn-success btn-large">
    </div>
</form>
@endsection