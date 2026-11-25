{{-- Pedido marcado para um dia que já passou e que ainda não foi fechado. Espera: $pedido --}}
@if($pedido->ficouPorFechar())
    <div class="tr-por-fechar">
        <i class="fas fa-history"></i>
        {{ __('Ficou por fechar de :data', ['data' => \Illuminate\Support\Carbon::parse($pedido->data_agendamento)->format('d/m')]) }}
    </div>
    {{-- No <head>: as <style> soltas no meio da página não chegam a valer aqui --}}
    @pushOnce('styles')
        <style>
            .tr-por-fechar { display: inline-flex; align-items: center; gap: .35rem; margin: .3rem 0; padding: .2rem .6rem;
                             border-radius: 999px; background: #ffedd5; color: #9a3412; font-size: .78rem; font-weight: 700; }
        </style>
    @endPushOnce
@endif
