<?php

declare(strict_types=1);

namespace GoldSpecDigital\ObjectOrientedOAS\Objects;

use GoldSpecDigital\ObjectOrientedOAS\Exceptions\PropertyDoesNotExistException;
use GoldSpecDigital\ObjectOrientedOAS\Utilities\Extensions;
use JsonSerializable;

/**
 * @property string|null $objectId
 * @property string|null $ref
 * @property array|null $x
 */
abstract class BaseObject implements JsonSerializable
{
    /**
     * @var string|null
     */
    protected $objectId;

    /**
     * @var string|null
     */
    protected $ref;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Utilities\Extensions
     */
    protected $extensions;

    /**
     * BaseObject constructor.
     *
     * @param string|null $objectId
     */
    public function __construct(string $objectId = null)
    {
        $this->objectId = $objectId;
        $this->extensions = new Extensions();
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function create(string $objectId = null): self
    {
        return new static($objectId);
    }

    /**
     * @param string $ref
     * @param string|null $objectId
     * @return static
     */
    public static function ref(string $ref, string $objectId = null): self
    {
        $instance = new static($objectId);

        $instance->ref = $ref;

        return $instance;
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public function objectId(?string $objectId): self
    {
        $instance = clone $this;

        $instance->objectId = $objectId;

        return $instance;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function x(string $key, $value = Extensions::X_EMPTY_VALUE): self
    {
        $instance = clone $this;

        if (mb_strpos($key, 'x-') === 0) {
            $key = mb_substr($key, 2);
        }

        $instance->extensions[$key] = $value;

        return $instance;
    }

    /**
     * @return array
     */
    abstract protected function generate(): array;

    /**
     * @return array
     */
    public function toArray(): array
    {
        if ($this->ref !== null) {
            return ['$ref' => $this->ref];
        }

        return array_merge(
            $this->generate(),
            $this->extensions->toArray()
        );
    }

    /**
     * @param int $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param string $name
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\PropertyDoesNotExistException
     * @return mixed
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        // Get all extensions.
        if ($name === 'x') {
            return $this->extensions->toArray();
        }

        // Get a single extension.
        if (mb_strpos($name, 'x-') === 0) {
            $key = mb_strtolower(substr_replace($name, '', 0, 2));

            if (isset($this->extensions[$key])) {
                return $this->extensions[$key];
            }
        }

        throw new PropertyDoesNotExistException("[{$name}] is not a valid property.");
    }
}
