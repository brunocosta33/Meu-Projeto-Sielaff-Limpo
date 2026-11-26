{{--
    Barra "Filtrado por": um botão por filtro ativo (o × tira só esse) e "Limpar filtros" para voltar à vista inicial.
    Espera: $rota (nome da rota), $atuais (os parâmetros dos filtros que estão na página),
            $manter (parâmetros que não são filtros e ficam sempre, ex.: o ano ou a vista),
            $ativos (chave do parâmetro => ['label' => texto, 'icon' => classe fa opcional]).
    Não mostra nada se não houver filtros.
--}}
@php($ativos = array_filter($ativos ?? []))
@if($ativos)
    @pushOnce('styles')
    <style>
        .filtros-ativos { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; margin-top: .75rem;
                          padding: .5rem .65rem; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 12px; }
        .filtros-ativos .rotulo { font-size: .7rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #64748b; margin-right: .15rem; }
        .filtros-ativos .filtro { display: inline-flex; align-items: center; gap: .35rem; padding: .18rem .3rem .18rem .65rem;
                                  border-radius: 999px; background: #e0e7ff; color: #3730a3; font-size: .8rem; font-weight: 700;
                                  text-decoration: none !important; border: 1px solid #c7d2fe; }
        .filtros-ativos .filtro .x { display: inline-flex; align-items: center; justify-content: center; width: 1.15rem; height: 1.15rem;
                                     border-radius: 50%; background: rgba(55, 48, 163, .12); font-size: .65rem; }
        .filtros-ativos .filtro:hover { background: #c7d2fe; color: #312e81; }
        .filtros-ativos .filtro:hover .x { background: #4338ca; color: #fff; }
        .filtros-ativos .limpar { margin-left: auto; display: inline-flex; align-items: center; gap: .35rem; padding: .25rem .75rem;
                                  border-radius: 999px; background: #fff; color: #b91c1c; border: 1px solid #fecaca;
                                  font-size: .8rem; font-weight: 800; text-decoration: none !important; }
        .filtros-ativos .limpar:hover { background: #fee2e2; color: #991b1b; }
    </style>
    @endPushOnce

    <div class="filtros-ativos">
        <span class="rotulo"><i class="fas fa-filter"></i> {{ __('Filtrado por') }}</span>
        @foreach($ativos as $chave => $filtro)
            <a href="{{ route($rota, $manter + \Illuminate\Support\Arr::except($atuais, $chave)) }}" class="filtro" title="{{ __('Tirar este filtro') }}">
                @if(!empty($filtro['icon']))<i class="fas {{ $filtro['icon'] }}"></i>@endif
                {{ $filtro['label'] }}
                <span class="x"><i class="fa fa-times"></i></span>
            </a>
        @endforeach
        <a href="{{ route($rota, $manter) }}" class="limpar"><i class="fas fa-undo"></i> {{ __('Limpar filtros') }}</a>
    </div>
@endif
