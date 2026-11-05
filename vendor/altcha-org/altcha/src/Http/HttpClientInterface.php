<?php

namespace AltchaOrg\Altcha\Http;

interface HttpClientInterface
{
    /**
     * @param array<string, string> $headers
     */
    public function send(string $url, string $method, array $headers, string $body, float $timeout): HttpResponse;
}
