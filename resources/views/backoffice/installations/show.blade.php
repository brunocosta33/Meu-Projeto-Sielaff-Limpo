@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Detalhes da Instalação') }}</title>
@endsection

@section('content')
<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">{{ __('Detalhes da Instalação') }}</h5>

                <ul class="list-group mb-4">
                    <li class="list-group-item"><strong>{{ __('Loja') }}:</strong> {{ $installation->store->nome_loja ?? '—' }}</li>
                    <li class="list-group-item"><strong>{{ __('Equipa Técnica') }}:</strong> {{ $installation->team->nome ?? '—' }}</li>
                    <li class="list-group-item"><strong>{{ __('Data') }}:</strong> {{ \Carbon\Carbon::parse($installation->scheduled_date)->format('d/m/Y') }}</li>
                    <li class="list-group-item"><strong>{{ __('Hora') }}:</strong> {{ $installation->scheduled_time }}</li>
                    <li class="list-group-item"><strong>{{ __('Tipo de Serviço') }}:</strong> {{ __($installation->tipo_servico) }}</li>
                    <li class="list-group-item"><strong>{{ __('Estado') }}:</strong> {{ __($installation->status) }}</li>
                    <li class="list-group-item"><strong>{{ __('Observações') }}:</strong> {{ $installation->observacoes ?? '—' }}</li>
                </ul>

                <div class="mb-4">
                    <h5 class="mb-2">{{ __('Ficheiros Relacionados') }}</h5>
                    @if($installation->pdfs->count())
                        <ul class="list-group">
                            @foreach($installation->pdfs as $pdf)
                                <li class="list-group-item d-flex justify-content-between align-items-center" style="background: #f8f9fa; border-radius: 8px; margin-bottom: 6px;">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($pdf->isImage())
                                            <img src="{{ asset('storage/' . $pdf->file_path) }}" alt="{{ $pdf->file_name }}" style="width: 42px; height: 42px; object-fit: cover; border-radius: 6px; margin-right: 8px;">
                                        @else
                                            <i class="fas fa-file-pdf text-danger fa-lg me-2"></i>
                                        @endif
                                        <span class="fw-semibold">{{ $pdf->file_name }}</span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ asset('storage/' . $pdf->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">{{ __('Abrir') }}</a>
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
                        <p class="text-muted">{{ __('Nenhum ficheiro relacionado.') }}</p>
                    @endif
                </div>

                <div class="d-flex gap-2">
                    <a href="{{ route('backoffice.installations.index', ['page' => request('page')]) }}" class="btn btn-secondary">
                        ← {{ __('Voltar para a lista') }}
                    </a>

                    <a href="{{ route('backoffice.installations.edit', ['installation' => $installation->id, 'page' => request('page')]) }}" class="btn btn-primary">
                        ✎ {{ __('Editar Instalação') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
