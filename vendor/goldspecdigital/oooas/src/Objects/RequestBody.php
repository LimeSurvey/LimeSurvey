<?php

declare(strict_types=1);

namespace GoldSpecDigital\ObjectOrientedOAS\Objects;

use GoldSpecDigital\ObjectOrientedOAS\Utilities\Arr;

/**
 * @property string|null $description
 * @property \GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType[]|null $content
 * @property bool|null $required
 */
class RequestBody extends BaseObject
{
    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType[]|null
     */
    protected $content;

    /**
     * @var bool|null
     */
    protected $required;

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
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType[] $content
     * @return static
     */
    public function content(MediaType ...$content): self
    {
        $instance = clone $this;

        $instance->content = $content ?: null;

        return $instance;
    }

    /**
     * @param bool|null $required
     * @return static
     */
    public function required(?bool $required = true): self
    {
        $instance = clone $this;

        $instance->required = $required;

        return $instance;
    }

    /**
     * @return array
     */
    protected function generate(): array
    {
        $content = [];
        foreach ($this->content ?? [] as $contentItem) {
            $content[$contentItem->mediaType] = $contentItem;
        }

        return Arr::filter([
            'description' => $this->description,
            'content' => $content ?: null,
            'required' => $this->required,
        ]);
    }
}
