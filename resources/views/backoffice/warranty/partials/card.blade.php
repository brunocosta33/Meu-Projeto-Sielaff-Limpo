{{-- Um processo no quadro. Clicar abre-o. --}}
@php($marca = config('maintenance.clients.' . $case->cliente))
@php($tipo = $case->typeConfig())
@php($alerta = $case->isOpen() ? $case->waitingAlert() : null)
@php($dias = $case->daysInPhase())

<a href="{{ route('backoffice.warranty.show', $case->id) }}" class="fg-cartao {{ $alerta }}" style="--c: {{ $cor }}">
    <div class="fg-topo">
        @if($marca)
            <span class="fg-marca" style="--grad: {{ $marca['gradiente'] }}; --txt: {{ $marca['texto'] }}">{{ $marca['label'] }}</span>
        @endif
        <span class="fg-tipo" style="--tc: {{ $tipo['color'] }}; --tb: {{ $tipo['bg'] }}">
            <i class="fas {{ $tipo['icon'] }}"></i> {{ __($tipo['curto']) }}
        </span>
    </div>

    <div class="fg-loja"><span class="cod">{{ $case->store->codigo_loja ?? '' }}</span>{{ $case->store->nome_loja ?? __('Sem loja') }}</div>

    @if($case->machine?->serial_number)
        <span class="fg-serie"><i class="fa fa-hashtag"></i> {{ $case->machine->serial_number }}</span>
    @endif

    @if($case->descricao)
        <div class="fg-avaria">{{ $case->descricao }}</div>
    @endif

    <div class="fg-passos" title="{{ collect($case->blocks())->map(fn ($b) => __($b['curto']))->implode(' → ') }}">
        @php($proximo = $case->nextStep())
        @foreach($case->blocks() as $chave => $bloco)
            <span class="{{ $case->blockState($chave, $proximo) }}" style="--bc: {{ $bloco['color'] }}" title="{{ __($bloco['label']) }}"></span>
        @endforeach
    </div>

    @if($mostrarProximo && $case->todoLabel())
        <div class="fg-proximo">▸ {{ $case->todoLabel() }}</div>
    @endif

    @if($case->isOpen())
        <div class="fg-rodape">
            <span class="fg-dias">
                <i class="far fa-clock"></i>
                {{ trans_choice('{0} hoje|{1} há :n dia|[2,*] há :n dias', $dias, ['n' => $dias]) }}
                @if($alerta === 'late') · {{ __('passou do prazo') }} @endif
            </span>
            @if(($case->photos_count ?? 0) > 0)
                <span class="fotos"><i class="fas fa-camera"></i> {{ $case->photos_count }}</span>
            @endif
        </div>
    @else
        <div class="fg-fechado-estado" style="color: {{ $cor }}">
            <i class="fas {{ $case->phase()['icon'] }}"></i> {{ __($case->phase()['label']) }}
            @if($case->fechado_em) · {{ \Carbon\Carbon::parse($case->fechado_em)->format('d/m/Y') }} @endif
        </div>
    @endif
</a>
