@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Importar artigos') }}</title>
@endsection

@push('styles')
<style>
    .imp-passo { display: flex; gap: .75rem; align-items: flex-start; }
    .imp-passo + .imp-passo { margin-top: .9rem; }
    .imp-num { flex: 0 0 1.7rem; height: 1.7rem; border-radius: 50%; background: #e0f2fe; color: #0369a1;
               display: grid; place-items: center; font-weight: 800; font-size: .85rem; }
    .imp-passo h6 { margin: 0 0 .15rem; font-size: .92rem; font-weight: 700; color: #0f172a; }
    .imp-passo p { margin: 0; font-size: .86rem; color: #64748b; }
    .imp-cols { font-family: ui-monospace, Menlo, monospace; font-size: .8rem; background: #f8fafc;
                border: 1px solid #e2e8f0; border-radius: 8px; padding: .5rem .7rem; color: #334155; margin-top: .4rem; }

    .imp-resumo { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: .75rem; }
    .imp-caixa { border-radius: 12px; padding: .8rem 1rem; background: var(--bg); color: var(--fg); }
    .imp-caixa .num { font-size: 1.8rem; font-weight: 800; line-height: 1; }
    .imp-caixa .lbl { font-size: .78rem; font-weight: 600; margin-top: .25rem; }
    .imp-lista { max-height: 260px; overflow-y: auto; font-size: .84rem; }
    .imp-lista div { padding: .25rem .5rem; border-bottom: 1px solid #f1f5f9; }
    .imp-lista .ln { font-family: ui-monospace, Menlo, monospace; color: #94a3b8; margin-right: .4rem; }
</style>
@endpush

@section('content')
<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: .75rem">
            <div>
                <h4 class="mb-1"><i class="fas fa-file-import text-primary"></i> {{ __('Importar artigos') }}</h4>
                <p class="text-muted small mb-0">{{ __('Carregue a folha do armazém. Os artigos são encontrados pelo código.') }}</p>
            </div>
            <a href="{{ route('backoffice.stock.items.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> {{ __('Voltar aos artigos') }}
            </a>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $erro)<li>{{ $erro }}</li>@endforeach</ul></div>
@endif

@if(!$previsao)
    <div class="row">
        <div class="col-lg-7 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-upload text-success"></i> {{ __('Escolher o ficheiro') }}</h5>
                    <form method="POST" action="{{ route('backoffice.stock.import.preview') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <input type="file" name="ficheiro" class="form-control-file" accept=".csv,.txt,.xlsx,.xls" required>
                            <small class="form-text text-muted">{{ __('CSV ou Excel (xlsx, xls), até 10 MB.') }}</small>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-eye"></i> {{ __('Ver o que vai acontecer') }}</button>
                        <a href="{{ route('backoffice.stock.import.template') }}" class="btn btn-outline-secondary">
                            <i class="fa fa-download"></i> {{ __('Descarregar modelo') }}
                        </a>
                    </form>
                    <p class="text-muted small mb-0 mt-3">
                        <i class="fa fa-info-circle"></i>
                        {{ __('Nada é gravado agora. Primeiro mostro-lhe o que muda, e só aplica se confirmar.') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="col-lg-5 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-list-ol text-secondary"></i> {{ __('Como funciona') }}</h5>
                    <div class="imp-passo">
                        <span class="imp-num">1</span>
                        <div>
                            <h6>{{ __('Encontra pelo código') }}</h6>
                            <p>{{ __('Código que não existe cria o artigo. Código conhecido actualiza-o.') }}</p>
                        </div>
                    </div>
                    <div class="imp-passo">
                        <span class="imp-num">2</span>
                        <div>
                            <h6>{{ __('A quantidade substitui') }}</h6>
                            <p>{{ __('Se a folha diz 7, fica 7. Carregar o mesmo ficheiro outra vez não duplica nada.') }}</p>
                        </div>
                    </div>
                    <div class="imp-passo">
                        <span class="imp-num">3</span>
                        <div>
                            <h6>{{ __('O que não vem na folha não se toca') }}</h6>
                            <p>{{ __('Os artigos que não estiverem no ficheiro ficam como estão.') }}</p>
                        </div>
                    </div>
                    <div class="imp-cols">
                        codigo ; nome ; quantidade ; minimo ; localizacao
                    </div>
                    <small class="form-text text-muted">{{ __('Só o código e o nome são obrigatórios. Aceita vírgula ou ponto e vírgula.') }}</small>
                </div>
            </div>
        </div>
    </div>
@else
    {{-- Pré-visualização --}}
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">
                <i class="fa fa-eye text-primary"></i> {{ __('O que vai acontecer') }}
                <small class="text-muted">· {{ $previsao['nome_ficheiro'] }}</small>
            </h5>

            <div class="imp-resumo mb-3">
                <div class="imp-caixa" style="--bg: #e0f2fe; --fg: #0369a1">
                    <div class="num">{{ $previsao['novos'] }}</div><div class="lbl">{{ __('artigos novos') }}</div>
                </div>
                <div class="imp-caixa" style="--bg: #dcfce7; --fg: #15803d">
                    <div class="num">{{ $previsao['atualizados'] }}</div><div class="lbl">{{ __('actualizados') }}</div>
                </div>
                <div class="imp-caixa" style="--bg: {{ $previsao['problemas'] ? '#fef2f2' : '#f1f5f9' }}; --fg: {{ $previsao['problemas'] ? '#b91c1c' : '#64748b' }}">
                    <div class="num">{{ count($previsao['problemas']) }}</div><div class="lbl">{{ __('linhas com problemas') }}</div>
                </div>
                <div class="imp-caixa" style="--bg: #f1f5f9; --fg: #475569">
                    <div class="num">{{ $previsao['total'] }}</div><div class="lbl">{{ __('linhas lidas') }}</div>
                </div>
            </div>

            @if($previsao['problemas'])
                <h6 class="text-uppercase small font-weight-bold text-danger">{{ __('Estas linhas vão ser ignoradas') }}</h6>
                <div class="imp-lista mb-3">
                    @foreach($previsao['problemas'] as $p)
                        <div><span class="ln">{{ __('linha') }} {{ $p['linha'] }}</span> {{ $p['erro'] }}</div>
                    @endforeach
                </div>
            @endif

            @if($previsao['nomes_alterados'])
                <h6 class="text-uppercase small font-weight-bold text-muted">{{ __('Nomes que mudam') }}</h6>
                <div class="imp-lista mb-3">
                    @foreach($previsao['nomes_alterados'] as $n)
                        <div><span class="ln">{{ $n['codigo'] }}</span> {{ $n['de'] }} <i class="fa fa-arrow-right text-muted mx-1"></i> <strong>{{ $n['para'] }}</strong></div>
                    @endforeach
                </div>
            @endif

            @if(count($previsao['validas']))
                <form method="POST" action="{{ route('backoffice.stock.import.apply') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-primary">
                        <i class="fa fa-check"></i>
                        {{ __('Aplicar a :total linhas', ['total' => count($previsao['validas'])]) }}
                    </button>
                </form>
            @else
                <p class="text-danger mb-2"><i class="fa fa-exclamation-triangle"></i> {{ __('Não há nenhuma linha aproveitável neste ficheiro.') }}</p>
            @endif
            <form method="POST" action="{{ route('backoffice.stock.import.cancel') }}" class="d-inline">
                @csrf
                <button class="btn btn-outline-secondary">{{ __('Cancelar') }}</button>
            </form>
        </div>
    </div>
@endif
@endsection
