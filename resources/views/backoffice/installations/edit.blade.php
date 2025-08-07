@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Editar Instalação') }}</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h5 class="card-title mb-4">{{ __('Editar Instalação') }}</h5>

                {{-- FORMULÁRIO DE EDIÇÃO --}}
                {!! Form::model($installation, ['route' => ['backoffice.installations.update', $installation->id], 'method' => 'PUT', 'files' => true]) !!}
                {{ csrf_field() }}

                <div class="form-group">
                    <label>{{ __('Loja') }}</label>
                    <select name="store_id" class="form-control" required>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $installation->store_id == $store->id ? 'selected' : '' }}>
                                {{ $store->nome_loja }} ({{ $store->codigo_loja }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Equipa Técnica') }}</label>
                    <select name="team_id" class="form-control" required>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" {{ $installation->team_id == $team->id ? 'selected' : '' }}>
                                {{ $team->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <label for="scheduled_date" style="font-size: 0.95em;">{{ __('Data') }}</label>
                <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" style="max-width: 180px;" value="{{ \Carbon\Carbon::parse($installation->scheduled_date)->format('Y-m-d') }}" required>

                <label for="scheduled_time" style="font-size: 0.95em;">{{ __('Hora') }}</label>
                <input type="time" id="scheduled_time" name="scheduled_time" class="form-control" style="max-width: 120px;" value="{{ \Carbon\Carbon::parse($installation->scheduled_time)->format('H:i') }}" required>

                <div class="form-group">
                    <label>{{ __('Tipo de Serviço') }}</label>
                    <select name="tipo_servico" class="form-control" required>
                        <option value="Instalação" {{ $installation->tipo_servico == 'Instalação' ? 'selected' : '' }}>{{ __('Instalação') }}</option>
                        <option value="Desinstalação" {{ $installation->tipo_servico == 'Desinstalação' ? 'selected' : '' }}>{{ __('Desinstalação') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Estado') }}</label>
                    <select name="status" class="form-control" required>
                        <option value="Agendado" {{ $installation->status == 'Agendado' ? 'selected' : '' }}>{{ __('Agendado') }}</option>
                        <option value="Concluído" {{ $installation->status == 'Concluído' ? 'selected' : '' }}>{{ __('Concluído') }}</option>
                        <option value="Cancelado" {{ $installation->status == 'Cancelado' ? 'selected' : '' }}>{{ __('Cancelado') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Observações') }}</label>
                    <textarea name="observacoes" class="form-control">{{ $installation->observacoes }}</textarea>
                </div>

                <div class="form-group mt-3">
                    <label>{{ __('Adicionar PDFs Relacionados') }}</label>
                    <input type="file" name="pdfs[]" class="form-control" accept="application/pdf" multiple>
                    <small class="form-text text-muted">{{ __('Pode selecionar vários ficheiros PDF') }}</small>
                </div>

                <input type="hidden" name="page" value="{{ request('page') }}">
                <button type="submit" class="btn btn-primary">{{ __('Atualizar') }}</button>
                <a href="{{ route('backoffice.installations.index', ['page' => request('page')]) }}" class="btn btn-secondary">{{ __('Cancelar') }}</a>

                {!! Form::close() !!}
                {{-- FIM FORM DE EDIÇÃO --}}

                {{-- PDFS ASSOCIADOS --}}
                <div class="form-group mt-4">
                    <label class="mb-2">{{ __('PDFs já associados') }}</label>
                    @if($installation->pdfs->count())
                        <ul class="list-group">
                            @foreach($installation->pdfs as $pdf)
                                <li class="list-group-item d-flex justify-content-between align-items-center" style="background: #f8f9fa; border-radius: 8px; margin-bottom: 6px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-file-pdf text-danger fa-lg me-2"></i>
                                        <span class="fw-semibold">{{ $pdf->file_name }}</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ asset('storage/' . $pdf->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">Abrir</a>
                                        <form method="POST" action="{{ route('backoffice.installations.pdfs.delete', $pdf->id) }}" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('Tem a certeza que deseja apagar este ficheiro?') }}')">{{ __('Apagar') }}</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">{{ __('Nenhum PDF associado.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var dateInput = document.querySelector('input[type="date"][name="scheduled_date"]');
        if (dateInput) {
            dateInput.addEventListener('keydown', function (e) {
                e.preventDefault();
            });
        }
    });
</script>
@endsection
