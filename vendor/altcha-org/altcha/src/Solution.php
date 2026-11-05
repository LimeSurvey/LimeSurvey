<?php

namespace AltchaOrg\Altcha;

class Solution
{
    public function __construct(
        public readonly int $counter,
        public readonly string $derivedKey,
        public readonly ?float $time = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $arr = [
            'counter' => $this->counter,
            'derivedKey' => $this->derivedKey,
        ];
        if (null !== $this->time) {
            $arr['time'] = $this->time;
        }

        return $arr;
    }

    /**
     * @param array<string, mixed> $arr
     */
    public static function fromArray(array $arr): self
    {
        if (!isset($arr['counter'], $arr['derivedKey']) || !\is_int($arr['counter']) || !\is_string($arr['derivedKey'])) {
            throw new \InvalidArgumentException('Invalid solution data: expected "counter" (int) and "derivedKey" (string).');
        }

        return new self(
            counter: $arr['counter'],
            derivedKey: $arr['derivedKey'],
            time: isset($arr['time']) && is_numeric($arr['time']) ? (float) $arr['time'] : null,
        );
    }
}
