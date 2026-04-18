@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Atualizar Estado da Tarefa') }}</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;700&display=swap" rel="stylesheet">
@endsection

@push('styles')
    <link rel="stylesheet" href="https://interno.farmaciagaiajardim.com/assets/css/style.css">
    <link rel="stylesheet" href="https://interno.farmaciagaiajardim.com/assets/css/style_header_footer.css">
    <link rel="stylesheet" href="{{ asset('assets/css/style_content_pages.css') }}">
@endpush

@section('content')
<div class="bg-white d-flex align-items-center gap-4 mb-4 page-main-title">
    <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
        <i class="fas fa-tasks fa-lg"></i>
    </div>
    <h1 class="mb-0">{{ __('Atualizar Estado da Tarefa') }}</h1>
</div>


@php
    function normalize($str) {
        return strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $str));
    }
    $pivotEstadoNorm = normalize($pivot->estado ?? '');
    $isConcluida = $pivotEstadoNorm === normalize('Concluída');
@endphp

@if($isConcluida)
    <div class="bg-white p-3">
        <div class="col-md-7 col-lg-6 ms-auto">
            <div class="form-group mb-3">
                <label class="mb-2"><strong>{{ __('Tarefa') }}</strong></label>
                <input type="text" class="form-control rounded-pill bg-light" value="{{ $schedule->task->title ?? '-' }}" disabled>
            </div>
            <div class="form-group mb-3">
                <label class="mb-2"><strong>{{ __('Descrição') }}</strong></label>
                <textarea class="form-control bg-light rounded-4" rows="3" style="resize: none;" disabled>{{ $schedule->description }}</textarea>
            </div>
            <div class="d-flex flex-wrap gap-2 text-center mb-3">
                    <div class="col-3 form-group flex-fill">
                        <label class="mb-2"><strong>{{ __('Data Limite') }}</strong></label>
                        <input type="text" class="form-control rounded-pill bg-light text-center" value="{{ $schedule->display_date ? \Carbon\Carbon::parse($schedule->display_date)->format('d/m/Y') : '-' }}" disabled>
                    </div>
                    <div class="col-3 form-group flex-fill">
                        <label class="mb-2"><strong>{{ __('Hora Limite') }}</strong></label>
                        <input type="text" class="form-control rounded-pill bg-light text-center" value="{{ $schedule->display_time ? \Carbon\Carbon::parse($schedule->display_time)->format('H:i') : '-' }}" disabled>
                    </div>
                <div class="col-3 form-group flex-fill">
                    <label class="mb-2"><strong>{{ __('Prioridade') }}</strong></label>
                    <span class="detalhe-badge prioridade-{{ strtolower($schedule->prioridade) }}">{{ $schedule->prioridade }}</span>
                </div>
                <div class="col-3 form-group flex-fill">
                    <label class="mb-2"><strong>{{ __('Tarefa Grupo') }}</strong></label>
                    <span class="detalhe-badge {{ $schedule->grupo ? 'sim' : 'nao' }}">{{ $schedule->grupo ? __('Sim') : __('Não') }}</span>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 text-center mb-3">
                <div class="col-4 form-group flex-fill">
                    <label class="mb-2"><strong>{{ __('Estado da Tarefa') }}</strong></label>
                    <span class="detalhe-badge estado-{{ strtolower($pivot->estado ?? 'pendente') }}">{{ $pivot->estado ?? '-' }}</span>
                </div>
                <div class="col-4 form-group flex-fill">
                    <label class="mb-2"><strong>{{ __('Data Conclusão') }}</strong></label>
                    <input type="text" class="form-control rounded-pill bg-light text-center" value="{{ $pivot->data_conclusao ? \Carbon\Carbon::parse($pivot->data_conclusao)->format('d/m/Y') : '-' }}" disabled>
                </div>
                <div class="col-4 form-group flex-fill">
                    <label class="mb-2"><strong>{{ __('Hora Conclusão') }}</strong></label>
                    <input type="text" class="form-control rounded-pill bg-light text-center" value="{{ $pivot->data_conclusao ? \Carbon\Carbon::parse($pivot->data_conclusao)->format('H:i') : '-' }}" disabled>
                </div>
            </div>
            <div class="form-group mb-3">
                <label class="mb-2"><strong>{{ __('Comentários') }}</strong></label>
                <textarea class="form-control bg-light rounded-4" rows="3" style="resize: none;" disabled>{{ $pivot->comentarios ?? '' }}</textarea>
            </div>
            <div class="d-flex flex-wrap justify-content-between page-main-actions">
                <a class="btn btn-outline-dark rounded-pill px-4" href="{{ route('backoffice.task_schedules.minhas') }}">{{ __('VOLTAR') }}</a>
                {{-- Botão "Concluir todas as ocorrências futuras" removido conforme solicitado --}}
            </div>
        </div>
    </div>
@else
    <form method="POST" action="{{ route('backoffice.task_schedules.minhas.update', $schedule->id) }}">
        @csrf
        @method('PUT')
        <div class="bg-white p-3">
            <div class="col-md-7 col-lg-6">
                <div class="form-group my-4">
                <label class="mb-2"><strong>{{ __('Prioridade') }}</strong></label>
                    <input type="text" class="form-control" value="{{ $schedule->prioridade }}" readonly>
                </div>
                <div class="form-group my-4">
                <label class="mb-2"><strong>{{ __('Tarefa') }}</strong></label>
                    <input type="text" class="form-control" value="{{ $schedule->task->title ?? '-' }}" readonly>
                </div>
                <div class="form-group mb-3">
                <label class="mb-2"><strong>{{ __('Descrição') }}</strong></label>
                    <textarea class="form-control" rows="5" readonly>{{ $schedule->description }}</textarea>
                </div>
                <div class="d-flex flex-wrap gap-2 text-center">
                    <div class="form-group flex-fill">
                        <label class="mb-2"><strong>{{ __('Data Limite') }}</strong></label>
                        <input type="date" class="form-control" value="{{ $schedule->display_date ? \Carbon\Carbon::parse($schedule->display_date)->format('Y-m-d') : '' }}" readonly>
                    </div>
                    <div class="form-group flex-fill">
                        <label class="mb-2"><strong>{{ __('Hora Limite') }}</strong></label>
                        <input type="time" class="form-control" value="{{ $schedule->display_time ? \Carbon\Carbon::parse($schedule->display_time)->format('H:i') : '' }}" readonly>
                    </div>
                </div>
                <div class="form-group my-4">
                    <label for="estado" class="mb-2"><strong>{{ __('Estado') }}</strong></label>
                    <select name="estado" class="form-control form-select" required>
                        <option value="">{{ __('Selecionar Estado') }}</option>
                        @php
                            function normalizeSelect($str) {
                                return strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $str));
                            }
                            $pivotEstadoNorm = normalizeSelect($pivot->estado ?? '');
                        @endphp
                        @foreach(['Em Execução', 'Concluída'] as $estado)
                            @php $estadoNorm = normalizeSelect($estado); @endphp
                            <option value="{{ $estado }}" {{ $pivotEstadoNorm === $estadoNorm ? 'selected' : '' }}>{{ __($estado) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-4">
                    <label for="comentario" class="mb-2"><strong>{{ __('Comentário') }}</strong></label>
                    <textarea name="comentario" id="comentario" class="form-control" rows="3" maxlength="255">{{ old('comentario', $pivot->comentarios ?? '') }}</textarea>
                    <small class="text-muted fa-pull-right">{{ __('máx. 255') }}</small>
                </div>
                <div class="d-flex justify-content-between page-main-actions position-sticky px-4 py-3" style="background-color: #fff; bottom: 0; z-index: 10; box-shadow: 0 -2px 10px rgba(0,0,0,0.05);">
                    <a href="{{ route('backoffice.task_schedules.minhas') }}" class="btn btn-outline-dark rounded-pill px-4">{{ __('Voltar') }}</a>
                    {{-- Modal de escolha removido. Apenas atualização simples do estado. --}}
                    <button type="submit" class="btn btn-success rounded-pill px-4">{{ __('Atualizar') }}</button>
                </div>
            </div>
        </div>
    </form>
@endif

{{-- Histórico de ocorrências removido conforme solicitado --}}
@endsection
