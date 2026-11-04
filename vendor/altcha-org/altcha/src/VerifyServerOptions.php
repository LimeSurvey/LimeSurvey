<?php

namespace AltchaOrg\Altcha;

use AltchaOrg\Altcha\Http\HttpClientInterface;

class VerifyServerOptions
{
    /**
     * @param array<string, mixed>|string $payload    The payload to verify, as received from the client
     *                                                (either the raw base64 string or a decoded array).
     * @param string                      $url        Full URL of the Sentinel `/v1/verify/signature` endpoint.
     * @param null|string                 $secret     API key secret. If provided, Sentinel checks that it
     *                                                matches the API key associated with the payload.
     * @param null|HttpClientInterface    $httpClient Custom HTTP client. Defaults to a built-in stream-based client.
     * @param array<string, string>       $headers    Additional headers to send with the request.
     * @param float                       $timeout    Per-attempt request timeout in seconds. Defaults to `10.0`.
     * @param int                         $retries    Number of retry attempts after the first try. Defaults to `0`.
     * @param int                         $retryDelay Base delay in milliseconds between retry attempts. Defaults to `300`.
     */
    public function __construct(
        public readonly array|string $payload,
        public readonly string $url,
        public readonly ?string $secret = null,
        public readonly ?HttpClientInterface $httpClient = null,
        public readonly array $headers = [],
        public readonly float $timeout = 10.0,
        public readonly int $retries = 0,
        public readonly int $retryDelay = 300,
        public readonly RetryBackoff $retryBackoff = RetryBackoff::Exponential,
    ) {
    }
}
