@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>{{ $title }} "{{ $release->filetitle }}"</h1>
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
<form name="translateform" method="post" onsubmit = "return validate(event.preventDefault())" enctype="multipart/form-data" action="/admin/ventures/file/onlytranslate" id="translateform" class="form-horizontal">

    <input name="_token" type="hidden" value="{{ csrf_token() }}" />
    <input name="edit_id" type="hidden" value="{{ $release->id }}" />
    
    <div class="form-actionss" style="text-align:right">
        @if($show_translate_button)
        <input type="submit" name="translatefile" value="Translate" id="translatefile" class="btn btn-success btn-large" />
        @else
        <p>SPREADSHEETIDMANUAL missing in env  (domain.env) </p>
        <p>add <span style="color:red"> SPREADSHEETIDMANUAL=***** </span> and <span style="color:red">MULTILINGUAL=true </span> in env (domain.env)</p>
        @endif
    </div>
    <div class="control-group">
        <span id="description-span" style="text-align:left;width:100%" class="control-label"><strong>File Content: [ File Path <span style="color:red">"{{ $release->folder_link }}" ]</span></strong></span>
    </div>

    <div class="control-group">
        <div class="controls" style="margin-left:0;">

            <textarea name="filecontent" id="filecontent"   style="width: 100%" cols="65" rows="85" require="required"></textarea>
            <p  style="color:Red;"> {{ $errors->first('description') }} </span>
        </div>
       
    </div>


   
</form>
<script>
    function validate(){
        let filecontent = document.getElementById('filecontent').value;
        //alert(title);
        if(filecontent == ""){
            //event.preventDefault();
            alert( "Please add filecontent");
            document.getElementById('filecontent').focus()
            return false;
        }else{
            
            $.ajax({
                url: "{{ route('admin.onlytranslate')}}",
                type: 'POST',
                data: {
                    'filecontent': filecontent,
                    'id': <?php echo $release->id ?>,
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {
                    alert(response);
                    return false;
                }
		    });
        }
    }
</script>
</div>
@endsection