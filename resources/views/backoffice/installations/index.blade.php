@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - Instalações</title>
@endsection

@push('styles')
    <style>
        .week-0 { background-color: #f8f9fa !important; }
        .week-1 { background-color: #e3f2fd !important; }
        .week-2 { background-color: #f1f8e9 !important; }
        .week-3 { background-color: #fff3e0 !important; }
        .week-4 { background-color: #fce4ec !important; }
        .week-5 { background-color: #ede7f6 !important; }
        .today-row { background-color: #ffebee !important; color: #c62828 !important; font-weight: bold; }
    </style>
@endpush

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card">
            <div class="card-body">

                <div class="float-right mb-3">
                    <a href="{{ route('backoffice.installations.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> Nova Instalação
                    </a>
                </div>

                <h5 class="card-title">Lista de Instalações</h5>

                <form method="GET" action="{{ route('backoffice.installations.index') }}" class="mb-4">
                    <div class="form-row">
                        <div class="col">
                            <input type="text" name="codigo_loja" class="form-control" placeholder="Código da Loja"
                                value="{{ request('codigo_loja') }}">
                        </div>
                        <div class="col">
                            <input type="text" name="nome_loja" class="form-control" placeholder="Nome da Loja"
                                value="{{ request('nome_loja') }}">
                        </div>
                        <div class="col">
                            <input type="text" name="team" class="form-control" placeholder="Equipa Técnica"
                                value="{{ request('team') }}">
                        </div>
                        <div class="col">
                            <input type="date" name="data" class="form-control" value="{{ request('data') }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fa fa-search"></i> Filtrar
                            </button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('backoffice.installations.index') }}" class="btn btn-outline-secondary">
                                Limpar
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Código Loja</th>
                                <th>Loja</th>
                                <th>Equipa Técnica</th>
                                <th>Hora</th>
                                <th>Data</th>
                                <th>Tipo de Serviço</th>
                                <th>Estado</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($installations as $installation)
                                @php
                                    $date = \Carbon\Carbon::parse($installation->scheduled_date);
                                    $weekNumber = $date->week;
                                    $colorClass = 'week-' . ($weekNumber % 6);
                                    $isToday = $date->isToday();
                                @endphp
                                <tr class="{{ $isToday ? 'today-row' : '' }}">
                                    <td class="{{ $colorClass }}">{{ $installation->store->codigo_loja ?? '-' }}</td>
                                    <td class="{{ $colorClass }}">{{ $installation->store->nome_loja ?? '-' }}</td>
                                    <td class="{{ $colorClass }}">{{ $installation->team->nome ?? '-' }}</td>
                                    <td class="{{ $colorClass }}">{{ $installation->scheduled_time }}</td>
                                    <td class="{{ $colorClass }}">
                                        {{ $date->format('d/m/Y') }}
                                        @if($isToday)
                                            <br>
                                            <span class="badge badge-danger">Hoje</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">
                                            {{ ucfirst($date->translatedFormat('l')) }} | CW{{ $weekNumber }}
                                        </small>
                                    </td>
                                    <td>{{ $installation->tipo_servico }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match($installation->status) {
                                                'Concluído' => 'badge-success',
                                                'Cancelado' => 'badge-danger',
                                                default => 'badge-primary',
                                            };
                                        @endphp

                                        <span class="badge {{ $badgeClass }}">{{ $installation->status }}</span>

                                        @if (!empty($installation->observacoes))
                                            <br>
                                            <span class="badge badge-warning mt-1" title="Este registo tem observações.">
                                                <i class="fa fa-sticky-note"></i> Observações
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('backoffice.installations.show', ['installation' => $installation->id, 'page' => request('page')]) }}" class="ml-2" title="Ver Detalhes">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('backoffice.installations.edit', ['installation' => $installation->id, 'page' => request('page')]) }}" class="ml-2" title="Editar">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <a href="{{ route('backoffice.installations.delete', $installation->id) }}"
                                                onclick="return confirm('Tem a certeza que deseja apagar esta instalação?')"
                                                class="ml-2 text-danger" title="Apagar">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8">Nenhuma instalação registada.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $installations->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
