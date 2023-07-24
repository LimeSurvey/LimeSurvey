<?php

declare(strict_types=1);

namespace GoldSpecDigital\ObjectOrientedOAS\Objects;

use GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException;
use GoldSpecDigital\ObjectOrientedOAS\Utilities\Arr;

/**
 * @property string|null $propertyName
 * @property array|null $mapping
 */
class Discriminator extends BaseObject
{
    /**
     * @var string|null
     */
    protected $propertyName;

    /**
     * @var array|null
     */
    protected $mapping;

    /**
     * @param string|null $propertyName
     * @return static
     */
    public function propertyName(?string $propertyName): self
    {
        $instance = clone $this;

        $instance->propertyName = $propertyName;

        return $instance;
    }

    /**
     * @param array $mapping
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return static
     */
    public function mapping(array $mapping): self
    {
        // Ensure the mappings are string => string.
        foreach ($mapping as $key => $value) {
            if (is_string($key) && is_string($value)) {
                continue;
            }

            throw new InvalidArgumentException('Each mapping must have a string key and a string value.');
        }

        $instance = clone $this;

        $instance->mapping = $mapping ?: null;

        return $instance;
    }

    /**
     * @return array
     */
    protected function generate(): array
    {
        return Arr::filter([
            'propertyName' => $this->propertyName,
            'mapping' => $this->mapping,
        ]);
    }
}
