@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-dark text-white">{{ __('Verifica tu correo') }}</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('Un link de verificacion ha sido enviado a tu correo.') }}
                        </div>
                    @endif

                    {{ __('Antes de continuar, revisa tu correo.') }}
                    {{ __('Si no has recibido el email') }}, <a href="{{ route('verification.resend') }}">{{ __(' click aqui para enviarlo nuevamente') }}</a>.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
