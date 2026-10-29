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

    // Gestão de RH: veem e gerem tudo em Recursos Humanos; os restantes só veem a sua parte
    'manager_functions' => ['coordenador', 'country_manager', 'administrativa'],

    // Funções que podem ver e gerir a página Colaboradores
    'staff_manager_functions' => ['coordenador', 'country_manager'],

    // Funções que podem aprovar/rejeitar férias
    'vacation_approver_functions' => ['coordenador', 'country_manager'],

    // Funções com acesso a Assistência > Fora da Garantia (cotações e ordens de compra)
    'warranty_functions' => ['coordenador', 'country_manager'],

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

    // Horas de um dia normal de trabalho (folga compensatória: dia inteiro = isto, meio dia = metade)
    'work_day_hours' => 8,

    // Horas e Piquetes: tipos de movimento (todos em horas).
    // sign: +1 a favor do colaborador (empresa deve), -1 a favor da empresa / acerto.
    // self: o próprio colaborador pode registar (fica pendente).
    // in_balance: conta para o saldo de horas (o piquete é pago em dinheiro, à parte, e não conta).
    'time_entry_types' => [
        // Piquete realizado: confirmado pela gestão a partir da escala (ou acrescentado à mão); pago em dinheiro
        'piquete_feito' => ['label' => 'Piquete realizado', 'sign' => 1, 'self' => false, 'in_balance' => false, 'color' => '#1565c0', 'icon' => 'fa-shield-alt'],
        'piquete' => ['label' => 'Trabalho suplementar em piquete', 'sign' => 1, 'self' => true, 'in_balance' => false, 'color' => '#0277bd', 'icon' => 'fa-tools'],
        'hora_extra' => ['label' => 'Horas extra', 'sign' => 1, 'self' => true, 'color' => '#2e7d32', 'icon' => 'fa-plus-circle'],
        'reposicao' => ['label' => 'Reposição de horas', 'sign' => 1, 'self' => false, 'color' => '#00897b', 'icon' => 'fa-undo'],
        'horas_em_falta' => ['label' => 'Horas em falta', 'sign' => -1, 'self' => false, 'color' => '#c62828', 'icon' => 'fa-minus-circle'],
        'folga_compensatoria' => ['label' => 'Folga compensatória', 'sign' => -1, 'self' => false, 'color' => '#6a1b9a', 'icon' => 'fa-bed'],
        'pagamento_horas' => ['label' => 'Pagamento de horas', 'sign' => -1, 'self' => false, 'color' => '#455a64', 'icon' => 'fa-money-bill-wave'],
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

    // Cores das marcas do calendário da equipa. As cores de estado das faltas
    // (a aguardar / justificada / injustificada) continuam a valer nas listas de RH.
    'calendar_colors' => [
        'ferias' => '#f9a825',
        'falta' => '#546e7a',
    ],

    'override_reasons' => [
        'doenca' => 'Doença',
        'troca' => 'Troca',
        'ferias' => 'Férias',
        'formacao' => 'Formação',
        'outro' => 'Outro',
    ],
];
