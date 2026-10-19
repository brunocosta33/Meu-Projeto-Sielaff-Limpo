<?php

// Avarias fora da garantia (culpa do cliente): cotação Sielaff -> cliente -> ordem de compra
return [
    'clients' => [
        'lidl' => 'Lidl',
        'sonae' => 'Sonae',
    ],

    // Ordem das fases. 'waiting' descreve o que falta fazer enquanto o processo está nessa fase.
    'phases' => [
        'registado' => ['label' => 'Avaria registada', 'waiting' => 'Pedir cotação à Sielaff', 'icon' => 'fa-clipboard-list', 'color' => '#475569', 'bg' => '#f1f5f9'],
        'cotacao_pedida' => ['label' => 'Cotação pedida à Sielaff', 'waiting' => 'À espera da cotação Sielaff', 'icon' => 'fa-paper-plane', 'color' => '#b45309', 'bg' => '#fef3c7'],
        'cotacao_recebida' => ['label' => 'Cotação recebida', 'waiting' => 'Enviar cotação ao cliente', 'icon' => 'fa-file-invoice', 'color' => '#0369a1', 'bg' => '#e0f2fe'],
        'enviada_cliente' => ['label' => 'Enviada ao cliente', 'waiting' => 'À espera da ordem de compra', 'icon' => 'fa-hourglass-half', 'color' => '#6d28d9', 'bg' => '#ede9fe'],
        'oc_recebida' => ['label' => 'Ordem de compra recebida', 'waiting' => 'Enviar pedido para a Alemanha', 'icon' => 'fa-file-signature', 'color' => '#1d4ed8', 'bg' => '#dbeafe'],
        'pedido_alemanha' => ['label' => 'Pedido enviado para a Alemanha', 'waiting' => 'Fazer a reparação', 'icon' => 'fa-globe-europe', 'color' => '#0f766e', 'bg' => '#ccfbf1'],
        'concluido' => ['label' => 'Reparação feita', 'waiting' => null, 'icon' => 'fa-check-circle', 'color' => '#15803d', 'bg' => '#dcfce7'],
        'recusado' => ['label' => 'Recusada pelo cliente', 'waiting' => null, 'icon' => 'fa-times-circle', 'color' => '#b91c1c', 'bg' => '#fee2e2'],
    ],

    // Pedido de peças à Sielaff Alemanha (passo "Pedido enviado para a Alemanha")
    'germany_email' => 'spareparts@sielaff.de',

    // Fases em que estamos à espera de terceiros: avisa a partir de X dias e fica a vermelho a partir de Y
    'waiting_alert_days' => ['cotacao_pedida' => [5, 10], 'enviada_cliente' => [7, 14]],
];
