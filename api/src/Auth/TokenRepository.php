<?php

declare(strict_types=1);

namespace App\Auth;

use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Auth\IdentityWithTokenRepositoryInterface;
use Yiisoft\ActiveRecord\ActiveQuery;
use App\Auth\Token;
use App\Exception\UnauthorisedException;
use App\Exception\ServerErrorException;

final class TokenRepository implements IdentityWithTokenRepositoryInterface, IdentityRepositoryInterface
{

    public function __construct()
    {

    }

    // Not used in the REST API
    // phpcs:ignore
    public function findIdentity(string $id): ?IdentityInterface
    {
        /**
         * @psalm-suppress UndefinedClass
         */
        throw new \RuntimeException('not implemented');
    }

    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    // phpcs:ignore
    public function findIdentityByToken(string $token, string $type = null): ?IdentityInterface
    {
        session_id($token);
        session_start();

        if (count($_SESSION) == 0) {
            throw new UnauthorisedException();
        }

        $tokenData = Token::fromSession($_SESSION);

        return $tokenData;
    }

    public function save(Token $tokenData): string
    {
        return "";
    }
}
