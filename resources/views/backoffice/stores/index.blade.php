@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Lojas') }}</title>
@endsection

@section('head-scripts')
<style>
    .stores-page {
        --stores-ink: #132238;
        --stores-muted: #6c7a89;
        --stores-border: #dfe7f1;
        --stores-soft: #f6f8fb;
        --stores-primary: #1456b8;
        --stores-primary-soft: #ebf2ff;
        --stores-success-soft: #ebf8ef;
        --stores-warning-soft: #fff7df;
        --stores-info-soft: #e7f7ff;
        position: relative;
    }

    .stores-page::before {
        content: "";
        position: absolute;
        inset: -24px 0 auto;
        height: 280px;
        background:
            radial-gradient(circle at top left, rgba(20, 86, 184, 0.14), transparent 28%),
            radial-gradient(circle at top right, rgba(20, 184, 166, 0.10), transparent 24%),
            linear-gradient(180deg, #f8fbff 0%, rgba(248, 251, 255, 0) 100%);
        pointer-events: none;
    }

    .stores-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f3f7ff 60%, #ecfbf7 100%);
        border: 1px solid var(--stores-border);
        border-radius: 22px;
        box-shadow: 0 14px 30px rgba(19, 34, 56, 0.07);
        padding: 24px 26px;
    }

    .stores-title {
        color: var(--stores-ink);
        font-size: 1.75rem;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .stores-copy {
        color: var(--stores-muted);
        max-width: 560px;
        line-height: 1.6;
        margin-bottom: 0;
        font-size: 0.95rem;
    }

    .stores-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .stores-hero-actions .btn {
        border-radius: 14px;
        padding: 0.8rem 1.15rem;
        font-weight: 700;
        box-shadow: 0 10px 24px rgba(19, 34, 56, 0.08);
    }

    .stores-stat {
        border: 1px solid var(--stores-border);
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 10px 22px rgba(19, 34, 56, 0.05);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .stores-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 36px rgba(19, 34, 56, 0.1);
    }

    .stores-stat .card-body {
        padding: 0.95rem 1rem;
    }

    .stores-stat-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.8rem;
    }

    .stores-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.84);
        font-size: 1rem;
    }

    .stores-stat-value {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 10px;
    }

    .stores-stat-value h3 {
        margin: 0;
        font-size: 1.65rem;
        line-height: 1;
        font-weight: 800;
        color: var(--stores-ink);
    }

    .stores-stat-primary { background: linear-gradient(135deg, #ffffff 0%, #eef4ff 100%); }
    .stores-stat-success { background: linear-gradient(135deg, #f8fff9 0%, #e8f7ef 100%); }
    .stores-stat-warning { background: linear-gradient(135deg, #fffef8 0%, #fff5dc 100%); }

    .stores-panel {
        background: #fff;
        border: 1px solid var(--stores-border);
        border-radius: 22px;
        box-shadow: 0 14px 32px rgba(19, 34, 56, 0.07);
        overflow: hidden;
    }

    .stores-panel-title {
        color: var(--stores-ink);
        font-weight: 800;
        margin-bottom: 0.2rem;
    }

    .stores-panel-copy {
        color: var(--stores-muted);
        margin-bottom: 0;
    }

    .stores-filter-box {
        background: linear-gradient(180deg, #fbfcfe 0%, #f5f8fc 100%);
        border: 1px solid var(--stores-border);
        border-radius: 18px;
        padding: 1rem;
    }

    .stores-filter-box label {
        color: var(--stores-ink);
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.45rem;
    }

    .stores-filter-box .form-control {
        min-height: 46px;
        border-radius: 14px;
        border: 1px solid #d8e2ef;
        box-shadow: none;
        background: rgba(255, 255, 255, 0.96);
    }

    .stores-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .stores-filter-actions .btn {
        border-radius: 14px;
        padding: 0.78rem 1.05rem;
        font-weight: 700;
    }

    .stores-table-shell {
        margin-top: 1.25rem;
        border: 1px solid #e6edf6;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
    }

    .stores-table {
        margin-bottom: 0;
    }

    .stores-table thead th {
        border: 0;
        background: linear-gradient(90deg, #0f5bcf, #20a4f3);
        color: #fff;
        font-size: 0.77rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.82rem 0.85rem;
        vertical-align: middle;
    }

    .stores-table tbody td {
        border-top: 1px solid #edf2f8;
        padding: 0.82rem 0.85rem;
        vertical-align: top;
    }

    .stores-table tbody tr {
        transition: background 0.18s ease;
    }

    .stores-table tbody tr:hover {
        background: #f9fbfe;
    }

    .stores-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        padding: 0.46rem 0.78rem;
        font-size: 0.82rem;
        font-weight: 800;
    }

    .stores-badge-code {
        background: linear-gradient(135deg, #ebf2ff 0%, #dce8ff 100%);
        color: var(--stores-primary);
    }

    .stores-store-name {
        color: var(--stores-ink);
        font-size: 0.96rem;
        font-weight: 800;
        margin-bottom: 0.18rem;
    }

    .stores-subline {
        color: var(--stores-muted);
        font-size: 0.82rem;
        line-height: 1.4;
    }

    .stores-region-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.38rem 0.72rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #f0f5fb 0%, #e8f1fb 100%);
        color: #38506b;
        font-size: 0.79rem;
        font-weight: 700;
        margin-bottom: 0.35rem;
    }

    .stores-machine-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .stores-machine-card {
        min-width: 150px;
        max-width: 200px;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        border: 1px solid #dfe9f5;
        border-radius: 14px;
        padding: 0.55rem 0.65rem;
        box-shadow: 0 6px 14px rgba(19, 34, 56, 0.04);
    }

    .stores-machine-serial {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.4rem 0.7rem;
        border-radius: 999px;
        background: linear-gradient(135deg, #ffe69a 0%, #ffd166 100%);
        color: #7a4a00;
        font-size: 0.82rem;
        font-weight: 800;
    }

    .stores-machine-model {
        color: var(--stores-muted);
        font-size: 0.77rem;
        line-height: 1.35;
        margin-top: 0.42rem;
    }

    .stores-empty {
        text-align: center;
        padding: 2.6rem 1.5rem;
        color: var(--stores-muted);
    }

    .stores-empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1rem;
        border-radius: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #eef4ff 0%, #e8f7fb 100%);
        color: var(--stores-primary);
        font-size: 1.35rem;
        box-shadow: 0 14px 30px rgba(19, 34, 56, 0.08);
    }

    .stores-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }

    .stores-actions .btn {
        border-radius: 12px;
    }

    @media (max-width: 991.98px) {
        .stores-hero {
            padding: 24px;
        }

        .stores-hero-actions {
            justify-content: flex-start;
            margin-top: 1rem;
        }

        .stores-machine-card {
            min-width: 100%;
            max-width: none;
        }
    }
</style>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

@php
    $storeCount = $stores->count();
    $machineCount = $stores->sum(fn($store) => $store->machines->count());
    $sonaeCount = $stores->where('insignia', 'sonae')->count();
@endphp

<div class="row stores-page">
    <div class="col">
        <div class="stores-hero mb-4">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                <div>
                    <h4 class="stores-title mb-2">{{ __('Lojas') }}</h4>
                    <p class="stores-copy">{{ __('Consulte toda a rede de lojas, identifique rapidamente os códigos e veja as máquinas associadas com uma leitura mais limpa e direta.') }}</p>
                </div>
                @if($canManageStores ?? true)
                    <div class="stores-hero-actions">
                        <a href="{{ route('backoffice.stores.create') }}" class="btn btn-success">
                            <i class="fa fa-plus"></i> {{ __('Nova Loja') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6 col-xl-4 mb-3">
                <div class="card stores-stat stores-stat-primary h-100">
                    <div class="card-body">
                        <div class="stores-stat-top">
                            <small class="text-muted text-uppercase d-block mb-0">{{ __('Total de lojas') }}</small>
                            <span class="stores-stat-icon text-primary"><i class="fa fa-store"></i></span>
                        </div>
                        <div class="stores-stat-value">
                            <h3>{{ $storeCount }}</h3>
                            <span class="badge badge-light">{{ __('Registos') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4 mb-3">
                <div class="card stores-stat stores-stat-success h-100">
                    <div class="card-body">
                        <div class="stores-stat-top">
                            <small class="text-muted text-uppercase d-block mb-0">{{ __('Máquinas ligadas') }}</small>
                            <span class="stores-stat-icon text-success"><i class="fa fa-cogs"></i></span>
                        </div>
                        <div class="stores-stat-value">
                            <h3>{{ $machineCount }}</h3>
                            <span class="badge badge-light">{{ __('Total') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4 mb-3">
                <div class="card stores-stat stores-stat-warning h-100">
                    <div class="card-body">
                        <div class="stores-stat-top">
                            <small class="text-muted text-uppercase d-block mb-0">{{ __('Lojas Sonae') }}</small>
                            <span class="stores-stat-icon text-warning"><i class="fa fa-building"></i></span>
                        </div>
                        <div class="stores-stat-value">
                            <h3>{{ $sonaeCount }}</h3>
                            <span class="badge badge-light">{{ __('Insígnia') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="stores-panel">
            <div class="card-body">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
                    <div>
                        <h5 class="stores-panel-title">{{ __('Lista de Lojas') }}</h5>
                        <p class="stores-panel-copy">{{ __('Filtre por nome, código, cidade, número de série ou insígnia e consulte as máquinas diretamente na mesma vista.') }}</p>
                    </div>
                </div>

                <form method="GET" class="mb-4">
                    <div class="stores-filter-box">
                        <div class="row align-items-end">
                            <div class="col-md-8 mb-3">
                                <label>{{ __('Pesquisa rápida') }}</label>
                                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ __('Código, nome, cidade, morada, região ou nº de série') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>{{ __('Insígnia') }}</label>
                                <select name="insignia" class="form-control">
                                    <option value="">{{ __('Todas') }}</option>
                                    @foreach($insignias as $value => $label)
                                        <option value="{{ $value }}" {{ request('insignia') === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="stores-filter-actions">
                            <button class="btn btn-primary" type="submit">
                                <i class="fa fa-search"></i> {{ __('Filtrar') }}
                            </button>
                            <a href="{{ route('backoffice.stores.index') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-undo"></i> {{ __('Limpar') }}
                            </a>
                        </div>
                    </div>
                </form>

                <div class="stores-table-shell">
                    <div class="table-responsive">
                        <table class="table stores-table align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('Insígnia') }}</th>
                                    <th>{{ __('Código Loja') }}</th>
                                    <th>{{ __('Nome Loja') }}</th>
                                    <th>{{ __('Região') }}</th>
                                    <th>{{ __('Modelos de Máquina') }}</th>
                                    @if($canManageStores ?? true)
                                        <th class="text-right">{{ __('Ações') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stores as $store)
                                <tr>
                                    <td>
                                        <span class="stores-badge {{ $store->insignia === 'lidl' ? 'badge-warning text-dark' : 'badge-success' }}" style="{{ $store->insignia === 'lidl' ? 'background:#ffe39a;' : 'background:#c9f1d7; color:#17663a;' }}">
                                            <i class="fa fa-tag"></i>
                                            {{ ucfirst($store->insignia ?? '—') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="stores-badge stores-badge-code">
                                            {{ $store->codigo_loja }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="stores-store-name">{{ $store->nome_loja }}</div>
                                        <div class="stores-subline">{{ $store->cidade ?: '—' }}</div>
                                        <div class="stores-subline">{{ $store->morada ?: '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="stores-region-pill">
                                            <i class="fa fa-map-marker-alt"></i> {{ $store->regiao }}
                                        </div>
                                        <div class="stores-subline">{{ $store->codigo_postal ?: '—' }}</div>
                                    </td>
                                    <td>
                                        @if($store->machines->isNotEmpty())
                                            <div class="stores-machine-list">
                                                @foreach($store->machines as $machine)
                                                    <div class="stores-machine-card">
                                                        <div>
                                                            <span class="stores-machine-serial">
                                                                <i class="fa fa-hashtag"></i> {{ $machine->serial_number }}
                                                            </span>
                                                        </div>
                                                        <div class="stores-machine-model">
                                                            <i class="fa fa-cogs"></i> {{ $machine->descricao ?: __('Modelo não definido') }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">
                                                <i class="fa fa-minus-circle"></i> {{ __('Sem máquinas') }}
                                            </span>
                                        @endif
                                    </td>
                                    @if($canManageStores ?? true)
                                        <td class="text-right">
                                            <div class="stores-actions">
                                                <a href="{{ route('backoffice.stores.edit', $store->id) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="{{ route('backoffice.stores.delete', $store->id) }}"
                                                   onclick="return confirm('Tem a certeza que deseja apagar esta loja?')"
                                                   class="btn btn-sm btn-outline-danger" title="Apagar">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ ($canManageStores ?? true) ? 6 : 5 }}">
                                        <div class="stores-empty">
                                            <div class="stores-empty-icon">
                                                <i class="fa fa-store-slash"></i>
                                            </div>
                                            <div><i class="fa fa-info-circle"></i> {{ __('Nenhuma loja registada.') }}</div>
                                        </div>
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
</div>
@endsection
