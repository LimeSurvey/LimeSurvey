<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Auth\Handler\AuthenticationFailureHandler;
use Yiisoft\Strings\WildcardPattern;

/**
 * Authentication middleware tries to authenticate and identity using request data.
 * If identity is found, it is set to request attribute allowing further middleware to obtain and use it.
 * If identity is not found failure handler is called. By default it is {@see AuthenticationFailureHandler}.
 */
final class AuthMiddleware implements MiddlewareInterface
{
    private AuthenticationMethodInterface $authenticationMethod;

    /**
     * @var RequestHandlerInterface A handler that is called when there is a failure authenticating an identity.
     */
    private RequestHandlerInterface $failureHandler;

    /**
     * @var array Patterns to match to consider the given request URI path optional.
     */
    private array $optionalPatterns = [];

    public function __construct(
        AuthenticationMethodInterface $authenticationMethod,
        ResponseFactoryInterface $responseFactory,
        RequestHandlerInterface $authenticationFailureHandler = null
    ) {
        $this->authenticationMethod = $authenticationMethod;
        $this->failureHandler = $authenticationFailureHandler ?? new AuthenticationFailureHandler(
            $responseFactory
        );
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $identity = $this->authenticationMethod->authenticate($request);

        $request = $request->withAttribute("user", $identity);

        if ($identity === null && !$this->isOptional($request)) {
            return $this->authenticationMethod->challenge(
                $this->failureHandler->handle($request)
            );
        }

        return $handler->handle($request);
    }

    /**
     * @param array $optional Patterns to match to consider the given request URI path optional.
     *
     * @see WildcardPattern
     *
     * @return self
     */
    public function withOptionalPatterns(array $optional): self
    {
        $new = clone $this;
        $new->optionalPatterns = $optional;
        return $new;
    }

    /**
     * Checks, whether authentication is optional for the given request URI path.
     */
    private function isOptional(ServerRequestInterface $request): bool
    {
        $path = $request->getUri()->getPath();
        foreach ($this->optionalPatterns as $pattern) {
            $wildcardPattern = new WildcardPattern($pattern);
            if ($wildcardPattern->match($path)) {
                return true;
            }
        }
        return false;
    }
}
