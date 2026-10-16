@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Documentação') }}</title>
@endsection

@php($types = collect(config('documents.types'))->map(fn ($t) => ['label' => __($t['label'])] + $t)->all())
@php($languages = array_map(fn ($l) => __($l), config('documents.languages')))
@php($filters = request()->only(['q', 'tipo', 'modelo']))
@php($familyIcons = ['SiCompact' => 'fa-cube', 'SiVario' => 'fa-cubes', 'Outros' => 'fa-ellipsis-h'])

@push('styles')
<style>
    /* Cabeçalho */
    .doc-hero { background: linear-gradient(135deg, #0f4c81 0%, #1f76c2 100%); border-radius: 18px; padding: 22px 24px; color: #fff; margin-bottom: 1.2rem;
                box-shadow: 0 12px 28px rgba(15, 76, 129, .25); }
    .doc-hero h4 { margin: 0; font-weight: 800; font-size: 1.5rem; }
    .doc-hero .sub { opacity: .8; font-size: .9rem; }
    .doc-hero .search { position: relative; flex: 1 1 320px; max-width: 560px; }
    .doc-hero .search i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #7d8ea3; }
    .doc-hero .search input { height: 46px; border-radius: 999px; border: 0; padding-left: 2.6rem; font-size: .95rem; box-shadow: 0 4px 14px rgba(0, 0, 0, .15); }
    .doc-hero .btn-light { border-radius: 999px; font-weight: 700; padding: .55rem 1.1rem; }
    .doc-hero .btn-ghost { border-radius: 999px; font-weight: 600; padding: .55rem 1rem; color: #fff; border: 1px solid rgba(255, 255, 255, .45); background: rgba(255, 255, 255, .1); }
    .doc-hero .btn-ghost:hover { background: rgba(255, 255, 255, .2); color: #fff; }

    /* Menu lateral */
    .doc-side { background: #fff; border-radius: 16px; box-shadow: 0 6px 18px rgba(15, 23, 42, .07); padding: 14px; position: sticky; top: 1rem; }
    .doc-side h6 { font-size: .7rem; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; color: #8a96a8; margin: 6px 8px 8px; }
    .doc-side .family { font-size: .72rem; font-weight: 700; color: #415166; margin: 10px 8px 4px; display: flex; align-items: center; gap: .4rem; }
    .doc-nav { display: flex; align-items: center; gap: .6rem; padding: .5rem .6rem; border-radius: 10px; color: #263238; font-size: .88rem; font-weight: 600;
               text-decoration: none !important; transition: background .12s; }
    .doc-nav:hover { background: #f1f5fb; color: #132238; }
    .doc-nav.is-active { background: var(--c, #1f6fd1); color: #fff; }
    .doc-nav .ico { width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: .8rem;
                    background: color-mix(in srgb, var(--c, #1f6fd1) 14%, #fff); color: var(--c, #1f6fd1); flex: 0 0 28px; }
    .doc-nav.is-active .ico { background: rgba(255, 255, 255, .22); color: #fff; }
    .doc-nav .count { margin-left: auto; font-size: .72rem; font-weight: 700; background: #eef2f7; color: #546e7a; border-radius: 999px; padding: .05rem .5rem; }
    .doc-nav.is-active .count { background: rgba(255, 255, 255, .25); color: #fff; }
    .doc-nav.is-model { padding-left: 1.4rem; font-weight: 500; }
    .doc-nav.is-model .dot { width: 8px; height: 8px; border-radius: 50%; background: #b0bec5; }
    .doc-nav.is-model.is-active .dot { background: #fff; }
    .doc-side hr { margin: 10px 4px; border-color: #eef2f7; }

    /* Barra de filtros ativos */
    .doc-active { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; margin-bottom: .9rem; }
    .doc-active .label { font-weight: 800; font-size: 1.05rem; color: #132238; margin-right: .3rem; }
    .doc-active .tag { display: inline-flex; align-items: center; gap: .35rem; background: #fff; border: 1px solid #dfe5ee; border-radius: 999px; padding: .2rem .7rem;
                       font-size: .8rem; color: #415166; text-decoration: none !important; }
    .doc-active .tag:hover { border-color: #c62828; color: #c62828; }

    /* Cartões */
    .doc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem; }
    .doc-card { position: relative; background: #fff; border-radius: 16px; box-shadow: 0 4px 14px rgba(15, 23, 42, .07); display: flex; flex-direction: column;
                overflow: hidden; transition: transform .15s, box-shadow .15s; }
    .doc-card:hover { transform: translateY(-3px); box-shadow: 0 12px 26px rgba(15, 23, 42, .12); }
    .doc-card.is-checked { box-shadow: 0 0 0 3px var(--doc-color), 0 12px 26px rgba(15, 23, 42, .12); }
    .doc-band { height: 6px; background: var(--doc-color); }
    .doc-body { padding: 1rem 1rem .5rem; flex: 1; display: flex; gap: .85rem; }
    .doc-icon { flex: 0 0 48px; height: 58px; border-radius: 10px; background: color-mix(in srgb, var(--doc-color) 12%, #fff); color: var(--doc-color);
                display: flex; align-items: center; justify-content: center; font-size: 1.5rem; text-decoration: none !important; position: relative; }
    .doc-icon small { position: absolute; bottom: 4px; font-size: .5rem; font-weight: 800; letter-spacing: .05em; }
    .doc-type { display: inline-flex; align-items: center; gap: .3rem; font-size: .68rem; font-weight: 800; color: var(--doc-color); text-transform: uppercase; letter-spacing: .05em; }
    .doc-title { font-weight: 700; color: #132238; line-height: 1.25; display: block; margin: .2rem 0 .4rem; word-break: break-word; text-decoration: none !important; }
    .doc-title:hover { color: var(--doc-color); }
    .doc-models { display: flex; flex-wrap: wrap; gap: .25rem; margin-bottom: .35rem; }
    .doc-model { font-size: .7rem; font-weight: 600; background: #eef2f7; color: #37474f; border-radius: 6px; padding: .12rem .45rem; }
    .doc-meta { font-size: .74rem; color: #8a96a8; }
    .doc-desc { font-size: .8rem; color: #6c757d; margin-top: .3rem; }
    .doc-actions { display: flex; gap: .4rem; padding: .6rem 1rem .9rem; align-items: center; border-top: 1px solid #f1f4f8; }
    .doc-actions .btn { border-radius: 9px; font-weight: 600; }
    .doc-check { position: absolute; top: 14px; right: 12px; }
    .doc-check input { width: 18px; height: 18px; cursor: pointer; }
    .doc-toolbar { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; justify-content: space-between; margin-bottom: .8rem; }
    .doc-empty { background: #fff; border-radius: 16px; padding: 3rem 1rem; text-align: center; color: #8a96a8; box-shadow: 0 4px 14px rgba(15, 23, 42, .06); }

    /* Adicionar documentos: passos */
    .doc-upload { background: #fff; border-radius: 16px; box-shadow: 0 6px 18px rgba(15, 23, 42, .08); padding: 20px; margin-bottom: 1.2rem; border-top: 5px solid #1f6fd1; }
    .doc-step { display: flex; align-items: center; gap: .55rem; font-weight: 800; color: #132238; margin: 0 0 .7rem; font-size: .95rem; }
    .doc-step span { width: 26px; height: 26px; border-radius: 50%; background: #1f6fd1; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: .8rem; }
    .doc-step small { font-weight: 500; color: #8a96a8; }
    .doc-drop { border: 2px dashed #90caf9; border-radius: 14px; background: #f5faff; padding: 1.6rem; text-align: center; cursor: pointer; transition: all .15s; display: block; margin: 0; }
    .doc-drop.is-over { background: #e3f2fd; border-color: #1976d2; }
    .doc-drop i { font-size: 2rem; color: #1976d2; }
    .doc-file-row { display: flex; align-items: center; gap: .6rem; padding: .45rem .6rem; border: 1px solid #e9ecef; border-radius: 9px; margin-top: .4rem; background: #fff; }
    .doc-file-row .fa-file-pdf { color: #d32f2f; font-size: 1.2rem; }
    .doc-file-row small { white-space: nowrap; color: #8a96a8; }
    .type-tiles { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: .6rem; }
    .type-tile { margin: 0; cursor: pointer; }
    .type-tile input { display: none; }
    .type-tile .box { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .45rem; text-align: center; height: 92px; padding: .5rem;
                      border: 2px solid #e3e9f1; border-radius: 12px; background: #fff; font-size: .8rem; font-weight: 700; color: #415166; transition: all .12s; }
    .type-tile .box i { font-size: 1.35rem; color: var(--c); }
    .type-tile:hover .box { border-color: var(--c); }
    .type-tile input:checked + .box { border-color: var(--c); background: color-mix(in srgb, var(--c) 10%, #fff); color: #132238; box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 25%, transparent); }
    .model-groups { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .7rem; }
    .model-group { border: 1px solid #e3e9f1; border-radius: 12px; padding: .65rem .75rem; background: #fafbfd; }
    .model-group .gtitle { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #546e7a; margin-bottom: .45rem; display: flex; align-items: center; gap: .4rem; }
    .model-pick { display: flex; align-items: center; gap: .5rem; margin: 0 0 .3rem; padding: .35rem .5rem; border-radius: 8px; cursor: pointer; font-size: .86rem; font-weight: 600; color: #263238; }
    .model-pick:hover { background: #eef4fc; }
    .model-pick input { width: 17px; height: 17px; accent-color: #1f6fd1; }
    .model-general { display: flex; align-items: center; gap: .6rem; padding: .6rem .8rem; border-radius: 12px; border: 2px dashed #cfd8e3; margin-bottom: .7rem; cursor: pointer; font-weight: 700; color: #415166; }
    .model-general input { width: 17px; height: 17px; accent-color: #1f6fd1; }
    .model-general.is-on { border-style: solid; border-color: #1f6fd1; background: #eef4fc; color: #132238; }

    @media (max-width: 991px) { .doc-side { position: static; margin-bottom: 1rem; } }
</style>
@endpush

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

{{-- Cabeçalho --}}
<div class="doc-hero">
    <div class="d-flex flex-wrap align-items-center" style="gap: 1rem 1.5rem">
        <div>
            <h4><i class="fas fa-book-open"></i> {{ __('Documentação') }}</h4>
            <div class="sub">{{ $total }} {{ $total === 1 ? __('documento') : __('documentos') }} · {{ __('manuais, especificações e esquemas') }}</div>
        </div>
        <form method="GET" class="search ml-lg-auto">
            @foreach(request()->only(['tipo', 'modelo']) as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
            <i class="fa fa-search"></i>
            <input type="search" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ __('Pesquisar por título, modelo ou descrição…') }}">
        </form>
        <div class="d-flex" style="gap: .5rem">
            @if($canConfigure)
                <button class="btn btn-ghost" type="button" data-toggle="collapse" data-target="#doc-access"><i class="fa fa-user-lock"></i> {{ __('Acessos') }}</button>
            @endif
            @if($canManage)
                <button class="btn btn-light" type="button" data-toggle="collapse" data-target="#doc-upload"><i class="fa fa-plus"></i> {{ __('Adicionar') }}</button>
            @endif
        </div>
    </div>
</div>

{{-- Acessos por função (só Coordenador) --}}
@if($canConfigure)
    <div class="collapse" id="doc-access">
        <div class="doc-upload" style="border-top-color: #546e7a">
            <div class="doc-step"><i class="fa fa-user-lock"></i> {{ __('Quem tem acesso à Documentação') }}</div>
            <p class="small text-muted">{{ __('Escolha que funções podem ver (abrir e descarregar) e gerir (adicionar, editar e apagar). Quem não tiver acesso não vê a Documentação no menu.') }}</p>
            <form method="POST" action="{{ route('backoffice.documents.permissions') }}">
                @csrf
                @method('PUT')
                <table class="table table-sm mb-3" style="max-width: 560px">
                    <thead>
                        <tr><th>{{ __('Função') }}</th><th class="text-center">{{ __('Ver') }}</th><th class="text-center">{{ __('Gerir') }}</th></tr>
                    </thead>
                    <tbody>
                        @foreach(config('hr.functions') as $key => $label)
                            @php($isOwner = $key === config('documents.owner_function'))
                            @php($perm = $permissions[$key] ?? null)
                            <tr>
                                <td>
                                    <span class="badge text-white" style="background: {{ config('hr.function_colors.' . $key, '#6c757d') }}; font-size: .85rem">{{ __($label) }}</span>
                                    @if($isOwner)<small class="text-muted ml-1">{{ __('acesso total (fixo)') }}</small>@endif
                                </td>
                                <td class="text-center"><input type="checkbox" name="ver[{{ $key }}]" value="1" class="doc-perm-view" data-funcao="{{ $key }}" @checked($isOwner || $perm?->pode_ver) @disabled($isOwner)></td>
                                <td class="text-center"><input type="checkbox" name="gerir[{{ $key }}]" value="1" class="doc-perm-manage" data-funcao="{{ $key }}" @checked($isOwner || $perm?->pode_gerir) @disabled($isOwner)></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> {{ __('Gravar acessos') }}</button>
            </form>
        </div>
    </div>
@endif

{{-- Adicionar documentos, em passos --}}
@if($canManage)
    <div class="collapse {{ $errors->any() ? 'show' : '' }}" id="doc-upload">
        <form method="POST" action="{{ route('backoffice.documents.store') }}" enctype="multipart/form-data" class="doc-upload">
            @csrf
            <div class="row">
                <div class="col-lg-5 mb-3">
                    <div class="doc-step"><span>1</span> {{ __('Ficheiros') }} <small>{{ __('PDF, vários de uma vez') }}</small></div>
                    <label class="doc-drop" id="doc-drop">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <div class="font-weight-bold mt-2">{{ __('Arraste os PDFs para aqui') }}</div>
                        <div class="small text-muted">{{ __('ou clique para escolher · até') }} {{ round(config('documents.max_size_kb') / 1024) }} MB {{ __('cada') }}</div>
                        <input type="file" name="files[]" id="doc-files" accept="application/pdf,.pdf" multiple hidden>
                    </label>
                    <div id="doc-file-list"></div>
                    <small class="text-muted d-block mt-2">{{ __('O título é preenchido a partir do nome do ficheiro e pode ser corrigido.') }}</small>
                </div>
                <div class="col-lg-7">
                    <div class="doc-step"><span>2</span> {{ __('Tipo de documento') }}</div>
                    <div class="type-tiles mb-3">
                        @foreach($types as $key => $type)
                            <label class="type-tile" style="--c: {{ $type['color'] }}">
                                <input type="radio" name="tipo" value="{{ $key }}" @checked(old('tipo', 'instalacao') === $key)>
                                <span class="box"><i class="fas {{ $type['icon'] }}"></i>{{ $type['label'] }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div class="doc-step"><span>3</span> {{ __('Modelos') }} <small>{{ __('a que modelos se aplica') }}</small></div>
                    @php($oldModels = old('modelos', request('modelo') && request('modelo') !== 'geral' ? [request('modelo')] : []))
                    <label class="model-general {{ $oldModels ? '' : 'is-on' }}" id="model-general">
                        <input type="checkbox" id="model-general-input" @checked(!$oldModels)>
                        <i class="fas fa-globe-europe"></i> {{ __('Geral — serve para todos os modelos') }}
                    </label>
                    <div class="model-groups mb-2">
                        @foreach(collect($modelGroups)->except('Outros') as $family => $list)
                            <div class="model-group">
                                <div class="gtitle"><i class="fas {{ $familyIcons[$family] ?? 'fa-cube' }}"></i> {{ __($family) }}</div>
                                @foreach($list as $model)
                                    <label class="model-pick"><input type="checkbox" name="modelos[]" value="{{ $model }}" class="model-input" @checked(in_array($model, $oldModels))> {{ $model }}</label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <input type="text" name="novo_modelo" class="form-control form-control-sm mb-3" value="{{ old('novo_modelo') }}" placeholder="{{ __('Outro modelo que não está na lista (opcional)') }}">

                    <div class="doc-step"><span>4</span> {{ __('Detalhes') }} <small>{{ __('opcional') }}</small></div>
                    <div class="form-row">
                        <div class="form-group col-sm-4">
                            <select name="idioma" class="form-control">
                                <option value="">{{ __('Idioma') }}</option>
                                @foreach($languages as $key => $label)
                                    <option value="{{ $key }}" @selected(old('idioma', 'pt') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-sm-8">
                            <input type="text" name="descricao" class="form-control" value="{{ old('descricao') }}" placeholder="{{ __('Descrição curta') }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="doc-submit" disabled style="border-radius: 12px; font-weight: 700">
                        <i class="fa fa-upload"></i> {{ __('Gravar documentos') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endif

<div class="row">
    {{-- Menu lateral --}}
    <div class="col-lg-3">
        <nav class="doc-side">
            <h6>{{ __('Tipo de documento') }}</h6>
            <a href="{{ route('backoffice.documents.index', \Illuminate\Support\Arr::except($filters, 'tipo')) }}" class="doc-nav {{ request('tipo') ? '' : 'is-active' }}" style="--c: #263238">
                <span class="ico"><i class="fas fa-layer-group"></i></span> {{ __('Todos os tipos') }} <span class="count">{{ $total }}</span>
            </a>
            @foreach($types as $key => $type)
                <a href="{{ route('backoffice.documents.index', ['tipo' => $key] + $filters) }}" class="doc-nav {{ request('tipo') === $key ? 'is-active' : '' }}" style="--c: {{ $type['color'] }}">
                    <span class="ico"><i class="fas {{ $type['icon'] }}"></i></span> {{ $type['label'] }} <span class="count">{{ $typeCounts[$key] ?? 0 }}</span>
                </a>
            @endforeach

            <hr>
            <h6>{{ __('Modelo') }}</h6>
            <a href="{{ route('backoffice.documents.index', \Illuminate\Support\Arr::except($filters, 'modelo')) }}" class="doc-nav {{ request('modelo') ? '' : 'is-active' }}" style="--c: #263238">
                <span class="ico"><i class="fas fa-th-large"></i></span> {{ __('Todos os modelos') }}
            </a>
            @foreach($modelGroups as $family => $list)
                <div class="family"><i class="fas {{ $familyIcons[$family] ?? 'fa-cube' }}"></i> {{ __($family) }}</div>
                @foreach($list as $model)
                    <a href="{{ route('backoffice.documents.index', ['modelo' => $model] + $filters) }}" class="doc-nav is-model {{ request('modelo') === $model ? 'is-active' : '' }}" style="--c: #1f6fd1">
                        <span class="dot"></span> {{ $model }} <span class="count">{{ $modelCounts[$model] ?? 0 }}</span>
                    </a>
                @endforeach
            @endforeach
            <div class="family"><i class="fas fa-globe-europe"></i> {{ __('Comum') }}</div>
            <a href="{{ route('backoffice.documents.index', ['modelo' => 'geral'] + $filters) }}" class="doc-nav is-model {{ request('modelo') === 'geral' ? 'is-active' : '' }}" style="--c: #1f6fd1">
                <span class="dot"></span> {{ __('Geral (todos os modelos)') }} <span class="count">{{ $modelCounts['geral'] ?? 0 }}</span>
            </a>
        </nav>
    </div>

    {{-- Lista --}}
    <div class="col-lg-9">
        @if($filters)
            <div class="doc-active">
                <span class="label">{{ $documents->count() }} {{ $documents->count() === 1 ? __('resultado') : __('resultados') }}</span>
                @if(request('tipo'))
                    <a class="tag" href="{{ route('backoffice.documents.index', \Illuminate\Support\Arr::except($filters, 'tipo')) }}"><i class="fas {{ $types[request('tipo')]['icon'] ?? 'fa-file' }}"></i> {{ $types[request('tipo')]['label'] ?? request('tipo') }} <i class="fa fa-times"></i></a>
                @endif
                @if(request('modelo'))
                    <a class="tag" href="{{ route('backoffice.documents.index', \Illuminate\Support\Arr::except($filters, 'modelo')) }}"><i class="fas fa-cube"></i> {{ request('modelo') === 'geral' ? __('Geral') : request('modelo') }} <i class="fa fa-times"></i></a>
                @endif
                @if(request('q'))
                    <a class="tag" href="{{ route('backoffice.documents.index', \Illuminate\Support\Arr::except($filters, 'q')) }}"><i class="fa fa-search"></i> “{{ request('q') }}” <i class="fa fa-times"></i></a>
                @endif
            </div>
        @endif

        @if($documents->isEmpty())
            <div class="doc-empty">
                <i class="fas fa-folder-open fa-3x mb-3"></i>
                <p class="mb-1">{{ $total ? __('Nenhum documento corresponde aos filtros.') : __('Ainda não há documentos.') }}</p>
                @if($filters)<a href="{{ route('backoffice.documents.index') }}">{{ __('Limpar filtros') }}</a>@endif
            </div>
        @else
            <form method="GET" action="{{ route('backoffice.documents.zip') }}" id="doc-zip-form"></form>
            <div class="doc-toolbar">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="doc-check-all">
                    <label class="form-check-label small" for="doc-check-all">{{ __('Selecionar todos') }} ({{ $documents->count() }})</label>
                </div>
                <div>
                    <button type="submit" form="doc-zip-form" class="btn btn-outline-secondary btn-sm" id="doc-zip-selected" disabled style="border-radius: 999px">
                        <i class="fa fa-file-archive"></i> {{ __('Descarregar selecionados') }} <span id="doc-selected-count"></span>
                    </button>
                    @if(request('modelo'))
                        <a href="{{ route('backoffice.documents.zip', ['modelo' => request('modelo')]) }}" class="btn btn-primary btn-sm" style="border-radius: 999px">
                            <i class="fa fa-file-archive"></i> {{ __('Tudo de') }} {{ request('modelo') === 'geral' ? __('Geral') : request('modelo') }} (ZIP)
                        </a>
                    @endif
                </div>
            </div>

            <div class="doc-grid">
                @foreach($documents as $doc)
                    @php($mobileHref = route('backoffice.documents.read', [$doc->id, 'voltar' => request()->fullUrl()]))
                    <div class="doc-card" style="--doc-color: {{ $doc->type['color'] }}">
                        <div class="doc-band"></div>
                        <label class="doc-check" title="{{ __('Selecionar') }}">
                            <input type="checkbox" name="ids[]" value="{{ $doc->id }}" form="doc-zip-form" class="doc-select">
                        </label>
                        <div class="doc-body">
                            <a href="{{ route('backoffice.documents.view', $doc->id) }}" target="_blank" data-mobile-href="{{ $mobileHref }}" class="doc-icon">
                                <i class="fas fa-file-pdf"></i><small>PDF</small>
                            </a>
                            <div class="flex-grow-1" style="min-width: 0; padding-right: 1.2rem">
                                <span class="doc-type"><i class="fas {{ $doc->type['icon'] }}"></i> {{ $doc->type['label'] }}</span>
                                <a href="{{ route('backoffice.documents.view', $doc->id) }}" target="_blank" data-mobile-href="{{ $mobileHref }}" class="doc-title">{{ $doc->titulo }}</a>
                                <div class="doc-models">
                                    @forelse($doc->modelos ?? [] as $model)
                                        <span class="doc-model">{{ $model }}</span>
                                    @empty
                                        <span class="doc-model"><i class="fas fa-globe-europe"></i> {{ __('Geral') }}</span>
                                    @endforelse
                                </div>
                                <div class="doc-meta">
                                    @if($doc->idioma)<span title="{{ $languages[$doc->idioma] ?? '' }}">{{ strtoupper($doc->idioma) }}</span> · @endif
                                    {{ $doc->size_label }} · {{ $doc->created_at->format('d/m/Y') }}
                                </div>
                                @if($doc->descricao)<div class="doc-desc">{{ \Illuminate\Support\Str::limit($doc->descricao, 110) }}</div>@endif
                            </div>
                        </div>
                        <div class="doc-actions">
                            <a href="{{ route('backoffice.documents.view', $doc->id) }}" target="_blank" data-mobile-href="{{ $mobileHref }}" class="btn btn-sm btn-primary"><i class="fas fa-book-open"></i> {{ __('Abrir') }}</a>
                            <a href="{{ route('backoffice.documents.download', $doc->id) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Descarregar') }}"><i class="fa fa-download"></i></a>
                            @if($canManage)
                                <a href="{{ route('backoffice.documents.edit', $doc->id) }}" class="btn btn-sm btn-link text-secondary ml-auto px-1" title="{{ __('Editar') }}"><i class="fa fa-edit"></i></a>
                                <form method="POST" action="{{ route('backoffice.documents.destroy', $doc->id) }}" class="d-inline" onsubmit="return confirm({{ json_encode(__('Apagar este documento?')) }})">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger px-1" title="{{ __('Apagar') }}"><i class="fa fa-trash"></i></button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Abrir PDF: no computador abre num separador novo (rápido); no telemóvel abre a página
        // de leitura do site, que tem o botão "Voltar" (as apps no ecrã principal não têm botão de voltar)
        var isMobile = window.matchMedia('(pointer: coarse)').matches && window.innerWidth < 1024;
        if (isMobile) {
            document.querySelectorAll('a[data-mobile-href]').forEach(function (a) {
                a.addEventListener('click', function (e) {
                    e.preventDefault();
                    window.location.href = a.dataset.mobileHref;
                });
            });
        }

        // Acessos: quem gere também vê
        document.querySelectorAll('.doc-perm-manage').forEach(function (m) {
            m.addEventListener('change', function () {
                if (m.checked) document.querySelector('.doc-perm-view[data-funcao="' + m.dataset.funcao + '"]').checked = true;
            });
        });
        document.querySelectorAll('.doc-perm-view').forEach(function (v) {
            v.addEventListener('change', function () {
                if (!v.checked) document.querySelector('.doc-perm-manage[data-funcao="' + v.dataset.funcao + '"]').checked = false;
            });
        });

        // Seleção para ZIP
        var boxes = document.querySelectorAll('.doc-select');
        var all = document.getElementById('doc-check-all');
        var zipBtn = document.getElementById('doc-zip-selected');
        var countEl = document.getElementById('doc-selected-count');
        function updateSelection() {
            var n = 0;
            boxes.forEach(function (b) { b.closest('.doc-card').classList.toggle('is-checked', b.checked); if (b.checked) n++; });
            if (zipBtn) { zipBtn.disabled = n === 0; countEl.textContent = n ? '(' + n + ')' : ''; }
            if (all) { all.checked = n === boxes.length && n > 0; all.indeterminate = n > 0 && n < boxes.length; }
        }
        boxes.forEach(function (b) { b.addEventListener('change', updateSelection); });
        if (all) all.addEventListener('change', function () { boxes.forEach(function (b) { b.checked = all.checked; }); updateSelection(); });

        // Modelos: "Geral" e modelos específicos excluem-se
        var general = document.getElementById('model-general-input');
        var generalBox = document.getElementById('model-general');
        var modelInputs = document.querySelectorAll('.model-input');
        var newModel = document.querySelector('#doc-upload input[name="novo_modelo"]');
        function syncGeneral() {
            var any = Array.prototype.some.call(modelInputs, function (i) { return i.checked; }) || (newModel && newModel.value.trim());
            general.checked = !any;
            generalBox.classList.toggle('is-on', !any);
        }
        if (general) {
            general.addEventListener('change', function () {
                if (general.checked) { modelInputs.forEach(function (i) { i.checked = false; }); if (newModel) newModel.value = ''; }
                syncGeneral();
            });
            modelInputs.forEach(function (i) { i.addEventListener('change', syncGeneral); });
            if (newModel) newModel.addEventListener('input', syncGeneral);
        }

        // Envio: arrastar e largar + título por ficheiro
        var input = document.getElementById('doc-files');
        if (!input) return;
        var drop = document.getElementById('doc-drop');
        var list = document.getElementById('doc-file-list');
        var submit = document.getElementById('doc-submit');
        var maxBytes = {{ config('documents.max_size_kb') }} * 1024;
        var files = [];
        var T = {{ \Illuminate\Support\Js::from(['titulo' => __('Título'), 'grande' => __('(demasiado grande)'), 'remover' => __('Remover')]) }};

        function titleFrom(name) {
            var t = name.replace(/\.pdf$/i, '').replace(/[_-]+/g, ' ').replace(/\s+/g, ' ').trim();
            return t.charAt(0).toUpperCase() + t.slice(1);
        }
        function size(b) { return b >= 1048576 ? (b / 1048576).toFixed(1).replace('.', ',') + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB'; }
        function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }

        function sync() {
            var dt = new DataTransfer();
            files.forEach(function (f) { dt.items.add(f.file); });
            input.files = dt.files;
            list.innerHTML = '';
            files.forEach(function (f, i) {
                var tooBig = f.file.size > maxBytes;
                var row = document.createElement('div');
                row.className = 'doc-file-row';
                row.innerHTML = '<i class="fas fa-file-pdf"></i>' +
                    '<input type="text" name="titulos[]" class="form-control form-control-sm" value="' + esc(f.title) + '" title="' + esc(T.titulo) + '">' +
                    '<small class="' + (tooBig ? 'text-danger font-weight-bold' : '') + '">' + size(f.file.size) + (tooBig ? ' ' + esc(T.grande) : '') + '</small>' +
                    '<button type="button" class="btn btn-sm btn-link text-danger p-0" title="' + esc(T.remover) + '"><i class="fa fa-times"></i></button>';
                row.querySelector('input').addEventListener('input', function (e) { f.title = e.target.value; });
                row.querySelector('button').addEventListener('click', function () { files.splice(i, 1); sync(); });
                list.appendChild(row);
            });
            submit.disabled = files.length === 0 || files.some(function (f) { return f.file.size > maxBytes; });
        }
        function add(fileList) {
            Array.prototype.forEach.call(fileList, function (file) {
                if (!/\.pdf$/i.test(file.name) && file.type !== 'application/pdf') return;
                files.push({ file: file, title: titleFrom(file.name) });
            });
            sync();
        }

        input.addEventListener('change', function () { add(Array.prototype.slice.call(input.files)); });
        ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); }); });
        ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-over'); }); });
        drop.addEventListener('drop', function (e) { add(e.dataTransfer.files); });
    });
</script>
@endpush
