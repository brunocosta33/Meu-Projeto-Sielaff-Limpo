@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Documentação') }}</title>
@endsection

@php($types = config('documents.types'))
@php($languages = config('documents.languages'))
@php($filters = request()->only(['q', 'tipo', 'modelo']))

@push('styles')
<style>
    .doc-header { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem 1rem; }
    .doc-header h4 { margin: 0; font-weight: 700; }
    .doc-search { flex: 1 1 260px; max-width: 480px; position: relative; }
    .doc-search i { position: absolute; left: .8rem; top: 50%; transform: translateY(-50%); color: #9aa0a6; }
    .doc-search input { padding-left: 2.2rem; border-radius: 999px; }

    .doc-chips { display: flex; flex-wrap: wrap; gap: .4rem; }
    .doc-chips .chip-label { font-size: .75rem; font-weight: 700; text-transform: uppercase; color: #6c757d; margin-right: .3rem; align-self: center; min-width: 60px; }
    .doc-chip { display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .75rem; border-radius: 999px; border: 1px solid #dee2e6;
                background: #fff; color: #495057; font-size: .85rem; text-decoration: none !important; transition: all .15s; }
    .doc-chip:hover { border-color: var(--chip-color, #1976d2); color: var(--chip-color, #1976d2); }
    .doc-chip.is-active { background: var(--chip-color, #1976d2); border-color: var(--chip-color, #1976d2); color: #fff; }
    .doc-chip .count { font-size: .72rem; opacity: .75; }

    .doc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(270px, 1fr)); gap: 1rem; }
    .doc-card { position: relative; background: #fff; border-radius: 14px; box-shadow: 0 4px 14px rgba(15, 23, 42, .07); border-top: 5px solid var(--doc-color);
                display: flex; flex-direction: column; transition: transform .15s, box-shadow .15s; }
    .doc-card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(15, 23, 42, .12); }
    .doc-card.is-checked { box-shadow: 0 0 0 3px var(--doc-color), 0 10px 24px rgba(15, 23, 42, .12); }
    .doc-body { padding: 1rem 1rem .6rem; flex: 1; display: flex; gap: .8rem; }
    .doc-icon { flex: 0 0 46px; height: 56px; border-radius: 8px; background: #fdecea; color: #d32f2f; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; }
    .doc-title { font-weight: 700; color: #212529; line-height: 1.25; display: block; margin-bottom: .3rem; word-break: break-word; }
    .doc-title:hover { color: var(--doc-color); text-decoration: none; }
    .doc-type { display: inline-block; font-size: .7rem; font-weight: 700; color: var(--doc-color); text-transform: uppercase; letter-spacing: .04em; margin-bottom: .35rem; }
    .doc-models { display: flex; flex-wrap: wrap; gap: .25rem; margin-bottom: .35rem; }
    .doc-model { font-size: .72rem; background: #eef2f7; color: #37474f; border-radius: 6px; padding: .1rem .45rem; }
    .doc-meta { font-size: .75rem; color: #8a9199; }
    .doc-desc { font-size: .8rem; color: #6c757d; margin-top: .3rem; }
    .doc-actions { display: flex; gap: .4rem; padding: .6rem 1rem .9rem; align-items: center; }
    .doc-actions .btn { border-radius: 8px; }
    .doc-check { position: absolute; top: .55rem; right: .7rem; }
    .doc-check input { width: 18px; height: 18px; cursor: pointer; }

    .doc-toolbar { display: flex; flex-wrap: wrap; gap: .5rem; align-items: center; justify-content: space-between; margin-bottom: .8rem; }

    .doc-drop { border: 2px dashed #90caf9; border-radius: 14px; background: #f5faff; padding: 1.6rem; text-align: center; cursor: pointer; transition: all .15s; }
    .doc-drop.is-over { background: #e3f2fd; border-color: #1976d2; }
    .doc-drop i { font-size: 2rem; color: #1976d2; }
    .doc-file-row { display: flex; align-items: center; gap: .6rem; padding: .45rem .6rem; border: 1px solid #e9ecef; border-radius: 8px; margin-top: .4rem; background: #fff; }
    .doc-file-row .fa-file-pdf { color: #d32f2f; font-size: 1.2rem; }
    .doc-file-row small { white-space: nowrap; color: #8a9199; }
    .doc-pick { display: flex; flex-wrap: wrap; gap: .4rem; }
    .doc-pick label { margin: 0; }
    .doc-pick input { display: none; }
    .doc-pick span { display: inline-block; padding: .3rem .75rem; border-radius: 999px; border: 1px solid #dee2e6; cursor: pointer; font-size: .85rem; background: #fff; }
    .doc-pick input:checked + span { background: var(--chip-color, #1976d2); border-color: var(--chip-color, #1976d2); color: #fff; }
</style>
@endpush

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <div class="doc-header mb-3">
            <h4><i class="fas fa-book text-primary"></i> {{ __('Documentação') }}</h4>
            <span class="text-muted">{{ $total }} {{ $total === 1 ? __('documento') : __('documentos') }}</span>
            <form method="GET" class="doc-search ml-auto">
                @foreach(request()->only(['tipo', 'modelo']) as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
                <i class="fa fa-search"></i>
                <input type="search" name="q" class="form-control" value="{{ request('q') }}" placeholder="{{ __('Pesquisar por título, modelo ou descrição…') }}">
            </form>
            @if($canConfigure)
                <button class="btn btn-outline-secondary" type="button" data-toggle="collapse" data-target="#doc-access" aria-expanded="false">
                    <i class="fa fa-user-lock"></i> {{ __('Quem tem acesso') }}
                </button>
            @endif
            @if($canManage)
                <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#doc-upload" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">
                    <i class="fa fa-plus"></i> {{ __('Adicionar documentos') }}
                </button>
            @endif
        </div>

        {{-- Filtros --}}
        <div class="doc-chips mb-2">
            <span class="chip-label">{{ __('Tipo') }}</span>
            <a href="{{ route('backoffice.documents.index', \Illuminate\Support\Arr::except($filters, 'tipo')) }}" class="doc-chip {{ request('tipo') ? '' : 'is-active' }}">{{ __('Todos') }}</a>
            @foreach($types as $key => $type)
                <a href="{{ route('backoffice.documents.index', ['tipo' => $key] + $filters) }}" class="doc-chip {{ request('tipo') === $key ? 'is-active' : '' }}" style="--chip-color: {{ $type['color'] }}">
                    <i class="fas {{ $type['icon'] }}"></i> {{ $type['label'] }} <span class="count">{{ $typeCounts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </div>
        <div class="doc-chips">
            <span class="chip-label">{{ __('Modelo') }}</span>
            <a href="{{ route('backoffice.documents.index', \Illuminate\Support\Arr::except($filters, 'modelo')) }}" class="doc-chip {{ request('modelo') ? '' : 'is-active' }}" style="--chip-color: #37474f">{{ __('Todos') }}</a>
            @foreach($models as $model)
                <a href="{{ route('backoffice.documents.index', ['modelo' => $model] + $filters) }}" class="doc-chip {{ request('modelo') === $model ? 'is-active' : '' }}" style="--chip-color: #37474f">
                    {{ $model }} <span class="count">{{ $modelCounts[$model] ?? 0 }}</span>
                </a>
            @endforeach
            <a href="{{ route('backoffice.documents.index', ['modelo' => 'geral'] + $filters) }}" class="doc-chip {{ request('modelo') === 'geral' ? 'is-active' : '' }}" style="--chip-color: #37474f">
                {{ __('Geral') }} <span class="count">{{ $modelCounts['geral'] ?? 0 }}</span>
            </a>
        </div>
    </div>
</div>

{{-- Acessos por função (só Coordenador) --}}
@if($canConfigure)
    <div class="collapse" id="doc-access">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title mb-1"><i class="fa fa-user-lock"></i> {{ __('Quem tem acesso à Documentação') }}</h5>
                <p class="small text-muted">{{ __('Escolha que funções podem ver (abrir e descarregar) e gerir (adicionar, editar e apagar). Quem não tiver acesso não vê a Documentação no menu.') }}</p>
                <form method="POST" action="{{ route('backoffice.documents.permissions') }}">
                    @csrf
                    @method('PUT')
                    <table class="table table-sm mb-3" style="max-width: 560px">
                        <thead>
                            <tr>
                                <th>{{ __('Função') }}</th>
                                <th class="text-center">{{ __('Ver') }}</th>
                                <th class="text-center">{{ __('Gerir') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(config('hr.functions') as $key => $label)
                                @php($isOwner = $key === config('documents.owner_function'))
                                @php($perm = $permissions[$key] ?? null)
                                <tr>
                                    <td>
                                        <span class="badge text-white" style="background: {{ config('hr.function_colors.' . $key, '#6c757d') }}; font-size: .85rem">{{ $label }}</span>
                                        @if($isOwner)<small class="text-muted ml-1">{{ __('acesso total (fixo)') }}</small>@endif
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="ver[{{ $key }}]" value="1" class="doc-perm-view" data-funcao="{{ $key }}"
                                               @checked($isOwner || $perm?->pode_ver) @disabled($isOwner)>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="gerir[{{ $key }}]" value="1" class="doc-perm-manage" data-funcao="{{ $key }}"
                                               @checked($isOwner || $perm?->pode_gerir) @disabled($isOwner)>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> {{ __('Gravar acessos') }}</button>
                </form>
            </div>
        </div>
    </div>
@endif

{{-- Adicionar documentos --}}
@if($canManage)
    <div class="collapse {{ $errors->any() ? 'show' : '' }}" id="doc-upload">
        <div class="card mb-3 border-primary">
            <div class="card-body">
                <form method="POST" action="{{ route('backoffice.documents.store') }}" enctype="multipart/form-data" id="doc-upload-form">
                    @csrf
                    <div class="row">
                        <div class="col-lg-6 mb-3">
                            <label class="doc-drop d-block" id="doc-drop">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <div class="font-weight-bold mt-2">{{ __('Arraste os PDFs para aqui') }}</div>
                                <div class="small text-muted">{{ __('ou clique para escolher · vários de uma vez · até') }} {{ round(config('documents.max_size_kb') / 1024) }} MB {{ __('cada') }}</div>
                                <input type="file" name="files[]" id="doc-files" accept="application/pdf,.pdf" multiple hidden>
                            </label>
                            <div id="doc-file-list"></div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="small font-weight-bold mb-1">{{ __('Tipo de documento') }}</label>
                                <div class="doc-pick">
                                    @foreach($types as $key => $type)
                                        <label style="--chip-color: {{ $type['color'] }}">
                                            <input type="radio" name="tipo" value="{{ $key }}" @checked(old('tipo', 'instalacao') === $key)>
                                            <span><i class="fas {{ $type['icon'] }}"></i> {{ $type['label'] }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold mb-1">{{ __('Modelo(s)') }} <span class="font-weight-normal text-muted">— {{ __('nenhum = Geral') }}</span></label>
                                <div class="doc-pick mb-2">
                                    @foreach($models as $model)
                                        <label style="--chip-color: #37474f">
                                            <input type="checkbox" name="modelos[]" value="{{ $model }}" @checked(in_array($model, old('modelos', request('modelo') ? [request('modelo')] : [])))>
                                            <span>{{ $model }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <input type="text" name="novo_modelo" class="form-control form-control-sm" value="{{ old('novo_modelo') }}" placeholder="{{ __('Outro modelo (separe vários por vírgula)') }}">
                            </div>
                            <div class="form-row">
                                <div class="form-group col-sm-5">
                                    <label class="small font-weight-bold mb-1">{{ __('Idioma') }}</label>
                                    <select name="idioma" class="form-control form-control-sm">
                                        <option value="">—</option>
                                        @foreach($languages as $key => $label)
                                            <option value="{{ $key }}" @selected(old('idioma', 'pt') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-sm-7">
                                    <label class="small font-weight-bold mb-1">{{ __('Descrição (opcional)') }}</label>
                                    <input type="text" name="descricao" class="form-control form-control-sm" value="{{ old('descricao') }}">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" id="doc-submit" disabled><i class="fa fa-upload"></i> {{ __('Gravar documentos') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

{{-- Lista --}}
@if($documents->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-folder-open fa-3x mb-3"></i>
            <p class="mb-0">{{ $total ? __('Nenhum documento corresponde aos filtros.') : __('Ainda não há documentos.') }}</p>
            @if($filters)<a href="{{ route('backoffice.documents.index') }}">{{ __('Limpar filtros') }}</a>@endif
        </div>
    </div>
@else
    <form method="GET" action="{{ route('backoffice.documents.zip') }}" id="doc-zip-form"></form>
    <div class="doc-toolbar">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="doc-check-all">
            <label class="form-check-label" for="doc-check-all">{{ __('Selecionar todos') }} ({{ $documents->count() }})</label>
        </div>
        <div>
            <button type="submit" form="doc-zip-form" class="btn btn-outline-secondary btn-sm" id="doc-zip-selected" disabled>
                <i class="fa fa-file-archive"></i> {{ __('Descarregar selecionados') }} <span id="doc-selected-count"></span>
            </button>
            @if(request('modelo'))
                <a href="{{ route('backoffice.documents.zip', ['modelo' => request('modelo')]) }}" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-file-archive"></i> {{ __('Exportar tudo de') }} {{ request('modelo') === 'geral' ? __('Geral') : request('modelo') }} (ZIP)
                </a>
            @endif
        </div>
    </div>

    <div class="doc-grid">
        @foreach($documents as $doc)
            <div class="doc-card" style="--doc-color: {{ $doc->type['color'] }}">
                <label class="doc-check" title="{{ __('Selecionar') }}">
                    <input type="checkbox" name="ids[]" value="{{ $doc->id }}" form="doc-zip-form" class="doc-select">
                </label>
                <div class="doc-body">
                    <a href="{{ route('backoffice.documents.view', $doc->id) }}" target="_blank" class="doc-icon"><i class="fas fa-file-pdf"></i></a>
                    <div class="flex-grow-1" style="min-width: 0; padding-right: 1.2rem">
                        <span class="doc-type"><i class="fas {{ $doc->type['icon'] }}"></i> {{ $doc->type['label'] }}</span>
                        <a href="{{ route('backoffice.documents.view', $doc->id) }}" target="_blank" class="doc-title">{{ $doc->titulo }}</a>
                        <div class="doc-models">
                            @forelse($doc->modelos ?? [] as $model)
                                <span class="doc-model">{{ $model }}</span>
                            @empty
                                <span class="doc-model">{{ __('Geral') }}</span>
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
                    <a href="{{ route('backoffice.documents.view', $doc->id) }}" target="_blank" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i> {{ __('Abrir') }}</a>
                    <a href="{{ route('backoffice.documents.download', $doc->id) }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-download"></i> {{ __('Descarregar') }}</a>
                    @if($canManage)
                        <a href="{{ route('backoffice.documents.edit', $doc->id) }}" class="btn btn-sm btn-link text-secondary ml-auto px-1" title="{{ __('Editar') }}"><i class="fa fa-edit"></i></a>
                        <form method="POST" action="{{ route('backoffice.documents.destroy', $doc->id) }}" class="d-inline" onsubmit="return confirm('Apagar este documento?')">
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Acessos: quem gere também vê
        document.querySelectorAll('.doc-perm-manage').forEach(function (m) {
            m.addEventListener('change', function () {
                var view = document.querySelector('.doc-perm-view[data-funcao="' + m.dataset.funcao + '"]');
                if (m.checked) view.checked = true;
            });
        });
        document.querySelectorAll('.doc-perm-view').forEach(function (v) {
            v.addEventListener('change', function () {
                var manage = document.querySelector('.doc-perm-manage[data-funcao="' + v.dataset.funcao + '"]');
                if (!v.checked) manage.checked = false;
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

        // Envio: arrastar e largar + título por ficheiro
        var input = document.getElementById('doc-files');
        if (!input) return;
        var drop = document.getElementById('doc-drop');
        var list = document.getElementById('doc-file-list');
        var submit = document.getElementById('doc-submit');
        var maxBytes = {{ config('documents.max_size_kb') }} * 1024;
        var files = [];

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
                    '<input type="text" name="titulos[]" class="form-control form-control-sm" value="' + esc(f.title) + '" title="Título">' +
                    '<small class="' + (tooBig ? 'text-danger font-weight-bold' : '') + '">' + size(f.file.size) + (tooBig ? ' (demasiado grande)' : '') + '</small>' +
                    '<button type="button" class="btn btn-sm btn-link text-danger p-0" title="Remover"><i class="fa fa-times"></i></button>';
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

        input.addEventListener('change', function () { var picked = Array.prototype.slice.call(input.files); add(picked); });
        ['dragenter', 'dragover'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.add('is-over'); }); });
        ['dragleave', 'drop'].forEach(function (ev) { drop.addEventListener(ev, function (e) { e.preventDefault(); drop.classList.remove('is-over'); }); });
        drop.addEventListener('drop', function (e) { add(e.dataTransfer.files); });
    });
</script>
@endpush
