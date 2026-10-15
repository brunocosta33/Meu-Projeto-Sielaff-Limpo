<?php

return [

    // A manutenção pode ser feita entre X dias antes e X dias depois da data contratada
    'window_days' => 15,

    // Marcar como "Próxima" quando o prazo abre dentro destes dias
    'upcoming_days' => 30,

    'states' => [
        'atrasada' => ['label' => 'Em atraso', 'color' => '#c62828', 'bg' => '#fde2e1', 'icon' => 'fa-exclamation-circle'],
        'janela' => ['label' => 'Dentro do prazo', 'color' => '#b26a00', 'bg' => '#fff4cc', 'icon' => 'fa-hourglass-half'],
        'proxima' => ['label' => 'Próxima', 'color' => '#1565c0', 'bg' => '#e3f0ff', 'icon' => 'fa-calendar-alt'],
        'futura' => ['label' => 'Por fazer', 'color' => '#546e7a', 'bg' => '#eceff1', 'icon' => 'fa-clock'],
        'feita' => ['label' => 'Feita', 'color' => '#2e7d32', 'bg' => '#dff0e0', 'icon' => 'fa-check-circle'],
        'feita_fora' => ['label' => 'Feita fora do prazo', 'color' => '#6d4c41', 'bg' => '#efe5df', 'icon' => 'fa-check'],
    ],
];
