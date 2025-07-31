@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Verificando email') }}</div>
                    <div class="card-body">
                        @if (session('resent'))
                            <div class="alert alert-success" role="alert">
                                {{ __('Enviamos um link de verificação ao seu email.') }}
                            </div>
                        @endif                        
                        {{ __('Antes de continuar verifique o seu email.') }}
                        {{ __('Se você não recebeu o email') }}, <a href="{{ route('verification.resend') }}">{{ __('clique aqui para enviar novamente.') }}</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
