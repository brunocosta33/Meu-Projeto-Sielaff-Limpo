@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Editar Documento') }}</title>
@endsection

@push('styles')
<style>
    .type-tiles { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: .6rem; }
    .type-tile { margin: 0; cursor: pointer; }
    .type-tile input { display: none; }
    .type-tile .box { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .45rem; text-align: center; height: 88px; padding: .5rem;
                      border: 2px solid #e3e9f1; border-radius: 12px; background: #fff; font-size: .8rem; font-weight: 700; color: #415166; transition: all .12s; }
    .type-tile .box i { font-size: 1.3rem; color: var(--c); }
    .type-tile:hover .box { border-color: var(--c); }
    .type-tile input:checked + .box { border-color: var(--c); background: color-mix(in srgb, var(--c) 10%, #fff); color: #132238; box-shadow: 0 0 0 3px color-mix(in srgb, var(--c) 25%, transparent); }
    .model-groups { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: .7rem; }
    .model-group { border: 1px solid #e3e9f1; border-radius: 12px; padding: .65rem .75rem; background: #fafbfd; }
    .model-group .gtitle { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #546e7a; margin-bottom: .45rem; }
    .model-pick { display: flex; align-items: center; gap: .5rem; margin: 0 0 .3rem; padding: .35rem .5rem; border-radius: 8px; cursor: pointer; font-size: .86rem; font-weight: 600; color: #263238; }
    .model-pick:hover { background: #eef4fc; }
    .model-pick input { width: 17px; height: 17px; accent-color: #1f6fd1; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-1">{{ __('Editar documento') }}</h5>
                <p class="text-muted small mb-4">
                    <a href="{{ route('backoffice.documents.view', $document->id) }}" target="_blank"><i class="fas fa-file-pdf text-danger"></i> {{ $document->file_name }}</a>
                    · {{ $document->size_label }}
                    · {{ __('Adicionado por') }} {{ $document->uploader->name ?? '—' }} {{ __('em') }} {{ $document->created_at->format('d/m/Y') }}
                </p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('backoffice.documents.update', $document->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Título') }}</label>
                        <input type="text" name="titulo" class="form-control" value="{{ old('titulo', $document->titulo) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Tipo de documento') }}</label>
                        <div class="type-tiles">
                            @foreach(config('documents.types') as $key => $type)
                                <label class="type-tile" style="--c: {{ $type['color'] }}">
                                    <input type="radio" name="tipo" value="{{ $key }}" @checked(old('tipo', $document->tipo) === $key)>
                                    <span class="box"><i class="fas {{ $type['icon'] }}"></i>{{ $type['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Modelo(s)') }} <span class="font-weight-normal text-muted small">— {{ __('nenhum marcado = Geral (todos os modelos)') }}</span></label>
                        <div class="model-groups mb-2">
                            @foreach($modelGroups as $family => $list)
                                <div class="model-group">
                                    <div class="gtitle">{{ $family }}</div>
                                    @foreach($list as $model)
                                        <label class="model-pick"><input type="checkbox" name="modelos[]" value="{{ $model }}" @checked(in_array($model, old('modelos', $document->modelos ?? [])))> {{ $model }}</label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                        <input type="text" name="novo_modelo" class="form-control form-control-sm" value="{{ old('novo_modelo') }}" placeholder="{{ __('Outro modelo (separe vários por vírgula)') }}">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="font-weight-bold">{{ __('Idioma') }}</label>
                            <select name="idioma" class="form-control">
                                <option value="">—</option>
                                @foreach(config('documents.languages') as $key => $label)
                                    <option value="{{ $key }}" @selected(old('idioma', $document->idioma) === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-8">
                            <label class="font-weight-bold">{{ __('Substituir ficheiro (opcional)') }}</label>
                            <input type="file" name="file" class="form-control-file" accept="application/pdf,.pdf">
                            <small class="form-text text-muted">{{ __('Por exemplo, uma versão mais recente do manual.') }}</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Descrição') }}</label>
                        <textarea name="descricao" class="form-control" rows="3">{{ old('descricao', $document->descricao) }}</textarea>
                    </div>

                    <a class="btn btn-outline-secondary" href="{{ route('backoffice.documents.index') }}"><i class="fa fa-arrow-left"></i> {{ __('Voltar') }}</a>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> {{ __('Gravar') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
