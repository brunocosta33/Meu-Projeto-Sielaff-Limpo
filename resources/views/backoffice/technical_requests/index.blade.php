@extends('layouts.backoffice_master')

@section('head-meta')
<title>{{ config('app.name') }} - {{ __('Pedidos de Assistência Técnica') }}</title>
@endsection

@section('head-scripts')
<style>
    .hotline-page {
        --hotline-ink: #132238;
        --hotline-muted: #6c7a89;
        --hotline-surface: #f6f8fb;
        --hotline-border: #dfe7f1;
        --hotline-primary: #0b5ed7;
        --hotline-primary-soft: #e8f1ff;
        --hotline-success-soft: #eaf7ef;
        --hotline-warning-soft: #fff6df;
        --hotline-info-soft: #e8f7fb;
        --hotline-danger-soft: #fdecec;
        --hotline-dark-surface: #eef3f9;
        --hotline-shadow: 0 18px 40px rgba(19, 34, 56, 0.08);
        position: relative;
    }

    .hotline-page::before {
        content: "";
        position: absolute;
        inset: -24px 0 auto;
        height: 320px;
        background:
            radial-gradient(circle at top left, rgba(29, 78, 216, 0.12), transparent 30%),
            radial-gradient(circle at top right, rgba(13, 148, 136, 0.10), transparent 28%),
            linear-gradient(180deg, #f8fbff 0%, rgba(248, 251, 255, 0) 100%);
        pointer-events: none;
    }

    .hotline-hero {
        background:
            radial-gradient(circle at top right, rgba(11, 94, 215, 0.22), transparent 32%),
            radial-gradient(circle at bottom left, rgba(13, 148, 136, 0.14), transparent 30%),
            linear-gradient(135deg, #ffffff 0%, #f3f7fd 55%, #eef8f7 100%);
        border: 1px solid var(--hotline-border);
        border-radius: 26px;
        padding: 34px;
        box-shadow: var(--hotline-shadow);
        position: relative;
        overflow: hidden;
    }

    .hotline-hero-title {
        color: var(--hotline-ink);
        font-size: 2.15rem;
        font-weight: 800;
        letter-spacing: -0.03em;
    }

    .hotline-hero-copy {
        color: var(--hotline-muted);
        max-width: 620px;
        font-size: 1rem;
        line-height: 1.7;
    }

    .hotline-hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.85);
        color: #1d4ed8;
        border: 1px solid rgba(29, 78, 216, 0.12);
        font-size: 0.78rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 1rem;
    }

    .hotline-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .hotline-hero-actions .btn {
        border-radius: 14px;
        padding: 0.8rem 1.15rem;
        font-weight: 700;
        box-shadow: 0 10px 24px rgba(19, 34, 56, 0.08);
    }

    .hotline-stats-row {
        margin-top: -6px;
    }

    .hotline-stat {
        display: block;
        color: inherit;
        border: 1px solid var(--hotline-border);
        border-radius: 22px;
        box-shadow: 0 14px 32px rgba(19, 34, 56, 0.06);
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .hotline-stat:hover,
    .hotline-stat:focus {
        color: inherit;
        text-decoration: none;
        outline: 0;
    }

    .hotline-stat:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 36px rgba(19, 34, 56, 0.1);
    }

    .hotline-stat.is-active {
        border-color: var(--hotline-primary);
        box-shadow: 0 18px 36px rgba(11, 94, 215, 0.16);
    }

    .hotline-stat .card-body {
        padding: 1.15rem 1.25rem;
    }

    .hotline-stat-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.7rem;
    }

    .hotline-stat-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        background: rgba(255, 255, 255, 0.84);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.7);
    }

    .hotline-stat-value {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 10px;
    }

    .hotline-stat-value h3 {
        font-size: 2rem;
        line-height: 1;
        font-weight: 800;
    }

    .hotline-stat-total { background: linear-gradient(135deg, #ffffff 0%, #eef4ff 100%); }
    .hotline-stat-pending { background: linear-gradient(135deg, #fffdf7 0%, #fff3d8 100%); }
    .hotline-stat-scheduled { background: linear-gradient(135deg, #f8feff 0%, #e6f7fb 100%); }
    .hotline-stat-awaiting { background: linear-gradient(135deg, #fff8f8 0%, #fde8e8 100%); }
    .hotline-stat-done { background: linear-gradient(135deg, #f6fff8 0%, #e4f7ea 100%); }

    .hotline-panel {
        background: #fff;
        border: 1px solid var(--hotline-border);
        border-radius: 26px;
        box-shadow: var(--hotline-shadow);
        width: 100%;
        overflow: hidden;
    }

    .hotline-panel-header {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 1.25rem;
    }

    .hotline-panel-title {
        color: var(--hotline-ink);
        margin-bottom: 0.2rem;
        font-weight: 800;
    }

    .hotline-panel-copy {
        color: var(--hotline-muted);
        margin-bottom: 0;
    }

    .hotline-filter-box {
        background:
            linear-gradient(180deg, #fbfcfe 0%, #f5f8fc 100%);
        border: 1px solid var(--hotline-border);
        border-radius: 22px;
        padding: 1.2rem;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .hotline-filter-box label {
        color: var(--hotline-ink);
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        margin-bottom: 0.45rem;
    }

    .hotline-filter-box .form-control,
    .hotline-filter-box .bootstrap-select > .dropdown-toggle {
        min-height: 46px;
        border-radius: 14px;
        border: 1px solid #d8e2ef;
        box-shadow: none;
        background: rgba(255, 255, 255, 0.94);
    }

    .hotline-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .hotline-filter-actions .btn {
        border-radius: 14px;
        padding: 0.78rem 1.05rem;
        font-weight: 700;
    }

    .hotline-active-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0.9rem 0 1.25rem;
    }

    .hotline-open-callout {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 1.25rem;
        padding: 1.15rem;
        border-radius: 22px;
        border: 1px solid #cfe0ff;
        background:
            radial-gradient(circle at top right, rgba(11, 94, 215, 0.12), transparent 28%),
            linear-gradient(135deg, #eef4ff 0%, #f8fbff 100%);
        box-shadow: 0 14px 30px rgba(19, 34, 56, 0.08);
    }

    .hotline-open-callout-title {
        color: var(--hotline-ink);
        font-weight: 800;
        margin-bottom: 0.2rem;
    }

    .hotline-open-callout-copy {
        color: var(--hotline-muted);
        margin-bottom: 0;
        line-height: 1.5;
    }

    .hotline-open-callout .btn {
        border-radius: 16px;
        padding: 0.9rem 1.2rem;
        font-weight: 800;
        box-shadow: 0 12px 26px rgba(11, 94, 215, 0.16);
        white-space: nowrap;
    }

    .hotline-filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        background: var(--hotline-primary-soft);
        color: #1d4ed8;
        border: 1px solid #cfe0ff;
        font-size: 0.82rem;
        font-weight: 700;
    }

    .hotline-grid {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .hotline-ticket {
        background: #fff;
        border: 1px solid var(--hotline-border);
        border-radius: 26px;
        box-shadow: 0 18px 42px rgba(19, 34, 56, 0.09);
        padding: 1.35rem 1.4rem;
        display: grid;
        grid-template-columns: minmax(360px, 2.6fr) minmax(220px, 1.15fr) minmax(180px, 0.95fr) minmax(220px, 1.15fr) minmax(270px, 1.2fr);
        gap: 20px;
        align-items: start;
        position: relative;
        overflow: hidden;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .hotline-ticket:hover {
        transform: translateY(-2px);
        box-shadow: 0 22px 44px rgba(19, 34, 56, 0.12);
        border-color: #cfd9e7;
    }

    .hotline-ticket::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 7px;
        background: linear-gradient(180deg, #0b5ed7 0%, #54a4ff 100%);
    }

    .hotline-ticket[data-state="pendente"]::before { background: linear-gradient(180deg, #d39d00 0%, #ffd45a 100%); }
    .hotline-ticket[data-state="agendado"]::before { background: linear-gradient(180deg, #0d8db0 0%, #70d8f2 100%); }
    .hotline-ticket[data-state="concluido"]::before { background: linear-gradient(180deg, #208a47 0%, #6ad48f 100%); }
    .hotline-ticket[data-state="cancelado"]::before,
    .hotline-ticket[data-state="aguarda_peca"]::before { background: linear-gradient(180deg, #b3261e 0%, #ff7b72 100%); }

    .hotline-ticket-header {
        display: block;
        margin-bottom: 0.75rem;
    }

    .hotline-store-title {
        color: var(--hotline-ink);
        font-size: 1rem;
        font-weight: 700;
    }

    .hotline-store-subtitle {
        color: var(--hotline-muted);
        font-size: 0.85rem;
        margin-top: 0.2rem;
    }

    .hotline-brand-badge {
        display: inline-flex;
        align-items: center;
        margin-left: 0.45rem;
        padding: 0.28rem 0.62rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .hotline-brand-badge.brand-lidl {
        background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
        color: #ffeb3b;
        box-shadow: 0 8px 18px rgba(37, 99, 235, 0.22);
    }

    .hotline-brand-badge.brand-sonae {
        background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        color: #fff7d6;
        box-shadow: 0 8px 18px rgba(245, 158, 11, 0.2);
    }

    .hotline-address {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 0.45rem;
        padding: 0.55rem 0.8rem;
        border-radius: 14px;
        background: linear-gradient(135deg, #eef4ff 0%, #f6faff 100%);
        border: 1px solid #d8e5fb;
        color: #26415f;
        font-size: 0.84rem;
        font-weight: 600;
    }

    .hotline-meta {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.38rem 0.78rem;
        border-radius: 999px;
        background: var(--hotline-surface);
        color: #3c5067;
        font-size: 0.8rem;
        font-weight: 600;
        margin: 0.35rem 0.35rem 0 0;
    }

    .hotline-meta-strong {
        background: linear-gradient(180deg, #edf4ff 0%, #e7f0ff 100%);
        color: #184a93;
        border: 1px solid #cfe0ff;
    }

    .hotline-meta-stack {
        margin-bottom: 0.75rem;
    }

    .hotline-snippet {
        color: #39506a;
        line-height: 1.55;
        min-height: 0;
        font-size: 0.95rem;
    }

    .hotline-ticket-section {
        margin-top: 0;
        padding-top: 0;
        border-top: 0;
        background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
        border: 1px solid #ebf0f7;
        border-radius: 18px;
        padding: 0.95rem;
    }

    .hotline-service {
        font-weight: 700;
        color: var(--hotline-ink);
    }

    .hotline-section-label {
        color: var(--hotline-muted);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 0.35rem;
    }

    .hotline-note {
        display: block;
        margin-top: 0.45rem;
        color: var(--hotline-muted);
        line-height: 1.45;
    }

    .hotline-note-highlight {
        margin-top: 0.75rem;
        padding: 0.8rem 0.9rem;
        border-radius: 16px;
        background: linear-gradient(135deg, #fff7df 0%, #ffefbf 100%);
        border: 1px solid #f2d98b;
        color: #7a5900;
        box-shadow: 0 10px 22px rgba(122, 89, 0, 0.08);
    }

    .hotline-note-highlight .hotline-section-label {
        color: #8a6500;
        margin-bottom: 0.4rem;
    }

    .hotline-dates {
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .hotline-date-box {
        padding: 0.75rem 0.85rem;
        border-radius: 12px;
        background: var(--hotline-surface);
        border: 1px solid #e5ecf5;
    }

    .hotline-schedule-highlight {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 0.7rem;
        padding: 0.6rem 0.8rem;
        border-radius: 14px;
        background: linear-gradient(135deg, #e8f7fb 0%, #d7f0f8 100%);
        border: 1px solid #9dd7e6;
        color: #0f5f77;
        font-size: 0.82rem;
        font-weight: 700;
        box-shadow: 0 8px 18px rgba(15, 95, 119, 0.10);
    }

    .hotline-resolution-highlight {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 0.7rem;
        padding: 0.6rem 0.8rem;
        border-radius: 14px;
        background: linear-gradient(135deg, #e8f8ec 0%, #d8f2df 100%);
        border: 1px solid #9fd6af;
        color: #17663a;
        font-size: 0.82rem;
        font-weight: 700;
        box-shadow: 0 8px 18px rgba(23, 102, 58, 0.10);
    }

    .hotline-status-badge {
        font-size: 0.82rem;
        padding: 0.5rem 0.8rem;
        border-radius: 999px;
        font-weight: 700;
    }

    .hotline-date-box strong {
        color: var(--hotline-ink);
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }

    .hotline-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-start;
        align-content: flex-start;
        background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
        border: 1px solid #ebf0f7;
        border-radius: 18px;
        padding: 0.95rem;
        min-height: 100%;
    }

    .hotline-actions .btn {
        flex: 1 1 100%;
        width: 100%;
        border-radius: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .hotline-empty {
        border: 1px dashed #cfd8e5;
        border-radius: 22px;
        background: linear-gradient(180deg, #fbfcfe 0%, #f5f8fc 100%);
        padding: 2.6rem 1.5rem;
    }

    .hotline-empty-icon {
        width: 72px;
        height: 72px;
        border-radius: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #eef4ff 0%, #e8f7fb 100%);
        color: #1d4ed8;
        font-size: 1.55rem;
        margin-bottom: 1rem;
        box-shadow: 0 14px 30px rgba(19, 34, 56, 0.08);
    }

    @media (max-width: 1599.98px) {
        .hotline-ticket {
            grid-template-columns: minmax(320px, 2.2fr) minmax(190px, 1fr) minmax(160px, 0.9fr) minmax(210px, 1fr);
        }

        .hotline-actions {
            grid-column: 1 / -1;
            flex-direction: row;
            justify-content: flex-start;
            min-height: auto;
        }

        .hotline-actions .btn {
            flex: 0 0 auto;
            width: auto;
            min-width: 110px;
        }
    }

    @media (max-width: 991.98px) {
        .hotline-hero {
            padding: 22px;
        }

        .hotline-hero-title {
            font-size: 1.6rem;
        }

        .hotline-hero-actions {
            justify-content: flex-start;
        }

        .hotline-ticket {
            grid-template-columns: 1fr;
        }

        .hotline-actions {
            justify-content: flex-start;
        }

        .hotline-actions .btn {
            flex: 1 1 auto;
            width: auto;
        }

        .hotline-open-callout {
            align-items: stretch;
        }

        .hotline-open-callout .btn {
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 575.98px) {
        .hotline-page::before {
            inset: -12px 0 auto;
            height: 180px;
        }

        .hotline-hero {
            border-radius: 18px;
            padding: 18px 16px;
        }

        .hotline-hero-kicker {
            font-size: 0.7rem;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }

        .hotline-hero-title {
            font-size: 1.35rem;
        }

        .hotline-hero-copy {
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .hotline-hero-actions {
            width: 100%;
        }

        .hotline-hero-actions .btn {
            width: 100%;
            margin-right: 0 !important;
            padding: 0.72rem 0.95rem;
        }

        .hotline-stats-row {
            margin-bottom: 0.5rem !important;
        }

        .hotline-stat {
            border-radius: 16px;
        }

        .hotline-stat .card-body {
            padding: 0.95rem 1rem;
        }

        .hotline-stat-value h3 {
            font-size: 1.55rem;
        }

        .hotline-panel {
            border-radius: 18px;
        }

        .hotline-panel .card-body {
            padding: 1rem;
        }

        .hotline-panel-header {
            margin-bottom: 1rem;
        }

        .hotline-panel-title {
            font-size: 1rem;
        }

        .hotline-panel-copy {
            font-size: 0.88rem;
            line-height: 1.45;
        }

        .hotline-filter-box {
            border-radius: 16px;
            padding: 0.95rem;
        }

        .hotline-filter-actions .btn {
            width: 100%;
        }

        .hotline-active-filters {
            gap: 6px;
        }

        .hotline-filter-chip {
            font-size: 0.76rem;
            padding: 0.38rem 0.65rem;
        }

        .hotline-grid {
            gap: 12px;
        }

        .hotline-ticket {
            border-radius: 18px;
            padding: 1rem 0.95rem 0.95rem 1rem;
            gap: 12px;
            box-shadow: 0 10px 24px rgba(19, 34, 56, 0.08);
        }

        .hotline-ticket::before {
            width: 5px;
        }

        .hotline-ticket-header {
            margin-bottom: 0.55rem;
        }

        .hotline-store-title {
            font-size: 0.95rem;
            line-height: 1.35;
        }

        .hotline-store-subtitle {
            font-size: 0.77rem;
            line-height: 1.4;
        }

        .hotline-meta {
            font-size: 0.74rem;
            padding: 0.34rem 0.62rem;
            gap: 0.35rem;
            margin: 0.3rem 0.25rem 0 0;
        }

        .hotline-snippet {
            font-size: 0.88rem;
            line-height: 1.45;
        }

        .hotline-ticket-section,
        .hotline-actions {
            border-radius: 14px;
            padding: 0.8rem;
        }

        .hotline-section-label {
            font-size: 0.68rem;
            margin-bottom: 0.28rem;
        }

        .hotline-date-box {
            padding: 0.65rem 0.72rem;
        }

        .hotline-date-box strong {
            font-size: 0.68rem;
        }

        .hotline-status-badge,
        .hotline-note-highlight,
        .hotline-schedule-highlight,
        .hotline-resolution-highlight {
            font-size: 0.76rem;
        }

        .hotline-note-highlight,
        .hotline-schedule-highlight,
        .hotline-resolution-highlight {
            padding: 0.65rem 0.72rem;
            border-radius: 12px;
        }

        .hotline-actions {
            gap: 6px;
        }

        .hotline-actions .btn {
            width: 100%;
            flex: 1 1 100%;
            min-width: 0;
            padding: 0.68rem 0.8rem;
            font-size: 0.82rem;
        }

        .hotline-empty {
            border-radius: 18px;
            padding: 2rem 1rem;
        }

        .hotline-empty-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            font-size: 1.25rem;
            margin-bottom: 0.8rem;
        }
    }
</style>
@endsection

@section('content')
<div class="row">@include('flash::message')</div>

@php
    $hasFilters = collect(request()->only(['q', 'codigo_loja', 'serial_number', 'estado', 'prioridade', 'zona', 'assigned_technician_id', 'mes', 'data_inicio', 'data_fim']))
        ->flatten()
        ->filter(function ($value) {
            return $value !== null && $value !== '';
        })
        ->isNotEmpty();
    $statBaseParams = request()->except(['estado', 'page']);
    $selectedStates = (array) request('estado', []);
    $statUrl = fn (?string $state = null) => route('backoffice.technical_requests.index', $state ? array_merge($statBaseParams, ['estado' => [$state]]) : $statBaseParams);
@endphp

<div class="row hotline-page">
    <div class="col">
        <div class="hotline-hero mb-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center">
                    <div>
                        <div class="hotline-hero-kicker">
                            <i class="fa fa-headset"></i> {{ __('Centro Operacional') }}
                        </div>
                        <h4 class="hotline-hero-title mb-2">{{ __('HotLine') }}</h4>
                        <p class="hotline-hero-copy mb-0">{{ __('Consulte, filtre e acompanhe rapidamente os pedidos de assistência com uma vista mais clara, rápida e agradável.') }}</p>
                    </div>
                    <div class="mt-3 mt-lg-0 hotline-hero-actions">
                        @if($canManageAll)
                            <a href="{{ route('backoffice.technical_requests.create') }}" class="btn btn-success mr-2 px-4">
                                <i class="fa fa-plus"></i> {{ __('Novo Pedido') }}
                            </a>
                            <a href="{{ route('backoffice.technical_requests.technicians') }}" class="btn btn-outline-secondary mr-2 px-4">
                                <i class="fa fa-users"></i> {{ __('Ver Responsáveis') }}
                            </a>
                            <a href="{{ route('backoffice.technical_requests.export', request()->only(['q', 'codigo_loja', 'serial_number', 'estado', 'prioridade', 'tipo_servico', 'assigned_technician_id', 'mes', 'data_inicio', 'data_fim'])) }}" class="btn btn-outline-primary px-4">
                                <i class="fa fa-file-excel"></i> {{ __('Exportar Excel') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

        <div class="row mb-4 hotline-stats-row">
            <div class="col-md-6 col-xl-3 mb-3">
                <a href="{{ $statUrl() }}" class="card hotline-stat hotline-stat-total h-100 {{ empty($selectedStates) ? 'is-active' : '' }}" aria-label="{{ __('Mostrar todos os pedidos') }}">
                    <div class="card-body">
                        <div class="hotline-stat-top">
                            <small class="text-muted text-uppercase d-block mb-0">{{ __('Total') }}</small>
                            <span class="hotline-stat-icon text-primary"><i class="fa fa-layer-group"></i></span>
                        </div>
                        <div class="hotline-stat-value">
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                            <span class="badge badge-light">{{ __('Pedidos') }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-xl-3 mb-3">
                <a href="{{ $statUrl('pendente') }}" class="card hotline-stat hotline-stat-pending h-100 {{ in_array('pendente', $selectedStates, true) ? 'is-active' : '' }}" aria-label="{{ __('Mostrar pedidos pendentes') }}">
                    <div class="card-body">
                        <div class="hotline-stat-top">
                            <small class="text-muted text-uppercase d-block mb-0">{{ __('Pendentes') }}</small>
                            <span class="hotline-stat-icon text-warning"><i class="fas fa-clock"></i></span>
                        </div>
                        <div class="hotline-stat-value">
                            <h3 class="mb-0 text-warning">{{ $stats['pendente'] }}</h3>
                            <span class="badge badge-light">{{ __('Ação') }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-xl-3 mb-3">
                <a href="{{ $statUrl('agendado') }}" class="card hotline-stat hotline-stat-scheduled h-100 {{ in_array('agendado', $selectedStates, true) ? 'is-active' : '' }}" aria-label="{{ __('Mostrar pedidos agendados') }}">
                    <div class="card-body">
                        <div class="hotline-stat-top">
                            <small class="text-muted text-uppercase d-block mb-0">{{ __('Agendados') }}</small>
                            <span class="hotline-stat-icon text-info"><i class="fas fa-calendar-check"></i></span>
                        </div>
                        <div class="hotline-stat-value">
                            <h3 class="mb-0 text-info">{{ $stats['agendado'] }}</h3>
                            <span class="badge badge-light">{{ __('Planeado') }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-xl-3 mb-3">
                <a href="{{ $statUrl('aguarda_peca') }}" class="card hotline-stat hotline-stat-awaiting h-100 {{ in_array('aguarda_peca', $selectedStates, true) ? 'is-active' : '' }}" aria-label="{{ __('Mostrar pedidos a aguardar peça') }}">
                    <div class="card-body">
                        <div class="hotline-stat-top">
                            <small class="text-muted text-uppercase d-block mb-0">{{ __('Aguarda Peça') }}</small>
                            <span class="hotline-stat-icon text-danger"><i class="fas fa-tools"></i></span>
                        </div>
                        <div class="hotline-stat-value">
                            <h3 class="mb-0 text-danger">{{ $stats['aguarda_peca'] }}</h3>
                            <span class="badge badge-light">{{ __('Bloqueado') }}</span>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-xl-3 mb-3">
                <a href="{{ $statUrl('concluido') }}" class="card hotline-stat hotline-stat-done h-100 {{ in_array('concluido', $selectedStates, true) ? 'is-active' : '' }}" aria-label="{{ __('Mostrar pedidos concluídos') }}">
                    <div class="card-body">
                        <div class="hotline-stat-top">
                            <small class="text-muted text-uppercase d-block mb-0">{{ __('Concluídos') }}</small>
                            <span class="hotline-stat-icon text-success"><i class="fas fa-check-circle"></i></span>
                        </div>
                        <div class="hotline-stat-value">
                            <h3 class="mb-0 text-success">{{ $stats['concluido'] }}</h3>
                            <span class="badge badge-light">{{ __('Fechado') }}</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="card hotline-panel border-0">
            <div class="card-body">
                @if($canManageAll)
                    <div class="hotline-open-callout">
                        <div>
                            <h5 class="hotline-open-callout-title">{{ __('Todos os pedidos em aberto') }}</h5>
                            <p class="hotline-open-callout-copy">{{ __('Abra uma tabela resumida, agrupada por técnico, para perceber rapidamente tudo o que ainda está por concluir.') }}</p>
                        </div>
                        <a href="{{ route('backoffice.technical_requests.open_all', request()->only(['mes', 'data_inicio', 'data_fim'])) }}" class="btn btn-primary">
                            <i class="fa fa-table"></i> {{ __('Ver todos em aberto') }}
                        </a>
                    </div>
                @else
                    <div class="hotline-open-callout">
                        <div>
                            <h5 class="hotline-open-callout-title">{{ __('Meus pedidos em aberto') }}</h5>
                            <p class="hotline-open-callout-copy">{{ __('Abra uma vista resumida e adaptada ao telemóvel com todos os seus pedidos ainda por concluir.') }}</p>
                        </div>
                        <a href="{{ route('backoffice.technical_requests.my_open', request()->only(['mes', 'data_inicio', 'data_fim'])) }}" class="btn btn-primary">
                            <i class="fa fa-table"></i> {{ __('Ver pedidos em aberto') }}
                        </a>
                    </div>
                @endif

                <div class="hotline-panel-header">
                    <div>
                        <h5 class="hotline-panel-title">{{ __('Pedidos de Assistência Técnica') }}</h5>
                        <p class="hotline-panel-copy">{{ __('Tudo o que precisa para acompanhar pedidos, estados, técnicos e agenda numa só vista.') }}</p>
                    </div>
                    @if($hasFilters)
                        <span class="badge badge-primary">{{ __('Filtros ativos') }}</span>
                    @endif
                </div>

                <form method="GET" class="mb-4">
                    <div class="hotline-filter-box">
                        <div class="row align-items-end">
                            <div class="col-md-6 col-xl-3 mb-3">
                                <label for="q">{{ __('Pesquisa rápida') }}</label>
                                <input type="text" name="q" id="q" value="{{ request('q') }}" class="form-control" placeholder="{{ __('Loja, origem, descrição...') }}">
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="codigo_loja">{{ __('Código Loja') }}</label>
                                <input type="text" name="codigo_loja" id="codigo_loja" value="{{ request('codigo_loja') }}" class="form-control" placeholder="{{ __('Ex: 123') }}">
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="serial_number">{{ __('Nº Série') }}</label>
                                <input type="text" name="serial_number" id="serial_number" value="{{ request('serial_number') }}" class="form-control" placeholder="{{ __('Ex: SN12345') }}">
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="data_inicio">{{ __('Data início') }}</label>
                                <input type="date" name="data_inicio" id="data_inicio" value="{{ request('data_inicio') }}" class="form-control">
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="prioridade">{{ __('Prioridade') }}</label>
                                <select name="prioridade" id="prioridade" class="form-control">
                                    <option value="">{{ __('Todas') }}</option>
                                    @foreach($priorities as $value => $label)
                                        <option value="{{ $value }}" {{ request('prioridade') === $value ? 'selected' : '' }}>
                                            {{ __($label) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="zona">{{ __('Zona') }}</label>
                                <select name="zona" id="zona" class="form-control">
                                    <option value="">{{ __('Todas') }}</option>
                                    @foreach($zones as $value => $label)
                                        <option value="{{ $value }}" {{ request('zona') === $value ? 'selected' : '' }}>
                                            {{ __($label) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="data_fim">{{ __('Data fim') }}</label>
                                <input type="date" name="data_fim" id="data_fim" value="{{ request('data_fim') }}" class="form-control">
                            </div>
                            @if($canManageAll)
                                <div class="col-md-6 col-xl-3 mb-3">
                                    <label for="assigned_technician_id">{{ __('Técnico') }}</label>
                                    <select name="assigned_technician_id" id="assigned_technician_id" class="form-control selectpicker" data-live-search="true" title="{{ __('Todos') }}">
                                        <option value="">{{ __('Todos') }}</option>
                                        <option value="unassigned" {{ request('assigned_technician_id') === 'unassigned' ? 'selected' : '' }}>{{ __('Por atribuir') }}</option>
                                        @foreach($technicians as $technician)
                                            <option value="{{ $technician->id }}" {{ (string) request('assigned_technician_id') === (string) $technician->id ? 'selected' : '' }}>
                                                {{ $technician->name ?: $technician->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-6 col-xl-2 mb-3">
                                <label for="mes">{{ __('Mês exportação') }}</label>
                                <input type="month" name="mes" id="mes" value="{{ request('mes') }}" class="form-control">
                            </div>
                            <div class="col-md-6 col-xl-3 mb-3">
                                <label for="estado">{{ __('Estado') }}</label>
                                <select name="estado[]" id="estado" class="form-control selectpicker" multiple data-actions-box="true" data-selected-text-format="count > 2" title="{{ __('Selecionar estados') }}">
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ in_array($value, (array) request('estado')) ? 'selected' : '' }}>
                                            {{ __($label) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="hotline-filter-actions">
                            <button type="submit" class="btn btn-primary mr-sm-2 mb-2 mb-sm-0">
                                <i class="fa fa-search"></i> {{ __('Aplicar filtros') }}
                            </button>
                            <a href="{{ route('backoffice.technical_requests.index') }}" class="btn btn-outline-secondary">
                                <i class="fa fa-undo"></i> {{ __('Limpar') }}
                            </a>
                        </div>
                    </div>
                </form>

                @if($hasFilters)
                    <div class="hotline-active-filters">
                        @if(request('q'))
                            <span class="hotline-filter-chip"><i class="fa fa-search"></i> {{ request('q') }}</span>
                        @endif
                        @if(request('codigo_loja'))
                            <span class="hotline-filter-chip"><i class="fa fa-store"></i> {{ __('Loja') }}: {{ request('codigo_loja') }}</span>
                        @endif
                        @if(request('serial_number'))
                            <span class="hotline-filter-chip"><i class="fa fa-hashtag"></i> {{ request('serial_number') }}</span>
                        @endif
                        @if(request('prioridade'))
                            <span class="hotline-filter-chip"><i class="fa fa-flag"></i> {{ ucfirst(request('prioridade')) }}</span>
                        @endif
                        @if(request('zona'))
                            <span class="hotline-filter-chip"><i class="fa fa-map-marker-alt"></i> {{ $zones[request('zona')] ?? request('zona') }}</span>
                        @endif
                        @if(request('data_inicio') || request('data_fim'))
                            <span class="hotline-filter-chip"><i class="fa fa-calendar-alt"></i> {{ request('data_inicio') ?: '...' }} - {{ request('data_fim') ?: '...' }}</span>
                        @endif
                    </div>
                @endif

                @if($requests->isNotEmpty())
                    <div class="hotline-grid">
                        @foreach($requests as $request)
                            <article class="hotline-ticket" data-state="{{ $request->estado }}">
                                @php
                                    $wasEdited = $request->updated_at && $request->created_at && $request->updated_at->ne($request->created_at);
                                @endphp
                                <div>
                                    <div class="hotline-ticket-header">
                                        <div class="hotline-store-title">
                                            {{ $request->store->codigo_loja ?? '-' }} - {{ $request->store->nome_loja ?? '-' }}
                                            @if($request->store->insignia ?? null)
                                                <span class="hotline-brand-badge brand-{{ $request->store->insignia }}">
                                                    {{ ucfirst($request->store->insignia) }}
                                                </span>
                                            @endif
                                        </div>
                                        @if(($request->store->morada ?? null) || ($request->store->cidade ?? null) || ($request->store->codigo_postal ?? null))
                                            <div class="hotline-address">
                                                <i class="fa fa-map-marker-alt"></i>
                                                {{ implode(', ', array_filter([
                                                    $request->store->morada ?? null,
                                                    trim(implode(' ', array_filter([
                                                        $request->store->codigo_postal ?? null,
                                                        $request->store->cidade ?? null,
                                                    ]))),
                                                ])) }}
                                            </div>
                                        @endif
                                        <div class="hotline-store-subtitle">{{ __('Criado por') }}: {{ $request->creator->name ?? $request->creator->email ?? '—' }} - {{ $request->created_at ? \Carbon\Carbon::parse($request->created_at)->format('H:i') : '—' }}</div>
                                        @if($wasEdited)
                                            <div class="hotline-store-subtitle">{{ __('Última edição por') }}: {{ $request->editor->name ?? $request->editor->email ?? '—' }} - {{ $request->updated_at ? \Carbon\Carbon::parse($request->updated_at)->format('H:i') : '—' }}</div>
                                        @endif
                                        @if($request->estado === 'concluido')
                                            <div class="hotline-store-subtitle">{{ __('Concluído por') }}: {{ $request->editor->name ?? $request->editor->email ?? '—' }}</div>
                                        @endif
                                    </div>
                                    <div class="hotline-meta-stack">
                                        <span class="hotline-meta hotline-meta-strong">
                                            <i class="fa fa-user-cog"></i>
                                            {{ $request->assignedPersonTypeLabel() }}: {{ $request->assignedPersonLabel() }}
                                        </span>
                                    </div>
                                    <div class="hotline-snippet">{{ Str::limit($request->descricao_problema ?: 'Sem descrição.', 180) }}</div>
                                </div>

                                <div>
                                    <div class="hotline-section-label">{{ __('Dados') }}</div>
                                    <div class="hotline-meta-stack">
                                        <span class="hotline-meta"><i class="fa fa-user-circle"></i> {{ $request->origem ?: '—' }}</span>
                                        <span class="hotline-meta"><i class="fa fa-hashtag"></i> {{ $request->machine->serial_number ?? '—' }}</span>
                                        @if($request->zona)
                                            <span class="hotline-meta"><i class="fa fa-map-marker-alt"></i> {{ $zones[$request->zona] ?? ucfirst($request->zona) }}</span>
                                        @endif
                                    </div>
                                    <span class="hotline-meta"><i class="fa fa-tools"></i> {{ $serviceTypes[$request->tipo_servico] ?? ucfirst($request->tipo_servico) }}</span>
                                </div>

                                <div>
                                    <span class="badge 
                                        hotline-status-badge
                                        @switch($request->estado)
                                            @case('pendente') bg-warning @break
                                            @case('agendado') bg-info @break
                                            @case('concluido') bg-success @break
                                            @case('cancelado') bg-danger @break
                                            @case('aguarda_peca') bg-danger text-white @break
                                            @default bg-light
                                        @endswitch">
                                        {{ ucfirst(str_replace('_', ' ', $request->estado)) }}
                                    </span>
                                    <div class="hotline-section-label">{{ __('Prioridade') }}</div>
                                    <span class="badge 
                                        @switch($request->prioridade)
                                            @case('baixa') bg-info @break
                                            @case('media') bg-warning text-dark @break
                                            @case('alta') bg-danger text-white @break
                                            @default bg-light
                                        @endswitch">
                                        {{ ucfirst($request->prioridade) }}
                                    </span>
                                    @if($request->observacoes)
                                        <div class="hotline-note-highlight">
                                            <div class="hotline-section-label">{{ __('Observações') }}</div>
                                            <div class="hotline-note">{{ Str::limit($request->observacoes, 120) }}</div>
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <div class="hotline-section-label">{{ __('Datas') }}</div>
                                    <div class="hotline-dates">
                                        <div class="hotline-date-box">
                                            <strong>{{ __('Pedido') }}</strong>
                                            {{ \Carbon\Carbon::parse($request->data_pedido)->format('d/m/Y') }}
                                        </div>
                                        <div class="hotline-date-box">
                                            <strong>{{ __('Resolução') }}</strong>
                                            {{ $request->data_resolucao ? \Carbon\Carbon::parse($request->data_resolucao)->format('d/m/Y H:i') : '—' }}
                                        </div>
                                    </div>
                                    @if($request->estado === 'agendado' && $request->data_agendamento)
                                        <div class="hotline-schedule-highlight">
                                            <i class="fa fa-calendar-alt"></i>
                                            {{ __('Data de Agendamento') }}:
                                            {{ \Carbon\Carbon::parse($request->data_agendamento)->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                    @if($request->data_resolucao)
                                        <div class="hotline-resolution-highlight">
                                            <i class="fa fa-check-circle"></i>
                                            {{ __('Data de Resolução') }}:
                                            {{ \Carbon\Carbon::parse($request->data_resolucao)->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </div>

                                <div class="hotline-actions">
                                    <a href="{{ route('backoffice.technical_requests.show', ['id' => $request->id, 'return_url' => url()->full()]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-eye"></i> {{ __('Ver') }}
                                    </a>
                                    @if($canManageAll || $request->estado !== 'concluido')
                                        <a href="{{ route('backoffice.technical_requests.edit', ['id' => $request->id, 'return_url' => url()->full()]) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa fa-edit"></i> {{ __('Editar') }}
                                        </a>
                                    @endif
                                    @if($canManageAll)
                                        <a href="{{ route('backoffice.technical_requests.delete', array_merge(['id' => $request->id], request()->only(['page', 'q', 'codigo_loja', 'serial_number', 'estado', 'prioridade', 'zona', 'assigned_technician_id']))) }}"
                                           onclick="return confirm('@lang('Tem a certeza que deseja apagar este pedido?')')"
                                           class="btn btn-sm btn-outline-danger">
                                            <i class="fa fa-trash"></i> {{ __('Apagar') }}
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="text-center hotline-empty">
                        <div class="hotline-empty-icon">
                            <i class="fa fa-inbox"></i>
                        </div>
                        <div class="text-muted mb-2">{{ __('Nenhum pedido encontrado com os filtros atuais.') }}</div>
                        @if($canManageAll)
                            <a href="{{ route('backoffice.technical_requests.create') }}" class="btn btn-sm btn-success">
                                <i class="fa fa-plus"></i> {{ __('Criar primeiro pedido') }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('foot-scripts')
<script>
document.addEventListener("DOMContentLoaded", function () {
    if (window.jQuery && $('.selectpicker').length) {
        $('.selectpicker').selectpicker();
    }
});
</script>
@endsection
