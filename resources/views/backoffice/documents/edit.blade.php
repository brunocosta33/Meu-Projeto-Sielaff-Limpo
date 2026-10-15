@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Editar Documento') }}</title>
@endsection

@push('styles')
<style>
    .doc-pick { display: flex; flex-wrap: wrap; gap: .4rem; }
    .doc-pick label { margin: 0; }
    .doc-pick input { display: none; }
    .doc-pick span { display: inline-block; padding: .3rem .75rem; border-radius: 999px; border: 1px solid #dee2e6; cursor: pointer; font-size: .85rem; background: #fff; }
    .doc-pick input:checked + span { background: var(--chip-color, #1976d2); border-color: var(--chip-color, #1976d2); color: #fff; }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-1">{{ __('Editar documento') }}</h5>
                <p class="text-muted small mb-4">
                    <a href="{{ route('backoffice.documents.read', $document->id) }}"><i class="fas fa-file-pdf text-danger"></i> {{ $document->file_name }}</a>
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
                        <div class="doc-pick">
                            @foreach(config('documents.types') as $key => $type)
                                <label style="--chip-color: {{ $type['color'] }}">
                                    <input type="radio" name="tipo" value="{{ $key }}" @checked(old('tipo', $document->tipo) === $key)>
                                    <span><i class="fas {{ $type['icon'] }}"></i> {{ $type['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">{{ __('Modelo(s)') }} <span class="font-weight-normal text-muted small">— {{ __('nenhum = Geral') }}</span></label>
                        <div class="doc-pick mb-2">
                            @foreach($models as $model)
                                <label style="--chip-color: #37474f">
                                    <input type="checkbox" name="modelos[]" value="{{ $model }}" @checked(in_array($model, old('modelos', $document->modelos ?? [])))>
                                    <span>{{ $model }}</span>
                                </label>
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
