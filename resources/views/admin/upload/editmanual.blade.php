@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>{{ $title }}</h1>
</div>

@include('flash::message')
@if(Session::has('error'))
    <div class="alert alert-danger">
        {{Session::get('error')}}
    </div>
@endif
@if(Session::has('success'))
    <div class="alert alert-success">
        {{Session::get('success')}}
    </div>
@endif
<form name="aspnetForm" method="post" enctype="multipart/form-data" action="/admin/ventures/file/updatemaual" id="aspnetForm" class="form-horizontal">

    <input name="_token" type="hidden" value="{{ csrf_token() }}" />
    <input name="edit_id" type="hidden" value="{{ $release->id }}" />

    <div class="control-group">
        <span id="productfamily-span" class="control-label">date_added</span>
        <div class="controls">
            <input readonly name="date_added" type="text" maxlength="500" id="date_added" value="{{ $release->date_added }}" style="width:750px;" />
        </div>
    </div>

    <div class="control-group">
        <span id="productfamily-span" class="control-label">created_at</span>
        <div class="controls">
            <input readonly name="created_at" type="text" maxlength="500" id="created_at" value="{{ $release->created_at }}" style="width:750px;" />
        </div>
    </div>

    <div class="control-group">
        <span id="productfamily-span" class="control-label">updated_at</span>
        <div class="controls">
            <input readonly name="updated_at" type="text" maxlength="500" id="updated_at" value="{{ $release->updated_at }}" style="width:750px;" />
        </div>
    </div>

    <div class="control-group">
        <span id="productfamily-span" class="control-label">is_new</span>
        <div class="controls">
            <input readonly name="is_new" type="text" maxlength="500" id="is_new" value="{{ $release->is_new }}" style="width:750px;" />
        </div>
    </div>

    <div class="control-group">
        <span id="productfamily-span" class="control-label">Product Family:</span>
        <div class="controls">
            <input readonly name="family" type="text" maxlength="500" id="family" value="{{ $release->family }}" style="width:750px;" />
        </div>
    </div>

    <div class="control-group">
        <span id="product-span" class="control-label">Product:</span>
        <div class="controls">
            <input readonly name="product" type="text" maxlength="500" id="product" value="{{ $release->product }}" style="width:750px;" />
        </div>
    </div>

    <div class="control-group">
        <span id="folder-span" class="control-label">Folder </span>
        
        <div class="controls"> 
            <input readonly name="folder" type="text" maxlength="500" id="folder" value="{{ $release->folder }}" style="width:750px;" />
        </div>
    </div>

    <div class="control-group">
        <span id="folder-span" class="control-label">folder_link </span>
        
        <div class="controls"> 
            <input readonly name="folder_link" type="text" maxlength="500" id="folder_link" value="{{ $release->folder_link }}" style="width:750px;" />
        </div>
    </div>


    

    <div class="control-group">
        <span id="title-span" class="control-label">Title </span>
        <div class="controls">
            <input readonly name="title" type="text" maxlength="100" id="title" value="{{ $release->filetitle }}" style="width:450px;" />
            <span  style="color:Red;visibility:hidden;">* Required</span>
            <p  style="color:Red;"> {{ $errors->first('title') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="description-span" class="control-label">Short Description </span>
        <div class="controls">
            <input readonly name="description" type="text" maxlength="500" id="description" value="{{ $release->description }}" style="width:450px;" />
            <p  style="color:Red;"> {{ $errors->first('description') }} </span>
        </div>
       
    </div>

    <div class="control-group">
        <span id="releaseurl-span" class="control-label">Release Notes Url: (Public Wiki) </span>
        <div class="controls">
            <input readonly name="releaseurl" type="text" maxlength="200" id="releaseurl" value="{{ $release->release_notes_url }}" style="width:450px;" />
            <p  style="color:Red;"> {{ $errors->first('releaseurl') }} </span>
        </div>
        
    </div>

    <div class="control-group">
         <span id="releaseurl-span" class="control-label">S3 Url</span>
        <div class="controls">
        <input readonly name="s3_path" type="text" maxlength="500" id="s3_path" value="{{ $release->s3_path }}" style="width:750px;" />
        </div>
    </div>

    <div class="control-group">
        <span id="releaseurl-span" class="control-label">view_count</span>
        <div class="controls">
        <input readonly name="view_count" type="text" maxlength="500" id="view_count" value="{{ $release->view_count }}" style="width:750px;" />
        </div>
    </div>

    <div class="control-group">
        <span id="releaseurl-span" class="control-label">download_count</span>
        <div class="controls">
        <input readonly name="download_count" type="text" maxlength="500" id="download_count" value="{{ $release->download_count }}" style="width:750px;" />
        </div>
    </div>

    <div class="control-group">
        <span id="releaseurl-span" class="control-label">posted_by</span>
        <div class="controls">
        <input readonly name="posted_by" type="text" maxlength="500" id="posted_by" value="{{ $release->posted_by }}" style="width:750px;" />
        </div>
    </div>

    <div class="control-group">
        <span id="releaseurl-span" class="control-label">filesize</span>
        <div class="controls">
        <input readonly name="filesize" type="text" maxlength="500" id="filesize" value="{{ $release->filesize }}" style="width:750px;" />
        </div>
    </div>

    <div class="control-group">
        <span id="releaseurl-span" class="control-label">filename</span>
        <div class="controls">
        <input readonly name="filename" type="text" maxlength="500" id="filename" value="{{ $release->filename }}" style="width:750px;" />
        </div>
    </div>

    <div class="control-group">
        <span id="releaseurl-span" class="control-label">weight</span>
        <div class="controls">
        <input  name="weight" type="text" maxlength="500" id="weight" value="{{ $release->weight }}" style="width:750px;" />
        </div>
    </div>
    
    
    <div class="control-group">
        <span id="releaseurl-span" class="control-label">sha1</span>
        <div class="controls">
        <input readonly name="sha1" type="text" maxlength="500" id="sha1" value="{{ $release->sha1 }}" style="width:750px;" />
        </div>
    </div>
    <div class="control-group">
        <span id="releaseurl-span" class="control-label">md5</span>
        <div class="controls">
        <input readonly name="md5" type="text" maxlength="500" id="md5" value="{{ $release->md5 }}" style="width:750px;" />
        </div>
    </div>

    <div class="control-group">
        <span id="releaseurl-span" class="control-label">Etag</span>
        <div class="controls">
        <input readonly name="file" type="text" maxlength="500" id="file" value="{{ $release->etag_id }}" style="width:750px;" />
        </div>
    </div>
    
    <div class="control-group alert-info">

    </div>
    <div class="form-actions">
        <input type="submit" name="uploadfile" value="Update" id="uploadfile" class="btn btn-success btn-large" />
    </div>
</form>
</div>
@endsection