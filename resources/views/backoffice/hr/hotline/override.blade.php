@extends('layouts.backoffice_master')

@php(\Carbon\Carbon::setLocale(app()->getLocale()))

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Substituição de Turno') }}</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-1">{{ __('Substituição de Turno') }}</h5>
                <p class="text-muted mb-4">
                    {{ ucfirst($date->translatedFormat('l, d/m/Y')) }} ·
                    {{ __('Turno de') }} <strong>{{ $staff->user->name }}</strong> ·
                    {{ \App\Services\HotlineRotation::formatHours($shift) }}
                </p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('backoffice.hr.hotline.override.store') }}">
                    @csrf
                    <input type="hidden" name="hr_staff_id" value="{{ $staff->id }}">
                    <input type="hidden" name="data" value="{{ $date->toDateString() }}">

                    <div class="form-group">
                        <label>{{ __('Quem faz o turno') }}</label>
                        <select name="substitute_user_id" id="substitute_user_id" class="form-control">
                            <option value="">{{ __('— Sem substituto (turno a descoberto) —') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected(old('substitute_user_id', $override?->substitute_user_id) == $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Motivo') }}</label>
                        <select name="motivo" id="motivo" class="form-control" required>
                            @foreach(config('hr.override_reasons') as $key => $label)
                                <option value="{{ $key }}" @selected(old('motivo', $override?->motivo) === $key)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group p-3 rounded" id="devolucao-field" style="background:#eef4ff; display:none">
                        <label class="font-weight-bold mb-1"><i class="fa fa-exchange-alt"></i> {{ __('Troca de dias (opcional)') }}</label>
                        <p class="small text-muted mb-2">
                            {{ __('Se é uma troca com um colega da mesma escala, indique o dia em que') }} <strong>{{ $staff->user->name }}</strong>
                            {{ __('fica com o turno do colega. Os dois dias ficam registados de uma vez.') }}
                        </p>
                        <input type="date" name="devolucao_data" class="form-control" style="max-width: 220px" value="{{ old('devolucao_data') }}">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>{{ __('Hora início') }}</label>
                            <input type="time" name="hora_inicio" class="form-control"
                                   value="{{ old('hora_inicio', $override?->hora_inicio ? substr($override->hora_inicio, 0, 5) : '') }}" placeholder="{{ $shift['inicio'] }}">
                        </div>
                        <div class="form-group col-md-3">
                            <label>{{ __('Hora fim') }}</label>
                            <input type="time" name="hora_fim" class="form-control"
                                   value="{{ old('hora_fim', $override?->hora_fim ? substr($override->hora_fim, 0, 5) : '') }}">
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <small class="text-muted">{{ __('Deixe em branco para manter o horário normal') }} ({{ $shift['inicio'] }}–{{ $shift['fim'] }}).</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Observações') }}</label>
                        <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes', $override?->observacoes) }}</textarea>
                    </div>

                    @if($override)
                        <p class="small text-muted">
                            {{ __('Registado por') }} {{ $override->creator->name ?? '—' }} {{ __('em') }} {{ $override->updated_at->format('d/m/Y H:i') }}
                        </p>
                    @endif

                    <a class="btn btn-outline-secondary" href="{{ route(\App\Services\HotlineRotation::kindOf($staff) === 'piquete' ? 'backoffice.hr.piquete.index' : 'backoffice.hr.hotline.index', ['mes' => $date->format('Y-m')]) }}">
                        <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                    </a>
                    <button type="submit" class="btn btn-outline-secondary"><i class="fa fa-save"></i> {{ __('Gravar') }}</button>
                </form>

                @if($override)
                    <form method="POST" action="{{ route('backoffice.hr.hotline.override.delete', $override->id) }}" class="mt-3"
                          onsubmit="return confirm({{ json_encode(__('Remover a substituição e repor a rotação normal neste dia?')) }})">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa fa-undo"></i> {{ __('Repor rotação normal') }}</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var hotliners = @json($otherHotlinerUserIds->map(fn ($id) => (string) $id)->values());
        var motivo = document.getElementById('motivo');
        var substitute = document.getElementById('substitute_user_id');
        var field = document.getElementById('devolucao-field');
        var toggle = function () {
            field.style.display = (motivo.value === 'troca' && hotliners.indexOf(substitute.value) !== -1) ? '' : 'none';
        };
        motivo.addEventListener('change', toggle);
        substitute.addEventListener('change', toggle);
        toggle();
    });
</script>
@endsection
