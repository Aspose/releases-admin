@extends('layouts.app-login')

@section('content')

            <div class="panel panel-default">
                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form class="form-horizontal" role="form" method="POST" action="{{ route('password.request') }}">
                        {{ csrf_field() }}

                        <input type="hidden" name="token" value="{{ $token }}">


                        <div class="control-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <span id="email" class="control-label">E-Mail Address:</span>
                            <div class="controls">
                            <input id="email" type="email" class="form-control" name="email" value="{{  old('email') }}" required autofocus>
                                @if ($errors->has('email'))
                                    <span class="help-block" style="color: red;">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>



                        <div class="control-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <span id="password" class="control-label">Password:</span>
                            <div class="controls">
                            <input id="password" type="password" class="form-control" name="password" required>
                                @if ($errors->has('password'))
                                    <span class="help-block" style="color: red;">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                    

                        <div class="control-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                            <span id="password-confirm" class="control-label">Confirm Password:</span>
                            <div class="controls">
                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block" style="color: red;">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="control-group" style="">
                            <button type="submit" class="btn btn-primary">
                                Reset Password
                            </button>
                        </div>
                    </form>
    </div>
</div>
@endsection
