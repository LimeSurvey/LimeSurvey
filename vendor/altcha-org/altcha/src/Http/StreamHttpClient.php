<?php

namespace AltchaOrg\Altcha\Http;

/**
 * Default HttpClientInterface implementation using PHP's built-in stream wrappers.
 * Requires no additional extension or dependency.
 */
class StreamHttpClient implements HttpClientInterface
{
    public function send(string $url, string $method, array $headers, string $body, float $timeout): HttpResponse
    {
        $headerLines = [];
        foreach ($headers as $name => $value) {
            $headerLines[] = $name . ': ' . $value;
        }

        $context = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headerLines),
                'content' => $body,
                'timeout' => $timeout,
                'ignore_errors' => true,
            ],
        ]);

        $errorMessage = null;
        set_error_handler(static function (int $errno, string $errstr) use (&$errorMessage): bool {
            $errorMessage = $errstr;

            return true;
        });

        try {
            $handle = fopen($url, 'r', false, $context);
        } finally {
            restore_error_handler();
        }

        if (false === $handle) {
            throw new \RuntimeException($errorMessage ?? 'Network error while requesting Sentinel API.');
        }

        $responseBody = stream_get_contents($handle);
        $meta = stream_get_meta_data($handle);
        fclose($handle);

        if (false === $responseBody) {
            throw new \RuntimeException('Failed to read response from Sentinel API.');
        }

        $statusCode = 0;
        /** @var list<string> $wrapperData */
        $wrapperData = $meta['wrapper_data'] ?? [];
        foreach ($wrapperData as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d+)#', $line, $matches)) {
                $statusCode = (int) $matches[1];
            }
        }

        return new HttpResponse($statusCode, $responseBody);
    }
}
