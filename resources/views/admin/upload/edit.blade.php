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
<form name="aspnetForm" method="post" enctype="multipart/form-data" action="/admin/ventures/file/update" id="aspnetForm" class="form-horizontal">

    <input name="_token" type="hidden" value="{{ csrf_token() }}" />
    <input name="edit_id" type="hidden" value="{{ $release->id }}" />
    <div class="control-group">
        <span id="productfamily-span" class="control-label">Product Family:</span>

        <div class="controls">
            <select name="productfamily" onchange="getchildnodes(this, 'product');" id="productfamily" >
                @foreach($familySelected as $key => $single)
                    <option value="{{ $single['url'] }}">{{ $single['text'] }}</option>
                @endforeach
            </select>
            <span style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('productfamily') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="product-span" class="control-label">Product:</span>
        <div class="controls">
            <select name="product" onchange="getchildnodes(this, 'folder');"  id="product" >
                @foreach($productSelected as $key => $single)
                    <option value="{{ $single['url'] }}">{{ $single['text'] }}</option>
                @endforeach
            </select>
            <span  style="color:Red;visibility:hidden;">* Required</span>
            <p  style="color:Red;"> {{ $errors->first('product') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="folder-span" class="control-label">Select a Folder </span>
        
        <div class="controls"> 
            <select name="folder" id="folder">
                @foreach($folders as $key => $value)
                    <option value="{{ $value }}" {{ ( $selected_folder == $value) ? 'selected' : '' }} > {{ $key }} </option>
                @endforeach
            </select>
            <span style="color:Red;visibility:hidden;">* Required</span>
            <p  style="color:Red;"> {{ $errors->first('folder') }} </p>
        </div>
    </div>


    <div class="control-group">
        <span id="title-span" class="control-label">Title </span>
        <div class="controls">
            <input name="title" type="text" maxlength="100" id="title" value="{{ $release->filetitle }}" style="width:450px;" />
            <span  style="color:Red;visibility:hidden;">* Required</span>
            <p  style="color:Red;"> {{ $errors->first('title') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="description-span" class="control-label">Short Description </span>
        <div class="controls">
            <input name="description" type="text" maxlength="500" id="description" value="{{ $release->description }}" style="width:450px;" />
            <p  style="color:Red;"> {{ $errors->first('description') }} </span>
        </div>
       
    </div>

    <div class="control-group">
        <span id="tags-span" class="control-label">Tags</span>
        <div class="controls">
            <input name="tags" type="text" maxlength="500" id="tags" value="{{ $release->tags }}" style="width:450px;" />
            <p  style="color:Red;"> {{ $errors->first('tags') }} </span>
        </div>
       
    </div>

    <div class="control-group">
        <span id="releaseurl-span" class="control-label">Release Notes Url: (Public Wiki) </span>
        <div class="controls">
            <input name="releaseurl" type="text" maxlength="200" id="releaseurl" value="{{ $release->release_notes_url }}" style="width:450px;" />
            <p  style="color:Red;"> {{ $errors->first('releaseurl') }} </span>
        </div>
        
    </div>

    <div class="control-group">
        <div class="p-6">
            <div class="flex items-center">
                <input type="hidden" name="file" id="file" />
                <x-input.uppy />
            </div>
        </div>
    </div>
    
    <div class="control-group alert-info">

    </div>
    <div class="form-actions">
        <input type="submit" name="uploadfile" value="Update" id="uploadfile" class="btn btn-success btn-large" />
    </div>
</form>
<script>
    function getchildnodes(node, childtype){
        console.log(node.value);
        $.ajax({
			url: "{{ route('admin.getchildnodes')}}",
			type: 'POST',
			data: {
				'id': node.value,
                'childtype': childtype,
                "_token": "{{ csrf_token() }}",
			},
			success: function(response) {
				//if (response == 1) {
                  var $select = $('#' + childtype); 
                  $select.find('option').remove();  
                  
                  var listitems = '';
                  if(childtype != 'folder'){
                      
                  }
                  listitems += "<option value=''> </option>";
                    $.each(response, function(key, value){
                        listitems += "<option value='" + value + "'>" + key + "</option>";
                    });
                    $select.append(listitems);
				//}
			}
		});
    }
</script>
</div>
@endsection