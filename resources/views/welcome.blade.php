@extends('layouts.admin')
@section('title')
Dashboard
@endsection
@section('content')

<div class="row">
    <div class=" col-md-2">
        <a href="{{ route('propiedades') }}">
            <div class="social-box bg-grubsa text-primary">
                <i class="fas fa-home"></i>
                <span class="text-grubsa-clear">Polizas a excel</span>
            </div>
        </a>
    </div>
    <div class=" col-md-2">
        <a href="{{ route('poliza_ws') }}">
            <div class="social-box bg-grubsa text-primary">
                <i class="fas fa-users"></i>
                <span class="text-grubsa-clear">Polizas WS</span>
            </div>
        </a>
    </div>
    <div class=" col-md-2">
        <a href="{{ route('propiedades') }}">
            <div class="social-box bg-grubsa text-primary">
                <i class="fas fa-money-bill-wave"></i>
                <span class="text-grubsa-clear">Pagos referenciados</span>
            </div>
        </a>
    </div>
    <div class=" col-md-2">
        <a href="{{ route('propiedades') }}">
            <div class="social-box bg-grubsa text-primary">
                <i class="fas fa-phone"></i>
                <span class="text-grubsa-clear">Descargar documentos</span>
            </div>
        </a>
    </div>
    <div class=" col-md-2">
        <a href="{{ route('propiedades') }}">
            <div class="social-box bg-grubsa text-primary">
                <i class="fas fa-calendar-alt"></i>
                <span class="text-grubsa-clear">PLD</span>
            </div>
        </a>
    </div>
</div>

@push('scripts')
@endpush 
@endsection