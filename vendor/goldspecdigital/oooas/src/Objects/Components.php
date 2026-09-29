<?php

declare(strict_types=1);

namespace GoldSpecDigital\ObjectOrientedOAS\Objects;

use GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract;
use GoldSpecDigital\ObjectOrientedOAS\Utilities\Arr;

/**
 * @property \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema[]|null $schemas
 * @property \GoldSpecDigital\ObjectOrientedOAS\Objects\Response[]|null $responses
 * @property \GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter[]|null $parameters
 * @property \GoldSpecDigital\ObjectOrientedOAS\Objects\Example[]|null $examples
 * @property \GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody[]|null $requestBodies
 * @property \GoldSpecDigital\ObjectOrientedOAS\Objects\Header[]|null $headers
 * @property \GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityScheme[]|null $securitySchemes
 * @property \GoldSpecDigital\ObjectOrientedOAS\Objects\Link[]|null $links
 */
class Components extends BaseObject
{
    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\Schema[]|null
     */
    protected $schemas;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\Response[]|null
     */
    protected $responses;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter[]|null
     */
    protected $parameters;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\Example[]|null
     */
    protected $examples;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody[]|null
     */
    protected $requestBodies;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\Header[]|null
     */
    protected $headers;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityScheme[]|null
     */
    protected $securitySchemes;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\Link[]|null
     */
    protected $links;

    /**
     * @var \GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem[]|null
     */
    protected $callbacks;

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Contracts\SchemaContract[] $schemas
     * @return static
     */
    public function schemas(SchemaContract ...$schemas): self
    {
        $instance = clone $this;

        $instance->schemas = $schemas ?: null;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\Response[] $responses
     * @return static
     */
    public function responses(Response ...$responses): self
    {
        $instance = clone $this;

        $instance->responses = $responses ?: null;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter[] $parameters
     * @return static
     */
    public function parameters(Parameter ...$parameters): self
    {
        $instance = clone $this;

        $instance->parameters = $parameters ?: null;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\Example[] $examples
     * @return static
     */
    public function examples(Example ...$examples): self
    {
        $instance = clone $this;

        $instance->examples = $examples ?: null;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\RequestBody[] $requestBodies
     * @return static
     */
    public function requestBodies(RequestBody ...$requestBodies): self
    {
        $instance = clone $this;

        $instance->requestBodies = $requestBodies ?: null;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\Header[] $headers
     * @return static
     */
    public function headers(Header ...$headers): self
    {
        $instance = clone $this;

        $instance->headers = $headers ?: null;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\SecurityScheme[] $securitySchemes
     * @return static
     */
    public function securitySchemes(SecurityScheme ...$securitySchemes): self
    {
        $instance = clone $this;

        $instance->securitySchemes = $securitySchemes ?: null;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\Link[] $links
     * @return static
     */
    public function links(Link ...$links): self
    {
        $instance = clone $this;

        $instance->links = $links ?: null;

        return $instance;
    }

    /**
     * @param \GoldSpecDigital\ObjectOrientedOAS\Objects\PathItem[] $callbacks
     * @return static
     */
    public function callbacks(PathItem ...$callbacks): self
    {
        $instance = clone $this;

        $instance->callbacks = $callbacks ?: null;

        return $instance;
    }

    /**
     * @return array
     */
    protected function generate(): array
    {
        $schemas = [];
        foreach ($this->schemas ?? [] as $schema) {
            $schemas[$schema->objectId] = $schema;
        }

        $responses = [];
        foreach ($this->responses ?? [] as $response) {
            $responses[$response->objectId] = $response;
        }

        $parameters = [];
        foreach ($this->parameters ?? [] as $parameter) {
            $parameters[$parameter->objectId] = $parameter;
        }

        $examples = [];
        foreach ($this->examples ?? [] as $example) {
            $examples[$example->objectId] = $example;
        }

        $requestBodies = [];
        foreach ($this->requestBodies ?? [] as $requestBodie) {
            $requestBodies[$requestBodie->objectId] = $requestBodie;
        }

        $headers = [];
        foreach ($this->headers ?? [] as $header) {
            $headers[$header->objectId] = $header;
        }

        $securitySchemes = [];
        foreach ($this->securitySchemes ?? [] as $securityScheme) {
            $securitySchemes[$securityScheme->objectId] = $securityScheme;
        }

        $links = [];
        foreach ($this->links ?? [] as $link) {
            $links[$link->objectId] = $link;
        }

        $callbacks = [];
        foreach ($this->callbacks ?? [] as $callback) {
            $callbacks[$callback->objectId][$callback->route] = $callback;
        }

        return Arr::filter([
            'schemas' => $schemas ?: null,
            'responses' => $responses ?: null,
            'parameters' => $parameters ?: null,
            'examples' => $examples ?: null,
            'requestBodies' => $requestBodies ?: null,
            'headers' => $headers ?: null,
            'securitySchemes' => $securitySchemes ?: null,
            'links' => $links ?: null,
            'callbacks' => $callbacks ?: null,
        ]);
    }
}
