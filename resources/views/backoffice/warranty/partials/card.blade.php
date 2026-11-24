{{-- Um processo no quadro. Clicar abre-o. --}}
@php($marca = config('maintenance.clients.' . $case->cliente))
@php($tipo = $case->typeConfig())

<a href="{{ route('backoffice.warranty.show', $case->id) }}" class="fg-cartao" style="--c: {{ $cor }}">
    <div class="fg-topo">
        @if($marca)
            <span class="fg-marca" style="--grad: {{ $marca['gradiente'] }}; --txt: {{ $marca['texto'] }}">{{ $marca['label'] }}</span>
        @endif
        <span class="fg-tipo" style="--tc: {{ $tipo['color'] }}; --tb: {{ $tipo['bg'] }}">
            <i class="fas {{ $tipo['icon'] }}"></i> {{ __($tipo['curto']) }}
        </span>
    </div>

    <div class="fg-loja"><span class="cod">{{ $case->store->codigo_loja ?? '' }}</span>{{ $case->store->nome_loja ?? __('Sem loja') }}</div>

    {{-- A ordem de serviço é o que identifica o processo: fica à vista --}}
    @if(filled($case->os_numero))
        <span class="fg-os" title="{{ __('Ordem de serviço') }}"><b>OS</b> {{ $case->os_numero }}</span>
    @else
        <span class="fg-os falta" title="{{ __('Ainda sem ordem de serviço') }}"><b>OS</b> {{ __('por registar') }}</span>
    @endif

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

    {{-- As datas do processo, à vista: a avaria e cada passo já dado --}}
    <div class="fg-datas">
        <span><b>{{ __('Avaria') }}</b> {{ optional($case->data_avaria)->format('d/m/Y') ?? '—' }}</span>
        @foreach($case->blocks() as $chave => $bloco)
            @if($data = $case->blockDate($chave))
                <span style="--bc: {{ $bloco['color'] }}"><b>{{ __($bloco['curto']) }}</b> {{ $data->format('d/m/Y') }}</span>
            @endif
        @endforeach
        @if(($case->photos_count ?? 0) > 0)
            <span class="fotos"><i class="fas fa-camera"></i> {{ $case->photos_count }}</span>
        @endif
    </div>

    @if($case->isOpen())
    @else
        <div class="fg-fechado-estado" style="color: {{ $cor }}">
            <i class="fas {{ $case->phase()['icon'] }}"></i> {{ __($case->phase()['label']) }}
            @if($case->fechado_em) · {{ \Carbon\Carbon::parse($case->fechado_em)->format('d/m/Y') }} @endif
        </div>
    @endif
</a>
