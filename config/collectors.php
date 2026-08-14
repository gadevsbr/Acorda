<?php

return [
    'user_agent' => env('COLLECTOR_USER_AGENT', 'AcordaAlcobaca/0.2 (+https://github.com/gadevsbr/Acorda)'),
    'timeout_seconds' => (int) env('COLLECTOR_TIMEOUT_SECONDS', 20),
    'connect_timeout_seconds' => (int) env('COLLECTOR_CONNECT_TIMEOUT_SECONDS', 5),
    'max_attempts' => (int) env('COLLECTOR_MAX_ATTEMPTS', 3),
    'retry_delay_ms' => (int) env('COLLECTOR_RETRY_DELAY_MS', 500),
    'ca_bundle' => env('COLLECTOR_CA_BUNDLE'),
    'alert_email' => env('SOURCE_ALERT_EMAIL'),
    'payroll_per_page' => (int) env('PREFEITURA_PAYROLL_PER_PAGE', 100),
    'payroll_max_pages' => (int) env('PREFEITURA_PAYROLL_MAX_PAGES', 20),

    'prefeitura' => [
        'base_url' => env(
            'PREFEITURA_API_BASE_URL',
            'https://www.acessoinformacao.com.br/transparencia/entidades/ba/alcobaca/dados-abertos',
        ),
        'official_url' => 'https://www.acessoinformacao.com.br/transparencia/entidades/ba/alcobaca/dados-abertos',
        'allowed_hosts' => ['www.acessoinformacao.com.br'],
    ],

    'kbf' => [
        'base_url' => env('KBF_BASE_URL', 'https://folhadepagamento.kbfsistemas.com.br/pmalcobaca_portal'),
        'official_url' => 'https://folhadepagamento.kbfsistemas.com.br/pmalcobaca_portal/form.jsp?action=openform&formID=229&popup=true&sys=PSM',
        'payroll_official_url' => 'https://folhadepagamento.kbfsistemas.com.br/pmalcobaca_portal/form.jsp?action=openform&formID=278&popup=true&sys=PSM',
        'allowed_hosts' => ['folhadepagamento.kbfsistemas.com.br'],
        'timeout_seconds' => (int) env('KBF_TIMEOUT_SECONDS', 120),
    ],
];
