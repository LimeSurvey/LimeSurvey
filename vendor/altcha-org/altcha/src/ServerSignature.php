<?php

namespace AltchaOrg\Altcha;

class ServerSignature
{
    /** @var list<string> */
    private const ARRAY_KEYS = ['fields', 'reasons'];

    /**
     * @param array<array-key, mixed>|string $data
     */
    public static function verifyServerSignature(
        string|array $data,
        string $hmacKey,
    ): ServerSignatureVerification {
        $startTime = hrtime(true);
        $payload = self::parsePayload($data);

        if (!$payload) {
            return new ServerSignatureVerification(
                verified: false,
                verificationData: null,
            );
        }

        $hashAlgo = $payload['hmacAlgorithm']->hashAlgo();
        $hash = hash($hashAlgo, $payload['verificationData'], true);
        $expectedSignature = bin2hex(hash_hmac($hashAlgo, $hash, $hmacKey, true));

        $verificationData = self::parseVerificationData($payload['verificationData']);

        $invalidSignature = !hash_equals($expectedSignature, $payload['signature']);
        $invalidSolution = !$payload['verified'] || !($verificationData['verified'] ?? false);

        $expire = $verificationData['expire'] ?? null;
        $expired = \is_int($expire) && $expire < time();

        $verified = !$invalidSignature && !$invalidSolution && !$expired;

        $elapsedTime = (hrtime(true) - $startTime) / 1e9;

        return new ServerSignatureVerification(
            verified: $verified,
            verificationData: $verificationData,
            expired: $expired,
            invalidSignature: $invalidSignature,
            invalidSolution: $invalidSolution,
            time: $elapsedTime,
        );
    }

    /**
     * Parse URL-encoded verification data with auto type detection.
     */
    public static function parseVerificationData(string $data): ServerSignatureVerificationData
    {
        parse_str($data, $params);

        /** @var array<string, mixed> $result */
        $result = [];

        foreach ($params as $key => $value) {
            $key = (string) $key;

            if (!\is_string($value)) {
                $result[$key] = $value;
                continue;
            }

            if (\in_array($key, self::ARRAY_KEYS, true)) {
                $result[$key] = '' !== $value ? explode(',', $value) : [];
                continue;
            }

            if ('true' === $value) {
                $result[$key] = true;
                continue;
            }

            if ('false' === $value) {
                $result[$key] = false;
                continue;
            }

            if (preg_match('/^\d+$/', $value)) {
                $result[$key] = (int) $value;
                continue;
            }

            if (preg_match('/^\d+\.\d+$/', $value)) {
                $result[$key] = (float) $value;
                continue;
            }

            $result[$key] = $value;
        }

        return new ServerSignatureVerificationData($result);
    }

    /**
     * @param array<string, mixed> $formData
     * @param list<string>         $fields
     */
    public static function verifyFieldsHash(
        array $formData,
        array $fields,
        string $fieldsHash,
        HmacAlgorithm $hmacAlgorithm = HmacAlgorithm::SHA256,
    ): bool {
        $lines = [];
        foreach ($fields as $field) {
            $value = $formData[$field] ?? null;
            $lines[] = \is_scalar($value) ? (string) $value : '';
        }
        $joinedData = implode("\n", $lines);
        $computedHash = hash($hmacAlgorithm->hashAlgo(), $joinedData);

        return $computedHash === $fieldsHash;
    }

    /**
     * @param array<array-key, mixed>|string $data
     *
     * @return null|array{hmacAlgorithm: HmacAlgorithm, verificationData: string, signature: string, verified: bool}
     */
    private static function parsePayload(string|array $data): ?array
    {
        if (\is_string($data)) {
            $decoded = base64_decode($data, true);
            if (!$decoded) {
                return null;
            }

            try {
                $data = json_decode($decoded, true, 2, \JSON_THROW_ON_ERROR);
            } catch (\JsonException|\ValueError) {
                return null;
            }

            if (!\is_array($data) || empty($data)) {
                return null;
            }
        }

        if (!isset($data['algorithm'], $data['verificationData'], $data['signature'], $data['verified'])
            || !\is_string($data['algorithm'])
            || !\is_string($data['verificationData'])
            || !\is_string($data['signature'])
            || !\is_bool($data['verified'])
        ) {
            return null;
        }

        $hmacAlgorithm = HmacAlgorithm::tryFrom($data['algorithm']);
        if (!$hmacAlgorithm) {
            return null;
        }

        return [
            'hmacAlgorithm' => $hmacAlgorithm,
            'verificationData' => $data['verificationData'],
            'signature' => $data['signature'],
            'verified' => $data['verified'],
        ];
    }
}
