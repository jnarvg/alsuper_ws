@extends('layouts.app')
@section('content')
<form class="user" method="POST" action="{{ route('login') }}">
    @csrf
    <div class="form-group">
      <input type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }} form-control-user" id="email" name="email" aria-describedby="emailHelp" placeholder="{{ __('Correo electronico...') }}" required autofocus>
      @if ($errors->has('email'))
        <span class="invalid-feedback" role="alert">
            <strong>{{ $errors->first('email') }}</strong>
        </span>
      @endif
    </div>
    <div class="form-group">
      <input type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }} form-control-user" id="password" name="password" placeholder="Password" required>
      @if ($errors->has('password'))
        <span class="invalid-feedback" role="alert">
            <strong>{{ $errors->first('password') }}</strong>
        </span>
      @endif
    </div>
    <div class="form-group">
      <div class="custom-control custom-checkbox small">
        <input type="checkbox" class="custom-control-input" id="remember" name="remember">
        <label class="custom-control-label" for="customCheck">Remember Me</label>
      </div>
    </div>
    <button type="submit" class="btn btn-dark btn-user btn-block">
      Login
    </button>
</form>
<hr>
@if (Route::has('password.request'))
<div class="text-center">
    <a class="small" href="forgot-password.html">Forgot Password?</a>
</div>
@endif
<br>
<br>
<br>
<br>
@endsection
