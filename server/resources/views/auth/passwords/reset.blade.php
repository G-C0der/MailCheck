@extends('layouts.login')

@section('content')
    <div class="container h-100">
        <div class="row h-100">
            <div class="col-sm-9 col-md-7 col-lg-5 mx-auto h-100">
                <div class="card card-signin my-5">
                    <div class="card-body">
                        <div class="login-logo">
                            <div class="brand-logo mx-auto"></div>
                        </div>

                        @if ($errors->count())
                            <div class="alert alert-danger">
                                {{ trans('messages.failed') }}
                            </div>
                        @endif

                        <div class="alert alert-danger hide error-message"></div>
                        <!-- Form -->
                        <form class="form-signin" id="resetform" role="form" method="POST" action="{{ url('/password/reset') }}">
                            @csrf

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="form-label-group">
                                <input autocomplete="username" type="email" name="email" id="inputEmail" class="form-control" placeholder="{{__('field.email')}}" required autofocus>
                                <label for="inputEmail">{{__('field.email')}}</label>
                            </div>

                            <div class="form-label-group">
                                <input autocomplete="current-password"
                                       type="password" name="password" id="inputPassword" class="form-control" placeholder="{{__('field.password')}}" required>
                                <label for="inputPassword">{{__('field.password')}}</label>
                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-label-group">
                                <input autocomplete="confirm-password"
                                       type="password" name="password_confirmation" id="inputPassword" class="form-control" placeholder="{{__('field.password')}}" required>
                                <label for="inputPassword">{{__('field.password')}}</label>
                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <button class="btn btn-lg btn-primary btn-block text-uppercase" type="submit">{{__('auth.reset_pw')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
