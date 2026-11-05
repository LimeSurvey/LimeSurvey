<?php

namespace AltchaOrg\Altcha\V1;

use AltchaOrg\Altcha\V1\Hasher\Algorithm;
use AltchaOrg\Altcha\V1\Hasher\Hasher;
use AltchaOrg\Altcha\V1\Hasher\HasherInterface;

class Altcha
{
    /**
     * @param string $hmacKey Required HMAC key for challenge calculation and solution verification.
     */
    public function __construct(
        private readonly string $hmacKey,
        private readonly HasherInterface $hasher = new Hasher(),
    ) {
    }

    /**
     * @return null|array<array-key, mixed>
     */
    private function decodePayload(string $payload): ?array
    {
        $decoded = base64_decode($payload, true);

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

        return $data;
    }

    /**
     * @param array<array-key, mixed>|string $data
     */
    private function verifyAndBuildSolutionPayload(string|array $data): ?Payload
    {
        if (\is_string($data)) {
            $data = $this->decodePayload($data);
        }

        if (null === $data
            || !isset($data['algorithm'], $data['challenge'], $data['number'], $data['salt'], $data['signature'])
            || !\is_string($data['algorithm'])
            || !\is_string($data['challenge'])
            || !\is_int($data['number'])
            || !\is_string($data['salt'])
            || !\is_string($data['signature'])
        ) {
            return null;
        }

        $algorithm = Algorithm::tryFrom($data['algorithm']);

        if (!$algorithm) {
            return null;
        }

        return new Payload($algorithm, $data['challenge'], $data['number'], $data['salt'], $data['signature']);
    }

    /**
     * @param array<array-key, mixed>|string $data
     */
    private function verifyAndBuildServerSignaturePayload(string|array $data): ?ServerSignaturePayload
    {
        if (\is_string($data)) {
            $data = $this->decodePayload($data);
        }

        if (null === $data
            || !isset($data['algorithm'], $data['verificationData'], $data['signature'], $data['verified'])
            || !\is_string($data['algorithm'])
            || !\is_string($data['verificationData'])
            || !\is_string($data['signature'])
            || !\is_bool($data['verified'])
        ) {
            return null;
        }

        $algorithm = Algorithm::tryFrom($data['algorithm']);

        if (!$algorithm) {
            return null;
        }

        return new ServerSignaturePayload($algorithm, $data['verificationData'], $data['signature'], $data['verified']);
    }

    /**
     * Creates a new challenge for ALTCHA.
     *
     * @return Challenge The challenge data to be passed to ALTCHA.
     */
    public function createChallenge(BaseChallengeOptions $options = new ChallengeOptions()): Challenge
    {
        $challenge = $this->hasher->hashHex($options->algorithm, $options->salt . $options->number);
        $signature = $this->hasher->hashHmacHex($options->algorithm, $challenge, $this->hmacKey);

        return new Challenge($options->algorithm->value, $challenge, $options->maxNumber, $options->salt, $signature);
    }

    /**
     * Verifies an ALTCHA solution.
     *
     * @param array<array-key, mixed>|string $data         The solution payload to verify.
     * @param bool                           $checkExpires Whether to check if the challenge has expired.
     *
     * @return bool True if the solution is valid.
     */
    public function verifySolution(string|array $data, bool $checkExpires = true): bool
    {
        $payload = $this->verifyAndBuildSolutionPayload($data);

        if (!$payload) {
            return false;
        }

        $params = $this->extractParams($payload);
        if ($checkExpires && isset($params['expires']) && is_numeric($params['expires'])) {
            $expireTime = (int) $params['expires'];
            if (time() > $expireTime) {
                return false;
            }
        }

        $challengeOptions = new CheckChallengeOptions(
            $payload->algorithm,
            $payload->salt,
            $payload->number
        );

        $expectedChallenge = $this->createChallenge($challengeOptions);

        return $expectedChallenge->challenge === $payload->challenge
            && $expectedChallenge->signature === $payload->signature;
    }

    /**
     * @return array<array-key, array<array-key, mixed>|string>
     */
    private function extractParams(Payload $payload): array
    {
        $saltParts = explode('?', $payload->salt);
        if (\count($saltParts) > 1) {
            parse_str($saltParts[1], $params);

            return $params;
        }

        return [];
    }

    /**
     * Verifies the hash of form fields.
     *
     * @param array<string, mixed> $formData   The form data to hash.
     * @param list<string>         $fields     The fields to include in the hash.
     * @param string               $fieldsHash The expected hash value.
     * @param Algorithm            $algorithm  Hashing algorithm (`SHA-1`, `SHA-256`, `SHA-512`).
     */
    public function verifyFieldsHash(array $formData, array $fields, string $fieldsHash, Algorithm $algorithm): bool
    {
        $lines = [];
        foreach ($fields as $field) {
            $value = $formData[$field] ?? null;
            $lines[] = \is_scalar($value) ? (string) $value : '';
        }
        $joinedData = implode("\n", $lines);
        $computedHash = $this->hasher->hashHex($algorithm, $joinedData);

        return $computedHash === $fieldsHash;
    }

    /**
     * Verifies the server signature.
     *
     * @param array<array-key, mixed>|string $data The payload to verify (string or `ServerSignaturePayload` array).
     */
    public function verifyServerSignature(string|array $data): ServerSignatureVerification
    {
        $payload = $this->verifyAndBuildServerSignaturePayload($data);

        if (!$payload) {
            return new ServerSignatureVerification(false, null);
        }

        $hash = $this->hasher->hash($payload->algorithm, $payload->verificationData);
        $expectedSignature = $this->hasher->hashHmacHex($payload->algorithm, $hash, $this->hmacKey);

        parse_str($payload->verificationData, $params);

        $verificationData = new ServerSignatureVerificationData(
            classification: isset($params['classification']) && \is_string($params['classification']) ? $params['classification'] : '',
            country: isset($params['country']) && \is_string($params['country']) ? $params['country'] : '',
            detectedLanguage: isset($params['detectedLanguage']) && \is_string($params['detectedLanguage']) ? $params['detectedLanguage'] : '',
            email: isset($params['email']) && \is_string($params['email']) ? $params['email'] : '',
            expire: isset($params['expire']) && is_numeric($params['expire']) ? (int) $params['expire'] : 0,
            fields: isset($params['fields']) && \is_array($params['fields']) ? $params['fields'] : [],
            fieldsHash: isset($params['fieldsHash']) && \is_string($params['fieldsHash']) ? $params['fieldsHash'] : '',
            reasons: isset($params['reasons']) && \is_array($params['reasons']) ? $params['reasons'] : [],
            score: isset($params['score']) && is_numeric($params['score']) ? (float) $params['score'] : 0.0,
            time: isset($params['time']) && is_numeric($params['time']) ? (int) $params['time'] : 0,
            verified: isset($params['verified']) && $params['verified'],
        );

        $now = time();
        $isVerified = $payload->verified && $verificationData->verified
            && $verificationData->expire > $now
            && $payload->signature === $expectedSignature;

        return new ServerSignatureVerification($isVerified, $verificationData);
    }

    /**
     * Finds a solution to the given challenge.
     *
     * @param string    $challenge The challenge hash.
     * @param string    $salt      The challenge salt.
     * @param Algorithm $algorithm Hashing algorithm.
     * @param int       $max       Maximum number to iterate to.
     * @param int       $start     Starting number.
     */
    public function solveChallenge(string $challenge, string $salt, Algorithm $algorithm, int $max, int $start = 0): ?Solution
    {
        $startTime = microtime(true);

        for ($n = $start; $n <= $max; $n++) {
            $hash = $this->hasher->hashHex($algorithm, $salt . $n);
            if ($hash === $challenge) {
                $took = microtime(true) - $startTime;

                return new Solution($n, $took);
            }
        }

        return null;
    }
}
