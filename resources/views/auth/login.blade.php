@extends('layouts.app-login')

@section('content')

            <div class="panel panel-default">
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}

                        <!-- <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">E-Mail Address</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div> -->

                        <div class="control-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <span id="email" class="control-label">E-Mail Address:</span>
                            <div class="controls">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus>
                            </div>
                        </div>

                        <div class="control-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <span id="password" class="control-label">Password:</span>
                            <div class="controls">
                            <input id="password" type="password" class="form-control" name="password" required>
                            </div>
                        </div>

                        <!-- <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
                                    </label>
                                </div>
                            </div>
                        </div> -->
                        <div class="control-group" style="text-align:center">
                            @if ($errors->has('email'))
                                            <span class="help-block" style="color:red">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                            @endif
                            @if ($errors->has('password'))
                                    <span class="help-block" style="color:red">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                            @endif
                        </div>
                        <div class="control-group" style="text-align:center">
                            
                                <button type="submit" class="btn btn-primary">
                                    Login
                                </button>

                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    Forgot Your Password?
                                </a>
                            
                        </div>
                    </form>
                </div>
</div>
@endsection
