<?php

return [

    // Segunda-feira de referência: nesta semana o grupo A faz a "semana com fim de semana"
    // e o grupo B a "semana normal". As semanas seguintes alternam.
    'reference_monday' => '2026-01-05',

    // Horários por tipo de semana (1 = segunda ... 7 = domingo). null = folga.
    // Se a hora de fim for inferior à de início, o turno termina no dia seguinte.
    'week_patterns' => [
        'fim_de_semana' => [
            'label' => 'Semana com fim de semana',
            'days' => [
                1 => ['08:00', '17:00'],
                2 => ['08:00', '17:00'],
                3 => ['08:00', '17:00'],
                4 => null,
                5 => null,
                6 => ['11:00', '20:00'],
                7 => ['11:00', '20:00'],
            ],
        ],
        'semana' => [
            'label' => 'Semana normal',
            'days' => [
                1 => ['11:00', '20:00'],
                2 => ['11:00', '20:00'],
                3 => ['11:00', '20:00'],
                4 => ['11:00', '20:00'],
                5 => ['11:00', '20:00'],
                6 => null,
                7 => null,
            ],
        ],
    ],

    // Funções dos colaboradores (hotliner entra na escala rotativa; técnicos farão o piquete no futuro)
    'functions' => [
        'hotliner' => 'Hotliner',
        'tecnico' => 'Técnico',
        'coordenador' => 'Coordenador',
        'country_manager' => 'Country Manager',
        'administrativa' => 'Administrativa',
    ],

    // Funções que podem aprovar/rejeitar férias
    'vacation_approver_functions' => ['coordenador', 'country_manager'],

    'function_colors' => [
        'hotliner' => '#1976d2',
        'tecnico' => '#ef6c00',
        'coordenador' => '#2e7d32',
        'country_manager' => '#6a1b9a',
        'administrativa' => '#c2185b',
    ],

    // Férias: dias úteis de direito por ano (valor por defeito; pode ser alterado por colaborador/ano)
    'vacation_default_days' => 25,

    // Piquete dos técnicos: sábados alternados, um técnico do Norte e um do Sul.
    // No sábado de referência está de serviço o grupo A; no seguinte o grupo B, e assim por diante.
    'piquete' => [
        'label' => 'Técnico de Piquete',
        'reference_saturday' => '2026-01-03',
        'days' => [6], // 6 = sábado
        'hours' => ['08:00', '20:00'],
    ],

    'zones' => [
        'norte' => 'Norte',
        'sul' => 'Sul',
    ],

    // Motivos de falta (baseados nas faltas justificadas do Código do Trabalho)
    'absence_reasons' => [
        'doenca' => 'Doença',
        'consulta' => 'Consulta / tratamento médico',
        'assistencia_familiar' => 'Assistência a familiar',
        'falecimento' => 'Falecimento de familiar',
        'casamento' => 'Casamento',
        'parentalidade' => 'Parentalidade',
        'provas_escolares' => 'Provas escolares',
        'autorizada' => 'Outro motivo autorizado pela empresa',
        'sem_motivo' => 'Sem motivo',
    ],

    'override_reasons' => [
        'doenca' => 'Doença',
        'troca' => 'Troca',
        'ferias' => 'Férias',
        'formacao' => 'Formação',
        'outro' => 'Outro',
    ],
];
