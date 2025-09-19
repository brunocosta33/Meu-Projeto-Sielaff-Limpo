@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Nova Máquina') }}</title>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">{{ __('Nova Máquina') }}</h5>

                {{-- Mensagens de erro --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('backoffice.machines.store') }}">
                    @csrf

                    {{-- Loja --}}
                    <div class="form-group mb-3">
                        <label for="store_id">{{ __('Loja') }}</label>
                        <select name="store_id" id="store_id" class="form-control @error('store_id') is-invalid @enderror" required>
                            <option value="">{{ __('-- Selecionar Loja --') }}</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->codigo_loja }} - {{ $store->nome_loja }}
                                </option>
                            @endforeach
                        </select>
                        @error('store_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Serial Number --}}
                    <div class="form-group mb-3">
                        <label for="serial_number">{{ __('Número de Série') }}</label>
                        <input type="text" name="serial_number" id="serial_number"
                               value="{{ old('serial_number') }}"
                               class="form-control @error('serial_number') is-invalid @enderror" required>
                        @error('serial_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Endereço IP --}}
                    <div class="form-group mb-3">
                        <label for="ip_address">{{ __('Endereço IP') }}</label>
                        <input type="text" name="ip_address" id="ip_address"
                               value="{{ old('ip_address') }}"
                               class="form-control @error('ip_address') is-invalid @enderror"
                               placeholder="Ex: 192.168.1.100">
                        @error('ip_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Descrição --}}
                    <div class="form-group mb-3">
                        <label for="descricao">{{ __('Modelo') }}</label>
                        <textarea name="descricao" id="descricao" class="form-control @error('descricao') is-invalid @enderror" rows="3">{{ old('descricao') }}</textarea>
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Botões --}}
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('backoffice.machines.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-save"></i> {{ __('Guardar') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
