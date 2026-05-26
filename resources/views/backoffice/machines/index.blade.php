@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Máquinas') }}</title>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

<div class="row">
    <div class="col">
        <div class="card shadow-sm">
            <div class="card-body">

                {{-- Header --}}
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-server"></i> {{ __('Lista de Máquinas') }}
                    </h5>
                    <a href="{{ route('backoffice.machines.create') }}" class="btn btn-success">
                        <i class="fa fa-plus"></i> {{ __('Nova Máquina') }}
                    </a>
                </div>

                {{-- 🔎 Filtros --}}
                <form method="GET" class="mb-4">
                    <div class="form-row align-items-end">
                        {{-- Código da Loja --}}
                        <div class="col-md-3 mb-2">
                            <label for="codigo_loja" class="form-label">{{ __('Código da Loja') }}</label>
                            <input type="text" name="codigo_loja" id="codigo_loja"
                                value="{{ request('codigo_loja') }}"
                                class="form-control" placeholder="Ex: 123">
                        </div>

                        {{-- Nome da Loja --}}
                        <div class="col-md-4 mb-2">
                            <label for="nome_loja" class="form-label">{{ __('Nome da Loja') }}</label>
                            <input type="text" name="nome_loja" id="nome_loja"
                                value="{{ request('nome_loja') }}"
                                class="form-control" placeholder="Ex: Loja Central">
                        </div>

                        {{-- Número de Série --}}
                        <div class="col-md-3 mb-2">
                            <label for="serial_number" class="form-label">{{ __('Número de Série') }}</label>
                            <input type="text" name="serial_number" id="serial_number"
                                value="{{ request('serial_number') }}"
                                class="form-control" placeholder="Ex: SN12345">
                        </div>

                        {{-- Botões --}}
                        <div class="col-md-2 mb-2 d-flex">
                            <button type="submit" class="btn btn-primary w-100 mr-2">
                                <i class="fa fa-search"></i> {{ __('Pesquisar') }}
                            </button>
                            <a href="{{ route('backoffice.machines.index') }}" class="btn btn-secondary w-100">
                                <i class="fa fa-undo"></i> {{ __('Limpar') }}
                            </a>
                        </div>
                    </div>
                </form>


                {{-- Tabela --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background: linear-gradient(90deg, #007bff, #00c6ff); color: white;">
                            <tr>
                                <th>{{ __('Loja') }}</th>
                                <th>{{ __('Número de Série') }}</th>
                                <th>{{ __('Endereço IP') }}</th>
                                <th>{{ __('Modelo') }}</th>
                                <th class="text-right">{{ __('Ações') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($machines as $m)
                            <tr>
                                {{-- Loja --}}
                                <td>
                                    <span class="badge badge-primary px-3 py-2" style="font-size: 0.9rem; font-weight: bold;">
                                        {{ $m->store->codigo_loja }}
                                    </span>
                                    <span style="font-size: 1.1rem; font-weight: 600; margin-left: 8px;">
                                        {{ $m->store->nome_loja }}
                                    </span>
                                </td>

                                {{-- Nº Série --}}
                                <td>
                                    <span class="badge badge-warning px-3 py-2" style="font-size: 0.95rem;">
                                        <i class="fa fa-hashtag"></i> {{ $m->serial_number }}
                                    </span>
                                </td>

                                {{-- IP --}}
                                <td>
                                    @if($m->ip_address)
                                    <span class="badge badge-info px-3 py-2" style="font-size: 0.95rem;">
                                        <i class="fa fa-network-wired"></i> {{ $m->ip_address }}
                                    </span>
                                    @else
                                    <span class="text-muted"><i class="fa fa-minus-circle"></i> Sem IP</span>
                                    @endif
                                </td>


                                {{-- Descrição --}}
                                <td>{{ $m->descricao ?? '—' }}</td>

                                {{-- Ações --}}
                                <td class="text-right">
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('backoffice.machines.history', $m->id) }}"
                                            class="btn btn-sm btn-outline-info mr-2" title="Histórico">
                                            <i class="fa fa-history"></i>
                                        </a>
                                        <a href="{{ route('backoffice.machines.edit', $m->id) }}"
                                            class="btn btn-sm btn-outline-primary mr-2" title="Editar">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('backoffice.machines.destroy', $m->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Tem a certeza que deseja apagar esta máquina?')"
                                                title="Apagar">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <i class="fa fa-info-circle"></i> Nenhuma máquina registada.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection