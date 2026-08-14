<?php

namespace App\Collectors\Alcobaca\Kbf;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

final class KbfClient
{
    private const FORM_ID = 229;

    private const GRID_ID = 818627;

    public function __construct(private readonly KbfActiveEmployeeParser $parser) {}

    public function activeEmployees(): KbfActiveEmployeeDataset
    {
        $base = rtrim((string) config('collectors.kbf.base_url'), '/').'/';
        $this->assertAllowedUrl($base);
        $formUrl = $base.'openform.do?action=openform&formID=229&popup=true&sys=PSM';
        $startedAt = hrtime(true);
        $form = $this->request()->get($formUrl)->throw();
        $cookie = $this->sessionCookie($form->header('Set-Cookie'));

        $execution = $this->request($cookie)
            ->withBody($this->queryBody(), 'application/x-www-form-urlencoded')
            ->post($base.'executeRule.do')
            ->throw();
        $executionBody = $this->utf8($execution->body(), $execution->header('Content-Type'));
        if (! preg_match('/setTotalRows\((\d+)\)/', $executionBody, $match)) {
            throw new RuntimeException('O KBF não informou o total de servidores ativos.');
        }

        $total = (int) $match[1];
        $navigateUrl = $base.'navigate.do?'.http_build_query([
            'sys' => 'PSM', 'formID' => self::FORM_ID, 'componentID' => self::GRID_ID,
            'action' => 'navigate', 'param' => 'first', 'inner' => 'true', 'gt' => $total,
        ]);
        $grid = $this->request($cookie)->get($navigateUrl)->throw();
        $gridBody = $this->utf8($grid->body(), $grid->header('Content-Type'));
        $records = $this->parser->parse($gridBody);
        if (count($records) !== $total) {
            throw new RuntimeException("O KBF informou {$total} registros, mas a grade entregou ".count($records).'.');
        }

        return new KbfActiveEmployeeDataset(
            records: $records,
            total: $total,
            url: (string) config('collectors.kbf.official_url'),
            httpStatus: $grid->status(),
            contentType: $grid->header('Content-Type'),
            responseTimeMs: (int) round((hrtime(true) - $startedAt) / 1_000_000),
        );
    }

    private function request(?string $cookie = null): PendingRequest
    {
        $caBundle = config('collectors.ca_bundle');
        $request = Http::withUserAgent((string) config('collectors.user_agent'))
            ->connectTimeout((int) config('collectors.connect_timeout_seconds'))
            ->timeout((int) config('collectors.kbf.timeout_seconds'))
            ->retry((int) config('collectors.max_attempts'), (int) config('collectors.retry_delay_ms'), throw: false)
            ->withOptions(['allow_redirects' => false, 'verify' => is_string($caBundle) && $caBundle !== '' ? $caBundle : true]);

        return $cookie === null ? $request : $request->withHeaders(['Cookie' => $cookie]);
    }

    private function sessionCookie(?string $setCookie): string
    {
        if (! is_string($setCookie) || ! preg_match('/(?:^|[,;]\s*)(JSESSIONID=[^;,\s]+)/', $setCookie, $match)) {
            throw new RuntimeException('O KBF não iniciou uma sessão de consulta.');
        }

        return $match[1];
    }

    private function queryBody(): string
    {
        return 'iframeId=RULEACORDA&sys=PSM&formID=229&action=executeRule&ruleName=Transpar%EAncia+-+Servidores+-+Atualiza+grade'
            .'&P_0=&P_1=&P_2=&P_3=&P_4=&P_5=&P_6=&P_7=&P_8=&P_9=&F_0_828116=&F_2_828118=PREFEITURA+MUNICIPAL+DE+ALCOBACA'
            .'&F_3_828119=13.761.721%2F0001-66&F_4_828120=PRACA+SAO+BERNARDO&F_5_828122=CENTRO&F_6_828121=330&F_7_826647='
            .'&F_8_826648=&F_9_826649=&F_10_826650=&F_11_826656=&F_12_826657=&F_13_828580=&F_14_828581=At%E9&F_15_828582='
            .'&F_16_828583=&F_17_828584=At%E9&F_18_828585=&F_19_828586=&F_20_818627=&F_21_826655=&F_22_828677=&F_23_828678='
            .'&F_24_826653=Gerando+aguarde...&F_25_826654=';
    }

    private function utf8(string $body, ?string $contentType): string
    {
        return is_string($contentType) && str_contains(strtoupper($contentType), 'ISO-8859-1')
            ? mb_convert_encoding($body, 'UTF-8', 'ISO-8859-1') : $body;
    }

    private function assertAllowedUrl(string $url): void
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (parse_url($url, PHP_URL_SCHEME) !== 'https' || ! in_array($host, (array) config('collectors.kbf.allowed_hosts'), true)) {
            throw new InvalidArgumentException('URL do coletor KBF fora da allowlist oficial.');
        }
    }
}
