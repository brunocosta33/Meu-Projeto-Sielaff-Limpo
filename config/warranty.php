<?php

// Avarias fora da garantia (culpa do cliente).
//
// O processo são cinco blocos, cada um com duas metades ("pedi" e "recebi"). Uma metade por preencher
// é uma coisa por fazer; a data da primeira metade é o que faz correr o relógio dos avisos.
// Nada é obrigatório pela ordem: um bloco vazio é um passo que não foi preciso (ex.: peça já em stock).
//
//   com peças -> visita -> cotação Sielaff -> cliente -> peça da Alemanha -> reparação
//   sem peças -> visita -> cliente -> fecho
return [
    'clients' => [
        'lidl' => 'Lidl',
        'sonae' => 'Sonae',
    ],

    // Tipo de processo: só muda os blocos por que passa.
    'types' => [
        'com_pecas' => [
            'label' => 'Com peças',
            'curto' => 'Peças',
            'ajuda' => 'A reparação precisa de peça: pede-se cotação à Sielaff e encomenda-se à Alemanha.',
            'icon' => 'fa-cogs',
            'color' => '#b91c1c',
            'bg' => '#fee2e2',
        ],
        'sem_pecas' => [
            'label' => 'Sem peças',
            'curto' => 'Serviço',
            'ajuda' => 'Só deslocação e mão de obra (ex.: cobrar a visita de diagnóstico). A cotação é nossa e vai direta ao cliente.',
            'icon' => 'fa-tools',
            'color' => '#0369a1',
            'bg' => '#e0f2fe',
        ],
    ],

    // Os blocos, por ordem. Cada metade diz qual é a sua data, a fase que representa e se leva documento.
    'blocks' => [
        'visita' => [
            'label' => 'Visita de diagnóstico',
            'icon' => 'fa-clipboard-check',
            'color' => '#7c3aed',
            'bg' => '#f3e8ff',
            'types' => ['com_pecas', 'sem_pecas'],
            'halves' => [
                'feita' => ['label' => 'Ordem de serviço', 'date' => 'os_em', 'fase' => 'os_diagnostico', 'doc' => 'os', 'todo' => 'Registar a ordem de serviço da visita'],
            ],
        ],
        'sielaff' => [
            'label' => 'Cotação Sielaff',
            'icon' => 'fa-file-invoice',
            'color' => '#b45309',
            'bg' => '#fef3c7',
            'types' => ['com_pecas'],
            'halves' => [
                'pedida' => ['label' => 'Pedida à Sielaff', 'date' => 'cotacao_pedida_em', 'fase' => 'cotacao_pedida', 'todo' => 'Pedir a cotação à Sielaff'],
                'recebida' => ['label' => 'Cotação recebida', 'date' => 'cotacao_recebida_em', 'fase' => 'cotacao_recebida', 'doc' => 'cotacao', 'todo' => 'Receber a cotação da Sielaff'],
            ],
        ],
        'cliente' => [
            'label' => 'Cliente',
            'icon' => 'fa-paper-plane',
            'color' => '#6d28d9',
            'bg' => '#ede9fe',
            'types' => ['com_pecas', 'sem_pecas'],
            'halves' => [
                'enviada' => ['label' => 'Cotação enviada', 'date' => 'enviada_cliente_em', 'fase' => 'enviada_cliente', 'todo' => 'Enviar a cotação ao cliente'],
                'oc' => ['label' => 'Ordem de compra', 'date' => 'oc_recebida_em', 'fase' => 'oc_recebida', 'doc' => 'oc', 'todo' => 'Receber a ordem de compra'],
            ],
        ],
        'alemanha' => [
            'label' => 'Peça (Alemanha)',
            'icon' => 'fa-globe-europe',
            'color' => '#0f766e',
            'bg' => '#ccfbf1',
            'types' => ['com_pecas'],
            'halves' => [
                'pedida' => ['label' => 'Pedido enviado', 'date' => 'alemanha_enviado_em', 'fase' => 'pedido_alemanha', 'todo' => 'Enviar o pedido para a Alemanha'],
                'recebida' => ['label' => 'Peça confirmada', 'date' => 'peca_recebida_em', 'fase' => 'peca_recebida', 'todo' => 'Confirmar a peça'],
            ],
        ],
        'fecho' => [
            'label' => 'Fecho',
            'icon' => 'fa-check-circle',
            'color' => '#15803d',
            'bg' => '#dcfce7',
            'types' => ['com_pecas', 'sem_pecas'],
            // As duas metades são alternativas: ou se fez, ou o cliente recusou
            'halves' => [
                'concluido' => ['label' => 'Reparação feita', 'date' => 'fechado_em', 'fase' => 'concluido', 'doc' => 'servico', 'todo' => 'Fechar o processo'],
                'recusado' => ['label' => 'Recusada pelo cliente', 'date' => 'fechado_em', 'fase' => 'recusado'],
            ],
        ],
    ],

    // Nomes das fases (derivadas das datas preenchidas): crachás, listagem e Excel.
    'phases' => [
        'registado' => ['label' => 'Avaria registada', 'icon' => 'fa-clipboard-list', 'color' => '#475569', 'bg' => '#f1f5f9'],
        'os_diagnostico' => ['label' => 'Visita feita', 'icon' => 'fa-clipboard-check', 'color' => '#7c3aed', 'bg' => '#f3e8ff'],
        'cotacao_pedida' => ['label' => 'Cotação pedida à Sielaff', 'icon' => 'fa-paper-plane', 'color' => '#b45309', 'bg' => '#fef3c7'],
        'cotacao_recebida' => ['label' => 'Cotação recebida', 'icon' => 'fa-file-invoice', 'color' => '#0369a1', 'bg' => '#e0f2fe'],
        'enviada_cliente' => ['label' => 'Enviada ao cliente', 'icon' => 'fa-hourglass-half', 'color' => '#6d28d9', 'bg' => '#ede9fe'],
        'oc_recebida' => ['label' => 'Ordem de compra recebida', 'icon' => 'fa-file-signature', 'color' => '#1d4ed8', 'bg' => '#dbeafe'],
        'pedido_alemanha' => ['label' => 'Pedido enviado para a Alemanha', 'icon' => 'fa-globe-europe', 'color' => '#0f766e', 'bg' => '#ccfbf1'],
        'peca_recebida' => ['label' => 'Peça confirmada', 'icon' => 'fa-box-open', 'color' => '#c2410c', 'bg' => '#ffedd5'],
        'concluido' => ['label' => 'Reparação feita', 'icon' => 'fa-check-circle', 'color' => '#15803d', 'bg' => '#dcfce7'],
        'recusado' => ['label' => 'Recusada pelo cliente', 'icon' => 'fa-times-circle', 'color' => '#b91c1c', 'bg' => '#fee2e2'],
    ],

    // De quem estamos à espera em cada fase. É assim que a listagem se organiza:
    // a pergunta não é "em que fase está", é "o que tenho de fazer e por quem espero".
    'waiting_on' => [
        'nos' => [
            'label' => 'A nossa vez', 'curto' => 'A nossa vez', 'icon' => 'fa-user-check', 'color' => '#b45309', 'bg' => '#fef3c7',
            'phases' => ['registado', 'os_diagnostico', 'cotacao_recebida', 'oc_recebida', 'peca_recebida'],
        ],
        'sielaff' => [
            'label' => 'À espera da Sielaff', 'curto' => 'Sielaff', 'icon' => 'fa-paper-plane', 'color' => '#0369a1', 'bg' => '#e0f2fe',
            'phases' => ['cotacao_pedida'],
        ],
        'cliente' => [
            'label' => 'À espera do cliente', 'curto' => 'Cliente', 'icon' => 'fa-hourglass-half', 'color' => '#6d28d9', 'bg' => '#ede9fe',
            'phases' => ['enviada_cliente'],
        ],
        'alemanha' => [
            'label' => 'À espera da Alemanha', 'curto' => 'Alemanha', 'icon' => 'fa-globe-europe', 'color' => '#0f766e', 'bg' => '#ccfbf1',
            'phases' => ['pedido_alemanha'],
        ],
        // Não é uma fase: são os que já passaram do prazo de espera (ver waiting_alert_days)
        'atrasados' => [
            'label' => 'Passaram do prazo', 'curto' => 'Atrasados', 'icon' => 'fa-exclamation-triangle', 'color' => '#b91c1c', 'bg' => '#fee2e2',
            'late' => true,
        ],
        'fechados' => [
            'label' => 'Fechados', 'curto' => 'Fechados', 'icon' => 'fa-check-circle', 'color' => '#15803d', 'bg' => '#dcfce7',
            'phases' => ['concluido', 'recusado'],
        ],
    ],

    // Passo "Peça confirmada": de onde veio a peça
    'part_origins' => [
        'recebida' => 'Recebemos a peça',
        'stock' => 'Já tínhamos a peça connosco',
    ],

    // Pedido de peças à Sielaff Alemanha
    'germany_email' => 'spareparts@sielaff.de',

    // Estamos à espera de terceiros: avisa a partir de X dias, fica a vermelho a partir de Y
    'waiting_alert_days' => ['cotacao_pedida' => [5, 10], 'enviada_cliente' => [7, 14], 'pedido_alemanha' => [14, 30]],
];
