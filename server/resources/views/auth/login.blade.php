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
                        <form class="form-signin ajax-login" id="loginform" role="form" method="POST" action="{{ url('/login') }}">
                            @csrf

                            <div class="form-label-group">
                                <input autocomplete="username" type="text" name="username" id="inputEmail" class="form-control" placeholder="{{__('Username')}}" required autofocus>
                                <label for="inputEmail">{{__('Username')}}</label>
                                {{-- @if ($errors->has('email'))
                                    <p class="info-error">{{ trans('messages.email') }}</p>
                                @endif --}}
                            </div>

                            <div class="form-label-group">
                                <input autocomplete="current-password"
                                       type="password" name="password" id="inputPassword" class="form-control" placeholder="{{__('Passwort')}}" required>
                                <label for="inputPassword">{{__('Passwort')}}</label>
                                {{-- @if ($errors->has('email'))
                                    <p class="info-error">{{ trans('messages.password') }}</p>
                                @endif --}}
                            </div>

                            {{--<div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" name="remember" class="custom-control-input" id="remember-token">
                                <label class="custom-control-label" for="remember-token">{{__('auth.remember')}}</label>
                            </div>--}}
                            <button class="btn btn-lg btn-primary btn-block text-uppercase" type="submit">{{__('Login')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
