@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Editar Agendamento') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">{{ __('Editar Agendamento') }}</h5>

                {!! Form::model($appointment, ['route' => ['backoffice.appointments.update', $appointment->id], 'method' => 'POST', 'files' => true]) !!}
                {{ csrf_field() }}

                <div class="form-group">
                    <label>{{ __('Loja') }}</label>
                    <select name="store_id" class="form-control" required>
                        <option value="">{{ __('-- Selecione a Loja --') }}</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}" {{ $appointment->store_id == $store->id ? 'selected' : '' }}>
                                {{ $store->nome_loja }} ({{ $store->codigo_loja }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Transportadora') }}</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">{{ __('-- Selecione a Transportadora --') }}</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ $appointment->supplier_id == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <label for="scheduled_time" style="font-size: 0.95em; margin-bottom: 2px;">{{ __('Hora') }}</label>
                <input type="time" id="scheduled_time" name="scheduled_time" class="form-control" style="max-width: 120px; font-size: 0.95em; padding: 2px 8px; margin-bottom: 10px;" value="{{ \Carbon\Carbon::parse($appointment->scheduled_time)->format('H:i') }}" required>

                <label for="scheduled_date" style="font-size: 0.95em; margin-bottom: 2px;">{{ __('Data') }}</label>
                <input type="date" id="scheduled_date" name="scheduled_date" class="form-control" style="max-width: 180px; font-size: 0.95em; padding: 2px 8px; margin-bottom: 10px;" value="{{ \Carbon\Carbon::parse($appointment->scheduled_date)->format('Y-m-d') }}" required onkeydown="return false;">

                <div class="form-group">
                    <label>{{ __('Tipo de Serviço') }}</label>
                    <select name="tipo_servico" class="form-control" required>
                        <option value="Entrega" {{ $appointment->tipo_servico == 'Entrega' ? 'selected' : '' }}>{{ __('Entrega') }}</option>
                        <option value="Levantamento" {{ $appointment->tipo_servico == 'Levantamento' ? 'selected' : '' }}>{{ __('Levantamento') }}</option>
                        <option value="Outro" {{ $appointment->tipo_servico == 'Outro' ? 'selected' : '' }}>{{ __('Outro') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Estado') }}</label>
                    <select name="status" class="form-control" required>
                        <option value="Agendado" {{ $appointment->status == 'Agendado' ? 'selected' : '' }}>{{ __('Agendado') }}</option>
                        <option value="Concluído" {{ $appointment->status == 'Concluído' ? 'selected' : '' }}>{{ __('Concluído') }}</option>
                        <option value="Cancelado" {{ $appointment->status == 'Cancelado' ? 'selected' : '' }}>{{ __('Cancelado') }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>{{ __('Observações') }}</label>
                    <textarea name="observacoes" class="form-control" rows="3">{{ $appointment->observacoes }}</textarea>
                </div>

                <div class="form-group mt-3">
                    <label>{{ __('Adicionar PDFs Relacionados') }}</label>
                    <input type="file" name="pdfs[]" class="form-control" accept="application/pdf" multiple>
                    <small class="form-text text-muted">Pode selecionar vários ficheiros PDF.</small>
                </div>

                <input type="hidden" name="page" value="{{ request('page') }}">
                <button type="submit" class="btn btn-primary">{{ __('Atualizar') }}</button>
                <a href="{{ route('backoffice.appointments.index', ['page' => request('page')]) }}" class="btn btn-secondary">{{ __('Cancelar') }}</a>

                {!! Form::close() !!}
                {{-- FIM FORM DE EDIÇÃO --}}

                {{-- PDFS ASSOCIADOS --}}
                <div class="form-group mt-4">
                    <label class="mb-2">{{ __('PDFs já associados') }}</label>
                    @if($files->count())
                        <ul class="list-group">
                            @foreach($files as $file)
                                <li class="list-group-item d-flex justify-content-between align-items-center" style="background: #f8f9fa; border-radius: 8px; margin-bottom: 6px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-file-pdf text-danger fa-lg me-2"></i>
                                        <span class="fw-semibold">{{ $file->file_name }}</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">Abrir</a>
                                        <form method="POST" action="{{ route('backoffice.appointments.files.delete', $file->id) }}" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remover este PDF?')">Apagar</button>
                                        </form>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">Nenhum PDF enviado.</p>
                    @endif
                </div
            </div>
        </div>
    </div>
</div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateInput = document.querySelector('input[type="date"][name="scheduled_date"]');
            if (dateInput) {
                dateInput.addEventListener('keydown', function(e) {
                    e.preventDefault();
                });
            }
        });
    </script>
@endsection
