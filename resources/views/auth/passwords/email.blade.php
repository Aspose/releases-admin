@extends('layouts.app-login')

@section('content')

            <div class="panel panel-default">
                
                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form class="form-horizontal" role="form" method="POST" action="{{ route('password.email') }}">
                        {{ csrf_field() }}

                        

                        <div class="control-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <span id="email" class="control-label">E-Mail Address:</span>
                            <div class="controls">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                                @if ($errors->has('email'))
                                    <span class="help-block" style="color: red;">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="control-group" style="padding:20px;">
                            
                                <button type="submit" class="btn btn-primary">
                                    Send Password Reset Link
                                </button>
                           
                        </div>
                    </form>
                </div>
            
</div>
@endsection
