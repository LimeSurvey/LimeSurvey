<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Exception\HostedDomainException;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Google extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string If set, this will be sent to google as the "access_type" parameter.
     * @link https://developers.google.com/identity/protocols/OpenIDConnect#authenticationuriparameters
     */
    protected $accessType;

    /**
     * @var string If set, this will be sent to google as the "hd" parameter.
     * @link https://developers.google.com/identity/protocols/OpenIDConnect#authenticationuriparameters
     */
    protected $hostedDomain;

    /**
     * @var string If set, this will be sent to google as the "prompt" parameter.
     * @link https://developers.google.com/identity/protocols/OpenIDConnect#authenticationuriparameters
     */
    protected $prompt;

    /**
     * @var array List of scopes that will be used for authentication.
     * @link https://developers.google.com/identity/protocols/googlescopes
     */
    protected $scopes = [];

    public function getBaseAuthorizationUrl(): string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth';
    }

    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://oauth2.googleapis.com/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://openidconnect.googleapis.com/v1/userinfo';
    }

    protected function getAuthorizationParameters(array $options): array
    {
        if (empty($options['hd']) && $this->hostedDomain) {
            $options['hd'] = $this->hostedDomain;
        }

        if (empty($options['access_type']) && $this->accessType) {
            $options['access_type'] = $this->accessType;
        }

        if (empty($options['prompt']) && $this->prompt) {
            $options['prompt'] = $this->prompt;
        }

        // Default scopes MUST be included for OpenID Connect.
        // Additional scopes MAY be added by constructor or option.
        $scopes = array_merge($this->getDefaultScopes(), $this->scopes);

        if (!empty($options['scope'])) {
            $scopes = array_merge($scopes, $options['scope']);
        }

        $options['scope'] = array_unique($scopes);

        $options = parent::getAuthorizationParameters($options);

        // The "approval_prompt" MUST be removed as it is not supported by Google, use "prompt" instead:
        // https://developers.google.com/identity/protocols/oauth2/openid-connect#prompt
        unset($options['approval_prompt']);

        return $options;
    }

    protected function getDefaultScopes(): array
    {
        // "openid" MUST be the first scope in the list.
        return [
            'openid',
            'email',
            'profile',
        ];
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data): void
    {
        // @codeCoverageIgnoreStart
        if (empty($data['error'])) {
            return;
        }
        // @codeCoverageIgnoreEnd

        $code = 0;
        $error = $data['error'];

        if (is_array($error)) {
            $code = $error['code'];
            $error = $error['message'];
        }

        throw new IdentityProviderException($error, $code, $data);
    }

    protected function createResourceOwner(array $response, AccessToken $token): GoogleUser
    {
        $user = new GoogleUser($response);

        $this->assertMatchingDomain($user->getHostedDomain());

        return $user;
    }

    /**
     * @param string|null $hostedDomain
     *
     * @throws HostedDomainException If the domain does not match the configured domain.
     */
    protected function assertMatchingDomain(?string $hostedDomain): void
    {
        if ($this->hostedDomain === null) {
            // No hosted domain configured.
            return;
        }

        if ($this->hostedDomain === '*' && $hostedDomain) {
            // Any hosted domain is allowed.
            return;
        }

        if ($this->hostedDomain === $hostedDomain) {
            // Hosted domain is correct.
            return;
        }

        throw HostedDomainException::notMatchingDomain($this->hostedDomain);
    }
}
