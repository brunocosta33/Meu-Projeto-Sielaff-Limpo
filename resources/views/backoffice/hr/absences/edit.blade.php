@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Editar Falta') }}</title>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-1">{{ __('Falta de') }} {{ $absence->staff->user->name ?? '—' }}</h5>
                <p class="text-muted mb-4">
                    <span class="badge" style="{{ \App\Models\HrAbsence::badgeStyle($absence->situacao) }}">{{ $absence->situacao_label }}</span>
                    · {{ __('Registada por') }} {{ $absence->creator->name ?? '—' }} {{ __('em') }} {{ $absence->created_at->format('d/m/Y H:i') }}
                    @if($absence->decided_at)
                        · {{ __('Decidida por') }} {{ $absence->decider->name ?? '—' }} {{ __('em') }} {{ $absence->decided_at->format('d/m/Y H:i') }}
                    @endif
                </p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('backoffice.hr.absences.update', $absence->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label>{{ __('Colaborador') }}</label>
                        <select name="hr_staff_id" class="form-control" required>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}" @selected(old('hr_staff_id', $absence->hr_staff_id) == $s->id)>{{ $s->user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>{{ __('De') }}</label>
                            <input type="date" name="data_inicio" class="form-control" value="{{ old('data_inicio', $absence->data_inicio->toDateString()) }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>{{ __('Até') }}</label>
                            <input type="date" name="data_fim" class="form-control" value="{{ old('data_fim', $absence->data_fim->toDateString()) }}" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>{{ __('Das (só se for parte do dia)') }}</label>
                            <input type="time" name="hora_inicio" class="form-control" value="{{ old('hora_inicio', $absence->hora_inicio ? substr($absence->hora_inicio, 0, 5) : '') }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>{{ __('Às') }}</label>
                            <input type="time" name="hora_fim" class="form-control" value="{{ old('hora_fim', $absence->hora_fim ? substr($absence->hora_fim, 0, 5) : '') }}">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>{{ __('Motivo') }}</label>
                            <select name="motivo" class="form-control" required>
                                @foreach(config('hr.absence_reasons') as $key => $label)
                                    <option value="{{ $key }}" @selected(old('motivo', $absence->motivo) === $key)>{{ __($label) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>{{ __('Situação') }}</label>
                            <select name="situacao" class="form-control" required>
                                @foreach(\App\Models\HrAbsence::STATES as $key => $state)
                                    <option value="{{ $key }}" @selected(old('situacao', $absence->situacao) === $key)>{{ __($state['label']) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Comprovativo') }}</label>
                        @if($absence->documento_path)
                            <div class="mb-2">
                                <a href="{{ route('backoffice.hr.absences.document', $absence->id) }}" target="_blank"><i class="fa fa-paperclip"></i> {{ $absence->documento_nome }}</a>
                                <button type="submit" form="delete-document" class="btn btn-link btn-sm text-danger" onclick="return confirm({{ json_encode(__('Remover o comprovativo?')) }})">
                                    <i class="fa fa-trash"></i> {{ __('Remover') }}
                                </button>
                            </div>
                        @endif
                        <input type="file" name="documento" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png,.heic,.doc,.docx">
                        <small class="form-text text-muted">
                            {{ $absence->documento_path ? __('Escolha um ficheiro para substituir o atual.') : __('Opcional. PDF, imagem ou Word, até 10 MB.') }}
                        </small>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Observações') }}</label>
                        <textarea name="observacoes" class="form-control" rows="3">{{ old('observacoes', $absence->observacoes) }}</textarea>
                    </div>

                    <a class="btn btn-outline-secondary" href="{{ route('backoffice.hr.vacations.index', ['ano' => $absence->data_inicio->year]) }}">
                        <i class="fa fa-arrow-left"></i> {{ __('Voltar') }}
                    </a>
                    <button type="submit" class="btn btn-outline-secondary"><i class="fa fa-save"></i> {{ __('Gravar') }}</button>
                </form>

                @if($absence->documento_path)
                    <form method="POST" action="{{ route('backoffice.hr.absences.document.delete', $absence->id) }}" id="delete-document">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
