<?php

declare(strict_types=1);

namespace GoldSpecDigital\ObjectOrientedOAS\Objects;

use GoldSpecDigital\ObjectOrientedOAS\Utilities\Arr;

/**
 * @property string|null $description
 * @property string|null $url
 */
class ExternalDocs extends BaseObject
{
    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string
     */
    protected $url;

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
     * @param string|null $url
     * @return static
     */
    public function url(?string $url): self
    {
        $instance = clone $this;

        $instance->url = $url;

        return $instance;
    }

    /**
     * @return array
     */
    protected function generate(): array
    {
        return Arr::filter([
            'description' => $this->description,
            'url' => $this->url,
        ]);
    }
}
