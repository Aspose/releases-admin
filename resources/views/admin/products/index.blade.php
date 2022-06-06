@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>{{ $title }}</h1>
</div>

@if(Session::has('info'))
    <div class="alert alert-info">
        {{Session::get('info')}}
    </div>
@endif
@if(Session::has('success'))
    <div class="alert alert-success">
        {{Session::get('success')}}
    </div>
@endif

<form name="aspnetForm" method="post" enctype="multipart/form-data" action="/admin/products/manage-allproducts" id="aspnetForm" class="form-horizontal">
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
        <span id="productfoldernameID" class="control-label">SEO Friendly Key / Folder Name:</span>
        <div class="controls">
            <input name="productfoldername" type="text" maxlength="50" id="productfoldername" class="input-xlarge" value="{{ Request::old('productfoldername') }}" >
            <span id="ValidatortxtKey" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('productfoldername') }} </p>
        </div>
    </div>
    <div class="control-group">
        <span id="txtproductnameid" class="control-label">Product Name:</span>
        <div class="controls">
            <input name="productname" type="text" style="width:550px;" id="productname" class="input-xlarge" value="{{ Request::old('productname') }}" >
            <span id="Validatetxtproductname" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('productname') }} </p>
        </div>
    </div>

    <!------------- Meta ---------->
    <div class="control-group">
        <span id="metatitleID" class="control-label">Meta Title:</span>
        <div class="controls">
            <input name="metatitle" type="text" style="width:550px;" id="metatitle" class="input-xlarge" value="{{ Request::old('metatitle') }}">
            <span id="Validatetxtmetatitle" style="color:Red;visibility:hidden;">* Required</span>
        </div>
    </div>

    <div class="control-group">
        <span id="metadescriptionID" class="control-label">Meta Description:</span>
        <div class="controls">
            <input name="metadescription" type="text" style="width:550px;" id="metadescription" class="input-xlarge" value="{{ Request::old('metadescription') }}">
            <span id="Validatemetadescriptione" style="color:Red;visibility:hidden;">* Required</span>
        </div>
    </div>

    <div class="control-group">
        <span id="KeywordsID" class="control-label">Keywords:</span>
        <div class="controls">
            <input name="Keywords" type="text" style="width:550px;" id="Keywords" class="input-xlarge" value="{{ Request::old('Keywords') }}">
            <span id="ValidateKeywords" style="color:Red;visibility:hidden;">* Required</span>
        </div>
    </div>
    <!------------- Meta ---------->
    
    
    <div class="control-group">
        <span id="forumlinkID" class="control-label">Forum Link:</span>
        <div class="controls">
            <input name="forumlink" type="text" maxlength="200" id="forumlink" class="input-xlarge" value="{{ Request::old('forumlink') }}" style="width:550px;" value="https://forum.aspose.com/c/neo">
            <p style="color:Red;"> {{ $errors->first('forumlink') }} </p>
        </div>
        
    </div>

    <div class="control-group">
        <span id="listingpageimagelinkID" class="control-label">ListingPage Image Link</span>
        <div class="controls">
            <input name="listingpageimagelink" type="text" maxlength="200" id="listingpageimagelink" class="input-xlarge"  value="{{ Request::old('listingpageimagelink') }}" style="width:550px;" value="https://forum.aspose.com/c/3d">
            <p style="color:Red;"> {{ $errors->first('listingpageimagelink') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="listingpagedescID" class="control-label">ListingPage Desciption</span>
        <div class="controls">
            <input name="listingpagedesc" type="text" maxlength="200" id="listingpagedesc" class="input-xlarge"  value="{{ Request::old('listingpagedesc') }}" style="width:550px;" value="https://forum.aspose.com/c/3d">
            <p style="color:Red;"> {{ $errors->first('listingpagedesc') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="SortOrderID" class="control-label">Sort Order / Weight</span>
        <div class="controls">
            <input name="SortOrder" type="text" maxlength="2" id="SortOrder" class="input-xlarge" value="{{ Request::old('SortOrder') }}" style="width:50px;" >
            <span id="ValidatetxtSortOrder" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('SortOrder') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="pagecontentID" class="control-label">Page Content</span>
        <div class="controls">
            <textarea  style="width: 850px;" name="pagecontent" id="pagecontent" rows="53" cols="65">
               
       <?php echo' 
        {{< Common/h1 text="###PRODUCTNAME_PLACEHOLDER####" >}}
        {{< Common/paragraph>}}
        {{< Common/ul>}}
        {{< Common/li >}} {{< /Common/li >}}
        {{< /Common/ul>}}
        {{< /Common/paragraph>}}
        {{< Common/hr >}}
        
'; ?>  
            </textarea>
            <p style="color:Red;"> {{ $errors->first('listingpagedesc') }} </p>
        </div>
    </div>


    <div class="control-group alert-info">





    </div>
    <div class="form-actions">
        <input type="submit" name="addnewpf" value="Add New Product"  id="addnewpf" class="btn btn-success btn-large">


    </div>

</form>

</div>
@endsection