<?php

namespace AltchaOrg\Altcha;

class ChallengeParameters
{
    /**
     * @param null|array<array-key, mixed> $data JSON object; numeric-string keys become int keys in PHP
     */
    public function __construct(
        public readonly string $algorithm,
        public readonly string $nonce,
        public readonly string $salt,
        public readonly int $cost,
        public readonly int $keyLength = 32,
        public readonly string $keyPrefix = '',
        public readonly ?string $keySignature = null,
        public readonly ?int $memoryCost = null,
        public readonly ?int $parallelism = null,
        public readonly int|float|null $expiresAt = null,
        public readonly ?array $data = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $arr = [
            'algorithm' => $this->algorithm,
            'cost' => $this->cost,
            'keyLength' => $this->keyLength,
            'keyPrefix' => $this->keyPrefix,
            'nonce' => $this->nonce,
            'salt' => $this->salt,
        ];

        if (null !== $this->keySignature) {
            $arr['keySignature'] = $this->keySignature;
        }
        if (null !== $this->memoryCost) {
            $arr['memoryCost'] = $this->memoryCost;
        }
        if (null !== $this->parallelism) {
            $arr['parallelism'] = $this->parallelism;
        }
        if (null !== $this->expiresAt) {
            $arr['expiresAt'] = $this->expiresAt;
        }
        if (null !== $this->data) {
            // `data` is a JSON object (JS: Record<string, …>); PHP can't tell `{}`/`{"0": …}` from a list.
            $arr['data'] = array_is_list($this->data) ? (object) $this->data : $this->data;
        }

        ksort($arr);

        return $arr;
    }

    /**
     * Byte-for-byte equal to altcha-lib (JS) `JSON.stringify(sortKeys(parameters))`.
     *
     * @throws \JsonException if a string is not valid UTF-8
     */
    public function toCanonicalJson(): string
    {
        return self::encodeCanonical($this->toArray());
    }

    /**
     * @param bool $sortKeys false inside lists: JS `sortKeys()` returns arrays (and their contents) untouched
     */
    private static function encodeCanonical(mixed $value, bool $sortKeys = true): string
    {
        if (\is_array($value) && array_is_list($value)) {
            return '[' . implode(',', array_map(static fn (mixed $item): string => self::encodeCanonical($item, false), $value)) . ']';
        }
        if (\is_array($value) || $value instanceof \stdClass) {
            $value = (array) $value;
            // PHP turns numeric-string keys into ints; JS keys are always strings.
            $keys = array_map(strval(...), array_keys($value));
            usort($keys, static fn (string $a, string $b): int => self::compareKeys($a, $b, $sortKeys));
            $members = array_map(
                static fn (string $key): string => self::encodeCanonical($key) . ':' . self::encodeCanonical($value[$key], $sortKeys),
                $keys,
            );

            return '{' . implode(',', $members) . '}';
        }
        if (\is_float($value) || (\is_int($value) && abs($value) > 2 ** 53)) {
            return self::encodeJsNumber((float) $value);
        }

        return json_encode(
            $value,
            \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_LINE_TERMINATORS | \JSON_THROW_ON_ERROR,
        );
    }

    /**
     * JS property enumeration order: array-index keys ("0"…"4294967294") first in numeric order, then the
     * remaining keys sorted (objects rebuilt by `sortKeys()`) or in insertion order (stable `usort`).
     */
    private static function compareKeys(string $a, string $b, bool $sortKeys): int
    {
        $aIndex = self::arrayIndex($a);
        $bIndex = self::arrayIndex($b);
        if (null !== $aIndex || null !== $bIndex) {
            return (null === $aIndex ? 1 : 0) <=> (null === $bIndex ? 1 : 0) ?: $aIndex <=> $bIndex;
        }

        return $sortKeys ? self::compareUtf16($a, $b) : 0;
    }

    private static function arrayIndex(string $key): ?int
    {
        return 1 === preg_match('/\A(?:0|[1-9][0-9]{0,9})\z/', $key) && (int) $key <= 4294967294 ? (int) $key : null;
    }

    /**
     * Compares UTF-8 strings in UTF-16 code unit order (JS `Array.prototype.sort`): identical to byte
     * order except that U+E000–U+FFFF (lead bytes EE, EF) sorts after supplementary characters
     * (lead bytes F0–F4, surrogate pairs in UTF-16).
     */
    private static function compareUtf16(string $a, string $b): int
    {
        $common = strspn($a ^ $b, "\0");
        if ($common === min(\strlen($a), \strlen($b))) {
            return \strlen($a) <=> \strlen($b);
        }
        $x = \ord($a[$common]);
        $y = \ord($b[$common]);
        if ($x >= 0xEE && $y >= 0xEE) {
            $x = $x < 0xF0 ? $x + 0x10 : $x;
            $y = $y < 0xF0 ? $y + 0x10 : $y;
        }

        return $x <=> $y;
    }

    /**
     * Formats a double like JS `Number.prototype.toString()`: shortest round-trip digits, plain notation
     * for 1e-6 <= |n| < 1e21, otherwise `d[.ddd]e±x`.
     */
    private static function encodeJsNumber(float $value): string
    {
        if (!is_finite($value)) {
            return 'null';
        }
        if (0.0 === $value) {
            return '0';
        }

        // Shortest digits that round-trip, as coefficient × 10^scale.
        $abs = abs($value);
        for ($precision = 0; $precision < 17; $precision++) {
            [$mantissa, $exponent] = explode('e', \sprintf('%.' . $precision . 'e', $abs));
            $scale = (int) $exponent - $precision;
            $coefficient = (int) str_replace('.', '', $mantissa);
            if ((float) ($coefficient . 'e' . $scale) === $abs) {
                break;
            }
            // At exact powers of two the gap below is half the gap above: the nearest candidate can fail
            // while the next one up round-trips, and JS picks that one.
            if ((float) (($coefficient + 1) . 'e' . $scale) === $abs) {
                $coefficient++;
                break;
            }
        }
        $sign = $value < 0 ? '-' : '';
        $digits = rtrim((string) $coefficient, '0');
        $k = \strlen($digits);
        $n = $scale + \strlen((string) $coefficient);

        if ($k <= $n && $n <= 21) {
            return $sign . $digits . str_repeat('0', $n - $k);
        }
        if (0 < $n && $n <= 21) {
            return $sign . substr($digits, 0, $n) . '.' . substr($digits, $n);
        }
        if (-6 < $n && $n <= 0) {
            return $sign . '0.' . str_repeat('0', -$n) . $digits;
        }

        return $sign . $digits[0] . ($k > 1 ? '.' . substr($digits, 1) : '') . 'e' . ($n > 0 ? '+' : '-') . abs($n - 1);
    }

    /**
     * @param array<string, mixed> $arr
     */
    public static function fromArray(array $arr): self
    {
        $algorithm = isset($arr['algorithm']) && \is_string($arr['algorithm']) ? $arr['algorithm'] : '';
        $nonce = isset($arr['nonce']) && \is_string($arr['nonce']) ? $arr['nonce'] : '';
        $salt = isset($arr['salt']) && \is_string($arr['salt']) ? $arr['salt'] : '';
        $cost = isset($arr['cost']) && \is_int($arr['cost']) ? $arr['cost'] : 0;
        $keyLength = isset($arr['keyLength']) && \is_int($arr['keyLength']) ? $arr['keyLength'] : 32;
        $keyPrefix = isset($arr['keyPrefix']) && \is_string($arr['keyPrefix']) ? $arr['keyPrefix'] : '';
        $keySignature = isset($arr['keySignature']) && \is_string($arr['keySignature']) ? $arr['keySignature'] : null;
        $memoryCost = isset($arr['memoryCost']) && \is_int($arr['memoryCost']) ? $arr['memoryCost'] : null;
        $parallelism = isset($arr['parallelism']) && \is_int($arr['parallelism']) ? $arr['parallelism'] : null;
        // JS issuers may send a fractional expiresAt (e.g. Date.now() / 1000 + 600); it is signed as-is.
        $expiresAt = isset($arr['expiresAt']) && (\is_int($arr['expiresAt']) || \is_float($arr['expiresAt'])) ? $arr['expiresAt'] : null;
        /** @var null|array<array-key, mixed> $data */
        $data = isset($arr['data']) && (\is_array($arr['data']) || $arr['data'] instanceof \stdClass) ? (array) $arr['data'] : null;

        return new self(
            algorithm: $algorithm,
            nonce: $nonce,
            salt: $salt,
            cost: $cost,
            keyLength: $keyLength,
            keyPrefix: $keyPrefix,
            keySignature: $keySignature,
            memoryCost: $memoryCost,
            parallelism: $parallelism,
            expiresAt: $expiresAt,
            data: $data,
        );
    }
}
