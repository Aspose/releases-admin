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

<form name="addnewuserfrom" method="post" enctype="multipart/form-data" action="/admin/addnewuser" id="aspnetForm" class="form-horizontal">
    <input name="_token" type="hidden" value="{{ csrf_token() }}" />

    <div class="control-group">
        <span id="usernameID" class="control-label">Username</span>
        <div class="controls">
            <input name="username" type="text" style="width:550px;" id="username" class="input-xlarge" value="{{ Request::old('username') }}">
            <span id="ValidatetxtUsername" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('username') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="emailID" class="control-label">Email:</span>
        <div class="controls">
            <input name="email" type="text" style="width:550px;" id="email" class="input-xlarge" value="{{ Request::old('email') }}">
            <span id="Validateemail" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('email') }} </p>
        </div>
    </div>
    <div class="control-group">
        <span id="userroleD" class="control-label">Role:</span>
        <div class="controls">
            <select name="userrole" id="userrole">
                <option value=''>Select</option>
                <option value='user'>User</option>
                <option value='admin'>Admin</option>
            </select>
            <span id="Validateuserrole" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('userrole') }} </p>
        </div>
    </div>
    <div class="control-group">
        <span id="passwordID" class="control-label">Password:</span>
        <div class="controls">
            <input name="password" type="text" style="width:550px;" id="password" class="input-xlarge" value="">
            <span id="Validatepassword" style="color:Red;visibility:hidden;">* Required</span>
        </div>
    </div>
    <div class="control-group">
        <span id="confpasswordID" class="control-label">Conf. Password:</span>
        <div class="controls">
            <input name="confpassword" type="text" style="width:550px;" id="confpassword" class="input-xlarge" value="">
            <span id="Validateconfpassword" style="color:Red;visibility:hidden;">* Required</span>
        </div>
    </div>
    <div class="form-actions">
        <input type="submit" name="addnewpf" value="Add New User" id="addnewuser" class="btn btn-success btn-large">


    </div>
</form>
<div>
    <table cellspacing="0" rules="all" class="table table-bordered" border="1" id="ctl00_ContentPlaceHolder1_grdResultDetails" style="border-collapse:collapse;">
        <tbody>
            <tr>
                <th scope="col" style="width:250px;">UserName</th>
                <th scope="col">Email</th>
                <th scope="col">Role</th>
                <th align="center" scope="col" style="width:150px;">Modified On</th>
               
            </tr>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td align="center">{{  $user->is_admin  === 1 ? "Admin" : "User" }} </td>
                <td align="center">{{ $user->updated_at }}</td>
               
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection