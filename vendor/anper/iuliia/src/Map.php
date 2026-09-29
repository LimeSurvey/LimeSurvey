<?php

namespace Anper\Iuliia;

/**
 * Class Map
 * @package Anper\Iuliia
 */
class Map
{
    /**
     * @var array<string,string>
     */
    protected $data;

    /**
     * @param array<string,string> $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array<string,string>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @param string|null $default
     *
     * @return string|null
     */
    public function get(string $key, ?string $default = ''): ?string
    {
        return $this->data[$key] ?? $default;
    }
}
