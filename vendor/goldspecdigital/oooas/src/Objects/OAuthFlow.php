<?php

declare(strict_types=1);

namespace GoldSpecDigital\ObjectOrientedOAS\Objects;

use GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException;
use GoldSpecDigital\ObjectOrientedOAS\Utilities\Arr;

/**
 * @property string|null $flow
 * @property string|null $authorizationUrl
 * @property string|null $tokenUrl
 * @property string|null $refreshUrl
 * @property array|null $scopes
 */
class OAuthFlow extends BaseObject
{
    const FLOW_IMPLICIT = 'implicit';
    const FLOW_PASSWORD = 'password';
    const FLOW_CLIENT_CREDENTIALS = 'clientCredentials';
    const FLOW_AUTHORIZATION_CODE = 'authorizationCode';

    /**
     * @var string|null
     */
    protected $flow;

    /**
     * @var string|null
     */
    protected $authorizationUrl;

    /**
     * @var string|null
     */
    protected $tokenUrl;

    /**
     * @var string|null
     */
    protected $refreshUrl;

    /**
     * @var array|null
     */
    protected $scopes;

    /**
     * @param string|null $flow
     * @return static
     */
    public function flow(?string $flow): self
    {
        $instance = clone $this;

        $instance->flow = $flow;

        return $instance;
    }

    /**
     * @param string|null $authorizationUrl
     * @return static
     */
    public function authorizationUrl(?string $authorizationUrl): self
    {
        $instance = clone $this;

        $instance->authorizationUrl = $authorizationUrl;

        return $instance;
    }

    /**
     * @param string|null $tokenUrl
     * @return static
     */
    public function tokenUrl(?string $tokenUrl): self
    {
        $instance = clone $this;

        $instance->tokenUrl = $tokenUrl;

        return $instance;
    }

    /**
     * @param string|null $refreshUrl
     * @return static
     */
    public function refreshUrl(?string $refreshUrl): self
    {
        $instance = clone $this;

        $instance->refreshUrl = $refreshUrl;

        return $instance;
    }

    /**
     * @param array|null $scopes
     * @throws \GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException
     * @return static
     */
    public function scopes(?array $scopes): self
    {
        // Ensure the scopes are string => string.
        foreach ($scopes as $key => $value) {
            if (is_string($key) && is_string($value)) {
                continue;
            }

            throw new InvalidArgumentException('Each scope must have a string key and a string value.');
        }

        $instance = clone $this;

        $instance->scopes = $scopes;

        return $instance;
    }

    /**
     * @return array
     */
    protected function generate(): array
    {
        return Arr::filter([
            'authorizationUrl' => $this->authorizationUrl,
            'tokenUrl' => $this->tokenUrl,
            'refreshUrl' => $this->refreshUrl,
            'scopes' => $this->scopes,
        ]);
    }
}
