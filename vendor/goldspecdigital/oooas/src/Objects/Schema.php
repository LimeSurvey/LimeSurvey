<?php

declare(strict_types=1);

namespace GoldSpecDigital\ObjectOrientedOAS\Objects;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException;
use GoldSpecDigital\ObjectOrientedOAS\Utilities\Arr;

/**
 * @property string|null $title
 * @property string|null $description
 * @property mixed[]|null $enum
 * @property mixed|null $default
 * @property string|null $format
 * @property string|null $type
 * @property \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema[]|null $items
 * @property int|null $maxItems
 * @property int|null $minItems
 * @property bool|null $uniqueItems
 * @property string|null $pattern
 * @property int|null $maxLength
 * @property int|null $minLength
 * @property int|float|null $maximum
 * @property int|float|null $exclusiveMaximum
 * @property int|float|null $minimum
 * @property int|float|null $exclusiveMinimum
 * @property int|float|null $multipleOf
 * @property string[]|null $required
 * @property \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract[]|null $properties
 * @property \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema|null $additionalProperties
 * @property int|null $maxProperties
 * @property int|null $minProperties
 * @property bool|null $nullable
 * @property \GoldSpecDigital\ObjectOrientedOAS\Objects\Discriminator|null $discriminator
 * @property bool|null $readOnly
 * @property bool|null $writeOnly
 * @property \GoldSpecDigital\ObjectOrientedOAS\Objects\Xml|null $xml
 * @property \GoldSpecDigital\ObjectOrientedOAS\Objects\ExternalDocs|null $externalDocs
 * @property mixed|null $example
 * @property bool|null $deprecated
 */
class Schema extends BaseObject implements SchemaContract
{
    const TYPE_ARRAY = 'array';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_INTEGER = 'integer';
    const TYPE_NUMBER = 'number';
    const TYPE_OBJECT = 'object';
    const TYPE_STRING = 'string';

    const FORMAT_INT32 = 'int32';
    const FORMAT_INT64 = 'int64';
    const FORMAT_FLOAT = 'float';
    const FORMAT_DOUBLE = 'double';
    const FORMAT_BYTE = 'byte';
    const FORMAT_BINARY = 'binary';
    const FORMAT_DATE = 'date';
    const FORMAT_DATE_TIME = 'date-time';
    const FORMAT_PASSWORD = 'password';
    const FORMAT_UUID = 'uuid';

    /**
     * @var string|null
     */
    protected $title;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var mixed[]|null
     */
    protected $enum;

    /**
     * @var mixed|null
     */
    protected $default;

    /**
     * @var string|null
     */
    protected $format;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema|null
     */
    protected $items;

    /**
     * @var int|null
     */
    protected $maxItems;

    /**
     * @var int|null
     */
    protected $minItems;

    /**
     * @var bool|null
     */
    protected $uniqueItems;

    /**
     * @var string|null
     */
    protected $pattern;

    /**
     * @var int|null
     */
    protected $maxLength;

    /**
     * @var int|null
     */
    protected $minLength;

    /**
     * @var int|null
     */
    protected $maximum;

    /**
     * @var int|null
     */
    protected $exclusiveMaximum;

    /**
     * @var int|null
     */
    protected $minimum;

    /**
     * @var int|null
     */
    protected $exclusiveMinimum;

    /**
     * @var int|null
     */
    protected $multipleOf;

    /**
     * @var string[]|null
     */
    protected $required;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract[]|null
     */
    protected $properties;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema|null
     */
    protected $additionalProperties;

    /**
     * @var int|null
     */
    protected $maxProperties;

    /**
     * @var int|null
     */
    protected $minProperties;

    /**
     * @var bool|null
     */
    protected $nullable;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\Discriminator|null
     */
    protected $discriminator;

    /**
     * @var bool|null
     */
    protected $readOnly;

    /**
     * @var bool|null
     */
    protected $writeOnly;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\Xml|null
     */
    protected $xml;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\ExternalDocs|null
     */
    protected $externalDocs;

    /**
     * @var mixed|null
     */
    protected $example;

    /**
     * @var bool|null
     */
    protected $deprecated;

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function array(string $objectId = null): self
    {
        return static::create($objectId)->type(static::TYPE_ARRAY);
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function boolean(string $objectId = null): self
    {
        return static::create($objectId)->type(static::TYPE_BOOLEAN);
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function integer(string $objectId = null): self
    {
        return static::create($objectId)->type(static::TYPE_INTEGER);
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function number(string $objectId = null): self
    {
        return static::create($objectId)->type(static::TYPE_NUMBER);
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function object(string $objectId = null): self
    {
        return static::create($objectId)->type(static::TYPE_OBJECT);
    }

    /**
     * @param string|null $objectId
     * @return static
     */
    public static function string(string $objectId = null): self
    {
        return static::create($objectId)->type(static::TYPE_STRING);
    }

    /**
     * @param string|null $title
     * @return static
     */
    public function title(?string $title): self
    {
        $instance = clone $this;

        $instance->title = $title;

        return $instance;
    }

    /**
     * @param string|null $description
     * @return static
     */
    public function description(?string $description): self
    {
        $instance = clone $this;

        $instance->description = $description;

        return $instance;
    }

    /**
     * @param mixed[] $enum
     * @return static
     */
    public function enum(...$enum): self
    {
        $instance = clone $this;

        $instance->enum = $enum ?: null;

        return $instance;
    }

    /**
     * @param mixed|null $default
     * @return static
     */
    public function default($default): self
    {
        $instance = clone $this;

        $instance->default = $default;

        return $instance;
    }

    /**
     * @param string|null $format
     * @return static
     */
    public function format(?string $format): self
    {
        $instance = clone $this;

        $instance->format = $format;

        return $instance;
    }

    /**
     * @param string|null $type
     * @return static
     */
    public function type(?string $type): self
    {
        $instance = clone $this;

        $instance->type = $type;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract $items
     * @return static
     */
    public function items(SchemaContract $items): self
    {
        $instance = clone $this;

        $instance->items = $items;

        return $instance;
    }

    /**
     * @param int|null $maxItems
     * @return static
     */
    public function maxItems(?int $maxItems): self
    {
        $instance = clone $this;

        $instance->maxItems = $maxItems;

        return $instance;
    }

    /**
     * @param int|null $minItems
     * @return static
     */
    public function minItems(?int $minItems): self
    {
        $instance = clone $this;

        $instance->minItems = $minItems;

        return $instance;
    }

    /**
     * @param bool|null $uniqueItems
     * @return static
     */
    public function uniqueItems(?bool $uniqueItems = true): self
    {
        $instance = clone $this;

        $instance->uniqueItems = $uniqueItems;

        return $instance;
    }

    /**
     * @param string|null $pattern
     * @return static
     */
    public function pattern(?string $pattern): self
    {
        $instance = clone $this;

        $instance->pattern = $pattern;

        return $instance;
    }

    /**
     * @param int|null $maxLength
     * @return static
     */
    public function maxLength(?int $maxLength): self
    {
        $instance = clone $this;

        $instance->maxLength = $maxLength;

        return $instance;
    }

    /**
     * @param int|null $minLength
     * @return static
     */
    public function minLength(?int $minLength): self
    {
        $instance = clone $this;

        $instance->minLength = $minLength;

        return $instance;
    }

    /**
     * @param int|float|null $maximum
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return static
     */
    public function maximum($maximum): self
    {
        if (
            !is_int($maximum)
            && !is_float($maximum)
            && !is_null($maximum)
        ) {
            throw new InvalidArgumentException('The maximum must either be an int, float or null.');
        }

        $instance = clone $this;

        $instance->maximum = $maximum;

        return $instance;
    }

    /**
     * @param int|float|null $exclusiveMaximum
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return static
     */
    public function exclusiveMaximum($exclusiveMaximum): self
    {
        if (
            !is_int($exclusiveMaximum)
            && !is_float($exclusiveMaximum)
            && !is_null($exclusiveMaximum)
        ) {
            throw new InvalidArgumentException('The exclusive maximum must either be an int, float or null.');
        }

        $instance = clone $this;

        $instance->exclusiveMaximum = $exclusiveMaximum;

        return $instance;
    }

    /**
     * @param int|float|null $minimum
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return static
     */
    public function minimum($minimum): self
    {
        if (
            !is_int($minimum)
            && !is_float($minimum)
            && !is_null($minimum)
        ) {
            throw new InvalidArgumentException('The minimum must either be an int, float or null.');
        }

        $instance = clone $this;

        $instance->minimum = $minimum;

        return $instance;
    }

    /**
     * @param int|float|null $exclusiveMinimum
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return static
     */
    public function exclusiveMinimum($exclusiveMinimum): self
    {
        if (
            !is_int($exclusiveMinimum)
            && !is_float($exclusiveMinimum)
            && !is_null($exclusiveMinimum)
        ) {
            throw new InvalidArgumentException('The exclusive minimum must either be an int, float, or null.');
        }

        $instance = clone $this;

        $instance->exclusiveMinimum = $exclusiveMinimum;

        return $instance;
    }

    /**
     * @param int|float|null $multipleOf
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return static
     */
    public function multipleOf($multipleOf): self
    {
        if (
            !is_int($multipleOf)
            && !is_float($multipleOf)
            && !is_null($multipleOf)
        ) {
            throw new InvalidArgumentException('The multiple of must either be an int, float or null.');
        }

        $instance = clone $this;

        $instance->multipleOf = $multipleOf;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema[]|string[] $required
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return static
     */
    public function required(...$required): self
    {
        // Only allow Schema instances and strings.
        foreach ($required as &$require) {
            // If a Schema instance was passed in then extract it's name string.
            if ($require instanceof Schema) {
                $require = $require->objectId;
                continue;
            }

            if (is_string($require)) {
                continue;
            }

            throw new InvalidArgumentException(
                sprintf(
                    'The required must either be an instance of [%s] or a string.',
                    Schema::class
                )
            );
        }

        $instance = clone $this;

        $instance->required = $required ?: null;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract[] $properties
     * @return static
     */
    public function properties(SchemaContract ...$properties): self
    {
        $instance = clone $this;

        $instance->properties = $properties ?: null;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema|null $additionalProperties
     * @return static
     */
    public function additionalProperties(?Schema $additionalProperties): self
    {
        $instance = clone $this;

        $instance->additionalProperties = $additionalProperties;

        return $instance;
    }

    /**
     * @param int|null $maxProperties
     * @return static
     */
    public function maxProperties(?int $maxProperties): self
    {
        $instance = clone $this;

        $instance->maxProperties = $maxProperties;

        return $instance;
    }

    /**
     * @param int|null $minProperties
     * @return static
     */
    public function minProperties(?int $minProperties): self
    {
        $instance = clone $this;

        $instance->minProperties = $minProperties;

        return $instance;
    }

    /**
     * @param bool|null $nullable
     * @return static
     */
    public function nullable(?bool $nullable = true): self
    {
        $instance = clone $this;

        $instance->nullable = $nullable;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\Discriminator|null $discriminator
     * @return static
     */
    public function discriminator(?Discriminator $discriminator): self
    {
        $instance = clone $this;

        $instance->discriminator = $discriminator;

        return $instance;
    }

    /**
     * @param bool|null $readOnly
     * @return static
     */
    public function readOnly(?bool $readOnly = true): self
    {
        $instance = clone $this;

        $instance->readOnly = $readOnly;

        return $instance;
    }

    /**
     * @param bool|null $writeOnly
     * @return static
     */
    public function writeOnly(?bool $writeOnly = true): self
    {
        $instance = clone $this;

        $instance->writeOnly = $writeOnly;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\Xml|null $xml
     * @return static
     */
    public function xml(?Xml $xml): self
    {
        $instance = clone $this;

        $instance->xml = $xml;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\ExternalDocs|null $externalDocs
     * @return static
     */
    public function externalDocs(?ExternalDocs $externalDocs): self
    {
        $instance = clone $this;

        $instance->externalDocs = $externalDocs;

        return $instance;
    }

    /**
     * @param mixed|null $example
     * @return static
     */
    public function example($example): self
    {
        $instance = clone $this;

        $instance->example = $example;

        return $instance;
    }

    /**
     * @param bool|null $deprecated
     * @return static
     */
    public function deprecated(?bool $deprecated = true): self
    {
        $instance = clone $this;

        $instance->deprecated = $deprecated;

        return $instance;
    }

    /**
     * @return array
     */
    protected function generate(): array
    {
        $properties = [];
        foreach ($this->properties ?? [] as $property) {
            $properties[$property->objectId] = $property->toArray();
        }

        return Arr::filter([
            'title' => $this->title,
            'description' => $this->description,
            'enum' => $this->enum,
            'default' => $this->default,
            'format' => $this->format,
            'type' => $this->type,
            'items' => $this->items,
            'maxItems' => $this->maxItems,
            'minItems' => $this->minItems,
            'uniqueItems' => $this->uniqueItems,
            'pattern' => $this->pattern,
            'maxLength' => $this->maxLength,
            'minLength' => $this->minLength,
            'maximum' => $this->maximum,
            'exclusiveMaximum' => $this->exclusiveMaximum,
            'minimum' => $this->minimum,
            'exclusiveMinimum' => $this->exclusiveMinimum,
            'multipleOf' => $this->multipleOf,
            'required' => $this->required,
            'properties' => $properties ?: null,
            'additionalProperties' => $this->additionalProperties,
            'maxProperties' => $this->maxProperties,
            'minProperties' => $this->minProperties,
            'nullable' => $this->nullable,
            'discriminator' => $this->discriminator,
            'readOnly' => $this->readOnly,
            'writeOnly' => $this->writeOnly,
            'xml' => $this->xml,
            'externalDocs' => $this->externalDocs,
            'example' => $this->example,
            'deprecated' => $this->deprecated,
        ]);
    }
}
