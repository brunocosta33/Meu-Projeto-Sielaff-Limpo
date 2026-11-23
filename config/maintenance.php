<?php

return [

    // A manutenção pode ser feita entre X dias úteis antes e X dias úteis depois da data contratada.
    // Dias úteis aqui: segunda a sábado; domingos e feriados (nacionais e da empresa) não contam.
    'window_working_days' => 12,

    // Marcar como "Próxima" quando o prazo abre dentro destes dias
    'upcoming_days' => 30,

    // Clientes com regra e cor próprias. A loja entra pelo que estiver na insígnia.
    'clients' => [
        'sonae' => ['label' => 'Sonae', 'color' => '#0a7d49', 'bg' => '#e2f4ea', 'icon' => 'fa-leaf'],
        'lidl' => ['label' => 'Lidl', 'color' => '#0050aa', 'bg' => '#e4edfa', 'icon' => 'fa-store'],
    ],

    // Clientes em que a manutenção vale o mês inteiro contratado, e não uma data com prazo à volta
    'month_clients' => ['sonae'],

    'states' => [
        'atrasada' => ['label' => 'Em atraso', 'color' => '#c62828', 'bg' => '#fde2e1', 'icon' => 'fa-exclamation-circle'],
        'janela' => ['label' => 'Dentro do prazo', 'color' => '#b26a00', 'bg' => '#fff4cc', 'icon' => 'fa-hourglass-half'],
        'proxima' => ['label' => 'Próxima', 'color' => '#1565c0', 'bg' => '#e3f0ff', 'icon' => 'fa-calendar-alt'],
        'futura' => ['label' => 'Por fazer', 'color' => '#546e7a', 'bg' => '#eceff1', 'icon' => 'fa-clock'],
        'contrato_futuro' => ['label' => 'Começa depois', 'color' => '#6d5ba6', 'bg' => '#efecf9', 'icon' => 'fa-hourglass-start'],
        'por_definir' => ['label' => 'Por definir', 'color' => '#5b6b7c', 'bg' => '#eef2f6', 'icon' => 'fa-calendar-plus'],
        'feita' => ['label' => 'Feita', 'color' => '#2e7d32', 'bg' => '#dff0e0', 'icon' => 'fa-check-circle'],
        'feita_fora' => ['label' => 'Feita fora do prazo', 'color' => '#6d4c41', 'bg' => '#efe5df', 'icon' => 'fa-check'],
    ],
];
