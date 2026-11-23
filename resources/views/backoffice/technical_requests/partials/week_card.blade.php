{{-- Um pedido no quadro da semana. Arrasta-se; clicar abre o pedido. --}}
@php($cliente = $p->store?->clienteConfig())
@php($zonas = ['norte' => '#0f766e', 'centro' => '#b45309', 'sul' => '#be185d'])
<a class="sm-pedido" draggable="{{ ($leitura ?? false) ? 'false' : 'true' }}"
   href="{{ route('backoffice.technical_requests.show', $p->id) }}"
   data-id="{{ $p->id }}"
   data-tec="{{ $p->assigned_technician_id }}"
   data-dia="{{ $p->data_agendamento?->toDateString() }}"
   data-titulo="#{{ $p->id }} — {{ $p->store->nome_loja ?? __('Sem loja') }}"
   style="--p: {{ $cores[$p->prioridade] ?? '#94a3b8' }}"
   title="{{ $p->descricao_problema }}">

    <span class="topo">
        <span class="n">#{{ $p->id }}</span>
        @if($p->zona)
            <span class="sm-chip clara" style="--c: {{ $zonas[$p->zona] ?? '#64748b' }}">{{ __(\App\Http\Controllers\TechnicalRequestController::ZONES[$p->zona] ?? $p->zona) }}</span>
        @endif
        {{-- Só a alta vem cheia de cor: é a que tem de saltar à vista --}}
        <span class="sm-chip {{ $p->prioridade === 'alta' ? '' : 'clara' }}" style="--c: {{ $cores[$p->prioridade] ?? '#94a3b8' }}">
            {{ __(\App\Http\Controllers\TechnicalRequestController::PRIORITIES[$p->prioridade] ?? $p->prioridade) }}
        </span>
    </span>

    <span class="loja">
        @if($cliente)
            <span class="sm-marca" style="--c: {{ $cliente['color'] }}; --bg: {{ $cliente['bg'] }}">{{ $cliente['label'] }}</span>
        @endif
        {{ $p->store->codigo_loja ?? '' }} {{ $p->store->nome_loja ?? __('Sem loja') }}
    </span>

    <span class="tipo">
        {{ __(\App\Http\Controllers\TechnicalRequestController::SERVICE_TYPES[$p->tipo_servico] ?? ($p->tipo_servico ?: '—')) }}
        @if($p->estado === 'aguarda_peca')<span class="peca">· {{ __('Aguarda Peça') }}</span>@endif
    </span>

    @unless($leitura ?? false)
        @if($p->data_agendamento)
            <button type="button" class="tirar" title="{{ __('Tirar da semana') }}"><i class="fa fa-times"></i></button>
        @endif
        <button type="button" class="mover" title="{{ __('Escolher técnico e dia') }}"><i class="fa fa-pen"></i></button>
    @endunless
</a>
