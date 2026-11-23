@extends('layouts.backoffice_master')

@section('head-meta')
    <title>{{ config('app.name') }} - {{ __('Semana dos Técnicos') }}</title>
@endsection

@php(\Carbon\Carbon::setLocale(app()->getLocale()))
@php($cores = ['alta' => '#b3261e', 'media' => '#d39d00', 'baixa' => '#208a47'])

@push('styles')
<style>
    .sm-faixa { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .6rem;
                padding: .8rem 1.1rem; color: #fff; border-radius: 14px 14px 0 0;
                background: linear-gradient(115deg, #0b5ed7, #1b9aaa 55%, #20a464); }
    .sm-faixa .t { font-size: 1.1rem; font-weight: 800; }
    .sm-faixa .sub { display: block; font-size: .76rem; font-weight: 500; opacity: .85; }
    .sm-nav { display: flex; align-items: center; gap: .3rem; }
    .sm-nav .data { min-width: 14rem; text-align: center; font-weight: 700; }
    .sm-seta { border: 1px solid rgba(255, 255, 255, .35); background: rgba(255, 255, 255, .12); color: #fff;
               border-radius: 8px; padding: .1rem .55rem; }
    .sm-seta:hover { background: #fff; color: #0b5ed7; }
    .sm-hoje { border: 0; background: #fff; color: #0b5ed7; font-weight: 800; font-size: .76rem;
               border-radius: 8px; padding: .22rem .7rem; margin-left: .3rem; }

    /* O quadro: uma coluna para o técnico e uma por dia */
    .sm-quadro { display: grid; grid-template-columns: 12rem repeat({{ count($dias) }}, minmax(11.5rem, 1fr)); gap: .45rem; }
    .sm-cab { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #64748b;
              text-align: center; padding: .2rem 0 .1rem; }
    .sm-cab.hoje { color: #0b5ed7; }
    .sm-cab .d { display: block; font-size: 1.05rem; font-weight: 800; color: #132238; }
    .sm-cab.hoje .d { color: #0b5ed7; }

    .sm-tecnico { display: flex; align-items: center; gap: .55rem; background: #fff; border: 1px solid #e3e9f1;
                  border-left: 5px solid var(--c, #0b5ed7); border-radius: 12px; padding: .5rem .7rem; }
    .sm-tecnico .bola { width: 34px; height: 34px; flex: 0 0 34px; border-radius: 50%; display: grid; place-items: center;
                        background: var(--c, #0b5ed7); color: #fff; font-size: .72rem; font-weight: 800; }
    .sm-tecnico .nome { font-weight: 700; color: #132238; line-height: 1.15; font-size: .92rem; }
    .sm-tecnico .n { font-size: .74rem; color: #6c7a89; }

    .sm-celula { min-height: 5.4rem; background: #f7f9fc; border: 1px dashed #dde5ef; border-radius: 12px; padding: .35rem;
                 transition: background .12s, border-color .12s; }
    .sm-celula.fds { background: #f3f4f6; }
    .sm-celula.hoje { border-color: #9dc2f5; background: #f2f8ff; }
    .sm-celula.alvo { border-color: #0b5ed7; border-style: solid; background: #e7f0ff; }

    .sm-pedido { display: block; position: relative; background: #fff; border: 1px solid #e3e9f1;
                 border-left: 4px solid var(--p, #94a3b8); border-radius: 9px; padding: .3rem .45rem;
                 margin-bottom: .3rem; cursor: grab; text-decoration: none !important;
                 box-shadow: 0 1px 2px rgba(19, 34, 56, .06); }
    .sm-pedido:hover { box-shadow: 0 4px 10px rgba(19, 34, 56, .12); }
    .sm-pedido .topo { display: flex; align-items: center; gap: .25rem; margin-bottom: .1rem; padding-right: .8rem; }
    .sm-pedido .n { font-size: .68rem; font-weight: 800; color: #94a3b8; margin-right: auto; }
    .sm-chip { font-size: .6rem; font-weight: 800; text-transform: uppercase; letter-spacing: .03em;
               border-radius: 999px; padding: .02rem .4rem; color: #fff; background: var(--c, #64748b); white-space: nowrap; }
    .sm-chip.clara { background: #fff; color: var(--c); border: 1px solid currentColor; }
    .sm-marca { font-size: .6rem; font-weight: 800; text-transform: uppercase; letter-spacing: .03em;
                border-radius: 4px; padding: .02rem .3rem; color: var(--c); background: var(--bg); }
    .sm-pedido .loja { display: block; font-size: .78rem; font-weight: 700; color: #132238; line-height: 1.25;
                       overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .sm-pedido .tipo { display: block; font-size: .7rem; color: #6c7a89; line-height: 1.2; padding-right: .8rem; }
    .sm-pedido .peca { color: #b26a00; font-weight: 700; }
    .sm-pedido .tirar { position: absolute; top: .1rem; right: .15rem; border: 0; background: none; color: #cbd5e1;
                        font-size: .7rem; line-height: 1; padding: .1rem; }
    .sm-pedido .tirar:hover { color: #b3261e; }
    .sm-pedido .mover { position: absolute; bottom: .1rem; right: .15rem; border: 0; background: none; color: #cbd5e1;
                        font-size: .7rem; padding: .1rem; }
    .sm-pedido .mover:hover { color: #0b5ed7; }

    .sm-fila { display: flex; flex-wrap: wrap; gap: .4rem; }
    .sm-fila .sm-pedido { width: 16.5rem; margin: 0; }
    .sm-vazio { color: #9aa7b6; font-size: .84rem; padding: .6rem; }
    /* Os por agendar vêm arrumados pelo técnico que já têm atribuído */
    .sm-grupo { display: flex; align-items: center; gap: .4rem; font-size: .82rem; font-weight: 800; color: #132238;
                border-top: 1px solid #eef2f7; padding: .6rem 0 .45rem; }
    .sm-grupo:first-of-type { border-top: 0; padding-top: 0; }
    .sm-grupo .bola { width: 24px; height: 24px; border-radius: 50%; display: grid; place-items: center;
                      background: var(--c); color: #fff; font-size: .64rem; }
    .sm-grupo .n { background: #eef2f7; color: #475569; border-radius: 999px; padding: 0 .5rem; font-size: .74rem; }
    .sm-ajuda { font-size: .8rem; color: #6c7a89; }
</style>
@endpush

@section('content')

<div class="card mb-3" style="border: 0; overflow: hidden; border-radius: 14px">
    <div class="sm-faixa">
        <div>
            <div class="t"><i class="fas fa-calendar-week"></i> {{ __('Semana dos Técnicos') }}</div>
            <span class="sub">{{ __('Arraste um pedido para o dia do técnico. Clique no pedido para o abrir.') }}</span>
        </div>
        <div class="sm-nav">
            <a class="sm-seta" href="{{ route('backoffice.technical_requests.week', ['semana' => $inicio->copy()->subWeek()->toDateString()]) }}"><i class="fa fa-chevron-left"></i></a>
            <span class="data">{{ $inicio->format('d/m') }} — {{ $fim->format('d/m/Y') }}</span>
            <a class="sm-seta" href="{{ route('backoffice.technical_requests.week', ['semana' => $inicio->copy()->addWeek()->toDateString()]) }}"><i class="fa fa-chevron-right"></i></a>
            @unless($inicio->isSameWeek(now()))
                <a class="sm-hoje" href="{{ route('backoffice.technical_requests.week') }}">{{ __('Esta semana') }}</a>
            @endunless
        </div>
    </div>

    <div class="card-body">
        <div class="sm-quadro">
            <div></div>
            @foreach($dias as $dia)
                <div class="sm-cab {{ $dia->isToday() ? 'hoje' : '' }}">
                    {{ $dia->translatedFormat('D') }}
                    <span class="d">{{ $dia->format('d/m') }}</span>
                </div>
            @endforeach

            @forelse($tecnicos as $tecnico)
                @php($cor = \App\Services\HotlineRotation::PIQUETE_COLORS[$loop->index % count(\App\Services\HotlineRotation::PIQUETE_COLORS)])
                @php($totalSemana = $dias->sum(fn ($d) => count($celulas[$tecnico->id . '_' . $d->toDateString()] ?? [])))
                <div class="sm-tecnico" style="--c: {{ $cor }}">
                    <span class="bola">{{ \App\Services\HotlineChecklist::iniciais($tecnico->name) }}</span>
                    <div>
                        <div class="nome">{{ $tecnico->name }}</div>
                        <div class="n">{{ trans_choice('{0} sem pedidos|{1} :count pedido|[2,*] :count pedidos', $totalSemana, ['count' => $totalSemana]) }}</div>
                    </div>
                </div>

                @foreach($dias as $dia)
                    <div class="sm-celula {{ $dia->isToday() ? 'hoje' : '' }} {{ $dia->isWeekend() ? 'fds' : '' }}"
                         data-tec="{{ $tecnico->id }}" data-dia="{{ $dia->toDateString() }}">
                        @foreach($celulas[$tecnico->id . '_' . $dia->toDateString()] ?? [] as $p)
                            @include('backoffice.technical_requests.partials.week_card', ['p' => $p, 'cores' => $cores])
                        @endforeach
                    </div>
                @endforeach
            @empty
                <div class="sm-vazio" style="grid-column: 1 / -1">{{ __('Ainda não há técnicos registados.') }}</div>
            @endforelse
        </div>
    </div>
</div>

@if($semAgendar->isNotEmpty())
    <div class="card mb-3">
        <div class="card-body">
            <h6 class="text-uppercase text-muted mb-2" style="font-size: .75rem; letter-spacing: .06em">
                <i class="fa fa-user-slash"></i> {{ __('Marcados para esta semana, mas sem técnico') }} ({{ $semAgendar->count() }})
            </h6>
            <div class="sm-fila">
                @foreach($semAgendar as $p)
                    @include('backoffice.technical_requests.partials.week_card', ['p' => $p, 'cores' => $cores])
                @endforeach
            </div>
        </div>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <h6 class="text-uppercase text-muted mb-2" style="font-size: .75rem; letter-spacing: .06em">
            <i class="fa fa-inbox"></i> {{ __('Por agendar') }} ({{ $totalPorAgendar }})
        </h6>
        <p class="sm-ajuda">{{ __('Pedidos em aberto que ainda não têm dia, pelo técnico que já lhes foi atribuído. Arraste um para a semana, ou use o lápis para escolher o técnico e o dia.') }}</p>

        @forelse($porAgendar as $tecnicoId => $grupo)
            @php($dono = $tecnicos->firstWhere('id', $tecnicoId))
            @php($cor = $dono ? \App\Services\HotlineRotation::PIQUETE_COLORS[$tecnicos->search(fn ($t) => $t->id === $tecnicoId) % count(\App\Services\HotlineRotation::PIQUETE_COLORS)] : '#94a3b8')
            <div class="sm-grupo" style="--c: {{ $cor }}">
                <span class="bola">{{ $dono ? \App\Services\HotlineChecklist::iniciais($dono->name) : '?' }}</span>
                {{ $dono->name ?? __('Por atribuir') }}
                <span class="n">{{ $grupo->count() }}</span>
            </div>
            <div class="sm-fila mb-3">
                @foreach($grupo as $p)
                    @include('backoffice.technical_requests.partials.week_card', ['p' => $p, 'cores' => $cores])
                @endforeach
            </div>
        @empty
            <div class="sm-vazio">{{ __('Não há pedidos por agendar. Bom trabalho.') }}</div>
        @endforelse
    </div>
</div>

{{-- Form escondido: é o que leva o pedido para o dia, tanto no arrastar como no modal --}}
<form method="POST" id="sm-form" class="d-none">
    @csrf
    <input type="hidden" name="dia" id="sm-dia">
    <input type="hidden" name="technician_id" id="sm-tec">
</form>

{{-- Alternativa ao arrastar, para o telemóvel --}}
<div class="modal fade" id="sm-mover" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-calendar-day text-primary"></i> {{ __('Pôr na semana') }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="font-weight-bold mb-3" id="sm-mover-titulo"></p>
                <div class="form-group">
                    <label class="small mb-1">{{ __('Técnico') }}</label>
                    <select class="form-control" id="sm-mover-tec">
                        <option value="">{{ __('Sem técnico') }}</option>
                        @foreach($tecnicos as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-0">
                    <label class="small mb-1">{{ __('Dia') }}</label>
                    <select class="form-control" id="sm-mover-dia">
                        <option value="">{{ __('Tirar da semana') }}</option>
                        @foreach($dias as $dia)
                            <option value="{{ $dia->toDateString() }}">{{ \Illuminate\Support\Str::ucfirst($dia->translatedFormat('dddd, d/m')) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ __('Cancelar') }}</button>
                <button type="button" class="btn btn-primary" id="sm-mover-ok"><i class="fa fa-check"></i> {{ __('Guardar') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var base = {{ \Illuminate\Support\Js::from(url('backoffice/technical-requests')) }};
        var form = document.getElementById('sm-form');

        var enviar = function (id, tecnico, dia) {
            form.action = base + '/' + id + '/agendar';
            document.getElementById('sm-tec').value = tecnico || '';
            document.getElementById('sm-dia').value = dia || '';
            form.submit();
        };

        // Arrastar o pedido para a célula do técnico/dia
        document.querySelectorAll('.sm-pedido').forEach(function (card) {
            card.addEventListener('dragstart', function (ev) {
                ev.dataTransfer.setData('text/plain', card.dataset.id);
                ev.dataTransfer.effectAllowed = 'move';
            });
        });

        document.querySelectorAll('.sm-celula').forEach(function (celula) {
            celula.addEventListener('dragover', function (ev) { ev.preventDefault(); celula.classList.add('alvo'); });
            celula.addEventListener('dragleave', function () { celula.classList.remove('alvo'); });
            celula.addEventListener('drop', function (ev) {
                ev.preventDefault();
                celula.classList.remove('alvo');
                var id = ev.dataTransfer.getData('text/plain');
                if (id) enviar(id, celula.dataset.tec, celula.dataset.dia);
            });
        });

        // Tirar da semana
        document.querySelectorAll('.sm-pedido .tirar').forEach(function (b) {
            b.addEventListener('click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                enviar(b.closest('.sm-pedido').dataset.id, '', '');
            });
        });

        // Escolher à mão (telemóvel)
        var atual = null;
        document.querySelectorAll('.sm-pedido .mover').forEach(function (b) {
            b.addEventListener('click', function (ev) {
                ev.preventDefault();
                ev.stopPropagation();
                var card = b.closest('.sm-pedido');
                atual = card.dataset.id;
                document.getElementById('sm-mover-titulo').textContent = card.dataset.titulo || '';
                document.getElementById('sm-mover-tec').value = card.dataset.tec || '';
                document.getElementById('sm-mover-dia').value = card.dataset.dia || '';
                if (window.jQuery) $('#sm-mover').modal('show');
            });
        });

        var ok = document.getElementById('sm-mover-ok');
        if (ok) {
            ok.addEventListener('click', function () {
                if (atual) enviar(atual, document.getElementById('sm-mover-tec').value, document.getElementById('sm-mover-dia').value);
            });
        }
    });
</script>
@endpush
