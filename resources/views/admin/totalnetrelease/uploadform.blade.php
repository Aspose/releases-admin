@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">

        



    <div class="panel panel-primary">
      <div class="panel-heading"><h2>{{ $title }}</div>
      <div class="panel-body">
   
        @if ($message = Session::get('success'))
        <div class="alert alert-success alert-block">
            <button type="button" class="close" data-dismiss="alert">×</button>
                <strong>{{ $message }}</strong>
        </div>
        @endif
  
        @if (count($errors) > 0)
            <div class="alert alert-danger">
                <strong>Whoops!</strong> There were some problems with your input.
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
  
        <form action="{{ route('file.upload.post') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
  
                <div class="col-md-6">
                    <input type="file" name="file" class="form-control">
                    <input type="hidden" name="path" value="{{ $path }}" class="form-control">
                </div>
   
                <div class="col-md-6">
                    <button type="submit" class="btn btn-success">Upload</button>
                </div>
   
            </div>
        </form>
            @foreach($files as $file)

                {{ $file }} <hr>
            @endforeach

            <form action="{{ route('file.remove.post') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <input type="hidden" name="path" value="{{ $path }}" class="form-control">
                </div>
   
                <div class="col-md-6">
                    <button type="submit" class="btn btn-success">Delete Files in path</button>
                </div>
   
            </div>
        </form>
      </div>
    </div>
</div>

</div>
</div>

<script>
    
</script>
@endsection