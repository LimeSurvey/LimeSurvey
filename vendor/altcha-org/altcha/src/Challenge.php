<?php

namespace AltchaOrg\Altcha;

class Challenge
{
    public function __construct(
        public readonly ChallengeParameters $parameters,
        public readonly ?string $signature = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $arr = ['parameters' => $this->parameters->toArray()];
        if (null !== $this->signature) {
            $arr['signature'] = $this->signature;
        }

        return $arr;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE) ?: '';
    }

    /**
     * @param array<string, mixed> $arr
     */
    public static function fromArray(array $arr): self
    {
        if (!isset($arr['parameters']) || !\is_array($arr['parameters'])) {
            throw new \InvalidArgumentException('Invalid challenge data: expected "parameters" (array).');
        }

        /** @var array<string, mixed> $parameters */
        $parameters = $arr['parameters'];

        return new self(
            parameters: ChallengeParameters::fromArray($parameters),
            signature: isset($arr['signature']) && \is_string($arr['signature']) ? $arr['signature'] : null,
        );
    }
}
