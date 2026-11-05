<?php

namespace AltchaOrg\Altcha\Http;

class HttpResponse
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $body,
    ) {
    }
}
