@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>{{ $title }}</h1>
</div>

@include('flash::message')
<form name="aspnetForm" method="post" enctype="multipart/form-data" action="/admin/ventures/file/upload" id="aspnetForm" class="form-horizontal">

    <input name="_token" type="hidden" value="{{ csrf_token() }}" />
    <div class="control-group">
        <span id="productfamily-span" class="control-label">Product Family:</span>

        <div class="controls">
            <select name="productfamily" onchange="getchildnodes(this, 'product');" id="productfamily">
                <option selected="selected" value="">-- Select Product Family --</option>
                @foreach($DropDownContent as $key => $single)
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
            <select name="product" onchange="getchildnodes(this, 'folder');"  id="product">

            </select>
            <span  style="color:Red;visibility:hidden;">* Required</span>
            <p  style="color:Red;"> {{ $errors->first('product') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="folder-span" class="control-label">Select a Folder </span>

        <div class="controls">
            <select name="folder" id="folder">

            </select>
            <span style="color:Red;visibility:hidden;">* Required</span>
            <p  style="color:Red;"> {{ $errors->first('folder') }} </p>
        </div>
    </div>


    <div class="control-group">
        <span id="title-span" class="control-label">Title </span>
        <div class="controls">
            <input name="title" type="text" maxlength="100" id="title" value="New Release Title" style="width:450px;" />
            <span  style="color:Red;visibility:hidden;">* Required</span>
            <p  style="color:Red;"> {{ $errors->first('title') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="description-span" class="control-label">Short Description </span>
        <div class="controls">
            <input name="description" type="text" maxlength="500" id="description" value="Short Description Short Description" style="width:450px;" />
            <p  style="color:Red;"> {{ $errors->first('description') }} </span>
        </div>
       
    </div>

    <div class="control-group">
        <span id="releaseurl-span" class="control-label">Release Notes Url: (Public Wiki) </span>
        <div class="controls">
            <input name="releaseurl" type="text" maxlength="200" id="releaseurl" value="ptc.coms" style="width:450px;" />
            <p  style="color:Red;"> {{ $errors->first('releaseurl') }} </span>
        </div>
        
    </div>

    <div class="control-group">
        <span id="filetoupload-span" class="control-label">Select File: </span>
        <div class="controls">
            <input type="file" name="filetoupload" id="filetoupload" />
            <span style="color:Red;visibility:hidden;">* Required</span>
            <div><i>(File Name with Version e.g Aspose.Words_16.6.0.msi)</i></div>
            <p  style="color:Red;"> {{ $errors->first('filetoupload') }} </p>
        </div>
        
    </div>

    <div class="control-group alert-info">

    </div>
    <div class="form-actions">
        <input type="submit" name="uploadfile" value="Publish" id="uploadfile" class="btn btn-success btn-large" />
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