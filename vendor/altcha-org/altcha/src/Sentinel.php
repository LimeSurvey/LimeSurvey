<?php

namespace AltchaOrg\Altcha;

use AltchaOrg\Altcha\Http\HttpResponse;
use AltchaOrg\Altcha\Http\StreamHttpClient;

class Sentinel
{
    /**
     * Verifies a payload remotely via the ALTCHA Sentinel `/v1/verify/signature` API,
     * instead of verifying the HMAC signature locally.
     */
    public static function verify(VerifyServerOptions $options): VerifyServerResult
    {
        $httpClient = $options->httpClient ?? new StreamHttpClient();

        $body = ['payload' => $options->payload];
        if (null !== $options->secret) {
            $body['secret'] = $options->secret;
        }
        $bodyJson = json_encode($body, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE) ?: '{}';

        $headers = ['Content-Type' => 'application/json', ...$options->headers];

        for ($attempt = 0; $attempt <= $options->retries; $attempt++) {
            try {
                $response = $httpClient->send($options->url, 'POST', $headers, $bodyJson, $options->timeout);

                return self::handleResponse($response);
            } catch (\Throwable $e) {
                if ($attempt >= $options->retries) {
                    return new VerifyServerResult(verified: false, reason: $e->getMessage());
                }

                $backoffMs = RetryBackoff::Fixed === $options->retryBackoff
                    ? $options->retryDelay
                    : $options->retryDelay * 2 ** $attempt;
                usleep($backoffMs * 1000);
            }
        }

        return new VerifyServerResult(verified: false, reason: 'NETWORK_ERROR');
    }

    /**
     * Interprets an HTTP response. Returns a terminal (non-retryable) VerifyServerResult,
     * or throws to trigger the retry path for non-2xx/non-400 statuses and unparseable bodies.
     */
    private static function handleResponse(HttpResponse $response): VerifyServerResult
    {
        if (400 === $response->statusCode) {
            $data = json_decode($response->body, true);
            $reason = \is_array($data) && isset($data['error']) && \is_string($data['error']) ? $data['error'] : 'HTTP_400';

            return new VerifyServerResult(verified: false, reason: $reason);
        }

        if ($response->statusCode < 200 || $response->statusCode >= 300) {
            throw new \RuntimeException('HTTP_' . $response->statusCode);
        }

        $data = json_decode($response->body, true);
        if (!\is_array($data)) {
            throw new \RuntimeException('Invalid JSON response from Sentinel API.');
        }

        /** @var array<string, mixed> $result */
        $result = $data;

        return VerifyServerResult::fromArray($result);
    }
}
