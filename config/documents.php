<?php

return [

    // Função com acesso total, que escolhe as outras funções com acesso
    'owner_function' => 'coordenador',

    // Modelos disponíveis na Documentação, agrupados por família (sem variantes de housings).
    // Modelos escritos à mão ao adicionar documentos aparecem em "Outros".
    'model_groups' => [
        'SiCompact' => ['SiCompact 2020', 'SiCompact 2020 XL', 'SiCompact ECO'],
        'SiVario' => ['SiVario DualSort', 'SiVario MonoSort'],
    ],

    // Tipos de documento (cor usada nas etiquetas e filtros)
    'types' => [
        'instalacao' => ['label' => 'Manual de Instalação', 'color' => '#1976d2', 'icon' => 'fa-tools'],
        'utilizador' => ['label' => 'Manual de Utilizador', 'color' => '#2e7d32', 'icon' => 'fa-book-open'],
        'tecnico' => ['label' => 'Manual Técnico / Serviço', 'color' => '#ef6c00', 'icon' => 'fa-wrench'],
        'especificacoes' => ['label' => 'Especificações', 'color' => '#6a1b9a', 'icon' => 'fa-clipboard-list'],
        'esquemas' => ['label' => 'Esquemas', 'color' => '#00838f', 'icon' => 'fa-project-diagram'],
        'outros' => ['label' => 'Outros', 'color' => '#546e7a', 'icon' => 'fa-file-alt'],
    ],

    'languages' => [
        'pt' => 'Português',
        'en' => 'Inglês',
        'de' => 'Alemão',
        'es' => 'Espanhol',
        'fr' => 'Francês',
    ],

    // Tamanho máximo por ficheiro, em KB (o servidor aceita 32 MB por ficheiro em public/.htaccess)
    'max_size_kb' => 32768,
];
