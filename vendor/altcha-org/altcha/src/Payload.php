<?php

namespace AltchaOrg\Altcha;

class Payload
{
    public function __construct(
        public readonly Challenge $challenge,
        public readonly Solution $solution,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'challenge' => $this->challenge->toArray(),
            'solution' => $this->solution->toArray(),
        ];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE) ?: '';
    }

    public function toBase64(): string
    {
        return base64_encode($this->toJson());
    }

    /**
     * @param array<string, mixed> $arr
     */
    public static function fromArray(array $arr): self
    {
        if (!isset($arr['challenge'], $arr['solution']) || !\is_array($arr['challenge']) || !\is_array($arr['solution'])) {
            throw new \InvalidArgumentException('Invalid payload data: expected "challenge" and "solution".');
        }

        /** @var array<string, mixed> $challenge */
        $challenge = $arr['challenge'];
        /** @var array<string, mixed> $solution */
        $solution = $arr['solution'];

        return new self(
            challenge: Challenge::fromArray($challenge),
            solution: Solution::fromArray($solution),
        );
    }

    public static function fromBase64(string $payload): self
    {
        $decoded = base64_decode($payload, true);
        if (false === $decoded) {
            throw new \InvalidArgumentException('Invalid base64-encoded payload.');
        }

        try {
            $data = json_decode($decoded, true, 512, \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Invalid JSON in payload.', previous: $e);
        }

        if (!\is_array($data)) {
            throw new \InvalidArgumentException('Invalid payload: expected a JSON object.');
        }

        /** @var array<string, mixed> $decodedData */
        $decodedData = $data;

        return self::fromArray($decodedData);
    }
}
