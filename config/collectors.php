<?php

return [
    'user_agent' => env('COLLECTOR_USER_AGENT', 'AcordaAlcobaca/0.2 (+https://github.com/gadevsbr/Acorda)'),
    'timeout_seconds' => (int) env('COLLECTOR_TIMEOUT_SECONDS', 20),
    'connect_timeout_seconds' => (int) env('COLLECTOR_CONNECT_TIMEOUT_SECONDS', 5),
    'max_attempts' => (int) env('COLLECTOR_MAX_ATTEMPTS', 3),
    'retry_delay_ms' => (int) env('COLLECTOR_RETRY_DELAY_MS', 500),
    'ca_bundle' => env('COLLECTOR_CA_BUNDLE'),
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
];
