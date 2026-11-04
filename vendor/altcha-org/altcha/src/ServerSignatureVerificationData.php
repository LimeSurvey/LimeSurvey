<?php

namespace AltchaOrg\Altcha;

/**
 * Generic verification data parsed from server signature payload.
 *
 * Supports property access ($data->key) and array access ($data['key']).
 *
 * @implements \ArrayAccess<string, mixed>
 *
 * @property mixed $verified
 * @property mixed $expire
 * @property mixed $score
 * @property mixed $fields
 * @property mixed $fieldsHash
 * @property mixed $reasons
 * @property mixed $classification
 * @property mixed $country
 * @property mixed $detectedLanguage
 * @property mixed $email
 * @property mixed $time
 */
class ServerSignatureVerificationData implements \ArrayAccess, \JsonSerializable
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly array $data = [],
    ) {
    }

    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->data[$name]);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('ServerSignatureVerificationData is read-only.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException('ServerSignatureVerificationData is read-only.');
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
