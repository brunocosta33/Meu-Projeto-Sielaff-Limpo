@extends('layouts.backoffice_master')

@php($isEdit = $member->exists)

@section('head-meta')
    <title>{{ config('app.name') }} - {{ $isEdit ? __('Editar Colaborador') : __('Novo Colaborador') }}</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">{{ $isEdit ? __('Editar Colaborador') : __('Novo Colaborador') }}</h5>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ $isEdit ? route('backoffice.hr.staff.update', $member->id) : route('backoffice.hr.staff.store') }}">
                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    <div class="form-group">
                        <label>{{ __('Utilizador') }}</label>
                        <select name="user_id" class="form-control" required>
                            @unless($isEdit)<option value="">{{ __('Selecione...') }}</option>@endunless
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id', $member->user_id) == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @unless($isEdit)
                            <small class="form-text text-muted">{{ __('Se a pessoa não aparece, crie primeiro o utilizador em Administração → Users.') }}</small>
                        @endunless
                    </div>

                    <div class="form-group">
                        <label>{{ __('Função') }}</label>
                        <select name="funcao" id="funcao" class="form-control" required>
                            <option value="">{{ __('Selecione...') }}</option>
                            @foreach(config('hr.functions') as $key => $label)
                                <option value="{{ $key }}" @selected(old('funcao', $member->funcao) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">{{ __('Os hotliners entram na escala da Hotline; os técnicos podem entrar na escala de piquete.') }}</small>
                    </div>

                    <div class="form-group" id="rotation-group-field">
                        <label id="rotation-group-label">{{ __('Grupo de rotação (Hotliner)') }}</label>
                        <select name="rotation_group" class="form-control">
                            <option value="">{{ __('— Não entra na escala —') }}</option>
                            <option value="A" @selected(old('rotation_group', $member->rotation_group) === 'A')>{{ __('Grupo A') }}</option>
                            <option value="B" @selected(old('rotation_group', $member->rotation_group) === 'B')>{{ __('Grupo B') }}</option>
                        </select>
                        <small class="form-text text-muted" data-help="hotliner">
                            {{ __('Os dois hotliners devem estar em grupos diferentes: quando um faz a semana com fim de semana, o outro faz a semana normal. Se a escala estiver trocada uma semana, troque os grupos.') }}
                        </small>
                        <small class="form-text text-muted" data-help="tecnico">
                            {{ __('Sábados alternados das') }} {{ config('hr.piquete.hours')[0] }} {{ __('às') }} {{ config('hr.piquete.hours')[1] }}. {{ __('Em cada grupo deve haver um técnico do Norte e um do Sul. Se a escala estiver trocada um sábado, troque os grupos.') }}
                        </small>
                    </div>

                    <div class="form-group" id="zona-field">
                        <label>{{ __('Zona (piquete)') }}</label>
                        <select name="zona" class="form-control">
                            <option value="">—</option>
                            @foreach(config('hr.zones') as $key => $label)
                                <option value="{{ $key }}" @selected(old('zona', $member->zona) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group form-check">
                        <input class="form-check-input" type="checkbox" name="ativo" value="1" id="ativo" @checked(old('ativo', $member->ativo))>
                        <label class="form-check-label" for="ativo">{{ __('Ativo') }}</label>
                    </div>

                    <a class="btn btn-outline-secondary" href="{{ route('backoffice.hr.staff.index') }}">
                        <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                    </a>
                    <button type="submit" class="btn btn-outline-secondary"><i class="fa fa-save"></i> {{ __('Gravar') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var funcao = document.getElementById('funcao');
        var field = document.getElementById('rotation-group-field');
        var zona = document.getElementById('zona-field');
        var label = document.getElementById('rotation-group-label');
        var toggle = function () {
            var f = funcao.value;
            field.style.display = (f === 'hotliner' || f === 'tecnico') ? '' : 'none';
            zona.style.display = f === 'tecnico' ? '' : 'none';
            label.textContent = f === 'tecnico' ? 'Grupo de piquete (sábados)' : 'Grupo de rotação (Hotliner)';
            document.querySelectorAll('[data-help]').forEach(function (h) { h.style.display = h.dataset.help === f ? '' : 'none'; });
        };
        funcao.addEventListener('change', toggle);
        toggle();
    });
</script>
@endsection
