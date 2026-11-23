{{-- Aviso ao técnico: esta página é o dia de hoje; a semana está noutro sítio. --}}
@if($soHoje ?? false)
    <div class="sh-aviso">
        <i class="fas fa-calendar-day"></i>
        <div>
            <b>{{ \Illuminate\Support\Str::ucfirst(now()->isoFormat('dddd, D [de] MMMM')) }}</b>
            <span>{{ __('Aqui aparece só o que está marcado para hoje.') }}</span>
        </div>
        <a href="{{ route('backoffice.technical_requests.my_week') }}">
            <i class="fas fa-route"></i> {{ __('A Minha Semana') }}
        </a>
    </div>
    <style>
        .sh-aviso { display: flex; flex-wrap: wrap; align-items: center; gap: .6rem; margin-bottom: 1rem;
                    padding: .7rem 1rem; border-radius: 12px; color: #fff;
                    background: linear-gradient(115deg, #0b5ed7, #1b9aaa); }
        .sh-aviso > i { font-size: 1.2rem; opacity: .9; }
        .sh-aviso b { display: block; font-size: .95rem; text-transform: capitalize; }
        .sh-aviso span { font-size: .8rem; opacity: .88; }
        .sh-aviso a { margin-left: auto; background: #fff; color: #0b5ed7; font-weight: 700; font-size: .84rem;
                      border-radius: 9px; padding: .35rem .8rem; text-decoration: none !important; white-space: nowrap; }
        .sh-aviso a:hover { background: #e7f0ff; }
    </style>
@endif
