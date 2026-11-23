{{-- Um pedido no quadro da semana. Arrasta-se; clicar abre o pedido. --}}
<a class="sm-pedido" draggable="true"
   href="{{ route('backoffice.technical_requests.show', $p->id) }}"
   data-id="{{ $p->id }}"
   data-tec="{{ $p->assigned_technician_id }}"
   data-dia="{{ $p->data_agendamento?->toDateString() }}"
   data-titulo="#{{ $p->id }} — {{ $p->store->nome_loja ?? __('Sem loja') }}"
   style="--p: {{ $cores[$p->prioridade] ?? '#94a3b8' }}"
   title="{{ $p->descricao_problema }}">
    <span class="n">#{{ $p->id }}</span>
    <span class="loja">{{ $p->store->codigo_loja ?? '' }} {{ $p->store->nome_loja ?? __('Sem loja') }}</span>
    <span class="tipo">
        {{ __(\App\Http\Controllers\TechnicalRequestController::SERVICE_TYPES[$p->tipo_servico] ?? ($p->tipo_servico ?: '—')) }}
        @if($p->estado === 'aguarda_peca')<span class="peca">· {{ __('Aguarda Peça') }}</span>@endif
    </span>
    @if($p->data_agendamento)
        <button type="button" class="tirar" title="{{ __('Tirar da semana') }}"><i class="fa fa-times"></i></button>
    @endif
    <button type="button" class="mover" title="{{ __('Escolher técnico e dia') }}"><i class="fa fa-pen"></i></button>
</a>
