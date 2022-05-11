@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>{{ $title }}</h1>
</div>
 <!-- {!! Form::open(['url' => '/admin/ventures/amazon-s3-settings', 'name' => 'aspnetForm', 'class' => 'form-horizontal', 'id' => 'aspnetForm', 'role' => 'form']) !!} -->
 @include('flash::message')
 <form name="aspnetForm" method="post" action="/admin/ventures/amazon-s3-settings"  id="aspnetForm" class="form-horizontal"> 
    <div class="control-group">
    <input name="_token" type="hidden" value="{{ csrf_token() }}"/>
    <input name="edit_id" type="hidden" value="{{ isset($settings->id ) ? $settings->id  : '' }}"/>
        <span id="bucketname" class="control-label">Bucket Name</span>
        <div class="controls">
            <input name="bucketname" type="text" value="{{ isset($settings->bucketname ) ? $settings->bucketname  : '' }}" maxlength="50" id="bucketname" class="input-xlarge" require />
            <span  style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('bucketname') }} </p>
        </div>
    </div>
    <div class="control-group">
        <span id="apikey" class="control-label">API Key</span>
        <div class="controls">
            <input name="apikey" type="text" value="{{ isset($settings->apikey ) ? $settings->apikey  : '' }}" maxlength="200" id="apikey" class="input-xlarge" style="width:400px;" require />
            <span  style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('apikey') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="apisecret" class="control-label">API Secret</span>
        <div class="controls">
            <input name="apisecret" type="text" value="{{ isset($settings->apisecret ) ? $settings->apisecret  : '' }}" maxlength="200" id="apikey" class="input-xlarge" style="width:400px;" require />
            <span style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('apisecret') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="hugositeurl" class="control-label">Hugo Site Url</span>
        <div class="controls">
            <input name="hugositeurl" type="text" value="{{ isset($settings->hugositeurl ) ? $settings->hugositeurl  : '' }}" maxlength="200" id="apikey" class="input-xlarge" style="width:400px;" require />
            <span style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('hugositeurl') }} </p>
        </div>
    </div>
    <div class="control-group alert-info">


    </div>
    <div class="form-actions">
        <input type="submit" name="savesettings" value="Save Settings"  id="savesettings" class="btn btn-success btn-large" />
        <input type="button" name="verifysettings" value="Verify Settings" id="verifysettings" class="btn btn-success btn-large" />
    </div>
    <!-- {!! Form::close() !!} -->
</form> 
</div>
@endsection